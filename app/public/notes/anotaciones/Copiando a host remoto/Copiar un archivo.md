<img src="https://r2cdn.perplexity.ai/pplx-full-logo-primary-dark%402x.png" style="height:64px;margin-right:32px"/>

# quiero copiar un archivo a un host remoto. tengo una llave SSH

La forma más directa es usar `scp` con tu llave SSH para subir el archivo al host remoto. Si tu llave privada no es la predeterminada, añade `-i`; si el servidor usa otro puerto, añade `-P`.[^1_1][^1_2][^1_3]

## Comando básico

```bash
scp archivo.txt usuario@host-remoto:/ruta/destino/
```

Ejemplo:

```bash
scp informe.pdf admin@192.168.1.10:/home/admin/
```


## Con llave SSH

Si tu llave privada está en otra ruta:

```bash
scp -i ~/.ssh/mi_llave archivo.txt usuario@host-remoto:/ruta/destino/
```

Ejemplo:

```bash
scp -i ~/.ssh/id_ed25519 informe.pdf admin@192.168.1.10:/home/admin/
```


## Si usas otro puerto

```bash
scp -P 2222 -i ~/.ssh/id_ed25519 archivo.txt usuario@host-remoto:/ruta/destino/
```


## Si quieres una carpeta

Usa `-r` para copiar directorios completos.[^1_2][^1_4]

```bash
scp -r -i ~/.ssh/id_ed25519 carpeta/ usuario@host-remoto:/ruta/destino/
```


## Alternativa útil

Si tu objetivo es solo habilitar acceso por llave al servidor, `ssh-copy-id` sirve para instalar tu clave pública en `authorized_keys`. Eso no copia archivos, pero facilita futuras conexiones y transferencias.[^1_5][^1_6]

Puedo darte el comando exacto si me pasas: ruta del archivo, usuario, host y puerto.
<span style="display:none">[^1_10][^1_7][^1_8][^1_9]</span>

<div align="center">⁂</div>

[^1_1]: https://geekytheory.com/copiar-archivos-a-traves-de-ssh-con-scp/

[^1_2]: https://www.swhosting.com/es/blog/como-transferir-archivos-entre-servidores-utilizando-ssh

[^1_3]: https://www.piensasolutions.com/blog/comando-scp-ejemplos-practicos-para-utilizarlo

[^1_4]: https://voidnull.es/scp-copiar-ficheros-a-traves-de-ssh/

[^1_5]: https://dtg.epet1.edu.ar/2021/08/ssh-copiar-claves-a-un-servidor-remoto/

[^1_6]: https://eltallerdelbit.com/copiar-clave-publica-ssh-scp-ssh-copy-id/

[^1_7]: https://1000demonios.com/como-copiar-los-archivos-con-ssh-y-pipe-a-un-host-remoto/

[^1_8]: https://newsmatic.com.ar/redes/como-usar-copia-segura-con-autenticacion-de-llave-ssh

[^1_9]: https://www.macosas.com/archives/scp-automatizar-las-copias-de-archivos/

[^1_10]: https://serverspace.io/es/support/help/copy-files-and-run-commands-through-ssh/

