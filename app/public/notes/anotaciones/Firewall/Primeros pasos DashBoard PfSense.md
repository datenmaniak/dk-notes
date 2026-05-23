
¡Excelente! Ya tienes el corazón de tu red latiendo. El hecho de que veas ambas interfaces (WAN y LAN) en el Dashboard significa que el "apretón de manos" entre Proxmox y pfSense ha sido exitoso.

Ahora que tienes el control, vamos a dejarlo listo para que tu infraestructura de **K3s** y tu marca **datenmaniak** operen de forma profesional. Aquí tienes los **3 pasos inmediatos** para que todo sea funcional y seguro:

### 1. El ajuste de rendimiento (Vital en Proxmox)

FreeBSD (la base de pfSense) intenta delegar tareas a la tarjeta de red, pero al ser tarjetas virtuales (`virtio`), esto puede causar lentitud extrema.

1. Ve a **System** > **Advanced** > **Networking**.
    
2. Busca la sección **Network Interfaces**.
    
3. **Marca (Check)** las siguientes tres opciones:
    
    - `Disable hardware checksum offload`
        
    - `Disable hardware TCP segmentation offload`
        
    - `Disable hardware large receive offload`
        
4. Baja y dale a **Save**. Te pedirá reiniciar para aplicar, pero puedes esperar al final.
    

### 2. DNS para tus nodos de DevOps

Para que tus futuros workers de Kubernetes resuelvan nombres (como descargar imágenes de Docker Hub), pfSense debe ser un buen servidor DNS.

1. Ve a **Services** > **DNS Resolver** > **General Settings**.
    
2. Asegúrate de que **Enable DNS Resolver** esté marcado.
    
3. En **Outgoing Network Interfaces**, selecciona `WAN`.
    
4. (Opcional) En **System** > **General Setup**, añade los DNS de Google (`8.8.8.8`) o Cloudflare (`1.1.1.1`) para que pfSense sepa a quién preguntar si él no lo sabe.
    

### 3. Prueba de fuego: Conectar una VM al Bridge

Para verificar que el aislamiento funciona:

1. Toma una VM que tengas en Proxmox (preferiblemente una con interfaz gráfica o un Linux simple).
    
2. En la pestaña **Hardware** de la VM, edita la tarjeta de red y cámbiala de `vmbr0` a **`vmbr1`**.
    
3. Inicia la VM. Debería recibir una IP del rango **10.0.0.x** automáticamente.
    
4. Intenta navegar desde esa VM. Si tienes internet, ¡tu firewall está ruteando perfectamente!
    

---

### Siguiente paso: El Clúster de K3s

Una vez que confirmes que una VM en `vmbr1` tiene internet, el siguiente reto es mover tus nodos de Kubernetes.

**¿Quieres que veamos cómo mapear los puertos (NAT) para que puedas seguir accediendo al API de Kubernetes desde tu red principal (192.168.1.x)?**


> [!NOTE]
>  Hice una prueba con un  VM minimalista instalada con un ubuntu server. Obtiene la IP de la LAN y se actualizaron correctamente los repositorios.


¡Excelente noticia! Si el Ubuntu Server pudo actualizar los repositorios (`apt update`), significa que el **NAT (Network Address Translation)** y el **DNS** en pfSense están funcionando a la perfección.

Ya tienes el "túnel" de salida configurado. Ahora, para que tu entorno de **DevOps** sea realmente funcional, necesitamos resolver el problema inverso: **¿Cómo entras tú a esa VM desde tu PC principal?**

Aquí tienes los dos caminos profesionales para manejar tu clúster de **K3s** en esta nueva arquitectura:

### 1. El acceso por Port Forwarding (NAT)

Si quieres usar la terminal de tu PC principal (donde tienes tus dotfiles, Neovim y herramientas de red) para gestionar los nodos, debes mapear los puertos en pfSense.

**Ejemplo para SSH:**

1. Ve a **Firewall** > **NAT** > **Port Forward**.
    
2. Haz clic en **Add**.
    
3. Configura lo siguiente:
    
    - **Interface:** `WAN`
        
    - **Protocol:** `TCP`
        
    - **Destination Port Range:** `2222` (por ejemplo, para no chocar con el SSH del propio pfSense).
        
    - **Redirect Target IP:** `10.0.0.100` (la IP de tu Ubuntu Server).
        
    - **Redirect Target Port:** `22`.
        
4. Dale a **Save** y **Apply Changes**.
    

_Ahora, desde tu PC principal, entrarías con: `ssh usuario@IP_WAN_PFSENSE -p 2222`._

---

### 2. Configuración de una "Static Lease" (IP Reservada)

Como vas a montar un clúster de Kubernetes, es **crítico** que los nodos siempre tengan la misma IP. No queremos que tras un reinicio el Master cambie de la `.100` a la `.105`.

1. En pfSense, ve a **Status** > **DHCP Leases**.
    
2. Busca tu Ubuntu Server en la lista.
    
3. Haz clic en el botón del **botón "+" blanco con fondo azul** (Add Static Mapping).
    
4. Baja hasta el final y dale a **Save**.
    
5. Ahora esa VM siempre tendrá esa IP, lo cual es vital para el archivo `kubeconfig`.
    

---

### 3. El siguiente paso: Preparar K3s

Con internet funcionando y la red estable, ya puedes empezar a clonar esa VM minimalista para crear tus nodos.

**Un consejo de "SysAdmin":** Antes de instalar K3s, asegúrate de que el Ubuntu Server tenga:

- **QEMU Guest Agent** instalado (`sudo apt install qemu-guest-agent`).
    
- **Swap desactivada** (Kubernetes suele preferir que no haya swap para gestionar mejor la memoria).
    
- **Nombre de host único** (ej: `k3s-master`, `k3s-worker-01`).
    

¿Quieres que te ayude con el comando de instalación de K3s optimizado para pasar a través de este firewall?
