En el proceso de recuperar los dotfiles en un nuevo host, es probable que se presente el siguiente error:

```bash
$ chezmoi apply
Claves SSH privadas listas para usar.
gpg: creado el directorio '/home/willians/.gngupg'
gpg: cifrado con clave EDCG, ID BC2683240135E
gpg: descifrado de la clave publica fallido: No tenemos la clave secreta
gpg: descrifrado falliddo: No tenemos la clave secreta
chezmoi: .gitconfig: exit status 2
```

significa que **la clave GPG usada para cifrar `~/.gitconfig` (u otro archivo) no está disponible en la nueva máquina**: tienes la clave pública, pero **no la clave privada**.

### 1. Qué está pasando

- En la máquina original:
    
    - `~/.gitconfig` o algún archivo fue cifrado con GPG usando una clave con ID `BC2683240135E`.
        
- En la nueva máquina:
    
    - `chezmoi apply` intenta descifrarlo,
        
    - `gpg` ve que el archivo está cifrado con esa clave pública,
        
    - pero **no encuentra la clave privada** para descifrar → `No secret key`.

### 2. Qué tienes que hacer en la nueva máquina

###  2.1. Verifica qué claves GPG tienes

```bash
`gpg --list-secret-keys`
```

Si **no ves la clave** con ID `...BC2683240135E`, entonces **no está disponible** en esta máquina.

## 2.2. Exporta la clave privada desde la máquina original

En la máquina donde sí tienes la clave privada:

```bash
gpg --export-secret-keys --armor "BC2683240135E" > gpg-secret-key.asc

```

(Si no sale con `BC2683240135E`, usa tu UID o email: `gpg --export-secret-keys --armor "tu-usuario@tu-email.com" > gpg-secret-key.asc`.)

###  2.3. Copia `gpg-secret-key.asc` a la nueva máquina

Método preferido:

- USB cifrada,
    
- `scp` seguro,
    
- o canal seguro.

### 2.4. Importa la clave privada en la nueva máquina

```bash
gpg --import gpg-secret-key.asc

```

Verifica:

```bash
gpg --list-secret-keys

```

Ahora deberías ver:

```bash
sec   ed25519 ... BC2683240135E
uid                 [ tu-usuario@tu-email.com ]

```

## 2.5. Vuelve a intentar `chezmoi apply`

```bash
chezmoi apply

```