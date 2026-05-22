<img src="https://r2cdn.perplexity.ai/pplx-full-logo-primary-dark%402x.png" style="height:64px;margin-right:32px"/>

# cual es el procedimiento regular cuando se agrega informacion a chezmoi

El procedimiento regular para agregar información (archivos o directorios) a chezmoi implica usar el comando `chezmoi add`. Este comando incorpora archivos del directorio destino (como tu home) al estado fuente gestionado por chezmoi, sobrescribiendo si ya existe.[^1_1]

## Pasos Básicos

- Edita o crea el archivo en su ubicación habitual (ej. `~/.bashrc`).
- Ejecuta `chezmoi add <ruta-al-archivo>` para añadirlo al repositorio fuente (por defecto `~/.local/share/chezmoi`).
- Usa `chezmoi status` para verificar cambios, y `chezmoi diff` para revisar diferencias antes de confirmar.[^1_5][^1_1]


## Opciones Comunes

- `--template` o `-T`: Marca el archivo como plantilla para variables dinámicas.
- `--recursive` o `-r`: Añade directorios recursivamente (activado por defecto).
- `--exact`: Para directorios, evita plantillas en subdirectorios.
- `--encrypt`: Encripta archivos sensibles (requiere configuración previa).[^1_1]


## Flujo Completo

Después de `add`, haz commit con git (`chezmoi git --commit`) y aplica en otras máquinas con `chezmoi apply`. Si editas directamente en destino, usa `chezmoi re-add` para actualizar sin perder plantillas.[^1_5]
<span style="display:none">[^1_10][^1_2][^1_3][^1_4][^1_6][^1_7][^1_8][^1_9]</span>

<div align="center">⁂</div>

[^1_1]: https://chezmoi.io/reference/commands/add/

[^1_2]: https://tiendamia.com/producto?amz=B01M66TT83

[^1_3]: https://www.youtube.com/watch%3Fv=Xsh2DGSe6Lg

[^1_4]: https://www.jackierueda.com/chez-moi/

[^1_5]: https://chezmoi.io/user-guide/frequently-asked-questions/usage/

[^1_6]: https://dogv.gva.es/datos/2014/09/01/pdf/2014_7937.pdf

[^1_7]: https://www.reddit.com/r/linux/comments/afogsb/chezmoi_manage_your_dotfiles_securely_across/%3Ftl=es-419

[^1_8]: https://www.tripadvisor.es/Restaurant_Review-g187234-d6463433-Reviews-Chez_Moi-Nice_French_Riviera_Cote_d_Azur_Provence_Alpes_Cote_d_Azur.html

[^1_9]: https://chezmoi.io/quick-start/

[^1_10]: https://www.gorterhof.nl/De-Colcha-De-3-Piezas-Yaxa-1005435/


---

# supongamos que quiero agregar los plugins de tmux localizado en ~/.tmux/

Para agregar los plugins de tmux en `~/.tmux/` (que suele contener `.tmux/plugins/` con TPM y otros), usa el comando recursivo de chezmoi, ya que es un directorio con subdirectorios. Esto incorpora todo al repositorio fuente sin sobrescribir plantillas existentes.[^2_1]

## Comando Principal

Ejecuta directamente:

```
chezmoi add ~/.tmux --recursive
```

Esto añade el directorio completo, incluyendo plugins instalados por TPM (como `~/.tmux/plugins/tpm`). Verifica primero con `chezmoi status` o `chezmoi diff` para revisar cambios.[^2_4][^2_1]

## Consideraciones Específicas

- **Plugins dinámicos**: Ignora archivos generados por TPM (ej. cachés o saves de resurrect) editando `.chezmoiregex` en tu repo fuente para excluir patrones como `*.tpm` o `saved-session.yml`, evitando commits innecesarios.[^2_3][^2_4]
- **TPM externo**: Si usas symlinks o repos externos (recomendado para actualizaciones automáticas), crea `.chezmoiexternal.toml` en tu fuente para clonar `https://github.com/tmux-plugins/tpm` en `~/.tmux/plugins/tpm` y mantenerlo actualizado con `chezmoi update`.[^2_3]
- **Opciones útiles**: `--exact` si no quieres plantillas en subdirs; `--follow` para resolver symlinks; `--prompt` para confirmar archivo por archivo.[^2_1]


## Flujo Completo

1. Asegúrate de que plugins estén instalados (ej. `~/.tmux/plugins/tpm/install_plugins.sh` si es necesario).
2. `chezmoi add ~/.tmux --recursive`.
3. `chezmoi git --commit -m "Add tmux plugins"`.
4. En otras máquinas: `chezmoi apply` (plugins se clonan si usas externos).[^2_1][^2_3]
<span style="display:none">[^2_2][^2_5][^2_6][^2_7][^2_8][^2_9]</span>

<div align="center">⁂</div>

[^2_1]: https://chezmoi.io/reference/commands/add/

[^2_2]: https://www.youtube.com/watch?v=L3kJytIvvzk

[^2_3]: https://www.lorenzobettini.it/2025/04/installing-the-tmux-plugin-manager-tpm-with-chezmoi/

[^2_4]: https://github.com/twpayne/chezmoi/issues/3780

[^2_5]: https://www.reddit.com/r/linuxmasterrace/comments/15nbwzp/please_help_how_to_migratemove_my_previous_vim/

[^2_6]: https://github.com/twpayne/chezmoi/issues/3784

[^2_7]: https://chezmoi.io

[^2_8]: https://news.ycombinator.com/item?id=32636051

[^2_9]: https://www.lorenzobettini.it/2025/11/maintaining-kde-dotfiles-with-chezmoi-modify-manager/

