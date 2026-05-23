
```bash
 pass -c ssh/key-2026
gpg: Note: database_open 134217901 waiting for lock (held by 16478) ...
gpg: Note: database_open 134217901 waiting for lock (held by 16478) ...
```


### Solución:

**Verifica agente SSH:**
```bash
 eval "$(ssh-agent -s)"
```

**Intenta matar el proceso:**
```bash
 kill -9 16478
```

```bash
gpgconf --kill gpg-agent keyboxd
```

**Verifica si es visible la llave**
```bash
 gpg --list-keys
```

**Intentar con:**
```bash
 gpgconf --reload gpg-agent
```
 
 **Verifica nuevamente si la llave es visible**

**Revisar el directorio ~/.gnupg**

```bash
ls -la ~/.gnupg 
```

**Salida:**
```bash

drwx------@    - datenmaniak  8 abr 08:49  openpgp-revocs.d
drwx------@    - datenmaniak  8 abr 08:49  private-keys-v1.d
drwxr-x---@    - datenmaniak 23 abr 12:28  public-keys.d
.rw-r--r--@   12 datenmaniak  8 abr 08:14 󱁻 common.conf
.rw-r--r--@   84 datenmaniak  8 abr 08:49 󱁻 gpg-agent.conf
.rw-r--r--@   78 datenmaniak  8 abr 08:49 󱁻 gpg.conf
.rw-------@  600 datenmaniak 23 abr 00:56 󰡯 random_seed
.rw-r--r--@  676 datenmaniak  8 abr 08:49 󰡯 sshcontrol
.rw-------@ 1,4k datenmaniak  8 abr 08:49 󰦝 trustdb.gpg
```

**Buscar algún bloqueo y eliminar:**
```bash
ll ~/.gnupg/public-keys.d 
```

```
🐧🔻23% victus ~ 

.rw-r--r--@ 66k datenmaniak  8 abr 08:14  pubring.db
.rw-------@  18 datenmaniak 22 abr 23:45  pubring.db.lock


```

> [!Solución rápida]
> Parece la mas directa

```
 rm ~/.gnupg/public-keys.d/pubring.db.lock

```

#### Si hay salida, entonces OK!

```bash
gpg --list-keys                          
```

```
[keyboxd]
---------
pub   ed25519 2026-04-03 [SC] [caduca: 2031-04-02]
      F0.....................................A
uid        [  absoluta ] Willians Patino (2026) (Creada para el proyecto: Personal Yubikey) <email>
sub   cv25519 2026-04-03 [E] [caduca: 2031-04-02]

```

