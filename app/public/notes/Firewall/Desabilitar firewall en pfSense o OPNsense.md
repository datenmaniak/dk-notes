
### 3. El último paso de "Seguridad"

Si estás intentando configurar OPNsense desde tu PC (a través de la IP de la WAN), OPNsense bloquea por defecto el acceso administrativo desde la WAN por seguridad.

**Si te quedas bloqueado fuera:** Entra a la **Console** de la VM en Proxmox (la pantalla negra) y elige la **opción 8 (Shell)**. Escribe esto para desabilitar el firewall temporalmente y poder entrar desde tu red actual:


**En la consola del firewall pfSense o OPNsense**:

```bash
pfctl -d
```


Esto te dará "vía libre" para entrar desde tu navegador habitual a la IP de la WAN de OPNsense, terminar de configurar la LAN y luego volver a activar el firewall.