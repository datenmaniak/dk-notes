## Acceso seguro

Para gestionar la contraseña de MariaDB en Kubernetes de manera segura, lo ideal es no escribirla directamente en el archivo de despliegue, sino utilizar un **Secret**.

Aquí tienes los pasos y los manifiestos necesarios para configurar esto correctamente en tu clúster.

### 1. Crear el Secret (Recomendado)

Primero, crea un manifiesto para el Secret. Los valores en un Secret de Kubernetes deben estar codificados en **Base64**.

> **Nota:** Para codificar tu contraseña en la terminal, puedes usar: 
>     echo -n 'tu_password' | base64


```bash 
touch secret.yaml
```

```yaml
apiVersion: v1
kind: Secret
metadata:
  name: mariadb-passwords
  namespace: default
type: Opaque
data:
  # Ejemplo: la cadena 'admin123' en base64 es YWRtaW4xMjM=
  mariadb-root-password: YWRtaW4xMjM=
  mariadb-password: YWRtaW4xMjM=
```

---

### 2. Referenciar el Secret en el Deployment

En el manifiesto de tu base de datos (Deployment o StatefulSet), debes mapear esas llaves del Secret a las variables de entorno que MariaDB espera.

```bash
touch deployment.yaml
```

```yaml
apiVersion: apps/v1
kind: Deployment
metadata:
  name: mariadb
spec:
  selector:
    matchLabels:
      app: mariadb
  template:
    metadata:
      labels:
        app: mariadb
    spec:
      containers:
      - name: mariadb
        image: mariadb:latest
        env:
        - name: MARIADB_ROOT_PASSWORD
          valueFrom:
            secretKeyRef:
              name: mariadb-passwords
              key: mariadb-root-password
        - name: MARIADB_PASSWORD
          valueFrom:
            secretKeyRef:
              name: mariadb-passwords
              key: mariadb-password
        - name: MARIADB_USER
          value: "user_name"
        - name: MARIADB_DATABASE
          value: "my_database"
        ports:
        - containerPort: 3306
```

---

### Consideraciones de Seguridad y Flujo

Cuando aplicas estos manifiestos, Kubernetes inyecta los valores decodificados directamente en el contenedor.

- **Persistencia:** Asegúrate de que tu MariaDB esté usando un **PersistentVolumeClaim (PVC)** para que los datos no se borren si el pod se reinicia.
    
- **Gestión de Secretos:** Si estás usando herramientas como **chezmoi** para tus dotfiles, recuerda que no es seguro subir los archivos de Secrets con los valores reales a repositorios públicos; usa herramientas de cifrado (como GPG o Sealed Secrets) si planeas versionarlos.
    

Para aplicar ambos archivos, simplemente usa:

`kubectl apply -f secreto.yaml -f deployment.yaml`

## Quiero hacerlo de manera manual, donde asigno la clave que deseo?


Si prefieres crear el **Secret** de forma manual (directamente desde la terminal) para no lidiar con la codificación manual en Base64 dentro de un archivo YAML, la forma más rápida y segura es usar el comando `kubectl create secret`.

Aquí tienes cómo hacerlo paso a paso para integrarlo en tu flujo de trabajo:

### 1. Crear el Secret vía CLI

Ejecuta el siguiente comando reemplazando `tu_clave_root` y `tu_clave_usuario` por las contraseñas que desees. El flag `--from-literal` se encarga de codificarlas automáticamente por ti.


```bash
kubectl create secret generic mariadb-passwords \
  --from-literal=mariadb-root-password='tu_clave_root' \
  --from-literal=mariadb-password='tu_clave_usuario'
```

### 2. Verificar el Secret

Una vez creado, puedes verificar que existe y ver los nombres de las llaves (aunque los valores aparecerán ocultos):


```bash
kubectl get secret mariadb-passwords -o yaml
```

---
### 3. El Manifiesto del Deployment

Ahora que el secreto ya vive en tu clúster de **k3s**, solo necesitas el manifiesto que lo consuma. Como mencionamos antes, este archivo solo hace referencia a las "etiquetas" del secreto, por lo que es seguro guardarlo en tus directorios organizados bajo el sistema **Johnny.Decimal**.


```bash
apiVersion: apps/v1
kind: Deployment
metadata:
  name: mariadb-server
  labels:
    app: mariadb
spec:
  replicas: 1
  selector:
    matchLabels:
      app: mariadb
  template:
    metadata:
      labels:
        app: mariadb
    spec:
      containers:
      - name: mariadb
        image: mariadb:latest
        env:
          # Aquí asignamos la contraseña de ROOT desde el Secret
          - name: MARIADB_ROOT_PASSWORD
            valueFrom:
              secretKeyRef:
                name: mariadb-passwords
                key: mariadb-root-password
          
          # Aquí la contraseña de un usuario estándar
          - name: MARIADB_PASSWORD
            valueFrom:
              secretKeyRef:
                name: mariadb-passwords
                key: mariadb-password
          
          # Configuración adicional
          - name: MARIADB_USER
            value: "admin_user"
          - name: MARIADB_DATABASE
            value: "main_db"
        ports:
          - containerPort: 3306
            name: mysql
```


> [!NOTE]
> Si en algún momento necesitas actualizar la contraseña manualmente, recuerda que primero debes borrar el secreto anterior: `kubectl delete secret mariadb-passwords` y luego volver a crearlo con el nuevo valor.


## Si deseo declarar en el manifiesto la clave y que esta sea convertida

El manifiesto sera el "origen de la verdad". En Kubernetes, para que una clave escrita en texto plano en un archivo se convierta en un objeto de tipo **Secret**, se utiliza un recurso llamado **Kustomize** (que ya viene integrado en `kubectl`).

Esta es la forma más limpia de hacerlo:

### 1. Estructura de archivos

Crea una carpeta para este despliegue (por ejemplo, dentro de tu estructura de para servicios de base de datos) con estos dos archivos como ejemplo:


```plaintext
mariadb-config/
├── kustomization.yaml
└── deployment.yaml
```

### 2. El archivo `kustomization.yaml`

> [!NOTE]
> Aquí es donde pones la contraseña "en claro". Al ejecutar el comando, Kubernetes la transformará automáticamente en un Secret codificado.


```bash
# kustomization.yaml
apiVersion: kustomize.config.k8s.io/v1beta1
kind: Kustomization

# Aquí defines el secreto de forma manual
secretGenerator:
- name: mariadb-passwords
  literals:
    - mariadb-root-password=MiClaveSegura123
    - mariadb-password=OtraClaveDeUsuario456

# Aquí enlazas el archivo del deployment
resources:
- deployment.yaml
```

### 3. El archivo `deployment.yaml`

En este archivo no escribes la clave, solo haces referencia al nombre que definiste arriba.


```yaml
# deployment.yaml
apiVersion: apps/v1
kind: Deployment
metadata:
  name: mariadb
spec:
  selector:
    matchLabels:
      app: mariadb
  template:
    metadata:
      labels:
        app: mariadb
    spec:
      containers:
      - name: mariadb
        image: mariadb:latest
        env:
        - name: MARIADB_ROOT_PASSWORD
          valueFrom:
            secretKeyRef:
              name: mariadb-passwords
              key: mariadb-root-password
```

---

### Cómo aplicarlo

En lugar de usar el comando tradicional, usa el flag `-k` (de Kustomize) apuntando a la carpeta:


```bash
kubectl apply -k ./mariadb-config/
```

**¿Qué sucede al ejecutar esto?**

1. Kubernetes lee el `kustomization.yaml`.
    
2. Toma tus claves en texto plano y genera un objeto `Secret` en el clúster.
    
3. Le añade un sufijo aleatorio al nombre (ej. `mariadb-passwords-f76gh2`) para asegurar que, si cambias la clave en el futuro, los pods se reinicien automáticamente con el nuevo valor.
    

> **Advertencia de seguridad:** Como ahora la clave está escrita en un archivo `.yaml`, asegúrate de que este archivo esté protegido. Si usas un sistema de gestión de archivos como **chezmoi**, podrías cifrar este archivo específico con **GPG** antes de subirlo a cualquier sitio.


## Si ya tengo un manifiesto de Postgres

### Cuales ajustes son necesarios para el deployment?


Estar atento para revisar la estructura y  refinar los siguientes puntos técnicos:

1. **Integración con Secrets:** Para asegurar que las contraseñas se manejen como discutimos, ya sea vía Kustomize o referenciando secretos existentes.
    
2. **Configuración de Almacenamiento:** Revisar los `volumeMounts` y el `PersistentVolumeClaim` para asegurar que tus bases de datos MariaDB sean persistentes en tu nodo.
    
3. **Recursos y Límites:** Ajustar los parámetros de CPU y memoria para que funcionen de forma óptima en tu entorno de hardware actual.
    
4. **Networking:** Verificar que los puertos y selectores coincidan con el servicio que planeas exponer.


> [!mantener la persistencia ]
> Al momento de poner en marcha este manifiesto en Kubernetes, ha sido configurado con antelación el almacenamiento para MariaDB. Se trata de una partición reservada en el **Hipervisor Proxmox**. En este servidor, se realiza el montaje del almacenamiento y exportación por medio de NFS a la maquina virtual del master de K3s.


Hay varios puntos importantes que debemos corregir para que funcione correctamente. El error más crítico es que estás usando comandos de **PostgreSQL** (`pg_isready`) en una base de datos **MariaDB**, y hay algunas inconsistencias en los nombres de las variables de entorno.

Aquí tienes el despliegue afinado:

```yaml
apiVersion: apps/v1
kind: Deployment
metadata:
  name: mariadb
  namespace: mariadb
  labels:
    app: mariadb
spec:
  replicas: 1
  selector:
    matchLabels:
      app: mariadb
  template:
    metadata:
      labels:
        app: mariadb
    spec:
      nodeSelector:
        kubernetes.io/hostname: k3s-master
      # Se eliminó runAsUser: 1001 si usas la imagen 
      #  oficial de MariaDB, ya que suele usar el usuario
      #  `999`. Si tu volumen requiere 1001, mantenlo.
      securityContext:
        fsGroup: 1001
      containers:
      - name: mariadb
        image: mariadb:11.4 # Nota: MariaDB 12 aún no es
		                # estable/común, 11.4 es la LTS actual
        imagePullPolicy: IfNotPresent
        ports:
        - containerPort: 3306
          name: mariadb
        env:
        # 1. Contraseña Root desde Secret
        - name: MARIADB_ROOT_PASSWORD
          valueFrom:
            secretKeyRef:
              name: mariadb-password
              key: mariadb-root-password
        # 2. Configuración de DB y Usuario inicial
        - name: MARIADB_DATABASE
          value: "appdb"   # <-- ajustar 
        - name: MARIADB_USER
          value: "admin"    # <--- ajustar segun el caso
        - name: MARIADB_PASSWORD
          valueFrom:
            secretKeyRef:
              name: mariadb-password
              key: mariadb-password
        volumeMounts:
        - name: mariadb-storage
          mountPath: /var/lib/mysql # El path estándar de 
          #                          MariaDB.
        resources:
          requests:
            memory: "256Mi"
            cpu: "250m"
          limits:
            memory: "1Gi"
            cpu: "500m"
        # 3. Corrección de Probes (Usando comandos de mysqladmin)
        livenessProbe:
          exec:
            command:
            - "bash"
            - "-c"
            - "mysqladmin ping -u root -p${MARIADB_ROOT_PASSWORD}"
          initialDelaySeconds: 30
          periodSeconds: 10
          timeoutSeconds: 5
        readinessProbe:
          exec:
            command:
            - "bash"
            - "-c"
            - "mysqladmin ping -u root -p${MARIADB_ROOT_PASSWORD}"
          initialDelaySeconds: 5
          periodSeconds: 5
          timeoutSeconds: 3
      volumes:
      - name: mariadb-storage
        persistentVolumeClaim:
          claimName: mariadb-pvc
```

