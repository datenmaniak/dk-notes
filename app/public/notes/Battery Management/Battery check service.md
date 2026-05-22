<img src="https://r2cdn.perplexity.ai/pplx-full-logo-primary-dark%402x.png" style="height:64px;margin-right:32px"/>

# Por ahora estoy utilizando Fedora bluefin... En varias ocasiones mi laptop se ha apagado dado que ha bajado a 0 % de bateria... me preocupa que ocurra un da~no en la bateria

Sí, apagar la laptop varias veces al llegar a 0% de batería puede **acortar la vida útil** de la batería de iones de litio con el tiempo, aunque no suele “matarla” de golpe.[^1_2][^1_5]
Lo ideal es evitar que la descarga pase de un 20–30% con frecuencia y mantener la carga en un rango medio‑alto (por ejemplo, entre 30% y ~80%) cuando sea posible.[^1_5][^1_2]

## ¿Qué riesgo tiene dejar caer a 0%?

- Las baterías de litio se degradan más rápido si se llevan a descargas profundas muy seguido.[^1_2][^1_5]
- Un apagón repentino también puede causar que el sistema se quede “sucio” (por ejemplo, particiones sin desmontar o corrupción menor en cachés), aunque eso afecta más al sistema que a la batería en sí.[^1_4][^1_6]


## Qué puedes hacer en Fedora Bluefin

Dado que Bluefin ya está bastante optimizado para ahorro de energía, puedes reforzarlo:

- **Evitar llegar a 0%**: procura conectar el cargador cuando notes que estás por debajo de 20–30%.
- **Usar modo de suspensión en lugar de apagar**: si vas a dejar la laptop un rato, deja que se suspenda en lugar de consumir hasta 0% en “bespoke”/Bluefin.[^1_7]
- **Monitorizar el estado de la batería**:
    - En una terminal, ejecuta:

```bash
upower -i /org/freedesktop/UPower/devices/battery_BAT0
```

    - Ahí verás cosas como `energy-full`, `energy-full-design`, `percentage` y `cycle-count`; si la capacidad actual es mucho menor que la “design” es señal de desgaste.[^1_5]


## Opciones adicionales de protección

- Algunos fabricantes (Lenovo, ASUS, etc.) permiten limitar el cargado máximo (por ejemplo, hasta el 80%) desde el BIOS o una utilidad; revisa si tu portátil tiene algo así, ayuda mucho a preservar la batería.[^1_1][^1_8]
- Si notas que la batería se descarga muy rápido incluso en reposo (por ejemplo, se vacía en horas con el equipo apagado), puede haber un problema de fondo (firmware, batería vieja, o drenaje por hardware).[^1_6][^1_9]

Si me dices modelo de laptop y unos valores de `upower` (energía total y porcentaje que ves), puedo ayudarte a interpretar si la batería ya está demasiado desgastada o si solo es un tema de hábitos de uso.
<span style="display:none">[^1_10][^1_3]</span>

<div align="center">⁂</div>

[^1_1]: https://www.facebook.com/groups/Fedora.linux/posts/10174156101360249/

[^1_2]: https://amif.mx/es-perjudicial-dejar-que-la-bateria-del-celular-se-descargue-por-completo/

[^1_3]: https://community.acer.com/es/discussion/535568/mi-ordenador-se-apaga-a-pesar-de-que-le-queda-mas-de-la-mitad-de-bateria/p1

[^1_4]: https://www.reddit.com/r/Fedora/comments/1rco82j/my_fedora_wont_boot_anymore_after_the_battery/

[^1_5]: https://www.large-battery.com/es/blog/basics-about-discharging-battery-process-performance-lifespan/

[^1_6]: https://learn.microsoft.com/es-es/answers/questions/4236610/bater-a-cae-a-0-luego-de-tener-el-equipo-apagado-d

[^1_7]: https://www.reddit.com/r/linux4noobs/comments/1mah515/why_is_battery_life_on_bluefin_23x_better_than/

[^1_8]: https://www.infobae.com/tecno/2025/11/29/como-se-debe-cargar-el-movil-para-no-danar-la-bateria/

[^1_9]: https://damian.murana.uy/solucion-si-la-bateria-se-descarga-con-portatil-apagado/

[^1_10]: https://www.youtube.com/watch?v=DlGuhUhH7Js


---

# native-path:          BAT0

vendor:               HP
model:                Primary
serial:               SerialNumber
power supply:         yes
updated:              Tue 07 Apr 2026 12:20:01 PM -04 (0 seconds ago)
has history:          yes
has statistics:       yes
battery
present:             yes
rechargeable:        yes
state:               charging
warning-level:       none
energy:              44.074 Wh
energy-empty:        0 Wh
energy-full:         51.201 Wh
energy-full-design:  51.201 Wh
voltage-min-design:  11.55 V
capacity-level:      Normal
energy-rate:         9.99 W
voltage:             13.211 V
charge-cycles:       220
time to full:        42.8 minutes
percentage:          86%
capacity:            100%
technology:          lithium-ion
icon-name:          'battery-full-charging-symbolic'
History (charge):
1775578697	86.000	charging
History (rate):
1775578800	9.990	unknown
1775578787	10.056	charging
1775578771	10.162	charging
1775578760	10.226	charging
1775578749	10.293	charging
1775578737	10.371	charging
1775578727	10.422	charging
1775578715	10.484	charging
1775578705	10.556	charging
1775578692	10.639	charging
History (voltage):
1775578801	13.211	unknown
1775578786	13.212	charging
1775578772	13.212	charging
1775578760	13.211	charging
1775578750	13.210	charging
1775578739	13.212	charging
1775578729	13.210	charging
1775578717	13.210	charging
1775578707	13.212	charging
1775578692	13.210	charging

Tu batería está en muy buen estado técnicamente y **no hay señales de envejecimiento temprano**; lo que te preocupa es un problema de uso (llegar a 0%), no de hardware defectuoso.[^2_1][^2_4][^2_8]

### Qué te dice ese `upower`

- **`energy-full` = 51.201 Wh`**, **`energy-full-design` = 51.201 Wh`**:
La capacidad actual es igual a la diseñada, es decir, **no ha perdido capacidad** todavía.[^2_6][^2_10]
- **`capacity: 100%`**:
La batería reporta que está al 100% de su capacidad nominal, lo que apoya que está saludable.[^2_9][^2_6]
- **`charge-cycles: 220`**:
Es un número razonable para una batería de laptop; muchas baterías de litio se consideran “normales” hasta varios cientos de ciclos.[^2_4][^2_7]

En resumen: físicamente tu batería HP está bien, pero el uso con apagados a 0% puede acelerar su envejecimiento con el tiempo.[^2_8][^2_1][^2_4]

