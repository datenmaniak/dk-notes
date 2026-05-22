## Requisitos:

 -   Descarga de la Imagen Cloud e Instalación de Herramientas
 -   Creacion de la VM "Base" y Configuración de Cloud-Init
 -   El "Sellado" de la Plantilla (Template)
 -   Crear Credenciales API TOKEN y Role

### 1.  Preparar el directorio de trabajo en la estacion de trabajo

 ```bash
 mkdir -p ~/proyectos/infra-proxmox
cd ~/proyectos/infra-proxmox
touch main.tf providers.tf variables.tf
 ```

### 2. código de Terraform

Para no complicarnos, vamos a usar el proveedor de **Telmate** (es el más maduro para Proxmox). Abre `providers.tf` con Neovim y pega esto:

```bash
# providers.tf 
terraform {
  required_providers {
    proxmox = {
      source  = "telmate/proxmox"
      version = "3.0.2-rc07" #  estable mas reciente
    }
  }
}

provider "proxmox" {
  pm_api_url          = var.proxmox_api_url
  pm_api_token_id     = var.proxmox_api_token_id
  pm_api_token_secret = var.proxmox_api_token_secret
  pm_tls_insecure     = true
  pm_minimum_permission_check = false
  pm_log_enable       = true
  pm_log_file         = "terraform.log"
  pm_debug            = true
}
```


**¿Por qué `pm_tls_insecure = true`?**

Por defecto, Proxmox genera certificados SSL locales. A menos que configures un dominio real con Let's Encrypt, Terraform rechazará la conexión por seguridad. Con esta línea, le decimos que confíe en nuestra red local.

**¿Ya tienes el Token ID y el Secret a mano?** Si es así, podemos escribir el recurso de la VM en `main.tf` para lanzar el primer clon.

De lo contrario, revisar la salida de la consola durante la **creacion de API TOKE y Role**.

Vamos a escribir el archivo `main.tf`. Este código le dirá a Proxmox: _"Toma esa plantilla 9000 que creamos manualmente y clónala para crear el primer servidor real"_.

#### Al fin de este documento, referencia acerca de Telmate y Proxmox.


### 3. Definir el recurso en `main.tf`

Abre `main.tf` en tu laptop y pega este bloque. He ajustado los parámetros para que coincidan con tu hardware (usando el almacenamiento SSD `local-lvm` para el disco):

```bash
resource "proxmox_vm_qemu" "primer_servidor_devops" {
  name        = "srv-prod-01"
  target_node = "kubik" # Cambia esto si tu nodo Proxmox se llama distinto
  clone       = "ubuntu-2404-template" # El nombre que le diste a la VM 9000
  vmid       = 110 # ID para la nueva VM

  # Configuración básica de hardware
cpu {

  cores   = 2
  type    = "host"  # se recomienda, para que la VM use todas las instruccion 
                    # del procesador del servidor (i3-10100)
}  
memory  = 2048

agent   = 1 # Muy importante para que Proxmox vea la IP

  # Ubicación del disco (SSD para velocidad)
# NUEVA SINTAXIS PARA DISCOS (v3.x)
  disks {
    scsi {
      scsi0 {
        disk {
          size    = "20G"
          storage = "local-lvm"
        }
      }
    }
  }
  # Configuración de Red (Cloud-Init)
  # Esto sobrescribe lo que pusimos en el template si fuera necesario
  #ipconfig0 = "ip=dhcp"
  # Configuración de Red
  network {
    id = 0
    model  = "virtio"
    bridge = "vmbr0"
  }
  
  # Al usar Cloud-init, Terraform puede inyectar llaves SSH aquí también
  ipconfig0 = "ip=dhcp"
  # datenmaniak
  sshkeys = "ssh-ed25519 AAAAC3NzaC1lZDI1NTE5AAAAIBhEWrYpMmz1BafNtOHBENiNJhkUkTuKrgcQNhLMFHW8 datenmaniak@gmail.com"
  # ppwj
  #sshkeys = "ssh-ed25519 AAAAC3NzaC1lZDI1NTE5AAAAIDcS6B/fZPsFS/85qVrhVqvM6JQjElARP/DuINhbkQai ppwj@yahoo.com"
}

```


