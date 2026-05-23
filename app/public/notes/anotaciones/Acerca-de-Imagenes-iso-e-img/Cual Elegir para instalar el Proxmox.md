<img src="https://r2cdn.perplexity.ai/pplx-full-logo-primary-dark%402x.png" style="height:64px;margin-right:32px"/>

# Para instalar OPNsense en Proxmox, es necesario convertir de antemano la imagen .img en .iso ?

No, no es estrictamente necesario convertir la imagen `.img` de OPNsense a `.iso` para instalarla en Proxmox, ya que existen dos métodos principales: uno usando la ISO oficial (DVD) y otro importando directamente la imagen `.img` como disco virtual.[^1_1][^1_2]

## Método con ISO (DVD)

Descarga la versión "DVD" desde opnsense.org/download/, que ya viene en formato `.iso`. Súbela al almacenamiento ISO de Proxmox (en `local` > ISO Images > Upload) y crea una VM seleccionando "Use CD/DVD disc image file (iso)" en la pestaña OS.[^1_6][^1_1]

## Método con imagen .img

Descarga la imagen `.img.bz2` (USB/memstick), descomprime con `bunzip2`, redimensiona si es necesario (`qemu-img resize`), e importa como disco con `qm importdisk <VM_ID> <imagen.img> <storage>`. Luego configura la VM para bootear desde ese disco virtio.[^1_2]

El método ISO es más simple para principiantes, mientras que el `.img` directo es común para instalaciones optimizadas en Nano o mini.[^1_3][^1_2]


### Que hice en Proxmox?

Habiendo copiado en el `Storage`, la imagen original: **OPNsense-26.1.6-vga-amd64.img**

En mi caso: 

```bash
 cd /mnt/archives/template/iso/
```

**1. Configurar la imagen**

```bash
  qm create 9001 --name "opnsense-base" --memory 3096 --core 2 --net0 virtio,bridge=vmbr0
  qm import 9001  OPNsense-26.1.6-vga-amd64.img local-lvm
  qm importdisk 9001  OPNsense-26.1.6-vga-amd64.img local-lvm
  lsblk
  qm set 9001 --scsihw virtio-scsi-pci --scsi0 local-lvm:vm-9001-disk-0
  qm set 9001 --boot c --bootdisk scsi0
  qm set 9001 --agent enabled=1
  qm set 9001 --serial0 socket --vga serial0
  qm set 9001 --ipconfig0 ip=dhcp
  qm start 9001
  qm guest cmd 9001 network-get-interfaces | grep -i "ip-address"
  qm stop 9001
  qm start 9001
```


**2. Iniciar en la VM **

- 2.1  Voy a Proxmox, y hago las prueba
- 2.2   Configuro la Interfaz

**3. Comprobación**

Básicamente, probar que arranca y se presentan las opciones de configuración.

**4.  Convertir en Template**

```bash
qm template 9001
```


