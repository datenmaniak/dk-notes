
> [!NOTE]
> Tengo una duda. La imagen original de OPNsense es de 2.7G con la extension .img. Previamente he creado una VM base template con esta. Despues hago una clonacion y aplico un resize al disco para llevarlo a 40GB.  Sin embargo, pude observa que no ajusta la imagen.

Es un comportamiento totalmente normal y esperado. Lo que sucede es que has ampliado el **"contenedor"** (el disco virtual de Proxmox), pero no has ampliado el **"contenido"** (las particiones y el sistema de archivos de OPNsense).

Al ser una imagen `.img` (habitualmente la versión _Nano_ o _Serial_), viene pre-configurada con una tabla de particiones fija de 3GB. Aunque Proxmox ahora vea 40GB, el sistema FreeBSD interno de OPNsense sigue creyendo que su disco termina en el GB 3.

Para solucionarlo, tienes dos caminos dependiendo de en qué fase te encuentres:

### Opción 1: Si ya terminaste la instalación

Si ya instalaste OPNsense en el disco y lo que quieres es expandir la partición existente para usar los 40GB:

1. Entra por **SSH** o usa la **Opción 8 (Shell)** en la consola de la VM.
    
2. Ejecuta el siguiente comando para forzar al kernel a reconocer el nuevo tamaño del disco:
    
    Bash
    
    ```
    gpart recover vtbd0
    ```
    
3. Luego, redimensiona la partición (normalmente es la partición 3 o 4, puedes verlas con `gpart show vtbd0`):
    
    Bash
    
    ```
    gpart resize -i <numero_particion> vtbd0
    ```
    
4. Finalmente, expande el sistema de archivos (si usaste UFS):
    
    Bash
    
    ```
    growfs /
    ```
    

### Opción 2: El método limpio (Recomendado para tu HomeLab)

Dado que estás en fase de pruebas y tienes un **i3 con SSD**, lo más eficiente para que OPNsense aproveche esos 40GB de forma nativa desde el segundo 1 es:

1. **No uses el `.img` como disco principal.** El `.img` está diseñado para ser grabado en una SD o pendrive.
    
2. Descarga la versión **DVD ISO** (`.iso.bz2`), descomprímela y súbela a Proxmox.
    
3. Crea la VM con el disco de 40GB vacío (Bus VirtIO Block).
    
4. Arranca desde la ISO.
    
5. Durante la instalación (usuario `installer`), selecciona el disco de 40GB.
    
6. **Resultado:** El instalador detectará los 40GB completos y creará las particiones usando todo el espacio disponible automáticamente.
    

### ¿Por qué te sucede esto con el clonado?

Cuando clonas un template basado en una imagen de disco cruda (`.img`), estás haciendo una copia bit a bit. Si el archivo original decía que la partición termina en el sector X, el clon dirá lo mismo aunque tenga sectores libres después. Es exactamente lo mismo que ocurre cuando grabas una imagen de Raspberry Pi de 4GB en una SD de 32GB; siempre tienes que "expandir el sistema de archivos" después.

**Mi consejo:** Como vas a usar OPNsense de forma permanente para gestionar tu clúster de **K3s**, usa el método de la **ISO**. Te asegurarás de tener una tabla de particiones limpia y optimizada para tu SSD, y te ahorrarás pelear con comandos de `gpart` en FreeBSD.

¿Prefieres intentar rescatar la partición actual con comandos o prefieres ir por la vía limpia de la ISO?