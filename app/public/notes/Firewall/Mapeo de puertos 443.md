
### El "Truco" del puerto 443

**Cuidado aquí:** Por defecto, pfSense usa el puerto **443** para su propia interfaz web (WebGUI). Si intentas mapear el 443 de la WAN al clúster, podrías tener un conflicto o quedarte fuera de la configuración del firewall.

**Tienes dos opciones:**

1. **Cambiar el puerto de pfSense:** Ve a _System > Advanced > Admin Access_ y cambia "TCP Port" de 443 a algo como `8443`.
    
2. **Usar un puerto distinto en la WAN:** Mapear el puerto `8443` de la WAN al `443` del Master.

