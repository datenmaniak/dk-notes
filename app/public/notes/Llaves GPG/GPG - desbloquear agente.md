<img src="https://r2cdn.perplexity.ai/pplx-full-logo-primary-dark%402x.png" style="height:64px;margin-right:32px"/>

# ✗ gpg --list-keys

gpg: Note: database_open 134217901 waiting for lock (held by 6682) ...
gpg: Note: database_open 134217901 waiting for lock (held by 6682) ...
gpg: Note: database_open 134217901 waiting for lock (held by 6682) ...
gpg: Note: database_open 134217901 waiting for lock (held by 6682) ...

El error indica que GPG está esperando un bloqueo de base de datos retenido por el proceso 6682, lo que impide acceder a las claves.[^1_1][^1_7]

## Verificar proceso

Ejecuta `ps aux | grep 6682` para confirmar si el proceso 6682 sigue activo y qué está ejecutando (puede ser un `gpg-agent`, `keyboxd` o proceso colgado).[^1_10]

## Eliminar bloqueo huérfano

Si el proceso no existe o está colgado, elimina el archivo de bloqueo:

```
rm ~/.gnupg/public-keys.d/pubring.db.lock
```

También revisa y elimina otros `.lock` en `~/.gnupg/*` o `~/.gnupg/private-keys-v1.d/` si persiste.[^1_3][^1_7][^1_1]

## Reiniciar agente GPG

Luego ejecuta `gpgconf --kill gpg-agent` para limpiar el agente y prueba `gpg --list-keys` nuevamente.[^1_7]
<span style="display:none">[^1_2][^1_4][^1_5][^1_6][^1_8][^1_9]</span>

<div align="center">⁂</div>

[^1_1]: https://stackoverflow.com/questions/79266824/git-not-committing-gpg-stuck-in-database-open-waiting-for-lock

[^1_2]: https://lists.gnupg.org/pipermail/gnupg-users/2025-October/067829.html

[^1_3]: https://documentation.breadnet.co.uk/kb/linux-cli/gpg-note-database_open-waiting-for-lock-held-by/

[^1_4]: https://bbs.archlinux.org/viewtopic.php?pid=2198739

[^1_5]: https://www.reddit.com/r/debian/comments/cdgrik/gpg_e_hangs_checking_the_trustdb_removing_stale/

[^1_6]: https://luke.carrier.im/notes/p8x4128bt98vk5zcnqkwaa1/

[^1_7]: https://gist.github.com/bahadiraraz/f2fb15b07e0fce92d8d5a86ab33469f7?permalink_comment_id=5515139

[^1_8]: https://blog.ordinaryroad.tech/1/article/1771710383747665920

[^1_9]: https://bbs.archlinux.org/viewtopic.php?id=209892

[^1_10]: https://discourse.nixos.org/t/gpg-pubring-db-always-locked-after-reboot/51933

