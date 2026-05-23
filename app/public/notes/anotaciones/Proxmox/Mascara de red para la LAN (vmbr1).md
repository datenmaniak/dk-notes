
Para el rango que hemos elegido (**10.0.0.1**), la recomendación estándar y más equilibrada para un entorno de laboratorio es una **máscara de 24 bits**.

Aquí tienes los detalles técnicos para que los ingreses en el asistente de OPNsense:

### La recomendación: `/24` (o `255.255.255.0`)

- **CIDR:** `/24`
    
- **Máscara Decimal:** `255.255.255.0`
    
- **Rango de IPs:** Desde `10.0.0.1` hasta `10.0.0.254`.
    
- **Capacidad:** Permite hasta **254 dispositivos** conectados simultáneamente en ese bridge.
    

---

### ¿Por qué esta y no otra?

Como experto en sistemas, podrías sentir la tentación de usar una `/8` (la máscara natural de la Clase A), pero en un entorno virtualizado con Proxmox y K3s, la `/24` es superior por estas razones:

1. **Legibilidad:** Es extremadamente fácil saber qué IPs están libres. Las IPs de red terminan en `.0` y el broadcast en `.255`.
    
2. **Segmentación DevOps:** Si en el futuro tu marca **datenmaniak** crece y necesitas separar el tráfico (por ejemplo, una red para bases de datos y otra para el clúster), puedes usar `10.0.1.0/24`, `10.0.2.0/24`, etc. Esto mantiene el ruteo limpio y profesional.
    
3. **Compatibilidad con K3s:** Kubernetes suele usar rangos internos para los Pods y Servicios (como `10.42.0.0/16`). Al usar una máscara pequeña (`/24`) para los "nodos físicos", evitas solapamientos accidentales con la red interna del clúster.
    

---

### Configuración en el Wizard de OPNsense

Cuando llegues a la parte de **LAN Interface Configuration**:

1. **IPv4 Address:** `10.0.0.1`
    
2. **Subnet Mask:** Selecciona **24** en el desplegable.
    
3. **Gateway:** Deja este campo **vacío** (La LAN no necesita gateway porque ella _es_ el gateway).
    

### Un pequeño consejo para tu DHCP

Ya que vas a usar IPs estáticas para tus nodos de Kubernetes, te sugiero configurar el rango de DHCP de OPNsense así:

- **Range Start:** `10.0.0.100`
    
- **Range End:** `10.0.0.200`
    

Esto te deja las IPs de la **.2 a la .99** libres para asignarlas manualmente a tus servidores de Proxmox sin que el DHCP se las dé por error a otro dispositivo.

---



Aquí tienes los pasos exactos para que no haya pérdida de conectividad:

### 1. Asignación de Interfaces (Opción 1)

Selecciona la **opción 1** en el menú de la consola.

- **VLANs:** Escribe `n` (no las necesitamos por ahora).
    
- **WAN Interface:** Escribe `vtnet0` (vinculada a tu `vmbr0`).
    
- **LAN Interface:** Escribe `vtnet1` (vinculada a tu `vmbr1`).
    
- **Optional Interfaces:** Deja en blanco (presiona Enter).
    
- **¿Deseas proceder?:** Escribe `y`.
    

### 2. Configuración de IPs (Opción 2)

Selecciona la **opción 2** para configurar las direcciones.

**Para la WAN:**

1. Selecciona la interfaz **WAN**.
    
2. **DHCP para IPv4:** Escribe `y` (esto permitirá que tu router principal le asigne una IP y tenga salida a internet).
    
3. **IPv6:** Escribe `n` o déjalo en automático si no lo usas.
    

**Para la LAN (El corazón de tu lab):**

1. Selecciona la interfaz **LAN**.
    
2. **IP Address:** Escribe `10.0.0.1`.
    
3. **Subnet Mask (CIDR):** Escribe `24`.
    
4. **Gateway:** Presiona **Enter** (no pongas nada aquí, la LAN no tiene gateway propio).
    
5. **DHCP Server:** Escribe `y`.
    
    - **Range Start:** `10.0.0.100`
        
    - **Range End:** `10.0.0.200`
        
6. **Web GUI Protocol:** Escribe `y` (para usar HTTPS).
    

### 3. El acceso administrativo (Paso Vital)

Por seguridad, OPNsense bloquea el acceso a la interfaz web desde la WAN. Como tu PC está en la red de la WAN:

1. Elige la **opción 8 (Shell)** en la consola.
    
2. Escribe el comando: `pfctl -d`
    
    - _Esto desactivará el firewall temporalmente._
        
3. Ahora, desde el navegador de tu PC, intenta entrar a la **IP que OPNsense obtuvo en la WAN** (la verás en el menú principal de la consola).
    

**Una vez dentro de la interfaz web:** Ve a **Firewall -> Rules -> WAN** y crea una regla temporal que permita el tráfico al puerto 443 desde tu IP específica, o simplemente termina de configurar la LAN para entrar desde una VM interna.