#  dotfiles en el Host (fresh install)

En este caso, se utiliza Ubuntu 24.04 para el contenedor.

- Instalar paquetes esenciales

```bash
    apt install pass
```


- Install chezmoi

```bash
    sh -c "$(curl -fsLS get.chezmoi.io)"
```

-  Aplicar dotfiles 

```bash
    chezmoi init https://github.com/tu-usuario/dotfiles-chezmoi.git
    chezmoi cd
    chezmoi apply
    exit
```


