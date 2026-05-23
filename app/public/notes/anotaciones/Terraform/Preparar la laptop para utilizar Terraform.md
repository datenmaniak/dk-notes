
Ahora que el servidor tiene la plantilla lista, el siguiente paso es preparar tu laptop (Fedora Aurora) para que sea el centro de mando. No vamos a clonar la máquina desde la web de Proxmox; vamos a dejar que Terraform lo haga.


### Paso 1: Instalar Terraform

```bash
# usar Distrobox para instalar
distrobox enter
```


### Paso 2: Agregar repositorios
 
```bash 


sudo dnf install -y dnf-plugins-core
sudo dnf config-manager addrepo --from-repofile=https://rpm.releases.hashicorp.com/fedora/hashicorp.repo
sudo dnf -y install terraform

```

### Paso 3: Exportar el binario fuera del contenedor


```bash
which terraform
/usr/bin/terraform

📦[datenmaniak@my-distrobox]~% distrobox-export --bin /usr/bin/terraform
/usr/bin/terraform from my-distrobox exported successfully in /home/datenmaniak/.local/bin.
OK!

📦[datenmaniak@my-distrobox]~% exit

```

### Paso 4: Verificar la instalacion 

```bash
❯ terraform -version
Terraform v1.14.8
on linux_amd64
```