***

### Cómo protegerla desde ahora

- **Evita que baje de 20–30%**:
Programa en tu cabeza (o en un recordatorio) que si ves ~20–30% ya tengas que conectar el cargador. Las descargas profundes frecuentes aceleran el desgaste.[^2_1][^2_8][^2_9]
- **Mantén la carga ~entre 30% y 80% si es posible**:
Este rango es lo más “suave” para las celdas de litio y alarga la vida útil.[^2_2][^2_5][^2_10]
- **Usa suspensión en vez de esperar a 0%**:
Si vas a dejar la laptop un rato, deja que se suspenda; eso no fuerza la batería a consumirse hasta apagarse.[^2_3][^2_8]

***

### Qué hacer en el futuro con este mismo equipo

- **Revisa el control de batería en BIOS/HP Battery Care**:
Muchas HP permiten límites de carga (por ejemplo, “hasta 80%”) o modos “conservación de batería”; usarlo te ayuda a mantener la vida útil.[^2_5][^2_8]
- **Si se sigue apagando a 0%, cambia el hábito o usa el cargador conectado**:
Cuando sepas que vas a usarla muchas horas, convierte eso en un uso “sembléctrico” (siempre enchufado) con la batería en ese rango medio‑alto, en vez de forzar descargas profundas.[^2_2][^2_5]

Si quieres, dime cuántos años llevas usando esta laptop y si sueles tenerla siempre enchufada o con carga escasa, y te ayudo a ajustar un “perfil” de uso (20–80%, 40–80%, etc.) que encaje con tu flujo de trabajo.

<div align="center">⁂</div>

[^2_1]: https://es.everexceed.com/blog/damage-of-lithium-battery-caused-by-overcharge-and-discharge_b398

[^2_2]: https://www.pccomponentes.com/como-cuidar-bateria-portatil-consejos-trucos

[^2_3]: https://www.genbeta.com/linux/en-fedora-han-conseguido-formas-de-aumentar-el-rendimiento-de-la-bateria-en-linux-hasta-un-30

[^2_4]: https://lumisolar.es/blog/consecuencias-de-descargar-completamente-una-bateria-de-litio

[^2_5]: https://www.hp.com/mx-es/shop/tech-takes/como-incrementar-vida-util-bateria-de-laptop

[^2_6]: https://autosolar.pe/aspectos-tecnicos/mantenimiento-de-baterias-de-litio

[^2_7]: https://www.pknergy.com/es/news/5-reasons-why-lithium-batteries-should-not-be-fully-discharged/

[^2_8]: https://www.portatilmovil.com/es/blog/post/como-alargar-vida-util-bateria-portatil.html

[^2_9]: https://hyvel.mx/blogs/news/cuidados-para-bateria-de-litio

[^2_10]: https://www.large-battery.com/es/blog/partial-charging-lithium-battery-longevity/


---

# quisiera programar algo que verifique cuando la bateria llega por debajo del 10%, apagar...

Puedes hacer un script sencillo que se ejecuta periódicamente y apaga el sistema cuando la batería cae **por debajo del 10%** (mientras esté en batería). Te propongo una solución con `cron` y un script en Bash.

***

### 1. Crear el script

Crea un archivo, por ejemplo `/usr/local/bin/check‑battery‑shutdown.sh`:

```bash
sudo tee /usr/local/bin/check-battery-shutdown.sh << 'EOF'
#!/bin/bash

# Ajusta este valor al umbral de apagado (por ejemplo, 10)
THRESHOLD=10

# Directorio de la batería en sysfs (normalmente BAT0)
BAT_DIR="/sys/class/power_supply/BAT0"

# Si BAT0 no existe, termina
if [[ ! -d "$BAT_DIR" ]]; then
    exit 0
fi

# Si no hay batería presente, termina
if [[ "$(cat "$BAT_DIR/present" 2>/dev/null)" != "1" ]]; then
    exit 0
fi

# Leer energía actual y energía completa
ENERGY_NOW=$(cat "$BAT_DIR/energy_now" 2>/dev/null)
ENERGY_FULL=$(cat "$BAT_DIR/energy_full" 2>/dev/null)

# Si no pudimos leer valores, termina
if [[ -z "$ENERGY_NOW" || -z "$ENERGY_FULL" ]]; then
    exit 0
fi

# Calcular porcentaje
PERCENT=$(( 100 * ENERGY_NOW / ENERGY_FULL ))

# Leer estado (charging / discharging / full)
STATUS=$(cat "$BAT_DIR/status" 2>/dev/null)

# Solo actuar si está descargando (batería)
if [[ "$STATUS" != "Discharging" ]]; then
    exit 0
fi

# Si el porcentaje está por debajo del umbral, apagar
if [[ "$PERCENT" -lt "$THRESHOLD" ]]; then
    logger "Battery shutdown: level ${PERCENT}% < ${THRESHOLD}%"
    /sbin/shutdown -h now "Battery below ${THRESHOLD}%"
fi

EOF
```

Hazlo ejecutable:

```bash
sudo chmod +x /usr/local/bin/check-battery-shutdown.sh
```


***

### 2. Programar el chequeo con `cron`

Edita el crontab de root:

```bash
sudo crontab -e
```

Y añade esta línea para que revise cada minuto:

```bash
* * * * * /usr/local/bin/check-battery-shutdown.sh
```

Esto significa:

- **cada minuto** comprueba el porcentaje de batería
- si está en descarga y cae **por debajo de 10%**, apaga limpiamente el sistema.[^3_1][^3_2][^3_3]

***

### 3. Opciones adicionales (opcional)

- Cambiar el umbral: modifica `THRESHOLD=10` por `8` o `15` según convenga.
- Usar `sleep` y un bucle en el script si prefieres que no uses `cron` (por ejemplo, un servicio systemd con `ExecStart` ejecutando un loop infinito con `sleep 30`), pero `cron` es más simple y estándar en tu contexto Fedora.[^3_4][^3_5]

Si quieres, te puedo ayudarte a portarlo a un servicio `systemd` propio (por ejemplo `battery-check.service`) en lugar de cron.
<span style="display:none">[^3_10][^3_6][^3_7][^3_8][^3_9]</span>

<div align="center">⁂</div>

[^3_1]: https://www.geeksforgeeks.org/linux-unix/bash-script-to-get-low-battery-alert-in-linux/

[^3_2]: https://forum.armbian.com/topic/16397-script-to-check-the-power-status-and-shutdown-if-battery-is-low/

[^3_3]: https://www.reddit.com/r/sysadmin/comments/oxbzkc/script_to_monitor_battery_level_and_shut_down/

