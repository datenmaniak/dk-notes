**Error: clone failed: no such logical volume pve/vm-9200-disk-0**

```bash
Plan: 1 to add, 0 to change, 0 to destroy.

Do you want to perform these actions?
  Terraform will perform the actions described above.
  Only 'yes' will be accepted to approve.

  Enter a value: yes

proxmox_vm_qemu.segundo_servidor_terraform: Creating...
╷
│ Error: clone failed: no such logical volume pve/vm-9200-disk-0
│ 
│   with proxmox_vm_qemu.segundo_servidor_terraform,
│   on main.tf line 1, in resource "proxmox_vm_qemu" "segundo_servidor_terraform":
│    1: resource "proxmox_vm_qemu" "segundo_servidor_terraform" {
```

#### Causa:
Ese error es muy claro y nos dice exactamente dónde está el problema: **Proxmox no encuentra el disco de la plantilla que intentas clonar.**

El mensaje `no such logical volume pve/vm-9200-disk-0` significa que, aunque la VM con ID **9200** existe en el inventario de Proxmox, su archivo de disco físico no está en el almacenamiento esperado (`pve` o `local-lvm`).

### ¿Por qué sucede esto?

1. **Plantilla "huérfana":** Quizás borraste el disco manualmente o moviste la VM de almacenamiento, pero Proxmox todavía cree que el disco está en el lugar original.
    
2. **ID Incorrecto:** En tu `main.tf`, estás apuntando a la plantilla **9200**, pero anteriormente estuvimos trabajando con la **9000**.
    
3. **Almacenamiento Desconectado:** Si el disco estaba en un disco externo o NFS que no está montado, Proxmox no lo encontrará.
#### Solucion: 

Ajustar en `main.tf`:

```bash
resource "proxmox_vm_qemu" "segundo_servidor_terraform" { 
name = "srv-prod-02" 
target_node = "kubik" # ASEGÚRATE DE QUE ESTE NOMBRE/ID SEA EL CORRECTO 
clone = "ubuntu-2404-template" # O usa el ID "9000" 

full_clone = true # <--- IMPORTANTE: Cámbialo a true si está en false 
 
 clone       = "ubuntu-2404-template" # El nombre que le diste a la VM 9000
 
 # ... resto de la configuración }
 
```

#### Recomendación: `full_clone = true`

Si tienes `full_clone = false`, Proxmox intenta hacer un "Linked Clone". Esto requiere que el disco base sea inamovible y perfecto. Al poner `full_clone = true`, Terraform le pide a Proxmox que haga una copia completa del disco. Es un poco más lento la primera vez, pero mucho más confiable y evita errores de "logical volume not found".


---

**Error: 403 Permission check failed (/vms/111**



```bash
❯ terraform apply
proxmox_vm_qemu.segundo_servidor_terraform: Refreshing state... [id=kubik/qemu/111]
╷
│ Error: 403 Permission check failed (/vms/111, VM.GuestAgent.Audit|VM.GuestAgent.Unrestricted)
│ 
│   with proxmox_vm_qemu.segundo_servidor_terraform,
│   on main.tf line 1, in resource "proxmox_vm_qemu" "segundo_servidor_terraform":
│    1: resource "proxmox_vm_qemu" "segundo_servidor_terraform" {
```


#### Solucion: 

```bash
pveum role modify TerraformRole -privs "VM.Allocate VM.Clone VM.Config.CDROM VM.Config.Cloudinit VM.Config.CPU VM.Config.Disk VM.Config.HWType VM.Config.Memory VM.Config.Network VM.Config.Options VM.Audit VM.PowerMgmt  Datastore.Audit Datastore.Allocate SDN.Use Datastore.AllocateSpace Datastore.Audit VM.GuestAgent.Audit VM.GuestAgent.Unrestricted"
```