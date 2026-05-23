
```bash
# Escanear tu red local para encontrar las VMs
nmap -sn 192.168.1.0/24 | grep -E "Nmap scan|ubuntu"
```

