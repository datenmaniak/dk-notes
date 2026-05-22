<img src="https://r2cdn.perplexity.ai/pplx-full-logo-primary-dark%402x.png" style="height:64px;margin-right:32px"/>

# al ejecutar esto: eval "\$(ssh-agent -s)"  se produce este error: mkdtemp: private socker dir: No such file or directory

El error "mkdtemp: private socket dir: No such file or directory" al ejecutar `eval "$(ssh-agent -s)"` indica que ssh-agent no puede crear un directorio temporal privado para su socket, generalmente por problemas de permisos en `/tmp` o variables de entorno mal configuradas.[^1_1][^1_5]

## Causas comunes

- **Permisos en /tmp**: ssh-agent necesita crear directorios temporales seguros en `/tmp`. Si `/tmp` tiene permisos restrictivos (como sticky bit mal configurado o montado con `noexec`), falla.[^1_1]
- **Directorio \$TMPDIR**: Si la variable `TMPDIR` apunta a un directorio inexistente o sin permisos, ssh-agent usa eso en lugar de `/tmp`.[^1_5]
- **Entorno contenedorizado**: En Fedora Kinoite (tu distro probable), Podman/Distrobox o entornos flatpak pueden restringir creación de sockets temporales.[^1_1]


## Soluciones paso a paso

1. Verifica permisos de `/tmp`:

```
ls -ld /tmp
```

Debe mostrar `drwxrwxrwt`. Si no, corrige con `sudo chmod 1777 /tmp`.[^1_1]
2. Limpia variables de entorno temporales:

```
unset TMPDIR
eval "$(ssh-agent -s)"
```

3. Usa un directorio temporal alternativo:

```
export TMPDIR=~/tmp
mkdir -p ~/tmp
chmod 700 ~/tmp
eval "$(ssh-agent -s)"
```

4. Para Fedora Kinoite/Podman, inicia ssh-agent con systemd:

```
systemctl --user start ssh-agent
systemctl --user enable ssh-agent
eval "$(ssh-agent -s)"
```

O añade a `~/.bashrc` o `~/.zshrc`:

```
if [ -z "$SSH_AUTH_SOCK" ]; then
    eval "$(ssh-agent -s)"
    ssh-add ~/.ssh/id_rsa  # tu clave
fi
```[^1_9]

```

5. Si usas Distrobox, ejecuta dentro del contenedor:

```
distrobox enter --additional-flags "--tmpfs /tmp" tu-contenedor
```


Prueba la solución 2 primero (unset TMPDIR), es la más común en entornos Linux modernos.[^1_5][^1_1]
<span style="display:none">[^1_10][^1_2][^1_3][^1_4][^1_6][^1_7][^1_8]</span>

<div align="center">⁂</div>

[^1_1]: https://www.reddit.com/r/archlinux/comments/1jiad7h/eval_sshagent_returns_mkdtemp_error/

[^1_2]: https://es.unixlinux.online/ix/1002044884.html

[^1_3]: https://www.1password.community/discussions/developers/error-connecting-to-agent-no-such-file-or-directory/140776

[^1_4]: https://copyprogramming.com/howto/error-connecting-to-agent-no-such-file-or-directory

[^1_5]: https://github.com/PowerShell/Win32-OpenSSH/issues/1462

[^1_6]: https://resimyukle.tr/error-connecting-to-agent-no-such-file-or-directory-explained/

[^1_7]: https://stackoverflow.com/questions/65852243/attempting-to-add-my-ssh-key-to-the-ssh-agent-but-getting-no-such-file-or-direc

[^1_8]: https://forum.garudalinux.org/t/ssh-agent-works-ssh-add-doesnt/19373

[^1_9]: https://bbs.archlinux.org/viewtopic.php?id=257029

[^1_10]: https://docs.github.com/es/authentication/connecting-to-github-with-ssh/generating-a-new-ssh-key-and-adding-it-to-the-ssh-agent

