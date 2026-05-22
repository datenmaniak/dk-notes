

> [!NOTE]
> He dejado el 443 para pfsense, y aplique el Port forwarding 8443 para acceder al master por HTTPS.



Esa es una jugada inteligente y muy común en entornos de laboratorio para no perder el acceso estándar al firewall. Al usar el puerto **8443** como "puerta de entrada" en la WAN, dejas el camino libre para la administración y separas el tráfico de tus aplicaciones.

Para que esto funcione correctamente con tu clúster de **K3s**, solo asegúrate de que el **Ingress Controller** (Traefik) esté preparado para recibir tráfico en ese puerto o que el NAT esté haciendo la traducción de forma transparente.

### 1. Verificación de la regla NAT

En pfSense, tu regla debería verse exactamente así:

- **Interface:** `WAN`
    
- **Protocol:** `TCP`
    
- **Destination port range:** `8443`
    
- **Redirect target IP:** `10.0.0.10`
    
- **Redirect target port:** `443` (El puerto donde Traefik escucha por defecto para HTTPS).
    

---

### 2. Acceso desde el navegador

Ahora, cuando quieras probar una aplicación desplegada en tu clúster con TLS/HTTPS desde tu PC principal, la URL deberá incluir el puerto: `https://IP_WAN_PFSENSE:8443`

### 3. Consideración con el API de Kubernetes

Recuerda que el **API Server** de K3s usa el puerto **6443** por defecto. Si también quieres gestionar el clúster con `kubectl` desde fuera, mi recomendación es que hagas un Port Forward independiente:

- **WAN 6443** → **LAN 10.0.0.10:6443**
    

Esto te permitirá mantener tu archivo `kubeconfig` limpio y estándar, separando el tráfico de _gestión_ (6443) del tráfico de _aplicaciones_ (8443).

---

### 4. Siguiente paso: Certificados y SSL

Como profesional de IT, eventualmente querrás que ese acceso por el 8443 no lance advertencias de "Sitio no seguro".

- Dado que estás detrás de un firewall y en una red privada, podrías usar **Cert-Manager** dentro de K3s con el reto de DNS de Let's Encrypt o importar tus propios certificados de **datenmaniak**.
    

> [!NOTE]
> **¿Ya probaste hacer un `curl -k https://IP_WAN_PFSENSE:8443` para ver si el Master te responde?** (El `-k` es necesario por ahora para ignorar el certificado autofirmado de Traefik).

```bash
curl -k https://192.168.1.2:8443
```

> [!Error]
>  curl -k https://192.168.1.2:8443   
>  
> curl: (7) Failed to connect to 192.168.1.2 port 8443 after 1 ms: Could not connect to server

Ese error `(7) Failed to connect` y el tiempo de respuesta de **1 ms** indican que el rechazo es inmediato. Básicamente, pfSense o el nodo destino están "cerrando la puerta en la cara" del paquete antes de que pueda establecer la conexión.

Como estamos en un entorno Proxmox con pfSense virtualizado, hay **3 puntos de falla** probables. Revisémoslos en este orden:

---

### 1. El "Check" de Red Privada en la WAN (El más común)

pfSense, por seguridad, bloquea por defecto cualquier tráfico que venga de una IP privada (como tu `192.168.1.x`) hacia la interfaz WAN.

- Ve a **Interfaces > WAN**.
    
- Baja hasta el final de la página.
    
- **Desmarca** la opción: `Block private networks and loopback addresses`.
    
- Dale a **Save** y luego al botón verde **Apply Changes**.
    

### 2. ¿Traefik está escuchando en el Master?

Por defecto, el puerto 443 de Traefik en K3s a veces no se activa si no hay un recurso "Ingress" definido o si el ServiceLB no ha vinculado el puerto del host.

- En la consola del Master, ejecuta: `ss -tulpn | grep :443`
    
- Si no sale nada, el servicio de K3s no tiene el puerto abierto en el "host" y pfSense no tiene a dónde enviar el tráfico.
    
- **Solución rápida:** Asegúrate de tener al menos un Ingress creado (como el ejemplo del Nginx que te pasé antes) para que Traefik "despierte" el puerto 443.
    

### 3. La Regla de Firewall (No solo el NAT)

En pfSense, tener la regla en **NAT Port Forward** no siempre es suficiente si no se creó la regla correspondiente en el Firewall.

- Ve a **Firewall > Rules > WAN**.
    
- Debes ver una regla que diga: `IPv4 TCP * * 10.0.0.10 443 (8443)`.
    
