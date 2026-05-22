
### Prueba de fuego revisada

Después de ajustar la IP en el NAT de pfSense (usando la `.100` o forzando la `.10`), intenta de nuevo:


```bash
curl -k https://192.168.1.2:8443
```

**¿Qué IP prefieres dejar como definitiva para tu branding de "datenmaniak"?** Si la `.10` es tu IP estática de gestión, lo mejor es que el NAT apunte a esa y ajustemos K3s para que la reconozca.


> [!success]
> ❯ curl -k https://192.168.1.2:8443
> 404 page not found


¡**Victoria**! Ese `404 page not found` es la respuesta más hermosa que podías recibir en este momento.

### El siguiente paso: Tu primer Ingress (Para "datenmaniak")

Para dejar de ver ese 404 y ver algo real, necesitas crear un **Ingress**. Como profesional de IT, querrás usar nombres de dominio en lugar de IPs.

Sigue estos pasos para probarlo con un servidor Nginx básico:

#### 1. Desplegar una App de prueba

Copia y pega esto en tu terminal del Master:


```bash
cat <<EOF | kubectl apply -f -
apiVersion: apps/v1
kind: Deployment
metadata:
  name: web-test
spec:
  replicas: 1
  selector:
    matchLabels:
      app: web-test
  template:
    metadata:
      labels:
        app: web-test
    spec:
      containers:
      - name: nginx
        image: nginx
---
apiVersion: v1
kind: Service
metadata:
  name: web-test-svc
spec:
  ports:
  - port: 80
  selector:
    app: web-test
---
apiVersion: networking.k8s.io/v1
kind: Ingress
metadata:
  name: web-test-ingress
  annotations:
    traefik.ingress.kubernetes.io/router.entrypoints: websecure
spec:
  rules:
  - host: lab.datenmaniak.local
    http:
      paths:
      - path: /
        pathType: Prefix
        backend:
          service:
            name: web-test-svc
            port:
              number: 80
EOF
```

#### 2. Engañar a tu PC (DNS Local)

Como `lab.datenmaniak.local` no existe en internet, debes decirle a tu PC que ese nombre vive en tu pfSense.

- **En tu PC principal**, edita tu archivo `/etc/hosts` (o equivalente):
    
- Añade la línea: `192.168.1.2 lab.datenmaniak.local`
    

---

### Prueba final de oro

Abre tu navegador y entra en: `https://lab.datenmaniak.local:8443`

**(Acepta el riesgo del certificado autofirmado)**. Si ves el mensaje **"Welcome to nginx!"**, habrás completado el ciclo completo de Networking: desde el Firewall hasta el Pod de Kubernetes.

#### ¿Lograste entrar con el nombre de dominio?

> [!success]
> Si. lo he logrado.


¡Excelente noticia! Haber pasado del "Failed to connect" al "404" y finalmente al "Welcome to nginx" significa que has dominado la **Capa 3 (Routing)** y la **Capa 7 (Aplicación)** de tu nuevo Home Lab.

Ya tienes los cimientos profesionales para tu marca **datenmaniak**. Ahora que el tráfico fluye desde tu red principal, a través del firewall pfSense, hasta el corazón de tus contenedores en K3s, tienes un entorno de producción real en miniatura.

Aquí tienes un resumen de lo que has consolidado hoy:

### Tu Arquitectura Actual

- **Aislamiento:** Tu clúster vive en una red segura (`10.0.0.x`) gestionada por pfSense.
    
- **Acceso Seguro:** Entras por el puerto `8443` (WAN), que se traduce internamente al `443` de Kubernetes.
    
- **Orquestación:** Traefik está funcionando como **LoadBalancer**, listo para recibir múltiples dominios.
    
- **Resolución:** Ya sabes cómo usar el archivo `hosts` para simular dominios reales antes de lanzarlos al mundo.
    

---

### ¿Qué sigue para "datenmaniak"?

Si quieres seguir elevando el nivel de este laboratorio, estas son las tres rutas lógicas:

1. **Certificados Automáticos:** Instalar `cert-manager` para que tus sitios tengan el candado verde de **Let's Encrypt** automáticamente, incluso usando el reto de DNS (Cloudflare/DigitalOcean) para no tener que abrir más puertos.
    
2. **Dashboard de Monitoreo:** Desplegar **Grafana y Prometheus** para ver el consumo de recursos de tu laptop HP Victus en tiempo real.
    
3. **Almacenamiento Persistente:** Configurar un sistema de archivos (como **Longhorn** o un NFS externo) para que si borras un pod de base de datos, tus datos no se pierdan.
    

¿Te gustaría empezar a desplegar los servicios reales de tu página web de consultoría o prefieres asegurar primero la persistencia de los datos?