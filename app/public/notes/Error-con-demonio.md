<img src="https://r2cdn.perplexity.ai/pplx-full-logo-primary-dark%402x.png" style="height:64px;margin-right:32px"/>

# error con GPG y Pass   ✗ gpg --list-keys

gpg: Note: database_open 134217901 waiting for lock (held by 16478) ...
gpg: Note: database_open 134217901 waiting for lock (held by 16478) ...
gpg: Note: database_open 134217901 waiting for lock (held by 16478) ...
^C

El error indica que GPG está esperando un bloqueo en la base de datos mantenido por el proceso PID 16478, común en versiones modernas con keyboxd.[^1_1][^1_2]

## Verificar proceso

Ejecuta `ps aux | grep 16478` para confirmar si el proceso (probablemente keyboxd o gpg-agent) sigue activo.[^1_1][^1_3]

## Solución segura

Si el proceso no es esencial, mátalo con `kill 16478` (o `kill -9 16478` si persiste). Luego, reinicia los agentes GPG: `gpgconf --kill gpg-agent keyboxd` y `gpgconf --reload gpg-agent`.[^1_4][^1_5][^1_6]

## Eliminar lock manual

Busca locks en `~/.gnupg/` o `~/.gnupg/public-keys.d/` con `find ~/.gnupg -name "*.lock"`, y elimínalos: `rm ~/.gnupg/public-keys.d/pubring.db.lock` (ajusta según corresponda).[^1_3][^1_7][^1_8]

Prueba `gpg --list-keys` después; si persiste, verifica `~/.gnupg/common.conf` por `use-keyboxd`.[^1_9]
<span style="display:none">[^1_10][^1_11][^1_12][^1_13][^1_14][^1_15][^1_16][^1_17][^1_18][^1_19][^1_20][^1_21][^1_22][^1_23][^1_24]</span>

<div align="center">⁂</div>

[^1_1]: https://discourse.nixos.org/t/gpg-pubring-db-always-locked-after-reboot/51933

[^1_2]: https://luke.carrier.im/notes/p8x4128bt98vk5zcnqkwaa1/

[^1_3]: https://stackoverflow.com/questions/79266824/git-not-committing-gpg-stuck-in-database-open-waiting-for-lock

[^1_4]: https://dev.gnupg.org/T7569

[^1_5]: https://www.youtube.com/watch?v=vsaMx7o9UDo

[^1_6]: https://github.com/microsoft/vscode-remote-release/issues/11017

[^1_7]: https://documentation.breadnet.co.uk/kb/linux-cli/gpg-note-database_open-waiting-for-lock-held-by/

[^1_8]: https://gist.github.com/bahadiraraz/f2fb15b07e0fce92d8d5a86ab33469f7

[^1_9]: https://www.gnupg.org/documentation/manuals/gnupg/GPG-Configuration.html

[^1_10]: https://www.reddit.com/r/debian/comments/cdgrik/gpg_e_hangs_checking_the_trustdb_removing_stale/

[^1_11]: https://bbs.archlinux.org/viewtopic.php?pid=2226987

[^1_12]: https://blog.ordinaryroad.tech/1/article/1771710383747665920

[^1_13]: https://lists.gnupg.org/pipermail/gnupg-users/2025-February.txt

[^1_14]: https://blog.ni18.in/fix-git-gpg-lock-issue/

[^1_15]: https://bbs.archlinux.org/viewtopic.php?id=299715

[^1_16]: https://manpages.ubuntu.com/manpages/noble/man1/keyboxd.1.html

[^1_17]: https://dev.to/debborafernandess/how-to-fix-git-com-gpg-lock-4p4h

[^1_18]: https://manpages.debian.org/testing/gpg/keyboxd.1.en.html

[^1_19]: https://www.youtube.com/watch?v=L6_CMb7Nquk

[^1_20]: https://manpages.debian.org/experimental/gpg/keyboxd.1.en.html

[^1_21]: https://github.com/nix-community/home-manager/issues/1490

[^1_22]: https://manpages.ubuntu.com/manpages/noble/man1/gpg-agent.1.html

[^1_23]: https://gist.github.com/CMCDragonkai/2a99df87f280df835e9b7e190d8f46ec

[^1_24]: https://www.gnupg.org/documentation/manuals/gnupg/Invoking-GPG_002dAGENT.html