[^3_4]: https://kota.nz/battery_notifications_with_udev.html

[^3_5]: https://bbs.archlinux.org/viewtopic.php?id=149340

[^3_6]: https://forum.openmandriva.org/t/shutdown-on-boot-when-battery-level-is-critically-low/7785

[^3_7]: https://bbs.archlinux.org/viewtopic.php?id=304722

[^3_8]: https://www.reddit.com/r/archlinux/comments/ywl8aa/get_notified_when_battery_goes_below_a_certain/

[^3_9]: https://github.com/systemd/systemd/issues/13633

[^3_10]: https://stackoverflow.com/questions/53997451/script-to-play-a-warning-when-battery-level-is-under-a-threshold-in-macos


---

# es posible que crontab no exista en sistemas inmutables como Fedora?

Sí, en sistemas inmutables de Fedora como **Silverblue / Kinoite / Bluefin** el paquete `cron/crontab` **no está instalado por defecto**, pero sí puedes usar `crontab` si lo instalas manualmente o, mejor aún, usar `systemd` como recomienda Fedora.[^4_2][^4_3]

### ¿Está disponible `crontab` en Fedora inmutable?

- En **Fedora Workstation clásica**, `cronie` y `crontabs` suelen venir por defecto; en **Silverblue / Bluefin** no se instalan a menos que tú los agregues.
- Puedes instalarlo con:

```bash
sudo rpm-ostree install cronie crontabs
rpm-ostree status
```

y luego reiniciar; después funcionará `crontab -e` igual que en cualquier Fedora tradicional.[^4_3][^4_2]


### Alternativa recomendada: `systemd` en vez de `cron`

Fedora prefiere **`systemd timers`** en lugar de `cron` para tareas periódicas, especialmente en sistemas inmutables, porque encaja mejor con el modelo de gestión de paquetes/`rpm-ostree`.[^4_8][^4_2]

Si quieres podemos reescribir el monitor de batería como un servicio `systemd` + timer, y así evitar depender de `cron`. ¿Quieres que lo hagamos de esa forma?
<span style="display:none">[^4_1][^4_10][^4_4][^4_5][^4_6][^4_7][^4_9]</span>

<div align="center">⁂</div>

[^4_1]: https://www.youtube.com/watch?v=MmJUvMDrYBs

[^4_2]: https://docs.fedoraproject.org/es/packaging-guidelines/CronFiles/

[^4_3]: https://www.reddit.com/r/Fedora/comments/17eh4sj/cronie_on_fedora_39_beta_noob_question/

[^4_4]: https://www.reddit.com/r/linux4noobs/comments/z8a0ri/set_up_crontab_in_fedora/

[^4_5]: https://www.ochobitshacenunbyte.com/2018/03/22/programar-tareas-en-linux-facilmente-con-cron-y-crontab/

[^4_6]: https://coffeebytes.dev/es/linux/programa-tareas-periodicas-facil-en-linux-con-cron-y-crontab/

[^4_7]: https://www.youtube.com/watch?v=_bFuqyAZOVk

[^4_8]: https://docs.fedoraproject.org/es/fedora/f35/system-administrators-guide/

[^4_9]: https://www.youtube.com/watch?v=NoXSthaJqlI

[^4_10]: https://docs.oracle.com/es/learn/ol-crontab/index.html


---

# como seria con systemd ?

Con `systemd` puedes hacer lo mismo de forma más “limpia” para Fedora Bluefin: un servicio que revisa la batería y un timer que lo ejecuta cada cierto tiempo. A continuación va un ejemplo completo para **apagar cuando la batería esté por debajo del 10% y en descarga**.

***

### 1. Crear el script de batería

Guarda el siguiente script en `/usr/local/bin/battery-check.sh`:

```bash
sudo tee /usr/local/bin/battery-check.sh << 'EOF'
#!/bin/bash

THRESHOLD=10
BAT_DIR="/sys/class/power_supply/BAT0"

# Verificar que existe BAT0
if [[ ! -d "$BAT_DIR" ]]; then
    exit 0
fi

if [[ "$(cat "$BAT_DIR/present" 2>/dev/null)" != "1" ]]; then
    exit 0
fi

ENERGY_NOW=$(cat "$BAT_DIR/energy_now" 2>/dev/null)
ENERGY_FULL=$(cat "$BAT_DIR/energy_full" 2>/dev/null)

if [[ -z "$ENERGY_NOW" || -z "$ENERGY_FULL" ]]; then
    exit 0
fi

PERCENT=$(( 100 * ENERGY_NOW / ENERGY_FULL ))

STATUS=$(cat "$BAT_DIR/status" 2>/dev/null)

# Solo actuar si está descargando
if [[ "$STATUS" != "Discharging" ]]; then
    exit 0
fi

if [[ "$PERCENT" -lt "$THRESHOLD" ]]; then
    logger "Battery shutdown: level ${PERCENT}% < ${THRESHOLD}%"
    /sbin/shutdown -h now "Battery below ${THRESHOLD}%"
fi

EOF
```

Hazlo ejecutable:

```bash
sudo chmod +x /usr/local/bin/battery-check.sh
```


***

### 2. Crear el servicio `systemd`

Como root, crea el servicio:

```bash
sudo tee /etc/systemd/system/battery-check.service << 'EOF'
[Unit]
Description=Battery check and shutdown below 10%

[Service]
Type=oneshot
ExecStart=/usr/local/bin/battery-check.sh
EOF
```


***

### 3. Crear el timer (que sustituye a `cron`)

También como root:

```bash
sudo tee /etc/systemd/system/battery-check.timer << 'EOF'
[Unit]
Description=Run battery check every 60 seconds

[Timer]
OnBootSec=30
OnUnitActiveSec=60
Persistent=true
Unit=battery-check.service

[Install]
WantedBy=timers.target
EOF
```

Este timer:

- espera 30 s tras el arranque antes de empezar.
- se ejecuta cada 60 s mientras el servicio haya estado activo.[^5_2][^5_5]

***

### 4. Activar el timer

```bash
sudo systemctl daemon-reload
sudo systemctl enable battery-check.timer
sudo systemctl start battery-check.timer
```

Con eso tu sistema revisará la batería cada ~60 segundos y apagará automáticamente si cae por debajo del **10% en modo descarga**.[^5_1][^5_5]

Si quieres, te puedo ajustar el THRESHOLD a 12% o 15% y/o hacer una versión **usuario** (`systemd --user`) para que no requiera reiniciar el timer tras cada update de sistema.
<span style="display:none">[^5_3][^5_4][^5_6][^5_7][^5_8][^5_9]</span>