- Si no está, edita tu regla de NAT y asegúrate de que en **Filter Rule Association** esté seleccionado `Add associated filter rule`.
    

---

### 4. Verificación desde el propio pfSense

Para descartar que el problema sea el firewall, vamos a ver si pfSense mismo puede ver al Master:

1. En pfSense, ve a **Diagnostics > Test Port**.
    
2. **Remote Host:** `10.0.0.10`
    
3. **Port:** `443`
    
4. **Source Address:** `LAN`
    

Si esta prueba **falla**, el problema está en el Ubuntu Server del Master (Firewall interno `ufw` activo o servicio K3s caído). Si esta prueba **pasa**, el problema es definitivamente la regla de la WAN o el bloqueo de redes privadas que mencioné en el punto 1.

### **¿Qué resultado te dio el "Test Port" desde el menú de Diagnostics en pfSense?**


> [!Error]
> La verificacion en el propio pfsense ha fallado. Por otra parte, no estoy seguro si he creado el NAT correctamente... 


Si el **Test Port** desde pfSense hacia la IP `10.0.0.100` en el puerto `443` falló, significa que el problema no es el NAT todavía, sino que **pfSense no logra comunicarse con el Master** a través de la red interna (LAN).

Aquí tienes la ruta para solucionar esto como un experto en redes:

### 1. El "Muro" interno: El Firewall de Ubuntu

Es muy probable que el Ubuntu Server del Master tenga su propio firewall (`ufw`) activo y esté rechazando conexiones que no vengan de su propia red local antigua.

**En la consola del Master (K3s), ejecuta:**


```bash
# Verifica el estado
sudo ufw status

# Si está activo, permite el tráfico o desactívalo para pruebas
sudo ufw allow 443/tcp
sudo ufw allow 6443/tcp
sudo ufw allow 22/tcp
```

_Si `ufw` estaba activo y bloqueando, el Test Port de pfSense debería empezar a dar "Success" de inmediato._

---

### 2. ¿Está K3s escuchando en la IP de la LAN?

A veces K3s se queda "atado" a la interfaz de `localhost` o a la IP vieja. **Ejecuta en el Master:**


```bash
ss -tulpn | grep :443
```

Si la salida muestra `127.0.0.1:443`, el servicio no es accesible desde pfSense. Debe mostrar `0.0.0.0:443` o `*:443`.

---

### 3. Verificación del NAT en pfSense (Paso a paso)

Para estar seguros de que el NAT está bien, revisa que tu configuración coincida con esta tabla en **Firewall > NAT > Port Forward**:

|Campo|Valor Correcto|
|---|---|
|**Interface**|`WAN`|
|**Protocol**|`TCP`|
|**Destination**|`WAN Address`|
|**Destination Port Range**|`8443` a `8443`|
|**Redirect Target IP**|`10.0.0.10`|
|**Redirect Target Port**|`443`|
|**Filter Rule Association**|`Add associated filter rule` (¡Muy importante!)|


---

### 4. La prueba definitiva (Capa 2)

Si el firewall de Ubuntu está apagado y el servicio está corriendo, hagamos un ping desde pfSense:

- Ve a **Diagnostics > Ping**.
    
- **Hostname:** `10.0.0.100`.
    
- **Interface:** `LAN`.
    

**Si el ping falla:** Hay un problema en el Bridge de Proxmox o la VM del Master no tiene la IP `10.0.0.10` correctamente configurada en su Netplan. **Si el ping funciona pero el Test Port 443 falla:** El problema es definitivamente el software (K3s o UFW) dentro de la VM.

¿Qué resultado te dio el **Ping** desde pfSense hacia el Master?


> [!Error]
> ---
> 1) ufw esta inactivo en el servidor
> 2) no hay salida de: `ss -tulpn | grep :443` 
> 
> Creo que no tengo bien configurado algo en k3s.!


Si `ss -tulpn | grep :443` no devuelve nada, hemos encontrado la raíz del problema: **Traefik (el Ingress Controller de K3s) no está levantado o no ha vinculado los puertos.**

En una instalación de K3s, Traefik es el encargado de abrir los puertos 80 y 443 en el host. Si no hay nada escuchando ahí, pfSense no tiene a dónde enviar el tráfico.

### 1. ¿Por qué no está escuchando Traefik?

K3s instala Traefik mediante un "HelmChart" automático. A veces, si hubo cambios de IP o problemas de red durante el arranque, el despliegue falla.

Ejecuta esto en el Master para ver qué está pasando:


```bash
kubectl get pods -n kube-system | grep traefik
```

