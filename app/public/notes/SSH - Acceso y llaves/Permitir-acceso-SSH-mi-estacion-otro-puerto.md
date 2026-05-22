<img src="https://r2cdn.perplexity.ai/pplx-full-logo-primary-dark%402x.png" style="height:64px;margin-right:32px"/>

# dame las instrucciones para permitir accesso SSH a mi estacion de trabajo

Para permitir acceso SSH a tu estación de trabajo (Linux), en general necesitas:

1. **Instalar el servidor SSH (OpenSSH)**
En la mayoría de distros basadas en Debian/Ubuntu (incluyendo Fedora con `dnf`/`microdnf` adecuados):
    - Ubuntu/Debian:

```bash
sudo apt update
sudo apt install openssh-server
```

    - Fedora/RHEL (si no está instalado):

```bash
sudo dnf install openssh-server      # o microdnf en imágenes base
```

2. **Arrancar y habilitar el servicio**

```bash
sudo systemctl enable sshd
sudo systemctl start sshd
```

(En algunos sistemas el servicio se llama `ssh` en vez de `sshd`, usa `sudo systemctl status ssh` para ver el nombre exacto).
3. **Ajustar el firewall (por ejemplo, firewalld en Fedora)**
Si usas `firewalld`:

```bash
sudo firewall-cmd --permanent --add-service=ssh
sudo firewall-cmd --reload
```

Si usas `ufw` (Ubuntu típico):

```bash
sudo ufw allow ssh
```

4. **Opcional: endurecer la configuración SSH**
Edita el archivo de configuración:

```bash
sudo nano /etc/ssh/sshd_config
```

Y, por ejemplo, considera:
    - Cambiar el puerto por defecto de 22 a otro (por seguridad en la capa superficial):
`Port 2222`
    - Deshabilitar login como root:
`PermitRootLogin no`
    - Permitir solo ciertos usuarios:
`AllowUsers tu_usuario`
    - Deshabilitar autenticación por contraseña si usas claves:
`PasswordAuthentication no` (solo si ya tienes claves SSH configuradas bien).

Después de editar:

```bash
sudo systemctl restart sshd
```

5. **Acceder desde un cliente**
Desde otra máquina, usa:

```bash
ssh tu_usuario@ip_de_tu_estacion
```

Si cambiaste el puerto:

```bash
ssh -p 2222 tu_usuario@ip_de_tu_estacion
```


***

Si comentas qué distro usas exactamente (por ejemplo, Fedora Kinoite/Bluefin, Ubuntu, Debian) y si esta máquina está detrás de NAT/router, te doy una lista de comandos aún más específica para tu caso.
<span style="display:none">[^1_1][^1_10][^1_2][^1_3][^1_4][^1_5][^1_6][^1_7][^1_8][^1_9]</span>

<div align="center">⁂</div>

[^1_1]: https://www.redeszone.net/tutoriales/servidores/servidor-openssh-linux-configuracion-maxima-seguridad/

[^1_2]: https://helpdesk.cdmon.com/portal/es/kb/articles/cómo-activar-el-acceso-por-ssh

[^1_3]: https://docs.redhat.com/es/documentation/red_hat_enterprise_linux/6/html/installation_guide/sn-ssh-installation

[^1_4]: https://help.dreamhost.com/hc/es/articles/360000281572--Agregar-acceso-SSH-a-tus-usuarios

[^1_5]: https://es.siteground.com/tutoriales/ssh/activar/

[^1_6]: https://www.youtube.com/watch?v=A3HlrQKz7jw

[^1_7]: https://www.ibm.com/docs/es/ahts/4.4.x?topic=upgrades-installing-configuring-ssh-server

[^1_8]: https://www.youtube.com/watch?v=1_X2vVunC38

[^1_9]: https://www.reddit.com/r/linuxadmin/comments/1ax92h3/configuring_ssh_to_only_allow_access_from_1_ip/

[^1_10]: https://jotelulu.com/soporte/tutoriales/como-configurar-ssh-servidor-existente/


---

# aun aparece el mensaje: Connection refused

El mensaje `Connection refused` indica que el servidor SSH **no está escuchando** en el puerto al que intentas conectarte, o que algo lo bloquea (firewall, servicio apagado, etc.).[^2_1][^2_3]

### 1. Verifica que el servicio SSH está activo

En tu estación de trabajo (la máquina que quieres conectar por SSH):

```bash
sudo systemctl status sshd
```

- Si ves que está `inactive` o `failed`, arranca y habilita:

```bash
sudo systemctl enable --now sshd
sudo systemctl restart sshd
```

(En algunas distros el servicio se llama `ssh` en vez de `sshd`, prueba también `sudo systemctl status ssh` si falla).[^2_3][^2_7]


### 2. Comprueba que SSH está escuchando en el puerto

Ejecuta esto en la misma máquina:

```bash
sudo ss -tuln | grep ':22'
```

- Si no ves nada en el puerto 22, SSH no está escuchando y el error `Connection refused` es normal.
- Si cambiaste el puerto en `/etc/ssh/sshd_config` (por ejemplo a 2222), verifica:

```bash
sudo ss -tuln | grep ':2222'
```

Y luego conecta desde el cliente con:

```bash
ssh -p 2222 tu_usuario@ip_de_la_estacion
```


### 3. Revisa el firewall local

En tu estación de trabajo:

- Si usas `firewalld` (Fedora, RHEL):

```bash
sudo firewall-cmd --list-services | grep ssh
sudo firewall-cmd --permanent --add-service=ssh
sudo firewall-cmd --reload
```

- Si usas `ufw` (Ubuntu típico):

```bash
sudo ufw status
sudo ufw allow ssh
```


Cuando el firewall bloquea el puerto, se suele ver `Connection refused` o timeout.[^2_6][^2_1]

### 4. Comprueba IP y conectividad básica

Desde la máquina desde donde quieres conectarte:

- Asegúrate de que la IP de la estación de trabajo es correcta:

```bash
arp -a      # o nmap en tu red local
```

- Haz un ping básico:

```bash
ping ip_de_la_estacion
```

- Prueba si el puerto 22 está abierto desde afuera:

```bash
telnet ip_de_la_estacion 22
# o
nc -vz ip_de_la_estacion 22
```

Si el puerto 22 no responde, el problema es en el servidor (SSH no escuchando o firewall bloqueando).[^2_1][^2_6]

***

**Para darte una solución exacta**, dime:

- ¿Qué distro usas exactamente? (por ejemplo `Fedora 40 Bluefin`, `Ubuntu 24.04`, etc.).
- ¿Qué comando exacto estás usando para hacer SSH? (por ejemplo `ssh user@192.168.1.100`).
- ¿Estás intentando conectarte desde la misma red local o desde internet?
<span style="display:none">[^2_10][^2_2][^2_4][^2_5][^2_8][^2_9]</span>

<div align="center">⁂</div>

[^2_1]: https://builtin.com/articles/ssh-connection-refused

[^2_2]: https://learn.microsoft.com/es-es/troubleshoot/windows-server/system-management-components/troubleshoot-openssh-windows-firewall-port22

[^2_3]: https://kinsta.com/blog/ssh-connection-refused/

[^2_4]: https://www.reddit.com/r/digital_ocean/comments/1aw3chh/ssh_connect_to_host_port_22_connection_timed_out/

[^2_5]: https://www.k2webhost.com/blog/blog/solucion-al-error-conexion-rechazada-en-conexiones-ssh/

