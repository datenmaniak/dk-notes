
En Proxmox, cada comando `qm` (Qemu Manager) equivale a una acción en la interfaz web. Vamos a crear la VM con el ID **9000** (usar IDs altos para plantillas es una buena práctica).

> [!Cual es la imagen que se recomienda?]
> **Interrogante de novato:**  Tengo curiosidad acerca de la imagen a utilizar para crear el template. He sabido de Cloud images de Ubuntu. 

Trabajar **exclusivamente por consola** sobre Proxmox y usar **cloud images** es la combinación más profesional y eficiente. Es exactamente lo que hacen los SRE e ingenieros de plataforma para automatizar la creación de clústers.

Aquí tienes la respuesta a tu pregunta y la guía completa.

### 🏆 ¿Cuál es la imagen recomendada?

La respuesta corta es: **`noble-server-cloudimg-amd64.img`**.  
_(`noble` es el nombre en clave de Ubuntu 24.04 LTS)_

**¿Por qué esta y no la ISO normal?** Porque las **Cloud Images** están diseñadas específicamente para la virtualización y la nube [](https://github.com/chichocoria/proyecto_final_cf)[](https://github.com/shreead/ubuntu-cloud-init-docs). Están preconfiguradas para arrancar rápido, son muy ligeras y, lo más importante, vienen listas para ser gestionadas con **Cloud-Init**, la herramienta estándar para la configuración automática de servidores en el primer arranque.

### 🚀 Guía Rápida por Consola (De Imagen a Template)

Sigue estos pasos directamente en el shell de tu nodo Proxmox. Asumiremos que tu disco de almacenamiento se llama `local-lvm` (ajústalo si usas `local-zfs` u otro).

#### **Paso 1: Descargar la Imagen**

De antemano, ir al directorio donde Proxmox espera las imágenes y descarga la versión más reciente de Ubuntu 24.04

Copia y pega para entrar al directorio, según la ruta de preferencia:

**Ruta por defecto**
```bash
cd /var/lib/vz/template/iso
```

**Ruta personalizada** (Directorio agregado: partición dedicada  fuera del LVM de Proxmox)
```bash
cd /mnt/archives/template/iso/
```

Copia cualquiera de los dos enlaces y pega en la consola de Proxmox: 

#### wget : 

```bash
wget https://cloud-images.ubuntu.com/noble/current/noble-server-cloudimg-amd64.img
```

#### curl: Otra manera  para descargar la imagen:

```bash
curl -O  https://cloud-images.ubuntu.com/noble/current/noble-server-cloudimg-amd64.img
```


#### **Paso 2: Crear y configurar la VM Base**

Vamos a crear una VM con un ID (ej. `9000`) que actuará como nuestra plantilla.

 1. Crear la VM sin disco duro
```bash

qm create 9000 --name "ubuntu-2404-cloudimg" --memory 2048 --cores 2 --net0 virtio,bridge=vmbr0
```

2.  Importar la imagen Cloud descargada anteriormente como si fuera un disco duro

	**Es de importancia estar en el mismo directorio donde se encuentra la imagen**
		
```bash
qm importdisk 9000  noble-server-cloudimg-amd64.img local-lvm
```

3. Configurar el disco importado como SCSI  (el más rápido)
```bash
qm set 9000 --scsihw virtio-scsi-pci --scsi0 local-lvm:vm-9000-disk-0
```

4.  Añadir el disco de  de Cloud-Init  **(fundamental para la personalización)**
```bash
qm set 9000 --ide2 local-lvm:cloudinit
```

5. Configurar el orden de arranque
```bash
qm set 9000 --boot c --bootdisk scsi0
```
6. **(Recomendado)** Activar el Agente QEMU para ver la IP desde la consola web
```
qm set 9000 --agent enabled=1
```

6.  Agregar una consola serial para ver el arranque **(Vital para imágenes Cloud)**
```bash
qm set 9000 --serial0 socket --vga serial0
```

7.  Configura la red por defecto (DHCP):
```bash
qm set 9000 --ipconfig0 ip=dhcp
```


#### **Paso 3: Configurar Cloud-Init por Defecto**

Puedes dejar la contraseña y la clave SSH para configurar al momento de clonar. Sin embargo, para que sea más ágil, puedes poner un usuario y contraseña por defecto en el template:

1.  Establecer usuario y contraseña por defecto (cámbiala después)

```bash
qm set 9000 --ciuser datenk
```

```
qm set 9000 --cipassword "mani4k"
```


#### **Paso 4: Asegurar el acceso a la VM con una llave publica

Configura una llave SSH para Cloud-Init

**Si es NECESARIO restringir aun mas el acceso a una VM, entonces una clave SSH aplica a este escenario


> [!Importante]
> Cuando se aplica una llave SSH, se esta sellando el acceso unicamente con dicha llave y cualquier acceso sera restringido, siendo obligatorio utilizar algo semejante para conectarse a la VM:  **`ssh -i ~/.ssh/<llave-publica>  <user>@IP-VM`** .

**`Considerando la nota importante`**:  si desea un acceso temporalmente con el usuario y contraseña, entonces  omita el **Paso 4**.  Prosiga con el **Paso 5**


4.1 - Copiar desde la estación de trabajo al servidor Proxmox:

**Ejemplo:** Utilizo mi llave publica secundaria 

```bash
scp ~/.ssh/datenmaniak.pub root@IP_PROXMOX
```

4.2 -  Accedo al servidor Proxmox y ajustar permisos de la llave:

```bash
    chmod 600 /root/datenmaniak.pub
```

	
4.3 - **Configurar la llave SSH para Cloud-Init**

```bash
qm set 9000 --sshkey /root/datenmaniak.pub
```

#### **Paso 5: Actualizar Repositorio e Instalar Agente

1.  **Arrancar la VM dentro del Proxmox**

```bash
qm start 9000
```

2. Obtener la IP de la VM

```bash
qm guest cmd 9000 network-get-interfaces | grep -i "ip-address"
```

**Resultado:**
```bash
     "ip-addresses" : [
            "ip-address" : "127.0.0.1",
            "ip-address-type" : "ipv4",
            "ip-address" : "::1",
            "ip-address-type" : "ipv6",
      "ip-addresses" : [
            "ip-address" : "192.168.1.13",
            "ip-address-type" : "ipv4",
            "ip-address" : "fe80::be24:11ff:fecb:abd",
            "ip-address-type" : "ipv6",
```


3. Acceder  por SSH desde la estación remota. (**Mi estación de trabajo con Fedora**)

> [!Recordatorio personal]
> En mi laptop, utilizo dos llaves SSH. Un de ella con **`passphrase`** para actualizacion de repositorios y gestion segura de acceso y contenido. La secundaria para administración con Ansible y testing de VM **(sin passphrase)**.

**Acceso con la llave secundaria:**

```bash
 ssh -i ~/.ssh/datenmaniak datenk@192.168.1.13
```

> [!ATENCION:] 
>  Comprobar que la VM puede ser  iniciada en Proxmox y acceder a la terminal con  `>_Console`  acceso por Consola de manera exitosa,  es sumamente importante antes de convertir la VM Base en Template. 
> 

### Paso 6: Instalar Agente en la VM Base 

> **Ignora si se logro con exito el acceso con la llave SSH y se puedo acceder via Proxmox**

Antes de convertir a template, puedes inyectar el `qemu-guest-agent` dentro de la imagen. Esto mejora la integración con Proxmox.

Herramienta para modificar imágenes de disco

```bash
apt update && apt install libguestfs-tools -y
```

Inyección  del agente en la imagen descargada (ejecutar en el directorio de la imagen):

```bash
virt-customize -a noble-server-cloudimg-amd64.img --install qemu-guest-agent
```

_Nota: Si haces esto, deberás repetir el `qm importdisk` ya que modificaste el archivo `.img` original._


#### **Paso 7: Convertir a Template**

Apaga la VM (si está encendida) y conviértela en template.

```bash
qm shutdown 9000
```

```
qm template 9000
```

### ✅ El Siguiente Paso: Clonar tu Clúster

Ahora sí, desde tu nuevo template, puedes desplegar `master`, `worker1` y `worker2` con un solo comando cada uno, personalizando la RAM, CPU e IP sobre la marcha.