<div align="center">⁂</div>

[^5_1]: https://www.reddit.com/r/archlinux/comments/1ck77hm/low_battery_notification_using_a_systemd_timer/

[^5_2]: https://discussion.fedoraproject.org/t/systemd-timers-in-suspend/97515

[^5_3]: https://bbs.archlinux.org/viewtopic.php?id=182191

[^5_4]: https://discussion.fedoraproject.org/t/solved-an-help-on-systemd-timer-per-user-configuration/133388

[^5_5]: https://www.linux.com/topic/desktop/setting-timer-systemd-linux/

[^5_6]: https://knowledgebase.frame.work/es/optimizing-fedora-battery-life-r1baXZh

[^5_7]: https://discussion.fedoraproject.org/t/power-on-off-time-battery-test-how-to-know/70882

[^5_8]: https://documentation.suse.com/es-es/sle-micro/6.0/pdf/Micro-systemd-working-with-timers_es.pdf

[^5_9]: https://discussion.fedoraproject.org/t/failed-to-start-systemd-timer-and-service/99506?page=2


---

# observo que has generado un nuevo script.. No aplica el anterior para el caso de systemd.?

Perfecta observación: **el script sí puede ser el mismo**; solo cambia *quién* lo ejecuta (antes `cron`, ahora `systemd`). El código que usé en el ejemplo de `systemd` es básicamente el mismo que en el de `cron`, solo ligeramente reorganizado para que quede claro qué hace el servicio.

### ¿Se puede usar el script anterior con `systemd`?

Sí, puedes:

1. **Dejar el script de `cron`** (por ejemplo `/usr/local/bin/check-battery-shutdown.sh`) **sin tocarlo**.
2. **Crear el servicio `systemd` que ejecute exactamente ese script**:
```bash
sudo tee /etc/systemd/system/battery-check.service << 'EOF'
[Unit]
Description=Battery check and shutdown below 10%

[Service]
Type=oneshot
ExecStart=/usr/local/bin/check-battery-shutdown.sh
EOF
```

3. Luego crear el **mismo timer** que te puse antes:
```bash
sudo tee /etc/systemd/system/battery-check.timer << 'EOF'
[Unit]
Description=Run battery check every 60 seconds

[Timer]
OnBootSec=30
OnUnitActiveSec=60
Persistent=true
Unit=battery-check.service

[Install]
WantedBy=timers.target
EOF
```

4. Activar:
```bash
sudo systemctl daemon-reload
sudo systemctl enable battery-check.timer
sudo systemctl start battery-check.timer
```

En resumen:

- El **algoritmo de batería** (calcular %, ver si está en descarga, apagar si <10%) puede ser el mismo.
- Con `systemd` solo cambias el **“lanzador”**: de `crontab` a `battery-check.service` + `battery-check.timer`.

Si quieres, dime cuál versión del script quieres conservar (la de `cron` o la recién escrita) y ajusto el ejemplo exactamente a ese nombre y ruta.

---

# he apuntado a mi homeuser. es valido hacer esto: [Unit]

Description=Battery check and shutdown below 10%

[Service]
Type=oneshot
ExecStart=\$HOME/00-10_scripts/01.01_watch-battery/check-battery-shutdown.sh

Sí, la idea es válida, pero **no puedes usar `$HOME` literalmente en `ExecStart=`**; `systemd` no expande variables de shell como `$HOME` en esa línea.[^7_4][^7_7]

### Opciones correctas

1. **Ruta absoluta fija (la más simple)**
Si tu usuario es `tuuser`:

```ini
[Unit]
Description=Battery check and shutdown below 10%

[Service]
Type=oneshot
ExecStart=/home/tuuser/00-10_scripts/01.01_watch-battery/check-battery-shutdown.sh
```

2. **Si usas un servicio “user” (`systemctl --user`)**
Entonces puedes usar `%h` en lugar de `$HOME`:

```ini
[Unit]
Description=Battery check and shutdown below 10%

[Service]
Type=oneshot
ExecStart=%h/00-10_scripts/01.01_watch-battery/check-battery-shutdown.sh
```

Y luego activarlo con `systemctl --user enable ...`.[^7_3][^7_5]
3. **Con `WorkingDirectory` (opcional)**

También puedes combinarlo con `WorkingDirectory` para que no dependas tanto de la ruta completa:

```ini
[Unit]
Description=Battery check and shutdown below 10%

[Service]
Type=oneshot
WorkingDirectory=/home/tuuser/00-10_scripts/01.01_watch-battery
ExecStart=./check-battery-shutdown.sh
```


### Recomendación para tu caso

- Si el servicio es **del sistema** (`/etc/systemd/system/...`), usa **ruta absoluta completa**.
- Si quieres algo más “portable” entre usuarios, conviértelo en servicio **user** y usa `%h`.

Si me dices si lo quieres como servicio del sistema (`sudo`) o como servicio `--user`, te armo el archivo completo con el formato exacto correcto.
<span style="display:none">[^7_1][^7_10][^7_2][^7_6][^7_8][^7_9]</span>

<div align="center">⁂</div>

[^7_1]: https://github.com/systemd/systemd/issues/30782

[^7_2]: https://dev.to/piyushbagani15/automate-your-scripts-with-systemd-services-benefits-and-step-by-step-guide-3nik

[^7_3]: https://docs.oracle.com/en/operating-systems/oracle-linux/8/systemd/CreatingasystemdUserBasedService.html

[^7_4]: https://www.baeldung.com/linux/systemd-service-working-directory

[^7_5]: https://bbs.archlinux.org/viewtopic.php?id=297777

[^7_6]: https://linuxcourse.rutgers.edu/summer2025/html/systemd.html

[^7_7]: https://www.freedesktop.org/software/systemd/man/latest/systemd.exec.html

[^7_8]: https://gist.github.com/torch2424/a2d4632831db4327b02d839147685007

[^7_9]: https://github.com/systemd/systemd/issues/19690

[^7_10]: https://notes.neeasade.net/systemd-user-services.html


---

# como seria la instruccion completa para activarlo con el usuario,  y cual para todo el sistema?

Perfecto, vamos a aclararlo de forma concreta:

- **Como servicio del usuario** → se gestiona con `systemctl --user` y se suelen guardar en `~/.config/systemd/user/`.
- **Como servicio de todo el sistema** → se gestiona con `sudo systemctl` y se guarda en `/etc/systemd/system/`.

Te pongo ejemplos completos para tu script `check-battery-shutdown.sh` en tu `$HOME` (por ejemplo `/home/tuuser/00-10_scripts/01.01_watch-battery/`).

***

### 1. Opción “usuario” (`systemctl --user`)

