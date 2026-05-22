
Exactamente. Al colocar pfSense en medio, has creado una frontera. Tu PC (en la `192.168.1.0/24`) ahora está en lo que pfSense considera la **WAN** (el exterior), y tus VMs están en la **LAN** (el interior).

Para que la comunicación fluya, tienes dos opciones principales. Dado que eres un profesional de IT y buscas eficiencia, aquí tienes cómo configurarlas:

---

### Opción 1: Port Forwarding (Acceso por puertos distintos)

Es la más común si solo quieres mapear servicios específicos. Como todos los nodos escuchan en el puerto 22, usaremos "puertos de entrada" diferentes en la IP de la WAN de pfSense.

1. Ve a **Firewall > NAT > Port Forward**.
    
2. Haz clic en **Add** (flecha hacia arriba) y crea una regla para el **Master**:
    
    - **Interface:** `WAN`
        
    - **Protocol:** `TCP`
        
    - **Destination port range:** `2210` (Este será el puerto que usarás desde tu PC).
        
    - **Redirect target IP:** `10.0.0.10` (La nueva IP del Master).
        
    - **Redirect target port:** `22`.
        
    - **Description:** `SSH al Master K3s`.
        
3. **Repite** para los workers usando los puertos `2211` para el `.11` y `2212` para el `.12`.
    

**Cómo entrarías desde tu terminal:** `ssh usuario@192.168.1.X -p 2210` (donde la IP es la que tenga la WAN de pfSense).

---

### Opción 2: Abrir el Firewall (Acceso directo a la IP de la LAN)

Si quieres poder hacer `ssh usuario@10.0.0.10` directamente desde tu PC sin mapear puertos, necesitas dos cosas:

#### A. La Regla en pfSense

Por defecto, pfSense bloquea todo lo que entra por la WAN.

1. Ve a **Firewall > Rules > WAN**.
    
2. Añade una regla:
    
    - **Action:** `Pass`
        
    - **Protocol:** `TCP`
        
    - **Source:** `Network` -> `192.168.1.0/24` (Tu red doméstica).
        
    - **Destination:** `LAN net`.
        
    - **Destination Port Range:** `Any` (o `22` para solo SSH).
        

#### B. La "Ruta Estática" en tu PC (O en tu Router Principal)

Tu PC sabe cómo llegar a la `192.168.1.0`, pero no sabe que para llegar a la `10.0.0.0` tiene que pasar por pfSense.

- **En Linux/Mac:** `sudo ip route add 10.0.0.0/24 via [IP_WAN_DE_PFSENSE]`
    
- **En Windows (CMD Admin):** `route add 10.0.0.0 mask 255.255.255.0 [IP_WAN_DE_PFSENSE]`
    

---

### Mi recomendación para "datenmaniak"

Para un laboratorio de **DevOps**, la **Opción 1 (Port Forwarding)** es más limpia porque no necesitas modificar las tablas de rutas de tu sistema operativo anfitrión cada vez que reinicies. Además, te prepara para cuando quieras exponer servicios de tus Pods de Kubernetes (como un Ingress Controller) al exterior.

**Ojo con los "Bogon Networks":** Como mencionamos antes, asegúrate en `Interfaces > WAN` de que la opción **"Block private networks and loopback addresses"** esté **desmarcada**, de lo contrario pfSense ignorará cualquier tráfico que venga de tu red `192.168.1.x`.

¿Cuál de los dos métodos prefieres implementar para empezar a mover los nodos a sus nuevas IPs?

