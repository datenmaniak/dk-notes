<img src="https://r2cdn.perplexity.ai/pplx-full-logo-primary-dark%402x.png" style="height:64px;margin-right:32px"/>

# tengo este problema:  ❯ pass -c token/github

gpg: public key decryption failed: No secret key
gpg: decryption failed: No secret key

El error `gpg: public key decryption failed: No secret key` significa que `pass` está intentando descifrar la entrada `token/github` con una clave GPG, pero **no encuentra la clave privada (secret key)** correspondiente en tu llavero de GPG.

## 1. Verifica que tienes la clave privada

Ejecuta:

```bash
gpg --list-secret-keys
```

Deberías ver algo parecido a:

```
sec   rsa4096 2025-01-01 [SC] [expires: 2030-01-01]
      DEADBEEFDEADBEEFDEADBEEFDEADBEEFDEADBEEF
```

Si no ves nada (o solo `pub` sin `sec`), **no tienes la clave privada** en este sistema.

## 2. Si copiaste la llave desde otro equipo

Si usas `pass` en múltiples máquinas, normalmente:

- La clave pública se usa para cifrar (`pass insert ...`).
- La clave privada se usa para descifrar (`pass show ...` / `pass -c ...`).

Pasos típicos:

1. En el equipo original, exporta la clave privada:

```bash
gpg --export-secret-keys --armor ID_DE_LA_CLAVE > secret-key.asc
```

2. Cómpratela al nuevo equipo (por ejemplo vía USB o `scp`).
3. En el nuevo equipo, impórtala:

```bash
gpg --import secret-key.asc
```

4. Verifica que aparece en `gpg --list-secret-keys`.

## 3. Si usas GPG con smart‑card o YubiKey

Si tu clave privada está en una YubiKey o smart‑card:

- Asegúrate de que el dispositivo está conectado e insertado.
- Ejecuta:

```bash
gpg --card-status
gpg --list-secret-keys
```


Si en `--list-secret-keys` ves algo como:

```
sec>  ...  [SC] ...
```

significa que la clave privada está en la tarjeta; `pass` debería poder usarla si el daemon GPG está funcionando.

## 4. Si cambió el `GPG_ID` de `pass`

`pass` usa la clave asociada al `gpg-id` del store. Puedes verlo:

```bash
cat ~/.password-store/.gpg-id
```

Si el ID que aparece ahí no coincide con ninguna de tus claves (`gpg --list-secret-keys`), tendrás que:

- Actualizar el `gpg-id` del store (por ejemplo, si cambiaste de clave) o
- Importar/poner la clave indicada en el llavero.


## 5. Ejemplo de flujo rápido (si falta la clave)

1. Verifica qué clave está configurada en `pass`:

```bash
cat ~/.password-store/.gpg-id
# supongamos que devuelve: DEADBEEFDEADBEEFDEADBEEFDEADBEEFDEADBEEF
```

2. En el equipo donde sí tienes la clave, exporta:

```bash
gpg --export-secret-keys --armor DEADBEEFDEADBEEFDEADBEEFDEADBEEFDEADBEEF > secret-key.asc
```

3. En el equipo donde te da error:

```bash
gpg --import secret-key.asc
gpg --list-secret-keys
pass -c token/github
```


Si me pegas la salida de:

```bash
gpg --list-secret-keys
cat ~/.password-store/.gpg-id
```

te digo exactamente qué clave te falta y qué comando usar.
<span style="display:none">[^1_1][^1_10][^1_2][^1_3][^1_4][^1_5][^1_6][^1_7][^1_8][^1_9]</span>

<div align="center">⁂</div>

[^1_1]: https://support.nitrokey.com/t/how-do-you-import-a-publickey-asc-and-a-secretkey-asc-with-pass-password-manager-on-another-computer-step-by-step/4367

[^1_2]: https://bbs.archlinux.org/viewtopic.php?id=294191

[^1_3]: https://github.com/stefanzweifel/git-auto-commit-action/discussions/334

[^1_4]: https://github.com/gopasspw/gopass/discussions/2471

[^1_5]: https://github.com/kovidgoyal/kitty/issues/5844

[^1_6]: https://forum.gnupg.org/t/gpg-decryption-fails-no-secret-key-found-after-upgrade-from-gpg4win-3-1-1-4-3-0/4375