### Puntos clave que se ajustaron:

1. **Probes (Liveness/Readiness):** Tenías `pg_isready`, que es exclusivo de PostgreSQL. Para MariaDB se usa `mysqladmin ping`. He encapsulado el comando en un `bash -c` para que pueda leer la variable de entorno de la contraseña directamente.
    
2. **Variables de Entorno:**
    
    - Cambié `mariadb_PASSWORD` a `MARIADB_PASSWORD` (las imágenes oficiales son sensibles a mayúsculas).
        
    - Cambié `mariadb_DB` a `MARIADB_DATABASE`.
        
    - Eliminé la variable `mysql`, ya que no es necesaria para la inicialización estándar.
        
3. **Mount Path:** El path de datos por defecto es `/var/lib/mysql`. En tu versión tenías `/var/lib/mysql/mysql`, lo que podría causar problemas de permisos o estructuras de carpetas duplicadas.
    
4. **SecurityContext:** He dejado el `fsGroup`, pero ten cuidado con `runAsUser: 1001`. La imagen oficial de MariaDB usa internamente el usuario `mysql` (UID 999). Si tu almacenamiento NFS o local en el nodo master tiene permisos específicos para el 1001, está bien, de lo contrario, podría fallar al intentar escribir.
    
5. **Versión de Imagen:** He sugerido `11.4` (LTS) o simplemente `latest`. `12-noble` no es una etiqueta común todavía para MariaDB; usualmente se prefiere usar versiones basadas en Debian o la versión numérica específica.
    

### El Secret que necesitas

Para que este deployment no falle, asegúrate de haber creado el secreto con las llaves exactas:


```bash
kubectl create secret generic mariadb-password \
  --namespace mariadb \
  --from-literal=mariadb-root-password='TU_CLAVE_ROOT' \
  --from-literal=mariadb-password='TU_CLAVE_USUARIO'
```


## Revisión del archivo: `kustomization.yaml`

Al revisar un archivo `kustomization.yaml`, es necesario asegurarse de verificar:

1. **Generación de Secretos:** Que los `literals` coincidan con las llaves que definimos en el `Deployment` (`mariadb-root-password` y `mariadb-password`).
    
2. **Namespace:** Que el namespace coincida con el que definiste en tu despliegue (`mariadb`), para que los objetos se encuentren entre sí.
    
3. **Referencias de Recursos:** Que el archivo `deployment.yaml` esté correctamente listado en la sección `resources`.
    
4. **Generadores de Configuración:** Si tienes algún `ConfigMap` adicional para los archivos de configuración de MariaDB (como un `my.cnf` personalizado).


Tu `kustomization.yaml` está bien estructurado, pero hay un detalle crítico de **nombres** que hará que el Deployment no encuentre las contraseñas. En tu `deployment.yaml` pusiste `name: mariadb-password` (en singular), mientras que en el Kustomization pusiste `name: mariadb-passwords` (en plural).

Aquí tienes los ajustes necesarios para que todo encaje a la primera:

### 1. Kustomization.yaml (Afinado)

He ajustado el nombre para que coincida y he añadido el `namespace` a nivel global. Al poner el namespace aquí, Kustomize se asegura de que **todos** los recursos (PV, PVC, Deploy, etc.) se creen en el lugar correcto sin tener que editar cada archivo manualmente.


```yaml
apiVersion: kustomize.config.k8s.io/v1beta1
kind: Kustomization

namespace: mariadb

secretGenerator:
- name: mariadb-password  # Eliminada la 's' para que coincida con tu Deploy
  literals:
    - mariadb-root-password=margarit4
    - mariadb-password=julian4

resources:
- 01-ns-mariadb.yaml
- 03-pv-mariadb.yaml
- 04-pvc-mariadb.yaml
- 05-deploy-mariadb.yaml
- 06-svc-mariadb.yaml
- 07-nodeport-mariadb.yaml
- 08-lb-mariadb.yaml
```

---

### 2. Ajustes finales en el Deployment (`05-deploy-mariadb.yaml`)

Asegúrate de que la sección de variables de entorno use exactamente estos nombres. Basado en el `kustomization` que pasaste, así debe quedar el bloque `env`:


```yaml
        env:
        - name: MARIADB_ROOT_PASSWORD
          valueFrom:
            secretKeyRef:
              name: mariadb-password
              key: mariadb-root-password
        - name: MARIADB_USER
          value: "admin" # O el usuario que prefieras
        - name: MARIADB_PASSWORD
          valueFrom:
            secretKeyRef:
              name: mariadb-password
              key: mariadb-password
        - name: MARIADB_DATABASE
          value: "appdb"
```

---

### Notas importantes para tu laboratorio:

- **Orden de ejecución:** Kustomize es inteligente. Aunque listes los archivos en cualquier orden, él sabe que primero debe aplicar el `Namespace`, luego los `Secrets/Volumes` y finalmente el `Deployment`.
    
- **PersistentVolume (03-pv):** Como estás usando un clúster con `k3s-master` específico, asegúrate de que el PV tenga definido el `nodeAffinity` o que el almacenamiento NFS esté accesible desde ese nodo.
    
- **Doble Service (NodePort y LB):** Veo que tienes archivos `07` y `08`. Si estás en un entorno local, el **LoadBalancer** (08) solo funcionará si tienes instalado algo como **MetalLB**. Si no, el **NodePort** (07) será tu mejor opción para acceder desde tu HP Victus.
    

**¿Cómo ejecutarlo?** Desde la carpeta donde tienes todos estos archivos: 

```bash
kubectl apply -k .
```


> [!Error]
> #### Durante la creacion del Pod, se presenta un error en el STATUS: 
> 
`pod/mariadb-db58fbfd9-hn9r8   0/1     CreateContainerConfigError   0          2m45s`


El error `CreateContainerConfigError` suele ser un problema de **referencias**. Kubernetes está intentando levantar el contenedor, pero no encuentra alguno de los recursos que definiste (probablemente el **Secret**).

Aquí están las causas más probables en tu caso y cómo solucionarlas:

### 1. El nombre del Secret no coincide

Kustomize añade un **hash** (un sufijo de números y letras) al final del nombre de los secretos por seguridad y para forzar el reinicio de los pods.

- **El problema:** Si en tu `05-deploy-mariadb.yaml` escribiste `name: mariadb-password`, pero Kustomize generó `mariadb-password-f76gh2`, el pod se quedará esperando el nombre exacto.
    
- **La solución:** En tu archivo `kustomization.yaml`, asegúrate de que el generador tenga el nombre correcto y deja que Kustomize se encargue de actualizar el Deployment automáticamente.
    

### 2. Error en las llaves (Keys)

Verifica que las llaves dentro del `secretGenerator` coincidan exactamente con lo que pide el Deployment:

- **En Kustomization:**
    
    ```yaml
    literals:
      - mariadb-root-password=<CLAVE-AQUI> # Esta es la KEY
    ```
    
- **En Deployment:**
    
    ```yaml
    key: mariadb-root-password # Debe ser idéntica
    ```

---

### 3. Cómo diagnosticarlo (Comandos rápidos)

Ejecuta estos comandos en tu terminal para ver el error real:

1. **Ver el detalle del error:**
    
    
    ```bash
    kubectl describe pod mariadb-XXXXXXXX-YYYY -n mariadb
    ```
    
    Busca al final en la sección de **Events**. Verás un mensaje como: `secret "mariadb-password" not found`.
    
2. **Ver los secretos generados:**
    
    
    ```bash
    kubectl get secrets -n mariadb
    ```
    
    Si ves algo como `mariadb-password-bc8hgh`, es que Kustomize está aplicando el sufijo y el Deployment no se enteró (o no aplicaste el `kustomization.yaml` completo con `kubectl apply -k .`).
    

### 4. Posible conflicto con el `namespace`

Si creaste el secreto manualmente en el namespace `default` pero el pod está en el namespace `mariadb`, el pod jamás podrá leerlo.

> **Tip:** Asegúrate de que tanto el secreto como el pod vivan en el mismo namespace. Al usar `namespace: mariadb` en el `kustomization.yaml`, todo debería ir al mismo sitio.

**¿Qué te dice el `kubectl describe pod` en la sección de eventos?** Eso nos dará la respuesta definitiva.


## Puedo eliminar todo el deployment y comenzar de nuevo?

 **Es decir, aplicando directamente a la carpeta de `kustomization.yaml` **

Sí, exactamente. Una de las grandes ventajas de usar **Kustomize** es que puedes gestionar el ciclo de vida completo de la aplicación (creación y borrado) apuntando simplemente al directorio.

Para eliminar todos los recursos definidos en tu `kustomization.yaml` (Namespace, PV, PVC, Deployment, Services y el Secret generado), ejecuta:


```bash
kubectl delete -k .
```

### ¿Qué sucede tras bambalinas?

1. **Orden inverso:** Kubernetes es lo suficientemente inteligente para borrar los recursos en el orden correcto (primero el Deployment, luego los Services, y finalmente el Namespace).
    
2. **Limpieza total:** Al borrar el **Namespace**, se eliminará cualquier rastro de MariaDB que estuviera dentro de él.
    
3. **Persistencia:** Ten en cuenta que si tu **PersistentVolume (PV)** tiene una `reclaimPolicy: Retain`, aunque borres el recurso en Kubernetes, los archivos físicos en tu servidor Proxmox/NFS se mantendrán intactos.
    

---

### Mi recomendación para solucionar el error previo:

Dado que tuviste el `CreateContainerConfigError`, lo mejor es hacer una limpieza limpia y volver a subirlo todo para que Kustomize regenere las referencias:

1. **Limpia todo:**
    
    ```bash
    kubectl delete -k .
    ```
    
2. **Verifica que no queden restos:** (A veces el namespace tarda unos segundos en terminar de borrarse).
    
    ```bash
    kubectl get ns
    ```
    
