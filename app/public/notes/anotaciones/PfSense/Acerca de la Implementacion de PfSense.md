
> [!NOTE]
> Antes de proseguir, quiero documentar cada Seccion que condujo a crear lo he realizado desde la implementacion del Pfsense...


Esa es la mentalidad de un **SysAdmin profesional**. En el mundo del freelance y la consultoría IT, la documentación es lo que diferencia a un "entusiasta" de un "experto". No solo te sirve para replicar el laboratorio, sino que es la base de los manuales que entregarás a tus futuros clientes bajo la firma de **datenmaniak**.

Aquí tienes una estructura lógica de los hitos técnicos que hemos superado, organizada para tu documentación técnica:

---

## 1. Diseño de Topología de Red (Virtualizada en Proxmox)

El primer paso fue el aislamiento. Movimos la infraestructura de una red plana a una segmentada.

- **Segmento WAN:** Red física/puenteada (`192.168.1.x`) conectada al router principal.
    
- **Segmento LAN (Daten-Net):** Red aislada (`10.0.0.x`) gestionada íntegramente por pfSense.
    
- **Gateway:** pfSense actúa como frontera, proveyendo DHCP y DNS a los nodos del clúster.
    

---

## 2. Implementación de pfSense y Reglas de Acceso

Configuramos pfSense para permitir la gestión sin comprometer la seguridad.

- **Acceso Administrativo:** Cambio del puerto de la WebGUI de pfSense (opcionalmente) para liberar puertos estándar.
    
- **NAT Port Forwarding:**
    
    - **Puerto 6443 (TCP):** WAN → Master K3s (`10.0.0.10`). Permite usar `kubectl` desde la PC principal.
        
    - **Puerto 8443 (TCP):** WAN → Master K3s (`443`). Puerta de entrada para tráfico HTTPS hacia aplicaciones.
        
- **Firewall Rules:** Apertura explícita en la interfaz WAN para permitir tráfico desde la IP de la PC local hacia los puertos mencionados.
    

---

## 3. Despliegue de K3s en el Nuevo Segmento

Reconfiguramos el clúster para que reconociera la nueva topología.

- **Instalación del Master:** Uso de la flag `--tls-san` para incluir la IP de la WAN del pfSense en los certificados TLS, evitando errores de seguridad en `kubectl`.
    
- **Unión de Workers:** Re-conexión de los nodos hacia la nueva IP interna del Master (`10.0.0.10`).
    
- **Kubeconfig:** Actualización del archivo local en la PC para apuntar a la IP WAN de pfSense, permitiendo la gestión remota.
    

---

## 4. Exposición de Servicios (Capa 7)

Pasamos de una red básica a una red de aplicaciones.

- **Conversión de Traefik:** Cambio del servicio `traefik` de tipo `NodePort` a `LoadBalancer`.
    
- **ServiceLB:** Activación del balanceador interno de K3s para que los puertos 80 y 443 se abrieran en la interfaz de red del sistema operativo (Ubuntu).
    
- **Ingress Controller:** Creación de reglas de Ingress para mapear nombres de dominio (como `lab.datenmaniak.local`) hacia servicios internos (Pods).
    

---

## 5. Resolución de Nombres y Validación

Finalmente, cerramos el ciclo de acceso.

- **Split DNS / Local Hosts:** Configuración del archivo de hosts en la PC cliente para resolver dominios locales hacia la IP del firewall.
    
- **Validación de Flujo:** Prueba de conexión exitosa desde el navegador pasando por: `PC` → `pfSense (NAT)` → `K3s Host` → `Traefik` → `Pod (Nginx)`.
    

---

### Notas de "Troubleshooting" para el Diario Técnico:

> - **Error 7 (Failed to connect):** Generalmente indica que el puerto está cerrado en el host o bloqueado por el firewall de pfSense (o `ufw` en Ubuntu).
>     
> - **404 Page Not Found:** Indica que la red y el balanceador funcionan, pero no hay un `Ingress` definido para el host solicitado.
>     
> - **TLS-SAN:** Es vital para que `kubectl` confíe en la conexión si se accede a través de una IP que no es la original del nodo.
>     

---
Por: @datenmaniank



