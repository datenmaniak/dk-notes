
Para actualizar el `kubeconfig` local y que puedas administrar el clúster desde tu PC (pasando por el pfSense), el procedimiento requiere ajustar la dirección del servidor y asegurarte de que los certificados reconozcan la nueva IP.

Aquí tienes los pasos exactos:

### 1. Obtener el archivo desde el Master

Primero, necesitas el contenido del archivo de configuración que genera K3s. Ejecuta esto en la consola del **Master**:


```bash
sudo cat /etc/rancher/k3s/k3s.yaml
```

### 2. Actualizar el archivo en tu PC local

Copia el contenido y pégalo en tu archivo local (normalmente ubicado en `~/.kube/config`). Debes realizar dos cambios críticos:

1. **Cambiar la IP del Server:** Busca la línea que dice `server: https://127.0.0.1:6443` y cámbiala por la IP que tiene la **WAN de tu pfSense** (o la IP interna del Master `10.0.0.10` si configuraste la ruta estática o estás en la misma red).
    
    - _Ejemplo:_ `server: https://192.168.1.X:6443` (la IP de la WAN).
        
2. **Verificar el Puerto:** Si hiciste un **Port Forward** en el pfSense del puerto `6443` externo al `6443` interno del Master, asegúrate de que coincida en la URL.
    

### 3. El problema del Certificado (TLS)

Como el certificado de K3s se generó originalmente para la IP `192.168.1.100`, al intentar conectar mediante la nueva IP o la IP del pfSense, `kubectl` te dará un error de **"Certificate signed by unknown authority"** o un error de validación de IP.

Tienes dos formas de solucionar esto:

#### Opción A: La forma correcta (Añadir TLS-SAN)

Si seguiste el paso anterior de reinstalar con `--tls-san`, el certificado ya debería ser válido para la nueva IP. Si no lo hiciste, puedes forzar al Master a aceptar la nueva IP del pfSense editando el servicio:

1. En el **Master**, edita `/etc/systemd/system/k3s.service`.
    
2. En `ExecStart`, añade: `--tls-san=IP_WAN_PFSENSE`.
    
3. Reinicia:
    
    
    ```bash
    sudo systemctl daemon-reload
    sudo systemctl restart k3s
    ```
    

#### Opción B: La forma rápida (Omitir verificación)

Si es un entorno de laboratorio y quieres probar ya mismo, puedes decirle a `kubectl` que ignore la validación del certificado (aunque no se recomienda para producción):

Busca en tu archivo `config` local la sección del clúster y añade `insecure-skip-tls-verify: true`, pero **debes borrar** la línea de `certificate-authority-data`. Quedaría algo así:


```yaml
clusters:
- cluster:
    insecure-skip-tls-verify: true
    server: https://192.168.1.X:6443
  name: default
```

### 4. Prueba de conexión

Una vez guardado el archivo en tu PC, verifica la conexión:


```bash
kubectl get nodes
```

Si todo está bien configurado en el **Port Forward** de pfSense y el `kubeconfig` apunta a la IP correcta, deberías ver tus tres nodos (Master y los 2 Workers) con sus nuevas IPs del rango `10.0.0.x`.

**¿Te ha dado algún error de "connection refused" o de certificado al intentar el comando?**


Estaré atento especialmente a estos puntos en el log o el error que te arroje:

- **`Unable to connect to the server`**: Si sale esto, revisaremos el **Port Forward** en pfSense o si el servicio `k3s` no está escuchando en la interfaz correcta.
    
- **`X509: certificate relies on legacy Common Name`** o **`certificate signed by unknown authority`**: Si sale esto, el tema está en los **TLS-SAN** que comentamos antes.
    
- **`The connection to the server [IP]:6443 was refused`**: Esto suele indicar que el puerto está cerrado en el firewall o el servicio está caído.
    

Quedo a la espera de tu `kubectl get nodes` o el error que te dé el sistema para decirte exactamente dónde ajustar el tornillo.

```plaintext
❯ kubectl get nodes       
NAME         STATUS     ROLES           AGE     VERSION
k3s-master   Ready      control-plane   3h27m   v1.34.6+k3s1
k8s-master   NotReady   control-plane   3d3h    v1.34.6+k3s1
worker1      Ready      <none>          3d1h    v1.34.6+k3s1
worker2      Ready      <none>          3d1h    v1.34.6+k3s1
```

