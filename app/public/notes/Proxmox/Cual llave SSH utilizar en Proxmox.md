
### Tengo dos llaves en mi laptop. una con passphrase y otra sin ella. Cual recomienda utilizar para este caso y como debo agregarla.?


Para un entorno de **Home Lab y DevOps**, la recomendación técnica varía según el equilibrio entre comodidad y seguridad que busques, pero hay una práctica estándar en la industria.

### ¿Cuál llave utilizar?

1. **La llave con Passphrase (Recomendada para Seguridad):** Es la mejor práctica. Si alguien logra copiar tu llave privada de la laptop, no podrá usarla sin la frase de acceso. En DevOps, usamos un **SSH Agent** en la laptop para escribir la contraseña una sola vez por sesión y que el resto de los comandos (como Terraform o Ansible) fluyan sin pedirla constantemente.
    
2. **La llave sin Passphrase (Comodidad):** Es útil para automatizaciones totalmente desatendidas (scripts que corren solos), pero menos segura.

**Mi recomendación:** Usa la **llave con passphrase**. Como usas un entorno Linux (Fedora Aurora), el agente SSH gestionará la clave de forma transparente para ti.

---
### ¿Cómo agregarla a Proxmox?

Cloud-Init necesita la **llave pública** (el archivo que termina en `.pub`). Tienes dos formas de hacerlo desde la consola de Proxmox:

#### Opción A: Copiar el contenido directamente (La más rápida)

En tu laptop, visualiza tu llave pública:

 ```bash
 cat ~/.ssh/id_rsa_con_passphrase.pub
 ```

Copia todo el texto que empieza por `ssh-rsa ...` y en la consola de Proxmox ejecutas:

```bash
qm set 9000 --sshkeys "pega_aquí_todo_el_texto_de_la_llave"
```

**ID VM Base:** 9000


#### Opción B: Usar el archivo de Proxmox

Si ya habías hecho un `ssh-copy-id` desde tu laptop hacia el nodo de Proxmox (el servidor físico), tu llave ya está en el archivo `/root/.ssh/authorized_keys` del servidor. En ese caso, el comando que te pasé antes funciona directamente:

```bash
qm set 9000 --sshkeys /root/.ssh/authorized_keys
```

o de esta manera alterna:

```bash
qm set 9000 --sshkeys /root/id_266515.pub
```

Para este ultimo, es necesario copiar:

```bash 
scp $HOME/.ssh/tu_llave.pub root@IP_PROXMOX:~/
```


Esto le dice a Cloud-Init: "Copia todas las llaves que tienen permiso para entrar a este servidor Proxmox y dalos de alta en la nueva VM".


### Verificación

Una vez que hayas ejecutado el comando `qm set 9000 --sshkeys ...`, puedes verificar que la configuración quedó guardada antes de convertirla en plantilla:

```bash 
qm cloudinit dump 9000 user
```