- **Si no aparece nada:** Traefik fue desactivado o no se instaló.
    
- **Si dice `Pending` o `CrashLoopBackOff`:** Hay un error interno.
    
- **Si dice `Running`:** Entonces está escuchando en una IP específica que no es la del host.
    

### 2. Verificar el servicio LoadBalancer

K3s usa un componente llamado `svclb` (Service Load Balancer) para exponer a Traefik. Verifica su estado:


```bash
kubectl get svc -n kube-system traefik
```

Deberías ver algo como: `TYPE: LoadBalancer | CLUSTER-IP: 10.43.x.x | EXTERNAL-IP: 10.0.0.10, 10.0.0.11, 10.0.0.12`

### 3. La solución rápida: Forzar el despliegue

Si Traefik no está funcionando bien tras el cambio de red a la `10.0.0.x`, lo más sano es forzar a K3s a que lo reconfigure.

Ejecuta este comando en el Master (esto actualizará el servidor y asegurará que Traefik intente levantar):

Bash

```
curl -sfL https://get.k3s.io | sh -s - server --tls-san 10.0.0.100 --write-kubeconfig-mode 644
```

### 4. Comprobación de logs

Si después de unos minutos `ss -tulpn | grep :443` sigue vacío, mira los logs del sistema para ver si hay errores de red:


```bash
sudo journalctl -u k3s | grep -i traefik
```

---

### Un detalle sobre el NAT (Por si acaso)

Mencionaste que no estás seguro de si el NAT está bien. Aquí tienes una referencia rápida de cómo debe verse en la interfaz de pfSense:

| Campo                    | Configuración Esperada             |
| ------------------------ | ---------------------------------- |
| **Interface**            | WAN                                |
| **Protocol**             | TCP                                |
| **Destination Port**     | 8443 (El que usas en tu navegador) |
| **Redirect Target IP**   | 10.0.0.100                         |
| **Redirect Target Port** | 443 (El que debería abrir Traefik) |


> [!NOTE]
> **¿Qué te mostró el comando `kubectl get pods -n kube-system`?** Si los pods de Traefik no están en `Running`, ahí tenemos al culpable.

```plaintext
kubectl get pods -n kube-system | grep traefik
   traefik-c5c8bf4ff-s5r8d                   1/1     Running   0          94m
```


Si el pod de Traefik está en **Running**, pero `ss -tulpn` no muestra nada escuchando en el puerto 443 del host, el problema es que el **ServiceLB** (el componente de K3s que vincula los puertos del pod con los del nodo físico) no está haciendo su trabajo.

Esto ocurre frecuentemente cuando se cambia la red: el servicio quedó "atado" a las IPs viejas o está esperando una IP externa que no llega.

Aquí tienes los pasos para forzar a K3s a exponer los puertos en tu nueva red `10.0.0.x`:

### 1. Verificar el Service de Traefik

Ejecuta este comando para ver qué IPs está intentando usar Traefik para exponerse:


```bash
kubectl get svc -n kube-system traefik
```

**Lo que deberías ver:** En la columna `EXTERNAL-IP`, deberían aparecer tus nuevas IPs: `10.0.0.100, 10.0.0.110, 10.0.0.102`. **El problema:** Si dice `<pending>` o muestra las IPs viejas (`192.168.1.x`), el tráfico nunca llegará al pod aunque esté corriendo.

---

### 2. Reiniciar el balanceador de carga de K3s

Para obligar a K3s a que refresque la asignación de puertos en las interfaces de red de Ubuntu, reinicia los pods que gestionan la red:


```bash
kubectl delete pods -n kube-system -l app.kubernetes.io/name=traefik
kubectl delete pods -n kube-system -l svccontroller.k3s.cattle.io/svcname=traefik
```

_Esto no borra Traefik, solo reinicia los procesos que "abren" los puertos 80 y 443 en el Ubuntu Server._

---

### 3. Comprobar de nuevo el puerto en el Master

Espera un minuto y vuelve a ejecutar en la consola del Master:


```bash
ss -tulpn | grep :443
```

Si ahora ves una línea que dice `LISTEN`, ¡victoria! pfSense ya tendrá a quién entregarle los paquetes.

---

### 4. Revisión del NAT en pfSense (El detalle del "Redirect")

Como mencionaste que no estabas seguro del NAT, compáralo con este esquema. Es vital que el **puerto de destino** sea el 443, aunque tú entres por el 8443:

