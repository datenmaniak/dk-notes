
>[!NOTE]
> 
> Te comento que he desistido de usar OPNsense. Es pesado y por alguna razon no mantiene la configuracion de red y se bloquea la salida del router principal.
> 

### Proseguimos con PfSense... estoy en plena configuración de red.


¡Entendido! Al final del día, la mejor herramienta es la que funciona para **tu** flujo de trabajo. Aunque OPNsense es más moderno visualmente, **pfSense** es una roca en cuanto a estabilidad y manejo de configuración en disco (especialmente si hubo algún problema de escritura con ZFS en la prueba anterior).

Como ya estás en plena configuración de red de **pfSense**, aquí tienes los puntos clave para que esta vez quede blindado y no interfiera con tu clúster de **K3s**:

### 1. Asignación de Interfaces (Consola)

Asegúrate de que el orden sea el correcto para tus puentes de Proxmox:

- **WAN** -> `vtnet0` (Conectada a `vmbr0`).
    
- **LAN** -> `vtnet1` (Conectada a `vmbr1`).
    

### 2. Configuración de la LAN (La red 10.0.0.1/24)

Cuando pfSense te pregunte los detalles de la LAN:

1. **IP Address:** `10.0.0.1`
    
2. **Subnet Mask:** Selecciona `24`.
    
3. **Gateway:** Déjalo en **blanco**.
    
4. **DHCP:** Dile que **SÍ**. Define el rango (ej. `10.0.0.100` a `10.0.0.200`).
    

### 3. Evita el "Bloqueo" de entrada

pfSense tiene una regla de seguridad muy estricta llamada **"Block private networks"** en la WAN. Como tu PC administrativo está en la `192.168.1.x`, pfSense considerará a tu propio PC como una "amenaza externa" y no te dejará entrar a la web GUI por la IP de la WAN.

**Para entrar a configurarlo desde tu PC actual:**

1. En la consola de pfSense (pantalla negra), elige la **opción 8 (Shell)**.
    
2. Escribe: `pfctl -d` (esto desactiva el filtro temporalmente).
    
3. Entra por navegador a la IP de la WAN.
    
4. **Inmediatamente**, ve a `Interfaces` -> `WAN` y, al final de la página, **desmarca** las opciones:
    
    - _Block private networks and loopback addresses_
        
    - _Block bogon networks_
        
5. Dale a **Save** y **Apply**. Ahora ya puedes volver a activar el firewall y no te echará fuera.
    

### 4. Ajuste para Virtualización (Proxmox)

Para que pfSense no te de problemas de velocidad o paquetes perdidos bajo Proxmox, ve a: **System > Advanced > Networking** y **marca** estas tres casillas:

- [x] _Disable hardware checksum offload_
    
- [x] _Disable hardware TCP segmentation offload_
    
- [x] _Disable hardware large receive offload_
    

¿Ya lograste ver el Dashboard de pfSense en el navegador? Avisame cuando lo tengas y te ayudo a preparar el primer "Forward" para que tus nodos de K3s (que pondremos en el `vmbr1`) puedan salir a internet.