[^2_6]: https://es.hostzealot.com/blog/how-to/como-solucionar-el-error-conexion-denegada-en-el-puerto-22-de-linux

[^2_7]: https://www.hostinger.com/es/tutoriales/ssh-connection-refused

[^2_8]: https://www.siteground.es/kb/error-connection-refused-ssh/

[^2_9]: https://www.youtube.com/watch?v=-tl_-d0JfuU

[^2_10]: https://soporte.hostgator.mx/hc/es-419/articles/44275774391059-Cómo-solucionar-errores-de-acceso-SSH


---

# ahora que cambie el puerto de 22 a otro. me parece que hay que agregar una regla al firewall...

Sí, justo ahí te falta abrir el nuevo puerto en el firewall; si el servicio SSH escucha en, por ejemplo, el puerto `2222`, pero el firewall solo permite el 22, verás `Connection refused` o que no llega el paquete.[^3_1][^3_8]

### 1. Si usas `firewalld` (típico en Fedora / RHEL)

1. Abre el puerto nuevo (por ejemplo `2222`):

```bash
sudo firewall-cmd --permanent --add-port=2222/tcp
sudo firewall-cmd --reload
```

2. Si quieres, puedes cerrar el 22 (opcional, por seguridad):

```bash
sudo firewall-cmd --permanent --remove-service=ssh
sudo firewall-cmd --reload
```

(Si dejas `ssh` habilitado, seguirá permitiendo el puerto 22; si sólo quieres el 2222, quítalo).[^3_2][^3_4]

### 2. Si usas `ufw` (Ubuntu / Debian típico)

1. Abre el nuevo puerto:

```bash
sudo ufw allow 2222/tcp
sudo ufw status
```

2. Si quieres cerrar el 22:

```bash
sudo ufw deny 22/tcp
sudo ufw delete allow 22
```

(No lo hagas sin antes comprobar que ya te conectas bien por el nuevo puerto).[^3_8][^3_1]

### 3. Desde el cliente, conecta al nuevo puerto

```bash
ssh -p 2222 tu_usuario@ip_de_tu_estacion
```

Si still te da `Connection refused`, comprueba:

```bash
sudo ss -tuln | grep ':2222'
```

que muestre algo como `0.0.0.0:2222` o `*:2222`.[^3_6][^3_1]

***

Indícame qué distro usas (por ejemplo Fedora Bluefin, Ubuntu, etc.) y el puerto que elegiste (`2222`, `2223`, etc.), y te escribo los comandos exactos para tu caso.
<span style="display:none">[^3_10][^3_3][^3_5][^3_7][^3_9]</span>

<div align="center">⁂</div>

[^3_1]: https://contabo.com/blog/es/como-cambiar-el-puerto-ssh-en-linux/

[^3_2]: https://newsmatic.com.ar/seguridad/configurar-ssh-usar-puerto-no-estandar-selinux-aplicado

[^3_3]: https://www.youtube.com/watch?v=B5IYAJbA6WI

[^3_4]: https://www.compuhoy.com/como-configurar-ssh-para-usar-un-puerto-no-estandar-con-selinux-configurado-para-hacer-cumplir/

[^3_5]: https://www.youtube.com/watch?v=wTJvMxK2cBM

[^3_6]: https://www.netuy.net/como-modifico-el-puerto-estandar-de-acceso-ssh-en-mi-vps-linux/

[^3_7]: https://introserv.com/es/tutorials/changing-the-ssh-port/

[^3_8]: https://learn.microsoft.com/es-es/troubleshoot/developer/webapps/aspnetcore/practice-troubleshoot-linux/2-4-use-local-firewall-allowing-http-access

[^3_9]: https://docs.github.com/es/enterprise-server@3.20/admin/configuring-settings/configuring-network-settings/configuring-built-in-firewall-rules?learn=improve_security_of_your_instance\&learnProduct=admin

[^3_10]: https://learn.microsoft.com/es-es/troubleshoot/windows-server/system-management-components/troubleshoot-openssh-windows-firewall-port22


---

# dame algunas instrucciones para comprobar la configuracion del firewall y que puertos esta publicados

Para comprobar la configuración del firewall y qué puertos están “publicados” (escuchando o abiertos) en tu estación de trabajo, puedes usar varias herramientas de forma rápida y sencilla.

### 1. Ver estado del firewall

Depende de qué gestor usas:

- **Si usas `firewalld` (Fedora, RHEL, etc.)**:

```bash
sudo firewall-cmd --state
sudo firewall-cmd --get-default-zone
sudo firewall-cmd --get-active-zones
sudo firewall-cmd --list-all
```

Esto te muestra qué zonas están activas y qué servicios/puertos están permitidos.[^4_3][^4_9]
- **Si usas `ufw` (Ubuntu/Debian típico)**:

```bash
sudo ufw status verbose
sudo ufw status numbered
```

Muestra si está activo, y qué puertos/servicios están permitidos o denegados.[^4_8][^4_3]


### 2. Ver qué puertos están escuchando en tu máquina

Esto te dice qué puertos están abiertos y esperando conexiones:

- Con `ss` (recomendado):

```bash
sudo ss -tuln
```

    - `t`: TCP
    - `u`: UDP
    - `l`: solo puertos escuchando
    - `n`: muestra IPs y puertos como números

Busca líneas como `LISTEN 0.0.0.0:22` o `*:2222`.[^4_2][^4_5]
- Con `netstat` (si está instalado):

```bash
sudo netstat -tuln
```

Hace algo similar a `ss -tuln`.[^4_5][^4_7]
- Con `lsof` (para ver qué proceso usa cada puerto):

```bash
sudo lsof -iTCP -sTCP:LISTEN
```

Te muestra, por ejemplo, que `sshd` está escuchando en el puerto 2222.[^4_5][^4_8]


### 3. Comprobar si un puerto concreto está abierto (localmente)

- Ver si, por ejemplo, el 2222 está escuchando:

```bash
sudo ss -tuln | grep ':2222'
```

- O con `netcat` en modo servidor (temporal):

```bash
nc -l 2222
```

y desde otra terminal:

```bash
nc localhost 2222
```

Si se conecta, el puerto está accesible localmente.[^4_4][^4_2]


### 4. Comprobar desde otra máquina (comprobación externa)

Desde la máquina desde donde quieres conectarte:

- Ver si el puerto está abierto:

```bash
nc -vz ip_de_tu_estacion 2222
```

- O con `telnet`:

```bash
telnet ip_de_tu_estacion 2222
```

Si falla pero `ss` muestra el puerto, probablemente el firewall del servidor o el del router está bloqueando ese puerto.[^4_1][^4_4]

***

Si me dices qué distro usas (por ejemplo Fedora Bluefin, Ubuntu, etc.), te escribo una lista de comandos exactos “lista para copiar/pegar” para revisar todo el firewall y puertos SSH.
<span style="display:none">[^4_10][^4_6]</span>

<div align="center">⁂</div>

[^4_1]: https://www.testdevelocidad.es/test-de-puertos/

[^4_2]: https://www.redeszone.net/tutoriales/servidores/saber-puerto-abierto-cerrado-linux/

[^4_3]: https://www.youstable.com/es/blog/Cómo-monitorizar-firewalld-seguro-en-un-servidor-Linux

[^4_4]: https://www.redeszone.net/tutoriales/configuracion-puertos/comprobar-bloqueo-puertos-salida-firewall/

