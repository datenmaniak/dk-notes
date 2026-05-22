
### ¿Te gustaría que veamos cómo exponer tu primera aplicación web a través del Firewall ahora que la red está lista?


> [!NOTE]
> Hay que hacer un Port Forwarding del 80 y 883... ?


¡Exacto! Ese es el siguiente paso lógico. Para que tu portal **IT Freelancers** (o cualquier servicio que despliegues en el clúster) sea accesible desde tu PC o desde fuera, pfSense debe "abrir la puerta" hacia el **Ingress Controller** de tu clúster.

En K3s, el Ingress por defecto es **Traefik**. Por lo general, Traefik se expone en los puertos 80 y 443 de los nodos del clúster.

Aquí tienes cómo configurar el **Port Forwarding** en pfSense de manera profesional:

---

### 1. Mapeo de Puertos en pfSense

Ve a **Firewall > NAT > Port Forward** y añade estas dos reglas:

|Servicio|Puerto WAN|IP Destino (LAN)|Puerto Interno|
|---|---|---|---|
|**HTTP**|80|`10.0.0.10` (Master)|80|
|**HTTPS**|443|`10.0.0.10` (Master)|443|

Exportar a Hojas de cálculo

> **Nota:** He puesto la IP del Master (`10.0.0.10`) asumiendo que Traefik está corriendo allí. En un clúster K3s estándar, Traefik suele estar disponible en todos los nodos.

---

### 2. El "Truco" del puerto 443

**Cuidado aquí:** Por defecto, pfSense usa el puerto **443** para su propia interfaz web (WebGUI). Si intentas mapear el 443 de la WAN al clúster, podrías tener un conflicto o quedarte fuera de la configuración del firewall.

**Tienes dos opciones:**

1. **Cambiar el puerto de pfSense:** Ve a _System > Advanced > Admin Access_ y cambia "TCP Port" de 443 a algo como `8443`.
    
2. **Usar un puerto distinto en la WAN:** Mapear el puerto `8443` de la WAN al `443` del Master.
    

---

### 3. Configuración de DNS Local (Split DNS)

Como vas a trabajar con dominios (por ejemplo, `portal.datenmaniak.local`), necesitas que tu PC sepa que ese nombre apunta a la IP de la WAN de pfSense.

- **En tu archivo `/etc/hosts` (Linux) o `C:\Windows\System32\drivers\etc\hosts` (Windows):** Añade la línea: `192.168.1.X portal.datenmaniak.local` (Usa la IP WAN de pfSense).
    

---

### 4. Verificación en K3s

Asegúrate de que Traefik esté escuchando correctamente. Puedes verlo con:

Bash

```
kubectl get svc -n kube-system
```

Busca el servicio llamado `traefik`. Debería mostrar algo como `80:3xxxx/TCP, 443:3xxxx/TCP`. K3s configura esto automáticamente para que el puerto 80 del nodo redirija al balanceador de carga de Traefik.

---

### Un detalle sobre el puerto 443

Mencionaste el **883**. ¿Es para algún servicio específico de base de datos o una aplicación custom de tu proyecto? Si es así, el procedimiento es el mismo:

- **Protocolo:** TCP.
    
- **Puerto WAN:** 883.
    
- **IP LAN:** La del nodo donde corra el servicio.
    
- **Puerto LAN:** 883.
    

#### **¿Quieres que probemos desplegar un "Hello World" rápido en Nginx para confirmar que el tráfico fluye desde tu navegador hasta el clúster?**



> [!NOTE]
> Hablemos entonces del **puerto 443 (HTTPS)**, porque este tiene una "trampa" específica en pfSense que debes desactivar para que tu proyecto **datenmaniak** fluya sin problemas.




### 1. El Conflicto del Puerto 443

Por defecto, la interfaz web de pfSense usa el puerto 443. Si intentas hacer un Port Forward del 443 de la WAN hacia tu Master de K3s, pfSense podría ignorarlo porque cree que quieres entrar a su panel de administración.

**La solución profesional:**

1. Ve a **System > Advanced > Admin Access**.
    
2. En **TCP Port**, cámbialo de vacío (443) a algo como **`8443`**.
    
3. Asegúrate de que **Protocol** sea HTTPS.
    
4. Baja y dale a **Save**.
    
    - _A partir de ahora, para entrar a pfSense usarás: `https://192.168.1.X:8443`._
        

---

### 2. Configuración del Port Forward (80 y 443)

Ahora que el 443 está libre, vamos a redirigir el tráfico hacia el Ingress Controller de tu clúster (Traefik).

**Regla para HTTP:**

- **Interface:** `WAN`
    
- **Protocol:** `TCP`
    
- **Destination Port Range:** `80` (HTTP)
    
- **Redirect Target IP:** `10.0.0.10` (Master)
    
- **Redirect Target Port:** `80`
    

**Regla para HTTPS:**

- **Interface:** `WAN`
    
- **Protocol:** `TCP`
    
- **Destination Port Range:** `443` (HTTPS)
    
- **Redirect Target IP:** `10.0.0.10` (Master)
    
- **Redirect Target Port:** `443`
    

---

### 3. ¿Por qué al Master?

Como estás usando K3s, el servicio **Traefik** viene configurado por defecto como un `LoadBalancer`. Como no tienes un proveedor de nube (AWS/Azure) que te dé una IP externa, K3s usa **ServiceLB**.

Esto significa que cualquier tráfico que llegue al puerto 80/443 de **cualquiera** de tus nodos (`10.0.0.10`, `.11` o `.12`) será capturado por Traefik y llevado al Pod correcto. Mapearlo al Master es lo más sencillo para empezar.

---

### 4. Prueba de fuego

Para confirmar que esto funciona, puedes desplegar un servidor Nginx básico en tu clúster:

```bash
nano test.yaml
```

***Pega este trozo de YAML:***


```yaml
apiVersion: apps/v1
kind: Deployment
metadata:
  name: test-web
spec:
  replicas: 1
  selector:
    matchLabels:
      app: test-web
  template:
    metadata:
      labels:
        app: test-web
    spec:
      containers:
      - name: nginx
        image: nginx
---
apiVersion: v1
kind: Service
metadata:
  name: test-web-svc
spec:
  type: ClusterIP
  ports:
  - port: 80
  selector:
    app: test-web
---
apiVersion: networking.k8s.io/v1
kind: Ingress
metadata:
  name: test-ingress
  annotations:
    traefik.ingress.kubernetes.io/router.entrypoints: web
spec:
  rules:
  - http:
      paths:
      - path: /
        pathType: Prefix
        backend:
          service:
            name: test-web-svc
            port:
              number: 80
```

Si aplicas esto (`kubectl apply -f test.yaml`), al poner la **IP WAN de tu pfSense** en el navegador de tu PC, deberías ver la página de bienvenida de Nginx.

> [!NOTE]
> **¿Ya cambiaste el puerto de administración del pfSense para liberar el 443?** Es el paso más importante para evitar bloqueos.