3. **Vuelve a aplicar:**
    
    ```bash
    kubectl apply -k .
    ```
    
### Un pequeño detalle en tus archivos:

Revisa que en tu `05-deploy-mariadb.yaml`, el nombre del secret sea exactamente **el mismo** que definiste en el `kustomization.yaml`.

- Si en el kustomization pusiste: `name: mariadb-password`
    
- En el deploy debe decir: `name: mariadb-password` (Kustomize se encargará de añadirle los hashes automáticos a ambos por ti).


> [!NOTE]
> El error `CreateContainerConfigError` en este punto (usando Kustomize) casi siempre se debe a que el **Deployment** está buscando un nombre de secreto que no existe exactamente igual en el clúster.

Cópia aquí el contenido de estos dos archivos para que pueda encontrar el fallo:

1. **`05-deploy-mariadb.yaml`**: Necesito ver cómo estás llamando al secreto en la sección `env`.
    
2. **`kustomization.yaml`**: Para ver cómo estás generando ese secreto.
    

### ¿Qué es lo que probablemente está pasando?

Cuando usas `secretGenerator` en Kustomize, ocurre lo siguiente:

- Si en el `kustomization.yaml` pones `name: mariadb-password`, Kustomize crea algo llamado `mariadb-password-h8g27f` (con un hash al final).
    
- **Si todo está bien configurado**, Kustomize busca en tu `05-deploy-mariadb.yaml` y cambia automáticamente la referencia para que coincida con el hash.
    
- Si hay una pequeña diferencia en el nombre (por ejemplo, una "s" de más o un guion distinto), Kustomize **no encuentra** la referencia, no la actualiza, y el Pod intenta buscar un secreto sin hash que no existe.
    

**Comando de diagnóstico rápido:** Mientras me pasas los archivos, puedes ejecutar esto en tu terminal:


```bash
kubectl describe pod -n mariadb | grep Error
```

Normalmente ahí verás una línea que dice algo como: `Error: secret "mariadb-passwords" not found` (o el nombre que esté fallando).

```bash
 kubectl describe pod -n mariadb | grep Error
    23:	     Reason:       CreateContainerConfigError
    69:	 Warning  Failed     8s (x11 over 115s)  kubelet            spec.containers{mariadb}: Error: secret "mariadb-password" not found
```

¡Bingo! Ahí lo tenemos. El error es claro: **`secret "mariadb-password" not found`**.

Esto sucede porque **Kustomize** le añade un sufijo (un hash como `-f76gh2`) al nombre del secreto para gestionar versiones, pero por alguna razón la referencia en tu Deployment no se actualizó automáticamente para incluir ese hash.

Para solucionarlo, asegúrate de que tus archivos se vean **exactamente** así:

### 1. Revisa tu `kustomization.yaml`

Asegúrate de que el nombre sea exactamente `mariadb-password`.


```yaml
# kustomization.yaml
apiVersion: kustomize.config.k8s.io/v1beta1
kind: Kustomization

namespace: mariadb

secretGenerator:
- name: mariadb-password  # <--- Este nombre es la clave
  literals:
    - mariadb-root-password=<PASSWORD1>
    - mariadb-password=<PASSWORD2>

resources:
- 01-ns-mariadb.yaml
- 03-pv-mariadb.yaml
- 04-pvc-mariadb.yaml
- 05-deploy-mariadb.yaml
- 06-svc-mariadb.yaml
- 07-nodeport-mariadb.yaml
- 08-lb-mariadb.yaml
```

### 2. Revisa tu `05-deploy-mariadb.yaml`

Verifica que las referencias apunten al mismo nombre. **Importante:** No le pongas el hash manualmente aquí, Kustomize lo hará por ti al aplicar.


```yaml
# Fragmento de 05-deploy-mariadb.yaml
        env:
        - name: MARIADB_ROOT_PASSWORD
          valueFrom:
            secretKeyRef:
              name: mariadb-password # <--- Debe coincidir con el
              #                       nombre en kustomization.yaml
              key: mariadb-root-password
        - name: MARIADB_PASSWORD
          valueFrom:
            secretKeyRef:
              name: mariadb-password # <--- Debe coincidir con el
              #                    nombre en kustomization.yaml
              key: mariadb-password
```

---

### ¿Por qué falló si los nombres parecen iguales?

Si los nombres coinciden y aun así da error, puede ser por dos motivos:

1. **El archivo del Deployment no está en `resources`:** Revisa que `05-deploy-mariadb.yaml` esté bien escrito en la lista de recursos del `kustomization.yaml`. Si Kustomize no lo "ve" en la lista, no puede entrar al archivo y parchear el nombre del secreto con el hash.
    
2. **Diferencia de Namespace:** Si el Deployment tiene un `namespace: mariadb` escrito dentro del archivo, pero el `kustomization.yaml` no lo tiene (o viceversa), a veces la asociación falla.
    

### El "Fix" definitivo:

1. **Borra todo** para limpiar el estado actual:
    
    ```bash
    kubectl delete -k .
    ```
    
2. **Verifica los nombres** en ambos archivos (que no tengan espacios extra al final ni errores de dedo).
    
3. **Aplica de nuevo**:
    
    
    ```bash
    kubectl apply -k .
    ```
    

> [!NOTE]
> Si quieres estar 100% seguro de qué nombre está generando Kustomize, puedes correr este comando (no aplica nada, solo te muestra el resultado en pantalla): `kubectl kustomize . | grep -A 5 "kind: Secret"`
> 
> Ahí verás el nombre real (con el hash) que Kubernetes está intentando usar.


## Quiero mirar los logs de MariaDB

Para ver los logs de MariaDB en Kubernetes, tienes un par de opciones dependiendo de qué tan profundo quieras investigar (si los logs del contenedor o los eventos del sistema).

Aquí tienes los comandos ordenados por utilidad:

### 1. Ver los logs del contenedor (Lo más común)

Como tu pod tiene un nombre dinámico (termina en `-hn9r8`), lo más fácil es usar el nombre del **Deployment** o un selector para no tener que escribir todo el ID:


```bash
# Ver los logs actuales
kubectl logs -l app=mariadb -n mariadb

# Seguir los logs en tiempo real (streaming)
kubectl logs -f -l app=mariadb -n mariadb
```

Si prefieres usar el nombre completo del pod que mencionaste antes:

```bash
kubectl logs mariadb-db58fbfd9-hn9r8 -n mariadb
```

---

### 2. Ver por qué no arranca (Si sigue en Error)

Si el pod sigue mostrando `CreateContainerConfigError`, los logs del contenedor estarán vacíos porque **el contenedor ni siquiera ha podido iniciar**. En ese caso, lo que necesitas es ver los eventos del pod:


```bash
kubectl describe pod -l app=mariadb -n mariadb
```

> **Qué buscar:** Ve al final de todo, a la sección **Events**. Ahí verás exactamente qué recurso le falta para arrancar.

---

### 3. Ver los logs de errores internos de MariaDB

Si el pod ya está corriendo (`Running`), pero la base de datos se comporta raro, puedes entrar al pod y leer los archivos de log directamente:


```bash
# Entrar a la terminal del pod
kubectl exec -it -n mariadb deploy/mariadb -- bash
```

#### Una vez dentro, mira el log de errores (el path depende de la versión)

```bash
cat /var/lib/mysql/error.log
```

**O simplemente mirar la salida estándar**

```bash
tail -f /var/log/mysql/error.log
```

---

### Tip para tu flujo en la terminal

En la terminal de **Kitty **o  **Alacritty**, puedes dejar un panel pequeño en la parte inferior ejecutando el comando con `-f` (follow) mientras ajustas tus archivos YAML en el panel principal:

```bash
watch -n 1 kubectl get pods -n mariadb
# O
kubectl logs -f -l app=mariadb -n mariadb
```


## Ahora cambio al STATUS a: `CrashLoopBackOff

Durante un momento estuvo mostrando el **STATUS: Running**... se observa nuevos archivos y directorio en el volumen de persistencia de MariaDB.


> [!success]
> Este es un progreso excelente. Si pasó a `Running` y generó archivos en el volumen, significa que el **Secret** y el **PVC** ya están funcionando correctamente.

El estado `CrashLoopBackOff` ahora indica que el proceso de MariaDB intentó arrancar pero se cerró por un error interno de configuración o de la base de datos.

Aquí te explico cómo encontrar la causa exacta y las razones más probables:

### 1. Revisa los Logs (La respuesta definitiva)

Ejecuta esto para ver por qué se detuvo el proceso:


```bash
kubectl logs -n mariadb -l app=mariadb --previous
```

> **Nota:** El flag `--previous` es clave aquí, porque te permite ver los logs del contenedor que acaba de "morir".


### 2. Causas probables (Basado en tu manifiesto)

#### A. El conflicto de las "Probes" (Causa más probable)

En tu manifiesto anterior tenías comandos de PostgreSQL (`pg_isready`). Si no los cambiaste por los de MariaDB, Kubernetes cree que la base de datos está fallando y reinicia el pod constantemente, entrando en el bucle de "Crash".

> [!NOTE]
> **Solución:** Asegúrate de que las `livenessProbe` y `readinessProbe` usen `mysqladmin ping` o simplemente desactívalas temporalmente para probar.


#### B. Permisos en el volumen (/var/lib/mysql)

Como estás usando un montaje de **Proxmox/NFS** y definiste un `securityContext` con `runAsUser: 1001`:

- Si los archivos que se crearon en el volumen pertenecen al usuario `root` o a otro UID, MariaDB no puede leer sus propios archivos de configuración y se cierra.
    
- **Prueba:** Intenta comentar las líneas de `runAsUser` y `runAsNonRoot` en tu `05-deploy-mariadb.yaml` para ver si es un problema de permisos.
    

#### C. Inconsistencia de Datos (Re-inicialización)

Si cambiaste la contraseña en el Secret pero el volumen ya tenía datos creados con la contraseña vieja (`PASSWORD1`), MariaDB a veces se confunde al intentar validar el usuario root existente con la nueva configuración del entorno.

---

### 3. Qué buscar en los logs

Cuando hagas el `kubectl logs`, busca líneas que digan:

- `[ERROR] InnoDB: ...` (Problemas de disco o archivos corruptos).
    
- `[ERROR] Access denied for user 'root'@'localhost'` (Problemas con la contraseña del Secret).
    
- `Permission denied` (Problemas con el `securityContext` y tu almacenamiento).
    

### Mi recomendación inmediata:

> [!NOTE]
> Para estabilizarlo mientras depuras, modifica tu deployment y **elimina temporalmente** las secciones de `livenessProbe` y `readinessProbe`. Si el pod se queda en `Running`, entonces el problema era el comando de chequeo.

¿Qué te dice el comando `kubectl logs -n mariadb -l app=mariadb --previous`?

