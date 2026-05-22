

Como ingeniero DevOps, evitaremos usar la contraseña de `root`. En su lugar, vamos a crear un **API Token**. Esto permite que, si alguna vez pierdes la laptop o el código se filtra, puedas revocar solo ese token sin cambiar la contraseña maestra del servidor.

### Crear el API Token en Proxmox

Sigue estos pasos en la interfaz web de Proxmox:

**1. Crear un Rol específico (Opcional pero recomendado):**
    
    - Ve a **Datacenter > Permissions > Roles**.
        
    - Crea uno llamado `TerraformProv` con los permisos: `VM.Allocate`, `VM.Clone`, `VM.Config.CDROM`, `VM.Config.CPU`, `VM.Config.Disk`, `VM.Config.HWType`, `VM.Config.Memory`, `VM.Config.Network`, `VM.Config.Options`, `VM.Monitor`, `VM.Audit`, `Datastore.AllocateSpace`, `Datastore.Audit`.

**2. Crear el Token:**

- Ve a **Datacenter > Permissions > API Tokens**.
    
- Haz clic en **Add**.
    
- Usuario: `root` (o un usuario específico que hayas creado).
    
- Token ID: `terraform-token`.
    
- **IMPORTANTE:** Desmarca la casilla _"Privilege Separation"_ para facilitar la primera configuración.
    
- Haz clic en **Add**.

**3. Guarda las credenciales:**

- Te aparecerá un **Token ID** (ej: `root@pam!terraform-token`) y un **Secret**.
    
- **Cópialos ahora**, porque el Secret no se volverá a mostrar nunca más. Guárdalos en tu gestor de secretos (ya que mencionaste que usas `pass`, es un buen lugar).