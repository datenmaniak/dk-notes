# Troubleshooting Error 404 page not found

Fecha: 26.05.2026, 23:04

## Introduccion

Se trata de una aplicacion Web para el registro de notas, desarrollada en `Laravel`. Utiliza `Postgres` como manejador de la base de datos. `nginx` como proxy.

El desarrollo se llevo a cabo de manera local, en mi estacion de trabajo. Se utilizo Podman.

Para crear el entorno de desarrollo, se utilizo `contenedores`. 

- Nginx
- Laravel
- Postgres

Un `Dockerfile` para cada uno, e integrado en un `docker-compose`. 


## Escenario 

Despues de haberse comprobado el correcto funcionamient de la aplicacion, se han escritos los manifiestos para migrar esta aplicacion `Laravel`  a `k3s`. 

La imagen para `laravel` fue creada con exito, y optimizada pensada para migrar a Kubernetes. De Manera que fue comprobado su funcionamiento.

La imagen que contiene la aplicacion, fue subida al registro local que se administra en el Master k3s.

- registro.local:5000/dknotes-laravel:1.2    


En el k3s-master, se ha mapeado una particion NFS en esta ruta: `/archives/dknotes`
para la persistencia.

Se ha creado en la estacion de trabajo local, una entrada en /etc/hosts:

- dknotes.local


En la configuracion de Ingress, se ha configurado los puertos 80 y 443.


## Problemas o fallas

Al acceder a la aplicacion via URL:  https://dknotes.local:8443, se observa:

    404 page not found


### Observaciones:

1. No estoy seguro que produce el erro 404.
2. No estoy seguro si se hace uso de la particion destinada para la persistencia.
3. Quisiera que evalue este documento.
4. No genere codigo
5. Muestra si hay errores y explicame
6. Dame alternativas antes de proceder a una solucion 

### Importante:
Olvide mencionar: mi estacion de trabajo se encuentra en la red 192.168.1.0/24 y el cluster de k3s esta detras de un firewall, en la red 10.0.0.1/24.  Ya que el acceso web del firewall se realiza a traves de: https://192.168.1.2  se ha creado un NAT con el puerto 8443

## Estructura del directorio (Orquestacion k3s)

```bash
kustomization
├── default.conf
├── deploy.yaml
├── dknotes.local-key.pem
├── dknotes.local.pem
├── ingress.yaml
├── kustomization.yaml
├── ns.yaml
├── pv-pvc-nfs-persistence.yaml
├── secret.yaml
└── webservice.yaml
```


###  kustomization.yaml

```yml
# kustomization.yaml
apiVersion: kustomize.config.k8s.io/v1beta1
kind: Kustomization

namespace: dknotes

# --- Aquí defines el secreto de forma manual  ---#
secretGenerator:
- name: dknotes-tls-secret
  type: kubernetes.io/tls
  files:
      - tls.crt=dknotes.local.pem 
      - tls.key=dknotes.local-key.pem


# Aquí enlazas el archivo del deployment
resources:
# 1. Base / entorno 
- ns.yaml
# 2. Configuracion u seguridad 
- secret.yaml
# configmap.yaml 
# 3. Almacenamiento
- pv-pvc-nfs-persistence.yaml
# 4. cargas de trabajo
- deploy.yaml
# 5. Red y conectividad
- webservice.yaml
- ingress.yaml
# patches:
#   - path: 09-patch-probes.yaml
#     target:
#       kind: Deployment
#       name: mariadb
ConfigMapGenerator:
- name: nginx-config
  files:
    - default.conf
```

## Namespace
```yml
apiVersion: v1
kind: Namespace
metadata:
  name: dknotes # Puedes usar el nombre que prefieras
```



## Secret
```yml
apiVersion: v1
kind: Secret
metadata:
  name: dknotes-secrets
  namespace: dknotes
type: Opaque
data:
  # Los valores deben estar en base64 (ej: echo -n "dk" | base64)
  username: ZGt1c2Vy
  password: N3Nob2d1bg==
```

## PersistentVolume / PersistenVolumeClaim

```yml
---
# 1. El Volumen Físico
apiVersion: v1
kind: PersistentVolume
metadata:
  name: dknotes-uploads-pv
spec:
  storageClassName: ""
  capacity:
    storage: 16Gi
  volumeMode: Filesystem
  accessModes:
    - ReadWriteMany # Permite que varios pods lean/escriban a la vez
  persistentVolumeReclaimPolicy: Retain # Mantiene los archivos si se borra el PVC
  nfs:
    path: /archives/dknotes     # ruta dedicada en el servidor NFS
    server: 10.0.0.200              # IP de servidor NFS
---
# 2. El Reclamo del Espacio
apiVersion: v1
kind: PersistentVolumeClaim
metadata:
  name: dknotes-uploads-pvc
  namespace: dknotes
spec:
  storageClassName: ""
  accessModes:
    - ReadWriteMany
  resources:
    requests:
      storage: 16Gi
  volumeName: dknotes-uploads-pv # Vinculación directa al PV creado arriba
```