[^4_5]: https://ubunlog.com/como-comprobar-los-puertos-en-uso-en-linux/

[^4_6]: https://www.reddit.com/r/linuxquestions/comments/13ibsk5/question_about_firewall_on_linux_fedora_38/

[^4_7]: https://www.tokioschool.com/noticias/comprobar-puertos-abiertos/

[^4_8]: https://www.hoswedaje.com/web/ver-los-puertos-abiertos-en-ubuntu/

[^4_9]: https://fedoraproject.org/wiki/FirewallD/es

[^4_10]: https://www.reddit.com/r/networking/comments/1s0y1j/how_to_check_if_firewall_is_blocking_the_correct/


---

# no veo configuracio asociada

Cuando dices “no veo configuración asociada”, suele pasar que:

1. **El firewall no está activo**, o
2. **No hay reglas específicas para el puerto de SSH**, o
3. **SSH ni siquiera está escuchando en ese puerto todavía**.

Vamos a revisarlo paso a paso desde tu estación de trabajo.

***

### 1. Verifica que el firewall está activo

- **Si usas `firewalld` (típico en Fedora)**:

```bash
sudo firewall-cmd --state
```

Si responde `running`, está activo. Si no, arráncalo:

```bash
sudo systemctl start firewalld
sudo systemctl enable firewalld
```

- **Si usas `ufw` (Ubuntu/Debian)**:

```bash
sudo ufw status
```

Si dice `inactive`, actívalo:

```bash
sudo ufw enable
```


***

### 2. Mira qué puertos están escuchando (no hay “configuración asociada” si no hay nada escuchando)

Comprueba si SSH está escuchando en el puerto que elegiste (por ejemplo 2222):

```bash
sudo ss -tuln | grep ':2222'
```

- Si NO ves nada, el servicio SSH no está escuchando en ese puerto: revisa `/etc/ssh/sshd_config` y asegúrate de que:

```ini
Port 2222
```

esté definido y que luego hayas hecho:

```bash
sudo systemctl restart sshd
```

- Si sí ves algo como `LISTEN 0.0.0.0:2222`, entonces el servicio está OK y el problema es del firewall.

***

### 3. Ver qué reglas reales tiene el firewall

- **Con `firewalld`**:

```bash
sudo firewall-cmd --list-ports
sudo firewall-cmd --list-services
```

Si cambiaste el puerto de SSH a 2222, debe aparecer en `--list-ports`:

```bash
2222/tcp
```

Si no aparece, ábrelo:

```bash
sudo firewall-cmd --permanent --add-port=2222/tcp
sudo firewall-cmd --reload
```

- **Con `ufw`**:

```bash
sudo ufw status numbered
```

Debería aparecer algo como:

```text
2222/tcp ALLOW
```

Si no está, ábrelo:

```bash
sudo ufw allow 2222/tcp
```


***

### 4. Prueba desde otra máquina que el puerto está “publicado”

Desde la máquina desde donde quieres conectarte:

```bash
nc -vz ip_de_tu_estacion 2222
```

- Si te responde `Connection refused` pero `ss` muestra el puerto 2222, el firewall está bloqueando.
- Si se conecta correctamente, todo está bien configurado.

***

### 5. Si quieres, te ayudo línea a línea

Indícame:

- Distro exacta (por ejemplo `Fedora 40 Bluefin`, `Ubuntu 24.04`, etc.).
- El puerto que elegiste para SSH (por ejemplo `2222`).
- El resultado de:

```bash
sudo firewall-cmd --state
sudo firewall-cmd --list-all    # o sudo ufw status
sudo ss -tuln | grep ':2222'
```


Con eso, te monto una lista de comandos exacta para que ya quede “visiblemente asociado” el puerto en el firewall.
<span style="display:none">[^5_1][^5_10][^5_2][^5_3][^5_4][^5_5][^5_6][^5_7][^5_8][^5_9]</span>

<div align="center">⁂</div>

[^5_1]: https://www.tokioschool.com/noticias/comprobar-puertos-abiertos/

[^5_2]: https://www.ragic.com/intl/es/doc-kb/203/¿Cómo-saber-cuál-proceso-está-usando-qué-puerto%3F

[^5_3]: https://www.redeszone.net/tutoriales/configuracion-puertos/comprobar-bloqueo-puertos-salida-firewall/

[^5_4]: https://ubunlog.com/como-comprobar-los-puertos-en-uso-en-linux/

[^5_5]: https://macaddresslookup.io/es/port-checker

[^5_6]: https://alexhost.com/es/faq/comprobacion-de-puertos-abiertos-y-en-escucha-en-linux-mediante-netstat-y-ss/

[^5_7]: https://www.reddit.com/r/linuxquestions/comments/1gi7soy/how_to_tell_if_you_have_open_ports_firewall_basics/

[^5_8]: https://alexhost.com/es/faq/checking-open-and-listening-ports-in-linux-using-netstat-and-ss/

[^5_9]: https://www.ionos.com/es-us/digitalguide/servidores/seguridad/puertos-abiertos/

[^5_10]: https://labex.io/es/tutorials/linux-how-to-check-if-a-port-is-open-in-linux-558746


---

# FedoraWorkstation (default, active)

target: default
ingress-priority: 0
egress-priority: 0
icmp-block-inversion: no
interfaces: eno1
sources:
services: dhcpv6-client samba samba-client ssh
ports: 1025-65535/udp 1025-65535/tcp
protocols:
forward: yes
masquerade: no
forward-ports:
source-ports:
icmp-blocks:
rich rules:

Tu configuración de `firewalld` está bien, pero el problema está en que **sigues usando el servicio `ssh`** (que está asociado por defecto al puerto 22), y el nuevo puerto que elegiste (por ejemplo 2222) **no está declarado explícitamente en el firewall** aunque el rango `1025‑65535/tcp/udp` esté abierto.[^6_5][^6_7]

### 1. Entendiendo lo que ves

En tu salida:

- `services: dhcpv6-client samba samba-client ssh`
→ `ssh` = puerto 22 (definido por el servicio `ssh` de firewalld).[^6_5]
- `ports: 1025-65535/udp 1025-65535/tcp`
→ En teoría permite ese rango completo, pero algunos firewalls pueden tener políticas por defecto que aún bloqueen el acceso si no está explícitamente asociado.[^6_7]


### 2. Opción recomendada: abrir tu puerto SSH explícitamente

Por ejemplo, si usas el puerto `2222`:

```bash
sudo firewall-cmd --permanent --add-port=2222/tcp
sudo firewall-cmd --reload
```

Luego verifica:

```bash
sudo firewall-cmd --list-ports
```

Debería aparecer:

```text
1025-65535/udp 1025-65535/tcp 2222/tcp
```


### 3. (Opcional) Ajustar el servicio `ssh` o usar un servicio personalizado

Si quieres que el servicio `ssh` apunte a tu puerto (más “limpio”), puedes definir un servicio personalizado `my-ssh` que use tu puerto y luego usar:

```bash
sudo firewall-cmd --permanent --add-service=my-ssh
sudo firewall-cmd --reload
```

Pero para tu caso, con Fedora Workstation, normalmente basta con añadir el puerto explícito con `--add-port`.[^6_3][^6_10]

### 4. Verifica que SSH escucha en ese puerto

Asegúrate de que:

1. En `/etc/ssh/sshd_config` tienes:

```ini
Port 2222
```

2. Reinicias el servicio:

