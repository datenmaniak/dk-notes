
### Error:

```
bash
 ssh -i ~/.ssh/datenmaniak datenk@192.168.1.2 -p 2210
@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
@    WARNING: REMOTE HOST IDENTIFICATION HAS CHANGED!     @
@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
IT IS POSSIBLE THAT SOMEONE IS DOING SOMETHING NASTY!
Someone could be eavesdropping on you right now (man-in-the-middle attack)!
It is also possible that a host key has just been changed.
The fingerprint for the ED25519 key sent by the remote host is
SHA256:+mtWF+g2TKUB9/EMudK8swD9zEZMUyHYw0SIbcmNdwI.
Please contact your system administrator.
Add correct host key in /home/datenmaniak/.ssh/known_hosts to get rid of this message.
Offending ECDSA key in /home/datenmaniak/.ssh/known_hosts:81
Host key for [192.168.1.2]:2210 has changed and you have requested strict checking.
Host key verification failed.
```

### Solución: 

**El error indica que la clave del host cambió en la línea 81 de `known_hosts`.** Es común cuando reinstalas VMs o cambias configuraciones SSH.

## Solución Rápida (Específica para puerto 2210):

```bash
# Opción 1: Remover clave específica para IP+puerto
ssh-keygen -R "[192.168.1.2]:2210"
```

### Opción 2: Remover TODAS las entradas de 192.168.1.2

```bash
ssh-keygen -R 192.168.1.2
```







