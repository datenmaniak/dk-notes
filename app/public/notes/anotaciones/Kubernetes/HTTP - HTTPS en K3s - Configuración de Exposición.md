
Esta sección documenta los pasos necesarios para asegurar que el clúster pueda recibir y enrutar tráfico web de forma automática para cualquier aplicación nueva.


## 1. Configuración del Ingress Controller (Capa de Infraestructura)

Para que el clúster acepte tráfico en los puertos estándar, el servicio de **Traefik** (nuestro Ingress Controller) debe estar configurado como un balanceador de carga vinculado a los puertos del host.

### Paso: Cambiar Service a LoadBalancer

Por defecto, K3s puede instalar Traefik como `NodePort`. Debemos forzarlo a `LoadBalancer` para que "escuche" en los puertos 80 y 443 de los nodos del cluster K3s.

#### Aplicar el parche al servicio de Traefik:

```bash
kubectl patch svc traefik -n kube-system -p '{"spec": {"type": "LoadBalancer"}}'
```

### Verificación de Puertos

Ejecuta el siguiente comando en cualquier nodo para confirmar que el sistema operativo ha abierto los puertos:

```bash
ss -tulpn | grep -E ":80|:443"
```

Debe mostrarse el estado `LISTEN` para ambos puertos.

#### Master y workers en una red aislada:

En caso de que los nodos se encuentren en una red interna dentro de Proxmox, chequear que los puertos `80` y `443` y cualquier otro que se haya definido exclusivamente para `Kubernetes` estén abiertos de la IP del Firewall:

```bash
nmap 192.168.1.2
```

**Output:**
```
Starting Nmap 7.92 ( https://nmap.org ) at 2026-04-25 12:54 -04
Nmap scan report for pfs.homelab (192.168.1.2)
Host is up (0.0012s latency).
Not shown: 995 filtered tcp ports (no-response)
PORT     STATE SERVICE
22/tcp   open  ssh
53/tcp   open  domain
80/tcp   open  http
443/tcp  open  https
8443/tcp open  https-alt
```

Se observa el puerto `8443` que ha sido configurado como **NAT**,  Port Forwarding en el Firewall para direccionar el trafico hacia las aplicaciones en Kubernetes que operan en HTTPS.

Esto para no interferir con el puerto `443` que se asocia con el acceso `https://192.168.1.2` del Firewall `pfSense`.

![[Pasted image 20260425130544.png]]


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


## 3. Registro de Nombres (Capa de Resolución)

Dado que estamos en un entorno de laboratorio, el DNS debe ser informado de que el nuevo dominio apunta a la puerta de enlace (pfSense).

### Paso: Actualización de Hosts

En la máquina cliente (tu PC de trabajo), añade el nuevo subdominio al archivo `/etc/hosts` (Linux/Mac) o `C:\Windows\System32\drivers\etc\hosts`:

```plaintext
# IP WAN del pfSense | Dominio de la nueva App
192.168.1.2          app.datenmaniak.local
```

## 4. Resumen del Flujo de Publicación

Para cualquier aplicación futura, los pasos de **datenmaniak** serán:

1. **Deploy:** Crear el `Deployment` y el `Service` de la App.
    
2. **Ingress:** Crear el archivo `Ingress` apuntando al dominio deseado.
    
3. **DNS:** Añadir el dominio al archivo de hosts apuntando a la IP del pfSense.
    

**Resultado:** La aplicación será accesible automáticamente a través de `https://dominio:8443` (o el puerto que hayas definido en pfSense).


### Notas de Ingeniería DevOps

- **Escalabilidad:** Con esta configuración, puedes tener decenas de aplicaciones (blog, base de datos, panel de control) compartiendo el mismo puerto 8443; Traefik sabrá a dónde enviar a cada usuario basándose en el nombre del host en la cabecera HTTP.
    
- **Seguridad:** El tráfico entra cifrado hasta Traefik, quien se encarga de la terminación TLS o el passthrough hacia los pods.
