# Segundo archivo con imagen embedida

```bash
 create-uuid-image.py notes/images/reparacion-computadores.png web 500 -d /tmp/ --code
Ejecutando: magick /var/home/datenmaniak/notes/images/reparacion-computadores.png -strip -density 72 -resize 390x500 -quality 85 -interlace Plane /tmp/7d611dd8-082c-59d7-acfe-f7e4eb7d1b8d.png
```

```plaintext
=== RESULTADOS ===
Archivo entrada     : /var/home/datenmaniak/notes/images/reparacion-computadores.png
Archivo salida      : /tmp/7d611dd8-082c-59d7-acfe-f7e4eb7d1b8d.png
DPI aplicado        : 72
Dimensiones orig.   : 912x1168
Dimensiones finales : 390x500
Tamaño original     : 1977.27 KB
Tamaño final        : 324.28 KB
Reducción           : 83.6%

### Código para embedir la imagen:
```

Markdown (sintaxis):
![reparacion-computadores](/tmp/7d611dd8-082c-59d7-acfe-f7e4eb7d1b8d.png)

Markdown (tag HTML):
<img src="/tmp/images/7d611dd8-082c-59d7-acfe-f7e4eb7d1b8d.png" alt="reparacion-computadores">

HTML:

<img src="/tmp/7d611dd8-082c-59d7-acfe-f7e4eb7d1b8d.png" alt="reparacion-computadores">

