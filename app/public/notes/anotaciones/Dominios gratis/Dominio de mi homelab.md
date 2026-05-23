
## Primeros pasos con el primer dominio de Internet

De antemano preparar una VM basada en Ubuntu. Le he asignado el nombre `dummy`.

**IP: 10.0.0.199**   Asignada por **pfsense**  por su MAC.

### 1. Crear cuenta en [www.dynu.com](https://www.dynu.com)

### 2. Crear mi dominio:

```bash
datenmaniak.opik.net
```

### 3. Efectuar las configuraciones necesarias en el `dummy`

Siguiendo las instrucciones de **dynu.com**

IP update client for Linux runs as a system service (systemd) and supports IPv4 and IPv6 updates. Users can use the group feature to update a specific collection of hostnames.

**Ubuntu 24.04 x64 (.deb file)**  
  

```bash
sudo apt update  
sudo apt install dotnet-runtime-8.0
```

```
sudo wget --trust-server-names https://www.dynu.com/support/downloadfile/69
```

```
sudo apt install ./dynu-ip-update-client_1.0.2-1_amd64.deb
```