[^1_7]: https://github.com/github/copilot-cli/issues/2071

[^1_8]: https://stackoverflow.com/questions/28321712/gpg-decryption-fails-with-no-secret-key-error

[^1_9]: https://docs.github.com/en/enterprise-server@3.16/authentication/keeping-your-account-and-data-secure/managing-your-personal-access-tokens

[^1_10]: https://www.reddit.com/r/commandline/comments/7741rz/help_installing_pass_and_getting_around_this/


---

# si veo la clave privada. El punto es que la clave GPG ha sido regenerada.

Perfecto, ya aclaramos la parte crítica: **sí ves la clave privada en el sistema**, pero la clave GPG asociada a `pass` ha sido regenerada (es decir, `~/.password-store/.gpg-id` todavía apunta a una clave antigua que ya no existe).

En ese caso, el problema es que:

- Las entradas están cifradas con la **clave GPG vieja**.
- Tu nueva clave **no puede descifrarlas**, porque no tiene la misma ID/fingerprint.


### Opción 1: intentar recuperar la clave privada original

Si la clave GPG regenerada fue borrada sin exportar, pero:

- Tienes copia del directorio `~/.gnupg` antiguo, o
- Tienes un backup del sistema donde la clave existía,

entonces:

```bash
gpg --export-secret-keys ID_VIEJA > secret-old.asc
scp secret-old.asc $TU_EQUIPO_ACTUAL
gpg --import secret-old.asc
```

Luego, prueba:

```bash
pass -c token/github
```

Si la clave importada funciona, podrás luego migrar todas las entradas a la nueva clave (opción 2).

### Opción 2: si la clave vieja ya no existe (no hay backup)

Si la clave GPG original **se perdió definitivamente**, entonces:

- **No puedes descifrar** las entradas ya existentes en `~/.password-store` porque están cifradas con esa clave fallecida.
- `pass` no tiene modo de “re‑cifrar” desde una clave que ya no existe.

Lo que puedes hacer es:

1. Empezar de nuevo con la nueva clave:

```bash
rm -rf ~/.password-store
pass init ID_DE_TU_CLAVE_NUEVA
```

2. Re‑insertar las contraseñas que recuerdes o tengas en otro lado:

```bash
pass insert token/github
```

3. Si tenías backup de las contraseñas en otro gestor (Bitwarden, KeePass, etc.), puedes regenerar manualmente las entradas en `pass`.

### 3. Prevención para el futuro

Cuando regeneres clave GPG:

- Exporta siempre la clave privada vieja **antes** de borrarla:

```bash
gpg --export-secret-keys ID_VIEJA > backup-secret-old.asc
```

- Luego puedes migrar todas las entradas con:

```bash
pass git remote set-url origin $TU_REPO_BAKED
pass init ID_NUEVA
pass git pull
pass git push
```

(o usar algo como `gpg --change‑passphrase` si solo quieres cambiar la passphrase, no regenerar la clave).

***

Si quieres, mándame:

```bash
gpg --list-secret-keys
cat ~/.password-store/.gpg-id
```

y te digo si todavía hay esperanza de recuperar la clave vieja o si es mejor recrear todo desde cero.
<span style="display:none">[^2_1][^2_10][^2_2][^2_3][^2_4][^2_5][^2_6][^2_7][^2_8][^2_9]</span>

<div align="center">⁂</div>

[^2_1]: https://maslinux.wordpress.com/2017/06/24/pass-simple-gestor-de-contrasenas-para-gnulinux/

[^2_2]: https://ericeastwood.com/blog/paper-backup-and-recovery-guide-for-pass-password-store/

[^2_3]: https://osiux.com/2021-02-19-no-me-acuerdo-de-nada-dejame-en-pass.html

[^2_4]: https://forum.gnupg.org/t/help-ive-forgotten-my-passphrase/2889

[^2_5]: https://moribundo.flounder.online/blog/001_gestor_contrase%C3%B1as_pass.gmi

[^2_6]: https://stackoverflow.com/questions/65020451/recover-lost-gpg-password

[^2_7]: https://blog.parravidales.es/gestiona-tus-contrasenas-y-secretos-con-pass/

