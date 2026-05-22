
Para un entorno de **Home Lab** profesional como el tuyo, no necesitas asignar mucho espacio, pero sí es importante el **tipo** de bus y la emulación para asegurar que OPNsense rinda al máximo en Proxmox.

Aquí tienes la configuración recomendada:

### 1. Parámetros Técnicos (Hardware)

- **Bus/Device:** Usa **VirtIO Block**. Es el que ofrece mejor rendimiento y menor latencia en Proxmox.
    
- **Storage:** Selecciona tu **SSD** (el de 480GB). Evita el HDD para el firewall, ya que OPNsense escribe constantemente logs y estados de conexión; en un SSD la interfaz web irá mucho más fluida.
    
- **Disk size:** **8 GB a 16 GB**.
    
    - Con **8 GB** es más que suficiente para el sistema y logs básicos.
        
    - Asigna **16 GB** si planeas instalar plugins pesados como _Zenarmor_ o _Suricata_ (detección de intrusos), que guardan bases de datos de firmas y reportes detallados.
        
- **Cache:** **Write back (unsafe)** o **Default**.
    
- **Discard:** **Activado (Checked)**. Esto es vital si usas SSD (permite el soporte TRIM) para que el disco no se degrade con el tiempo.
    

---

### 2. Consejos para el Particionado (Instalador)

Cuando el instalador de OPNsense te pregunte cómo particionar el disco:

1. **GPT/UEFI vs MBR:** Si creaste la VM con BIOS **OVMF (UEFI)** en Proxmox, elige GPT. Si la creaste con BIOS **Default (SeaBIOS)**, elige MBR.
    
2. **Sistema de Archivos:** Selecciona **ZFS**.
    
    - Aunque es una sola unidad virtual, ZFS en OPNsense es extremadamente resiliente a los cortes de energía repentinos (evita que se corrompa el sistema de archivos si se apaga el servidor de golpe).
        
3. **Swap:** OPNsense te preguntará si quieres crear una partición Swap. Con tus 16GB de RAM en el host, puedes asignarle 2GB de RAM a la VM y **no necesitarás Swap**. Puedes dejarla en 0 o el mínimo (2GB).
    

---

### Resumen rápido de la VM completa:

- **CPU:** 2 Cores (Tipo: `host`).
    
- **RAM:** 2 GB (Mínimo recomendado para estabilidad con servicios activos).
    
- **Disco:** 10 GB en el SSD, Bus VirtIO, Discard activado.
    
- **Red:** `net0` (vmbr0) y `net1` (vmbr1), ambos con VirtIO y sin Firewall de Proxmox.
    

**Tip extra:** Como eres programador y te gusta el orden, una vez que termines la instalación base y antes de configurar las reglas complejas, **haz un Snapshot** en Proxmox. Si algo sale mal configurando el NAT para K3s, puedes volver atrás en segundos.

¿Ya comenzó el formateo del disco?


> [!Error]
> Error cuando el OPNsense pregunta.. previamente he configurado como scsi0


El error ocurre porque **FreeBSD** (la base de OPNsense) a veces tiene problemas para reconocer el controlador **VirtIO SCSI** estándar de Proxmox durante el arranque inicial si no tiene los drivers cargados o si la BIOS no lo identifica correctamente.

Para solucionarlo y que el instalador detecte el disco a la primera, haz este cambio en la configuración de la VM en Proxmox:

### 1. Cambia el Bus del Disco

1. Apaga la VM de OPNsense.
    
2. Ve a la pestaña **Hardware**.
    
3. Selecciona el disco **`scsi0`** y haz clic en **Detach** (esto no borra los datos, solo "desenchufa" el disco).
    
4. Ahora aparecerá abajo como **Unused Disk 0**. Selecciónalo y haz clic en **Edit**.
    
5. En el desplegable de **Bus/Device**, cámbialo de `SCSI` a **`VirtIO Block`**.
    
