
### Objetivo: Crear una nueva VM usando una VM Base certificada.

Basada en Cloud Image de Ubuntu 24.04 LTS.  Previamente creada y comprobada y asignado el   `ID 9000`

**How-to:**  `Creacion de la VM "Base" y Configuración de Cloud-Init`

#### Paso 1: Acceder al Proxmox y consultar las VM actuales

```bash
# listar las VM
qm list
      VMID NAME                 STATUS     MEM(MB)    BOOTDISK(GB) PID       
      9000 ubuntu-2404-cloudimg stopped    2048               3.50 0     
```

#### Paso 2: clonacion de la VM Base.

En este procedimiento, se consideran estos parámetros:

- `ID 9000` correspondiente a la VM Base.
-  Se hereda toda las especificaciones de sistema  `--full`
- Se asigna `el nuevo ID: ` **104** para la nueva VM.
-  Se establece `2G` de RAM, y
-  `2 vCPU`  Dos Core de CPU


```bash
qm clone 9000 104 --name dbms --full && qm set 104 --memory 2048 --core 2 
```

#### Paso 3: Ajustar el tamano fijo del disco duro `(Muy importante)`

```bash
qm disk resize 104 scsi0 15G
```

##### Paso 4: Configurar la IP

**IP fija:**

```bash
qm set 104 --ipconfig0 ip=192.168.1.100/24,gw=192.168.1.1 --nameserver "192.168.1.1 8.8.8.8"
```

**Configura la red por defecto (DHCP):**

```bash
qm set 104 --ipconfig0 ip=dhcp
```


#### Comprobar la nueva  VM que se han creado

```bash
qm list
      VMID NAME                 STATUS     MEM(MB)    BOOTDISK(GB) PID       
       104 dbms                 running    2048              15.00 4089      
      9000 ubuntu-2404-cloudimg stopped    2048               3.50 0  
```

