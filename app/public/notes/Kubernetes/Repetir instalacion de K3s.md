
> [!NOTE]
> SI antes ya habia sido instalado el master k3s y los dos. Repetir estos pasos de instalación reinstala todo como de cero..?

La respuesta corta es: **No, no necesariamente.**

El instalador de K3s es bastante inteligente. Si detecta que ya hay un binario de K3s y una configuración en `/etc/rancher/k3s/`, se comportará de forma distinta dependiendo de si es el **Master** o el **Worker**.

Aquí te explico exactamente qué sucede en cada caso para que no pierdas tu trabajo:

### 1. En el Master (Server)

Si ejecutas el comando `curl -sfL https://get.k3s.io | sh -s - server ...` sobre una instalación existente:

- **Lo que SÍ sobrevive:** Tus despliegues, Pods, CRDs, ConfigMaps, Secrets y persistencia de datos en `/var/lib/rancher/k3s/storage/`. Estos datos se guardan en la base de datos (SQLite por defecto o etcd) y no se borran al reinstalar el binario.
    
- **Lo que cambia:** El script de instalación actualizará los argumentos del servicio `systemd` con los nuevos parámetros que le pases (como `--tls-san 10.0.0.100`).
    
- **El riesgo:** Los certificados antiguos (`/var/lib/rancher/k3s/server/tls/`) podrían quedar ligados a la IP antigua. Por eso, tras reinstalar, **es muy probable que debas borrar la carpeta `tls/` y reiniciar el servicio** para que K3s regenere los certificados con la nueva IP.
    

### 2. En los Workers (Agents)

Si ejecutas el comando del worker de nuevo:

- **Lo que ocurre:** El script simplemente sobrescribe el archivo de configuración `/etc/rancher/k3s/config.yaml` y la configuración del servicio `systemd`.
    
- **El resultado:** Es **totalmente seguro**. El nodo simplemente dejará de intentar hablar con la IP antigua y empezará a hablar con la nueva IP del Master. No pierde ninguna configuración local crítica.
    

---

### ¿Cómo hacerlo de forma segura y sin destruir tu clúster?

Si quieres mover el clúster a la red `10.0.0.x` sin tener que volver a desplegar tus aplicaciones desde cero, sigue este orden:

#### Paso A: En el Master

1. **Detén el servicio:** `sudo systemctl stop k3s`
    
2. **Borra los certificados antiguos** (solo la carpeta TLS):
    
    
    ```bash
    sudo rm -rf /var/lib/rancher/k3s/server/tls/
    ```
    
3. **Ejecuta el instalador con la nueva IP:**
    
    
    ```bash
    curl -sfL https://get.k3s.io | sh -s - server --tls-san 10.0.0.100
    ```
    
    _Al reiniciarse, K3s detectará que faltan los certificados y los regenerará automáticamente usando la nueva IP como base._
    

#### Paso B: En los Workers

1. **Simplemente actualiza el comando de unión:**
    
    
    ```bash
    curl -sfL https://get.k3s.io | K3S_URL=https://10.0.0.100:6443 K3S_TOKEN=TU_TOKEN_NUEVO sh -
    ```
    
2. Esto actualizará el servicio y el nodo se conectará al Master nuevo automáticamente.
    

---

### Lo que debes tener en cuenta (El "Gotcha")

- **El Token:** Si el instalador del Master regenera el archivo `/var/lib/rancher/k3s/server/node-token` (a veces pasa al regenerar todo), asegúrate de copiar ese **nuevo token** para los workers.
    
- **Kubeconfig local:** Si usas `kubectl` en el Master, tendrás que actualizar tu archivo `~/.kube/config` para que apunte a `https://10.0.0.100:6443` en lugar de la IP anterior.
    

¿Te sientes cómodo haciendo este "re-bind" de los certificados o prefieres que verifiquemos el contenido de alguna carpeta antes de tocar nada? Es un proceso que toma 5 minutos, pero hay que hacerlo en orden.