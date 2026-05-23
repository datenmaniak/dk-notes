<img src="https://r2cdn.perplexity.ai/pplx-full-logo-primary-dark%402x.png" style="height:64px;margin-right:32px"/>

# como recupero mi clave publica GPG

Para recuperar tu clave pública GPG, primero lista las claves disponibles en tu keyring local y luego expórtala si la tienes ahí.[^1_1][^1_2][^1_3]

## Listar claves públicas

Ejecuta este comando para ver todas tus claves públicas GPG en el sistema:

```
gpg --list-keys
```

Esto muestra detalles como el ID de la clave (por ejemplo, un fingerprint como `F8F2A90AF7A9BFCC530BFC8F603DCFEBDFC063AB`), email y fecha.[^1_3][^1_4]

Si tienes claves secretas (privadas), usa `gpg --list-secret-keys --keyid-format=long` para confirmar que la clave existe localmente.[^1_5][^1_6]

## Exportar clave pública

Una vez identificada la clave por su ID, email o nombre (ej. 'Tu Nombre'), expórtala en formato ASCII-armored:

```
gpg --armor --export 'tu-email@ejemplo.com' > mi_clave_publica.asc
```

O por fingerprint:

```
gpg --armor --export F8F2A90AF7A9BFCC530BFC8F603DCFEBDFC063AB > mi_clave_publica.asc
```

El archivo `mi_clave_publica.asc` contendrá la clave pública lista para compartir o importar en otro sistema.[^1_7][^1_8][^1_1]

## Si no aparece en la lista

Si no la tienes localmente, búscala en un keyserver público:

```
gpg --keyserver hkps://keys.openpgp.org --recv-keys TU_FINGERPRINT_O_ID
```

Luego expórtala como arriba. Asegúrate de tener GPG instalado (en Fedora/Ubuntu: `sudo dnf install gnupg` o `sudo apt install gnupg`).[^1_9][^1_10]
<span style="display:none">[^1_11][^1_12][^1_13][^1_14][^1_15][^1_16][^1_17][^1_18][^1_19][^1_20]</span>

<div align="center">⁂</div>

[^1_1]: https://doc.hpc.iter.es/2023.03/user_guides/how_to_gpg_linux/

[^1_2]: https://www.gnupg.org/documentation/manuals/gnupg/Operational-GPG-Commands.html

[^1_3]: https://www.josedomingo.org/pledin/2023/11/criptografia-con-gpg/

[^1_4]: https://thelinuxcode.com/list-gpg-keys-linux/

[^1_5]: https://docs.github.com/es/authentication/managing-commit-signature-verification/checking-for-existing-gpg-keys

[^1_6]: https://docs.github.com/en/authentication/managing-commit-signature-verification/checking-for-existing-gpg-keys

[^1_7]: https://docs.fedoraproject.org/es/quick-docs/create-gpg-keys/

[^1_8]: https://gist.github.com/Killeroid/6361944d0694e474fb94cc42a3b119d1

[^1_9]: https://nebul4ck.wordpress.com/comandos-para-el-uso-de-gpg/

[^1_10]: https://www.tiizss.com/2018/02/06/solucionar-error-gpg-claves-publicas-caducadas-kali/

[^1_11]: https://lists.ubuntu.com/archives/ubuntu-es/2009-December/095538.html

[^1_12]: https://www.gnupg.org/gph/es/manual.html

[^1_13]: https://www.youtube.com/watch?v=YePvwCQ60C0

[^1_14]: https://experienceleague.adobe.com/es/docs/experience-cloud-kcs/kbarticles/ka-26046

[^1_15]: https://hijosdeinit.gitlab.io/howto_solucionar_error_gpg_clave_publica_repositorio_Debian_y_derivados/

[^1_16]: https://linuxhint.com/export-import-keys-with-gpg/

[^1_17]: https://gist.github.com/planetacomputer/b88885b06b7e8c85de71304077404f59

[^1_18]: https://docs.accedian.io/docs/gpg-keys

[^1_19]: https://stackoverflow.com/questions/65242815/how-can-i-display-actual-public-private-key-of-key-pair-i-just-generated-with-gp

[^1_20]: https://gist.github.com/chrisroos/1205934?permalink_comment_id=2203760

