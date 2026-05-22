<img src="https://r2cdn.perplexity.ai/pplx-full-logo-primary-dark%402x.png" style="height:64px;margin-right:32px"/>

# como descomprimir un archivo cuya extension es .gz

Para descomprimir un archivo `.gz` en Linux, macOS o WSL en Windows, lo más sencillo es usar `gzip` o `gunzip` en la terminal.[^1_1][^1_2][^1_3]

### Con comando `gzip`

Si el archivo se llama `archivo.txt.gz`:

```bash
gzip -d archivo.txt.gz
```

Esto te deja el archivo original `archivo.txt` y borra el `.gz` (por defecto).[^1_2][^1_3]

Si quieres mantener el fichero `.gz` sin borrarlo:

```bash
gzip -dk archivo.txt.gz
```


### Con comando `gunzip`

`gunzip` es un alias práctico de `gzip -d`:

```bash
gunzip archivo.txt.gz
```

Te genera el archivo descomprimido y elimina el `.gz`.[^1_3][^1_2]

### Si es un `.tar.gz` (mucho más común)

Si en vez de un simple `.gz` tienes un `.tar.gz` (por ejemplo `datos.tar.gz`):

```bash
tar -xzf datos.tar.gz
```

O con más detalle:

```bash
tar -xzvf datos.tar.gz
```

Esto descomprime y extrae todo el contenido del paquete.[^1_4][^1_5][^1_1]

Si me dices qué sistema usas (Linux, macOS o Windows) y el nombre exacto del archivo, te monto el comando ajustado para tu caso.
<span style="display:none">[^1_10][^1_6][^1_7][^1_8][^1_9]</span>

<div align="center">⁂</div>

[^1_1]: https://gist.github.com/jcaromiq/87319ea132135700a23305c82ee38899

[^1_2]: https://noviello.it/es/como-descomprimir-archivos-gz-con-gzip-en-linux/

[^1_3]: https://linuxize.com/post/how-to-unzip-gz-file/

[^1_4]: https://robertcastro.co/comprimir-descomprimir-gz-tar-gz-zip-linux/

[^1_5]: https://kinsta.com/es/blog/descomprimir-tar-gz/

[^1_6]: https://www.youtube.com/watch?v=GRhl5vXcO9I

[^1_7]: https://www.youtube.com/watch?v=rRsQ02M3tAA

[^1_8]: https://www.reddit.com/r/unity/comments/1mimuvt/need_help_with_unzipping_a_gz_file/

[^1_9]: https://www.ezyzip.com/abrir-archivos-tar-gz-en-linea.html

[^1_10]: https://www.reviversoft.com/es/file-extensions/gz

