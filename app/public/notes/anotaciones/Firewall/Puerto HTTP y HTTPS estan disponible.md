

> [!NOTE]
> Menciona los pasos, para que sirva de documentacion en markdwown  lo necesario en Kubernetes para que los puerto HTTP y HTTPS este disponible para cualquier app.


Como ingeniero de DevOps, documentar el "Estado Deseado" de la infraestructura es vital. Para que cualquier aplicación futura en tu clúster **k3s** sea accesible vía HTTP/HTTPS sin tocar de nuevo el firewall, debes estandarizar el **Ingress Controller**.

Aquí tienes la documentación técnica en formato Markdown lista para tu repositorio:

---

##  🚀 Configuración Global de Exposición (HTTP/HTTPS) en K3s

Esta sección documenta los pasos necesarios para asegurar que el clúster pueda recibir y enrutar tráfico web de forma automática para cualquier aplicación nueva.

## 1. Configuración del Ingress Controller (Capa de Infraestructura)

Para que el clúster acepte tráfico en los puertos estándar, el servicio de **Traefik** (nuestro Ingress Controller) debe estar configurado como un balanceador de carga vinculado a los puertos del host.

### Paso: Cambiar Service a LoadBalancer

Por defecto, K3s puede instalar Traefik como `NodePort`. Debemos forzarlo a `LoadBalancer` para que "escuche" en los puertos 80 y 443 de los nodos de Ubuntu.


```bash
# Aplicar el parche al servicio de Traefik
kubectl patch svc traefik -n kube-system -p '{"spec": {"type": "LoadBalancer"}}'
```

### Verificación de Puertos

Ejecuta el siguiente comando en cualquier nodo para confirmar que el sistema operativo ha abierto los puertos:


```bash
ss -tulpn | grep -E ":80|:443"
```

_Debe mostrarse el estado `LISTEN` para ambos puertos._

---

## 2. Definición del Recurso Ingress (Capa de Aplicación)

Una vez que la "tubería" (Traefik) está abierta, cada aplicación nueva solo requiere un manifiesto de tipo `Ingress`. Este archivo actúa como la configuración del servidor virtual (similar a un VHost de Apache o Nginx).

### Estructura Estándar del Manifiesto

Para cada nueva aplicación, se debe crear un archivo `.yaml` con esta estructura:


```yaml
apiVersion: networking.k8s.io/v1
kind: Ingress
metadata:
  name: nombre-de-tu-app-ingress
  annotations:
    # Fuerza el uso de HTTPS si tienes certificados
    traefik.ingress.kubernetes.io/router.entrypoints: websecure
spec:
  rules:
  - host: app.datenmaniak.local  # <-- El dominio que usará el cliente
    http:
      paths:
      - path: /
        pathType: Prefix
        backend:
          service:
            name: nombre-de-tu-servicio-interno # <-- Debe coincidir con tu Service
            port:
              number: 80 # Puerto donde escucha tu App dentro del clúster
```

---

## 3. Registro de Nombres (Capa de Resolución)

Dado que estamos en un entorno de laboratorio, el DNS debe ser informado de que el nuevo dominio apunta a la puerta de enlace (pfSense).

### Paso: Actualización de Hosts

En la máquina cliente (tu PC de trabajo), añade el nuevo subdominio al archivo `/etc/hosts` (Linux/Mac) o `C:\Windows\System32\drivers\etc\hosts`:


```plaintext
# IP WAN del pfSense | Dominio de la nueva App
192.168.1.2          app.datenmaniak.local
```

---

## 4. Resumen del Flujo de Publicación

Para cualquier aplicación futura, los pasos de **datenmaniak** serán:

1. **Deploy:** Crear el `Deployment` y el `Service` de la App.
    
2. **Ingress:** Crear el archivo `Ingress` apuntando al dominio deseado.
    
3. **DNS:** Añadir el dominio al archivo de hosts apuntando a la IP del pfSense.
    

**Resultado:** La aplicación será accesible automáticamente a través de `https://dominio:8443` (o el puerto que hayas definido en pfSense).

---

### Notas de Ingeniería DevOps

- **Escalabilidad:** Con esta configuración, puedes tener decenas de aplicaciones (blog, base de datos, panel de control) compartiendo el mismo puerto 8443; Traefik sabrá a dónde enviar a cada usuario basándose en el nombre del host en la cabecera HTTP.
    
- **Seguridad:** El tráfico entra cifrado hasta Traefik, quien se encarga de la terminación TLS o el passthrough hacia los pods.
    

¿Te gustaría que la próxima nota trate sobre cómo automatizar este despliegue de Ingress usando **Helm**, que es la herramienta estándar en la industria?

