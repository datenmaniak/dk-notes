
Para que tu primera máquina no sea solo una instalación manual, sino el primer ladrillo de tu infraestructura como código, vamos a hacerlo con "mentalidad DevOps".

El objetivo no es solo tener una VM corriendo, sino crear el **molde (Template)** que usarás para clonar todo lo demás.

Aquí tienes la hoja de ruta de tareas:

---

### Tarea 1: Configuración de los Repositorios (CLI)

Nada más instalar, Proxmox viene configurado para clientes de pago. Debes habilitar el repositorio gratuito para poder instalar paquetes y actualizaciones de seguridad.

1. Entra por SSH a tu Proxmox o usa la consola web.
    
2. Edita el archivo: `nano /etc/apt/sources.list.d/pve-enterprise.list` y comenta la línea con un `#`.
    
3. Añade el repositorio gratuito en `/etc/apt/sources.list`:
    
    
    ```bash
    deb http://download.proxmox.com/debian/pve bookworm pve-no-subscription
    ```
    
#### Ajustar este **(principal)**

```bash
neovim  /etc/apt/sources.list.d/pve-enterprise.sources 
```

```bash

#enterprise
Types: deb
URIs: https://download.proxmox.com/debian/pve
Suites: trixie
Components: pve-no-subscription
Signed-By: /usr/share/keyrings/proxmox-archive-keyring.gpg
Enabled: false
```


### Actualiza repos

```bash
apt update && apt upgrade
```


### Tarea 2: Descarga de la Imagen Cloud e Instalación de Herramientas

En lugar de una ISO normal, vamos a usar una **Cloud Image** (Ubuntu o Debian). Estas imágenes están diseñadas para ser automatizadas y no tienen instalador gráfico.

- Descarga la imagen directamente al almacenamiento de Proxmox.
    
- Instala `libguestfs-tools` en el host de Proxmox para poder personalizar la imagen antes de convertirla en plantilla.


### Tarea 3: Creación de la VM "Base" y Configuración de Cloud-Init

Esta es la tarea técnica más importante. Vas a crear una VM que servirá de referencia:

1. **Asignación de hardware:** Dale valores modestos (1 CPU, 1GB RAM) ya que es solo una base.
    
2. **Añadir el Drive de Cloud-Init:** Proxmox usa un "disco virtual" especial para pasarle a la VM tu usuario, contraseña y **llaves SSH** (usa las que ya gestionas con tu sistema actual).
    
3. **Habilitar el QEMU Guest Agent:** Esto es vital para que Proxmox y la VM se comuniquen correctamente (para ver la IP desde la interfaz, por ejemplo).

### Tarea 4: El "Sellado" de la Plantilla (Template)

Una vez que la VM base está configurada con Cloud-Init:

1. Limpia los identificadores de red y logs (para que al clonarla no haya conflictos de IP).
    
2. Haz clic derecho en la VM y selecciona **"Convert to Template"**.

### Tarea 5: El primer despliegue con Terraform

Aquí es donde ocurre la magia. En lugar de clonar a mano:

1. Crea un directorio en tu laptop: `~/infra-proxmox`.
    
2. Crea un archivo `main.tf`.
    
3. Define el proveedor de Proxmox y apunta a la plantilla que acabas de crear.
    
4. Ejecuta `terraform apply`.

---
### ¿Por qué este orden?

Si instalas una VM a mano con una ISO, habrás aprendido a instalar un sistema operativo (algo que ya sabes hacer). Si sigues estas tareas, habrás creado un **pipeline de infraestructura**.

**Mi recomendación:** Empecemos por la **Tarea 1 y 2**. ¿Ya tienes acceso a la interfaz web de Proxmox? Si es así, podemos descargar la imagen de **Ubuntu 24.04 Cloud Image** o **Debian 12** para empezar a preparar ese molde.