## Cual es ID de usuario y grupo que debe tener el volumen de almacenamiento


Para la imagen oficial de MariaDB, el ID de usuario (UID) y el ID de grupo (GID) estándar es **999**.

En tu manifiesto anterior tenías configurado el `1001`, lo cual es común en imágenes de Bitnami, pero para la imagen oficial que estás usando (`mariadb:11.4` o similar), suele causar el error de permisos que provoca el `CrashLoopBackOff`.

### Configuración Correcta del `securityContext`

Ajusta tu archivo `05-deploy-mariadb.yaml` con estos valores para que coincidan con el dueño de los archivos internos del contenedor:


```yaml
spec:
  template:
    spec:
      securityContext:
        # El fsGroup 999 asegura que Kubernetes cambie los permisos 
        # del volumen montado para que el grupo de MariaDB pueda escribir.
        fsGroup: 999
      containers:
      - name: mariadb
        image: mariadb:11.4
        securityContext:
          # Forzamos a que el contenedor corra con el usuario oficial
          runAsUser: 999
          runAsGroup: 999
          runAsNonRoot: true
```

---

### El problema con NFS y Proxmox

Como estás usando un montaje **NFS** desde tu host Proxmox, hay un detalle técnico importante: NFS es muy estricto con los permisos del lado del servidor.

1. **En tu servidor Proxmox/NFS:** Debes asegurarte de que la carpeta física que estás compartiendo tenga los permisos adecuados. Si no quieres complicaciones en tu laboratorio, puedes aplicar un `chown` temporal en el servidor NFS:
    
    
    **Ejecuta esto en el servidor donde está el disco físico**
    
    ```bash
        chown -R 999:999 /ruta/de/tu/almacenamiento/mariadb
    ```
    
2. **Opciones de montaje:** Si no quieres cambiar el dueño en el servidor, asegúrate de que tu `exports` de NFS tenga la opción `no_root_squash` o `all_squash` con el `anonuid=999`.
    

### ¿Cómo verificarlo ahora?

Si los logs te muestran errores como `mysqld: Can't create/write to file... (Errcode: 13 "Permission denied")`, es definitivamente esto.

**Pasos para solucionar el CrashLoop:**

1. Actualiza el `securityContext` a **999**.
    
2. Si puedes, borra el contenido de la carpeta en tu volumen de persistencia para que MariaDB intente una "instalación limpia" con los nuevos permisos.
    
3. Aplica los cambios: `kubectl apply -k .`
    

> [!NOTE]
> ¿Qué mensaje te arroja ahora el `kubectl logs -n mariadb -l app=mariadb`? 
> 
> Si ves algo sobre "**Ready for connections**", habremos ganado la batalla.

## ID 999 en Debian y Ubuntu Server

> [!Warning]
> Pero tenemos un problema. Tanto Debian  asi como Ubuntu server, ya tiene registrado el ID `999` para  `systemd-journal.`
> 
Sera necesario crear el usuario mariadb y su grupo.?

Tienes toda la razón en ser precavido. En muchas distribuciones modernas basadas en Debian/Ubuntu, el UID **999** está reservado efectivamente para `systemd-journal` o `systemd-coredump`.

Si intentas forzar el UID **999** en el pod y el sistema operativo del nodo (o la imagen) ya lo tiene ocupado por un servicio del sistema, tendrás conflictos de permisos y errores de seguridad.

Para solucionar esto de forma profesional en tu entorno de **DevOps**, tienes dos caminos:

### Opción 1: Usar un UID en el rango de "Usuarios" (Recomendado)

En lugar de pelear por el 999, puedes usar un ID que esté libre tanto en el host como en la imagen, por ejemplo, el **1001** (que ya tenías) o cualquier otro por encima de 1000.

Para que MariaDB acepte esto, simplemente configuramos el `securityContext` para que Kubernetes "fuerce" la propiedad del volumen al arrancar:


```yaml
# En 05-deploy-mariadb.yaml
spec:
  template:
    spec:
      securityContext:
        runAsUser: 1005  # Un ID alto que no choque con el sistema
        runAsGroup: 1005
        fsGroup: 1005    # ESTA ES LA CLAVE: Kubernetes hará un 'chown' automático del volumen al 1005
      containers:
      - name: mariadb
        image: mariadb:11.4
        # ... resto de la config
```

### Opción 2: Crear el usuario en el contenedor (Vía Dockerfile)

Si quieres mantener la coherencia absoluta, podrías construir tu propia imagen de MariaDB que ya incluya el usuario con un ID específico. Pero esto añade complejidad a tu despliegue.

### El problema real: Tu almacenamiento NFS

Como usas NFS en Proxmox, el "mapeo" de usuarios es lo que te está dando el `CrashLoopBackOff`. NFS no sabe quién es el usuario "mariadb" de dentro del contenedor; solo ve un número (UID).

**La solución más limpia para tu homelab:**

1. **En tu servidor NFS (Proxmox/Ubuntu):** Crea un grupo y usuario específico con un ID que sepas que está libre (ej. 1005).
    
    
    ```bash
    sudo groupadd -g 1005 mariadb_hd
    sudo useradd -u 1005 -g 1005 -m -s /bin/false mariadb_hd
    ```
    
2. **Ajusta los permisos de la carpeta física:**
    
    ```bash
    sudo chown -R 1005:1005 /ruta/a/tu/nfs/mariadb
    sudo chmod -R 775 /ruta/a/tu/nfs/mariadb
    ```
    
3. **En tu Manifiesto (05-deploy-mariadb.yaml):** Usa el ID **1005** en el `securityContext`.
    

Al hacer esto, cuando el contenedor de MariaDB arranque, intentará escribir como el UID **1005**. Como la carpeta en el NFS ya le pertenece al UID **1005**, la base de datos podrá inicializarse sin errores de "Permission Denied".

### ¿Por qué esto es mejor?

- **Evitas conflictos:** No tocas los IDs reservados del sistema (`systemd-journal`).
    
- **Seguridad:** Sigues cumpliendo con `runAsNonRoot: true`.
    
- **Portabilidad:** Si mañana mueves tu laboratorio a otros nodos, mientras el UID coincida en el almacenamiento, funcionará.
    

¿Qué UID tienes libre actualmente en tu servidor Ubuntu que podamos asignar para esto? Si el **1001** está libre, puedes volver a él, pero asegúrate de hacer el `chown` en la carpeta del host primero.

## Se presenta nuevamente un error con **STATUS**:  `CrashLoopBackOff`.  Cuales podrian ser la causa?


Si ya corregimos el tema de los permisos del volumen y el pod sigue en `CrashLoopBackOff`, el problema ha pasado de ser un error de "infraestructura" a un error de **"configuración de la aplicación"**.

Aquí tienes las 3 causas más probables en este escenario:

### 1. El error de las Probes (La causa #1)

Si no actualizaste las `livenessProbe` y `readinessProbe` en tu archivo `05-deploy-mariadb.yaml`, Kubernetes está intentando ejecutar `pg_isready` (el comando de Postgres) dentro de MariaDB.

- **Resultado:** El comando falla, Kubernetes cree que la base de datos está muerta y mata el contenedor. Como esto pasa cada 30 segundos, entra en `CrashLoop`.
    
- **Solución:** Comenta toda la sección de `livenessProbe` y `readinessProbe` para probar si el pod se queda estable.
    
### 2. Inconsistencia de Datos en el Volumen

Si el volumen NFS ya tiene archivos de una ejecución anterior donde la contraseña era distinta o el ID de usuario era otro, MariaDB se niega a arrancar para evitar corromper los datos.

- **Qué buscar en los logs:** `[ERROR] [MY-011087] [Server] Different lower_case_table_names settings` o errores de `InnoDB`.
    
- **Solución:** Limpia la carpeta física en tu servidor NFS por completo para que MariaDB realice el `mysql_install_db` desde cero.
    

### 3. Falta de memoria (OOMKill)

MariaDB es exigente con la memoria al arrancar. Si los límites que pusimos (`limits: memory: 1Gi`) son muy ajustados para la versión que estás usando, el nodo podría estar matando el proceso.

- **Comprobación:** Ejecuta `kubectl describe pod -n mariadb`. Si en la sección de "Last State" ves `Reason: OOMKilled`, necesitas subir los `limits`.
    

---

### Cómo obtener la respuesta definitiva

Para dejar de adivinar, necesitamos ver qué dijo MariaDB justo antes de morir. Ejecuta este comando exacto:


```bash
kubectl logs -n mariadb -l app=mariadb --previous
```

**Si los logs están vacíos, intenta esto:** A veces el contenedor muere tan rápido que no llega a escribir logs. Cambia temporalmente el comando de inicio en tu deployment para que el contenedor se mantenga "vivo" aunque la DB falle:


```yaml
# Solo para depurar en el container de MariaDB
command: ["sh", "-c", "sleep 3600"]
```

Luego entra con `kubectl exec`, intenta ejecutar `mariadbd` manualmente y mira el error que escupe en la consola.

---

### Diagnóstico de red y Services

Como tienes archivos para `NodePort` y `LoadBalancer`, asegúrate de que no haya un conflicto de puertos en el nodo `k3s-master`. Si algo más está usando el puerto **3306** en el host físico, el contenedor podría fallar al intentar bindear el puerto si usas `hostNetwork` (aunque no parece ser tu caso).

**¿Qué te muestra el log con el flag `--previous`?** Si ves algo que dice `[ERROR]`, cópialo aquí.

## Cual es el propósito de utilizar `'livenessProbe"` y `ReadinesProbe` ?


> [!NOTE]
> El uso de **Probes** (sondas) en Kubernetes es fundamental para que el clúster sea "autoconsciente". Sin ellas, Kubernetes solo sabe si el proceso del contenedor está encendido o apagado, pero no si la aplicación que corre dentro es realmente funcional.

Aquí te explico el propósito de cada una y por qué son vitales en tu laboratorio de MariaDB:

---

## 1. ReadinessProbe (¿Está listo?)

El propósito de la **ReadinessProbe** es determinar si el contenedor está preparado para **recibir tráfico**.

- **Qué hace:** Si la sonda falla, Kubernetes **elimina la IP del Pod de los Services** (como tu LoadBalancer o NodePort). El Pod sigue vivo, pero nadie puede conectarse a él.
    
- **Por qué es importante en MariaDB:** Una base de datos tarda unos segundos en inicializar el motor de almacenamiento y cargar tablas. Si intentas conectarte apenas el contenedor dice "Running", la conexión fallará. La sonda asegura que solo se envíen peticiones cuando MariaDB esté lista para responder.
    
- **Consecuencia de fallo:** El Service deja de enviar tráfico al Pod.
    

---

## 2. LivenessProbe (¿Está vivo?)

El propósito de la **LivenessProbe** es determinar si el contenedor está en un estado saludable o si se ha quedado **"congelado" (deadlock)**.