#### Pasos:

1. Crea el `.service` en tu directorio de usuario:
```bash
mkdir -p ~/.config/systemd/user

tee ~/.config/systemd/user/battery-check.service << 'EOF'
[Unit]
Description=Battery check and shutdown below 10%

[Service]
Type=oneshot
ExecStart=%h/00-10_scripts/01.01_watch-battery/check-battery-shutdown.sh
EOF
```

2. Crea el timer (también como usuario):
```bash
tee ~/.config/systemd/user/battery-check.timer << 'EOF'
[Unit]
Description=Run battery check every 60 seconds

[Timer]
OnBootSec=30
OnUnitActiveSec=60
Persistent=true
Unit=battery-check.service

[Install]
WantedBy=default.target
EOF
```

3. **Activar como usuario** (sin `sudo`):
```bash
systemctl --user daemon-reload
systemctl --user enable battery-check.timer
systemctl --user start battery-check.timer
```

Con esto el servicio se lanza bajo tu usuario, empieza cuando **inicias sesión** y se apaga cuando cierras todas las sesiones.[^8_1][^8_3][^8_6]

***

### 2. Opción “sistema” (todo el sistema)

#### Pasos:

1. Copia el mismo script a una ruta “global” (por ejemplo `/usr/local/bin/`):
```bash
sudo mkdir -p /usr/local/bin
sudo cp "$HOME/00-10_scripts/01.01_watch-battery/check-battery-shutdown.sh" /usr/local/bin/
sudo chmod +x /usr/local/bin/check-battery-shutdown.sh
```

2. Crea el servicio de sistema:
```bash
sudo tee /etc/systemd/system/battery-check.service << 'EOF'
[Unit]
Description=Battery check and shutdown below 10%

[Service]
Type=oneshot
ExecStart=/usr/local/bin/check-battery-shutdown.sh
EOF
```

3. Crea el timer de sistema:
```bash
sudo tee /etc/systemd/system/battery-check.timer << 'EOF'
[Unit]
Description=Run battery check every 60 seconds

[Timer]
OnBootSec=30
OnUnitActiveSec=60
Persistent=true
Unit=battery-check.service

[Install]
WantedBy=timers.target
EOF
```

4. **Activar como sistema** (con `sudo`):
```bash
sudo systemctl daemon-reload
sudo systemctl enable battery-check.timer
sudo systemctl start battery-check.timer
```

Aquí el timer se ejecuta independientemente de que un usuario esté logueado.[^8_2][^8_4][^8_9]

***

### ¿Cuál te conviene más?

- Si solo te interesa que tu **usuario específico** monitoree la batería, usa la versión **`--user`**.
- Si quieres que el sistema monitoree la batería **incluso sin que nadie haya iniciado sesión**, usa la versión **sistema**.

Si quieres, dime tu nombre de usuario y ruta exacta de `check-battery-shutdown.sh` y te genero los dos archivos con dichas rutas ya reemplazadas.
<span style="display:none">[^8_10][^8_5][^8_7][^8_8]</span>

<div align="center">⁂</div>

[^8_1]: https://nts.strzibny.name/systemd-user-services/

[^8_2]: https://dev.to/edgaras/creating-and-managing-custom-systemd-services-on-ubuntu-dkh

[^8_3]: https://wiki.archlinux.org/title/Systemd/User

[^8_4]: https://documentation.suse.com/smart/systems-management/html/systemd-setting-up-service/index.html

[^8_5]: https://www.youtube.com/watch?v=rkxERuVm1Kk

[^8_6]: https://www.baeldung.com/linux/systemd-create-user-services

[^8_7]: https://discourse.nixos.org/t/what-is-the-difference-between-systemd-services-and-systemd-user-services/25222

[^8_8]: https://blog.victormendonca.com/2018/05/14/creating-a-simple-systemd-user-service/

[^8_9]: https://dohost.us/index.php/2025/10/27/user-systemd-services-user-managing-services-without-root-privileges/

[^8_10]: https://discourse.nixos.org/t/how-to-create-user-systemd-service/57671


---

# al usar la opcion "usuario" solo se activa cuando el usuario hace login ?

Sí, en el caso de la opción **“usuario”** (`systemctl --user`) el timer y el servicio se activan **cuando el usuario inicia sesión** (login gráfico, SSH, etc.), y dejan de correr cuando ya no hay ninguna sesión de ese usuario activa, a menos que uses “linger”.[^9_3][^9_5]

### Qué significa eso en la práctica

- Si tu laptop **arranca, se inicia sesión con tu usuario y luego lo usas sin cerrar sesión**, el timer seguirá corriendo.
- Si el sistema **arranca pero nunca haces login** (p. ej., arranque en modo multiusuario sin desktop), el servicio `battery-check.timer` **no se activa**, porque el `systemd --user` del usuario no se levanta.[^9_5][^9_3]


### ¿Qué debes usar según tu necesidad?

- Si solo quieres que se monitoree la batería **cuando tú estés usando el equipo**, la opción “usuario” te sirve perfectamente.
- Si quieres que el sistema **siempre** monitoree la batería, incluso sin usuario logueado (por ejemplo, tras un arranque), entonces necesitas que el servicio y el timer sean **de sistema** (archivos en `/etc/systemd/system/` y `sudo systemctl enable ...`).[^9_1][^9_5]

Si quieres, dime:

- ¿Quieres que el apagado se gestione **incluso sin que tú hayas logueado** (por ejemplo, cuando el sistema arranca y se queda solo en batería)?
- ¿O te basta con que el control de batería funcione **solo cuando tú has iniciado sesión**?

Con eso te recomiendo concretamente si conviene quedarte en “usuario” o pasarte a “sistema”.
<span style="display:none">[^9_2][^9_4][^9_6]</span>

<div align="center">⁂</div>

[^9_1]: https://forum.manjaro.org/t/setting-up-a-system-service-to-start-on-login-for-a-user/162590

[^9_2]: https://github.com/systemd/systemd/issues/33405

[^9_3]: https://wiki.archlinux.org/title/Systemd/User

[^9_4]: https://www.reddit.com/r/linux4noobs/comments/hblt92/systemd_user_service_not_starting_at_login/

[^9_5]: https://discussion.fedoraproject.org/t/running-a-systemd-service-after-logins/73999

[^9_6]: https://bbs.archlinux.org/viewtopic.php?id=250813


---

# si quisiera iniciar el servicion si cerrar sesion?

Sí, **puedes iniciar el servicio de usuario aun sin estar logueado**, usando una funcionalidad de `systemd` llamada **“lingering”**.

### 1. Habilitar “lingering” para tu usuario

Como root:

