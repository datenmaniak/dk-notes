<img src="https://r2cdn.perplexity.ai/pplx-full-logo-primary-dark%402x.png" style="height:64px;margin-right:32px"/>

# despues de agregar nueva clave a pass en el; host anfitrion, y de actualizar en el repo. Como actualizo en otro host.

Para sincronizar la nueva clave agregada a `pass` (el gestor de contraseñas password-store) desde el host principal al otro host después de actualizar el repositorio git, sigue estos pasos en el segundo host.

## Pasos en el host destino

Clona o asegúrate de tener el repositorio local de `pass` (usualmente en `~/.password-store`):

```
git clone <URL_DEL_REPO_GIT> ~/.password-store
```

O si ya existe, actualiza con pull:

```
cd ~/.password-store
git pull origin main
```

Esto trae los cambios, incluyendo la nueva clave encriptada con GPG.[^1_2]

## Verificar acceso GPG

Asegúrate de que la clave pública del host principal esté importada en el segundo host para desencriptar:

```
gpg --recv-keys <KEY_ID_DEL_HOST_PRINCIPAL>
```

Lista las claves para confirmar: `gpg --list-keys`.

## Probar la clave nueva

```
pass show nombre/de/la/nueva/clave
```

Si falla, verifica permisos del directorio (`chmod 700 ~/.password-store`) o init: `pass init <GPG_ID>`.

El flujo completo es: push en origen → pull en destino → acceso GPG local.[^1_2]
<span style="display:none">[^1_1][^1_10][^1_3][^1_4][^1_5][^1_6][^1_7][^1_8][^1_9]</span>

<div align="center">⁂</div>

[^1_1]: https://tsplus.net/es/remote-access/blog/how-to-change-password-on-remote-desktop/

[^1_2]: https://voragine.net/linux/migrar-gestor-contrasenas-pass

[^1_3]: https://support.ptc.com/help/windchill/r13.1.2.0/es/Windchill_Help_Center/filevault/FileVaultConfigUpdateHost.html

[^1_4]: https://serverspace.io/es/support/help/how-to-change-rdp-account-password/

[^1_5]: https://www.youtube.com/watch?v=RKAg8aqKDxg

[^1_6]: https://help.dreamhost.com/hc/es/articles/217239087-Actualizar-las-llaves-del-host

[^1_7]: https://www.youtube.com/watch?v=rIOieR9Zptk

[^1_8]: https://learn.microsoft.com/es-es/entra/identity/domain-services/tutorial-configure-password-hash-sync

[^1_9]: https://www.dell.com/support/kbdoc/es-ve/000168145/procedimiento-de-idpa-para-actualizar-la-contraseña-correcta-del-host-esx-en-acm

[^1_10]: https://www.sysadmit.com/2018/01/windows-cambiar-contrasena-desde-rdp.html

