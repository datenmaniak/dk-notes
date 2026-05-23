Desde la terminal es la mejor forma de entender cómo Proxmox gestiona los recursos de QEMU y cómo se integra con Cloud-Init.

Para estas tareas, usaremos la imagen de **Ubuntu 24.04 (Noble Numbat)**, que es el estándar actual para despliegues DevOps.

Al momento de ejecutar estas tareas, con anticipación se había creado dos particiones en el segundo disco duro **HDD de 500 GB**,  y montadas desde `/etc/fstab`, así:


1)   /mnt/databases 
2)   /mnt/archives

La primera destinada para **persistencia de bases de datos** de los manejadores de bases de datos basado en contenedores. **MariaDB** y **PosgreSQL**

La segunda partición se agrego en Proxmox, via **`Datacenter > Storage`**. Con el ID **archives** y los tipos de **Content**: `Backup`, `Disk images `e `ISO images`.

El objetivo principal de ambas particiones es reducir el consumo de espacio en el disco SSD destinado para el sistema.

### Descarga y preparación de la imagen Cloud

Las imágenes "Cloud" vienen en formato `.img` o `.qcow2`. No se instalan; se "importan" como un disco duro ya listo.

#### Acceder por SSH a tu nodo de Proxmox.

```bash
ssh root@IP_PROXMOX
```

####  Elije la  ruta de descarga

**Opcion A: Ruta por defecto**
```bash
cd /var/lib/vz/template/iso
```

**Opcion B: Ruta personalizada**
```bash
cd /mnt/archives/template/iso/
```

#### Descarga la versión mas reciente de Ubuntu 24.04:

De antemano, entrar al directorio que hayas elegido  y donde Proxmox espera encontrar las imágenes.

**Paso 1: Copia la ruta oficial**

```bash
https://cloud-images.ubuntu.com/noble/current/noble-server-cloudimg-amd64.img
```

**Paso 2: Pega y ejecuta**

En mi caso, elijo la `Ruta Personalizada`

```bash

wget https://cloud-images.ubuntu.com/noble/current/noble-server-cloudimg-amd64.img
```

Otra manera  para descargar la imagen:

```bash
curl -O  https://cloud-images.ubuntu.com/noble/current/noble-server-cloudimg-amd64.img
```


#### Instala las utilidades de disco

Es necesaria para redimensionar la imagen o `inyectar componentes`.

```bash
apt update && apt install libguestfs-tools -y
```

#### Personalizar y Optimizar (Opcional pero profesional)

- Inyectar el agente en la imagen descargada 

		*(ejecutar en el directorio de la imagen)*

```bash
 
virt-customize -a noble-server-cloudimg-amd64.img --install qemu-guest-agent
```

_Nota: Si haces esto, deberás repetir el `qm importdisk` ya que modificaste el archivo `.img` original._

```bash
# 2. Importar la cloud image que descargaste como si fuera un disco duro
qm importdisk 9000 noble-server-cloudimg-amd64.img local-lvm
```
