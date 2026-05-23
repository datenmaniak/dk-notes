
Por que no se debe utilizar imagenes **.img** para instalaciones de pfSense y/o OPNSense.

> [!NOTE]
> Es decir, que no aplica el procedimiento de Cloud Image de Ubuntu Server, donde se crea un template, luego se clona y se ajusta el disco...


Exactamente. Has dado en el clavo: **OPNsense (y FreeBSD en general) no se comporta como las Cloud-init Images de Ubuntu o Debian.**

Hay tres razones técnicas fundamentales por las que el flujo de trabajo de "Cloud Image" que usas para tus workers de K3s no funciona igual aquí:

### 1. Falta de agente Cloud-init

Las imágenes de Ubuntu Cloud vienen con el paquete `cloud-init` preinstalado. Al arrancar, este servicio detecta que el "disco físico" es más grande que la partición y ejecuta automáticamente comandos de expansión. OPNsense no incluye `cloud-init` de serie en su imagen base `.img`.

### 2. La arquitectura de FreeBSD

Ubuntu usa **LVM** o particiones **EXT4/XFS** que Linux redimensiona con mucha facilidad en caliente. OPNsense usa la tabla de particiones de FreeBSD y, frecuentemente, el sistema de archivos **UFS** o **ZFS**. Estos requieren que el kernel "recupere" la tabla de particiones manualmente si el disco subyacente cambia de tamaño después de haber sido escrito.

### 3. Propósito de la imagen `.img`

La imagen `.img` de OPNsense es una "imagen de flasheo". Está diseñada para ser idéntica bit a bit a un medio físico (como una tarjeta SD de sistemas embebidos). No espera que el disco crezca.

---

### ¿Cómo deberías manejarlo para tu HomeLab?

Si quieres mantener un flujo de trabajo eficiente y profesional (estilo DevOps) para OPNsense en Proxmox, tienes estas dos mejores alternativas:

#### Alternativa A: El flujo de "Instalación Limpia" (Recomendada)

En lugar de un template de disco, usa la **ISO**.

1. Creas la VM con el disco de 40GB.
    
2. Instalas desde la ISO (tardarás menos de 5 minutos en tu SSD).
    
3. Una vez configurado lo básico, **esa VM es tu "Template"**.
    
4. Si necesitas otra, clonas esa VM. Al haber sido instalada sobre 40GB, ya nació con ese tamaño.
    

#### Alternativa B: El flujo de "Ajuste Manual"

Si insistes en usar la imagen `.img` y clonarla, después de hacer el resize en Proxmox, tienes que entrar a la consola de OPNsense y "despertar" al sistema para que vea el espacio:

1. **Arreglar la tabla de particiones:**
    
    Bash
    
    ```
    gpart recover vtbd0
    ```
    
2. **Expandir la partición de datos (asumiendo que es la 3):**
    
    Bash
    
    ```
    gpart resize -i 3 vtbd0
    ```
    
3. **Expandir el sistema de archivos:**
    
    Bash
    
    ```
    growfs /
    ```
    

### Resumen para tu flujo actual

Para tus **nodos de K3s**, el método de Cloud Image con Terraform es perfecto. Pero para el **Firewall (OPNsense/pfSense)**, lo más robusto y "limpio" es realizar una instalación manual desde la ISO sobre el disco del tamaño final que desees. Así evitas errores de alineación de sectores o problemas con el sistema de archivos en el futuro.

¿Quieres que te ayude a configurar la VM desde la ISO para que quede optimizada para Proxmox desde el inicio?