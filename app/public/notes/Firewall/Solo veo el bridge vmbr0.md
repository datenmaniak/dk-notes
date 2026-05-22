
> [!NOTE]
> Exactamente. Si OPNsense solo ve un dispositivo, es porque la VM solo tiene una "tarjeta de red" conectada. Aunque hayas creado el `vmbr1` en Proxmox, tienes que **"enchufar"** virtualmente ese cable a la VM de OPNsense.

Sigue estos pasos en la interfaz de **Proxmox**:

### 1. Añadir la interfaz a la VM

1. Selecciona la VM de OPNsense en el menú de la izquierda.
    
2. Ve a la pestaña **Hardware**.
    
3. Haz clic en **Add** > **Network Device**.
    
4. En la ventana que aparece:
    
    - **Bridge:** Selecciona **`vmbr1`**.
        
    - **Model:** Asegúrate de que sea **`VirtIO (paravirtualized)`**.
        
    - **Firewall:** Desmarca esta casilla (importante para que OPNsense gestione todo el tráfico).
        
5. Haz clic en **Add**.
    

---

### 2. Actualizar OPNsense (Sin reiniciar)

Ahora vuelve a la interfaz web de OPNsense (o a la consola si perdiste el acceso web):

1. Ve a **Interfaces** -> **Assignments**.
    
2. Ahora deberías ver un nuevo dispositivo en el desplegable (probablemente **`vtnet1`**).
    
3. Asigna esa `vtnet1` a la **LAN**.
    
4. Dale a **Save**.
    

### 3. El último paso de "Seguridad"

Si estás intentando configurar OPNsense desde tu PC (a través de la IP de la WAN), OPNsense bloquea por defecto el acceso administrativo desde la WAN por seguridad.

**Si te quedas bloqueado fuera:** Entra a la **Console** de la VM en Proxmox (la pantalla negra) y elige la **opción 8 (Shell)**. Escribe esto para desabilitar el firewall temporalmente y poder entrar desde tu red actual:

#### Firewall en FreeBSD :

**En la consola del firewall pfSense o OPNsense**:

```bash
pfctl -d
```


Esto te dará "vía libre" para entrar desde tu navegador habitual a la IP de la WAN de OPNsense, terminar de configurar la LAN y luego volver a activar el firewall.

**¿Ya te aparece el segundo dispositivo en Hardware?**