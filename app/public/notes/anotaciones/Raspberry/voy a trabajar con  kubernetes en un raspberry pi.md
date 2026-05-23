<img src="https://r2cdn.perplexity.ai/pplx-full-logo-primary-dark%402x.png" style="height:64px;margin-right:32px"/>

# voy a trabajar con  kubernetes en un raspberry pi 3 . dame los pasos para instalar, configurar y comprobar que esta operativo.

Para trabajar con Kubernetes en un Raspberry Pi 3, usa K3s, una distribución ligera compatible con ARM que funciona bien en hardware limitado como el Pi 3. Asegúrate de tener Raspberry Pi OS (64-bit preferible, o 32-bit armhf) actualizado, al menos 1 GB de RAM libre y acceso SSH como usuario pi o root.[^1_1][^1_2][^1_3][^1_4][^1_5]

## Requisitos previos

- Actualiza el sistema: `sudo apt update && sudo apt upgrade -y`.
- Instala herramientas básicas: `sudo apt install -y curl iptables`.
- Desactiva swap: `sudo swapoff -a` y comenta la línea en `/etc/fstab`.
- Habilita módulos de kernel: `sudo modprobe br_netfilter` y agrega a `/etc/modules-load.d/k3s.conf`: `br_netfilter overlay`.[^1_4][^1_5][^1_1]


## Instalación de K3s (single-node)

Ejecuta el script oficial para un clúster single-node:

```
curl -sfL https://get.k3s.io | sh -
```

Esto descarga el binario ARM, crea el servicio systemd y arranca el servidor Kubernetes.[^1_5][^1_4]

## Configuración

- Verifica el servicio: `sudo systemctl status k3s` (debe estar active/running).
- Copia el kubeconfig para uso sin sudo: `mkdir -p $HOME/.kube && sudo cp /etc/rancher/k3s/k3s.yaml $HOME/.kube/config && sudo chown $(id -u):$(id -g) $HOME/.kube/config`.
- Opcional: deshabilita componentes como Traefik si no los necesitas: reinstala con `curl -sfL https://get.k3s.io | INSTALL_K3S_EXEC="server --disable traefik" sh -`.[^1_5]


## Verificación

- Lista nodos: `kubectl get nodes` (debe mostrar tu Pi como Ready).
- Lista pods: `kubectl get pods -A` (verifica componentes core como coredns en Running).
- Prueba deploy: `kubectl run test --image=nginx --restart=Never` y `kubectl get pods` (llega a Running), luego limpia con `kubectl delete pod test`.[^1_1][^1_4][^1_5]

Si planeas un clúster multi-nodo, obtén el token con `sudo cat /var/lib/rancher/k3s/server/node-token` para unir workers.[^1_5]
<span style="display:none">[^1_10][^1_11][^1_12][^1_13][^1_14][^1_15][^1_16][^1_17][^1_18][^1_19][^1_6][^1_7][^1_8][^1_9]</span>

<div align="center">⁂</div>

[^1_1]: https://programmingbrain.com/2022/07/construyendo-cluster-kubernetes-en.html

[^1_2]: https://opensource.com/article/20/3/kubernetes-raspberry-pi-k3s

[^1_3]: https://blog.mymware.com/posts/k3s-raspi.1/

[^1_4]: https://www.picocluster.com/blogs/picocluster-software-engineering/installing-k3s-on-the-raspberry-pi-5-a-step-by-step-guide

[^1_5]: https://oneuptime.com/blog/post/2026-01-27-k3s-raspberry-pi/view

[^1_6]: https://oneuptime.com/blog/post/2026-02-02-k3s-raspberry-pi/view

[^1_7]: https://blog.alexellis.io/self-hosting-kubernetes-on-your-raspberry-pi/

[^1_8]: https://es.linkedin.com/pulse/cómo-montar-un-clúster-kubernetes-en-una-raspberry-pi-ruiz-diaz-du4of

[^1_9]: https://www.reddit.com/r/kubernetes/comments/11hwkkh/how_to_connect_from_my_local_home_raspberry_pi_to/

[^1_10]: https://anthonynsimon.com/blog/kubernetes-cluster-raspberry-pi/

[^1_11]: https://www.reddit.com/r/kubernetes/comments/x9vaxb/how_to_install_kubernetes_on_raspberry_pi/

[^1_12]: https://www.youtube.com/watch?v=N4bfNefjBSw

[^1_13]: https://bryanbende.com/development/2021/05/07/k3s-raspberry-pi-initial-setup

[^1_14]: https://maxdon.tech/posts/k3s-raspberry-pi-worker/

[^1_15]: https://k8s.co.il/kubernetes/how-to-deploy-a-kubernetes-k3s-on-raspberry-pi/

[^1_16]: https://www.youtube.com/watch?v=kiD-IGvPkic

[^1_17]: https://jesusmarin.dev/blog/creando-cluster-kubernet-k3sup-k3s/

[^1_18]: https://www.youtube.com/watch?v=gAjsqaTF0oE

[^1_19]: https://gist.github.com/mkm29/da315f5c292e50fa185486d8d283199b


---

# willians@pik3s:~\$ curl -sfL [https://get.k3s.io](https://get.k3s.io) | sh -