```bash
sudo loginctl enable-linger $USER
```

Esto hace que:

- `systemd --user` se inicie **al arrancar el sistema**, incluso si no hay nadie logueado.
- El timer `battery-check.timer` (instalado en `~/.config/systemd/user/`) se active **en el arranque** y siga corriendo, aunque cierres sesión.[^10_2][^10_6]

***

### 2. Confirmar que el usuario tiene lingering habilitado

```bash
loginctl show-user $USER
```

Busca la línea:

```
Linger=yes
```

Si aparece `yes`, tu usuario se mantiene vivo a nivel `systemd` y sus servicios/timers pueden correr siempre.

***

### 3. Re‑habilitar tu timer de usuario

Si ya lo tenías creado en `~/.config/systemd/user/`:

```bash
systemctl --user daemon-reload
systemctl --user enable battery-check.timer
```

(Nota: `enable` aquí registra el timer; el `linger` hace que `systemd --user` arranque en el boot, así que el timer se ejecutará aunque nunca hayas hecho login).[^10_4][^10_6]

***

### 4. ¿Qué opción sigue siendo mejor para tu caso?

- Si **solo quieres** que el control de batería funcione **siempre que ese usuario pueda estar logueado** (pero sin importar que cierres sesión), usa **servicio/timer de usuario + `loginctl enable-linger $USER`**.
- Si prefieres que el sistema gestione esto **por completo**, sin ligarlo a ningún usuario concreto, sigue siendo más limpio usar el servicio/timer **de sistema** (`/etc/systemd/system/` con `sudo systemctl enable ...`).[^10_6][^10_8]

Si quieres, dime si tu usuario es, por ejemplo, `carlos` o `juan`, y te escribo los comandos literales ya adaptados a tu nombre de usuario.
<span style="display:none">[^10_1][^10_10][^10_3][^10_5][^10_7][^10_9]</span>

<div align="center">⁂</div>

[^10_1]: https://github.com/systemd/systemd/issues/8486

[^10_2]: https://blog.khmersite.net/p/how-to-enable-check-linger-for-user/

[^10_3]: https://bbs.archlinux.org/viewtopic.php?id=235105

[^10_4]: https://deardevices.com/2020/08/31/systemd-user-service-on-raspberry-pi/

[^10_5]: https://groups.google.com/g/linux.debian.bugs.dist/c/JnMxgvAVF4M

[^10_6]: https://wiki.archlinux.org/title/Systemd/User

[^10_7]: https://www.reddit.com/r/kde/comments/q5ae8d/restarting_systemd_service_on_logout/

[^10_8]: https://access.redhat.com/solutions/6827941

[^10_9]: https://access.redhat.com/solutions/5254401

[^10_10]: https://www.youtube.com/watch?v=BpyqBbB1c9I


---

# ahora como puedo comprobar que funciona?

Puedes comprobar que el servicio y el timer funcionan en varios niveles: estado, registros y comportamiento. Vamos paso a paso suponiendo que usaste el **servicio/timer de usuario** (`battery-check.service` + `battery-check.timer` en `~/.config/systemd/user/`).

***

### 1. Verificar que el timer está activo

Como tu usuario (sin `sudo`):

```bash
systemctl --user status battery-check.timer
```

Busca que aparezca algo como:

- `Active: active (waiting)`
- `Loaded: loaded (...) enabled`

También puedes listar solo los timers:

```bash
systemctl --user list-timers --all
```

Deberías ver tu `battery-check.timer` con `NEXT` y `LAST` actualizados.

***

### 2. Verificar que el servicio se ejecutó (o es ejecutable)

```bash
systemctl --user status battery-check.service
```

Esto te muestra si el servicio:

- se ha ejecutado alguna vez,
- si falló,
- o si aún no se ha disparado.

***

### 3. Ver logs del servicio

```bash
journalctl --user -u battery-check.service -n 50
```

Si el script hace `logger "Battery shutdown..."` o imprime por `stdout`/`stderr`, lo verás aquí.
Si el script intenta apagar el sistema cuando baja al 10%, verás mensajes justo antes del apagado.

***

### 4. Confirmar que el lingering funciona

Como root:

```bash
loginctl show-user $USER
```

Asegúrate de que veas:

```
Linger=yes
```

Si es `yes`, entonces:

- `systemd --user` de tu usuario inicia en el arranque,
- y el timer `battery-check.timer` se activa **incluso sin que hagas login**.[^11_2][^11_7]

***

### 5. Prueba controlada

Para comprobar que el script **hace lo que quieres** sin quemar la batería siempre al 0%:

1. **Desconecta el cargador** y deja que la batería baje a, por ejemplo, 15–20%.
2. En otra terminal, sigue el estado del timer y del servicio:

```bash
watch -n 10 systemctl --user list-timers battery-check.timer
journalctl --user -u battery-check.service -f
```

3. Deja que la batería siga bajando.
4. Cuando el script vea que el porcentaje cae por debajo del 10% (y esté en `Discharging`), el sistema **debe emitir el mensaje `logger` y luego apagar**.

Si no apaga, revisa:

- Que el script se pueda ejecutar en tu ruta (`chmod +x ...`).
- Que el `BAT_DIR="/sys/class/power_supply/BAT0"` sea correcto (a veces es `BAT1`).
- Que el `STATUS` sea `Discharging` cuando estás en batería (puedes verlo con `cat /sys/class/power_supply/BAT0/status`).

***

Si quieres, copia aquí la salida de:

```bash
systemctl --user status battery-check.timer
systemctl --user status battery-check.service
journalctl --user -u battery-check.service --no-pager -n 20
```

y te ayudo a confirmar si está todo bien configurado o qué detalle falta.
<span style="display:none">[^11_1][^11_3][^11_4][^11_5][^11_6][^11_8][^11_9]</span>

<div align="center">⁂</div>

[^11_1]: https://www.ibm.com/docs/es/concert?topic=vm-configuring-concert-systemd-services

[^11_2]: https://documentation.suse.com/es-es/sle-micro/6.0/html/Micro-systemd-basics/index.html

[^11_3]: https://www.ionos.com/es-us/digitalguide/servidores/configuracion/systemctl/

[^11_4]: https://www.youtube.com/watch?v=BpyqBbB1c9I

[^11_5]: https://www.digitalocean.com/community/tutorials/how-to-use-systemctl-to-manage-systemd-services-and-units-es

[^11_6]: https://www.reddit.com/r/archlinux/comments/1295lyr/lingering_enabled_but_user_units_not_starting_on/

[^11_7]: https://docs.oracle.com/es/learn/use_systemd/index.html

