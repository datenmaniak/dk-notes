
> [!NOTE]
> En relacion al acceso Port Forwarding... Como puedo configurar en el firewall para permitir lo que antes se usaba para conectarse al k8s-master....??  
>  Es decir, antes del firewall, se empleaba: 
>  
>  "ssh -i ~/.ssh/datenmaniak datenk@192.168.1.100" 

Para replicar ese mismo comportamiento a través del firewall sin cambiar tu flujo de trabajo de **datenmaniak**, debes configurar un **Port Forward** en pfSense que actúe como puente.

Dado que la IP `192.168.1.100` ahora es externa al firewall y el nodo master vive en la red interna, el comando cambiará ligeramente de dirección, pero mantendrá la misma seguridad de tu llave SSH.

### 1. Configuración en pfSense (NAT Port Forward)

Sigue estos pasos para mapear el tráfico:

1. Ve a **Firewall** > **NAT** > **Port Forward**.
    
2. Haz clic en **Add** (flecha hacia arriba).
    
3. Configura los siguientes campos:
    
    - **Interface:** `WAN`
        
    - **Protocol:** `TCP`
        
    - **Destination Port Range:** Selecciona `Custom` y escribe `2210` (esto es para evitar conflictos si luego habilitas SSH en el propio pfSense).
        
    - **Redirect Target IP:** `10.0.0.10` (La nueva IP interna de tu Master).
        
    - **Redirect Target Port:** `22` (o `SSH`).
        
    - **Description:** `SSH forward para k8s-master`.
        
4. Haz clic en **Save** y luego en el botón verde **Apply Changes**.
    

### 2. Cómo te conectarás ahora

Una vez aplicada la regla, el comando desde tu terminal será:


```bash
ssh -i ~/.ssh/datenmaniak datenk@IP_WAN_PFSENSE -p 2210
```

_(Donde `IP_WAN_PFSENSE` es la IP que pfSense recibió de tu router principal, probablemente una del tipo 192.168.1.x)._

### 3. Ajuste de SSH Config (Opcional pero recomendado)

Como eres programador y te gusta la eficiencia, puedes editar tu archivo local `~/.config/ssh/config` (o `~/.ssh/config`) para no tener que escribir todo el comando cada vez:

Plaintext

```
Host k8s-master
    HostName IP_WAN_DE_PFSENSE
    User datenk
    Port 2210
    IdentityFile ~/.ssh/datenmaniak
```

Con esto, solo tendrías que escribir: `ssh k8s-master`.

### Consideración Crítica de Seguridad

Para que esto funcione, recuerda lo que mencionamos antes:

1. En **Interfaces** > **WAN**, asegúrate de que **"Block private networks and loopback addresses"** esté **DESMARCADO**. De lo contrario, pfSense rechazará la conexión de tu PC porque viene de una red privada (`192.168.1.0/24`).
    
2. Verifica que en **Firewall** > **Rules** > **WAN** se haya creado automáticamente la regla que permite el tráfico al puerto `2210`.
    

¿Ya tienes identificada la IP que tomó la WAN de pfSense para hacer la primera prueba?