## Deployment
```yaml
apiVersion: apps/v1
kind: Deployment
metadata:
  name: dknotes-web
  labels:
    app: dknotes
spec:
  replicas: 1
  selector:
    matchLabels:
      app: dknotes
  template:
    metadata:
      labels:
        app: dknotes
    spec:
      containers:
      # ----------------------------------------------------
      # 1. CONTENEDOR NGINX (Servidor Web)
      # ----------------------------------------------------
      - name: nginx
        image: nginx:alpine
        ports:
        - containerPort: 80
          name: http
        volumeMounts:
        # Volumen compartido: Aquí Nginx buscará el index.php y assets estáticos
        - name: public-assets
          mountPath: /var/www/html
        - name: nginx-config-volume
          mountPath: /etc/nginx/conf.d/default.conf
          subPath: default.conf
  
      # El Procesador de PHP (Laravel)
      - name: web-app
        image: 10.0.0.100:5000/dknotes-laravel:1.2
        imagePullPolicy: Always
        ports:
        - containerPort: 9000
          name: fastcgi

        # =========================================================================
        # 🚀 CONFIGURACIÓN DEL ENTORNO (INYECCIÓN SIN ARCHIVO .ENV)
        # =========================================================================
        env:
        # 1. Configuración General de la Aplicación
        - name: APP_ENV
          value: "production"
        - name: APP_DEBUG
          value: "false"            # Desactivado en producción para no exponer errores internos
        - name: APP_URL
          value: "https://dknotes.local" # Modifica esto por tu dominio o IP del clúster
        - name: APP_KEY
          value: "N3Nob2d1bg==" # Clave para encriptar sesiones

        # 2. Conexión al motor de Base de Datos (PostgreSQL)
        - name: DB_CONNECTION
          value: "pgsql"                 # ← Reemplaza el 'mysql' por defecto de Laravel
        - name: DB_HOST
          value: "postgres-service.postgres.svc.cluster.local"
        - name: DB_PORT
          value: "5432"                  # ← Puerto nativo de PostgreSQL
        - name: DB_DATABASE
          value: "dknotes"
 
        # 3. Credenciales de la Base de Datos (Inyectadas desde tu Secret de K8s)
        - name: DB_USERNAME
          valueFrom:
            secretKeyRef: { name: dknotes-secrets, key: username}
        - name: DB_PASSWORD
          valueFrom:
            secretKeyRef:
              name: dknotes-secrets
              key: password
        resources:
          limits:
            cpu: "1000m"
            memory: "1Gi"
          requests:
            cpu: "400m"
            memory: "512Mi"
        volumeMounts:
        # Laravel expone su carpeta pública nativa (con los assets de Vite ya compilados)
        # al volumen de Nginx
        - name: public-assets
          mountPath: /var/www/html/public
        # PUNTO DE PERSISTENCIA: Tu PVC montado en las rutas para cargar imágenes y data externa
        - name: storage-persistence
          mountPath: /var/www/html/storage/app/public/contenido
        - name: storage-persistence
          mountPath: /var/www/html/storage/app/data
      

      # ----------------------------------------------------
      # 3. VOLÚMENES DEL POD
      # ----------------------------------------------------
      volumes:
      - name: public-assets
        emptyDir: {}
      - name: nginx-config-volume
        configMap:
          name: nginx-config
      # PVC existente para datos persistentes
      - name: storage-persistence
        persistentVolumeClaim:
          claimName: dknotes-uploads-pvc
```

## Service
```yml
apiVersion: v1
kind: Service
metadata:
  name: dknotes-service
  namespace: dknotes
spec:
  selector:
    app: dknotes
  ports:
    - protocol: TCP
      port: 80
      targetPort: 80
  type: ClusterIP
```

## Ingress
```yaml
apiVersion: networking.k8s.io/v1
kind: Ingress
metadata:
  name: dknotes-ingress
  namespace: dknotes 
spec:
  ingressClassName: traefik
  rules:
  - host: dknotes.local
    http:
      paths:
      - path: /
        pathType: Prefix
        backend:
          service:
            name: webservice-service
            port:
              number: 80
  tls:
  - hosts:
      - dknotes.local
    secretName: dknotes-tls-secret
```