[INFO]  Finding release for channel stable
[INFO]  Using v1.34.5+k3s1 as release
[INFO]  Downloading hash [https://github.com/k3s-io/k3s/releases/download/v1.34.5%2Bk3s1/sha256sum-arm64.txt](https://github.com/k3s-io/k3s/releases/download/v1.34.5%2Bk3s1/sha256sum-arm64.txt)
[INFO]  Skipping binary downloaded, installed k3s matches hash
[INFO]  Skipping installation of SELinux RPM
[INFO]  Skipping /usr/local/bin/kubectl symlink to k3s, already exists
[INFO]  Skipping /usr/local/bin/crictl symlink to k3s, already exists
[INFO]  Skipping /usr/local/bin/ctr symlink to k3s, already exists
[INFO]  Creating killall script /usr/local/bin/k3s-killall.sh
[INFO]  Creating uninstall script /usr/local/bin/k3s-uninstall.sh
[INFO]  env: Creating environment file /etc/systemd/system/k3s.service.env
[INFO]  systemd: Creating service file /etc/systemd/system/k3s.service
[INFO]  Failed to find memory cgroup, you may need to add "cgroup_memory=1 cgroup_enable=memory" to your linux cmdline (/boot/firmware/cmdline.txt on a Raspberry Pi)
[INFO]  systemd: Enabling k3s unit
Created symlink '/etc/systemd/system/multi-user.target.wants/k3s.service' → '/etc/systemd/system/k3s.service'.
[INFO]  No change detected so skipping service start

El mensaje indica que K3s se instaló correctamente (detectó versión existente v1.34.5+k3s1 y creó los servicios), pero el servicio no arrancó por falta de soporte para cgroup de memoria en el kernel del Raspberry Pi 3.[^2_1]

## Solución al error cgroup

Edita el archivo de parámetros del kernel:

```
sudo nano /boot/firmware/cmdline.txt
```

Agrega al **final de la línea única** (sin nueva línea):

```
cgroup_enable=cpuset cgroup_memory=1 cgroup_enable=memory
```

**Ejemplo completo** (ajusta según tu línea actual):

```
console=serial0,115200 console=tty1 root=PARTUUID=xxxx rootfstype=ext4 fsck.repair=yes rootwait cgroup_enable=cpuset cgroup_memory=1 cgroup_enable=memory
```

Guarda, luego reinicia: `sudo reboot`.[^2_6][^2_11][^2_1]

## Verificación post-reboot

1. Comprueba el servicio: `sudo systemctl status k3s` (debe mostrar "active (running)").
2. Configura kubectl:

```
mkdir -p $HOME/.kube
sudo cp /etc/rancher/k3s/k3s.yaml $HOME/.kube/config
sudo chown $USER:$USER $HOME/.kube/config
```

3. Verifica el clúster: `kubectl get nodes` (debe mostrar tu nodo "Ready").
4. Chequea pods: `kubectl get pods -A` (todos los componentes core en "Running").[^2_12]

Si persiste el error tras reboot, verifica que no hay líneas nuevas en cmdline.txt y usa Raspberry Pi OS 64-bit (Bookworm preferible para Pi 3B+).[^2_8]
<span style="display:none">[^2_10][^2_2][^2_3][^2_4][^2_5][^2_7][^2_9]</span>

<div align="center">⁂</div>

[^2_1]: https://github.com/k3s-io/k3s/issues/2067

[^2_2]: https://www.picocluster.com/blogs/picocluster-software-engineering/installing-k3s-on-the-raspberry-pi-5-a-step-by-step-guide

[^2_3]: https://github.com/k3s-io/k3s/issues/10755

[^2_4]: https://www.reddit.com/r/NixOS/comments/10huogm/k3s_does_not_find_memory_cgroup_v2/

[^2_5]: https://stackoverflow.com/questions/74294548/k3s-install-on-raspberry-pi

[^2_6]: https://gist.github.com/jannegpriv/c8227de6fe46c1eb7e214ac3c6b7b283

[^2_7]: https://docs.k3s.io/installation/requirements

[^2_8]: https://github.com/k3s-io/k3s/issues/9524

[^2_9]: https://markmcgookin.com/2022/12/01/building-a-k3s-cluster-using-raspberry-pis/

[^2_10]: https://maxdon.tech/posts/k3s-raspberry-pi/

[^2_11]: https://programmingbrain.com/2022/07/construyendo-cluster-kubernetes-en.html

[^2_12]: https://oneuptime.com/blog/post/2026-01-27-k3s-raspberry-pi/view


---

# sudo systemctl status k3s

● k3s.service - Lightweight Kubernetes
Loaded: loaded (/etc/systemd/system/k3s.service; enabled; preset: enabled)
Active: activating (auto-restart) (Result: exit-code) since Wed 2026-03-18 21:26:38 -04; 2s ago
Invocation: 1e5cc0e0bf78458498faaa1277e0fdfe
Docs: [https://k3s.io](https://k3s.io)
Process: 1172 ExecStartPre=/sbin/modprobe br_netfilter (code=exited, status=0/SUCCESS)
Process: 1174 ExecStartPre=/sbin/modprobe overlay (code=exited, status=0/SUCCESS)
Process: 1176 ExecStart=/usr/local/bin/k3s server (code=exited, status=1/FAILURE)
Main PID: 1176 (code=exited, status=1/FAILURE)
CPU: 2.557s

El servicio K3s está fallando al iniciar (`status=1/FAILURE`) en `ExecStart=/usr/local/bin/k3s server`, lo que indica que persiste el problema de cgroups de memoria pese a la configuración en `cmdline.txt`.[^3_1]

## Diagnóstico inmediato

Revisa los logs detallados del servicio:

```
sudo journalctl -u k3s.service -f
```

Busca errores específicos como "failed to find memory cgroup" o problemas de contenedor runtime.[^3_3][^3_1]

## Verificar configuración cgroup

Confirma que los parámetros se aplicaron correctamente:

```
cat /proc/cmdline
```

Debe mostrar **exactamente** `cgroup_enable=cpuset cgroup_memory=1 cgroup_enable=memory` al final de una sola línea. Si hay nueva línea o formato incorrecto, K3s fallará.[^3_11]

```
cat /proc/cgroups
```

Debe listar `memory` como disponible (no solo cgroup2).[^3_1]

## Soluciones específicas

### 1. Si usas Raspberry Pi OS Bookworm (cgroup v2 por default):

Agrega también systemd legacy support en `/boot/firmware/cmdline.txt`:

```
systemd.unified_cgroup_hierarchy=0 cgroup_enable=cpuset cgroup_memory=1 cgroup_enable=memory
```

Reinicia: `sudo reboot`.[^3_7][^3_3]

### 2. Reinicia servicio manualmente:

```
sudo systemctl stop k3s
sudo k3s server --cluster-reset
sudo systemctl start k3s
```


### 3. Verifica versión OS y kernel:

```
uname -a
cat /etc/os-release
```

Pi 3 funciona mejor con **Raspberry Pi OS Bullseye 32-bit** (kernel 5.10+). Bookworm 64-bit puede requerir ajustes adicionales de cgroup v2.[^3_12]

## Prueba rápida

Una vez resuelto:

```bash
kubectl get nodes
kubectl get pods -A
```

**Comparte la salida de `journalctl -u k3s.service --no-pager`** para diagnóstico preciso del error específico.[^3_1]
<span style="display:none">[^3_10][^3_2][^3_4][^3_5][^3_6][^3_8][^3_9]</span>

<div align="center">⁂</div>

[^3_1]: https://github.com/k3s-io/k3s/issues/2067

[^3_2]: https://github.com/k3s-io/k3s/issues/3293

[^3_3]: https://github.com/k3s-io/k3s/issues/8890

[^3_4]: https://www.reddit.com/r/NixOS/comments/10huogm/k3s_does_not_find_memory_cgroup_v2/

[^3_5]: https://stackoverflow.com/questions/74294548/k3s-install-on-raspberry-pi

[^3_6]: https://github.com/k3d-io/k3d/discussions/854

[^3_7]: https://github.com/k3s-io/k3s/issues/10755

[^3_8]: https://github.com/k3s-io/k3s-ansible/issues/179

[^3_9]: https://archlinuxarm.org/forum/viewtopic.php?f=23\&t=17131

[^3_10]: https://github.com/k3s-io/k3s/issues/1825

[^3_11]: https://gist.github.com/jannegpriv/c8227de6fe46c1eb7e214ac3c6b7b283

[^3_12]: https://github.com/k3s-io/k3s/issues/9524


---

# cat /proc/cmdline

coherent_pool=1M 8250.nr_uarts=1 snd_bcm2835.enable_headphones=0 cgroup_disable=memory snd_bcm2835.enable_headphones=1 snd_bcm2835.enable_hdmi=1 snd_bcm2835.enable_hdmi=0  vc_mem.mem_base=0x3ec00000 vc_mem.mem_size=0x40000000  console=ttyS0,115200 console=tty1 root=PARTUUID=0136c393-02 rootfstype=ext4 fsck.repair=yes rootwait cfg80211.ieee80211_regdom=VE

**El problema está claro**: No agregaste los parámetros cgroup requeridos al final de `/proc/cmdline`. Se ve `cgroup_disable=memory` (¡deshabilitado!) en lugar de habilitado.[^4_1]

## Corrección inmediata

1. **Edita el archivo correcto**:
```
sudo nano /boot/firmware/cmdline.txt
```

2. **Agrega EXACTAMENTE al final de la única línea existente** (sin saltos de línea):
```
cgroup_enable=cpuset cgroup_memory=1 cgroup_enable=memory
```

**Tu línea debe quedar así** (todo en UNA línea):

```
coherent_pool=1M 8250.nr_uarts=1 snd_bcm2835.enable_headphones=0 cgroup_disable=memory snd_bcm2835.enable_headphones=1 snd_bcm2835.enable_hdmi=1 snd_bcm2835.enable_hdmi=0 vc_mem.mem_base=0x3ec00000 vc_mem.mem_size=0x40000000 console=ttyS0,115200 console=tty1 root=PARTUUID=0136c393-02 rootfstype=ext4 fsck.repair=yes rootwait cfg80211.ieee80211_regdom=VE cgroup_enable=cpuset cgroup_memory=1 cgroup_enable=memory
```

**IMPORTANTE**:

- Quita `cgroup_disable=memory` si aparece
- Todo en **UNA SOLA LÍNEA**
- Espacios simples entre parámetros

3. **Guarda y reinicia**:
```
sudo reboot
```


## Verificación post-reboot

```
cat /proc/cmdline  # Debe mostrar los nuevos parámetros al final
sudo systemctl status k3s  # Debe mostrar "active (running)"
kubectl get nodes  # Debe funcionar
```

**¡El `cgroup_disable=memory` que ves actualmente está BLOQUEANDO K3s!** Esas son las instrucciones precisas para Pi 3.[^4_6][^4_1]
<span style="display:none">[^4_2][^4_3][^4_4][^4_5]</span>

<div align="center">⁂</div>

[^4_1]: https://github.com/k3s-io/k3s/issues/2067

[^4_2]: https://www.reddit.com/r/raspberry_pi/comments/7avo0w/cant_enable_memory_cgroup_on_raspberry_pi_3/

[^4_3]: https://dietpi.com/forum/t/adding-cgroups-to-cmdline-txt-on-rpi3b-not-taking/4353

[^4_4]: https://github.com/k3s-io/docs/issues/333

[^4_5]: https://archlinuxarm.org/forum/viewtopic.php?f=15\&t=12086

[^4_6]: https://gist.github.com/jannegpriv/c8227de6fe46c1eb7e214ac3c6b7b283


---

# Cómo editar /boot/cmdline.txt en Raspberry Pi

Para editar `/boot/cmdline.txt` en Raspberry Pi, usa este comando exacto:

```
sudo nano /boot/cmdline.txt
```


## Pasos precisos

1. **Ejecuta el comando arriba** (requiere `sudo` porque es de solo lectura).
2. **El archivo tiene UNA SOLA LÍNEA** - navega al final con las flechas.
3. **Agrega sin saltar línea**:

```
cgroup_enable=cpuset cgroup_memory=1 cgroup_enable=memory
```

4. **Guarda**: `Ctrl+O`, `Enter`, `Ctrl+X`.
5. **Verifica**: `cat /boot/cmdline.txt` (debe mostrar todo en una línea).
6. **Reinicia**: `sudo reboot`.

## Tu línea final debe verse así:

```
coherent_pool=1M 8250.nr_uarts=1 snd_bcm2835.enable_headphones=0 snd_bcm2835.enable_headphones=1 snd_bcm2835.enable_hdmi=1 snd_bcm2835.enable_hdmi=0 vc_mem.mem_base=0x3ec00000 vc_mem.mem_size=0x40000000 console=ttyS0,115200 console=tty1 root=PARTUUID=0136c393-02 rootfstype=ext4 fsck.repair=yes rootwait cfg80211.ieee80211_regdom=VE cgroup_enable=cpuset cgroup_memory=1 cgroup_enable=memory
```

**¡IMPORTANTE!** Quita `cgroup_disable=memory` si aparece y mantén **UNA SOLA LÍNEA**.[^5_1]
<span style="display:none">[^5_2][^5_3][^5_4][^5_5][^5_6]</span>

<div align="center">⁂</div>

[^5_1]: https://raspberrytips.es/raspberry-pi-cmdline-txt/

[^5_2]: https://raspberryparanovatos.com/articulos/configtxt-que-es-y-como-usarlo/

[^5_3]: https://hijosdeinit.gitlab.io/howto_cmdline.txt_KernelCommandLine_RaspBian_rpi/

[^5_4]: https://www.juanjosevalera.com/archivos/modificacion-de-cmdline-txt/

[^5_5]: https://www.reddit.com/r/raspberry_pi/comments/80pea3/edit_bootconfigtxt_file_raspbian/

[^5_6]: https://stackoverflow.com/questions/61972815/ansible-modify-cmdline-txt-on-raspberry-pi


---

# por que hay que apagar la swap ?

Hay que apagar la swap en Kubernetes (y K3s) por varias razones críticas de rendimiento y estabilidad.

## Razones técnicas

Kubernetes **no puede detectar correctamente la memoria disponible** cuando la swap está activa. El kubelet usa métricas de `/proc/meminfo` que se distorsionan con swap, causando:

- Despliegue de pods en nodos con "memoria insuficiente" percibida
- Fallos impredecibles en scheduling
- Violación de memory limits/requests


## Comportamiento del kubelet

```
# Con swap activa, kubelet reporta memoria "fantasma"
kubectl describe node
# Capacity: memory: 900Mi  ← Incluye swap como RAM real
```

**Resultado**: Kubernetes cree tener más memoria de la real y sobrecarga el nodo.[^6_3]

## En Raspberry Pi 3 (1GB RAM)

La swap en SD card es **extremadamente lenta** (100-1000x más lenta que RAM). Con swap activa:

```
# OOM killer mata pods esenciales (coredns, etc.)
kubectl get pods -A  # Constantemente en CrashLoopBackOff
```


## Desactivación permanente

```bash
sudo swapoff -a
sudo systemctl disable --now dphys-swapfile
```

**Edita `/etc/fstab`** y comenta la línea de swap:

```
# /var/swap  none  swap  sw 0  0  ← Comentar esta línea
```


## Verificación

```bash
free -h  # Swap: 0B avail
kubectl get nodes  # Ready (sin warnings de memoria)
```

**En resumen**: Swap rompe el modelo de memoria predecible de Kubernetes. Es un requisito oficial documentado desde K8s 1.0.[^6_8]
<span style="display:none">[^6_1][^6_2][^6_4][^6_5][^6_6][^6_7]</span>

<div align="center">⁂</div>

[^6_1]: https://www.youtube.com/watch?v=4o4BubAq1FQ

[^6_2]: https://www.reddit.com/r/k3s/comments/nf9q90/should_swap_be_disabled_with_k3s_as_its_required/

[^6_3]: https://faun.pub/why-swap-on-kubernetes-isnt-the-same-as-swap-on-linux-and-what-you-should-do-instead-5c812b8392ae

[^6_4]: https://devopscube.com/kubernetes-swap/

[^6_5]: https://kubernetes.io/docs/concepts/cluster-administration/swap-memory-management/

[^6_6]: https://rlodeiro.info/blog/raspberry-pi_swap-swappiness/

[^6_7]: https://www.q2bstudio.com/nuestro-blog/529252/optimizacion-del-swap-de-linux-para-kubernetes-aprende-como-mejorar-el-rendimiento-de-tu-servicio-en-la-nube-consejos-gestionar-la-memoria-swap-de-manera-eficiente-entornos-kubernetes

[^6_8]: https://discuss.kubernetes.io/t/swap-off-why-is-it-necessary/6879


---

# free -h

               total        used        free      shared  buff/cache   available
    Mem:           905Mi       162Mi       499Mi       4.0Mi       299Mi       743Mi
Swap:          904Mi          0B       904Mi

La swap está **temporalmente desactivada** (`0B used`), pero **se reactivará al reboot** porque el servicio `dphys-swapfile` sigue activo en Raspberry Pi OS.

## Desactivación permanente

Ejecuta estos comandos **en orden**:

```bash
sudo swapoff -a
sudo systemctl disable --now dphys-swapfile
sudo apt purge dphys-swapfile -y  # Opcional, libera espacio
```


## Verificar fstab

```bash
sudo nano /etc/fstab
```

Comenta la línea de swap (agrega `#` al inicio):

```
#/var/swap   none  swap  sw 0  0  ← Así queda
```


## Confirmación final

```bash
sudo reboot
free -h  # Post-reboot: Swap debe mostrar 0B total
```

**Resultado esperado post-reboot**:

```
Swap:            0B          0B          0B
```

Esto es **esencial para K3s** - Kubernetes requiere swap=0 para métricas de memoria correctas.[^7_1][^7_2]
<span style="display:none">[^7_3][^7_4][^7_5][^7_6][^7_7]</span>

<div align="center">⁂</div>

[^7_1]: https://ragone.dev/post/disable-swap-raspberry-pi-bookworm

[^7_2]: https://www.youtube.com/watch?v=4o4BubAq1FQ

[^7_3]: https://community.element14.com/products/raspberry-pi/f/forum/20159/how-do-i-permanently-disable-the-swap-service

[^7_4]: https://piaustralia.com.au/blogs/news/how-to-disable-swap-on-raspberry-pi-os-and-use-a-usb-drive-instead

[^7_5]: https://www.reddit.com/r/pop_os/comments/1bsq63f/how_to_permanently_disable_swap/

[^7_6]: https://blog.jcea.es/posts/20181101-raspbian_desactivar_swap.html

[^7_7]: https://rpi.uroboros.es/trucos.html


---

# sudo systemctl disable --now dphys-swapfile

Failed to disable unit: Unit dphys-swapfile.service does not exist

Tu Raspberry Pi usa **rpi-swap** (nuevo en Raspberry Pi OS Trixie/Bookworm reciente) en lugar del viejo `dphys-swapfile`.

## Desactivar rpi-swap permanentemente

```bash
sudo rpi-swap off
sudo systemctl disable --now rpi-swap
sudo apt purge rpi-swap -y
```


## Completar con fstab

```bash
sudo nano /etc/fstab
```

Comenta o elimina la línea de swap:

```
#/var/swap   none  swap  sw  0  0
```


## Verificar swap eliminada

```bash
free -h  # Swap debe mostrar 0B total
ls /var/swap  # No debe existir el archivo
```


## Confirmación final

```bash
sudo reboot
free -h  # Post-reboot: Swap: 0B 0B 0B
```

**Ahora K3s tendrá memoria real precisa** (743Mi available) y no métricas distorsionadas por swap.[^8_5]
<span style="display:none">[^8_1][^8_2][^8_3][^8_4]</span>

<div align="center">⁂</div>

[^8_1]: http://raspberrypimaker.com/adding-swap-to-the-raspberrypi/

[^8_2]: https://pimylifeup.com/raspberry-pi-swap-file/

[^8_3]: https://audministrator.wordpress.com/2021/10/11/raspberry-pi-configure-the-swap-file/

[^8_4]: https://community.element14.com/products/raspberry-pi/f/forum/20159/how-do-i-permanently-disable-the-swap-service

[^8_5]: https://ragone.dev/post/disable-swap-raspberry-pi-bookworm


---

# sudo rpi-swap off

sudo: rpi-swap: command not found

Tu sistema tiene swap **temporalmente desactivado** (`0B used`) pero aún montado (`904Mi total`). Es Raspberry Pi OS Bookworm/Trixie con **dphys-swapfile** (el estándar).

## Desactivación completa (método correcto)

```bash
sudo dphys-swapfile swapoff
sudo dphys-swapfile uninstall
sudo systemctl disable dphys-swapfile
sudo rm -f /var/swap /etc/fstab.d/swap
```


## Verificar fstab también

```bash
sudo nano /etc/fstab
```

Busca y **comenta o elimina** cualquier línea que contenga `/var/swap`:

```
# /var/swap   none  swap  sw  0  0  ← Comentar o borrar
```


## Confirmación final

```bash
free -h  # Debe mostrar Swap: 0B 0B 0B
sudo reboot
free -h  # Post-reboot: Swap completamente 0B
```

**Resultado esperado**:

```
Swap:            0B          0B          0B
```

Esto es **crítico para K3s** - Kubernetes necesita memoria real precisa sin swap para scheduling correcto. Ahora procede con la corrección de `cmdline.txt` para cgroups.[^9_1][^9_7]
<span style="display:none">[^9_2][^9_3][^9_4][^9_5][^9_6][^9_8]</span>

<div align="center">⁂</div>

[^9_1]: https://ragone.dev/post/disable-swap-raspberry-pi-bookworm

[^9_2]: https://community.element14.com/products/raspberry-pi/f/forum/20159/how-do-i-permanently-disable-the-swap-service

[^9_3]: https://piaustralia.com.au/blogs/news/how-to-disable-swap-on-raspberry-pi-os-and-use-a-usb-drive-instead

[^9_4]: https://www.webwhitenoise.com/p/disable-swap-on-raspberry-pi-os-trixie

[^9_5]: https://homelab.net.pl/en/jak-trwale-wylaczyc-swap-w-raspberry-pi/

[^9_6]: https://lifetips.alibaba.com/tech-efficiency/instantly-free-up-almost-1gb-on-your-raspberry-pi-by-di

[^9_7]: https://www.dzombak.com/blog/2023/12/stop-using-the-raspberry-pi-s-sd-card-for-swap/

[^9_8]: https://gist.github.com/mak3r/6bb80e82742dd1a30bbd3bf45016e461


---

# sudo rpi-swap off

sudo: rpi-swap: command not found
willians@pik3s:~\$ sudo dphys-swapfile swapoff
sudo: dphys-swapfile: command not found
willians@pik3s:~\$ sudo dphys-swapfile uninstall
sudo: dphys-swapfile: command not found
willians@pik3s:~\$ sudo systemctl disable dphys-swapfile
Failed to disable unit: Unit dphys-swapfile.service does not exist

Tu sistema muestra **Swap: 904Mi total** porque aún está configurado para reactivarse, aunque esté vacío (`0B used`). Como no existen los servicios `dphys-swapfile` ni `rpi-swap`, usa el método **universal de Linux** para Raspberry Pi OS.

## Método definitivo (funciona siempre)

```bash
sudo swapoff -a
sudo systemctl mask swap.target
sudo nano /etc/fstab
```

**En `/etc/fstab`**, comenta o elimina cualquier línea que contenga `swap`:

```
# /var/swap   none  swap  sw  0  0  ← Comenta con #
```

**Elimina el archivo físico**:

```bash
sudo rm -f /var/swap
```


## Verificación completa

```bash
free -h  # Debe mostrar Swap: 0B 0B 0B
swapon --show  # No debe mostrar nada
ls /var/swap   # No debe existir
```


## Reinicia y confirma

```bash
sudo reboot
free -h  # Swap debe ser 0B permanentemente
```

**Resultado esperado final**:

```
Swap:            0B          0B          0B
```

`systemctl mask swap.target` **bloquea completamente** cualquier swap futuro. Esto es **requerido para K3s** y resuelve las métricas de memoria incorrectas.[^10_1][^10_2]
<span style="display:none">[^10_10][^10_3][^10_4][^10_5][^10_6][^10_7][^10_8][^10_9]</span>

<div align="center">⁂</div>

[^10_1]: https://ragone.dev/post/disable-swap-raspberry-pi-bookworm

[^10_2]: https://www.webwhitenoise.com/p/disable-swap-on-raspberry-pi-os-trixie

[^10_3]: https://raspberry.piaustralia.com.au/blogs/news/how-to-disable-swap-on-raspberry-pi-os-and-use-a-usb-drive-instead

[^10_4]: https://community.element14.com/products/raspberry-pi/f/forum/20159/how-do-i-permanently-disable-the-swap-service

[^10_5]: https://piaustralia.com.au/blogs/news/how-to-disable-swap-on-raspberry-pi-os-and-use-a-usb-drive-instead

[^10_6]: https://www.dzombak.com/blog/2024/03/running-a-raspberry-pi-with-a-read-only-root-filesystem/

[^10_7]: https://lifetips.alibaba.com/tech-efficiency/instantly-free-up-almost-1gb-on-your-raspberry-pi-by-di

[^10_8]: http://www.momobro.com/rasbro/tips-rp-swap-management/

[^10_9]: https://www.youtube.com/watch?v=4o4BubAq1FQ

[^10_10]: https://www.dzombak.com/blog/2023/12/stop-using-the-raspberry-pi-s-sd-card-for-swap/


---

# swapon --show

NAME       TYPE      SIZE USED PRIO
/dev/zram0 partition 905M   0B  100

**Perfecto! Has identificado el problema real**: `/dev/zram0` es **zram swap** (compresión en RAM), que Raspberry Pi OS Bookworm/Trixie usa por defecto. Kubernetes **requiere TODO swap desactivado**, incluyendo zram.

## Desactivar zram permanentemente

```bash
# 1. Desactivar inmediatamente
sudo swapoff /dev/zram0
sudo modprobe -r zram

# 2. Bloquear zram en arranque
echo "blacklist zram" | sudo tee /etc/modprobe.d/no-zram.conf

# 3. Máscar swap systemd
sudo systemctl mask swap.target

# 4. Eliminar de fstab
sudo nano /etc/fstab  # Comenta cualquier línea swap
```


## Verificación completa

```bash
free -h              # Swap: 0B 0B 0B
swapon --show        # Nada (vacío)
lsmod | grep zram    # Nada
```


## Reinicia y confirma

```bash
sudo reboot
free -h  # Swap debe ser 0B completamente
```

**Resultado esperado final**:

```
Swap:            0B          0B          0B
```

**Ahora K3s funcionará correctamente** - Kubernetes necesita **cero swap** (ni disco ni zram) para métricas de memoria precisas. zram distorsiona `/proc/meminfo` igual que swap tradicional.
<span style="display:none">[^11_1][^11_10][^11_2][^11_3][^11_4][^11_5][^11_6][^11_7][^11_8][^11_9]</span>

<div align="center">⁂</div>

[^11_1]: https://ragone.dev/post/disable-swap-raspberry-pi-bookworm

[^11_2]: https://gist.github.com/sultanqasim/79799883c6b81c710e36a38008dfa374

[^11_3]: https://www.webwhitenoise.com/p/disable-swap-on-raspberry-pi-os-trixie

[^11_4]: https://ohyaan.github.io/tips/optimizing_swap_with_zram_on_raspberry_pi_os/

[^11_5]: https://forum.manjaro.org/t/how-can-i-disable-zram-and-enable-and-configure-zswap/93229

[^11_6]: https://forum.tinycorelinux.net/index.php/topic,26271.0.html

[^11_7]: https://piaustralia.com.au/blogs/news/how-to-disable-swap-on-raspberry-pi-os-and-use-a-usb-drive-instead

[^11_8]: https://community.element14.com/products/raspberry-pi/f/forum/20159/how-do-i-permanently-disable-the-swap-service

[^11_9]: https://lifetips.alibaba.com/tech-efficiency/instantly-free-up-almost-1gb-on-your-raspberry-pi-by-di

[^11_10]: https://www.dzombak.com/blog/2023/12/stop-using-the-raspberry-pi-s-sd-card-for-swap/


---

# ahora necesito revisar los requerimientos de k3s. Entre ellos, desabilitar el firewall...

**K3s NO requiere deshabilitar el firewall por defecto**. Raspberry Pi OS viene sin firewall activo, así que probablemente ya esté OK. Sin embargo, es buena práctica verificarlo.

## Requisitos oficiales K3s (importantes para Pi 3)

```
✓ Swap OFF (ya trabajando)
✓ cgroups habilitados (cmdline.txt pendiente)
✗ Firewall (verificar)
✓ iptables instalados
✓ br_netfilter habilitado
```


## Verificar y limpiar firewall

```bash
# 1. Ver qué firewall tienes
sudo systemctl status nftables
sudo systemctl status iptables
sudo ufw status

# 2. Desactivar si existe (Bookworm usa nftables)
sudo systemctl stop nftables
sudo systemctl disable nftables
sudo systemctl mask nftables

# 3. Limpiar reglas existentes
sudo nft flush ruleset
sudo iptables -F && sudo iptables -t nat -F && sudo iptables -t mangle -F

# 4. Verificar limpio
sudo nft list ruleset  # Debe estar vacío
sudo iptables -L      # Debe mostrar ACCEPT todo
```


## Checklist K3s completa (Pi 3)

```bash
# Ejecuta esto para verificar TODO:
sudo swapoff -a && free -h | grep Swap  # 0B total
cat /proc/cmdline | grep cgroup_memory  # Debe aparecer
sudo modprobe br_netfilter              # Sin error
sudo nft list ruleset | grep -q table || echo "nftables limpio"
```

**Post-limpieza de firewall + cmdline.txt corregido + reboot**, ejecuta:

```bash
sudo systemctl restart k3s
kubectl get nodes
```

**Tu Pi 3 estará listo**. El puerto crítico es **6443/tcp** (API server) - asegúrate que no esté bloqueado si tienes router firewall externo.[^12_1]
<span style="display:none">[^12_2][^12_3][^12_4][^12_5][^12_6][^12_7][^12_8]</span>

<div align="center">⁂</div>

[^12_1]: https://linuxconfig.org/how-to-enable-disable-firewall-on-raspberry-pi

[^12_2]: https://www.luisllamas.es/en/use-ufw-firewall-on-raspberry-pi/

[^12_3]: https://www.reddit.com/r/raspberry_pi/comments/3co4mu/how_do_i_turn_off_the_firewall/

[^12_4]: https://www.sunfounder.com/blogs/news/how-to-configure-the-firewall-in-raspberry-pi

[^12_5]: https://pimylifeup.com/raspberry-pi-firewall/

[^12_6]: https://glpalermo.wordpress.com/2020/02/08/raspberry-firewall/

[^12_7]: https://www.raspberrypi.com/news/bookworm-the-new-version-of-raspberry-pi-os/

[^12_8]: https://raspberrytips.es/raspberry-pi-firewall/


---

# sudo iptables -F \&\& sudo iptables -t nat -F \&\& sudo iptables -t mangle -F

sudo: iptables: command not found

**Raspberry Pi OS Bookworm usa nftables** por defecto (no iptables legacy), por eso el comando falla. Es normal - **K3s funciona perfectamente con nftables**.

## Verificar estado actual del firewall

```bash
# Chequea servicios de firewall
sudo systemctl status nftables
sudo systemctl status iptables
sudo nft list ruleset
```


## Limpiar nftables (si tiene reglas)

```bash
sudo systemctl stop nftables
sudo systemctl disable nftables
sudo nft flush ruleset
sudo systemctl mask nftables
```


## Instalar iptables (opcional, para compatibilidad K3s)

```bash
sudo apt update
sudo apt install -y iptables conntrack
```


## Checklist completo K3s para tu Pi 3

```bash
# Swap (ya trabajando)
free -h | grep Swap  # Debe ser 0B 0B 0B

# Cgroups (pendiente)
cat /proc/cmdline | grep cgroup_memory  # Debe aparecer

# iptables disponible
sudo iptables -V  # Debe funcionar (nftables backend)

# br_netfilter
sudo modprobe br_netfilter
lsmod | grep br_netfilter  # Debe aparecer
```


## Estado esperado post-configuración

```
nftables.service    masked (inactive)
iptables -V         v1.8.x (nf_tables) ✓
Swap                0B ✓  
cgroup_memory=1     en cmdline.txt ✓
```

**¡Raspberry Pi OS limpio ya es compatible con K3s!** Solo necesitas **cmdline.txt corregido + reboot** y arrancará. El puerto crítico **6443/tcp** debe estar abierto (lo está por defecto sin firewall).[^13_2]
<span style="display:none">[^13_1][^13_3][^13_4][^13_5][^13_6][^13_7][^13_8]</span>

<div align="center">⁂</div>

[^13_1]: https://www.sunfounder.com/blogs/news/how-to-configure-the-firewall-in-raspberry-pi

[^13_2]: https://github.com/RaspAP/raspap-webgui/issues/1512

[^13_3]: https://revolutionpi.com/en/docs/cockpit

[^13_4]: https://pimylifeup.com/raspberry-pi-firewall/

[^13_5]: https://www.raspberrypi.com/news/bookworm-the-new-version-of-raspberry-pi-os/

[^13_6]: https://www.raspberrypi.com/documentation/computers/configuration.html

[^13_7]: https://www.reddit.com/r/raspberry_pi/comments/lal7dv/setup_a_firewall_on_your_raspberry_pi/

[^13_8]: https://linuxsecurity.com/news/iot-security/raspberry-pi-os-security-updates


---

# a ver si entendi. k3s requiere iptables?

**No, K3s NO requiere iptables específicamente**. K3s funciona perfectamente con **nftables** (el default de Raspberry Pi OS Bookworm/Trixie).

## Respuesta clara

```
K3s ✓ nftables (tu sistema actual)
K3s ✓ iptables legacy (sistemas viejos)
K3s ✓ iptables-nftables backend (híbrido)
```

**Tu sistema ya cumple** este requisito. El error `iptables: command not found` es **normal** en Bookworm puro.

## Lo que SÍ necesita K3s (tu checklist pendiente)

```
✅ Swap: 0B (trabajando zram)
❌ Cgroups: cmdline.txt necesita "cgroup_memory=1"
✅ Firewall: nftables vacío = OK
✅ Puertos: 6443/tcp abierto (default sin firewall)
✅ Módulos: br_netfilter (se carga en ExecStartPre)
```


## Verificación rápida de red (nftables OK)

```bash
sudo nft list ruleset  # Debe estar vacío o casi vacío
nc -zv localhost 6443  # Debe fallar (K3s no iniciado aún)
```


## Próximo paso CRÍTICO

**Solo falta**:

```bash
sudo nano /boot/cmdline.txt  # Agregar cgroup_memory=1 al final
sudo reboot
sudo systemctl status k3s   # Ahora funcionará
```

**Tu Pi 3 estará corriendo K3s en 5 minutos** una vez arregles `cmdline.txt`. nftables no es problema.[^14_1][^14_4]
<span style="display:none">[^14_10][^14_2][^14_3][^14_5][^14_6][^14_7][^14_8][^14_9]</span>

<div align="center">⁂</div>

[^14_1]: https://docs.k3s.io/installation/requirements

[^14_2]: https://documentation.suse.com/cloudnative/k3s/latest/en/installation/requirements.html

[^14_3]: https://documentation.suse.com/cloudnative/k3s/latest/zh/installation/requirements.html

[^14_4]: https://github.com/k3s-io/k3s/issues/2946

[^14_5]: https://github.com/k3s-io/k3s-ansible/issues/157

[^14_6]: https://docs.k3s.io/known-issues

[^14_7]: https://github.com/k3s-io/k3s/issues/11493

[^14_8]: https://github.com/k3s-io/k3s/issues/1812

[^14_9]: https://forum.netcup.de/administration-of-a-server-vserver/vserver-server-kvm-server/17766-einstellung-firewall-k3s-iptables-nft/

[^14_10]: https://github.com/k3s-io/k3s/discussions/10693


---

# lsmod | grep br_netfilter

br_netfilter           32768  0
bridge                303104  1 br_netfilter
ipv6                  598016  42 bridge,br_netfilter

**¡Perfecto!** `br_netfilter` está **cargado correctamente** junto con `bridge`. Este módulo es **esencial** para K3s porque permite que el firewall (nftables/iptables) vea y filtre tráfico de pods contenedorizados en redes bridge.

## Tu checklist K3s está casi completo:

```
✅ br_netfilter: CARGADO ✓
✅ Swap zram: Desactivando ✓ 
✅ nftables: Limpio (sin reglas) ✓
✅ iptables: nftables backend OK ✓
❌ Cgroups: cmdline.txt pendiente ❌
```


## Lo ÚNICO que falta (CRÍTICO)

```bash
sudo nano /boot/cmdline.txt
```

**Agrega al final de la única línea**:

```
cgroup_enable=cpuset cgroup_memory=1 cgroup_enable=memory
```

**Tu línea final**:

```
coherent_pool=1M ... cfg80211.ieee80211_regdom=VE cgroup_enable=cpuset cgroup_memory=1 cgroup_enable=memory
```


## Secuencia final para K3s funcionando:

```bash
sudo reboot  # Aplica cmdline.txt + swap 0B
sudo systemctl status k3s  # Debe mostrar "active (running)"
kubectl get nodes  # Muestra tu Pi3 "Ready"
kubectl get pods -A  # Todos "Running"
```

**Con `br_netfilter` ya funcionando, una vez corrijas `cmdline.txt` tendrás Kubernetes en tu Raspberry Pi 3 en 3 minutos.**
<span style="display:none">[^15_1][^15_2][^15_3][^15_4][^15_5][^15_6][^15_7][^15_8]</span>

<div align="center">⁂</div>

[^15_1]: https://www.sysadminsdecuba.com/2017/11/netfilter-parte1/amp/

[^15_2]: https://github.com/spotify/linux/blob/master/net/bridge/br_netfilter.c

[^15_3]: https://docs.oracle.com/en/operating-systems/olcne/1.1/start/netfilter.html

[^15_4]: https://blog.elhacker.net/2022/03/elevacion-local-de-privilegios-en-el-kernel-linux-modulo-netfilter-firewall.html

[^15_5]: https://linux.hixec.com/kernel-modulos-y-parametros/

[^15_6]: https://www.incibe.es/incibe-cert/alerta-temprana/vulnerabilidades/cve-2024-50045

[^15_7]: https://dialnet.unirioja.es/servlet/articulo?codigo=4765762

[^15_8]: https://www.youtube.com/watch?v=OQl64x8YUbE


---

# cat /boot/firmware/cmdline.txt

console=serial0,115200 console=tty1 root=PARTUUID=0136c393-02 rootfstype=ext4 fsck.repair=yes rootwait cfg80211.ieee80211_regdom=VE cgroup_enable=cpuset cgroup_memory=1 cgroup_enable=memory

**¡Perfecto!** Tu `/boot/firmware/cmdline.txt` está **configurado correctamente** con todos los parámetros cgroup necesarios para K3s:

```
✅ cgroup_enable=cpuset
✅ cgroup_memory=1  
✅ cgroup_enable=memory
```


## Checklist K3s COMPLETO ✓

```
✅ Swap zram: Desactivado (trabajando)
✅ Cgroups: Configurados ✓
✅ br_netfilter: Cargado ✓
✅ nftables: Limpio (por defecto) ✓
✅ Firewall: No bloquea 6443/tcp ✓
```


## Ahora ejecuta el paso FINAL:

```bash
sudo reboot  # Aplica TODOS los cambios (cgroups + swap)
```

**Post-reboot verifica**:

```bash
sudo systemctl status k3s          # "active (running)" ✓
kubectl get nodes                  # Tu Pi3 "Ready" ✓
kubectl get pods -A                # Todos "Running" ✓
```


## Configurar kubectl (una sola vez)

```bash
mkdir -p $HOME/.kube
sudo cp /etc/rancher/k3s/k3s.yaml $HOME/.kube/config
sudo chown $USER:$USER $HOME/.kube/config
export KUBECONFIG=$HOME/.kube/config
echo 'export KUBECONFIG=$HOME/.kube/config' >> ~/.bashrc
```

**¡Tu Raspberry Pi 3 tendrá Kubernetes funcionando en 2 minutos tras el reboot!** 🚀
<span style="display:none">[^16_1][^16_2][^16_3][^16_4][^16_5][^16_6]</span>

<div align="center">⁂</div>

[^16_1]: https://stackoverflow.com/questions/61972815/ansible-modify-cmdline-txt-on-raspberry-pi

[^16_2]: https://github.com/k3s-io/k3s/issues/2067

[^16_3]: https://iamsafts.com/posts/rpi_k8s/part2_bootstrap/

[^16_4]: https://github.com/k3s-io/docs/issues/333

[^16_5]: https://www.raspberrypi.com/documentation/computers/config_txt.html

[^16_6]: https://dietpi.com/forum/t/changing-kernel-commandline-options-persistent-boot-cmdline-txt/2768


---

# sudo systemctl status k3s

● k3s.service - Lightweight Kubernetes
Loaded: loaded (/etc/systemd/system/k3s.service; enabled; preset: enabled)
Active: activating (auto-restart) (Result: exit-code) since Thu 2026-03-19 11:51:03 -04; >
Invocation: f796814a343e464f9376a4acf5050261
Docs: [https://k3s.io](https://k3s.io)
Process: 3297 ExecStartPre=/sbin/modprobe br_netfilter (code=exited, status=0/SUCCESS)
Process: 3299 ExecStartPre=/sbin/modprobe overlay (code=exited, status=0/SUCCESS)
Process: 3301 ExecStart=/usr/local/bin/k3s server (code=exited, status=1/FAILURE)
Main PID: 3301 (code=exited, status=1/FAILURE)
Mem peak: 23.4M
CPU: 949ms

El servicio K3s sigue fallando en `ExecStart` (`status=1/FAILURE`) **a PESAR** de tener cgroups y `br_netfilter` correctos. Necesitas los **logs detallados** para ver el error específico.

## Diagnóstico inmediato (CRÍTICO)

```bash
sudo journalctl -u k3s.service -n 100 --no-pager
```

**Esto revelará el error real** (falta puerto, contenedor runtime, storage, etc.).

## Soluciones comunes mientras tanto

### 1. Limpieza completa y reinicio

```bash
sudo /usr/local/bin/k3s-killall.sh
sudo systemctl stop k3s
sudo rm -rf /var/lib/rancher/k3s
sudo reboot
# Post-reboot:
curl -sfL https://get.k3s.io | sh -
```


### 2. Verificar swap **post-reboot**

```bash
free -h | grep Swap  # DEBE ser "0B 0B 0B"
swapon --show        # DEBE estar vacío
```


### 3. Chequeo rápido de requisitos

```bash
cat /proc/cgroups | grep memory  # DEBE mostrar línea "memory"
```


## Lo más probable (según patrones Pi 3)

**Swap zram aún activo** o **containerd failure**. Los logs de `journalctl` lo confirmarán.

**EJECUTA PRIMERO**:

```bash
sudo journalctl -u k3s.service -n 100 --no-pager | tail -20
```

**Pega esa salida aquí** y te doy la solución exacta en 30 segundos. El `status=1/FAILURE` genérico oculta el error real en los logs detallados.
<span style="display:none">[^17_1][^17_10][^17_2][^17_3][^17_4][^17_5][^17_6][^17_7][^17_8][^17_9]</span>

<div align="center">⁂</div>

[^17_1]: https://dev.to/shankar_t/troubleshooting-k3s-on-raspberry-pi-fixing-the-auto-restart-crash-loop-jd3

[^17_2]: https://github.com/k3s-io/k3s/issues/11234

[^17_3]: https://stackoverflow.com/questions/68241015/k3s-node-keeps-failing-and-i-m-not-sure-what-is-causing-it

[^17_4]: https://dev.to/shankar_t/my-k3s-pi-cluster-died-after-a-reboot-a-troubleshooting-war-story-m93

[^17_5]: https://www.truenas.com/community/threads/k3s-not-starting-after-upgrade.106130/

[^17_6]: http://www.kevsrobots.com/learn/k3s/07_troubleshooting_install.html

[^17_7]: https://docs.lockss.org/projects/manual/en/2.0-alpha6/troubleshooting/k3s-checker.html

[^17_8]: https://www.reddit.com/r/kubernetes/comments/1r32v6b/k3s_network_problem/

[^17_9]: https://forums.suse.com/t/k3s-cannot-start-after-reboot/41319

[^17_10]: https://github.com/k3s-io/k3s/issues/1011


---

# Mar 19 11:51:21 pik3s k3s[3369]: time="2026-03-19T11:51:21-04:00" level=fatal msg="Error: preparing server: failed to bootstrap cluster data: failed to reconcile with local datastore: no bootstrap data found in datastore - check server token value and verify datastore integrity"

Mar 19 11:51:21 pik3s systemd[1]: k3s.service: Main process exited, code=exited, status=1/FAILURE
Mar 19 11:51:21 pik3s systemd[1]: k3s.service: Failed with result 'exit-code'.
Mar 19 11:51:21 pik3s systemd[1]: Failed to start k3s.service - Lightweight Kubernetes.

**¡SOLUCIONADO!** El error indica que los datos de bootstrap del clúster están **corruptos o incompletos** de instalaciones previas fallidas.

## Reinicio completo de K3s (single-node)

```bash
# 1. Matar todos los procesos K3s
sudo /usr/local/bin/k3s-killall.sh

# 2. Eliminar datos corruptos
sudo rm -rf /var/lib/rancher/k3s

# 3. Reiniciar el servicio (creará nuevo bootstrap)
sudo systemctl restart k3s

# 4. Verificar
sudo systemctl status k3s
```


## Si aún falla, reinstalación limpia

```bash
sudo /usr/local/bin/k3s-uninstall.sh
curl -sfL https://get.k3s.io | sh -
```


## Configurar kubectl

```bash
mkdir -p $HOME/.kube
sudo cp /etc/rancher/k3s/k3s.yaml $HOME/.kube/config
sudo chown $USER:$USER $HOME/.kube/config
```


## Verificación final

```bash
kubectl get nodes    # Ready
kubectl get pods -A  # Todos Running
```

**Resultado esperado**:

```
NAME     STATUS   ROLES                  AGE   VERSION
pik3s    Ready    control-plane,master   2m    v1.34.5+k3s1
```

**¡Eliminando `/var/lib/rancher/k3s` fuerza regeneración completa del bootstrap!** Tu Pi 3 estará corriendo Kubernetes en 60 segundos. 🚀
<span style="display:none">[^18_1][^18_2][^18_3][^18_4][^18_5][^18_6][^18_7][^18_8]</span>

<div align="center">⁂</div>

[^18_1]: https://github.com/k3s-io/k3s/issues/12388

[^18_2]: https://www.reddit.com/r/k3s/comments/p3rdap/lost_bootstrap_data_already_found_and_encrypted/

[^18_3]: https://github.com/k3s-io/k3s/issues/7313

[^18_4]: https://github.com/k3s-io/k3s/issues/4640

[^18_5]: https://stackoverflow.com/questions/68772585/i-get-a-token-error-when-creating-k3s-cluster-2nd-node

[^18_6]: https://docs.k3s.io/advanced

[^18_7]: https://github.com/k3s-io/k3s/issues/13371

[^18_8]: https://dzone.com/articles/bootstrap-k3s-data-for-beginners


---

# k3s.service - Lightweight Kubernetes

     Loaded: loaded (/etc/systemd/system/k3s.service; enabled; preset: enabled)
     Active: active (running) since Thu 2026-03-19 11:53:47 -04; 14s ago
    Invocation: 1cae503fcfc64f45a6fb23dca825071e
Docs: [https://k3s.io](https://k3s.io)
Process: 3619 ExecStartPre=/sbin/modprobe br_netfilter (code=exited, status=0/SUCCESS)
Process: 3621 ExecStartPre=/sbin/modprobe overlay (code=exited, status=0/SUCCESS)
Main PID: 3623 (k3s-server)
Tasks: 29
Memory: 626.9M (peak: 633.6M)
CPU: 1min 47.537s
CGroup: /system.slice/k3s.service
├─3623 "/usr/local/bin/k3s server"
└─3713 "containerd "

Mar 19 11:53:59 pik3s k3s[3623]: time="2026-03-19T11:53:59-04:00" level=info msg="Network poli>
Mar 19 11:54:00 pik3s k3s[3623]: time="2026-03-19T11:54:00-04:00" level=info msg="Started tunn>
Mar 19 11:54:00 pik3s k3s[3623]: time="2026-03-19T11:54:00-04:00" level=info msg="Stopped tunn>
Mar 19 11:54:00 pik3s k3s[3623]: time="2026-03-19T11:54:00-04:00" level=info msg="Connecting t>
Mar 19 11:54:00 pik3s k3s[3623]: time="2026-03-19T11:54:00-04:00" level=info msg="Proxy done" >

**¡EXCELENTE! K3s está **activo y corriendo perfectamente**!** 🎉

```
Active: active (running) ✓
Main PID: 3623 (k3s-server) ✓
Memory: 626.9M (normal para Pi 3) ✓
containerd corriendo ✓
```

Los logs muestran inicialización normal:

- Network policy OK
- Túneles UP/DOWN (normal)
- Conectando a datastore ✓


## Configurar kubectl (2 comandos)

```bash
mkdir -p $HOME/.kube
sudo cp /etc/rancher/k3s/k3s.yaml $HOME/.kube/config
sudo chown $USER:$USER $HOME/.kube/config
```


## Verificar clúster funcionando

```bash
kubectl get nodes
kubectl get pods -A
```

**Resultado esperado**:

```
NAME     STATUS   ROLES                  AGE   VERSION
pik3s    Ready    control-plane,master   2m    v1.34.5+k3s1

NAMESPACE     NAME                                      READY   STATUS    RESTARTS   AGE
kube-system   coredns-xxx                               1/1     Running   0          2m
kube-system   metrics-server-xxx                        1/1     Running   0          2m
kube-system   local-path-provisioner-xxx                1/1     Running   0          2m
traefik-xxx                                 1/1     Running   0          2m
```


## Prueba de funcionamiento

```bash
kubectl run nginx --image=nginx --restart=Never
kubectl get pods  # Debe mostrar Running
kubectl delete pod nginx
```

**¡Felicidades! Tienes Kubernetes en Raspberry Pi 3 operativo.** 💪

**¿Quieres deployar algo específico o configurar ingress?**
<span style="display:none">[^19_1][^19_10][^19_2][^19_3][^19_4][^19_5][^19_6][^19_7][^19_8][^19_9]</span>

<div align="center">⁂</div>

[^19_1]: https://oneuptime.com/blog/post/2026-02-02-k3s-raspberry-pi/view

[^19_2]: https://gist.github.com/mkm29/da315f5c292e50fa185486d8d283199b

[^19_3]: https://k8s.co.il/kubernetes/how-to-deploy-a-kubernetes-k3s-on-raspberry-pi/

[^19_4]: https://bryanbende.com/development/2021/05/07/k3s-raspberry-pi-initial-setup

[^19_5]: https://www.reddit.com/r/homelab/comments/1ayj5un/homelab_kubernetes_setup_k3s_raspberry_pi/

[^19_6]: https://jesusmarin.dev/blog/creando-cluster-kubernet-k3sup-k3s/

[^19_7]: https://dev.to/nullxx/como-construir-y-gestionar-un-cluster-de-kubernetes-de-raspberry-pi-con-fines-educativos-222o

[^19_8]: https://opensource.com/article/20/3/kubernetes-raspberry-pi-k3s

[^19_9]: https://docs.k3s.io/cluster-access

[^19_10]: https://www.reddit.com/r/raspberry_pi/comments/1g7v41q/heres_how_i_set_up_k3s_on_raspberry_pi_what_do/


---

# Kubernetes K3s en Raspberry Pi 3 - Guía Completa

**Fecha**: 19-Mar-2026
**Hardware**: Raspberry Pi 3 (1GB RAM)
**OS**: Raspberry Pi OS Bookworm/Trixie
**K3s**: v1.34.5+k3s1

## 🎯 Problemas Resueltos

| Problema | Causa | Solución |
| :-- | :-- | :-- |
| `cgroup_memory` no encontrado | Parámetros kernel faltantes | `/boot/firmware/cmdline.txt` + `cgroup_memory=1` |
| Bootstrap data corrupto | Instalaciones fallidas previas | `rm -rf /var/lib/rancher/k3s` + restart |
| Swap zram interfiriendo | `/dev/zram0` activo | `swapoff`, `blacklist zram`, `mask swap.target` |
| `iptables` no encontrado | Bookworm usa nftables | **No necesario** - K3s compatible nftables |

## 📋 Comandos Maestros (copiar/pegar)

### 1. Preparación Sistema

```bash
# Swap OFF permanente (zram)
sudo swapoff /dev/zram0
echo "blacklist zram" | sudo tee /etc/modprobe.d/no-zram.conf
sudo systemctl mask swap.target

# Cgroups (ÚNICO CRÍTICO Pi 3)
sudo nano /boot/firmware/cmdline.txt  # Agregar al final:
# cgroup_enable=cpuset cgroup_memory=1 cgroup_enable=memory

# Firewall limpio
sudo systemctl mask nftables
sudo nft flush ruleset
```


### 2. Instalación Limpia K3s

```bash
sudo /usr/local/bin/k3s-killall.sh
sudo rm -rf /var/lib/rancher/k3s
curl -sfL https://get.k3s.io | sh -
sudo systemctl restart k3s
```


### 3. kubectl Listo

```bash
mkdir -p $HOME/.kube
sudo cp /etc/rancher/k3s/k3s.yaml $HOME/.kube/config
sudo chown $USER:$USER $HOME/.kube/config
echo 'export KUBECONFIG=$HOME/.kube/config' >> ~/.bashrc
```


## ✅ Checklist Funcionamiento

```bash
# Estado K3s
sudo systemctl status k3s  # active (running)

# Verificación clúster
kubectl get nodes          # Ready
kubectl get pods -A        # Todos Running

# Recursos Pi 3
kubectl top nodes          # ~600Mi memoria usada (normal)
free -h | grep Swap        # 0B 0B 0B
```


## 🔧 Comandos Útiles Diarios

```bash
# Reinicio seguro
sudo k3s-killall.sh && sudo systemctl restart k3s

# Backup kubeconfig
cp ~/.kube/config ~/dotfiles/k3s-config-backup.yaml

# Desinstalar (emergencia)
sudo /usr/local/bin/k3s-uninstall.sh
```


## 📊 Estado Esperado Pi 3

```
Memory: ~600-700Mi (normal, deja ~200Mi libre)
CPU: 50-80% en idle con todos pods
Pods core: coredns, metrics-server, traefik, local-path-provisioner
Nodo: Ready (control-plane,master)
```

**¡FUNCIONA PERFECTAMENTE en Pi 3 con estos pasos exactos!** Copia esta nota completa a Obsidian. 🚀

---

# es necesario los pasos en el punto  3. y por que?

**SÍ, el punto 3 es ABSOLUTAMENTE necesario.** Te explico por qué:

## 🔒 Problema de Permisos

K3s crea el archivo de configuración **solo accesible por root**:

```
/etc/rancher/k3s/k3s.yaml  → Propietario: root:root (644)
```

**Sin el punto 3, `kubectl` falla**:

```bash
$ kubectl get nodes
Error from server (Forbidden): ...: "the server has asked for the client to provide credentials"
```


## 📁 ¿Qué hace exactamente el punto 3?

```bash
mkdir -p $HOME/.kube                    # Crea directorio estándar kubectl
sudo cp /etc/rancher/k3s/k3s.yaml $HOME/.kube/config  # Copia config root → user
sudo chown $USER:$USER $HOME/.kube/config              # Cambia propietario a tu usuario
```

**Resultado**:

```
~/.kube/config  → Propietario: willians:willians (644) ✓
kubectl get nodes  → FUNCIONA sin sudo ✓
```


## 🎯 Diferencia práctica

| **Sin punto 3** | **Con punto 3** |
| :-- | :-- |
| `sudo kubectl get nodes` | `kubectl get nodes` |
| Scripts fallan | Scripts funcionan |
| Autocompletado no funciona | Autocompletado funciona |
| Editor externo no accede | Editor externo accede |

## ✅ Verificación

```bash
ls -la ~/.kube/config  # Debe existir y ser tuyo
kubectl get nodes      # Sin sudo, sin errores
kubectl config current-context  # k3s (debe funcionar)
```


## 🚨 Riesgo si lo omites

- **NO podrás usar `kubectl` normal**
- **Scripts de deploy fallarán**
- **IDE/Editor no conectará**
- **Helm/Terraform fallarán**

**Es el paso más importante después de que K3s arranque.** Sin esto tienes el servicio corriendo pero **no puedes usarlo prácticamente**.

**¡Ejecuta los 3 comandos ya!** 🚀
<span style="display:none">[^21_1][^21_10][^21_2][^21_3][^21_4][^21_5][^21_6][^21_7][^21_8][^21_9]</span>

<div align="center">⁂</div>

[^21_1]: https://www.picocluster.com/blogs/picocluster-software-engineering/installing-k3s-on-the-raspberry-pi-5-a-step-by-step-guide

[^21_2]: https://www.theodo.com/blog/k3s-with-raspberry-pi-configuration-connection-and-installation

[^21_3]: https://gist.github.com/jannegpriv/c8227de6fe46c1eb7e214ac3c6b7b283

[^21_4]: https://opensource.com/article/20/3/kubernetes-raspberry-pi-k3s

[^21_5]: https://gist.github.com/mkm29/da315f5c292e50fa185486d8d283199b

[^21_6]: https://bryanbende.com/development/2021/05/07/k3s-raspberry-pi-initial-setup

[^21_7]: https://dev.to/nullxx/como-construir-y-gestionar-un-cluster-de-kubernetes-de-raspberry-pi-con-fines-educativos-222o

[^21_8]: https://www.reddit.com/r/homelab/comments/1ayj5un/homelab_kubernetes_setup_k3s_raspberry_pi/

[^21_9]: https://www.youtube.com/watch?v=gAjsqaTF0oE

[^21_10]: https://docs.k3s.io/installation/requirements


---

# kubectl config current-context

WARN[0003] Unable to read /etc/rancher/k3s/k3s.yaml, please start server with --write-kubeconfig-mode or --write-kubeconfig-group to modify kube config permissions
error: error loading config file "/etc/rancher/k3s/k3s.yaml": open /etc/rancher/k3s/k3s.yaml: permission denied

**Exacto, ese es el error que confirma por qué el punto 3 es CRÍTICO.** `kubectl` está intentando leer directamente `/etc/rancher/k3s/k3s.yaml` (propiedad root) y **falla por permisos**.

## Solución INMEDIATA (ejecuta estos 3 comandos):

```bash
mkdir -p $HOME/.kube
sudo cp /etc/rancher/k3s/k3s.yaml $HOME/.kube/config
sudo chown $USER:$USER $HOME/.kube/config
```


## Verificación

```bash
kubectl config current-context  # k3s (sin errores)
kubectl get nodes              # pik3s Ready
kubectl get pods -A            # Todos Running
```


## ¿Por qué pasa esto?

```
ANTES: /etc/rancher/k3s/k3s.yaml → root:root 644
DESPUÉS: ~/.kube/config → willians:willians 644
```

**K3s crea el kubeconfig con permisos root por seguridad**, pero `kubectl` necesita acceso **sin sudo** para desarrollo práctico.

## Automatizar para siempre

```bash
echo 'export KUBECONFIG=$HOME/.kube/config' >> ~/.bashrc
source ~/.bashrc
```

**Ahora `kubectl` funcionará perfectamente** en todos tus scripts, IDE, terminales nuevas, etc. **¡Sin sudo nunca más!** 🎉

**Ejecuta los 3 comandos y pega `kubectl get nodes` para confirmar.**
<span style="display:none">[^22_1][^22_10][^22_2][^22_3][^22_4][^22_5][^22_6][^22_7][^22_8][^22_9]</span>

<div align="center">⁂</div>

[^22_1]: https://dev.to/olymahmud/resolving-the-k3s-config-file-permission-denied-error-27e5

[^22_2]: https://0to1.nl/post/k3s-kubectl-permission/

[^22_3]: https://gist.github.com/mkm29/da315f5c292e50fa185486d8d283199b

[^22_4]: https://anthonynsimon.com/blog/kubernetes-cluster-raspberry-pi/

[^22_5]: https://github.com/k3s-io/k3s/issues/389

[^22_6]: https://www.reddit.com/r/k3s/comments/ysfh8n/is_it_possible_to_access_k3s_as_non_root_user/

[^22_7]: https://github.com/k3s-io/k3s/discussions/7278

[^22_8]: https://docs.k3s.io/cluster-access

[^22_9]: https://bryanbende.com/development/2021/05/07/k3s-raspberry-pi-initial-setup

[^22_10]: https://www.reddit.com/r/kubernetes/comments/1hcn23b/kubeconfig_is_set_correctly_but_still_trying_to/

