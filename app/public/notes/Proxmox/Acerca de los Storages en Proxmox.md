
He montado las dos particiones del HDD, asi: `/mnt/databases` y `/mnt/archives`. 

Esta ultima con los tipos de content: Backup, Disk images e ISO Images. Al agregar como 'storage' en el Proxmox, se crearon los directorios: dump, images y template.

## Que significa esto?

Lo que acabas de observar es la estructura de directorios estándar de Proxmox (**PVE Storage Layout**). Al añadir un almacenamiento de tipo "Directorio", Proxmox organiza los archivos de forma estricta para saber exactamente dónde buscar cada cosa.

Aquí tienes qué significa cada carpeta que se creó en `/mnt/archives` y cómo se relacionan con las tareas que hicimos:

### 1. Entendiendo la estructura de `/mnt/archives`

- **`template/iso/`**: Aquí es donde debes mover (o descargar) las imágenes `.iso`. Si quieres instalar algo de forma tradicional, Proxmox solo buscará los archivos aquí.
    
- **`template/cache/`**: (Solo si habilitas contenedores) Aquí se guardan las plantillas de **LXC** (como las de Alpine, Debian o Ubuntu para contenedores ligeros).
    
- **`images/`**: Aquí Proxmox guardará los discos virtuales (`.qcow2` o `.raw`) de las VMs que decidas alojar en el HDD. Cada VM tendrá su propia subcarpeta con su ID (ej. `images/9000/`).
    
- **`dump/`**: Aquí es donde se guardarán los archivos comprimidos (`.vma.zst`) cuando realices un **Backup** de tus máquinas. Es vital que esto esté en el HDD y no en el SSD.

### 2. Preparación para "debajo del capó" (Tarea 2 ampliada)

Como vamos a trabajar desde la consola para crear la plantilla, vamos a mover la imagen que descargamos (o descargarla directamente) al lugar correcto para que Proxmox la gestione.

Si descargaste la imagen de Ubuntu en la Tarea anterior, muévela a la carpeta de imágenes de disco para que sea persistente:

```bash
# O descargar directamente al HDD para no ocupar espacio en el SSD
cd /mnt/archives/template/iso
wget https://cloud-images.ubuntu.com/noble/current/noble-server-cloudimg-amd64.img
```

### ¿Qué sigue con `/mnt/databases`?

Esa partición la dejaremos "virgen" por ahora. No la agregues como storage de Proxmox desde la GUI si tu intención es usarla para **passthrough** o como montaje directo dentro de las VMs (vía NFS o Bind-mount).

En el mundo DevOps, es mejor que Kubernetes o Docker gestionen esa partición como un **volumen persistente** para que tus bases de datos escriban directamente ahí.