[^11_8]: https://docs.redhat.com/es/documentation/red_hat_enterprise_linux/8/html/configuring_basic_system_settings/managing-services-with-systemd_configuring-basic-system-settings

[^11_9]: https://docs.fedoraproject.org/es/quick-docs/systemd-understanding-and-administering/


---

# en conclusion, este servicio se iniciara automaticamente al iniciar sesion?

Sí, **en tu caso concreto** el servicio se inicia automáticamente de dos formas distintas, según cómo lo hayas dejado:

- Si usaste **servicio/timer de usuario (`systemctl --user`)** pero **sin `linger`**:
el timer `battery-check.timer` se inicia **cada vez que haces login** y deja de correr cuando cierras sesión.
- Si, además, hiciste:

```bash
sudo loginctl enable-linger $USER
```

entonces el `systemd --user` de tu usuario se inicia **al arrancar el sistema**, y el timer `battery-check.timer` se ejecuta **automáticamente al iniciarse el sistema**, incluso si tú **nunca haces login explícito** (no solo al iniciar sesión).

En resumen:

- Sin `linger`: el servicio/timer se inicia al iniciar sesión.
- Con `linger`: el servicio/timer se inicia automáticamente al arrancar el sistema, no solo al iniciar sesión.

Si quieres que el apagado cuando bajas al 10% “funcione siempre”, lo más seguro es usar **servicio/timer de sistema** (no de usuario), que se activa directamente con `sudo systemctl enable battery-check.timer` y corre siempre que el sistema esté en ejecución.
<span style="display:none">[^12_1][^12_10][^12_2][^12_3][^12_4][^12_5][^12_6][^12_7][^12_8][^12_9]</span>

<div align="center">⁂</div>

[^12_1]: https://www.youtube.com/watch?v=pa8gkl93I60

[^12_2]: https://learn.microsoft.com/es-es/answers/questions/4321093/error-en-el-servicio-de-perfil-de-usuario-al-inici

[^12_3]: https://www.youtube.com/watch?v=LUkrd-wV8ls

[^12_4]: https://www.sysadmit.com/2017/01/windows-error-en-el-servicio-de-perfil-de-usuario-al-iniciar-sesion.html

[^12_5]: https://learn.microsoft.com/es-es/answers/questions/3947574/los-programas-se-inician-antes-poner-las-credencia

[^12_6]: https://www.reddit.com/r/WindowsHelp/comments/1jch9yq/prevent_windows_from_autoloading_last_users/

[^12_7]: https://learn.microsoft.com/es-es/answers/questions/5639978/aparece-solo-mi-usuario-al-inicio-de-windows-11-y

[^12_8]: https://www.reddit.com/r/WindowsHelp/comments/14qrwg3/windows_10_automatically_tries_to_login_on_start/

[^12_9]: https://support.google.com/chrome/thread/299391313/inicio-automatico-del-navegador-de-google?hl=es

[^12_10]: https://help.netflix.com/es/node/470


---

# puedo respaldar con chezmoi lo que se ha creado en ~/.config/systemd/user ?

Sí, **puedes respaldar perfectamente** lo que tienes en `~/.config/systemd/user/` con `chezmoi`, con un pequeño detalle de cómo estructurarlo.

***

### 1. Cómo modelarlo en chezmoi

`chezmoi` maneja el árbol de configuración como una plantilla en `~/.local/share/chezmoi/` (por omisión). Si quieres respaldar unidades de `systemd` de usuario, las suelen guardarse en una ruta interna como:

```
dot_local/share/chezmoi/dot_config/systemd/user/
```

Dentro de esa ruta, puedes copiar tus archivos `.service` y `.timer`:

- `battery-check.service`
- `battery-check.timer`

Por ejemplo, si tu repositorio local de chezmoi tiene este árbol:

```
~/.local/share/chezmoi
└── dot_config
    └── systemd
        └── user
            ├── battery-check.service
            └── battery-check.timer
```

Cuando ejecutes:

```bash
chezmoi apply
```

chezmoi lo copiará a:

```
~/.config/systemd/user/battery-check.service
~/.config/systemd/user/battery-check.timer
```


***

### 2. Recomendaciones prácticas

- **No guardes archivos “volátiles” generados por el sistema**: solo guarda los archivos que **tú** creaste (por ejemplo, tus propios `.service` y `.timer`).
- Si quieres que el `lingering` también se repita en otro sistema, guarda el comando (o un script) que hace:

```bash
loginctl enable-linger $USER
```

en tu dotfiles (por ejemplo, en un script `post-install` gestionado con chezmoi).

***

### 3. ¿Es buena idea?

Sí, para tu caso:

- respaldas el **monitor de batería** y su timer,
- y lo puedes reproducir en otro Fedora/Silverblue/Bluefin añadiendo también `loginctl enable-linger $USER` en tu flujo de post‑instalación.

Si quieres, dime cómo tienes organizado tu repositorio de chezmoi (por ejemplo, si usas `template` o `run_once_` scripts) y te propongo una estructura concreta de archivos para estos servicios de `systemd`.
<span style="display:none">[^13_1][^13_2][^13_3][^13_4][^13_5][^13_6][^13_7][^13_8]</span>

<div align="center">⁂</div>

[^13_1]: https://www.facebook.com/ChezMadelaine/

[^13_2]: https://www.instagram.com/chezmamachi/

[^13_3]: https://soundcloud.com/chezm

[^13_4]: https://chezm.jp/en

[^13_5]: https://www.tiktok.com/@mrchezm

[^13_6]: https://www.facebook.com/restochezm/?locale=es_LA

[^13_7]: https://www.instagram.com/p/DWY3LcljiTy/

[^13_8]: https://www.flickr.com/photos/ameliebillie/4088135499


---

# de manera que al migrar una nueva maquina y recuperar los dotfiles. tambien recupero esta configuracion?

Sí, **exactamente así**: si respaldas `~/.config/systemd/user/` con `chezmoi` y luego en una nueva máquina recuperas tus dotfiles, recuperarás también esa configuración de `systemd` de usuario.

### Qué se recupera

- Los archivos `.service` y `.timer` que tengas en `~/.config/systemd/user/` (por ejemplo `battery-check.service` y `battery-check.timer`) se copiarán a la misma ruta en la nueva máquina gracias a `chezmoi apply`.
- Cuando ejecutes:

```bash
systemctl --user daemon-reload
systemctl --user enable battery-check.timer
```

el timer y el servicio quedarán habilitados igual que en tu máquina actual.


### Lo que todavía no hace chezmoi por ti

- `chezmoi` solo copia **los archivos de configuración**, no habilita automáticamente el servicio/timer ni activa el `linger`.
En la nueva máquina tendrás que hacer, al menos:

