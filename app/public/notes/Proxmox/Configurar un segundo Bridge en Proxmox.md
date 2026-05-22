
Configurar un segundo Bridge es un proceso sencillo, pero requiere cuidado para no perder acceso administrativo al nodo de Proxmox. Aquí tienes los pasos detallados para crear la red interna y preparar el terreno para tu Gateway.

### Paso 1: Crear el Bridge Interno (LAN)

Este puente actuará como el switch virtual donde se conectarán todas tus VMs.

1. Entra en la interfaz web de Proxmox.
    
2. En el menú de la izquierda, selecciona tu **Nodo** (ej. _pve_).
    
3. Ve a la pestaña **System** > **Network**.
    
4. Haz clic en **Create** > **Linux Bridge**.
    
5. En el cuadro de diálogo:
    
    - **Name:** `vmbr1` (se asigna automáticamente).
        
    - **IPv4/CIDR:** Déjalo **vacío** (pfSense se encargará de gestionar las IPs aquí).
        
    - **Gateway (IPv4):** Déjalo **vacío**.
        
    - **Bridge ports:** **MUY IMPORTANTE:** Déjalo **vacío**. No debe tener ninguna tarjeta física asociada.
        
    - **Comment:** Red Interna LAN.
        
6. Haz clic en **Create**.
    
7. Para que los cambios tengan efecto, haz clic en el botón **Apply Configuration** en la parte superior (esto evita tener que reiniciar el servidor).
    

---

### Paso 2: Configurar el Hardware de la VM (pfSense)

Ahora que tienes los dos "cables" (`vmbr0` y `vmbr1`), hay que conectarlos a tu router virtual.

1. Crea una nueva VM para pfSense (o ve a la configuración de la que ya tengas).
    
2. En la pestaña **Network**, asegúrate de tener dos dispositivos:
    
    - **net0:** Bridge `vmbr0`, Model `VirtIO (paravirtualized)`. (Esta será la **WAN**).
        
    - **net1:** Bridge `vmbr1`, Model `VirtIO (paravirtualized)`. (Esta será la **LAN**).
        

---

### Paso 3: Asignación de Interfaces en pfSense

Una vez que arranques la VM de pfSense por primera vez, la consola te preguntará por la asignación de interfaces:

1. **VLANs:** Normalmente dile que **No** (n).
    
2. **WAN Interface:** Selecciona `vtnet0` (que corresponde al `net0` de Proxmox / `vmbr0`).
    
3. **LAN Interface:** Selecciona `vtnet1` (que corresponde al `net1` de Proxmox / `vmbr1`).
    
4. Finaliza la configuración.
    

Por defecto, pfSense asignará la IP `192.168.1.1` a la LAN. Si esa IP entra en conflicto con tu router real, cámbiala desde la consola de pfSense (opción 2) a algo como `10.0.0.1`.

---

### Paso 4: Conectar tus VMs a la nueva red

Para cada VM que ya tengas creada o las nuevas que generes:

1. Ve al hardware de la VM.
    
2. Edita el **Network Device**.
    
3. Cambia el Bridge de `vmbr0` a **`vmbr1`**.
    
4. Reinicia la red de esa VM.
    

### Resultado Final

Ahora, cuando crees una VM, pfSense detectará la nueva conexión en `vmbr1`, le asignará una IP privada mediante su propio DHCP y le dará salida a internet. Tu router físico solo verá una conexión activa: la IP que le entregó a la interfaz WAN del pfSense.

**Nota sobre la latencia:** Al estar todo en `vmbr1`, verás que la comunicación entre máquinas es extremadamente rápida, ya que el tráfico se conmuta por software dentro del kernel de Proxmox sin tocar el hardware de red.