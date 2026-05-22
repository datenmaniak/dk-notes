
Para actualizar el `kubeconfig` local y que puedas administrar el clúster desde tu PC (pasando por el pfSense), el procedimiento requiere ajustar la dirección del servidor y asegurarte de que los certificados reconozcan la nueva IP.

### 1. Obtener el archivo desde el Master

Primero, necesitas el contenido del archivo de configuración que genera K3s. Ejecuta esto en la consola del **Master**:


```bash
sudo cat /etc/rancher/k3s/k3s.yaml
```

### 2. Actualizar el archivo en tu PC local



**Acceder al master k3s:**

```bash
sudo cat /etc/rancher/k3s/k3s.yaml > kubeconfig-10.0.0.100
```

**Fuera del master.  En la estación de trabajo remota:**

```bash
cd ~/homelab/re-install-k3s 
```

```bash
scp -P 2210 -i ~/.ssh/datenmaniak datenk@192.168.1.2:~/kubeconfig-10-0-0-100 $PWD
```

**Hacer un respaldo del arhivo**

```bash
cp kubeconfig-10-0-0-100 kubeconfig-10-0-0-100.BACKUP
```



### 3. Debes realizar dos cambios críticos:

#### 1. **Cambiar la IP del Server:**

Busca la línea que dice `server: https://127.0.0.1:6443` y cámbiala por la IP que tiene la **WAN de tu pfSense** (o la IP interna del Master `10.0.0.10` si configuraste la ruta estática o estás en la misma red).
    
    - _Ejemplo:_ `server: https://192.168.1.X:6443` (la IP de la WAN).

```bash
sed -i 's/127\.0\.0\.1/192.168.1.2/g' kubeconfig-10-0-0-100
```

**Verificar**

```bash
grep "server" kubeconfig-10-0-0-100

     5:	   server: https://192.168.1.2:6443
```



#### 2. **Verificar el Puerto:**
Si hiciste un **Port Forward** en el pfSense del puerto `6443` externo al `6443` interno del Master, asegúrate de que coincida en la URL.



## Asociar el archivo a la variable de entorno KUBECONFIG

Copia el contenido y agrégalo en tu archivo local (normalmente ubicado en `~/.kube/config`) o copia en mismo archivo asi:

```bash
cp ~/homelab/re-install-k3s/kubeconfig-10-0-0-100 ~/.kube/

```

**Ajustar en `~/.zshrc` o `~/.bashrc`**


```bash
export KUBECONFIG=~/.kube/kubeconfig-10-0-0-100
```

 **Mi configuracion:**
 
```bash
# k8s-master en la red 192.168.1.0/24
# export KUBECONFIG=~/.kube/config

# k3s master en la red 10.0.0.0/24
export KUBECONFIG=~/.kube/kubeconfig-10-0-0-100
# IP 10.10.10.100
# IP del Firewall: 192.168.1.2
```


### Prueba de conexión

Una vez guardado el archivo en tu PC, verifica la conexión:

```bash
kubectl get nodes
```


####  El problema del Certificado (TLS)

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

```bash
ss -tnlp | grep :6443
LISTEN 0      4096               *:6443             *:*   
```


#### Opción B: La forma rápida (Omitir verificación)

Si es un entorno de laboratorio y quieres probar ya mismo, puedes decirle a `kubectl` que ignore la validación del certificado (aunque no se recomienda para producción):

Busca en tu archivo `config` local la sección del clúster y añade `insecure-skip-tls-verify: true`, pero **debes borrar** la línea de `certificate-authority-data`.

Quedaría algo así:

```yaml
clusters:
- cluster:
    insecure-skip-tls-verify: true
    server: https://192.168.1.X:6443
  name: default
```

### Prueba de conexión

Una vez guardado el archivo en tu PC, verifica la conexión:

```bash
kubectl get nodes
```

Si todo está bien configurado en el **Port Forward** de pfSense y el `kubeconfig` apunta a la IP correcta, deberías ver tus tres nodos (Master y los 2 Workers) con sus nuevas IPs del rango `10.0.0.x`.


**Resultado:**

```bash
❯ kubectl get nodes       
NAME         STATUS     ROLES           AGE     VERSION
k3s-master   Ready      control-plane   3h27m   v1.34.6+k3s1
k8s-master   NotReady   control-plane   3d3h    v1.34.6+k3s1
worker1      Ready      <none>          3d1h    v1.34.6+k3s1
worker2      Ready      <none>          3d1h    v1.34.6+k3s1
```

¡Ya casi lo tienes! Esa salida es muy reveladora. Lo que estamos viendo es un "clúster fantasma" debido al cambio de nombre o de IP.

### El diagnóstico:

1. **`k3s-master` (Ready):** Este es tu nodo actual con la nueva configuración.
    
2. **`k8s-master` (NotReady):** Este es el registro antiguo del master (con el nombre anterior o la IP vieja). Kubernetes piensa que es un nodo distinto que se ha desconectado.
    
3. **`worker1` y `worker2` (Ready):** ¡Buenas noticias! Esto significa que los workers ya encontraron al master en la red `10.0.0.x` y están comunicando correctamente.

### Pasos para limpiar el clúster:

#### 1. Eliminar el nodo antiguo

Como `k8s-master` ya no existe (ahora es `k3s-master`), debes borrarlo lógicamente del clúster para que no ensucie tus comandos de `kubectl`:

```bash
kubectl delete node k8s-master
```

#### 2. Verificar las IPs internas
Para asegurarte de que todo el tráfico fluye por el bridge de pfSense (10.0.0.x) y no hay residuos de la red vieja, ejecuta:

```bash
kubectl get nodes -o wide
```

**Resultado:**

```bash
kubectl get nodes -o wide
NAME         STATUS   ROLES           AGE     VERSION        INTERNAL-IP   EXTERNAL-IP   OS-IMAGE             KERNEL-VERSION      CONTAINER-RUNTIME
k3s-master   Ready    control-plane   3h30m   v1.34.6+k3s1   10.0.0.100    <none>        Ubuntu 24.04.4 LTS   6.8.0-110-generic   containerd://2.2.2-bd1.34
worker1      Ready    <none>          3d1h    v1.34.6+k3s1   10.0.0.101    <none>        Ubuntu 24.04.4 LTS   6.8.0-110-generic   containerd://2.2.2-bd1.34
worker2      Ready    <none>          3d1h    v1.34.6+k3s1   10.0.0.102    <none>        Ubuntu 24.04.4 LTS   6.8.0-110-generic   containerd://2.2.2-bd1.34
```


**Qué revisar en la columna `INTERNAL-IP`:**

- Todos deberían mostrar direcciones `10.0.0.x`.
    
- Si alguno muestra `192.168.1.x`, significa que ese nodo todavía tiene configurada la IP vieja en su configuración de agente de K3s.

### Qué debes observar en la salida:

En la columna **`INTERNAL-IP`**, deberías ver el nuevo esquema que definimos para tu entorno:

- **k3s-master:** `10.0.0.100`
    
- **worker1:** `10.0.0.101`
    
- **worker2:** `10.0.0.102`