[^2_8]: https://gpgtools.tenderapp.com/discussions/problems/96991-lost-passphrase

[^2_9]: https://www.gnupg.org/gph/es/manual.html

[^2_10]: https://www.reddit.com/r/GnuPG/comments/q1k3ow/i_lost_my_password_for_my_key_i_have_a_revocation/


---

# no quiero eliminar el almacen de contrasena...

Perfecto, entonces **no vamos a tocar/eliminar el almacen de contraseñas**, solo necesitamos recuperar la situación.
El problema es que:

- El `.password-store` todavía está cifrado con la **clave GPG antigua**.
- Tu clave GPG ha sido regenerada y esa antigua ya no existe (o no está disponible en este sistema).


### 1. Lo que puedes recuperar sin perder el almacen

Para poder seguir usando ese almacen sin borrarlo, necesitas:

1. Tener instalada **la clave privada antigua** (la que cifró los archivos) en el `gpg` del sistema.
2. Que el archivo `~/.password-store/.gpg-id` contenga el ID de esa clave antigua.

Pasos:

1. Si tienes backup de la clave GPG antigua (por ejemplo un archivo `secret-old.asc`):

```bash
gpg --import secret-old.asc
gpg --list-secret-keys
```

Deberías ver el fingerprint de la clave antigua.
2. Verifica que el store hace referencia a esa clave:

```bash
cat ~/.password-store/.gpg-id
```

Si el ID que muestra ahí no coincide con ninguna de tus claves, dale su valor correcto:

```bash
echo "ID_DE_LA_CLAVE_ANTIGUA" > ~/.password-store/.gpg-id
```

3. Intenta leer una entrada:

```bash
pass show token/github
```

Si te pide la passphrase de la clave antigua y abre, entonces **puedes seguir usando el almacen como está**.

### 2. Si quieres migrar a la clave nueva (sin borrar el almacen)

Cuando ya tengas la clave antigua importada y funcional, puedes **migrar todo al nuevo GPG ID**:

```bash
pass git pull            # asegúrate de estar sincronizado
pass init ID_DE_TU_CLAVE_NUEVA
pass git push
```

Tras esto:

- `pass` re‑cifra todas las entradas con la nueva clave.
- El almacen físico (`~/.password-store`) sigue intacto, solo cambia el cifrado.


### 3. Si definitivamente ya no tienes la clave antigua

Si la clave antigua **no existe en ningún backup**, entonces:

- No puedes descifrar las entradas actuales en `~/.password-store`.
- Para no eliminar el almacen, puedes simplemente **dejarlo “inerte”** como backup “offline” y crear un nuevo repositorio:

```bash
mkdir ~/.password-store-old
mv ~/.password-store/* ~/.password-store-old/
mv ~/.password-store-old/.git ~/.password-store/  # si usas Git
pass init ID_DE_TU_CLAVE_NUEVA
```

Así conservas el almacen cifrado por si un día recuperas la clave antigua, y a la vez usas uno nuevo con la clave actual.

***

Si me pegas el resultado de:

```bash
gpg --list-secret-keys
cat ~/.password-store/.gpg-id
```

te digo exactamente qué opción aplicar y te escribo los comandos listos para copiar‑pegar.
<span style="display:none">[^3_1][^3_10][^3_2][^3_3][^3_4][^3_5][^3_6][^3_7][^3_8][^3_9]</span>

<div align="center">⁂</div>

[^3_1]: https://maslinux.wordpress.com/2017/06/24/pass-simple-gestor-de-contrasenas-para-gnulinux/

[^3_2]: https://docs.redhat.com/es/documentation/red_hat_enterprise_linux/8/html/planning_identity_management/restoring-encrypted-backup_backing-up-and-restoring-idm

[^3_3]: https://blog.parravidales.es/gestiona-tus-contrasenas-y-secretos-con-pass/

[^3_4]: https://www.hackplayers.com/2017/06/pass-gestiona-tus-contrasenas-desde-el-terminal.html

[^3_5]: https://osiux.com/2021-02-19-no-me-acuerdo-de-nada-dejame-en-pass.html

[^3_6]: https://moribundo.flounder.online/blog/001_gestor_contrase%C3%B1as_pass.gmi

[^3_7]: https://www.redeszone.net/2016/08/25/pass-un-simple-y-seguro-administrador-de-contrasenas-de-codigo-abierto/