| Campo en pfSense           | Valor                       |
| -------------------------- | --------------------------- |
| **Interface**              | WAN                         |
| **Protocol**               | TCP                         |
| **Destination port range** | **From:** 8443 **To:** 8443 |
| **Redirect Target IP**     | `10.0.0.100`                |
| **Redirect Target Port**   | **443**                     |


---

### ¿Por qué sigue fallando el "Test Port"?

Si el Pod está en `Running` pero el puerto sigue cerrado en el host, es posible que K3s necesite que le confirmes en qué interfaz debe trabajar. Como eres un apasionado de Linux, puedes verificar si hay errores específicos en el log del servicio:


```bash
sudo journalctl -u k3s | grep -iE "failed|error|address"
```


> [!NOTE]
> **Haz una cosa:** Después de reiniciar los pods de Traefik, intenta de nuevo el `ss -tulpn`. Si sigue sin aparecer nada, ¿podrías mostrarme la salida de `kubectl get svc -n kube-system traefik`? Eso nos dirá exactamente dónde está trabado el balanceador.


```bash
 kubectl get svc -n kube-system traefik
```

   **Resultado:**
```plaintext

NAME      TYPE      CLUSTER-IP    EXTERNAL-IP   PORT(S)                     AGE
traefik   NodePort  10.43.81.229  <none>        80:30740/TCP,443:32360/TCP  3d4h
```


> [!Success]
> ¡Ahí está el culpable! Lo hemos encontrado.
> 
> El problema es que tu servicio de Traefik está configurado como **`NodePort`** en lugar de **`LoadBalancer`**.


En K3s, cuando Traefik es un `NodePort`, Kubernetes abre puertos aleatorios y altos (en tu caso el **30740** para HTTP y el **32360** para HTTPS) en el host, pero **NO** abre los puertos estándar 80 y 443. Por eso pfSense no encuentra a nadie escuchando en el 443 y el `ss -tulpn` te sale vacío.

### La Solución: Cambiar el servicio a LoadBalancer

Para que K3s use su balanceador interno (ServiceLB) y abra los puertos 80/443 en tu Ubuntu Server, debemos cambiar el tipo de servicio.

Ejecuta este comando en el Master:


```bash
kubectl patch svc traefik -n kube-system -p '{"spec": {"type": "LoadBalancer"}}'
```

---

### ¿Qué sucederá después de ejecutarlo?

1. **K3s activará el ServiceLB:** Verás que aparecen nuevos pods llamados `svclb-traefik-xxx`.
    
2. **Puertos abiertos:** Si ejecutas `ss -tulpn | grep -E ":80|:443"`, ahora sí deberías ver el estado `LISTEN` en esos puertos.
    
3. **IP Externa:** Al ejecutar `kubectl get svc -n kube-system traefik`, la columna `EXTERNAL-IP` debería mostrar `10.0.0.10` (y las de tus otros nodos).
    

---

### Ajuste en pfSense (NAT)

Ahora que sabemos que el puerto real en el Master será el **443**, tu regla de NAT actual que mencionaste (8443 → 443) empezará a funcionar mágicamente.

**Repaso de la regla NAT:**

- **Puerto WAN:** 8443
    
- **IP Destino:** 10.0.0.10
    
- **Puerto Destino:** 443
    

---

### ¿Por qué cambió a NodePort?

A veces, si al instalar K3s se detecta algún conflicto de puertos o si se usaron flags específicas de personalización, el HelmChart de Traefik se despliega como NodePort por seguridad. Al forzarlo a `LoadBalancer`, le das permiso a K3s para "adueñarse" de los puertos 80/443 del sistema operativo.

**Prueba esto:** Ejecuta el comando `patch` que te puse arriba, espera unos 30 segundos y vuelve a probar el **Test Port** desde pfSense o el `curl -k https://192.168.1.2:8443` desde tu PC. ¡Debería responder!


> [!Warning]
> kubectl get svc -n kube-system traefik


Para que el clúster sea funcional y el tráfico llegue desde el firewall pfSense hasta tus aplicaciones, el servicio de Traefik debe estar en modo **LoadBalancer**. Al estar en modo `NodePort`, los puertos 80 y 443 del sistema operativo Ubuntu permanecen cerrados, y por eso el **Test Port** de pfSense falla.

Aquí tienes los pasos para corregirlo y sincronizarlo con tu regla de NAT:

### 1. Cambiar el tipo de servicio a LoadBalancer

Ejecuta este comando en el Master para obligar a K3s a abrir los puertos estándar (80/443) en el host:


```bash
kubectl patch svc traefik -n kube-system -p '{"spec": {"type": "LoadBalancer"}}'
```

