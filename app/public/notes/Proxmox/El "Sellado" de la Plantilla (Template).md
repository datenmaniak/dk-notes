### Configuración de Cloud-Init y "Sellado"

Antes de convertirla en plantilla, definimos los parámetros por defecto que heredará cualquier clon hecho por Terraform.

**1. Configurar usuario y llaves SSH:** 

Copiar la llave publica que sera utilizada en los próximos comandos para asociarla con la lmagen Cloud. 

En este ejemplo, se utiliza una llave SSH secundaria que no tiene **passphrase**. 

**Se envía al servidor  Proxmox:**

```bash
❯ scp ~/.ssh/datenmaniak.pub root@192.168.1.201:~/   
root@192.168.1.201's password: 
```

_(Reemplaza `tu_usuario` y pega tu llave pública de la laptop)_.  

```bash
qm set 9000 --ciuser willians
qm set 9000 --sshkeys /root/datenmaniak.pub
qm set 9000 --ciuser usuario --cipassword "tu_contraseña"

# Asigna la clave separada
qm set 9000  --cipassword "password"

```

**2. Configura la red por defecto (DHCP):**

```bash
qm set 9000 --ipconfig0 ip=dhcp
```

**3. PASO FINAL: Convertir en Plantilla:**

Una vez que ejecutas esto, el disco se vuelve de "solo lectura" y la VM ya no se puede encender; solo se puede clonar.

```bash
qm template 9000
```

---
### ¿Qué ocurrió "debajo del capó"?

- **`qm create`**: Creó un archivo de configuración en `/etc/pve/qemu-server/9000.conf`. Puedes leerlo con `cat` para ver cómo se estructuran los parámetros.
    
- **`qm importdisk`**: Convirtió el archivo `.img` (un formato genérico) a un volumen lógico dentro de tu SSD (`local-lvm`), preparándolo para que el hipervisor KVM lo maneje eficientemente.
    
- **`cloudinit`**: Creó un disco ISO invisible que contiene archivos llamados `user-data` y `meta-data`. Cuando la VM arranque por primera vez, leerá ese disco y configurará tu usuario y llaves SSH automáticamente.