¡Ya casi lo tienes! Esa salida es muy reveladora. Lo que estamos viendo es un "clúster fantasma" debido al cambio de nombre o de IP.

### El diagnóstico:

1. **`k3s-master` (Ready):** Este es tu nodo actual con la nueva configuración.
    
2. **`k8s-master` (NotReady):** Este es el registro antiguo del master (con el nombre anterior o la IP vieja). Kubernetes piensa que es un nodo distinto que se ha desconectado.
    
3. **`worker1` y `worker2` (Ready):** ¡Buenas noticias! Esto significa que los workers ya encontraron al master en la red `10.0.0.x` y están comunicando correctamente.
    

---

### Pasos para limpiar el clúster:

#### 1. Eliminar el nodo antiguo

Como `k8s-master` ya no existe (ahora es `k3s-master`), debes borrarlo lógicamente del clúster para que no ensucie tus comandos de `kubectl`:


```bash
kubectl delete node k8s-master
```

#### 2. Verificar las IPs internas

Para asegurarte de que todo el tráfico fluye por el bridge de pfSense (`10.0.0.x`) y no hay residuos de la red vieja, ejecuta:


```bash
kubectl get nodes -o wide
```

**Qué revisar en la columna `INTERNAL-IP`:**

- Todos deberían mostrar direcciones `10.0.0.x`.
    
- Si alguno muestra `192.168.1.x`, significa que ese nodo todavía tiene configurada la IP vieja en su configuración de agente de K3s.
    

---

### Un ajuste final para tus despliegues

Como ahora el tráfico externo pasa por **pfSense**, cuando quieras desplegar una aplicación (por ejemplo, un servidor web en el puerto 80) y quieras acceder desde tu PC:

1. Usarás un **Service** de tipo `NodePort` o un **Ingress**.
    
2. En **pfSense**, tendrás que hacer un **Port Forward** del puerto que desees (ej. 80 o 443) hacia la IP del Master o de los Workers en la LAN (`10.0.0.x`).
    

**¿Qué IPs te muestra el comando `kubectl get nodes -o wide`?** Si todas son `10.0.0.x`, tu migración de red para **datenmaniak** ha sido un éxito total.


> [!NOTE]
> Se muestra la IP correcta de cada uno..?


Para ver las IPs internas que Kubernetes tiene registradas para cada nodo y confirmar que todo el tráfico está pasando por el segmento de red gestionado por **pfSense**, ejecuta el siguiente comando:


```bash
kubectl get nodes -o wide
```

### Qué debes observar en la salida:

En la columna **`INTERNAL-IP`**, deberías ver el nuevo esquema que definimos para tu entorno:

- **k3s-master:** `10.0.0.10`
    
- **worker1:** `10.0.0.11`
    
- **worker2:** `10.0.0.12`
    

---

### ¿Qué hacer si ves IPs incorrectas?

1. **Si `k8s-master` sigue apareciendo con la IP vieja (`192.168.1.100`):** Como mencionamos, ese es un registro huérfano. Elimínalo con: `kubectl delete node k8s-master`
    
2. **Si los Workers muestran la IP vieja:** Esto sucede porque el agente de K3s se registró inicialmente con la interfaz de red anterior. Para forzar a que usen la nueva IP de la LAN de pfSense sin reinstalar, debes editar el archivo de configuración del agente en cada worker:
    
    - Edita `/etc/systemd/system/k3s-agent.service.env` (o el archivo de configuración en `/etc/rancher/k3s/config.yaml`).
        
    - Añade o modifica la flag: `--node-ip=10.0.0.11` (ajustando según el worker).
        
    - Reinicia el servicio: `sudo systemctl restart k3s-agent`.
        

### Próximo paso de red

Una vez que las IPs sean correctas en la columna `INTERNAL-IP`, tu clúster estará listo para que configures el **Ingress Controller** o **MetalLB**. Dado que estás detrás de un firewall (pfSense), lo más profesional será usar el puerto `80/443` del Master y redirigir el tráfico desde la WAN de pfSense hacia él.

¿Lograste limpiar el nodo duplicado y ver las IPs del rango `10.0.0.x`?