### 2. Verificar la apertura de puertos

Una vez aplicado el cambio, espera unos segundos y verifica que el sistema operativo ya esté escuchando en el puerto 443:


```bash
ss -tulpn | grep :443
```

_Ahora deberías ver una línea indicando que el proceso está en estado `LISTEN`._


### 3. Comprobar la IP Externa

Verifica nuevamente el servicio. Ahora la columna `EXTERNAL-IP` no debería estar vacía:


```bash
kubectl get svc -n kube-system traefik
```

Debería mostrar las IPs de tus nodos (`10.0.0.100`, etc.), lo que confirma que el tráfico que llegue a la interfaz LAN del Master será procesado por Traefik.

---

### 4. Sincronización con pfSense

Con el puerto 443 ya abierto en el Master, tu configuración de **Port Forwarding** actual debería empezar a funcionar:

- **Entrada (WAN):** Puerto `8443`
    
- **Redirección (LAN):** IP `10.0.0.10` al puerto `443`
    

### Prueba final

Intenta de nuevo la conexión desde tu PC principal:

```bash
curl -k https://192.168.1.2:8443
```

Si el comando `patch` tuvo éxito, ya no deberías recibir el error de "Failed to connect", sino posiblemente un error **404** de Traefik (lo cual es bueno, porque significa que ya llegaste al servidor y solo falta configurar un Ingress para tu aplicación).

> [!success]
> ❯ curl -k https://192.168.1.2:8443
> 404 page not found


### Qué te muestra ahora el comando `kubectl get svc` tras aplicar el parche?


```plaintext
  kubectl get svc -n kube-system traefik
NAME      TYPE           CLUSTER-IP     EXTERNAL-IP                        PORT(S)                      AGE
traefik   LoadBalancer   10.43.81.229   10.0.0.100,10.0.0.101,10.0.0.102   80:30740/TCP,443:32360/TCP   3d4h
```

Excelente! El cambio a **LoadBalancer** ha funcionado y las `EXTERNAL-IP` ya están asignadas. Sin embargo, hay un detalle crítico en esas IPs que explica por qué todavía no conectas.

### El Problema: Conflicto de IPs (`10.0.0.100...` vs `10.0.0.10`)

Fíjate en las `EXTERNAL-IP` que muestra tu comando: `10.0.0.100, 10.0.0.101, 10.0.0.102`

Pero tú estás intentando enviar el tráfico desde pfSense a la **`10.0.0.10`**.

**¿Qué está pasando?** K3s ha detectado interfaces de red o ha asignado IPs del rango DHCP de tu LAN de pfSense para el balanceador, pero no coinciden con la IP estática que le diste al Master (`.10`). Si pfSense envía el paquete a la `.10`, y Traefik está escuchando en la `.100`, el paquete se pierde.


#### La Solución: Sincronizar pfSense con K3s

Tienes dos caminos para arreglar esto ahora mismo:

#### Opción A: Cambiar el NAT en pfSense (La más rápida)

Modifica tu regla de Port Forward para que apunte a la IP que K3s decidió usar como principal:

- **Redirect Target IP:** `10.0.0.100` (en lugar de `10.0.0.10`).
    
- **Redirect Target Port:** `443`.
    

#### Opción B: Forzar a Traefik a usar la IP del Master (La más limpia)

Si prefieres que todo llegue a la `.10` (que es tu Master), puedes forzar la IP en el servicio:

Bash

```
kubectl patch svc traefik -n kube-system -p '{"spec": {"externalIPs": ["10.0.0.10"]}}'
```

---

### Verificación Final de Puertos

Una vez que decidas qué IP usar, verifica en el nodo Master que el puerto 443 ya esté "vivo" para el tráfico externo:


```bash
ss -tulpn | grep :443
```

Ahora **debería aparecer** el puerto escuchando en las IPs `10.0.0.x`.

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

### ¿Qué significa ese 404?

Significa que:

1. **Tu red funciona:** El paquete salió de tu PC, llegó a la WAN del pfSense (`192.168.1.2`).
    
2. **El NAT funciona:** El pfSense tradujo el puerto `8443` al `443` y lo envió a la IP correcta del Master.
    
3. **K3s responde:** El proceso de **Traefik** recibió el paquete.
    
4. **El "error":** Te da 404 simplemente porque Traefik dice: _"Ok, llegaste a la puerta, pero no me has dicho a qué aplicación quieres entrar (no hay reglas de Ingress que coincidan con esta solicitud)"_.
    

---

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

¿Lograste entrar con el nombre de dominio?