6. Haz clic en **Add**.
    

### 2. Verifica el Orden de Arranque (Boot Order)

Al cambiar el tipo de bus, Proxmox a veces "olvida" que ese es el disco de arranque:

1. Ve a la pestaña **Options** de la VM.
    
2. Busca **Boot Order**.
    
3. Asegúrate de que el nuevo disco **`virtio0`** esté marcado y muévelo hacia arriba (justo después o antes de la ISO de instalación).
    

---

### ¿Por qué VirtIO Block y no SCSI?

- **SCSI:** Requiere que el sistema operativo cargue un driver específico de Proxmox (`virtio-scsi`). Aunque las versiones modernas lo soportan, el instalador de OPNsense a veces se confunde durante el particionado.
    

> [!NOTE]
> - **VirtIO Block:** Es la implementación más directa y cruda de disco virtual. Es extremadamente estable en FreeBSD y OPNsense lo reconoce como un disco `/dev/vtbd0` de inmediato, sin errores de "Device not found".

    

### Si el error persiste:

Asegúrate de que en la pestaña **Hardware**, el **SCSI Controller** esté configurado como **`VirtIO SCSI`** (aunque uses VirtIO Block, este controlador debe estar presente para la comunicación del bus).

**Prueba cambiarlo a VirtIO Block y arranca de nuevo. Debería detectar el disco como `vtbd0` en el instalador.**

---

### Como aplicar estos ajuste directamente en la consola

Como estás en Proxmox y quieres hacer este cambio de hardware de forma rápida sin pasar por la interfaz web, puedes usar el comando `qm`.

Sigue esta secuencia de comandos desde la terminal de tu nodo Proxmox (como root):

### 1. Desconectar el disco actual (SCSI)

Primero debemos "desenchufar" el disco virtual del bus SCSI. Asumiendo que el ID de tu VM es **100** (cámbialo por el tuyo) y el disco es el **0**:

Bash

```
qm set 100 --delete scsi0
```

_Esto no borra los datos, solo mueve el disco a la sección de "Unused Disks" (Discos no utilizados)._

### 2. Reconectar como VirtIO Block

Ahora lo volvemos a conectar usando el bus `virtio` que es el que mejor reconoce el instalador de OPNsense:

Bash

```
qm set 100 --virtio0 local-lvm:vm-100-disk-0,discard=on
```

_Nota: Asegúrate de que el nombre del disco (`vm-100-disk-0`) y el storage (`local-lvm`) coincidan con lo que tenías. Puedes verlo antes con `qm config 100`._

### 3. Ajustar el orden de arranque

Al cambiar el bus, la VM podría intentar arrancar desde la red o la ISO. Forzamos a que el nuevo disco `virtio0` sea la prioridad:

Bash

```
qm set 100 --boot order=virtio0;ide2
```

_(Aquí `ide2` suele ser la unidad de CD/ISO para que puedas terminar la instalación)._

### 4. Verificar el controlador SCSI

Para que el bus VirtIO funcione correctamente, asegúrate de que el controlador esté en modo `virtio-scsi-single` (aunque uses VirtIO Block, este controlador ayuda a la gestión de la VM):

Bash

```
qm set 100 --scsihw virtio-scsi-single
```

---

### ¿Por qué esto soluciona el error?

El instalador de OPNsense (basado en FreeBSD) a veces no tiene cargados los drivers específicos para el controlador "Default" de SCSI de Proxmox en el momento del particionado. Al pasarlo a **VirtIO Block**, el kernel lo detecta como un dispositivo de bloque estándar (`/dev/vtbd0`) y el formateo ZFS o UFS suele completarse sin errores.

**Una vez ejecutados los comandos, inicia la VM:**

Bash

```
qm start 100
```

Intenta la instalación de nuevo y verás que ahora el disco aparece en la lista como **vtbd0**. ¡Me avisas si te da algún otro mensaje!