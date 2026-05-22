
He iniciado la instalacion de pfsense mientras estoy en espera de la descarga de OPNsense. Solo para probar hago la instalacion de pfsense. 

¡Perfecto! Hacer una instalación de prueba con **pfSense** es un ejercicio excelente para familiarizarte con la lógica de los puentes (`bridges`) en Proxmox antes de pasar a OPNsense. Como ya tienes el `vmbr1` listo, esto es lo que verás:

### 1. La fase crítica: Asignación de Interfaces

Cuando pfSense termine de cargar el instalador y reinicie, te hará la pregunta del millón en la consola negra:

- **WAN Interface:** Selecciona la que corresponde a `vmbr0` (normalmente `vtnet0`).
    
- **LAN Interface:** Selecciona la que corresponde a `vmbr1` (normalmente `vtnet1`).
    

### 2. Para no chocar con tu red actual (Muy importante)

Como tu red actual es la `192.168.1.0/24`, y pfSense viene por defecto con la `192.168.1.1` en la LAN:

1. En cuanto termine de arrancar, usa la **opción 2** del menú de pfSense (_Set interface(s) IP address_).
    
2. Selecciona la **LAN**.
    
3. Ponle una IP que no use nadie, por ejemplo: **`10.0.10.1`** con máscara **`24`**.
    
4. Dile que **SÍ** quieres activar el servidor DHCP.
    

### 3. El "Salto" para administrarlo

Aquí es donde verás la magia del aislamiento:

- Desde tu PC actual (`192.168.1.x`), **no podrás entrar** a la web de pfSense (`10.0.10.1`).
    
- Para entrar, ve a Proxmox y cambia la red de una VM que no sea crítica al bridge `vmbr1`.
    
- Esa VM recibirá una IP tipo `10.0.10.100` y desde ella ya podrás abrir el navegador y configurar pfSense.
    

---

### Un detalle sobre el rendimiento en Proxmox

Si notas que la red va un poco lenta en esta prueba de pfSense, es porque FreeBSD (la base de pfSense) a veces se pelea con el _Hardware Checksum Offloading_.

Si decides quedarte un rato en pfSense para probar, ve a: `System` > `Advanced` > `Networking` y **marca** las casillas de:

- _Disable hardware checksum offload_
    
- _Disable hardware TCP segmentation offload_
    
- _Disable hardware large receive offload_
    

Esto es un "clásico" cuando corres pfSense/OPNsense virtualizado. ¡Avisa cuando logres que una VM de prueba tenga internet pasando por el pfSense!