```bash
sudo systemctl restart sshd
```

3. Compruebas que escucha:

```bash
sudo ss -tuln | grep ':2222'
```


### 5. Prueba desde otra máquina

```bash
ssh -p 2222 tu_usuario@tu_ip
```

Si ahora no ves `Connection refused`, el firewall ya está bien configurado para ese puerto.[^6_9][^6_7]

***

Si me dices **qué puerto concreto** estás usando (por ejemplo 2222, 2223, etc.), te escribo los comandos exactos ya reemplazados para que copies/pegues directamente.
<span style="display:none">[^6_1][^6_2][^6_4][^6_6][^6_8]</span>

<div align="center">⁂</div>

[^6_1]: https://h4ckseed.wordpress.com/2020/11/12/guia-basica-de-firewalld/

[^6_2]: https://tuxcare.com/es/blog/centos-7-firewall/

[^6_3]: https://discussion.fedoraproject.org/t/how-to-open-a-tcp-port-using-firewall-config/73818

[^6_4]: https://www.youstable.com/es/blog/crear-firewalld-en-linux

[^6_5]: https://fedoramagazine.org/control-the-firewall-at-the-command-line/

[^6_6]: https://toquecanela.blogspot.com/2019/10/abrir-puerto-en-firewalld.html

[^6_7]: https://asamalik.fedorapeople.org/tmp-docs-preview/quick-docs/firewalld/

[^6_8]: https://alexhost.com/es/faq/introduccion-a-firewalld/

[^6_9]: https://fedoraproject.org/wiki/FirewallD/es

[^6_10]: https://blog.alcancelibre.org/staticpages/index.php/como-firewalld


---

# ❯ sudo firewall-cmd --permanent --add-port=2264/tcp

Warning: ALREADY_ENABLED: 2264:tcp
success

Perfecto, el mensaje:

```text
Warning: ALREADY_ENABLED: 2264:tcp
success
```

significa que **el puerto 2264/tcp ya estaba agregado en la configuración permanente de firewalld**, y no se ha vuelto a añadir porque ya existe.[^7_3][^7_6]
Es solo una advertencia, no un error.

### 1. Verifica que el puerto 2264 está realmente abierto

Ejecuta:

```bash
sudo firewall-cmd --list-ports
```

Debe aparecer algo como:

```text
1025-65535/udp 1025-65535/tcp 2264/tcp
```

Si no aparece, puedes forzar la recarga:

```bash
sudo firewall-cmd --reload
sudo firewall-cmd --list-ports
```


### 2. Confirma que SSH escucha en 2264

En tu máquina:

```bash
sudo ss -tuln | grep ':2264'
```

Debe mostrar algo como:

```text
tcp  LISTEN 0.0.0.0:2264  *:*
```

Si no aparece, revisa `/etc/ssh/sshd_config` y asegúrate de tener:

```ini
Port 2264
```

y luego:

```bash
sudo systemctl restart sshd
```


### 3. Prueba la conexión desde otro equipo

```bash
ssh -p 2264 usuario@tu_ip
```

Si ahora no ves `Connection refused`, el firewall está bien configurado para el puerto 2264.[^7_2][^7_4]

***

Si quieres, pega el resultado de:

```bash
sudo firewall-cmd --list-ports
sudo ss -tuln | grep ':2264'
```

y te digo exactamente si hay algo que cambiar.
<span style="display:none">[^7_1][^7_10][^7_5][^7_7][^7_8][^7_9]</span>

<div align="center">⁂</div>

[^7_1]: https://www.youtube.com/watch?v=ac42WGM3yHY

[^7_2]: https://www.redeszone.net/tutoriales/servidores/saber-puerto-abierto-cerrado-linux/

[^7_3]: https://extassisnetwork.com/tutoriales/configurar-un-firewall-usando-firewalld-centos7/

[^7_4]: https://www.hostingtg.com/blog/comprobar-puertos-abiertos/

[^7_5]: https://docs.redhat.com/es/documentation/red_hat_enterprise_linux/8/html/managing_systems_using_the_rhel_8_web_console/allowing-a-new-port-on-a-firewall_configuring-the-web-console-listening-port

[^7_6]: https://www.baeldung.com/linux/list-open-ports-firewall-cmd

[^7_7]: https://blog.alcancelibre.org/staticpages/index.php/como-firewalld

[^7_8]: https://docs.redhat.com/es/documentation/red_hat_enterprise_linux/8/html/securing_networks/viewing-firewalld-settings-using-cli_viewing-the-current-status-and-settings-of-firewalld

[^7_9]: https://www.solvetic.com/tutoriales/article/6414-como-instalar-y-configurar-firewalld-en-centos-y-ubuntu/

[^7_10]: https://www.reddit.com/r/linuxquestions/comments/1gi7soy/how_to_tell_if_you_have_open_ports_firewall_basics/


---

# sudo firewall-cmd --list-ports

1025-65535/tcp 1025-65535/udp

Tu salida de:

```bash
sudo firewall-cmd --list-ports
```

```text
1025-65535/tcp 1025-65535/udp
```

significa que **firewalld permite todo el rango 1025‑65535 en TCP y UDP**, pero **no muestra el puerto 2264 de forma explícita** porque fue añadido como parte de ese rango, no como un puerto individual.
El mensaje `ALREADY_ENABLED` solo indica que ya está contemplado; no hay error.[^8_1][^8_5]

### 1. Lo que necesitas comprobar ahora

1. **Que SSH escuche en el puerto 2264**:

```bash
sudo ss -tuln | grep ':2264'
```

Debe mostrar algo así:

```text
tcp  LISTEN 0.0.0.0:2264  *:*
```

Si no aparece, revisa `/etc/ssh/sshd_config`:

```ini
Port 2264
```

y luego:

```bash
sudo systemctl restart sshd
```

2. **Desde otra máquina, prueba el puerto 2264**:

```bash
nc -vz ip_de_tu_estacion 2264
# o
ssh -p 2264 tu_usuario@ip_de_tu_estacion
```

    - Si falla, pero el servicio SSH está escuchando, el problema está en el firewall del router/red o en que el rango 1025‑65535 no se está aplicando correctamente.
    - Si funciona, el firewall está bien y el puerto 2264 **sí está “publicado”**, aunque no se vea listado individualmente.

### 2. Si quieres que el 2264 aparezca explícito

Aunque no es obligatorio, puedes hacer que el puerto 2264 aparezca explícitamente en `--list-ports` quitando el rango y agregando solo el puerto concreto:

```bash
sudo firewall-cmd --permanent --remove-port=1025-65535/tcp
sudo firewall-cmd --permanent --remove-port=1025-65535/udp
sudo firewall-cmd --permanent --add-port=2264/tcp
sudo firewall-cmd --reload
sudo firewall-cmd --list-ports
```

Ahora verás:

```text
2264/tcp
```

y así sí verás “configuración asociada” al puerto 2264.[^8_5][^8_10]

Si quieres, pega el resultado de `sudo ss -tuln | grep ':2264'` y te digo si el problema es SSH o el firewall.
<span style="display:none">[^8_2][^8_3][^8_4][^8_6][^8_7][^8_8][^8_9]</span>

<div align="center">⁂</div>

[^8_1]: https://www.hostingtg.com/blog/comprobar-puertos-abiertos/

[^8_2]: https://arhivo.com/es/chapters/4026-how-to-list-open-ports-in-firewalld--linux-hint

