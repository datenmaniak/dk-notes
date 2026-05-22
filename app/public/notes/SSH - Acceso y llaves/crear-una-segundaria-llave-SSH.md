<img src="https://r2cdn.perplexity.ai/pplx-full-logo-primary-dark%402x.png" style="height:64px;margin-right:32px"/>

# actualmente tengo mis llaves SSH con passphrase. Sin embargo, para propositos de acceso y administracion de servidores, necesito una sin passphrase... Puedo crear llaves SSH adicional sin afectar la existente.?

Sí, puedes crear llaves SSH adicionales sin passphrase sin afectar las existentes. Las claves SSH se almacenan por separado en `~/.ssh/`, y generar nuevas no modifica las actuales.[^1_1][^1_6]

## Generar nueva clave

Ejecuta `ssh-keygen -t ed25519 -f ~/.ssh/id_ed25519_servidor` (o `-t rsa -b 4096` si prefieres RSA). Cuando pida la passphrase, presiona Enter dos veces para dejarla vacía. Esto crea `id_ed25519_servidor` (privada) y `id_ed25519_servidor.pub` (pública) sin sobrescribir tus claves existentes.[^1_3][^1_1]

## Copiar al servidor

Usa `ssh-copy-id -i ~/.ssh/id_ed25519_servidor.pub usuario@servidor` para agregar la clave pública al `~/.ssh/authorized_keys` del servidor remoto. Si no tienes `ssh-copy-id`, copia manualmente: `cat ~/.ssh/id_ed25519_servidor.pub | ssh usuario@servidor 'mkdir -p ~/.ssh && cat >> ~/.ssh/authorized_keys'` [^1_2][^1_3].

## Usar la clave específica

Conéctate con `ssh -i ~/.ssh/id_ed25519_servidor usuario@servidor`. SSH la seleccionará automáticamente si es la única para ese host, o configúrala en `~/.ssh/config`:

```
Host servidor
    HostName ip_o_dominio
    User usuario
    IdentityFile ~/.ssh/id_ed25519_servidor
```

Esto facilita el acceso sin passphrase solo para administracion, manteniendo tus claves seguras para otros usos.[^1_1]
<span style="display:none">[^1_10][^1_4][^1_5][^1_7][^1_8][^1_9]</span>

<div align="center">⁂</div>

[^1_1]: https://blog.infranetworking.com/como-conectar-via-ssh-sin-password-usando-llaves-publicas/

[^1_2]: https://dabad.es/sistemas/conexion-ssh-sin-password/

[^1_3]: https://hostinglabs.net/asistencia/base-de-conocimientos/generar-llaves-para-iniciar-sesion-sin-contrasena-en-servidores-linux/

[^1_4]: https://raiolanetworks.com/ayuda/clave-ssh/

[^1_5]: https://www.servidoresadmin.com/conexion-ssh-sin-password-en-linux-forma-mas-simple/

[^1_6]: https://www.hostinger.com/mx/tutoriales/configurar-ssh-sin-contrasena-linux

[^1_7]: https://clientes.hostinglabs.net/knowledgebase/2102/Generar-llaves-para-iniciar-sesion-sin-contrasena-en-servidor-Linux.html

[^1_8]: https://help.clouding.io/hc/es/articles/4406705095954-Creación-de-llaves-con-ssh-keygen

[^1_9]: https://www.youtube.com/watch?v=QmiLPGVvXpE

[^1_10]: https://adictosalinux.com/configurar-ssh-sin-contrasena/


---

# puedo crearla asociando un email, y luego al autenticar ejecutar ssh -i ~/.ssh/nueva-llave ?

Sí, puedes generar una nueva llave SSH asociando un email como comentario (con `-C`), y luego usarla específicamente con `ssh -i ~/.ssh/nueva_llave` para autenticar sin afectar tus claves existentes.[^2_1]

## Crear la llave con email

Ejecuta `ssh-keygen -t ed25519 -C "tu_email@ejemplo.com" -f ~/.ssh/nueva_llave`. Presiona Enter dos veces cuando pida la passphrase para dejarla vacía. El email se agrega como etiqueta en la fingerprint de la llave, útil para identificarla (ej. en `ssh-add -l`), pero no cambia su funcionalidad.[^2_7][^2_1]

## Autenticar con ella

Para conectar: `ssh -i ~/.ssh/nueva_llave usuario@servidor`. SSH usará esa llave privada específica, ignorando las demás. Copia primero la pública con `ssh-copy-id -i ~/.ssh/nueva_llave.pub usuario@servidor`.[^2_2][^2_4]
<span style="display:none">[^2_10][^2_3][^2_5][^2_6][^2_8][^2_9]</span>

<div align="center">⁂</div>

[^2_1]: https://docs.github.com/es/authentication/connecting-to-github-with-ssh/generating-a-new-ssh-key-and-adding-it-to-the-ssh-agent

[^2_2]: https://help.ovhcloud.com/csm/es-public-cloud-compute-additional-ssh-keys?id=kb_article_view\&sysparm_article=KB0050572

[^2_3]: https://blog.infranetworking.com/como-conectar-via-ssh-sin-password-usando-llaves-publicas/

[^2_4]: https://voragine.net/linux/acceso-ssh-seguro-servidor-autenticacion-clave-publica

[^2_5]: https://docs.github.com/es/enterprise-cloud@latest/authentication/connecting-to-github-with-ssh/generating-a-new-ssh-key-and-adding-it-to-the-ssh-agent

[^2_6]: https://gist.github.com/benjavicente/7614dd15ee6046355909d5e21a23dde6

[^2_7]: https://www.q2bstudio.com/nuestro-blog/17650/conecta-servidores-linux-por-ssh-sin-contrasena-guia-paso-a-paso

[^2_8]: https://www.stackscale.com/es/blog/configurar-llaves-ssh-servidor-linux/

[^2_9]: https://hostinglabs.net/asistencia/base-de-conocimientos/generar-llaves-para-iniciar-sesion-sin-contrasena-en-servidores-linux/

[^2_10]: https://docs.aws.amazon.com/es_es/AWSEC2/latest/UserGuide/connect-linux-inst-ssh.html

