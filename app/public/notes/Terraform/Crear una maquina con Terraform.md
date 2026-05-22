
Ahora que el servidor tiene la plantilla lista, el siguiente paso es preparar tu laptop (Fedora Aurora) para que sea el centro de mando. No vamos a clonar la máquina desde la web de Proxmox; vamos a dejar que Terraform lo haga.


## Requerimientos

### 1: Instalar Terraform en tu sistema 

	(puedes usar el binario oficial o vía dnf).


### 2: Generar un API Token en Proxmox: 

Como ingeniero DevOps, evitaremos usar la contraseña de `root`. En su lugar, vamos a crear un **API Token**. Esto permite que, si alguna vez pierdes la laptop o el código se filtra, puedas revocar solo ese token sin cambiar la contraseña maestra del servidor.

Sigue estos pasos en la interfaz web de Proxmox:

### 2.1.  Crear un Rol específico (Opcional pero recomendado):

- Ir a Datacenter > Permissions > Roles

- Crea uno llamado `TerraformProv` con los permisos: `VM.Allocate`, `VM.Clone`, `VM.Config.CDROM`, `VM.Config.CPU`, `VM.Config.Disk`, `VM.Config.HWType`, `VM.Config.Memory`, `VM.Config.Network`, `VM.Config.Options`, `VM.Monitor`, `VM.Audit`, `Datastore.AllocateSpace`, `Datastore.Audit`.

### 2.2. Crear el Token:

- Ve a **Datacenter > Permissions > API Tokens**.
    
- Haz clic en **Add**.
    
- Usuario: `root` (o un usuario específico que hayas creado).
    
- Token ID: `terraform-token`.
    
- **IMPORTANTE:** Desmarca la casilla _"Privilege Separation"_ para facilitar la primera configuración.
    
- Haz clic en **Add**.


### 2.3. Guardar las credenciales

- Te aparecerá un **Token ID** (ej: `root@pam!terraform-token`) y un **Secret**.
    
- **Cópialos ahora**, porque el Secret no se volverá a mostrar nunca más. Guárdalos en tu gestor de secretos (ya que mencionaste que usas `pass`, es un buen lugar).

## Otra forma de crear el Token

```bash

pveum user add terraform-user@pve
root@kubik:~# pveum acl modify / -user terraform-user@pve -role PVEVMAdmin
root@kubik:~# pveum user token add terraform-user@pve terraform-token --privsep 0
┌──────────────┬──────────────────────────────────────┐
│ key          │ value                                │
╞══════════════╪══════════════════════════════════════╡
│ full-tokenid │ terraform-user@pve!terraform-token   │
├──────────────┼──────────────────────────────────────┤
│ info         │ {"privsep":"0"}                      │
├──────────────┼──────────────────────────────────────┤
│ value        │ 32ad7194-10a7-4f17-b56f-c791951f7700 │
└──────────────┴──────────────────────────────────────┘
```
"32ad7194-10a7-4f17-b56f-c791951f7700"


## 3:  Preparar el directorio de trabajo

En tu terminal de la workstation Fedora

### 3.1  Vamos a crear la estructura de archivos 

Con estos se pretende administrar la infraestructura:

```bash
mkdir -p ~/homelab/infra-proxmox

cd ~/homelab/infra-proxmox

touch main.tf providers.tf  variables.tf
```

### 3.2  El código de Terraform

Para no complicarnos, vamos a usar el proveedor de **Telmate** (es el más maduro para Proxmox). 

Abre `providers.tf` con Neovim y pega esto:

```terraform
terraform {
  required_providers {
    proxmox = {
      source  = "telmate/proxmox"
      version = "3.0.1-rc3" # Versión estable actual
    }
  }
}

provider "proxmox" {
  pm_api_url          = "https://TU_IP_PROXMOX:8006/api2/json"
  pm_api_token_id     = "TU_TOKEN_ID"
  pm_api_token_secret = "TU_TOKEN_SECRET"
  pm_tls_insecure     = true # Ya que usamos certificados auto-firmados
}

# Ejemplos:
 pm_api_token_id     = "root@pam!terraform-token"
pm_api_token_secret = "4a98....ac5....6fef4"
```

### Observacion: 

### Es importante el signo ! en el token_id

### ¿Por qué `pm_tls_insecure = true`?

Por defecto, Proxmox genera certificados SSL locales. A menos que configures un dominio real con Let's Encrypt, Terraform rechazará la conexión por seguridad. Con esta línea, le decimos que confíe en nuestra red local.

**¿Ya tienes el Token ID y el Secret a mano?** Si es así, podemos escribir el recurso de la VM en `main.tf` para lanzar el primer clon de la VM 9000.


### 3.3  Definir el recurso en *main.tf*

Se ajustan  los parámetros para que coincidan con el hardware (usando el almacenamiento SSD local-lvm para el disco):

```bash
neovim main.tf
```

Pega esto en el archivo:

```bash
resource "proxmox_vm_qemu" "primer_servidor_devops" {
  name        = "srv-prod-01"
  target_node = "pve" # Cambia esto si tu nodo Proxmox se llama distinto
  clone       = "ubuntu-2404-template" # El nombre que le diste a la VM 9000

  # Configuración básica de hardware
  cores   = 2
  memory  = 2048
  agent   = 1 # Muy importante para que Proxmox vea la IP

  # Ubicación del disco (SSD para velocidad)
  disk {
    size            = "20G"
    type            = "scsi"
    storage         = "local-lvm"
  }

  # Configuración de Red (Cloud-Init)
  # Esto sobrescribe lo que pusimos en el template si fuera necesario
  ipconfig0 = "ip=dhcp"
  
  # Al usar Cloud-init, Terraform puede inyectar llaves SSH aquí también
  sshkeys = <<EOF
  ssh-rsa AAAAB3NzaC1yc2EAAAADAQABAAABgQC... (tu llave pública aquí)
  EOF
}
```

### 3.4. Inicializacion y Despliegue

Ahora, desde la terminal en el mismo directorio (`~/homelab/infra-proxmox`), ejecuta estos tres comandos sagrados del mundo DevOps:

1. **`terraform init`**: Descargará el plugin de Proxmox en tu laptop.
    
2. **`terraform plan`**: Terraform se conectará a Proxmox, verá que la VM `srv-prod-01` no existe y te mostrará un resumen de lo que va a crear. **Revisa que no haya errores de conexión.**
    
3. **`terraform apply`**: Escribe `yes` cuando te lo pida.

4a98c6fb-e4ce-4b17-ac51-c69505a6fef4

## Errores

