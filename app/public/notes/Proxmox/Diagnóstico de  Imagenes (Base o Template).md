
Si la consola de Proxmox te dio error o no cargó, es probable que la imagen base (`9000`) no esté configurada para usar una **consola serial**, que es lo que Terraform suele preferir para gestionar Cloud-Init.

Al momento de esta nota, antes se habia creado un VM como plantilla, que se registro como `ubuntu-2404-template`  con el ID `9000`

## 1. Clonar la imagen plantilla

**Antes de volver a usarla, haz esto con la VM template 9000:**

1. **Conviértela  en una nueva VM** (si ya era template, haz clic derecho -> _Clone_ a una nueva VM para editarla).

	1.1   Se asigna el ID **9100**
	1.2   Nombre:  **ubuntu-2404-template**
	1.3    Aceptar por defecto todas las opciones restantes.
    
2. **Abrir**  la VM 9100 y   **Verifica el Hardware:** Asegúrate de que tenga un dispositivo **Serial Port (0)** y que el **Display** esté configurado como `serial0`.

3. **Ir a Console**,  arrancar la imagen  con  **Start**

		3.1 Acceder con el usuario creado con Terraform
	
**En caso de no tener la clave de acceso o los credenciales no funciona:

1. Ir a **Cloud-Init**  y asignar una **clave de acceso**  
2. **Asignar** cuenta de usuario si es necesario.

## 2. Accede por SSH al servidor Proxmox e instala el Agente:

```bash
		apt update &&  apt install qemu-guest-agent -y
		systemctl enable qemu-guest-agent
```




