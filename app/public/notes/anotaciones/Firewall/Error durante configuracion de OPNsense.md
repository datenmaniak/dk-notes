

Tengo esta notificación dentro de la interfaz web de OPNsense en fase de configuración.  

> [!NOTE]
> Unable to enable this interface as it is not attached to a device, please assign first and run the wizard again.


Ese error es muy común en instalaciones virtualizadas. Lo que te está diciendo OPNsense es que, aunque ha detectado que existen interfaces lógicas (WAN y LAN), no tiene claro a qué "tarjeta física" virtual (`vtnet0`, `vtnet1`) debe amarrar cada una.

Para solucionarlo sin tener que reiniciar todo el asistente (wizard), sigue estos pasos:

### 1. La asignación manual de interfaces

No te preocupes por el asistente ahora, vamos a la raíz del problema:

1. En el menú lateral izquierdo, ve a **Interfaces** -> **Assignments**.
    
2. Verás una lista de "Devices" (probablemente `vtnet0` y `vtnet1`).
    
3. Asegúrate de que:
    
    - **WAN** esté asignada a **`vtnet0`** (que es tu `vmbr0`).
        
    - **LAN** esté asignada a **`vtnet1`** (que es tu `vmbr1`).
        
4. Si alguna aparece como "None" o está vacía, selecciona el dispositivo correcto en el desplegable y haz clic en el botón **"+"** (Add) o simplemente cámbialo y dale a **Save**.
    

### 2. Habilitar la interfaz

Una vez asignadas en esa pantalla, debes "encenderlas":

1. Ve a **Interfaces** -> **[LAN]**.
    
2. Marca la casilla que dice **Enable Interface**.
    
3. Aprovecha para bajar un poco en esa misma página y verificar que en **IPv4 Configuration Type** diga **Static IPv4**.
    
4. Más abajo, en **IPv4 Address**, asegúrate de que esté la IP que elegimos (ej. `10.0.0.1`) y la máscara `/24`.
    
5. Haz clic en **Save** al final de la página y luego en **Apply Changes** en la franja superior que aparecerá.
    

### 3. ¿Por qué pasó esto?

A veces, al instalar en Proxmox, el sistema detecta las tarjetas de red pero espera a que tú confirmes cuál es cuál para evitar que "abras la puerta" equivocada.

**Nota importante:** Si después de hacer esto pierdes conexión, recuerda que para acceder a la IP `10.0.0.1` (LAN) **debes estar dentro de una VM que esté conectada al bridge `vmbr1`**. Tu PC principal no verá esa interfaz a menos que crees una regla de firewall específica o un salto.

¿Ya te permite guardar la configuración de la LAN ahora?
> 
