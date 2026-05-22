
Dame unos momentos... Voy a descargar e instalar. Por cierto, ya he creado el bridge.


Perfecto. Mientras se descarga la ISO y preparas la VM, te dejo los **3 ajustes críticos** que debes hacer en Proxmox y en el instalador para que todo fluya a la primera.

### 1. Configuración de la VM en Proxmox

Cuando crees la VM para OPNsense, asegúrate de esto:

- **CPU:** Usa el tipo **`host`** en lugar de `kvm64`. Esto permite que OPNsense aproveche las instrucciones de tu i3 de 10ª Gen (como AES-NI para cifrado VPN rápido).
    
- **Red (Network):**
    
    - **`net0`** -> `vmbr0` (Tu salida a internet).
        
    - **`net1`** -> `vmbr1` (Tu nueva red interna).
        
    - **Importante:** En ambos, desmarca la casilla **"Firewall"** de Proxmox. No quieres que el firewall de Proxmox filtre el tráfico del firewall de OPNsense.
        

### 2. El truco de la instalación

Cuando inicies el instalador, verás que carga un modo "Live". Para instalarlo realmente en el disco:

- Espera a que aparezca el login y entra con el usuario **`installer`** y contraseña **`opnsense`**. Esto lanzará directamente el asistente de instalación en disco.
    

### 3. Evita el conflicto de IPs (Cuidado aquí)

Por defecto, OPNsense querrá usar la **192.168.1.1** para su red LAN. Como esa es probablemente la IP de tu router real:

1. Al terminar la instalación, en la consola (pantalla negra), elige la **opción 2 (Set interface IP address)**.
    
2. Selecciona la LAN.
    
3. Cámbiala a un rango diferente, por ejemplo: **`10.0.0.1`** con máscara **`/24`**.
    
4. Activa el DHCP cuando te lo pregunte.
    

---

**Tip de profesional:** Una vez que OPNsense esté arriba, no podrás entrar a su interfaz web desde tu PC principal (porque tu PC está en `vmbr0` y la web de gestión está en `vmbr1`).

**Para entrar a configurarlo:** Crea una VM pequeña de Linux (o usa una de tus workers si tiene interfaz gráfica) conectada al `vmbr1`. Desde el navegador de esa VM interna, entra a `https://10.0.0.1`.

¡Avisa cuando estés dentro del dashboard!




> [!NOTE]
> Recuerda que no queremos romper el acceso al cluster de k3s... tanto el master como los workers estan configurados en la red 192.168.1.0/24, y mi estacion de trabajo administrativa tambien en la misma red.


Tienes toda la razón en mantener la cautela. Si cambias las IPs de golpe, el clúster de K3s dejará de comunicarse porque los certificados TLS internos están firmados para las IPs de la red `192.168.1.x`.

Para evitar romper nada y mantener tu acceso administrativo, la estrategia debe ser una **migración transparente**. Aquí tienes el plan de acción para configurar OPNsense sin tocar (aún) tu clúster:

### 1. El OPNsense como "Vecino", no como "Jefe" (Por ahora)

Al instalar OPNsense, no le des la IP `192.168.1.1` en la LAN, porque entraría en conflicto con tu router físico y te quedarías sin internet en toda la casa.

- **WAN (vmbr0):** Deja que obtenga una IP por DHCP de tu router (será algo como `192.168.1.150`).
    
- **LAN (vmbr1):** Asígnale una IP de un rango **completamente distinto**, por ejemplo: **`172.16.0.1/24`**.
    

### 2. Cómo administrar OPNsense sin perder tu red

Como tu estación de trabajo está en la `192.168.1.x` (`vmbr0`), no podrás ver la interfaz de OPNsense en la `172.16.0.1` (`vmbr1`) porque están aisladas. Tienes dos opciones:

- **La opción segura:** Crea una VM de escritorio muy ligera (un Linux con XFCE o incluso un Windows ligero) conectada **únicamente al `vmbr1`**. Úsala como "consola de administración" saltando desde Proxmox.
    
- **La opción avanzada (Acceso Temporal):** En la consola de OPNsense, puedes deshabilitar temporalmente el firewall (`pfctl -d`) para intentar entrar desde la IP de la WAN, pero es más engorroso. **Te recomiendo la VM de administración interna.**
    

---

### 3. El plan para el Clúster K3s

Para no romper el clúster, **no muevas los nodos todavía**. El orden lógico sería:

1. Configurar OPNsense en su red aislada (`vmbr1`).
    
2. Verificar que una VM de prueba en `vmbr1` tiene internet a través de OPNsense.
    
3. **Para el K3s:** Tienes dos caminos:
    
    - **Camino A (Conservador):** Mantener el clúster donde está y usar el OPNsense solo para nuevos proyectos.
        
    - **Camino B (Migración):** Si decides moverlo, tendrás que editar el archivo de configuración de K3s en cada nodo y añadir la opción `--tls-san` con las nuevas IPs antes de cambiar el Bridge, o bien reinstalar el clúster (que teniendo tus manifiestos y siendo K3s, suele ser más limpio).
        

### Resumen de seguridad inmediata:

> [!IMPORTANT] Mientras configuras OPNsense, **no cambies el bridge de tus nodos K3s**. Déjalos en `vmbr0`. Solo cuando OPNsense esté listo y con internet, moveremos una sola VM de prueba al `vmbr1` para testear.

¿Ya tienes la ISO arrancada en la VM? Recuerda entrar como `installer` para empezar.

