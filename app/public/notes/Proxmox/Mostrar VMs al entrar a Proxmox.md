<img src="https://r2cdn.perplexity.ai/pplx-full-logo-primary-dark%402x.png" style="height:64px;margin-right:32px"/>

# Necesito ejecutar un comando para listar las VM, cada vez que hago un login al servidor Proxmox.

El comando es `qm list`, y te muestra las VM del nodo Proxmox con su ID, nombre y estado.[^1_1][^1_2]

## Hacerlo automático al login

Si quieres que se ejecute cada vez que entras por SSH, añade el comando al archivo `~/.bash_profile`, `~/.profile` o `~/.bashrc` del usuario con el que inicias sesión, según tu shell y configuración. Por ejemplo:

```bash
qm list
```

Si prefieres que se ejecute solo cuando entras por SSH y no en cada terminal interactiva, puedes ponerlo en `~/.bash_profile` con una condición como esta:

```bash
if [ -n "$SSH_CONNECTION" ]; then
  qm list
fi
```


## Opción más limpia

Si no quieres que se ejecute para todos los usuarios, puedes configurarlo solo para tu cuenta en el servidor. También puedes hacer que muestre un mensaje más útil, por ejemplo `echo "VMs en este nodo:"; qm list`.[^1_3][^1_4]

## Nota

`qm list` lista las VM del nodo donde estás conectado; si tienes un clúster, para ver nodos del clúster el comando sería distinto.[^1_4]
<span style="display:none">[^1_10][^1_5][^1_6][^1_7][^1_8][^1_9]</span>

<div align="center">⁂</div>

[^1_1]: https://www.techsyncer.com/es/top-10-proxmox-cli-commands-every-admin-should-know.html

[^1_2]: https://www.lastlogin.org/2020/03/07/comandos-para-usarlos-en-proxmox-ve/

[^1_3]: https://blogdesistemas.com/comandos-basicos-proxmox/

[^1_4]: https://www.nakivo.com/es/blog/top-10-proxmox-cli-commands/

[^1_5]: https://www.assetware-technology.com/es/administre-proxmox-con-la-interfaz-de-linea-de-comando/

[^1_6]: https://www.techsyncer.com/es/top-10-proxmox-cli-commands.html

[^1_7]: https://www.maquinasvirtuales.eu/comandos-basicos-proxmox/

[^1_8]: https://lavariega.com/administra-proxmox-desde-consola/

[^1_9]: https://www.nakivo.com/fr/blog/top-10-proxmox-cli-commands/

[^1_10]: https://www.youtube.com/watch?v=JtQQ5wj78N8

