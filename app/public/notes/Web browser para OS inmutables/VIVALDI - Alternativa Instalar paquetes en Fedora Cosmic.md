<img src="https://r2cdn.perplexity.ai/pplx-full-logo-primary-dark%402x.png" style="height:64px;margin-right:32px"/>

# ayudame a instalar Vivaldi en Fedora Cosmic

Para instalar Vivaldi en Fedora Cosmic (una variante inmutable de Fedora), la forma más recomendada es descargar el paquete RPM oficial desde el sitio de Vivaldi y usar `rpm-ostree` para layerlo, ya que no está en los repos por defecto y Fedora Cosmic prefiere capas sobre paquetes nativos.

## Método RPM Oficial (Recomendado)

1. Ve a [vivaldi.com/download](https://vivaldi.com/download/) y descarga el archivo **Linux RPM 64bit**.
2. Abre una terminal (usa `Ctrl+Alt+T` o tu terminal preferida como Alacritty/WezTerm).
3. Navega a Descargas: `cd ~/Descargas`.
4. Instala con rpm-ostree:

```
rpm-ostree install ./vivaldi-stable_*.rpm
```

5. Reinicia el sistema: `systemctl reboot`.
6. Tras reiniciar, Vivaldi aparecerá en el menú de aplicaciones.[^1_1][^1_5][^1_7]

## Método Flatpak (Alternativa sandboxed)

Si prefieres aislamiento (ideal para tu setup con Podman/Distrobox):

1. Asegúrate de tener Flathub: `flatpak remote-add --if-not-exists flathub https://flathub.org/repo/flathub.flatpakrepo`.
2. Instala: `flatpak install flathub com.vivaldi.Vivaldi`.
3. Ejecuta: `flatpak run com.vivaldi.Vivaldi` o búscalo en el menú.[^1_3]

## Verificación y Uso

- Confirma la instalación: `rpm-ostree status` (busca la capa de Vivaldi).
- Lanza desde menú o `vivaldi-stable`.
- Actualizaciones: `rpm-ostree update` y reinicia.

Este enfoque respeta la inmutabilidad de Fedora Cosmic (como Kinoite) y evita repos externos. Si usas Distrobox, podrías correrlo en un contenedor Ubuntu para .deb, pero el RPM es directo.[^1_9][^1_1]
<span style="display:none">[^1_10][^1_2][^1_4][^1_6][^1_8]</span>

<div align="center">⁂</div>

[^1_1]: https://linuxcapable.com/install-vivaldi-on-fedora-linux/

[^1_2]: https://www.youtube.com/watch?v=8rAFsP_YyBk

[^1_3]: https://snapcraft.io/install/vivaldi/fedora

[^1_4]: https://forum.vivaldi.net/topic/65536/instalando-vivaldi-linux

[^1_5]: https://help.vivaldi.com/es/escritorio/instalacion-actualizacion/instalar-el-navegador-vivaldi/

[^1_6]: https://help.vivaldi.com/es/category/escritorio/instalacion-actualizacion/

[^1_7]: https://discussion.fedoraproject.org/t/install-vivaldi-browser/172180

[^1_8]: https://wiki.archlinux.org/title/Vivaldi_(Espa%C3%B1ol)

[^1_9]: https://computingforgeeks.com/install-vivaldi-browser-linux/

[^1_10]: https://vivaldi.com/es/download/

