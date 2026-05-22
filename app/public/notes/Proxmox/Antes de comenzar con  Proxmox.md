
## Hardware disponible

- **Procesador:**  i3-10100
- **RAM:**  16 GB
- **SSD:** 230 GB
- **HDD:** 500 GB

¡Excelente! Tienes un hardware muy capaz para empezar. Ese procesador de 4 núcleos y 8 hilos es más que suficiente para correr varios servicios simultáneos, y los 16 GB de RAM te dan un margen cómodo para un clúster de Kubernetes ligero o varias instancias de desarrollo.

Aquí tienes una guía rápida de cómo organizar ese almacenamiento una vez que termines la instalación, pensando en la eficiencia para DevOps:

### 1. El SSD (Sistema y "Local-LVM")

Proxmox se instalará en el SSD. Por defecto, creará una partición llamada `local-lvm`.

- **Uso ideal:** Guarda aquí los discos de tus VMs que necesiten velocidad (como los nodos de tu clúster de Kubernetes o el sistema operativo de tus bases de datos). Al ser un SSD, el arranque y la lectura/escritura de logs serán inmediatos.

### 2. El HDD de 500GB (Particiones de Datos)

Aquí es donde debes ser estratégico. Proxmox prefiere gestionar discos completos, pero como ya tienes particiones, puedes integrarlas de la siguiente manera:

- **Partición "Databases":** * No la uses para el "binario" de la base de datos (eso ponlo en el SSD).
    
    - Úsala para montar los **volúmenes de datos**. Por ejemplo, si corres MariaDB en un contenedor o VM, mapea el directorio de datos (`/var/lib/mysql`) a esta partición del HDD. Así, si el sistema falla, tus datos están en un disco físico distinto.
        
- **Partición "Datos":**
    
    - **ISO Storage:** Úsala para guardar las imágenes `.iso` de los sistemas operativos que vayas a descargar. No tiene sentido desperdiciar espacio del SSD en archivos instaladores que solo usas una vez.
    - **Backups:** Configura Proxmox para que guarde los snapshots y copias de seguridad de tus VMs aquí. Es la regla de oro: **el backup nunca debe estar en el mismo disco que el sistema vivo**.

### 3. El siguiente paso técnico: "Proxmox Post-Install"

En cuanto termine la instalación y entres a la interfaz web (usualmente `https://tu-ip:8006`), haz lo siguiente:

1. **Actualiza los repositorios:** Como hablamos antes, desactiva el de "Enterprise" y activa el de "No-Subscription".
    
2. **Verifica el soporte de Virtualización:** Asegúrate de que en la BIOS de tu i3 esté activado **Intel VT-x**.
    
3. **Monta tus particiones:** Ve a la sección _Datacenter > Storage_ y añade tus particiones del HDD para que Proxmox las reconozca.
4. 
### Recomendación de "Ingeniero"

Con 16GB de RAM, intenta no asignar más de 2GB a cada VM al principio. En el mundo DevOps, preferimos muchas máquinas pequeñas y especializadas (microservicios) que una sola máquina gigante.