
### Requerimientos

Es necesario de antemano, descargar los *dotfiles*  con sus respectivos pasos.


### Descarga

```bash
git clone git@github.com:miusuario/pass-store.git ~/.password-store

```

Si ya existen las credenciales SSH y GPG, se observan estos mensajes:

```bash
Clonando en '/var/home/t01/.password-store'...
The authenticity of host 'github.com (140.82.112.3)' can't be established.
ED25519 key fingerprint is SHA256:+DiY3wvvV6TuJJhbpZisF/zLDA0zPMSvHdkr4UvCOqU.
This key is not known by any other names.
Are you sure you want to continue connecting (yes/no/[fingerprint])? yes
Warning: Permanently added 'github.com' (ED25519) to the list of known hosts.
Enter passphrase for key '/var/home/t01/.ssh/id_ed25519': 
Enter passphrase for key '/var/home/t01/.ssh/id_ed25519': 
remote: Enumerating objects: 52, done.
remote: Counting objects: 100% (52/52), done.
remote: Compressing objects: 100% (29/29), done.
remote: Total 52 (delta 11), reused 52 (delta 11), pack-reused 0 (from 0)
Recibiendo objetos: 100% (52/52), 9.05 KiB | 4.52 MiB/s, listo.
Resolviendo deltas: 100% (11/11), listo.

```

Ese mensaje es **normal** la primera vez que te conectas por SSH a GitHub desde esa máquina: simplemente SSH te está preguntando si confías en la clave del servidor `github.com`.

## Qué puedes hacer?

- La huella que te muestra:
- 
```bash
ED25519 key fingerprint is SHA256:+DiY3wvvV6TuJJhbpZisF/zLDA0zPMSvHdkr4UvCOqU.

```

Coincide con la clave pública oficial de GitHub** para el algoritmo `ssh-ed25519` (esta huella está documentada en la página oficial de GitHub).
    
- Por tanto, **puedes responder con seguridad**:  yes

###  Se indica la passphrase... 

y pulsar Enter.  

Después el clonado seguirá adelante y se guardará la clave del host en `~/.ssh/known_hosts` (no volverá a preguntar en ese host).


	Es importante agregar siempre una passphrase a las clave SSH.


### Voilá!