[^8_3]: https://www.reddit.com/r/linuxadmin/comments/ub7iow/firewallcmd_firewalld_issues_for_port_forwarding/

[^8_4]: https://www.redeszone.net/tutoriales/servidores/saber-puerto-abierto-cerrado-linux/

[^8_5]: https://cubepath.com/en/docs/configuración-del-servidor/configuracion-de-firewall-con-firewalld-centos-rocky

[^8_6]: https://hostman.com/es/tutorials/como-comprobar-si-un-puerto-esta-abierto-en-linux/

[^8_7]: https://puerto53.wordpress.com/2021/08/07/configuracion-firewalld-linux/

[^8_8]: https://docs.redhat.com/es/documentation/red_hat_enterprise_linux/8/html/securing_networks/viewing-firewalld-settings-using-cli_viewing-the-current-status-and-settings-of-firewalld

[^8_9]: https://forum.endeavouros.com/t/how-to-open-firewalld-ports-correctly/74814

[^8_10]: https://www.baeldung.com/linux/list-open-ports-firewall-cmd


---

# sudo firewall-cmd --zone=FedoraWorkstation --list-all

Con este comando:

```bash
sudo firewall-cmd --zone=FedoraWorkstation --list-all
```

vas a ver toda la configuración de la zona `FedoraWorkstation` aplicada a tu interfaz activa, incluyendo **servicios permitidos, puertos, interfaces, reglas rich, etc.**[^9_4][^9_5]

### 1. Qué te va a mostrar típicamente

La salida suele ser algo parecido a esto en Fedora WorkStation:

```text
FedoraWorkstation (active)
  target: default
  icmp-block-inversion: no
  interfaces: eno1
  sources:
  services: dhcpv6-client samba samba-client ssh
  ports: 1025-65535/udp 1025-65535/tcp
  protocols:
  masquerade: no
  forward-ports:
  source-ports:
  icmp-blocks:
  rich rules:
```

- `services: dhcpv6-client samba samba-client ssh` → permite los servicios estándar definidos en firewalld para esos nombres.
- `ports: 1025-65535/udp 1025-65535/tcp` → permite todos los puertos en ese rango, pero no muestra el puerto 2264 explícitamente, aunque sí esté cubierto por el rango.[^9_8][^9_4]


### 2. Cómo usar eso para tu SSH en 2264

- Si tu SSH está en el puerto `2264` y quieres verlo “asociado” en la zona:

```bash
sudo firewall-cmd --permanent --zone=FedoraWorkstation --add-port=2264/tcp
sudo firewall-cmd --reload
sudo firewall-cmd --zone=FedoraWorkstation --list-all
```

Ahora, en la sección `ports`, verás `2264/tcp` además del rango, o bien solo el puerto concreto si quitaras el rango.


### 3. Si quieres que solo el 2264 quede abierto (recomendado)

```bash
sudo firewall-cmd --permanent --zone=FedoraWorkstation --remove-port=1025-65535/tcp
sudo firewall-cmd --permanent --zone=FedoraWorkstation --remove-port=1025-65535/udp
sudo firewall-cmd --permanent --zone=FedoraWorkstation --add-port=2264/tcp
sudo firewall-cmd --reload
sudo firewall-cmd --zone=FedoraWorkstation --list-all
```

Con esto, en `ports` verás claramente `2264/tcp` y tendrás una configuración más limpia y segura.[^9_7][^9_4]

Pega aquí la salida completa de ese `--list-all` y te digo si SSH está bien cubierto para el puerto que usas.
<span style="display:none">[^9_1][^9_2][^9_3][^9_6][^9_9]</span>

<div align="center">⁂</div>

[^9_1]: https://discussion.fedoraproject.org/t/firewall-cmd-how-to-list-all-the-blocked-ip-services-and-ports/126228

[^9_2]: https://www.redhat.com/en/blog/beginners-guide-firewalld

[^9_3]: https://www.baeldung.com/linux/list-open-ports-firewall-cmd

[^9_4]: https://fedoramagazine.org/how-to-manage-network-services-with-firewall-cmd/

[^9_5]: https://docs.fedoraproject.org/en-US/quick-docs/firewalld/

[^9_6]: https://kodekloud.com/community/t/linux-firewalld-setup-firewall-cmd-list-all/11228

[^9_7]: https://www.krypt.com/knowledgebase/47/How-to-allow-firewall-RedHatorCentOSorFedora-using-firewalld.html

[^9_8]: https://alexhost.com/es/faq/configure-a-firewall-with-firewalld/

[^9_9]: https://github.com/firewalld/firewalld/issues/139


---

# quiero remover ssh

Para “remover SSH” de firewalld en tu zona `FedoraWorkstation` (es decir, cerrar el servicio que corresponde al puerto 22), usa:

```bash
sudo firewall-cmd --permanent --zone=FedoraWorkstation --remove-service=ssh
sudo firewall-cmd --reload
```

Luego confirma que ya no aparece `ssh` en los servicios:

```bash
sudo firewall-cmd --zone=FedoraWorkstation --list-all
```

En tu caso, como ya estás usando el puerto 2264 (u otro) y lo tienes abierto explícitamente por puerto (`2264/tcp`), eliminar el servicio `ssh` **no te bloquea el acceso por el puerto nuevo**, solo cierra el puerto 22 y aclara la configuración.[^10_1][^10_4]

Si quieres que yo te recomiende una configuración completa limpia (solo tu puerto personalizado SSH, sin el rango 1025‑65535), dime qué puerto estás usando exactamente (por ejemplo 2264).
<span style="display:none">[^10_2][^10_3][^10_5][^10_6][^10_7][^10_8][^10_9]</span>

<div align="center">⁂</div>

[^10_1]: https://discuss.freedombox.org/t/secuirty-should-ssh-service-in-firewallds-external-zone-be-disabled-by-default/857

[^10_2]: https://wpguru.co.uk/2015/11/how-to-disable-ssh-access-from-everywhere-except-for-certain-ips-in-centos-7/

[^10_3]: https://www.reddit.com/r/linuxquestions/comments/108mwln/blocking_ssh_and_samba_via_firewalld_firewallcmd/

[^10_4]: https://cubepath.com/docs/configuración-del-servidor/como-cambiar-el-puerto-ssh-por-defecto

[^10_5]: https://apuntes.ucr.ac.cr/index.php/Limitar_acceso_por_SSH_%C3%BAnicamente_a_ciertas_IP_con_FirewallD

[^10_6]: https://caminosdigitales.es/desactivar-firewall-cmd-y-activar-iptables-en-centos-7/

[^10_7]: https://www.youtube.com/watch?v=B5IYAJbA6WI

[^10_8]: https://www.vivaolinux.com.br/topico/CentOS/Como-liberar-ssh-para-determinado-IP-no-firewall-cmd

[^10_9]: https://www.youtube.com/watch?v=wTJvMxK2cBM


---

# aun aparece este mensaje desde otro equipo:  nc -vz aurora 2264

nc: connect to aurora (192.168.1.200) port 2264 (tcp) failed: Connection refused

El mensaje:

```text
nc: connect to aurora (192.168.1.200) port 2264 (tcp) failed: Connection refused
```

significa que **o el servidor no está escuchando en el puerto 2264, o está escuchando pero no permite conexiones entrantes** (por ejemplo, no está en la IP correcta, o una regla/filtro intermedio lo bloquea).[^11_1][^11_2]

