
OS Lite *trixie*

sudo swapoff /dev/zram0
sudo modprobe -r zram



# 1. Desactivar inmediatamente
sudo swapoff /dev/zram0
sudo modprobe -r zram

# 2. Bloquear zram en arranque
echo "blacklist zram" | sudo tee /etc/modprobe.d/no-zram.conf

# 3. Máscar swap systemd
sudo systemctl mask swap.target