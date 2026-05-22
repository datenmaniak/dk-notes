
**Objetivo:**  Crear las credenciales para administrar la maquina VM en Proxmox de manera remota, por consola de comando utilizando **Terraform**.

## Requerimiento:

Acceder a la consola de Proxmox

```bash 
ssh root@IP_PROXMOX
```

### 1. Crear el Rol

1.1  Crea el script
```bash
# nvim agrega-rol.sh
```

1.2 Pega este contenido: 

```bash
#!/bin/bash

# Nombre del rol
ROLE="TerraformRole"

# Arreglo de permisos (privilegios)
privs=(
    "VM.Allocate"
    "VM.Clone"
    "VM.Config.CDROM"
    "VM.Config.Cloudinit"
    "VM.Config.CPU"
    "VM.Config.Disk"
    "VM.Config.HWType"
    "VM.Config.Memory"
    "VM.Config.Network"
    "VM.Config.Options"
    "VM.Audit"
    "VM.PowerMgmt"
    "SDN.Use"
    "Datastore.AllocateSpace"
    "Datastore.AllocateTemplate"
    "Datastore.Audit"
)

# Unir el array en una sola cadena separada por espacios
privs_str=$(IFS=" "; echo "${privs[*]}")

# Ejecutar el comando pveum y validar resultado
echo "Creando rol '$ROLE' con privilegios..."
pveum role add "$ROLE" -privs "$privs_str"

if [ $? -eq 0 ]; then
    echo "✅ Rol '$ROLE' creado correctamente."
else
 echo "❌ Error al crear el rol '$ROLE'. Revise los permisos o el contexto de ejecución."
    exit 1
fi
```

1.3 **Agrega privilegios** según sea necesario a la variable **privs**

1.2  Ejecuta:

```bash
bash agrega-rol.sh
```

### 2. Crear el usuario y asignar el rol

```bash
pveum user add terraform-user@pve

pveum acl modify / -user terraform-user@pve -role TerraformRole
```
### 3. Genera el nuevo API TOKEN

```bash
pveum user token add terraform-user@pve terraform-token --privsep 0
```

**Si resulta con éxito, se observara en la consola algo parecido:**


```bash
┌──────────────┬──────────────────────────────────────┐
│ key          │ value                                │
╞══════════════╪══════════════════════════════════════╡
│ full-tokenid │ terraform-user@pve!terraform-token   │
├──────────────┼──────────────────────────────────────┤
│ info         │ {"privsep":"0"}                      │
├──────────────┼──────────────────────────────────────┤
│ value        │ 75c0bc2c-6cbf-4052-a255-130fb0913729 │
└──────────────┴──────────────────────────────────────┘
```

# IMPORTANTE:

### Guardar el API TOKEN mostrado en `value`


## Que significa el @pve en la parte de "terraform-user@pve"?

Esa es una excelente pregunta técnica. En Proxmox, el sufijo que sigue al `@` no es decorativo; define el **Reino de Autenticación** (Authentication Realm). Es lo que le dice al sistema _quién_ debe validar la contraseña o el token del usuario.


Aquí te explico los dos tipos principales que existen en tu servidor:

### 1. `@pve` (Proxmox VE Authentication Server)

Es el reino de autenticación **interno** de Proxmox.

- **Para qué sirve:** Para usuarios que solo existen dentro de la base de datos de Proxmox.
    
- **Comportamiento:** Proxmox gestiona sus contraseñas de forma independiente. No necesitan existir como usuarios en el sistema operativo Linux (Debian) que corre por debajo.
- **Tu caso:** Para herramientas de automatización como **Terraform**, siempre es mejor usar `@pve`. Es más limpio, seguro y no "ensucia" la lista de usuarios de tu servidor físico.
    

### 2. `@pam` (Pluggable Authentication Modules)

Este reino utiliza los usuarios del **sistema operativo** (Linux).

- **Para qué sirve:** Para los usuarios que creas directamente en la terminal con el comando `adduser`.
    
- **Comportamiento:** Si intentas loguearte con un usuario `@pam`, Proxmox le pregunta al archivo `/etc/shadow` de Linux si la contraseña es correcta.
    
- **Ejemplo:** El usuario `root@pam` es el administrador principal que viene por defecto.
    

---

**¿Por qué es importante para tu proyecto?**

Cuando ejecutas:

**pveum user add terraform-user@pve**

Le estás diciendo a Proxmox: "Crea un usuario llamado 'terraform-user' que solo vivirá dentro de tu interfaz de gestión. No quiero que tenga una carpeta /home ni que pueda hacer SSH directamente al servidor físico kubik".

---