- **Qué hace:** Si la sonda falla, Kubernetes **mata el contenedor y lo reinicia** automáticamente.
    
- **Por qué es importante en MariaDB:** Si el proceso de MariaDB se bloquea por un error crítico de memoria o un hilo colgado, el contenedor seguirá apareciendo como `Running`, pero no servirá de nada. La sonda detecta que no responde y lo reinicia para intentar recuperar el servicio.
    
- **Consecuencia de fallo:** Reinicio del contenedor (incrementa el contador de `RESTARTS`).
    

---

## Diferencias Clave

|Característica|ReadinessProbe|LivenessProbe|
|---|---|---|
|**Acción al fallar**|Aísla el Pod (lo quita del Service).|Reinicia el Pod (lo mata y lo crea).|
|**Objetivo**|Evitar enviar tráfico a un Pod que aún carga.|Recuperar un Pod que se ha bloqueado.|
|**Estado típico**|Útil durante el inicio y picos de carga.|Útil durante toda la vida del Pod.|

---

## El peligro en tu configuración actual

Como mencionamos antes, el motivo de tu **CrashLoopBackOff** es probablemente que configuraste una sonda mal.

> **Escenario de error:** Si pones una `LivenessProbe` que ejecuta un comando que no existe (como `pg_isready` en MariaDB), la sonda fallará siempre. Kubernetes pensará: _"El Pod no responde, voy a reiniciarlo"_. Lo reinicia, vuelve a fallar la sonda, y lo vuelve a reiniciar. Esto crea el bucle infinito de caídas.

### Ejemplo correcto para MariaDB:

Para que funcione, debes usar herramientas que MariaDB entienda, como `mysqladmin`:


```yaml
livenessProbe:
  exec:
    command: ["mysqladmin", "ping", "-h", "localhost"]
  initialDelaySeconds: 30 # Dale tiempo a la DB para arrancar antes de empezar a vigilar
  periodSeconds: 10
```

**¿Quieres que te ayude a integrar estas sondas correctamente en tu archivo `05-deploy-mariadb.yaml` para que dejen de causar el reinicio constante?**


## Se puede crear manifiesto solamente para los Probes?

Trozo de declaración comentada **removida** del deployment.

```yaml
  # 3. Corrección de Probes (Usando comandos de mysqladmin)
        livenessProbe:
          exec:
            command:
            - "bash"
            - "-c"
            - "mysqladmin ping -u root -p${MARIADB_ROOT_PASSWORD}"
          initialDelaySeconds: 30
          periodSeconds: 10
          timeoutSeconds: 5
        readinessProbe:
          exec:
            command:
            - "bash"
            - "-c"
            - "mysqladmin ping -u root -p${MARIADB_ROOT_PASSWORD}"
          initialDelaySeconds: 5
          periodSeconds: 5
          timeoutSeconds: 3
```

> [!success]
> 
> Ya que he eliminado este extracto del manifiesto del deployment y se ha logrado hacer funcionar MariaDB.  Ahora se observa el **STATUS** `Running`.

Si al eliminar ese extracto el pod finalmente se quedó en **Running**, confirmamos que Kubernetes estaba "matando" a MariaDB porque la sonda (**Probes**) le daba una respuesta falsa.

Sí, se puede (y se debe) crear un manifiesto para las sondas, pero adaptado a MariaDB. Aquí tienes el extracto corregido para que lo reintegres en tu `05-deploy-mariadb.yaml`.

### El extracto corregido para MariaDB

He ajustado los comandos para que usen `mariadb-admin` (el sucesor de mysqladmin), que es el estándar en las imágenes actuales, y he configurado los tiempos para que sean más tolerantes durante el arranque en tu servidor k3s.


```yaml
        livenessProbe:
          exec:
            command:
            - mariadb-admin
            - ping
            - "-h"
            - localhost
          initialDelaySeconds: 30  # Tiempo para que la DB inicialice archivos en el NFS
          periodSeconds: 20       # No satures la DB con chequeos constantes
          timeoutSeconds: 5
        readinessProbe:
          exec:
            command:
            - mariadb-admin
            - ping
            - "-h"
            - localhost
          initialDelaySeconds: 15
          periodSeconds: 10
          timeoutSeconds: 5
```

---

### ¿Por qué este sí va a funcionar?

1. **Comando Nativo:** `mariadb-admin ping` simplemente pregunta "¿estás ahí?". Si MariaDB responde, la sonda tiene éxito. No requiere contraseña si el socket local está configurado correctamente, o puedes usar la variable de entorno de root si fuera necesario.
    
2. **Tiempos de espera (InitialDelay):** Como tu almacenamiento es **NFS**, la primera vez que MariaDB arranca tiene que crear muchos archivos. Si la sonda empieza a preguntar demasiado pronto, fallará porque la DB está ocupada "escribiendo su casa". 30 segundos le da un margen seguro.
    
3. **Uso de Memoria:** Al usar un comando ligero, no consumes recursos extra de tus límites (256Mi/1Gi).
    

### Cómo aplicarlo ahora que funciona:

1. Abre tu `05-deploy-mariadb.yaml`.
    
2. Pega este bloque dentro de la sección `containers:`.
    
3. Aplica el cambio con `kubectl apply -k .`.
    

**Un detalle técnico:** Al aplicar esto, el pod se reiniciará una vez para cargar la nueva configuración. No te asustes si ves un `Terminating` y un `ContainerCreating`; es el comportamiento normal de Kubernetes al actualizar un Deployment.


## Se puede crear un manifiesto separado con estas dos funciones?

Técnicamente, **no se pueden separar** las sondas (`Liveness` y `Readiness`) en un archivo de manifiesto independiente (como un `.yaml` aparte).

La razón es que las sondas son **propiedades intrínsecas del contenedor**, al igual que la imagen o los puertos. Kubernetes necesita saber qué comando ejecutar _dentro_ del contenedor específico para monitorearlo, por lo que deben vivir obligatoriamente bajo la etiqueta `spec.containers` en tu `05-deploy-mariadb.yaml`.

Sin embargo, en **DevOps** hay dos formas de lograr esa "separación" o limpieza que buscas para no ensuciar el despliegue principal:

---

### 1. Usar un "Patch" en Kustomize (La opción más limpia)

Ya que estás usando **Kustomize**, esta es la mejor solución. Puedes tener tu `05-deploy-mariadb.yaml` muy simple y crear un archivo de "parche" que le inyecte las sondas solo cuando las necesites.

**Crea un archivo llamado `patch-probes.yaml`:**


```yaml
apiVersion: apps/v1
kind: Deployment
metadata:
  name: mariadb
spec:
  template:
    spec:
      containers:
      - name: mariadb
        livenessProbe:
          exec:
            command: ["mariadb-admin", "ping", "-h", "localhost"]
          initialDelaySeconds: 30
        readinessProbe:
          exec:
            command: ["mariadb-admin", "ping", "-h", "localhost"]
          initialDelaySeconds: 15
```

**Luego, agrégalo a tu `kustomization.yaml`:**


```yaml
# kustomization.yaml
resources:
- 05-deploy-mariadb.yaml
# ... tus otros archivos

patches:
- path: patch-probes.yaml
```

_Esto mantendrá tu archivo de despliegue original limpio y las funciones de monitoreo en un archivo separado._

---

### 2. Definir las sondas mediante un script (Ideal para lógica compleja)

Si lo que quieres es que el comando sea externo, puedes crear un **ConfigMap** que contenga un script de bash con la lógica de salud y simplemente llamar a ese script desde el manifiesto.

**Manifiesto del ConfigMap (`02-config-mariadb.yaml`):**


```yaml
apiVersion: v1
kind: ConfigMap
metadata:
  name: mariadb-healthcheck
data:
  check.sh: |
    #!/bin/bash
    mariadb-admin ping -h localhost
```

**En tu Deployment solo pones una línea:**

```yaml
        livenessProbe:
          exec:
            command: ["/bin/bash", "/scripts/check.sh"]
```

---

### Mi recomendación para tu Homelab

Si estás aprendiendo y quieres mantener el control visual de todo, te sugiero la **Opción 1 (Kustomize Patches)**.

Esto te permite:

1. Tener un archivo base que sabes que funciona (el que tienes ahora en `Running`).
    
2. "Encender" o "apagar" las sondas simplemente comentando una línea en el `kustomization.yaml`.
    
3. Evitar errores de indentación en el archivo principal, que suele ser lo más frustrante en YAML.
    



## El trozo de declaración omitida que permitió arrancar MariaDB

Analiza y compara con tu propuesta

```yaml
        # 3. Corrección de Probes (Usando comandos de mysqladmin)
        # livenessProbe:
        #   exec:
        #     command:
        #     - "bash"
        #     - "-c"
        #     - "mysqladmin ping -u root -p${MARIADB_ROOT_PASSWORD}"
        #   initialDelaySeconds: 30
        #   periodSeconds: 10
        #   timeoutSeconds: 5
        # readinessProbe:
        #   exec:
        #     command:
        #     - "bash"
        #     - "-c"
        #     - "mysqladmin ping -u root -p${MARIADB_ROOT_PASSWORD}"
        #   initialDelaySeconds: 5
        #   periodSeconds: 5
        #   timeoutSeconds: 3
```

Analizando el extracto que eliminaste, he identificado los **tres puntos críticos** que estaban causando el `CrashLoopBackOff`. Comparémoslo con la propuesta de usar un parche en Kustomize o una corrección directa:

### 1. El problema de la "ReadinessProbe" inicial (5 segundos)

- **En tu extracto:** `initialDelaySeconds: 5`.
    
- **El problema:** En un entorno con **NFS**, MariaDB tarda mucho más de 5 segundos en inicializar los archivos de base de datos la primera vez. La sonda empezaba a preguntar cuando la DB aún estaba "escribiendo su casa". Al fallar repetidamente desde el segundo 5, Kubernetes marcaba el Pod como no saludable antes de darle oportunidad de arrancar.
    

### 2. La dependencia de la Variable de Entorno

- **En tu extracto:** `-p${MARIADB_ROOT_PASSWORD}`.
    
- **El problema:** A veces, dentro del comando `exec`, las variables de entorno no se expanden correctamente si el shell no está bien inicializado, o el comando `mysqladmin` interpreta el password de forma literal. Si el comando devolvía un "Access Denied", la sonda fallaba y el ciclo de reinicio empezaba.
    

### 3. El uso de `bash -c`

- **Análisis:** Aunque no es incorrecto, añade una capa de complejidad. Si la imagen de MariaDB que usas es una versión minimalista (como las basadas en Alpine o versiones muy recortadas), a veces el path de `bash` es distinto o no está presente, haciendo que la sonda falle por "archivo no encontrado".
    

---

### Comparativa: Tu extracto vs. La nueva propuesta (Vía Patch)

