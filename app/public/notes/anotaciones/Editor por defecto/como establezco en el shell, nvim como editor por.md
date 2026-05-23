<img src="https://r2cdn.perplexity.ai/pplx-full-logo-primary-dark%402x.png" style="height:64px;margin-right:32px"/>

# como establezco en el shell, nvim como editor por defecto. uso zsh

Para configurar Neovim (nvim) como editor por defecto en tu shell Zsh, agrega las variables de entorno necesarias en tu archivo de configuración.

## Pasos para configurar

Edita tu archivo `~/.zshrc` con tu editor actual (por ejemplo, `nano ~/.zshrc` o `vim ~/.zshrc`):

```
export EDITOR=nvim
export VISUAL=nvim
```

Guarda los cambios y recarga la configuración:

```
source ~/.zshrc
```


## Verificación

Confirma que se aplicó correctamente:

```
echo $EDITOR
```

Debería mostrar `nvim`.[^1_1]

## Para editores del sistema (Debian/Ubuntu/Fedora)

Si usas comandos como `crontab -e` o `sudo visudo`, configura también el alternativo del sistema:

```
sudo update-alternatives --config editor
```

Selecciona la opción de nvim (generalmente `/usr/bin/nvim`).[^1_7]

Esto afectará herramientas como `git commit`, `crontab` y otros que respeten `$EDITOR` o `$VISUAL`. Reinicia la terminal si no ves cambios inmediatos.[^1_1]
<span style="display:none">[^1_10][^1_2][^1_3][^1_4][^1_5][^1_6][^1_8][^1_9]</span>

<div align="center">⁂</div>

[^1_1]: https://www.reddit.com/r/linux4noobs/comments/w9jy94/setting_neovim_as_default_text_editor/

[^1_2]: https://blog.openreplay.com/es/instalar-configurar-zsh-shell-predeterminado/

[^1_3]: https://www.reddit.com/r/neovim/comments/1cctwki/nvterm_how_to_configure_to_open_zsh_rather_than/

[^1_4]: https://logico.ar/blog/2019/03/10/como-entrar-y-usar-el-modo-vi-en-zsh

[^1_5]: https://www.youtube.com/watch?v=353UyFVNoW8

[^1_6]: https://www.genbeta.com/herramientas/no-todo-bash-como-cambiar-tu-interprete-comandos-bash-a-zsh-e-instalar-oh-my-zsh-para-personalizarlo

[^1_7]: https://cambiatealinux.com/cambiar-el-editor-por-defecto-de-la-linea-de-comandos

[^1_8]: https://platzi.com/blog/instalar-y-configurar-zsh-como-predeterminado-en-ubuntu-y-derivados/

[^1_9]: https://github.com/josuerom/nvim-linux

[^1_10]: https://www.elarraydejota.com/como-cambiar-el-editor-de-texto-por-defecto-en-linux/

