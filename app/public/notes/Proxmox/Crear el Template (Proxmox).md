
## Requerimientos 

###  1.  Configuracion de los Repositorios

```bash
# Editar /etc/apt/source.list.d/
```

Cambiar 'pve-enterprise' por 'pve-no-subscription'

### 2. Descarga de la imagen Cloud e instalacion de herramientas

1. Conectarse por SSH al server Proxmox

2. Instalar las utilidades de disco
```bash
apt update && apt install libguestfs-tools -y
```

3. Descargar la imagen oficial en un directorio temporal
4. Mover a la ruta *Ruta de Imagenes* mencionada mas abajo.

```bash
wget https://cloud-images.ubuntu.com/noble/current/noble-server-cloudimg-amd64.img
```

### 3. En caso de tener un disco HDD con una particion dedicada para imagenes ISO

1. En Proxmox, a nivel de Datacenter, buscar *Storage* 
2. Agregar la unidad 
	1. Tipo: Directorio
	2. Path/Target, ej: /mnt/archives
	3. Content:  Backup, ISO image


De ahora en adelante, se descargaran las imagenes en esta ruta:

### 3. Ruta de imagenes:  .img & .iso
```bash
/mnt/archives/template/iso/
```


Ahora que tienes los puntos de montaje listos, ejecuta estos comandos. He ajustado los nombres para que coincidan con tu configuración (`archives` para el Cloud-Init y las ISOs, y el SSD para el disco principal de la VM).

## Paso A: Crear el esqueleto de la VM

```bash
qm create 9000 --name "ubuntu-2404-template" --memory 2048 --cores 2 --net0 virtio,bridge=vmbr0
```


### Paso B: Importar el disco al SSD (para velocidad)

```bash
# Asumiendo que tu SSD en Proxmox se llama 'local-lvm'
qm importdisk 9000 /mnt/archives/template/iso/noble-server-cloudimg-amd64.img local-lvm
```

### Paso C: Configurar Cloud-Init en el HDD *(archives)*

```bash
qm set 9000 --scsihw virtio-scsi-pci --scsi0 local-lvm:vm-9000-disk-0
qm set 9000 --ide2 archives:cloudinit
qm set 9000 --boot c --bootdisk scsi0
qm set 9000 --agent enabled=1
qm set 9000 --serial0 socket --vga serial0
```

### Paso D: Configurar los datos del usuario (Cloud-Init)

Configura tu usuario y llaves. Si no tienes una llave SSH a mano, puedes generarla en tu laptop con `ssh-keygen`.

### 1. Copiar la llave
La llave publica SSH de mi usuario en la laptop que corresponde a la estacion de trabajo que dedico para la administracion de sistemas.

```bash
❯ scp ~/.ssh/id_ed25519.pub root@192.168.1.201:~/
```

### 2. Asociar la llave y el usuario
```bash
qm set 9000 --ciuser willians
qm set 9000 --sshkeys /root/id_ed25519.pub
qm set 9000 --ipconfig0 ip=dhcp
```


### Paso E: Convertir a Plantilla

```bash
qm template 9000
```