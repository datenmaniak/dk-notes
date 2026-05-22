<img src="https://r2cdn.perplexity.ai/pplx-full-logo-primary-dark%402x.png" style="height:64px;margin-right:32px"/>

# como instalo una lista de fuentes .ttf en Fedora Cosmic, desde la terminal

Para instalar una lista de fuentes `.ttf` en Fedora Cosmic desde la terminal hay dos formas típicas: a nivel de usuario o de todo el sistema.[^1_1][^1_2][^1_3]

***

### 1. Instalar para tu usuario (recomendado)

Si quieres solo que tú puedas usarlas (sin tocar el sistema global):

```bash
# Creas (si no existe) el directorio de fuentes locales
mkdir -p ~/.local/share/fonts

# Copias todos los .ttf de tu carpeta al directorio local de fuentes
cp /ruta/donde/tienes/las/fuentes/*.ttf ~/.local/share/fonts/

# Construyes/reconstruyes la caché de fuentes
fc-cache -fv ~/.local/share/fonts
```

Reemplaza `/ruta/donde/tienes/las/fuentes` por la ruta real donde están tus `.ttf`.[^1_2][^1_1]

***

### 2. Instalar para todo el sistema (todas las cuentas)

Si quieres que todas las cuentas de usuario las vean:

```bash
# Creas o aseguras el directorio del sistema
sudo mkdir -p /usr/local/share/fonts/tu-nombre

# Copias las fuentes
sudo cp /ruta/de/tus/fuentes/*.ttf /usr/local/share/fonts/tu-nombre/

# Actualizas la caché de fuentes
sudo fc-cache -fv
```

Esto suele ser útil si es un equipo compartido o servidor.[^1_3][^1_1]

***

### 3. Opcional: instalar vía `dnf` si son paquetes `.ttf`

Si tus fuentes están empaquetadas como `.rpm` (por ejemplo, fuentes MS o Google Fonts ya empaquetadas para Fedora), puedes usar:

```bash
sudo dnf install /ruta/a/tus/fuentes/*.ttf
```

Fedora interpreta algunos `.ttf` como paquetes de fuentes y `dnf` las instala en el sistema.[^1_3]

***

Con cualquiera de estos métodos, después de `fc-cache -fv` deberían estar disponibles en tus aplicaciones (LibreOffice, gedit, VS Code, etc.).[^1_1][^1_2]
<span style="display:none">[^1_10][^1_4][^1_5][^1_6][^1_7][^1_8][^1_9]</span>

<div align="center">⁂</div>

[^1_1]: https://alexhost.com/es/faq/how-to-install-fonts-on-gnu-linux/

[^1_2]: https://gist.github.com/dolftax/b89bef3ad133d1c62d9d

[^1_3]: https://www.tutorialspoint.com/article/how-to-add-new-fonts-in-fedora

[^1_4]: http://recursostic.es/observatorio/version/v2/fr/software/software-educativo/387-instalacion-fuentes-ttf-mis-primeras-fuentes

[^1_5]: https://guialinux.net/instalar-fuentes-tipograficas-en-linux/

[^1_6]: https://blog.desdelinux.net/how-to-fedora-instalar-fuentes-tipograficas-de-windows/

[^1_7]: https://www.youtube.com/watch?v=8ifNnpNCiI4

[^1_8]: https://ru.maisfontes.com/blog/como-instalar-fuentes-en-linux

[^1_9]: https://blog.desdelinux.net/en/install-linux-fonts/

[^1_10]: https://www.reddit.com/r/Fedora/comments/1jo4d5s/best_way_to_install_microsoft_truetype_fonts_on/