Vamos a comprobarlo paso a paso en tu máquina `aurora`.

***

### 1. Verifica que SSH escucha en el puerto 2264

En `aurora`:

```bash
sudo ss -tuln | grep ':2264'
```

- Si **no ves ninguna línea** en `2264/tcp`, entonces SSH no está escuchando.
- Si ves algo como:

```text
tcp  LISTEN 0.0.0.0:2264  *:*
```

entonces SSH sí está escuchando en el puerto 2264.

***

### 2. Confirma el puerto en `sshd_config` y reinicia el servicio

Revisa `/etc/ssh/sshd_config`:

```bash
sudo grep -E "^(Port|ListenAddress)" /etc/ssh/sshd_config
```

Deberías ver:

```ini
Port 2264
```

Si no está, añádelo (quitando cualquier otra línea `Port 22`), luego:

```bash
sudo systemctl restart sshd
sudo ss -tuln | grep ':2264'
```


***

### 3. Comprueba el firewall en `aurora`

En `aurora`:

```bash
sudo firewall-cmd --zone=FedoraWorkstation --list-all
```

Busca en `ports` que aparezca `2264/tcp` (no solo `1025-65535/tcp`).

Si no aparece explícitamente, añádelo:

```bash
sudo firewall-cmd --permanent --zone=FedoraWorkstation --add-port=2264/tcp
sudo firewall-cmd --reload
```


***

### 4. Prueba desde el mismo equipo (local)

En `aurora`:

```bash
nc -vz localhost 2264
```

- Si se conecta, SSH está OK internamente; el problema está en el acceso desde la red (interface, NAT, firewall externo, etc.).
- Si también falla, el servicio SSH aún no está funcionando bien.[^11_1]

***

### 5. Desde otro equipo, prueba bien el comando

En otro equipo:

```bash
nc -vz 192.168.1.200 2264
```

Si aún da `Connection refused` y el `ss` en `aurora` sí muestra `2264` escuchando (y `firewall-cmd` lo permite), revisa:

- Que el cliente esté en la misma red.
- Que no haya otro firewall en el router o switch bloqueando `2264/tcp`.
- Que el nombre `aurora` resuelva a `192.168.1.200` (haz `ping aurora` y `host aurora` o `getent hosts aurora`).

***

Si quieres que te dibuje el camino exacto, haz en `aurora` esto y pega el resultado aquí:

```bash
sudo systemctl status sshd | head -10
sudo ss -tuln | grep '2264'
sudo firewall-cmd --zone=FedoraWorkstation --list-all
```

Con eso te digo exactamente qué comando falta o qué está mal configurado.
<span style="display:none">[^11_10][^11_3][^11_4][^11_5][^11_6][^11_7][^11_8][^11_9]</span>

<div align="center">⁂</div>

[^11_1]: https://learn.microsoft.com/es-es/troubleshoot/windows-server/system-management-components/troubleshoot-openssh-windows-firewall-port22

[^11_2]: https://labex.io/es/tutorials/linux-how-to-check-if-an-ssh-server-is-running-in-linux-558785

[^11_3]: https://www.ibm.com/docs/es/software-hub/5.1.x?topic=services-firewall-issues-database-ports

[^11_4]: https://www.dell.com/support/kbdoc/es-ve/000158270/powerscale-cómo-configurar-ssh-para-escuchar-en-varios-puertos

[^11_5]: https://help.wnpower.com/hc/es/articles/360036342231-Cómo-abrir-y-cerrar-puertos-en-el-firewall-ufw-en-Ubuntu

[^11_6]: https://docs.github.com/es/authentication/connecting-to-github-with-ssh/testing-your-ssh-connection

[^11_7]: https://www.reddit.com/r/googlecloud/comments/174n683/the_port_is_showing_closed_despite_setting_the/

[^11_8]: https://www.reddit.com/r/commandline/comments/vqfplc/how_can_i_check_if_my_network_permits_ssh_from/

[^11_9]: https://www.redeszone.net/tutoriales/configuracion-puertos/ver-puertos-programas-bloquea-firewall-windows/

[^11_10]: https://www.redeszone.net/tutoriales/servidores/servidor-openssh-linux-configuracion-maxima-seguridad/


---

# no hay salida

“No hay salida” cuando haces `sudo ss -tuln | grep ':2264'` significa que **SSH no está escuchando en el puerto 2264**, y eso es justo lo que provoca el `Connection refused` desde el otro equipo. [^12_3][^12_5]

### 1. Paso 1: asegurar que el servicio SSH está activo

En `aurora`:

```bash
sudo systemctl status sshd
```

Debe decir `active (running)`. Si no, arrancalo:

```bash
sudo systemctl enable --now sshd
```


### 2. Paso 2: configurar el puerto 2264 en `sshd_config`

Edita el archivo:

```bash
sudo nano /etc/ssh/sshd_config
```

Asegúrate de tener:

```ini
Port 2264
```

- Comenta o elimina cualquier otra línea `Port 22` (si la hay).
- Guarda el archivo y prueba la sintaxis:

```bash
sudo sshd -t
```

Si no devuelve errores, reinicia el servicio:

```bash
sudo systemctl restart sshd
```


### 3. Paso 3: ver si ahora sí escucha en 2264

```bash
sudo ss -tuln | grep ':2264'
```

Ahora **debe aparecer una línea** como:

```text
tcp  LISTEN 0.0.0.0:2264  *:*
```

Si aún no sale nada, vuelve a revisar:

- Que el `Port 2264` esté bien escrito.
- Que `sudo systemctl status sshd` no muestre errores.


### 4. Paso 4: mantener el firewall correcto

Ya tienes abierto el rango amplio, pero para que sea claro:

```bash
sudo firewall-cmd --permanent --zone=FedoraWorkstation --add-port=2264/tcp
sudo firewall-cmd --reload
```

(No es el motivo de `Connection refused` si el puerto ni siquiera aparece en `ss`, pero sí lo necesitas después de que el servicio escuche).[^12_9]

***

Haz ahora esto en `aurora` y pega el resultado:

```bash
sudo systemctl status sshd | head -10
sudo grep -E "^(Port|ListenAddress)" /etc/ssh/sshd_config
sudo ss -tuln | grep 2264
```

Con eso te digo línea por línea qué corregir o si el problema está en otra parte (por ejemplo, interfaz o firewall más arriba).
<span style="display:none">[^12_1][^12_10][^12_2][^12_4][^12_6][^12_7][^12_8]</span>

<div align="center">⁂</div>

[^12_1]: https://www.dell.com/support/kbdoc/es-do/000158270/powerscale-cómo-configurar-ssh-para-escuchar-en-varios-puertos

[^12_2]: https://tildavps.com/blog/es/mejorando-la-seguridad-como-cambiar-el-puerto-ssh

[^12_3]: https://learn.microsoft.com/es-es/troubleshoot/windows-server/system-management-components/troubleshoot-openssh-windows-firewall-port22

[^12_4]: https://raiolanetworks.com/blog/cambiar-puerto-ssh-en-linux-centos-debian-ubuntu/

[^12_5]: https://blog.desdelinux.net/configurar-ssh-por-otro-puerto-y-no-por-el-22/

[^12_6]: https://www.devart.com/dbforge/edge/install-ssh-server.html

[^12_7]: https://eltallerdelbit.com/solucionar-problemas-con-ssh/