```bash
# Habilitar y (opcionalmente) activar el timer
systemctl --user daemon-reload
systemctl --user enable battery-check.timer
systemctl --user start battery-check.timer

# Habilitar lingering si lo quieres
loginctl enable-linger $USER
```


### Recomendación práctica

Para que en cada nueva máquina todo “se arranque solo”:

1. Incluye en chezmoi los archivos:
    - `~/.config/systemd/user/battery-check.service`
    - `~/.config/systemd/user/battery-check.timer`
2. Añade un script en chezmoi (por ejemplo `run_once_post_install.sh.tmpl`) que ejecute:

```bash
systemctl --user daemon-reload
systemctl --user enable battery-check.timer
systemctl --user start battery-check.timer
loginctl enable-linger $USER
```


Así, al restaurar tus dotfiles en una nueva laptop, recuperas la configuración y además habilitas automáticamente el servicio de batería.
<span style="display:none">[^14_1][^14_2][^14_3][^14_4][^14_5][^14_6][^14_7][^14_8][^14_9]</span>

<div align="center">⁂</div>

[^14_1]: https://atareao.es/podcast/el-secreto-para-dominar-systemd-en-linux/

[^14_2]: https://www.reddit.com/r/archlinux/comments/uhwspo/unable_to_enable_systemd_user_service/

[^14_3]: https://www.digitalocean.com/community/tutorials/how-to-use-systemctl-to-manage-systemd-services-and-units-es

[^14_4]: https://www.youtube.com/watch?v=Xsh2DGSe6Lg

[^14_5]: https://forum.manjaro.org/t/setting-up-a-system-service-to-start-on-login-for-a-user/162590

[^14_6]: https://alexhost.com/es/faq/use-systemd-to-start-a-linux-service-at-boot/

[^14_7]: https://docs.redhat.com/es/documentation/red_hat_enterprise_linux/8/html/configuring_basic_system_settings/making-systemd-services-start-at-boot-time_getting-started-with-system-administration

[^14_8]: https://www.ionos.com/es-us/digitalguide/servidores/configuracion/systemctl/

[^14_9]: https://bbs.archlinux.org/viewtopic.php?id=183503


---

# como debo agregar esto a chezmoi.

Sí, y se hace de forma muy natural con chezmoi. Lo que quieres es que tus archivos de `~/.config/systemd/user/` se gestionen igual que cualquier otro “dotfile”. Aquí va el flujo concreto.

***

### 1. Estructura que debes tener en chezmoi

Supongamos que tu fuente de chezmoi está en:

```bash
~/.local/share/chezmoi
```

Ahí deberías ver una estructura tipo:

```bash
~/.local/share/chezmoi
└── dot_config
    └── systemd
        └── user
```

En esa carpeta `user` vas a meter los archivos que tú creaste, por ejemplo:

- `battery-check.service`
- `battery-check.timer`

Es decir, en tu repositorio local quedaría:

```bash
~/.local/share/chezmoi/dot_config/systemd/user/battery-check.service
~/.local/share/chezmoi/dot_config/systemd/user/battery-check.timer
```


***

### 2. Añadir estos archivos al estado de chezmoi

Si estos archivos ya existen en tu `~/.config/systemd/user/`, úsalos:

```bash
chezmoi add ~/.config/systemd/user/battery-check.service
chezmoi add ~/.config/systemd/user/battery-check.timer
```

Esto copia esos archivos al árbol de chezmoi manteniendo la ruta relativa `dot_config/systemd/user/...`. Si editas el archivo después, puedes usar:

```bash
chezmoi edit ~/.config/systemd/user/battery-check.service
```

para modificarlo dentro de chezmoi y luego aplicar:

```bash
chezmoi apply
```

para que el contenido vuelva a `~/.config/systemd/user/`.

***

### 3. Automatizar la habilitación del servicio/timer en nuevas máquinas

Para que en cada laptop nueva no solo recuperes los archivos sino que también se habilite el timer y el `linger`, puedes crear un script gestionado por chezmoi.

Por ejemplo, en:

```bash
~/.local/share/chezmoi/run_once_post_install.sh.tmpl
```

pones:

```bash
#!/bin/bash

set -e

echo "Activando battery-check.timer..."
systemctl --user daemon-reload
systemctl --user enable battery-check.timer
systemctl --user start battery-check.timer

echo "Activando lingering para el usuario $USER..."
loginctl enable-linger $USER
```

Hazlo ejecutable:

```bash
chmod +x ~/.local/share/chezmoi/run_once_post_install.sh.tmpl
```

Y añádelo a chezmoi:

```bash
chezmoi add run_once_post_install.sh.tmpl
```


***

### 4. En una nueva máquina, el flujo es:

```bash
# 1. Inicializar chezmoi desde tu repositorio remoto
chezmoi init https://github.com/tuusuario/dotfiles.git --apply

# 2. El script run_once_post_install.sh.tmpl se ejecutará una sola vez:
#    - activa el timer
#    - activa el lingering
```

Con esto, al recuperar los dotfiles:

- recuperas la configuración de `systemd` de usuario exactamente igual que en tu máquina actual,
- y además se habilita el servicio/timer y el `linger` de forma automática.

Si quieres, dime qué ruta tienes para `chezmoi source` (por ejemplo, `chezmoi source-path`) y te escribo los comandos literales con tus rutas reales.
<span style="display:none">[^15_1][^15_10][^15_2][^15_3][^15_4][^15_5][^15_6][^15_7][^15_8][^15_9]</span>

<div align="center">⁂</div>

[^15_1]: https://www.youtube.com/watch?v=Xsh2DGSe6Lg

[^15_2]: https://wiki.archlinux.org/title/Systemd_(Espa%C3%B1ol)

[^15_3]: https://fig.io/manual/chezmoi/help

[^15_4]: https://picodotdev.github.io/blog-bitix/2022/02/que-son-los-archivos-dotfiles-y-como-gestionarlos-con-chezmoi/

[^15_5]: https://atareao.es/podcast/email-en-la-terminal-linux-y-otras-historias/

[^15_6]: https://chezmoi.io/reference/commands/manage/

[^15_7]: https://docs.redhat.com/es/documentation/red_hat_enterprise_linux/8/html/configuring_basic_system_settings/working-with-systemd-unit-files_managing-services-with-systemd

[^15_8]: https://github.com/nicholaschiang/dotfiles

[^15_9]: https://github.com/twpayne/chezmoi/issues/3405

[^15_10]: https://www.reddit.com/r/archlinux/comments/1lvq7ux/confusion_how_do_i_use_aconfmgr_with_chezmoi/