Si decides usar el sistema de **Patches de Kustomize** para mantener tu `05-deploy-mariadb.yaml` limpio, así es como se vería la mejora:

|Característica|Tu extracto (Eliminado)|Propuesta con Patch (Mejorada)|
|---|---|---|
|**Comando**|`mysqladmin ping` con Password|`mariadb-admin ping -h localhost`|
|**Seguridad**|Expone pass en el comando|Usa el socket local (más seguro)|
|**Espera Inicial**|5 segundos (Muy agresivo)|30-45 segundos (Ideal para NFS)|
|**Ubicación**|Dentro del Deployment|Archivo `patch-probes.yaml` separado|

---

### Mi recomendación técnica

Para que tu despliegue sea verdaderamente "DevOps" y profesional, te sugiero implementar la **Opción del Patch**. Esto te permite tener "módulos":

1. **Crea el archivo `patch-probes.yaml`:**
    
    
    ```yaml
    apiVersion: apps/v1
    kind: Deployment
    metadata:
      name: mariadb
      namespace: mariadb
    spec:
      template:
        spec:
          containers:
          - name: mariadb
            livenessProbe:
              exec:
                command: ["mariadb-admin", "ping", "-h", "localhost"]
              initialDelaySeconds: 40 # Margen amplio para el
							            # servidor Proxmox
              periodSeconds: 20
            readinessProbe:
              exec:
                command: ["mariadb-admin", "ping", "-h", "localhost"]
              initialDelaySeconds: 20
              periodSeconds: 10
    ```
    
2. **Actívalo en tu `kustomization.yaml`:**
    
    
    ```yaml
    patches:
      - path: patch-probes.yaml
    ```
    

**¿Por qué esto es mejor?** Si mañana necesitas hacer mantenimiento en tu servidor NFS y el almacenamiento se vuelve lento, solo modificas el archivo de `patch-probes.yaml` sin tocar la lógica principal de tu base de datos. Además, el comando `mariadb-admin ping` sin password suele funcionar para conexiones desde `localhost` dentro del contenedor, evitando errores de autenticación en los chequeos de salud.

¿Te parece bien esta estructura de archivos para tu carpeta de proyecto?

## Como hubieras corregido el extracto para evitar los tres puntos críticos.?


Para corregir el extracto original y asegurar que sea estable en tu entorno (K3s + NFS + Proxmox), la clave es **eliminar la fragilidad**.

Aquí tienes cómo quedaría el código corregido directamente, atacando los tres puntos críticos (tiempos, autenticación y ejecución):

### El extracto corregido (Versión Robusta)


```yaml
        # 3. Sondas Corregidas: Optimizadas para NFS y estabilidad de Shell
        livenessProbe:
          exec:
            command:
            - "mariadb-admin"
            - "ping"
            - "-h"
            - "localhost"
          # PUNTO CRÍTICO 1: Aumentamos a 40s para dar tiempo al montaje NFS y creación de tablas
          initialDelaySeconds: 40 
          periodSeconds: 20
          timeoutSeconds: 5
        readinessProbe:
          exec:
            command:
            - "mariadb-admin"
            - "ping"
            - "-h"
            - "localhost"
          # PUNTO CRÍTICO 1: No preguntamos antes de los 20s para evitar reinicios prematuros
          initialDelaySeconds: 20
          periodSeconds: 10
          timeoutSeconds: 3
```

---

### ¿Por qué esta corrección evita los fallos?

#### 1. Adiós al Password en la Sonda (Punto Crítico 2)

En el extracto original usabas `-p${MARIADB_ROOT_PASSWORD}`.

- **La corrección:** Al usar `mariadb-admin ping -h localhost`, aprovechamos que, por defecto, MariaDB permite el "ping" desde el socket local o localhost sin requerir el password completo de root para un simple chequeo de salud. Esto evita que la sonda falle si la variable de entorno no se expande correctamente o si hay caracteres especiales en la clave que `bash` no interprete bien.
    

#### 2. Tiempos de "Cortesía" para NFS (Punto Crítico 1)

En el original, la `readinessProbe` empezaba a los 5 segundos.

- **La corrección:** Subimos a **20 y 40 segundos**. En tu homelab, el disco físico en Proxmox y la capa de red NFS añaden latencia. MariaDB debe inicializar el log de transacciones (`ib_logfile0`) y las tablas del sistema. Si la sonda pregunta a los 5 segundos, la DB responderá "estoy ocupada", la sonda fallará, y entrarás en el `CrashLoopBackOff` que tenías.
    

#### 3. Ejecución Directa vs Bash (Punto Crítico 3)

En el original usabas `bash -c "comando"`.

- **La corrección:** Pasamos el comando como una lista de argumentos: `["mariadb-admin", "ping", ...]`. Esto es más eficiente porque Kubernetes ejecuta el binario directamente sin necesidad de levantar un proceso de shell intermedio. Es más seguro y consume menos memoria (importante si tienes límites de 256Mi).
    

### Resumen de la estrategia

Si aplicas esta versión corregida, el flujo de tu pod será:

1. **0-20 segundos:** El pod está `ContainerCreating` o `Running`, pero el Service no le envía tráfico (Readiness está esperando).
    
2. **20-40 segundos:** La Readiness empieza a preguntar. En cuanto MariaDB termina de cargar los archivos del NFS, responde "OK". Tu base de datos ya es visible para tus otros pods.
    
3. **40 segundos en adelante:** La Liveness empieza a vigilar. Si la base de datos se bloquea totalmente por más de 1 minuto (3 intentos x 20s), Kubernetes la reiniciará por ti.
    

##  kubectl apply -f 09-patch-probes.yaml

```bash
The Deployment "mariadb" is invalid: 
* spec.selector: Required value
* spec.template.metadata.labels: Invalid value: null: `selector` does not match template `labels`
* spec.template.spec.containers[0].image: Required value
```

El error que estás viendo ocurre porque intentaste aplicar el parche con `kubectl apply -f`. Cuando usas `-f`, Kubernetes espera un archivo de **recurso completo** (con imagen, selectores, etiquetas, etc.), y tu archivo de parche es solo un fragmento.

> [!NOTE]
> Para que los parches funcionen, no se "aplican" por separado, sino que se integran a través de **Kustomize**.
> 

Aquí tienes la solución paso a paso para corregir esto y dejar tu estructura profesional:

### 1. El archivo del Parche (`09-patch-probes.yaml`)

Asegúrate de que el archivo tenga exactamente esta estructura. No necesita `labels` ni `image` porque Kustomize los tomará del archivo principal (`05-deploy-mariadb.yaml`).

```yaml
apiVersion: apps/v1
kind: Deployment
metadata:
  name: mariadb
  namespace: mariadb
spec:
  template:
    spec:
      containers:
      - name: mariadb  # Este nombre DEBE coincidir con el del 05-deploy
        livenessProbe:
          exec:
            command: ["mariadb-admin", "ping", "-h", "localhost"]
          initialDelaySeconds: 40
          periodSeconds: 20
        readinessProbe:
          exec:
            command: ["mariadb-admin", "ping", "-h", "localhost"]
          initialDelaySeconds: 20
          periodSeconds: 10
```

### 2. Configura tu `kustomization.yaml`

Este es el archivo que "une" todo. Es aquí donde debes declarar el parche para que Kubernetes entienda que debe fusionar el archivo `09` con el `05`.


```yaml
# kustomization.yaml
apiVersion: kustomize.config.k8s.io/v1beta1
kind: Kustomization

namespace: mariadb

resources:
- 01-ns-mariadb.yaml
- 03-pv-mariadb.yaml
- 04-pvc-mariadb.yaml
- 05-deploy-mariadb.yaml
- 06-svc-mariadb.yaml
# ... tus otros archivos

# AQUÍ ES DONDE SUCEDE LA MAGIA
patches:
- path: 09-patch-probes.yaml
```

### 3. El comando correcto

Para desplegar usando la lógica de parches, **nunca** uses `apply -f` sobre el archivo del parche. Usa siempre la carpeta actual con el flag `-k`:


**Para previsualizar cómo queda la unión (muy útil para debug):

```bash
kubectl kustomize .
```

Genera un salida con todos los `resources` declarados en el `kustomization.yaml`.  Resulta útil para crear un único archivo YAML con la configuración necesario para el deployment de una aplicación. 

**Ejemplo:**

```bash
kubectl kustomize  > my-app.yaml
```

**Para aplicar todo el conjunto:**

```
kubectl apply -k ./kustomization/
```


### ¿Por qué te daba error con `apply -f`?

Kubernetes pensaba que estabas intentando crear un **nuevo** Deployment llamado "mariadb" que no tenía imagen ni selectores. Al usar `kubectl apply -k .`, Kustomize lee el `05-deploy-mariadb.yaml` (la base), luego lee el `09-patch-probes.yaml` (el parche), los fusiona en memoria y envía un único manifiesto completo y válido al clúster.

> [!Error]
> error: invalid Kustomization: json: cannot unmarshal string into Go struct field Kustomization.patches of type types.Patch

Este error es un clásico de **Kustomize** cuando hay un cambio de versión o de sintaxis. El mensaje "cannot unmarshal string" significa que Kustomize esperaba un objeto con formato específico y tú le pasaste una simple cadena de texto (el path del archivo).

En las versiones más recientes de Kustomize (que es la que probablemente tienes en tu **Fedora** o **Ubuntu Server**), la sintaxis de `patches` requiere que especifiques explícitamente que se trata de un `path`.

### La solución: Corrige tu `kustomization.yaml`

Cambia la sección de `patches` para que use esta estructura de objeto:


```yaml
# kustomization.yaml

# ... (tus recursos arriba)

patches:
  - path: 09-patch-probes.yaml
    target:
      kind: Deployment
      name: mariadb
```

---

### ¿Por qué falló la anterior?

Existen dos formas de declarar parches en Kustomize y a veces se confunden según la versión:

1. **Sintaxis Antigua/Simple (Legacy):**
    
    
    ```yaml
    patchesStrategicMerge:
      - 09-patch-probes.yaml
    ```
    
    _Si usas esta, solo pasas la lista de archivos._
    
2. **Sintaxis Moderna (Recomendada):**
    
    
    ```yaml
    patches:
      - path: 09-patch-probes.yaml
    ```
    
    _Aquí es donde ocurrió el error: si pones el guion y el nombre del archivo directamente debajo de `patches:`, Kustomize se confunde. Necesita la palabra clave `path:`._
    

### Paso a paso para arreglarlo:

1. **Edita tu `kustomization.yaml`** y asegúrate de que se vea exactamente así:
    
    
    ```yaml
    resources:
      - 05-deploy-mariadb.yaml
      # ... otros recursos
    
    patches:
      - path: 09-patch-probes.yaml
    ```
    
2. **Verifica la indentación:** YAML es muy sensible. Asegúrate de que `path:` tenga dos espacios de sangría respecto al guion `-`.
    