[^12_8]: https://www.ionos.es/ayuda/infraestructuras-servidores-cloud/primeros-pasos/informacion-importante-sobre-la-seguridad-de-su-servidor/cambiar-el-puerto-ssh-predeterminado/

[^12_9]: https://www.k2webhost.com/blog/blog/solucion-al-error-conexion-rechazada-en-conexiones-ssh/

[^12_10]: https://www.youtube.com/watch?v=wTJvMxK2cBM


---

# sudo systemctl status sshd

● sshd.service - OpenSSH server daemon
Loaded: loaded (/usr/lib/systemd/system/sshd.service; disabled; preset: disabled)
Drop-In: /usr/lib/systemd/system/service.d
└─10-timeout-abort.conf
Active: activating (auto-restart) (Result: exit-code) since Tue 2026-04-14 12:25:44 -04; 1s ago
Invocation: 689ee48ecc70444e9a65eb9482a4110a
Docs: man:sshd(8)
man:sshd_config(5)
Process: 36381 ExecStart=/usr/sbin/sshd -D \$OPTIONS (code=exited, status=255/EXCEPTION)
Main PID: 36381 (code=exited, status=255/EXCEPTION)
Mem peak: 1.8M
CPU: 23ms

El resultado de `sudo systemctl status sshd` muestra que el servicio SSH **no está arrancando**; está en `activating (auto-restart)` y `exit-code 255/EXCEPTION`, lo que suele pasar por un **error de configuración en `/etc/ssh/sshd_config`** o por un puerto conflictivo.[^13_1][^13_9]

### 1. Revisa los logs de `sshd` para ver el error exacto

En `aurora` ejecuta:

```bash
sudo journalctl -u sshd -n 50 --no-pager
```

Busca una línea que diga algo como:

- `error: config line ...` (error de sintaxis en el archivo de configuración).
- `Address already in use` (el puerto 2264 está ocupado por otra aplicación).

Eso te dirá qué rompe el arranque de `sshd`.

***

### 2. Verifica que el `Port 2264` está bien definido

Muestra todo el bloque relevante de la configuración:

```bash
sudo grep -E "^(Port|ListenAddress|Include|PidFile)" /etc/ssh/sshd_config
```

- Debe aparecer solo una línea `Port 2264` (no varias `Port ...` ni `Port 22` coexistiendo).
- Si hay varias líneas `Port`, **deja solo una**:

```ini
Port 2264
```


Guarda el archivo, valida sintaxis:

```bash
sudo sshd -t
```

- Si `sshd -t` no devuelve errores, reinicia:

```bash
sudo systemctl restart sshd
sudo systemctl status sshd
```

- Si `sshd -t` muestra un error (por ejemplo `Bad port number` o `directive`), copia ese mensaje y lo corregimos línea por línea.

***

### 3. Comprueba que el puerto 2264 no está ocupado

En `aurora`:

```bash
sudo ss -tuln | grep ':2264'
```

Si ves otra aplicación escuchando en `2264`, cambia el puerto en `sshd_config` a, por ejemplo, `2266` o `2265`, y luego:

```bash
sudo systemctl restart sshd
sudo ss -tuln | grep 226
```


***

### 4. Si tu distro está usando otro nombre de servicio

En Fedora WorkStation, el servicio suele llamarse `sshd`, pero en algunos entornos puede ser `ssh`:

```bash
sudo systemctl status ssh
```

Si ese sí está activo, entonces el servicio correcto es `ssh`, y el conflicto puede venir de que estás editando `sshd.service` sin que el sistema lo use realmente.

***

### Resumen de lo que quiero que pegues aquí

En `aurora` ejecuta y pega esto:

```bash
sudo journalctl -u sshd -n 30 --no-pager | grep -i error
sudo grep -E "^(Port|ListenAddress|Include|PidFile)" /etc/ssh/sshd_config
sudo ss -tuln | grep 226
```

Con eso, te indico exactamente qué línea de `sshd_config` corregir o qué puerto alternativo usar para que el servicio arranque y dejen de salir `Connection refused` al hacer `nc -vz aurora 2264`.
<span style="display:none">[^13_10][^13_2][^13_3][^13_4][^13_5][^13_6][^13_7][^13_8]</span>

<div align="center">⁂</div>

[^13_1]: https://forums.rockylinux.org/t/cannot-start-sshd/7727

[^13_2]: https://www.alibabacloud.com/help/en/ecs/the-ssh-service-cannot-be-started-due-to-port-occupation

[^13_3]: https://access.redhat.com/solutions/7011614

[^13_4]: https://www.alibabacloud.com/en/knowledge/tr/ecs-fault/failed-to-start-openssh-server-daemon?_p_lc=1

[^13_5]: https://www.alibabacloud.com/help/en/ecs/main-process-exited-code-exited-error-when-ssh-service-is-started

[^13_6]: https://bbs.archlinux.org/viewtopic.php?id=227787

[^13_7]: https://bbs.archlinux.org/viewtopic.php?id=309302

[^13_8]: https://discourse.ubuntu.com/t/systemd-shows-all-services-as-dead/52660

[^13_9]: https://access.redhat.com/solutions/7049614

[^13_10]: https://access.redhat.com/solutions/7018688


---

# error: Bind to port 2264 on 0.0.0.0 failed: Permission denied.

abr 14 12:25:44 aurora sshd[36381]: fatal: Cannot bind any address.
abr 14 12:25:44 aurora systemd[1]: sshd.service: Main process exited, code=exited, status=255/EXCEPTION
abr 14 12:25:44 aurora systemd[1]: sshd.service: Failed with result 'exit-code'.
abr 14 12:25:44 aurora systemd[1]: Failed to start sshd.service - OpenSSH server daemon.

El mensaje clave es:

```text
error: Bind to port 2264 on 0.0.0.0 failed: Permission denied.
fatal: Cannot bind any address.
```

Esto significa que **`sshd` no puede atar (bind) el puerto 2264**, normalmente por uno de estos motivos:

- El puerto 2264 está siendo bloqueado por SELinux.
- El puerto está restringido por el sistema (por debajo de 1024, no es tu caso).
- Algo externo impide el acceso al puerto (por ejemplo, una política de seguridad / contenedor).

Como usas Fedora, lo más probable es que sea **SELinux**, que por defecto solo permite que SSH escuche en su puerto tradicional (22) y no en puertos arbitrarios como 2264.

***

### 1. Solución rápida (recomendada): usar un puerto regular + SELinux

#### 1.1 Cambiar a un puerto “normal” (por ejemplo 2222 o 2266)

Edita `/etc/ssh/sshd_config`:

```bash
sudo nano /etc/ssh/sshd_config
```

Pon, por ejemplo:

```ini
Port 2222
```

Borra cualquier otra línea `Port 2264` y guarda el archivo.

Valida la sintaxis:

```bash
sudo sshd -t
```

Si no hay errores, reinicia:

```bash
sudo systemctl restart sshd
sudo systemctl status sshd
```

Ahora debe arrancar sin errores.

Comprueba:

```bash
sudo ss -tuln | grep 2222
```

Debe aparecer:

```text
tcp  LISTEN 0.0.0.0:2222  *:*
```

Y desde el otro equipo:

```bash
nc -vz aurora 2222
ssh -p 2222 tu_usuario@aurora
```


***

### 2. Opción avanzada: permitir el puerto 2264 en SELinux

Si de verdad quieres que el SSH escuche en `2264`, debes indicarle a SELinux que ese puerto es válido para SSH:

