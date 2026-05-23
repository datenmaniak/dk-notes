

> [!NOTE]
> Probado con exito el acceso SSH a pfSense a traves de la primera regla. Te hago la aclaratoria.... antes de instalar el firewall se habia instalado el Master de k3s y dos workers... cada uno en una VM separada..


¡Entendido! Ese es un detalle crítico. Básicamente, acabas de "soltar" un muro de hormigón (pfSense) en medio de una infraestructura que ya estaba hablando entre sí, probablemente en el rango `192.168.1.x`.

Para que tu clúster de **k3s** vuelva a la vida bajo la nueva red de **datenmaniak**, tienes dos opciones: una "quirúrgica" y otra "limpia".

---

### El Problema Técnico

K3s guarda la IP del Master en sus certificados y en la configuración de los workers (`K3S_URL`). Si las VMs cambiaron de la red `192.168.1.x` a la `10.0.0.x`, los workers estarán intentando conectarse a una dirección que ya no existe o que pfSense está bloqueando.

### Opción A: La vía "DevOps" (Recomendada)

Dado que son VMs minimalistas y ya tienes el flujo de trabajo claro, lo más sano es **reinstalar los agentes** para que se vinculen a la nueva IP del Master en la LAN de pfSense.

1. **En el Master:**
    
    - Cambia su red a `vmbr1` en Proxmox.
        
    - Asegúrate de que tenga una IP fija (vía DHCP Static Lease en pfSense, ej: `10.0.0.10`).
        
    - Reinicia el servicio: `sudo systemctl restart k3s`.
        
    - Obtén el nuevo token: `sudo cat /var/lib/rancher/k3s/server/node-token`.
        
2. **En los Workers:**
    
    - Cambia su red a `vmbr1`.
        
    - Ejecuta el script de instalación apuntando a la **nueva IP** del Master:
    - 
```bash
        curl -sfL https://get.k3s.io | K3S_URL=https://10.0.0.10:6443 K3S_TOKEN=TU_NUEVO_TOKEN sh -
```
        

---

### Opción B: Reparación Manual (Si no quieres reinstalar)

Si tienes configuraciones complejas dentro de los nodos que no quieres perder, debes editar los archivos de configuración de los workers:

1. Edita el archivo de servicio o el archivo de configuración en `/etc/rancher/k3s/config.yaml` (si existe).
    
2. Busca la línea `server: https://192.168.1.xxx:6443`.
    
3. Cámbiala por la nueva IP interna: `server: https://10.0.0.10:6443`.
    
4. Reinicia el agente: `sudo systemctl restart k3s-agent`.
    

---

### ⚠️ El ajuste necesario en pfSense para K3s

Kubernetes necesita que el tráfico entre nodos fluya sin restricciones. Asegúrate de que en **Firewall > Rules > LAN**, la regla por defecto ("Default allow LAN to any rule") esté activa.

**Un consejo clave:** Como tu `kubeconfig` en tu PC personal ahora apuntará a la IP de la WAN de pfSense, necesitarás hacer un **Port Forward** del puerto **6443** (Kubernetes API) en pfSense hacia la IP interna del Master (`10.0.0.10`).

¿Qué IPs tienen ahora mismo los nodos en la nueva red de pfSense? ¿Ya lograste que el Master responda al comando `kubectl get nodes`?


> [!NOTE]
> Las IP fueron asignadas de manera estatica, asi: 192.168.1.100 para el k8s-master, 192.168.1.101 para el worker1 y 192.168.1.102 para el worker2.... 
> 
> Que hacemos ahora?



Como las IPs están configuradas de forma estática dentro de los archivos del sistema operativo de cada VM, ahora mismo tienes un **conflicto de direccionamiento**: tus nodos creen que pertenecen a la red `192.168.1.x`, pero están conectados a un bridge (`vmbr1`) donde pfSense espera que hablen en el rango `10.0.0.x`.

Para que tu marca **datenmaniak** tenga un laboratorio funcional y profesional, debemos migrar esas IPs estáticas al nuevo rango. Aquí tienes el procedimiento paso a paso:

### 1. Cambiar la IP estática en Ubuntu Server (Netplan)

Debes entrar a la consola de cada VM en Proxmox y editar el archivo de red.

1. Edita el archivo de configuración (el nombre puede variar, suele ser `01-netcfg.yaml` o similar):
    
    
    ```bash
    sudo nano /etc/netplan/*.yaml
    ```
    
2. Modifica las direcciones según este nuevo esquema sugerido para mantener el orden:
    
    - **Master:** `10.0.0.10/24`
        
    - **Worker1:** `10.0.0.11/24`
        
    - **Worker2:** `10.0.0.12/24`
        
    - **Gateway:** `10.0.0.1` (la IP de pfSense).
        
    - **DNS:** `10.0.0.1` o `8.8.8.8`.
        
3. Aplica los cambios:
    
    
    ```bash
    sudo netplan apply
    ```
    

---

### 2. Actualizar el "Corazón" de K3s (El Master)

K3s es sensible a los cambios de IP porque genera certificados SSL basados en la IP que tenía al momento de la instalación.

**En el Master:** Al cambiar la IP a `10.0.0.10`, el servicio de K3s podría fallar al validar los certificados antiguos. La forma más rápida de arreglarlo en un entorno de laboratorio es forzar la regeneración de los certificados o reinstalar el binario (sin borrar tus datos/pods):

Bash

```
# Reinstalar sobre la marcha para actualizar la IP en el certificado
curl -sfL https://get.k3s.io | sh -s - server --tls-san 10.0.0.10
```

---

### 3. Reconectar los Workers

Los workers están intentando buscar al master en la `192.168.1.100`. Debes redirigirlos a la `10.0.0.10`.

**En cada Worker:** Ejecuta el instalador apuntando al nuevo destino. K3s es lo suficientemente inteligente para detectar que ya está instalado y solo actualizará la ruta del servidor y el token:

Bash

```
# Obtén primero el token en el Master: sudo cat /var/lib/rancher/k3s/server/node-token

# En los Workers:
curl -sfL https://get.k3s.io | K3S_URL=https://10.0.0.10:6443 K3S_TOKEN=TU_TOKEN_AQUI sh -
```

---

### 4. Port Forward en pfSense (Para tu comodidad)

Como ahora los nodos están "escondidos" tras pfSense, para que puedas usar `kubectl` desde tu PC principal o entrar por SSH sin usar la consola de Proxmox, necesitas mapear los puertos:

|Servicio|Puerto Externo (WAN)|IP Destino (LAN)|Puerto Interno|
|---|---|---|---|
|**Kubernetes API**|6443|10.0.0.10|6443|
|**SSH Master**|2210|10.0.0.10|22|
|**SSH Worker1**|2211|10.0.0.11|22|
|**SSH Worker2**|2212|10.0.0.12|22|

Exportar a Hojas de cálculo

### ¿Qué verificar primero?

Empieza por el **Master**. Cambia su IP en Netplan, aplica los cambios y verifica que tenga internet haciendo un `ping google.com`. Una vez que el Master tenga salida a través de pfSense, los workers seguirán el mismo camino mucho más fácil.

¿Te parece bien el esquema de IPs `10.0.0.10`, `.11` y `.12` o prefieres otros números?