3. **Prueba de nuevo:**
    
    
    ```bash
    kubectl apply -k .
    ```
    

Si esto te sigue dando problemas de "unmarshal", intenta usar la clave antigua `patchesStrategicMerge:` en lugar de `patches:`, pero la versión con `path:` es la estándar actualmente.

---

## Por que se esta mostrando en en log:

```bash
2026-05-01 18:00:46 3 [Warning] Access denied for user 'root'@'localhost' (using password: NO)
2026-05-01 18:00:54 4 [Warning] Access denied for user 'root'@'localhost' (using password: NO)
2026-05-01 18:00:56 5 [Warning] Access denied for user 'root'@'localhost' (using password: NO)
```


Ese error en los logs es la prueba de que tus **probes** (sondas) ya están funcionando, pero están intentando entrar a la casa sin tocar el timbre correctamente.

El mensaje `(using password: NO)` significa que el comando `mariadb-admin ping` está intentando conectar con el usuario `root` pero no está enviando ninguna contraseña, y tu instancia de MariaDB está configurada (correctamente por seguridad) para no permitir acceso a root sin credenciales.

### ¿Por qué sucede esto si usamos `localhost`?

Aunque estemos dentro del contenedor, las imágenes oficiales de MariaDB a partir de ciertas versiones (y especialmente si no se usa el socket de Unix `/var/run/mysqld/mysqld.sock`) requieren autenticación para el usuario root incluso desde el propio host.

---

### Cómo corregirlo sin comprometer la seguridad

Tienes dos caminos para limpiar esos warnings de tus logs:

#### Opción A: Pasar la contraseña en la sonda (La más rápida)

Debes modificar tu archivo `09-patch-probes.yaml` para que el comando incluya la variable de entorno que ya tienes definida en tu Deployment.


```yaml
        livenessProbe:
          exec:
            command:
            - "bash"
            - "-c"
            - "mariadb-admin ping -h localhost -u root -p${MARIADB_ROOT_PASSWORD}"
```

_Nota: Al usar la variable `${MARIADB_ROOT_PASSWORD}`, necesitamos envolver el comando en `bash -c` para que Kubernetes pueda expandir el valor de la variable antes de ejecutarlo._

#### Opción B: Usar el archivo `.my.cnf` (La más limpia/DevOps)

Puedes crear un archivo de configuración en el home del usuario que corre MariaDB (el UID 1002 que configuramos) para que los comandos administrativos se autentiquen automáticamente de forma local.

1. Crea un **ConfigMap** con las credenciales.
    
2. Móntalo en el contenedor en `/home/mariadb/.my.cnf`.
    
3. La sonda funcionará con un simple `mariadb-admin ping` porque leerá el password del archivo.
    

---

### ¿Es grave este Warning?

**No es crítico, pero es molesto.**

- **Lo bueno:** El pod está `Running` porque, aunque MariaDB deniegue el acceso, el comando `mariadb-admin` suele devolver un código de salida que Kubernetes interpreta como "el proceso está ahí".
    
- **Lo malo:** Tus logs se van a llenar de basura rápidamente (como estás viendo), lo que dificulta encontrar errores reales en el futuro. Además, si la seguridad es muy estricta, la sonda podría empezar a fallar y causar un `CrashLoopBackOff` de nuevo.
    

### Mi recomendación

Para tu laboratorio actual, aplica la **Opción A** en tu parche. Es la forma más directa de decirle a la sonda: "Usa la misma llave que usaste para crear la base de datos".

¿Quieres que te pase el contenido completo del `09-patch-probes.yaml` con la corrección de `bash -c` para que solo tengas que copiar y pegar?

## Has mencionado que usar bash podria traer problemas


Tienes toda la razón en cuestionarlo. Mi advertencia anterior se refería a que usar `bash` añade una **capa extra de fragilidad** si no se hace con cuidado.

Aquí te explico la contradicción y cómo resolverla de la forma más elegante:

### El dilema del "Bash"

1. **Por qué es un riesgo:** Si el contenedor es muy ligero (distroless o alpine), `bash` podría no existir. Además, `bash -c` levanta un proceso extra; si ese proceso se queda colgado, la sonda podría fallar por timeout aunque la base de datos esté bien.
    
2. **Por qué lo sugerí ahora:** Porque es la única forma de que Kubernetes "entienda" qué significa `${MARIADB_ROOT_PASSWORD}`. Sin un shell (bash o sh), Kubernetes intenta pasar literalmente el texto `${MARIADB_ROOT_PASSWORD}` a MariaDB, y lógicamente la contraseña falla.
    

---

### La Solución "Pro" (Sin usar Bash)

Si quieres evitar `bash` y mantener la seguridad, la mejor práctica en Kubernetes es usar el **Unix Socket**. El socket es un "archivo" de comunicación interna. Por defecto, MariaDB confía en cualquier proceso que se conecte a través del socket desde dentro del mismo contenedor.

Modifica tu `09-patch-probes.yaml` para usar el socket en lugar de la red (`localhost`):


```yaml
        livenessProbe:
          exec:
            command:
            - "mariadb-admin"
            - "ping"
            - "--socket=/var/run/mysqld/mysqld.sock" # Conexión interna directa
          initialDelaySeconds: 40
          periodSeconds: 20
        readinessProbe:
          exec:
            command:
            - "mariadb-admin"
            - "ping"
            - "--socket=/var/run/mysqld/mysqld.sock"
          initialDelaySeconds: 20
          periodSeconds: 10
```

### ¿Por qué esto es mejor?

- **Sin Contraseñas:** El socket suele estar configurado para permitir `root` sin password siempre que el comando venga del mismo sistema (autenticación `unix_socket`).
    
- **Sin Bash:** No necesitas `bash -c` ni expansión de variables. Ejecutas el binario directamente.
    
- **Más Rápido:** La comunicación por socket es ligeramente más veloz que la de red (`localhost/127.0.0.1`) porque no pasa por la pila de protocolos TCP/IP.
    
- **Limpieza de Logs:** Adiós a los mensajes de `Access denied`.
    

### Una pequeña verificación

Para que esto funcione, asegúrate de que en tu archivo de configuración de MariaDB (si tienes uno personalizado) o en los parámetros de inicio, el socket se esté creando en esa ruta. En la imagen oficial, `/var/run/mysqld/mysqld.sock` es el estándar.

**¿Qué te parece este enfoque?** Es mucho más cercano a lo que verías en un entorno de producción real de DevOps.


## hasta que no reciba una respuesta de los probes, el pod no tomara el STATUS de Running.?

Esta es una distinción técnica muy importante en Kubernetes: **El Pod sí entrará en estado `Running`**, pero su condición de **`Ready`** será la que cambie.

Aquí te explico exactamente qué sucede paso a paso para que no te confundas al ver el comando `kubectl get pods`:

### 1. El ciclo de vida: `Running` vs `Ready`

- **STATUS `Running`**: Significa que el contenedor se ha iniciado correctamente, el proceso principal (MariaDB) ha arrancado y no se ha detenido. Kubernetes ya le asignó recursos y el nodo lo está ejecutando.
    
- **READY `1/1`**: Esto es lo que controlan las **probes**. Un Pod puede estar en `Running` pero mostrar `0/1` en la columna `READY`.
    

### 2. ¿Qué pasa mientras los Probes esperan?

Cuando aplicaste los `initialDelaySeconds: 20` (o 40), sucede lo siguiente:

1. **Segundos 0-20:** El Pod aparece como **`Running`**, pero en la columna `READY` verás **`0/1`**.
    
2. **Durante este tiempo:** Kubernetes **no** le envía tráfico. Si intentas conectarte a través del Service (NodePort o LoadBalancer), la conexión será rechazada o se quedará esperando.
    
3. **Primer chequeo exitoso:** En cuanto la `readinessProbe` recibe una respuesta positiva del socket de MariaDB, el Pod pasa a **`1/1 READY`**.
    
4. **Acción inmediata:** En ese microsegundo, Kubernetes añade la IP del Pod a los "Endpoints" del Service y tu base de datos ya es accesible para otras aplicaciones.
    

---

### 3. ¿Qué pasa si las Probes fallan después?

- **Si falla la `readinessProbe`:** El Pod sigue en `Running`, pero vuelve a `0/1`. El tráfico se corta. Esto es útil si MariaDB está bajo un proceso pesado (como un backup o un import enorme) y no puede atender clientes.
    
- **Si falla la `livenessProbe`:** Aquí es cuando el STATUS cambia. Kubernetes matará el contenedor y verás que el estado pasa a **`CrashLoopBackOff`** o simplemente verás que el contador de **`RESTARTS`** aumenta.
    

### Resumen para tu monitoreo:

Si ejecutas `kubectl get pods -w` (el flag `-w` es para observar cambios en tiempo real), verás esto:

1. `mariadb-xxx 0/1 ContainerCreating`
    
2. `mariadb-xxx 0/1 Running` <-- Aquí es donde actúan tus `initialDelaySeconds`.
    
3. `mariadb-xxx 1/1 Running` <-- ¡Aquí ya puedes conectar tu cliente SQL!
    

**En conclusión:** El Pod estará en `Running` casi de inmediato, pero hasta que las probes no den el "visto bueno", el sistema lo mantendrá aislado del resto de la red por seguridad.

¿Cómo se ve el estado de tu pod actualmente tras aplicar el cambio del socket?

## OK.. Ya tengo un STATUS de Running. Quisiera hacer una comprobacion, entrando a la consola de MariaDB


Excelente! Ahora que tienes el pod en **Running** y con el **Ready 1/1**, significa que las sondas confirmaron que el motor de base de datos está listo para recibir órdenes.

Para entrar a la consola de MariaDB directamente desde tu terminal (usando el entorno que tienes configurado con el usuario `1002`), ejecuta el siguiente comando:


```bash
kubectl exec -it deployment/mariadb -n mariadb -- mariadb -u root -p
```

### ¿Qué está pasando en este comando?

1. **`kubectl exec -it`**: Abre una sesión interactiva con una terminal (TTY).
    
2. **`deployment/mariadb`**: Kubernetes buscará el pod que pertenece a ese despliegue.
    
3. **`-n mariadb`**: Especifica tu namespace.
    
4. **`--`**: Separa los comandos de kubectl del comando que quieres ejecutar dentro del contenedor.
    
5. **`mariadb -u root -p`**: Llama al cliente de MariaDB pidiéndote la contraseña (que es la que definiste en tu variable de entorno).
    

---

### Comprobaciones rápidas una vez dentro

Una vez que veas el prompt `MariaDB [(none)]>`, puedes ejecutar estos comandos SQL para verificar que todo esté en orden:

- **Verificar la versión y el usuario actual:**
    
    
    ```sql
    SELECT VERSION(), USER();
    ```
    