```bash
# Si no tienes `semanage`, instálalo (Fedora):
sudo dnf install -y policycoreutils-python-utils

# Añadir el puerto 2264 para SSH
sudo semanage port -a -t ssh_port_t -p tcp 2264

# Ver puertos permitidos para SSH
sudo semanage port -l | grep ssh
```

Ahora vuelve a dejar `Port 2264` en `sshd_config`, y reinicia:

```bash
sudo systemctl restart sshd
sudo systemctl status sshd
```

Si ves que el servicio arranca y `ss -tuln | grep 2264` muestra el puerto, todo está bien.

***

### 3. Si quieres conservar el firewall simple

Con Firewalld:

```bash
# Si antes tenías 1025‑65535 y quieres solo el puerto concreto:
sudo firewall-cmd --permanent --zone=FedoraWorkstation --remove-port=1025-65535/tcp
sudo firewall-cmd --permanent --zone=FedoraWorkstation --remove-port=1025-65535/udp
sudo firewall-cmd --permanent --zone=FedoraWorkstation --add-port=2222/tcp      # o 2264

sudo firewall-cmd --reload
sudo firewall-cmd --zone=FedoraWorkstation --list-all
```


***

### Recomendación práctica

Para tu caso, te propongo:

1. Cambiar el puerto a `2222` en `sshd_config`.
2. No tocar SELinux (por ahora).
3. Abrir `2222/tcp` en `firewalld`.

Con eso deberías dejar de ver `Connection refused` y `Permission denied`.

Si quieres, dime:

- ¿Quieres usar `2222` o otro puerto?
- ¿Quieres que te escriba todos los comandos en bloque para que solo los copies/pegues?
<span style="display:none">[^14_1][^14_10][^14_2][^14_3][^14_4][^14_5][^14_6][^14_7][^14_8][^14_9]</span>

<div align="center">⁂</div>

[^14_1]: https://lists.debian.org/debian-user-spanish/2013/09/msg00477.html

[^14_2]: https://www.geeksforgeeks.org/linux-unix/how-to-fix-ssh-failed-permission-denied/

[^14_3]: https://docs.github.com/es/authentication/troubleshooting-ssh/error-permission-denied-publickey

[^14_4]: https://blog.matrixpost.net/changing-ssh-port-on-selinux-error-bind-to-port-failed-permission-denied/

[^14_5]: https://www.youtube.com/watch?v=uB3HR9qbCzI

[^14_6]: https://forum.gitea.com/t/failed-to-start-ssh-server-listen-tcp-bind-permission-denied/1201

[^14_7]: https://soporte.hostgator.mx/hc/es-419/articles/44275774391059-Cómo-solucionar-errores-de-acceso-SSH

[^14_8]: https://www.reddit.com/r/homeassistant/comments/1hlfxtw/advanced_ssh_web_terminal_and_port_22_access/

[^14_9]: https://www.reddit.com/r/linuxquestions/comments/6l7cpg/ssh_permission_denied_why/

[^14_10]: https://www.siteground.es/kb/error-connection-refused-ssh/


---

# vi esto en el achivo: semanage port -a -t ssh_port_t -p tcp \#PORTNUMBER

Perfecto, lo que viste en el archivo:

```text
semanage port -a -t ssh_port_t -p tcp #PORTNUMBER
```

es justo la **plantilla de ejemplo** que te dice cómo permitir que SELinux deje que SSH escuche en un puerto distinto del 22.

Es decir, el comando correcto para tu puerto (por ejemplo 2222 o 2264) es:

```bash
sudo semanage port -a -t ssh_port_t -p tcp 2222
```

o si de verdad quieres el 2264:

```bash
sudo semanage port -a -t ssh_port_t -p tcp 2264
```

Antes, asegúrate de que `semanage` esté instalado (en Fedora):

```bash
sudo dnf install -y policycoreutils-python-utils
```


### 1. Flujo completo recomendado (por ejemplo con puerto 2222)

1. Instalar `semanage` si hace falta:

```bash
sudo dnf install -y policycoreutils-python-utils
```

2. Permitir el puerto en SELinux:

```bash
sudo semanage port -a -t ssh_port_t -p tcp 2222
```

3. Ver que el puerto está asociado a SSH:

```bash
sudo semanage port -l | grep ssh
```

Debería aparecer algo como:

```text
ssh_port_t                    tcp      2222, 22
```

4. Configurar `sshd` para usar ese puerto:

```bash
sudo nano /etc/ssh/sshd_config
```

Asegúrate de tener:

```ini
Port 2222
```

(sin `Port 22` duplicado ni mal escrito).
Guarda el archivo y valida:

```bash
sudo sshd -t
```

5. Arrancar/reiniciar el servicio:

```bash
sudo systemctl restart sshd
sudo systemctl status sshd
```

6. Verificar que escucha:

```bash
sudo ss -tuln | grep 2222
```

7. Abrir el puerto en firewalld:

```bash
sudo firewall-cmd --permanent --zone=FedoraWorkstation --add-port=2222/tcp
sudo firewall-cmd --reload
```

8. Desde el otro equipo:

```bash
ssh -p 2222 tu_usuario@aurora
```


Si en cambio quieres que el SSH corra en `2264`, solo cambia el número `2222` por `2264` en los comandos anteriores.
Con esto, SELinux ya no dirá `Permission denied` al bind del puerto.[^15_1][^15_3]
<span style="display:none">[^15_2][^15_4][^15_5][^15_6][^15_7][^15_8]</span>

<div align="center">⁂</div>

[^15_1]: https://oneuptime.com/blog/post/2026-03-04-change-selinux-port-types-non-standard-rhel-9/view

[^15_2]: https://oneuptime.com/blog/post/2026-03-04-change-default-ssh-port-update-selinux-rhel-9/view

[^15_3]: https://www.rootusers.com/use-selinux-port-labeling-to-allow-services-to-use-non-standard-ports/

[^15_4]: https://my.cloudfanatic.net/knowledgebase/9/How-to-Change-SSH-Port-in-Linux.html

[^15_5]: https://www.skillset.com/questions/choose-the-correct-answer-which-will-add-selinux-context-ssh_port_t-on-port-2222

[^15_6]: https://global.php.cn/zh-tw/faq/463755.html

[^15_7]: https://www.cable-nets.ru/как-изменить-порт-ssh-с-работающим-selinux/

[^15_8]: https://rootjs.tistory.com/19


---

# solucionado

Perfecto, ¡buen trabajo! 🎉

Con lo que hemos hecho, ahora tu SSH debería estar funcionando correctamente en el puerto que elegiste (ya sea 2222, 2264 u otro), con SELinux configurado y el firewall permitiendo ese puerto de forma explícita.

Si en el futuro:

- cambias el puerto de nuevo,
- reinstalas el sistema, o
- pones en marcha otra máquina con SSH en puerto no estándar,

recuerda repetir estos pasos clave:

1. Cambiar el `Port X` en `/etc/ssh/sshd_config`.
2. Validar con `sudo sshd -t` y reiniciar `sshd`.
3. Permitir el puerto en SELinux: `sudo semanage port -a -t ssh_port_t -p tcp X`.
4. Abrir el puerto en firewalld: `sudo firewall-cmd --permanent --add-port=X/tcp`.

Si en algún momento quieres que te prepare un pequeño script o dotfile de configuración automatizado para futuras instalaciones de Fedora con SSH en puerto personalizado, solo dime y te lo monto listo para usar.

