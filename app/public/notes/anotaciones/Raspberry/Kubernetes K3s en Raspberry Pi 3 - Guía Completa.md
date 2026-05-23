Kubernetes K3s en Raspberry Pi 3 - Guía Completa
Fecha: 19-Mar-2026
Hardware: Raspberry Pi 3 (1GB RAM)
OS: Raspberry Pi OS Bookworm/Trixie
K3s: v1.34.5+k3s1

🎯 Problemas Resueltos
|`cgroup_memory` no encontrado|Parámetros kernel faltantes|`/boot/firmware/cmdline.txt` + `cgroup_memory=1`|
|Bootstrap data corrupto|Instalaciones fallidas previas|`rm -rf /var/lib/rancher/k3s` + restart|
|Swap zram interfiriendo|`/dev/zram0` activo|`swapoff`, `blacklist zram`, `mask swap.target`|
|`iptables` no encontrado|Bookworm usa nftables|**No necesario** - K3s compatible nftables|

## 📋 Comandos Maestros (copiar/pegar)

## 1. Preparación Sistema

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



## 2. Instalación Limpia K3s
```bash
sudo /usr/local/bin/k3s-killall.sh
sudo rm -rf /var/lib/rancher/k3s
curl -sfL https://get.k3s.io | sh -
sudo systemctl restart k3s
```



## 3. kubectl Listo
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

```bash
Memory: ~600-700Mi (normal, deja ~200Mi libre)
CPU: 50-80% en idle con todos pods
Pods core: coredns, metrics-server, traefik, local-path-provisioner
Nodo: Ready (control-plane,master)

```


# Errores

kubectl config current-context WARN[0003] Unable to read /etc/rancher/k3s/k3s.yaml, please start server with --write-kubeconfig-mode or --write-kubeconfig-group to modify kube config permissions error: error loading config file "/etc/rancher/k3s/k3s.yaml": open /etc/rancher/k3s/k3s.yaml: permission denied

## Solución INMEDIATA (ejecuta estos 3 comandos):

```bash
mkdir -p $HOME/.kube
sudo cp /etc/rancher/k3s/k3s.yaml $HOME/.kube/config
sudo chown $USER:$USER $HOME/.kube/config

# Importante
sudo chmod 644 ~/.kube/config

```
