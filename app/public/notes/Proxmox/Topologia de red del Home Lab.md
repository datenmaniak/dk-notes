
## Voy a ilustrar un poco mas cual es la topologia de red de este homelab.


**A. Existe el servidor Proxmox, tiene dos bridges:   **

- vmbr0 (WAN) cuya IP es la 192.168.1.201/24. 
- vmbr1 (LAN) no tiene IP.      

**B. Hay un Firewall basado en Pfsense**

-  Interfaz WAN:  192.168.1.2
-  interfaz LAN:  10.0.0.0/24

**C. Un Cluster de Kubernetes**

Conformado por:
1. K3s Master, cuya IP es la 10.0.0.100
2. Worker1   con la IP 10.0.0.101
3. Worker2  con la IP 10.0.0.102


La particion **/mnt/databases**   se encuentra en el servidor Proxmox.