### 4. Crea un archivo secreto llamado `terraform.tfvars` (Opcional pero recomendado):

Terraform leerá automáticamente este archivo para rellenar las variables. **No olvides añadirlo a tu `.gitignore`**.

```bash
# terraform.tfvars
proxmox_api_url          = "https://192.168.1.201:8006/api2/json"
proxmox_api_token_id     = "terraform-user@pve!terraform-token"
proxmox_api_token_secret = "75c0bc2c-6cbf-4052-a255-130fb0913729"
target_node              = "kubik"
```


### 5. Inicialización y Despliegue

Ahora, desde la terminal en el mismo directorio (`~/proyectos/infra-proxmox`), ejecuta estos tres comandos sagrados del mundo DevOps:

1. **`terraform init`**: Descargará el plugin de Proxmox en tu laptop.
    
2. **`terraform plan`**: Terraform se conectará a Proxmox, verá que la VM `srv-prod-01` no existe y te mostrará un resumen de lo que va a crear. **Revisa que no haya errores de conexión.**
    
3. **`terraform apply`**: Escribe `yes` cuando te lo pida.



---
**Referencias:**

[Mas info acerca de Proxmox Provider](https://search.opentofu.org/provider/telmate/proxmox/latest)

A Terraform provider is responsible for understanding API interactions and exposing resources. The Proxmox provider uses the Proxmox API. This provider exposes two resources: [proxmox_vm_qemu](https://search.opentofu.org/provider/telmate/proxmox/resources/vm_qemu.md) and [proxmox_lxc](https://search.opentofu.org/provider/telmate/proxmox/resources/lxc.md).

#### Creating the Proxmox user and role for terraform

To ensure security, it's best practice to create a dedicated user and role for Terraform instead of using cluster-wide Administrator rights. The particular privileges required may change but here is a suitable starting point.

Log into the Proxmox cluster or host using ssh (or mimic these in the GUI) then:

- Create a new role for the future terraform user.
- Create the user "terraform-prov@pve"
- Add the TERRAFORM-PROV role to the terraform-prov user

##### Proxmox 9 and Newer[#](https://search.opentofu.org/provider/telmate/proxmox/latest#proxmox-9-and-newer)

In Proxmox 9, the `VM.Monitor` privilege was deprecated and is no longer required.

```bash
pveum role add TerraformProv -privs "Datastore.AllocateSpace Datastore.AllocateTemplate Datastore.Audit Pool.Allocate Pool.Audit Sys.Audit Sys.Console Sys.Modify VM.Allocate VM.Audit VM.Clone VM.Config.CDROM VM.Config.Cloudinit VM.Config.CPU VM.Config.Disk VM.Config.HWType VM.Config.Memory VM.Config.Network VM.Config.Options VM.Migrate VM.PowerMgmt SDN.Use"
pveum user add terraform-prov@pve --password <password>
pveum aclmod / -user terraform-prov@pve -role TerraformProv

```

##### Modifying Privileges[#](https://search.opentofu.org/provider/telmate/proxmox/latest#modifying-privileges)

If you need to adjust the role's permissions later, you can use the `pveum role modify` command. Simply add or remove privileges from the `-privs` list as needed. For example if you are migrating from Proxmox 8 to 9, you may run the following command to remove `VM.Monitor`

```bash
pveum role modify TerraformProv -privs "Datastore.AllocateSpace Datastore.AllocateTemplate Datastore.Audit Pool.Allocate Sys.Audit Sys.Console Sys.Modify VM.Allocate VM.Audit VM.Clone VM.Config.CDROM VM.Config.Cloudinit VM.Config.CPU VM.Config.Disk VM.Config.HWType VM.Config.Memory VM.Config.Network VM.Config.Options VM.Migrate VM.PowerMgmt SDN.Use"

```

##### Using an API Token (Recommended)

The provider also supports using an API token rather than a password. To create an API token, use the following command:

```bash
pveum user token add terraform-prov@pve mytoken

```