[^3_8]: https://joshblais.com/blog/migrate-password-store-gpg/

[^3_9]: https://www.reddit.com/r/yubikey/comments/p292b4/recovering_lost_gpg_public_keys_from_your_yubikey/

[^3_10]: https://www.reddit.com/r/linuxquestions/comments/18zhtvn/passwordstore_and_gpg_keys/


---

# He logrado importar la clave vieja y funciona. Ahora que pasos debo seguir para actualizar pass con la nueva clave GPG

Perfecto, que ya veas la clave vieja y puedas hacer `pass show token/github` significa que:

- El almacen (`~/.password-store`) está intacto y **todavía cifrado con la clave antigua**.
- Ahora solo falta migrarlo a la **nueva clave GPG** sin perder nada.

A continuación te dejo los pasos canónicos para hacer exactamente eso.

***

### 1. Verifica y prepara

1. Asegúrate de que la **nueva clave GPG** también está en el sistema:

```bash
gpg --list-secret-keys
```

Anota el ID de la **nueva clave** (por ejemplo `12345678`).
2. Que el repositorio esté sincronizado (si usas Git):

```bash
cd ~/.password-store
git add . && git status
git pull
```


***

### 2. Cambiar la clave maestra de `pass`

`pass` te permite cambiar la clave con la que está cifrada el store simplemente con `pass init` usando la nueva ID:

```bash
pass init ID_DE_LA_CLAVE_NUEVA
```

Ejemplo:

```bash
pass init 12345678
```

Cuando lo ejecutes:

- `pass` re‑cifra automáticamente **todas las entradas** del almacen con la nueva clave.
- El contenido del directorio `~/.password-store/` sigue siendo el mismo, solo cambia el cifrado.
- Si usas Git, verás un montón de commits nuevos porque cada archivo se re‑cifra.

***

### 3. Verificar que todo funciona

Prueba uno o dos ítems:

```bash
pass show token/github
pass -c token/github
```

Si ya no te pide la passphrase de la **clave vieja** y se copia en el portapapeles, entonces:

- La migración ya está lista.
- Puedes dejar la clave vieja en el llavero (por si quieres algún backup histórico) o borrarla si quieres.

***

### 4. (Opcional) Limpieza de claves

Si ya no necesitas la clave vieja en ningún otro lugar:

```bash
gpg --delete-secret-keys ID_DE_LA_CLAVE_VIEJA
```

Y luego:

```bash
gpg --list-secret-keys
```

para confirmar que solo queda la clave nueva.

***

### 5. Si compartes el store con otros

Si el repositorio de `pass` está en Git y lo usas en otros equipos:

En cada equipo donde uses el store:

```bash
pass git pull
pass init ID_DE_LA_CLAVE_NUEVA
```

Esto asegura que todos los equipos cifren/descifren con la misma clave nueva.

***

Si quieres, pega el output de:

```bash
gpg --list-secret-keys
```

y te digo exactamente qué ID poner en `pass init ID_...` para que no tengas que adivinar.
<span style="display:none">[^4_1][^4_10][^4_2][^4_3][^4_4][^4_5][^4_6][^4_7][^4_8][^4_9]</span>

<div align="center">⁂</div>

[^4_1]: https://voragine.net/linux/migrar-gestor-contrasenas-pass

[^4_2]: https://kb.mailfence.com/es/kb/cambiar-par-de-claves-openpgp/

[^4_3]: https://blog.parravidales.es/gestiona-tus-contrasenas-y-secretos-con-pass/

[^4_4]: https://www.reddit.com/r/GnuPG/comments/10hx44l/i_have_a_very_old_gpg_key_and_want_a_new_one_what/

[^4_5]: https://lignux.com/guia-completa-pass/

[^4_6]: https://www.gnupg.org/gph/es/manual.html

[^4_7]: https://www.youtube.com/watch?v=9PeCiNweRKI

[^4_8]: https://www.youtube.com/watch?v=-0yaJB1znFw

[^4_9]: https://www.reddit.com/r/linuxquestions/comments/18zhtvn/passwordstore_and_gpg_keys/

[^4_10]: https://serverspace.io/es/support/help/usage-of-gpg-keys/

