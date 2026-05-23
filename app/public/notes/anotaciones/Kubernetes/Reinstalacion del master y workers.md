
### **Limpiar antes de re-unir (Recomendado):**


**En los Workers:**

```bash
/usr/local/bin/k3s-agent-uninstall.sh
```

#### Paso A: En el Master

**Detén el servicio:** 
```bash
sudo systemctl stop k3s
```

**Borra los certificados antiguos** (solo la carpeta TLS):

```bash
sudo rm -rf /var/lib/rancher/k3s/server/tls/
```

**Desinstalar**

```bash
/usr/local/bin/k3s-uninstall.sh
```


#### Nueva instalación en el Master

**Ejecuta el instalador con la nueva IP:**

```bash
curl -sfL https://get.k3s.io | sh -s - server --tls-san 10.0.0.10
```

**Obtener el token;**
```bash
sudo cat /var/lib/rancher/k3s/server/node-token
```


### Instalación en los workers

```bash
curl -sfL https://get.k3s.io | K3S_URL=https://10.0.0.10:6443 K3S_TOKEN=TU_TOKEN_NUEVO sh -
```

### Configurar la estacion administrativa de Kubernetes

- **Kubeconfig local:** Si usas `kubectl` en el Master, tendrás que actualizar tu archivo `~/.kube/config` para que apunte a `https://10.0.0.10:6443` en lugar de la IP anterior.



### Obtener el nuevo token

**Acceder al master de Kubernetes**

```bash
sudo cat /var/lib/rancher/k3s/server/node-token > token.txt
```

**Descargar en la estacion de trabajo**

```bash
cd ~/homelab/re-install-k3s
```

**Copia desde el master:**

```bash
scp -P 2210 -i ~/.ssh/datenmaniak datenk@192.168.1.2:~/token.txt .  
```

### **editar el instalador de los workers**

**Pegar el token**

```bash

TOKEN="K10a2...........bf49674d9fa5"

curl -sfL https://get.k3s.io | K3S_URL=https://10.0.0.100:6443 K3S_TOKEN=${TOKEN} sh -
```

### Copiar a los workers

**Se envía la copia del script instalador**

**worker1**

```bash
 scp -P 2211 -i ~/.ssh/datenmaniak re-install-k3s.sh datenk@192.168.1.2:~/
```

**worker2**

```bash
 scp -P 2212 -i ~/.ssh/datenmaniak re-install-k3s.sh datenk@192.168.1.2:~/
```

> [!NOTA]
> Hay una Port forwarding SSH  en el firewall `192.168.1.2`, para cada uno de los puertos asociados al `master`, `worker` y `worker2`.
> 