- **Verificar que el almacenamiento es persistente:** Intenta crear una base de datos de prueba:
    
    
    ```sql
    CREATE DATABASE lab_test;
    SHOW DATABASES;
    ```
    
- **Verificar el socket y la conexión:**
    
    
    ```sql
    STATUS;
    ```
    

### Un detalle sobre tu flujo de trabajo

Como mencionaste que estás usando **Zellij**, podrías dejar una ventana dedicada a los logs en un panel y esta consola en otro. Si al intentar entrar recibes un error de "Permission Denied", significaría que el socket todavía tiene alguna restricción de permisos, pero si las sondas (`probes`) están en verde, esto debería funcionar a la primera.

**¿Lograste entrar y ver el prompt de MariaDB?** Si es así, ¡tu homelab ya tiene un motor de base de datos funcional y persistente!


## Cual es el equivalente para entrar a la consola de postgres?

Para entrar a la consola de PostgreSQL, el comando es muy similar, pero cambian el nombre del binario y la forma en que se maneja el usuario inicial.

Ejecuta el siguiente comando:


```bash
kubectl exec -it deployment/postgres -n postgres -- psql -U postgres
```

### Diferencias clave que debes notar:

1. **El binario:** En lugar de `mariadb` o `mysql`, el cliente de terminal se llama **`psql`**.
    
2. **El usuario:** Se usa la bandera **`-U`** (mayúscula) para especificar el usuario. En PostgreSQL, el superusuario por defecto casi siempre es `postgres`.
    
3. **Contraseña:** A diferencia de MariaDB, `psql` no suele pedir la contraseña interactivamente si la variable `POSTGRES_PASSWORD` está configurada en el entorno del contenedor o si la autenticación está en modo `trust` para conexiones locales. Si te la pide, simplemente ingrésala.
    
4. **Namespace:** Asegúrate de cambiar el namespace (`-n`) si tu despliegue de Postgres está en uno diferente al de MariaDB.
    

---

### Comandos útiles dentro de Postgres (`psql`)

Una vez dentro, notarás que el prompt cambia a `postgres=#`. Aquí los comandos no empiezan todos con SQL, sino con barras diagonales para la administración:

- **Listar bases de datos:** `\l`
    
- **Listar tablas:** `\dt`
    
- **Ver info de conexión:** `\conninfo`
    
- **Ayuda de comandos:** `\?`
    
- **Salir de la consola:** `\q` o escribe `exit`.
    

---

### Una nota sobre el usuario en Postgres

En el mundo DevOps, PostgreSQL es un poco más estricto con los permisos de los archivos de sistema. Si tu despliegue de Postgres también usa un **UID específico** (como el 1001 que mencionaste antes), `psql` funcionará sin problemas siempre que el usuario definido tenga permisos sobre el socket de Unix en `/var/run/postgresql/`.

¿Ya tienes el despliegue de Postgres corriendo en paralelo con el de MariaDB o estás por configurarlo?


# Versión final de los manifiestos

> [!success]
> A continuación los  manifiestos con los cuales se ha logrado un Deployment de  MariaDB en K3s,



```yaml
# kustomization.yaml
apiVersion: kustomize.config.k8s.io/v1beta1
kind: Kustomization

namespace: mariadb

# --- Aquí defines el secreto de forma manual  ---#
secretGenerator:
- name: mariadb-password
  literals:
    - mariadb-root-password=<PASSWORD1>
    - mariadb-password=<PASSWORD2>

# Aquí enlazas el archivo del deployment
resources:
- 01-ns-mariadb.yaml
#- 02_secret-mariadb.yaml   # <-- No aplica ya que se ha
#                                 declarado de manera manual.
- 03-pv-mariadb.yaml
- 04-pvc-mariadb.yaml
- 05-deploy-mariadb.yaml
#- 05-deploy-mariadb-probes.yaml
- 06-svc-mariadb.yaml
- 07-nodeport-mariadb.yaml
- 08-lb-mariadb.yaml
# patches:
#   - path: 09-patch-probes.yaml
#     target:
#       kind: Deployment
#       name: mariadb


```


```yaml
# 01-ns...
apiVersion: v1
kind: Namespace
metadata:
name: mariadb
```

```yaml
# 02-secret...
apiVersion: v1
kind: Secret
metadata:
name: mariadb-passwords
namespace: mariadb
type: Opaque
data:
# password: "admin123" en base64
POSTGRE
# password para usuario administrador
POSTGRES_ROOT_PASSWORD: YWRtaW4xMjM=
```

```yaml
# 03-pv...
apiVersion: v1
kind: PersistentVolume
metadata:
  name: mariadb-pv
  labels:
    type: local-mariadb
spec:
  storageClassName: local-path
  capacity:
    storage: 30Gi
  accessModes:
    - ReadWriteOnce
  persistentVolumeReclaimPolicy: Retain
  local:
    path: /mariadb-pv
  nodeAffinity:
    required:
      nodeSelectorTerms:
      - matchExpressions:
        - key: kubernetes.io/hostname
          operator: In
          values:
          - k3s-master

```

```yaml
# 04-pvc...
apiVersion: v1
kind: PersistentVolumeClaim
metadata:
  name: mariadb-pvc
  namespace: mariadb
spec:
  storageClassName: local-path
  accessModes:
    - ReadWriteOnce
#  volumeMode: Filesystem  
  resources:
    requests:
      storage: 30Gi

```

```yaml
# 05-deploy...
apiVersion: apps/v1
#kind: StatefulSet
kind: Deployment
metadata:
  name: mariadb
  namespace: mariadb
  labels:
    app: mariadb
spec:
  replicas: 1
  selector:
    matchLabels:
      app: mariadb
  template:
    metadata:
      labels:
        app: mariadb
    spec:
      nodeSelector:
        kubernetes.io/hostname: k3s-master
      # Se eliminó runAsUser: 1001 si usas la imagen oficial de MariaDB, 
      # ya que suele usar el usuario 999. Si tu volumen requiere 1001, mantenlo.
#      securityContext:
#        runAsUser: 1002
#        runAsGroup: 1002
#        fsGroup: 1002
      containers:
      - name: mariadb
        image: mariadb:11.4 # Nota: MariaDB 12 aún no es estable/común, 11.4 LTS actual
        imagePullPolicy: IfNotPresent
        ports:
        - containerPort: 3306
          name: mysql
        env:
        # 1. Contraseña Root desde Secret
        - name: MARIADB_ROOT_PASSWORD
          valueFrom:
            secretKeyRef:
              name: mariadb-password
              key: mariadb-root-password
        # 2. Configuración de DB y Usuario inicial
        - name: MARIADB_DATABASE
          value: "testdb"
        - name: MARIADB_USER
          value: "admin"
        - name: MARIADB_PASSWORD
          valueFrom:
            secretKeyRef:
              name: mariadb-password
              key: mariadb-password
        volumeMounts:
        - name: mariadb-storage
          mountPath: /var/lib/mysql # El path estándar de MariaDB es este
        resources:
          requests:
            memory: "512Mi"
            cpu: "250m"
          limits:
            memory: "1Gi"
            cpu: "500m"
        # livenessProbe:
        #   exec:
        #     command:
        #     - "bash"
        #     - "-c"
        #     - "mysqladmin ping -u root -p${MARIADB_ROOT_PASSWORD}"
        #   initialDelaySeconds: 30
        #   periodSeconds: 10
        #   timeoutSeconds: 5
        # readinessProbe:
        #   exec:
        #     command:
        #     - "bash"
        #     - "-c"
        #     - "mysqladmin ping -u root -p${MARIADB_ROOT_PASSWORD}"
        #   initialDelaySeconds: 5
        #   periodSeconds: 5
        #   timeoutSeconds: 3
      volumes:
      - name: mariadb-storage
        persistentVolumeClaim:
          claimName: mariadb-pvc

```

```yaml
# 06-svc..
apiVersion: v1
kind: Service
metadata:
  name: mariadb-service
  namespace: mariadb
  labels:
    app: mariadb
spec:
  type: ClusterIP
  ports:
  - port: 3306
    targetPort: 3306
    protocol: TCP
    name: mariadb
  selector:
    app: mariadb
```

```yaml
# 07-nodeport..
apiVersion: v1
kind: Service
metadata:
  name: mariadb-nodeport
  namespace: mariadb
  labels:
    app: mariadb
    type: external-access
spec:
  type: NodePort
  ports:
  - port: 3306
    targetPort: 3306
    nodePort: 32306
    protocol: TCP
    name: mariadb
  selector:
    app: mariadb

```


```yaml
# 08-lb...
apiVersion: v1
kind: Service
metadata:
  name: mariadb-lb
  namespace: mariadb
spec:
  type: LoadBalancer
  ports:
  - port: 3306
    targetPort: 3306
    protocol: TCP
  selector:
    app: postgres

```

```yaml
# 09-patch-probes..
apiVersion: apps/v1
kind: Deployment
metadata:
  name: mariadb
spec:
  template:
    spec:
      containers:
      - name: mariadb
        livenessProbe:
          exec:
            command: ["mariadb-admin",
             "ping",
              "-h",
               "10.0.0.100",
               "-p",
               "${MARIADB_ROOT_PASSWORD}"]
          initialDelaySeconds: 30
        readinessProbe:
          exec:
            command: ["mariadb-admin", "ping", "-h", "localhost", "-p","${MARIADB_ROOT_PASSWORD}"]
          initialDelaySeconds: 15
```


```bash
kubectl -k ./kustomization/
```

```bash
❯ ll kustomization 
.rw-r--r--@   59 datenmaniak 29 abr 18:51  01-ns-mariadb.yaml
.rw-r--r--@  252 datenmaniak 29 abr 19:29  02_secret-mariadb.yaml
.rw-r--r--@  457 datenmaniak  1 may 12:38  03-pv-mariadb.yaml
.rw-r--r--@  241 datenmaniak  1 may 12:36  04-pvc-mariadb.yaml
.rw-r--r--@ 2,3k datenmaniak  1 may 13:48  05-deploy-mariadb-probes.yaml
.rw-r--r--@ 2,3k datenmaniak  1 may 13:42  05-deploy-mariadb.yaml
.rw-r--r--@  244 datenmaniak 29 abr 19:03  06-svc-mariadb.yaml
.rw-r--r--@  291 datenmaniak 29 abr 20:30  07-nodeport-mariadb.yaml
.rw-r--r--@  199 datenmaniak 29 abr 19:02  08-lb-mariadb.yaml
.rw-r--r--@  549 datenmaniak  1 may 14:14  09-patch-probes.yaml
.rw-r--r--@  753 datenmaniak  1 may 14:27  kustomization.yaml
```

### Comprobar Login a la consola de MariaDB

```bash
kubectl exec -it deployment/mariadb -n mariadb -- mariadb -u root -p
```

```bash
kubectl exec -it deployment/mariadb -n mariadb -- mariadb -u admin -p
```

