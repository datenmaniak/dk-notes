En este caso, arrancamos la configuracion del entorno en Fedora Cosmic.


### Primeros paquetes despues de una instalacion fresca

```bash 
rpm-ostree install neovim alacritty  tmux zsh fira-code-fonts jetbrains-mono-fonts-all 
```

###  Instala el prompt

```bash
curl -sS https://starship.rs/install.sh | sh
```

### Cambiar al shell de preferencia

```bash
sudo usermod --shell /usr/bin/zsh $USER
```

### Reinicia la terminal

###  Llave GPG

		Algunos de los método preferido para portar la clave secreta:
		
		- USB cifrada,
		- `scp` seguro,
		- o canal seguro.

###  Hace disponible la llave secreta 

	En este caso, obtenga de mi llave USB encriptada:

```bash
sudo scp /run/media/willians/MyYubiKey/gpg/gpg-private-key.asc ~/

```

###   Desde el host origen al nuevo host
```bash
scp gpg-private-key.asc willians@192.168.122.29:~/
```

###   Importar la llave (en el nuevo host)
```bash
gpg --import gpg-secret-key.asc
```

### Chezmoi  (dotfiles)


###   Instalar *chezmoi*
```bash
sh -c "$(curl -fsLS get.chezmoi.io)" -- -b $HOME/.local/bin
```

### Importa los dotfiles

###     Descarga
```bash

chezmoi init https://github.com/<usuario>/dotfiles-chezmoi.git

```
###  Aplica 
```
chezmoi apply
```

# En este punto si no hubo fallos, han sido importados los *dotfiles*


### En caso de errores, siga los pasos a continuacion:

```
Claves SSH privadas listas para usar.
gpg: creado el directorio '/home/willians/.gngupg'
gpg: cifrado con clave EDCG, ID BC2683999999F
gpg: descifrado de la clave publica fallido: No tenemos la clave secreta
gpg: descrifrado falliddo: No tenemos la clave secreta
chezmoi: .gitconfig: exit status 2
```

###  Verificar qué claves GPG tienes

```bash
`gpg --list-secret-keys`
```

Aqui utilizaremos solamente como ejemplo, el ID: *BC2683999999F*

Si **no ves la clave** con BC2683999999F, entonces **no está disponible** en esta máquina.

### Exporta la clave privada desde la máquina original

En la máquina donde sí tienes la clave privada:

```bash
gpg --export-secret-keys --armor "BC2683999999F" > gpg-secret-key.asc
```


por: datenmaniak
30.03.25, 18h34
