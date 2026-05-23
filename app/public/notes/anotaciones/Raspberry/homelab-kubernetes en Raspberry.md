<img src="https://r2cdn.perplexity.ai/pplx-full-logo-primary-dark%402x.png" style="height:64px;margin-right:32px"/>

# vamos a trabajar en crear un home lab para kubernetes. Para ponerte en contexto, en mi estacion de trabajo tengo instalador el sistema Bluefin, kubectl y minikube.   Adicionalmente, tengo dos computadores conectados a la misma red: un Raspberry Pi 3 con OS Lite y k3s instalado, en el segundo computador Zorin Core 18.

Perfecto, ya tienes una base muy sólida para montar un homelab de Kubernetes: tu estación con Bluefin + kubectl + Minikube, un Raspberry Pi 3 con k3s y un equipo con Zorin Core 18 en la misma red.[^1_2][^1_3]

Voy a responderte en español, pensando en un diseño práctico y escalable para que puedas ir aprendiendo paso a paso.

***

### 1. Decidir el rol de cada nodo

En tu caso una configuración típica de homelab ligero sería:

- **Estación Bluefin**: client/dev (kubectl + Minikube para pruebas rápidas y aisladas).[^1_9][^1_2]
- **Raspberry Pi 3 con k3s**: maestro del clúster ligero (o solo worker si quieres usar otro nodo como maestro).[^1_3][^1_7]
- **Zorin Core 18**: worker principal (más RAM/CPU) o incluso maestro si abrazas una arquitectura multi‑nodo sencilla.[^1_6]

Con k3s, suele bastar con un nodo maestro más uno o dos workers; el Raspberry Pi 3 lo soporta bien aunque no es “ideal” por memoria y CPU.[^1_7][^1_3]

***

### 2. Objetivo inmediato: unir el Raspberry Pi 3 + Zorin en un mismo clúster k3s

#### En el nodo maestro (por ejemplo, Zorin Core 18)

1. Instala k3s (si aún no lo tienes) con el script oficial:

```bash
curl -sfL https://get.k3s.io | sh -
```

Esto levanta el control‑plane y crea el fichero `/etc/rancher/k3s/k3s.yaml`.[^1_2][^1_3]
2. Comprueba que el nodo está `Ready`:

```bash
sudo k3s kubectl get nodes
```

3. Captura el token del nodo maestro (para unir workers):

```bash
sudo cat /var/lib/rancher/k3s/server/node-token
```


#### En el Raspberry Pi 3 (como worker)

1. Instala k3s en modo worker apuntando al maestro:

```bash
curl -sfL https://get.k3s.io | K3S_URL=https://IP_DEL_MAESTRO:6443 K3S_TOKEN=token-del-maestro sh -
```

Asegúrate de usar la IP fija del maestro (Zorin) y que el puerto 6443 está accesible en la LAN.[^1_3][^1_7]
2. Verifica que el Pi aparece en el clúster:

```bash
sudo k3s kubectl get nodes
```


En este punto tienes un clúster de Kubernetes funcional (aunque pequeño) con al menos dos nodos.[^1_6][^1_3]

***

### 3. Usar kubectl desde tu estación Bluefin

Para que tu estación se comunique con ese clúster k3s:

1. Copia el fichero de configuración desde el nodo maestro:

```bash
scp usuario@IP_DEL_MAESTRO:/etc/rancher/k3s/k3s.yaml ~/.kube/config-k3s
```

2. Ajusta la dirección en el fichero: busca el campo `server:` y cambia `127.0.0.1` por la IP del maestro:

```yaml
server: https://IP_DEL_MAESTRO:6443
```

3. Apunta `kubectl` a esa configuración:

```bash
export KUBECONFIG=~/.kube/config-k3s
kubectl get nodes
```


Ahora podrás ver y gestionar el clúster desde tu estación.[^1_9][^1_2]

***

### 4. Mantener Minikube como entorno aislado

Minikube sigue siendo útil como “playground” local en tu Bluefin, independiente del clúster real en Raspberry Pi + Zorin.[^1_2][^1_9]

Puedes alternar entre contextos:

- Contexto Minikube:

```bash
minikube start
kubectl config use-context minikube
```

- Contexto k3s (homelab):

```bash
kubectl config use-context default
```


Así practicas cosas en Minikube (sin quemar recursos del Pi) y luego despliegas en tu clúster de homelab.[^1_6][^1_2]

***

### 5. Próximos pasos para tu homelab

Si quieres, en un siguiente mensaje podemos trabajar en:

- **Networking**: ingress (Traefik o Nginx) y un servicio de tipo `LoadBalancer` simple con MetalLB o kube‑vip sobre tu LAN.[^1_4][^1_3]
- **Almacenamiento**: usar `k3s` con `local-path` o montar NFS/¬CSI minimal en tu LAN.[^1_3][^1_6]
- **Observabilidad**: instalar Grafana + Prometheus + Loki en el clúster para ver logs y métricas.[^1_3]

Dime con qué quieres empezar primero (por ejemplo “quiero un ingress accesible desde mi red local” o “quiero montar un pod con persistencia en el Raspberry Pi”) y te preparo los comandos y manifests paso a paso.
<span style="display:none">[^1_1][^1_10][^1_5][^1_8]</span>

<div align="center">⁂</div>

[^1_1]: https://dev.to/caffinecoder54/building-a-kubernetes-cluster-on-raspberry-pi-for-home-lab-learning-39n3

[^1_2]: https://www.linkedin.com/pulse/day-3-minikube-k3s-kubectl-set-up-your-k8s-playground-chikkela-z6ane

[^1_3]: https://www.q2bstudio.com/nuestro-blog/17646/homelab-en-raspberry-pi-con-kubernetes

[^1_4]: https://jean-marc.io/blog/kubernetes-cluster-raspberry-pi-k3s-home-lab

[^1_5]: https://dev.to/mechcloud_academy/choosing-your-local-kubernetes-companion-a-developers-guide-to-minikube-k0s-k3s-and-microk8s-7g0

[^1_6]: https://www.glukhov.org/es/post/2025/08/kubernetes-distributions-comparison/

[^1_7]: https://some-natalie.dev/blog/raspberry-pi-kubernetes/

[^1_8]: https://universal-blue.discourse.group/t/how-to-install-microk8s-on-bazzite/8805

[^1_9]: https://kubernetes.io/es/docs/tasks/tools/

[^1_10]: https://www.youtube.com/watch?v=2cbniIZUpXM


---

# no iniciare minikube en mi estacion. Quiero manejar los nodos fuera de la estacion para crear un escenario lo mas parecido a un entorno de produccion....

Perfecto, tu objetivo tiene mucho sentido: usar tu estación solo como **client** (con `kubectl`) y que todo el “plano de control” y workloads corran en los nodos fuera de ella (Raspberry Pi 3 + Zorin Core 18), lo más parecido a un entorno de producción pero en homelab.[^2_2][^2_8]

Voy a plantearte un escenario concreto y realista con la infra que ya tienes.

***

### 1. Arquitectura propuesta: “small‑prod‑like”

- **Nodo maestro (control‑plane)**:
    - Máquina con más RAM/CPU → **Zorin Core 18** (por ejemplo, 1 CPU control‑plane + taint opcional para no meter pods de app).[^2_8][^2_2]
- **Nodo worker ligero**:
    - **Raspberry Pi 3 con OS Lite + k3s** como worker (menos crítico, ideal para cargas de prueba y servicios pequeños).[^2_4]
- **Estación Bluefin**:
    - Solo `kubectl` + `kubeconfig` remoto → actúa como terminal de administración, igual que un DevOps accediendo desde laptop a un clúster en la nube.[^2_2][^2_8]

Con esto ya te alejas de Minikube: el clúster real existe solo en tus nodos físicos.[^2_5][^2_8]

***

### 2. Configurar el clúster k3s (maestro + worker)

#### En el nodo maestro (Zorin Core 18)

1. Instala k3s en modo control‑plane (maestro único, sin HA):

```bash
curl -sfL https://get.k3s.io | sh -
```

Esto inicia el API server, scheduler, controller‑manager y etcd/SQLite en ese nodo.[^2_3][^2_4]
2. Verifica que el nodo está listo:

```bash
sudo k3s kubectl get node
```

3. Obtiene el token para unir workers:

```bash
sudo cat /var/lib/rancher/k3s/server/node-token
```


#### En el Raspberry Pi 3 (worker)

1. Instala k3s en modo worker apuntando al maestro:

```bash
curl -sfL https://get.k3s.io | \
  K3S_URL=https://IP_DEL_ZORIN:6443 \
  K3S_TOKEN=token-recien-creado \
  sh -
```

Asegúrate de que el Zorin tenga una **IP fija** y que el puerto 6443 esté abierta/en la LAN.[^2_4][^2_8]
2. Verifica que el Pi aparece como worker:

```bash
sudo k3s kubectl get nodes
# Deberías ver: Zorin (control-plane) y Pi (worker)
```


En este punto ya tienes un clúster de dos nodos gestionado desde los propios nodos, sin usar Minikube ni ningún “fake‑cluster” local en tu estación.[^2_5][^2_4]

***

### 3. Usar tu estación Bluefin como único cliente

Desde tu estación:

1. Copia el `kubeconfig` del maestro:

```bash
scp usuario@IP_DEL_ZORIN:/etc/rancher/k3s/k3s.yaml ~/.kube/config-homelab
```

2. Crea un alias/contexto para tu homelab:

```bash
export KUBECONFIG=~/.kube/config-homelab
kubectl config rename-context default homelab
kubectl config use-context homelab
```

3. Verifica que ves el clúster desde tu estación:

```bash
kubectl get nodes
kubectl get pods -A
```


Ahora tu Bluefin solo habla con el API server remoto (Zorin), igual que un operador en producción accediendo a un clúster real.[^2_8][^2_2]

***

### 4. Cosas que ya te acercan a “producción”

Con esta configuración, estás implementando algunos patrones de entorno real:

- **Separación de roles**: maestro, worker y client exterior.[^2_2][^2_8]
- **Acceso remoto**: solo usas `kubectl` desde tu estación, sin clúster local.[^2_8][^2_2]
- **Escalabilidad**: puedes añadir más workers (por ejemplo otro Raspberry Pi o un VM ligero) simplemente uniendo nodos con el mismo token.[^2_7][^2_4]

***

### 5. ¿Qué quieres hacer en el próximo paso?

Ahora tienes varias opciones “product‑like” para tu homelab:

- Configurar **ingress** (Traefik / Nginx) para exponer servicios vía FQDN en tu LAN.[^2_3][^2_7]
- Añadir un **servicio de logging/monitoring** (Prometheus + Grafana + Loki) dentro del clúster.[^2_7][^2_5]
- Definir **namespaces** y roles (RBAC) para simular distintos equipos (dev, staging, prod).[^2_2]

Dime con qué quieres seguir (por ejemplo: “quiero un ingress con Traefik” o “quiero un Grafana + Prometheus en el clúster”), y te preparo los manifests y comandos paso a paso pensados para tu escenario actual.
<span style="display:none">[^2_1][^2_10][^2_6][^2_9]</span>

<div align="center">⁂</div>

[^2_1]: https://dev.to/caffinecoder54/building-a-kubernetes-cluster-on-raspberry-pi-for-home-lab-learning-39n3

[^2_2]: https://www.datacamp.com/es/blog/kubernetes-architecture-explained

[^2_3]: https://www.glukhov.org/es/post/2025/08/kubernetes-distributions-comparison/

[^2_4]: https://www.reddit.com/r/homelab/comments/1ayj5un/homelab_kubernetes_setup_k3s_raspberry_pi/

[^2_5]: https://ualmtorres.github.io/SeminarioKubernetes/

[^2_6]: http://lsi.vc.ehu.es/pablogn/docencia/AS/Act8 Orquestación  Kubernetes/Act8.pdf

[^2_7]: https://opensource.com/sites/default/files/gated-content/running_kubernetes_on_your_raspberry_pi_homelab.pdf

[^2_8]: https://aws.amazon.com/es/what-is/kubernetes-cluster/

[^2_9]: https://www.reddit.com/r/kubernetes/comments/1m6eiry/setting_up_a_productiongrade_kubernetes_cluster/

[^2_10]: https://www.youtube.com/watch?v=YmhLNUOBSu0


---

# una duda: en caso que antes de realizar todo eso, se hubiera instalado k3s en el raspberry, se presentara algun problema al crear el worker.?  Lo menciono porque ha ocurrido un error.

Sí, puede haber problema, pero no es un “show‑stopper”; lo que suele pasar es que el Raspberry Pi ya quedó corriendo como **maestro/control‑plane** (o como nodo independiente) y luego hay conflicto al intentar convertirlo en **worker** de otro clúster.

### ¿Qué suele ir mal si ya tiene k3s instalado?

1. **Ya es maestro, no worker**:
    - Si el Pi tiene k3s instalado con `curl -sfL https://get.k3s.io | sh -`, está actuando como nodo de control‑plane, no como worker.
    - No puedes usar ese mismo binario para “convertirlo” en worker del Zorin sin antes limpiarlo o instalar el agente (`k3s-agent`) correctamente.
2. **Token y URL mal configurados**:
    - Al unirlo como worker, el script exige:
        - `K3S_URL=https://IP_DEL_MAESTRO:6443`
        - `K3S_TOKEN=...` del Zorin.
    - Si el Pi ya tiene un cluster local, puede intentar apuntar a `127.0.0.1:6443` o usar su propio token, y falla el TLS/auth.[^3_2][^3_9]
3. **Cgroups y boot‑config en Raspberry Pi**:
    - En algunos casos, si no están bien configurados los cgroups en `/boot/cmdline.txt` (por ejemplo falta `cgroup_enable=cpuset cgroup_enable=memory cgroup_memory=1` al final de la línea), el nodo se queda bloqueado o sin iniciar.[^3_9][^3_2]

***

### Cómo arreglarlo “limpio” (recomendado)

Te propongo este enfoque:

1. **En el Raspberry Pi, desinstala k3s actual**:

```bash
sudo /usr/local/bin/k3s-uninstall.sh
```

Esto deja el sistema listo para ser un worker del clúster Zorin.[^3_5][^3_6]
2. **En el Zorin Core 18 (maestro)**:
    - Asegúrate de que el servicio k3s está en marcha:

```bash
sudo systemctl status k3s
```

    - Vuelve a obtener el token:

```bash
sudo cat /var/lib/rancher/k3s/server/node-token
```

3. **En el Raspberry Pi, instala solo el agente k3s‑worker**:

```bash
curl -sfL https://get.k3s.io | \
  K3S_URL=https://IP_DEL_ZORIN:6443 \
  K3S_TOKEN=token-del-maestro \
  sh -
```

Con esto, el Pi se registra como worker del clúster del Zorin, no como otro maestro.[^3_3][^3_5]
4. **Verifica desde el Zorin**:

```bash
sudo k3s kubectl get nodes
```

    - Deberías ver el Zorin como `control‑plane` y el Pi como `<none>` o `worker`.
    - Si quieres que el Pi aparezca claramente como worker puedes taguearlo:

```bash
kubectl label node NOMBRE_DEL_PI kubernetes.io/role=worker
```

(aunque esto es solo estético; el rol se define por la forma en que se instaló el agente).[^3_1][^3_6]

***

### Si quieres que te ayude con el error específico

Dime qué mensaje exacto de error te aparece (puedes copiar desde `journalctl` o el `kubectl get nodes / status`), por ejemplo:

- Desde el Pi:

```bash
journalctl -u k3s -n 30
```

- Desde el Zorin:

```bash
sudo k3s kubectl get nodes
sudo k3s kubectl describe node NOMBRE_DEL_PI
```


Con el mensaje concreto puedo decirte si el problema es de certificados, token, red, cgroups u otro y ajustar el comando exacto que necesitas.
<span style="display:none">[^3_10][^3_4][^3_7][^3_8]</span>

<div align="center">⁂</div>

[^3_1]: https://maxdon.tech/posts/k3s-raspberry-pi-worker/

[^3_2]: https://stackoverflow.com/questions/72326132/unable-to-connect-worker-node-to-master-using-k3s

[^3_3]: https://www.picocluster.com/blogs/picocluster-software-engineering/installing-k3s-on-the-raspberry-pi-5-a-step-by-step-guide

[^3_4]: https://www.youtube.com/watch?v=gAjsqaTF0oE

[^3_5]: https://oneuptime.com/blog/post/2026-02-02-k3s-raspberry-pi/view

[^3_6]: https://opensource.com/article/20/3/kubernetes-raspberry-pi-k3s

[^3_7]: https://forums.suse.com/t/worker-node-failing-to-setup-k3s-on-fresh-install/39315

[^3_8]: https://github.com/k3s-io/k3s/issues/6384

[^3_9]: http://www.kevsrobots.com/learn/k3s/07_troubleshooting_install.html

[^3_10]: https://www.theodo.com/blog/k3s-with-raspberry-pi-configuration-connection-and-installation


---

# esto fue en el Raspberry.....  willians@k3s-worker-1:~\$ ./instala-k3s-modo-worker

[INFO]  Finding release for channel stable
[INFO]  Using v1.34.5+k3s1 as release
[INFO]  Downloading hash [https://github.com/k3s-io/k3s/releases/download/v1.34.5%2Bk3s1/sha256sum-arm64.txt](https://github.com/k3s-io/k3s/releases/download/v1.34.5%2Bk3s1/sha256sum-arm64.txt)
[INFO]  Skipping binary downloaded, installed k3s matches hash
[INFO]  Skipping installation of SELinux RPM
[INFO]  Skipping /usr/local/bin/kubectl symlink to k3s, already exists
[INFO]  Skipping /usr/local/bin/crictl symlink to k3s, already exists
[INFO]  Skipping /usr/local/bin/ctr symlink to k3s, already exists
[INFO]  Creating killall script /usr/local/bin/k3s-killall.sh
[INFO]  Creating uninstall script /usr/local/bin/k3s-agent-uninstall.sh
[INFO]  env: Creating environment file /etc/systemd/system/k3s-agent.service.env
[INFO]  systemd: Creating service file /etc/systemd/system/k3s-agent.service
[INFO]  systemd: Enabling k3s-agent unit
Created symlink '/etc/systemd/system/multi-user.target.wants/k3s-agent.service' → '/etc/systemd/system/k3s-agent.service'.
[INFO]  No change detected so skipping service start

Perfecto, el mensaje que ves **no es un error**, es solo la salida del script de instalación de k3s en modo agente (`k3s-agent`), y el último paso dice:

> `[INFO]  No change detected so skipping service start`

Esto significa que:

- El script ya encontró que **k3s (o k3s-agent) ya está instalado** (versión `v1.34.5+k3s1`).
- Los symlinks (`kubectl`, `crictl`, `ctr`) ya existían.
- El servicio `k3s-agent` ya estaba creado y habilitado en systemd.
- Como no detecta cambios, **no inicia el servicio de nuevo** (no hace `systemctl start k3s-agent`).

En resumen: el worker **no está fallando por causa del script**, pero sí puede que el agente **nunca haya empezado** o esté apuntando a otro servidor/token.

***

### 1. ¿El agente está corriendo?

Desde el Raspberry Pi:

```bash
sudo systemctl status k3s-agent
```

Busca:

- Estado: `active (running)`.
- Si está **inactivo, parado o en error**, enciéndelo:

```bash
sudo systemctl start k3s-agent
sudo systemctl enable k3s-agent
```

Y fíjate si aparece algo en los logs:

```bash
sudo journalctl -u k3s-agent -n 30 --no-pager
```

Ahí verás si falla por:

- IP del maestro incorrecta.
- Token inválido.
- Certificado/fecha mal configurada.[^4_3][^4_4]

***

### 2. Verifica que el Pi se vea en el clúster

Desde el **Zorin (maestro)**:

```bash
sudo k3s kubectl get nodes
```

Si el Pi **no aparece** o está en `NotReady`:

- Comprueba que el token usado en el Pi es exactamente el que tienes en `/var/lib/rancher/k3s/server/node-token` del Zorin.
- Comprueba que desde el Pi puedes llegar a la IP del Zorin en el puerto 6443:

```bash
curl -k https://IP_DEL_ZORIN:6443/healthz
```

Debe dar algo como `ok` o `Unauthorized` (no `Connection refused` ni timeout).[^4_6][^4_3]

***

### 3. Si el agente estaba mal configurado

Si el script `./instala-k3s-modo-worker` usa variables de entorno (`K3S_URL`, `K3S_TOKEN`) pero antes ya instalaste k3s como maestro, el Pi puede estar en un estado confuso.

Lo más limpio:

1. En el Raspberry Pi, desinstala el agente actual:

```bash
sudo /usr/local/bin/k3s-agent-uninstall.sh
```

2. Vuelve a ejecutar tu script **con la URL y token correctas** del Zorin:

```bash
K3S_URL=https://IP_DEL_ZORIN:6443 \
K3S_TOKEN=token-copiado-del-zorin \
./instala-k3s-modo-worker
```

3. Y luego inicia y habilita el servicio (si no lo hace solo):

```bash
sudo systemctl start k3s-agent
sudo systemctl enable k3s-agent
```


***

### 4. Dime el estado que ves

Por favor, copia (si puedes) el resultado de:

- En el Pi:

```bash
sudo systemctl status k3s-agent
sudo journalctl -u k3s-agent -n 30
```

- En el Zorin:

```bash
sudo k3s kubectl get nodes
sudo k3s kubectl describe node NOMBRE_DEL_PI
```


Con eso te digo exactamente si el error es de red, token, certificado o si solo hace falta un `systemctl start k3s-agent`.
<span style="display:none">[^4_1][^4_10][^4_2][^4_5][^4_7][^4_8][^4_9]</span>

<div align="center">⁂</div>

[^4_1]: https://github.com/k3s-io/k3s/issues/9071

[^4_2]: https://github.com/k3s-io/k3s/issues/4168

[^4_3]: https://oneuptime.com/blog/post/2026-02-02-k3s-agent-nodes/view

[^4_4]: https://stackoverflow.com/questions/72855183/k3s-agent-node-not-getting-connected-to-master-node-on-aws-instance

[^4_5]: https://github.com/k3s-io/k3s/issues/4239

[^4_6]: https://stackoverflow.com/questions/72326132/unable-to-connect-worker-node-to-master-using-k3s

[^4_7]: https://www.reddit.com/r/k3s/comments/1j4e4y1/cant_get_a_worker_to_join_cluster/

[^4_8]: https://github.com/k3s-io/k3s/issues/1170

[^4_9]: https://forums.suse.com/t/unable-to-join-k3s-agent-to-to-k3s-server/18332

[^4_10]: https://github.com/k3s-io/k3s/issues/9172


---

# funciona.. Se logro hacer el curl, y en el cluster se aprecia:  dk@k3s-cluster-1:~\$ sudo k3s kubectl get nodes

NAME           STATUS   ROLES           AGE   VERSION
k3s-worker-1   Ready    <none>          15s   v1.34.5+k3s1
vit            Ready    control-plane   19m   v1.34.5+k3s1

Perfecto, ¡ya está funcionando!

Tienes lo siguiente:

- `k3s-cluster-1` (Zorin) como **control‑plane** (`vit`),
- `k3s-worker-1` (Raspberry Pi) como **worker** en estado `Ready`.


### 1. Qué significa este estado

- `Status: Ready` en `k3s-worker-1` indica que el nodo acepta pods y está comunicado con el maestro.[^5_1][^5_2]
- El rol `<none>` es normal en k3s; se puede etiquetar después si quieres, pero no afecta el comportamiento.


### 2. Prueba simple para validar el worker

Desde el Zorin (maestro):

```bash
kubectl create ns test
kubectl run app-test --image=nginx --restart=Never -n test
kubectl get pods -A -o wide
```

Comprueba que el pod `app-test` se ha asignado al nodo `k3s-worker-1` (en la columna `NODE`).

Si quieres, en el siguiente paso podemos:

- Configurar un **ingress** para exponer ese Nginx por tu LAN, o
- Crear un primer “ambiente” realista (ej: `dev`, `staging`) con namespaces y labels.

¿Qué te gustaría hacer ahora?

<div align="center">⁂</div>

[^5_1]: https://www.datacamp.com/es/blog/kubernetes-architecture-explained

[^5_2]: https://aws.amazon.com/es/what-is/kubernetes-cluster/


---

# no veo al cluster ni worker desde mi estacion.

Perfecto, el clúster está funcionando pero **tu estación Bluefin no está apuntando al API server remoto**; por eso no ves los nodos ni worker desde allí.

### 1. Verifica que `kubectl` apunta al homelab

En tu Bluefin:

```bash
echo $KUBECONFIG
ls -l ~/.kube/config*
```

- Si el archivo `~/.kube/config` todavía apunta a Minikube o está vacío, no va a ver tu clúster de k3s.
- Lo que necesitas es que `kubectl` use el `kubeconfig` del maestro (`k3s-cluster-1` / Zorin).

***

### 2. Copia el kubeconfig desde el Zorin

Desde tu estación Bluefin:

```bash
scp usuario@IP_DEL_ZORIN:/etc/rancher/k3s/k3s.yaml ~/.kube/config-homelab
```

Luego configura `kubectl` para usarlo:

```bash
export KUBECONFIG=~/.kube/config-homelab
kubectl config get-contexts
kubectl config use-context default
```

Y prueba:

```bash
kubectl get nodes
kubectl get pods -A
```

Si ves:

- `k3s-worker-1` y `vit` → ya estás conectado al clúster real.
- Si falla, revísalo en el siguiente paso.

***

### 3. Ajusta la IP del servidor en el kubeconfig

Abre el fichero:

```bash
nano ~/.kube/config-homelab
```

Busca la entrada:

```yaml
server: https://127.0.0.1:6443
```

Cámbiala por la IP del **Zorin (maestro)**:

```yaml
server: https://IP_DEL_ZORIN:6443
```

Guarda y vuelve a probar:

```bash
kubectl get nodes
```

Con esto ya deberías ver el clúster y el worker desde tu estación Bluefin.

***

### 4. Si sigues sin verlo

Dime:

- Valor de `KUBECONFIG` en Bluefin.
- Salida de:

```bash
kubectl config get-contexts
kubectl get nodes
```


Con eso te ayudo a corregir el contexto exacto o a limpiar el fichero `~/.kube/config` para que solo use tu homelab.
<span style="display:none">[^6_1][^6_10][^6_2][^6_3][^6_4][^6_5][^6_6][^6_7][^6_8][^6_9]</span>

<div align="center">⁂</div>

[^6_1]: https://jesusmarin.dev/blog/creando-cluster-kubernet-k3sup-k3s/

[^6_2]: https://www.ibm.com/docs/es/devops-test-hub/11.0.7?topic=clusters-considerations-using-remote-kubernetes-cluster

[^6_3]: https://www.reddit.com/r/kubernetes/comments/1388hyw/i_can_only_access_my_k3s_service_from_the_node/

[^6_4]: https://siemprelisto.cl/tecnologias/k3s/04-kubectl-esencial/

[^6_5]: https://www.youtube.com/watch?v=Uf33jmDnBug

[^6_6]: https://docs.cloud.google.com/kubernetes-engine/docs/how-to/cluster-access-for-kubectl?hl=es

[^6_7]: http://luispa.com/posts/2025-10-26-k8s-101/

[^6_8]: https://www.reddit.com/r/kubernetes/comments/avonc2/how_to_connect_to_a_remote_kubernetes_cluster/

[^6_9]: https://www.ochobitshacenunbyte.com/2022/04/08/crear-un-entorno-de-kubernetes-en-alta-disponibilidad-con-ubuntu-20-04-y-k3s/

[^6_10]: https://www.youtube.com/watch?v=tbbZ5HURHhU


---

# ❯ ls -l ~/.kube/config*

.rw-------@ 9.6k willians 26 Mar 20:07 /var/home/willians/.kube/config
.rw-------@ 2.9k willians 27 Mar 12:41 /var/home/willians/.kube/config-homelab

Perfecto, ya tienes dos ficheros:

- `~/.kube/config` → probablemente el de Minikube o tu config “default”.
- `~/.kube/config-homelab` → el que copiaste del Zorin (k3s, con `k3s-worker-1` y `vit`).

Lo que pasa ahora es que `kubectl` está usando uno u otro, pero no el que apunta a tu homelab.

***

### 1. Haz que kubectl use `config-homelab`

Desde tu estación Bluefin:

```bash
export KUBECONFIG=~/.kube/config-homelab
```

Y luego verifica:

```bash
kubectl config get-contexts
kubectl get nodes
```

Deberías ver:

```text
NAME           STATUS   ROLES           AGE   VERSION
k3s-worker-1   Ready    <none>          ...   v1.34.5+k3s1
vit            Ready    control-plane   ...   v1.34.5+k3s1
```

Si lo ves, entonces ya estás conectado al clúster desde tu estación.

***

### 2. Renombra el contexto para que sea claro

Puedes renombrar el contexto del homelab para distinguirlo:

```bash
kubectl config rename-context default homelab
kubectl config use-context homelab
kubectl get nodes
```

Con esto, cuando quieras trabajar en el homelab:

```bash
export KUBECONFIG=~/.kube/config-homelab
kubectl config use-context homelab
```

Y cuando quieras volver a Minikube (si lo usas algún día):

```bash
export KUBECONFIG=~/.kube/config
kubectl config use-context minikube
```


***

### 3. Si sigues sin ver el clúster

Si ejecutas:

```bash
export KUBECONFIG=~/.kube/config-homelab
kubectl get nodes
```

y te da error o `No resources found`, dime:

- El mensaje exacto del error.
- El contenido de la línea `server:` adentro de `~/.kube/config-homelab`.

Con eso te ayudo a ajustar la IP del API server o el contexto para que te lo vea correctamente.
<span style="display:none">[^7_1][^7_10][^7_2][^7_3][^7_4][^7_5][^7_6][^7_7][^7_8][^7_9]</span>

<div align="center">⁂</div>

[^7_1]: https://oneuptime.com/blog/post/2026-02-09-kubectl-config-manage-kubeconfig-contexts/view

[^7_2]: https://stackovercoder.es/programming/43643463/how-to-switch-kubectl-clusters-between-gcloud-and-minikube

[^7_3]: https://gurubase.io/g/kubernetes/use-kubectl-with-different-kubeconfigs

[^7_4]: https://pt.linux-console.net/?p=21301

[^7_5]: https://netdevops.me/2024/managing-multiple-kubeconfigs-by-merging-them/

[^7_6]: https://www.sudosu.com.ar/kubernetes/contextos-en-kubernetes/

[^7_7]: https://devopsbug.com/posts/managing-multiple-kubeconfigs/

[^7_8]: https://docs.cloud.google.com/kubernetes-engine/docs/how-to/cluster-access-for-kubectl?hl=es-419

[^7_9]: https://stackoverflow.com/questions/55502859/how-to-have-multiple-kubernetes-config-and-change-quicky-between-them

[^7_10]: https://cubepath.com/docs/docker-kubernetes/comandos-esenciales-de-kubectl


---

# ❯ echo \$KUBECONFIG

/var/home/willians/.kube/config-homelab

Perfecto, el valor de `KUBECONFIG` está bien apuntando a tu clúster homelab:

```bash
echo $KUBECONFIG
/var/home/willians/.kube/config-homelab
```

Pero si aún no ves el clúster ni el worker con `kubectl`, casi seguro es uno de estos tres puntos:

***

### 1. Verifica el contexto actual

Ejecuta:

```bash
kubectl config get-contexts
```

Si sale algo como:

```text
CURRENT   NAME      CLUSTER   AUTHINFO   NAMESPACE
*         default   default   default
```

Eso está bien.
Si el contexto está vacío o no coincide con el cluster `k3s`, usa:

```bash
kubectl config use-context default
# o si has renombrado:
kubectl config use-context homelab
```

Y luego:

```bash
kubectl config view --minify | grep -A5 -B5 cluster
kubectl get nodes
```

Con esto confirmamos que el contexto realmente apunta al clúster k3s.

***

### 2. Revisa la IP del servidor en el kubeconfig

Abre el fichero:

```bash
nano ~/.kube/config-homelab
```

Busca algo parecido a:

```yaml
clusters:
- cluster:
    certificate-authority-data: ...
    server: https://127.0.0.1:6443
  name: default
```

Si el `server` todavía dice `127.0.0.1`, **cámbialo por la IP del Zorin (maestro)**:

```yaml
server: https://IP_DEL_ZORIN:6443
```

Guarda el archivo y vuelve a probar:

```bash
kubectl get nodes
```

Si ahora ves:

```text
NAME           STATUS   ROLES           AGE   VERSION
k3s-worker-1   Ready    <none>          ...   v1.34.5+k3s1
vit            Ready    control-plane   ...   v1.34.5+k3s1
```

ya tienes el clúster completamente visible desde tu estación.

***

### 3. Si usaste scp y sigue igual…

A veces el contenido del `k3s.yaml` del Zorin ya tiene hardcoded `127.0.0.1` o incluso otra IP que no es la pública del nodo.

En el Zorin, revisa:

```bash
sudo cat /etc/rancher/k3s/k3s.yaml | grep server
```

Y si pone `server: https://127.0.0.1:6443`, estás copiando una config que solo funciona en el mismo nodo.

En ese caso, después de copiar el fichero a tu Bluefin, **siempre debes editar** `~/.kube/config-homelab` y poner la IP que el Pi y tu estación pueden alcanzar en la LAN.

***

### 4. Prueba rápida de conectividad al API

Desde tu estación Bluefin:

```bash
curl -k https://IP_DEL_ZORIN:6443/healthz
```

Si da `ok` o `Unauthorized`, el API está accesible.
Si da `Connection refused` o timeout, revisa:

- Firewall/iptables en el Zorin (puerto 6443 TCP abierto en la LAN).
- IP fija del Zorin (que no cambie en el router).

Dime el resultado de:

```bash
kubectl config get-contexts
kubectl get nodes
curl -k https://IP_DEL_ZORIN:6443/healthz
```

y te ayudo a afinar el `kubeconfig` o el networking lo más rápido posible.
<span style="display:none">[^8_1][^8_10][^8_2][^8_3][^8_4][^8_5][^8_6][^8_7][^8_8][^8_9]</span>

<div align="center">⁂</div>

[^8_1]: https://docs.k3s.io/cluster-access

[^8_2]: https://github.com/kelseyhightower/kubernetes-the-hard-way/issues/439

[^8_3]: https://discuss.kubernetes.io/t/unable-to-connect-a-k3s-worker-to-a-remote-k3s-cluster-in-a-cloud-vm/8290

[^8_4]: https://www.reddit.com/r/kubernetes/comments/rei6ml/issue_with_kubectl_get_nodes/

[^8_5]: https://www.reddit.com/r/kubernetes/comments/1388hyw/i_can_only_access_my_k3s_service_from_the_node/

[^8_6]: https://stackoverflow.com/questions/59860987/kubectl-config-view-gives-empty-results

[^8_7]: https://jaehong21.com/posts/k3s/02-access-outside/

[^8_8]: https://stackoverflow.com/questions/61561329/no-configuration-has-been-provided-on-kubernetes-worker-nodes/61561364

[^8_9]: https://stackoverflow.com/questions/67977163/accessing-a-remote-k3s-cluster-via-lens-ide

[^8_10]: https://kubernetes.io/docs/tasks/debug/debug-cluster/troubleshoot-kubectl/


---

# ❯ kubectl config view --minify | grep -A5 -B5 cluster

     1- apiVersion: v1
     2: clusters:
     3: - cluster:
     4-     certificate-authority-data: DATA+OMITTED
     5-     server: [https://127.0.0.1:6443](https://127.0.0.1:6443)
     6-   name: default
     7- contexts:
     8- - context:
     9:     cluster: default
    10-     user: default
    11-   name: homelab
    12- current-context: homelab
    13- kind: Config
    14- users:
    Perfecto, el problema está claro: tu `kubeconfig` todavía apunta a `https://127.0.0.1:6443`, que es la IP del bucle local **del propio Zorin**, no accesible desde tu estación Bluefin. Por eso, aunque `KUBECONFIG` está bien configurado, `kubectl` intenta conectar a “localhost” y no ve el clúster.

***

### 1. Cambia la IP del servidor al IP del Zorin

Desde tu estación Bluefin, edita el fichero:

```bash
nano ~/.kube/config-homelab
```

Busca esta parte:

```yaml
clusters:
- cluster:
    certificate-authority-data: DATA+OMITTED
    server: https://127.0.0.1:6443
  name: default
```

y cambia `127.0.0.1` por la **IP de la LAN del Zorin** (por ejemplo `192.168.0.X`):

```yaml
server: https://IP_DEL_ZORIN:6443
```

Guarda el archivo (en nano: `Ctrl+O` → Intro → `Ctrl+X`).

***

### 2. Verifica que el contexto sigue apuntando bien

Tu salida ya muestra:

```yaml
contexts:
- context:
    cluster: default
    user: default
  name: homelab
current-context: homelab
```

Eso está perfecto; quiere decir que el contexto `homelab` usa el cluster `default` que acabas de modificar.

Ahora ejecuta:

```bash
kubectl config view --minify | grep server
kubectl get nodes
```

Debería salir:

```text
server: https://IP_DEL_ZORIN:6443
```

y luego verás:

```text
NAME           STATUS   ROLES           AGE     VERSION
k3s-worker-1   Ready    <none>          ...     v1.34.5+k3s1
vit            Ready    control-plane   ...     v1.34.5+k3s1
```


***

### 3. Si sigue fallando, prueba el API directamente

Desde tu estación:

```bash
curl -k https://IP_DEL_ZORIN:6443/healthz
```

Si:

- Responde `ok` o `Unauthorized` → el API está accesible, solo el `kubeconfig` estaba mal.
- Da `Connection refused` o timeout → revisa en el Zorin:
    - Que el puerto 6443 esté abierto en la LAN (sin firewall bloqueando).
    - Que el servicio `k3s` esté activo (`sudo systemctl status k3s`).

***

Con ese único cambio de `127.0.0.1` a la IP del Zorin, tu estación debería ver el clúster y el worker exactamente igual que desde el propio Zorin. Si después de este cambio aún ves error con `kubectl get nodes`, pégame el mensaje exacto y te lo arreglamos línea a línea.
<span style="display:none">[^9_1][^9_10][^9_2][^9_3][^9_4][^9_5][^9_6][^9_7][^9_8][^9_9]</span>

<div align="center">⁂</div>

[^9_1]: https://github.com/alexellis/k3sup/issues/43

[^9_2]: https://www.reddit.com/r/kubernetes/comments/1g4i186/junior_dev_trying_to_learn_k8s_using_local_k3s/

[^9_3]: https://stackoverflow.com/questions/61913810/how-to-customize-kubeconfig-server-ip-in-k3s

[^9_4]: https://www.youtube.com/watch?v=IDG0xpTjwuM

[^9_5]: https://community.netfoundry.io/t/how-to-correctly-configure-a-k3s-cluster-to-be-accessible-only-via-a-nic-bind-address-reachable-through-netfoundry-ziti-edge-tunnel/244

[^9_6]: https://stackoverflow.com/questions/37921222/kubectl-connection-to-server-was-refused

[^9_7]: https://github.com/k3s-io/k3s/issues/5423

[^9_8]: https://www.42.mach7x.com/2019/02/25/kubectl-error-the-connection-to-the-server-localhost8080-was-refused-did-you-specify-the-right-host-or-port/

[^9_9]: https://discuss.kubernetes.io/t/unable-to-connect-a-k3s-worker-to-a-remote-k3s-cluster-in-a-cloud-vm/8290

[^9_10]: https://stackoverflow.com/questions/51265483/kubectl-install-error-the-connection-to-the-server-localhost8080-was-refused/51284099


---

# ❯ curl -k https://vit:6443/healthz

{
"kind": "Status",
"apiVersion": "v1",
"metadata": {},
"status": "Failure",
"message": "Unauthorized",
"reason": "Unauthorized",
"code": 401
}%

Perfecto, el resultado de `curl` es exactamente lo que esperamos ver **cuando el API está accesible pero no autenticado**:

```text
{
  "kind": "Status",
  "status": "Failure",
  "message": "Unauthorized",
  "code": 401
}
```

Esto significa:

- ✅ El API de k3s del Zorin es alcanzable desde tu estación.
- ✅ El dominio/hostname `vit` se resuelve bien (o tienes `vit` en `/etc/hosts`).
- 🔐 No estás mandando credenciales, pero `kubectl` debería usarlas vía el `kubeconfig` (certificado + cluster).

***

### 1. Confirma que el `kubeconfig` ahora usa `vit`

En tu `~/.kube/config-homelab`, asegúrate de que el `server` apunta a `vit` y **no a `127.0.0.1`**:

```yaml
clusters:
- cluster:
    certificate-authority-data: ...
    server: https://vit:6443
  name: default
```

Guarda el archivo, y luego:

```bash
kubectl config view --minify | grep server
kubectl get nodes
```

Si el `server` ya está en `https://vit:6443` y aun así `kubectl get nodes` da algún error como:

- `Unauthorized`
- `Forbidden`
- `You must be logged in to the server`

es casi seguro que el fichero `k3s.yaml` que copiaste no trae el contenido correcto para uso remoto, o se está usando otra config por defecto.

***

### 2. Solución “limpia” (recomendada)

Haz esto en tu estación Bluefin:

```bash
# 1. Borra el contexto viejo (si quieres empezar limpio)
mv ~/.kube/config-homelab ~/.kube/config-homelab.backup

# 2. Vuelve a copiar el kubeconfig del Zorin
scp usuario@IP_DEL_ZORIN:/etc/rancher/k3s/k3s.yaml ~/.kube/config-homelab
```

Luego edita el fichero:

```bash
nano ~/.kube/config-homelab
```

y cambia:

```yaml
server: https://127.0.0.1:6443
```

por:

```yaml
server: https://vit:6443
# o si prefieres IP fija:
server: https://IP_DEL_ZORIN:6443
```

Guarda, y vuelve a probar:

```bash
export KUBECONFIG=~/.kube/config-homelab
kubectl config get-contexts
kubectl get nodes
```

Ahora deberías ver claramente el clúster con `k3s-worker-1` y `vit` desde tu estación.

***

### 3. Si sigues viendo errores con `kubectl`

Dime exactamente el mensaje de error de:

```bash
kubectl get nodes
```

y si el `kubectl config view --minify` ahora muestra el `server: https://vit:6443` correctamente.

Con eso te indico si el problema es:

- falta de certificados en el config remoto, o
- sobra otro `KUBECONFIG` / `~/.kube/config` interfiriendo.
<span style="display:none">[^10_1][^10_10][^10_2][^10_3][^10_4][^10_5][^10_6][^10_7][^10_8][^10_9]</span>

<div align="center">⁂</div>

[^10_1]: https://drdroid.io/stack-diagnosis/k3s-unauthorizedaccess

[^10_2]: https://github.com/k3s-io/k3s/issues/2869

[^10_3]: https://github.com/k3s-io/k3s/issues/4820

[^10_4]: https://support.scc.suse.com/s/kb/Error-Unable-to-authenticate-the-request-err-invalid-bearer-token-Token-has-been-invalidated-in-K3s-or-RKE2?language=es

[^10_5]: https://blog.differentpla.net/blog/2023/10/29/k3s-unauthorized/

[^10_6]: https://github.com/k3s-io/k3s/issues/5250

[^10_7]: https://www.reddit.com/r/kubernetes/comments/liajef/trying_to_access_k3s_cluster_from_another_machine/

[^10_8]: https://github.com/k3s-io/k3s/issues/11208

[^10_9]: https://stackoverflow.com/questions/74603633/k3s-allow-unauthenticated-access-to-oidc-endpoints

[^10_10]: https://www.reddit.com/r/homelab/comments/1k788ap/issues_with_k3s_and_authentik_for_cluster_oidc/


---

# pudiera deberse a permiso?  ya que la instruccion scp fallo. Entonces para solucionar, he copiado el archivo k3s.yaml en el directorio del usuario y reasignado permisos del usuario y despues obtuve a la estacion.

Sí, puede estar relacionado con permisos, pero **no tanto por el contenido del fichero** como por el flujo con el que lo copiaste.

### 1. Qué pasó con lo que hiciste

Cuando `scp` falló:

- No te copió el archivo `/etc/rancher/k3s/k3s.yaml` directamente.
- Lo que luego haces (copiarlo a un directorio de usuario, cambiar permisos, y después usar `scp` desde allí) puede estar bien, **siempre que el contenido del `k3s.yaml` sea el correcto** (con el certificado, el token y el `server` ajustado a `vit` o a la IP).

Si el contenido del `k3s.yaml` que ahora está en tu Bluefin:

- sigue siendo el mismo que el del Zorin (mismo `certificate-authority-data`, `client-certificate-data`, `client-key-data`, etc.),
- y ya cambiaste `server: 127.0.0.1` por `server: https://vit:6443`

entonces **los permisos del lado del Zorin ya no importan** para tu estación; lo único que importa es que el fichero en `~/.kube/config-homelab` sea accesible para tu usuario y esté bien formado.

***

### 2. Con qué debes asegurarte en el Bluefin

En tu estación, revisa el estado del fichero:

```bash
ls -l ~/.kube/config-homelab
```

Debe ser algo así:

```text
-rw------- 1 willians willians 3xxx Mar 27 12:xx /var/home/willians/.kube/config-homelab
```

Si el archivo está:

- bien formado (YAML válido, mismo contenido que el `k3s.yaml` del Zorin, pero con `server` corregido),
- y `export KUBECONFIG=~/.kube/config-homelab` está seteado,
- y el `curl -k https://vit:6443/healthz` responde `Unauthorized` (no `Connection refused`),

entonces cualquier problema de permisos en el Zorin **ya no es el motivo del fallo en `kubectl`** desde tu estación.

***

### 3. Qué probar ahora

Si después de todo lo que has hecho:

```bash
export KUBECONFIG=~/.kube/config-homelab
kubectl get nodes
```

te sigue dando error, **no es por permisos del Zorin**, sino por uno de estos tres puntos:

1. El `k3s.yaml` que copiaste está **dañado** (faltan campos, se cortó el base64 del certificado, se perdió `client-key-data`, etc.).
2. El `server` en `~/.kube/config-homelab` todavía apunta a `127.0.0.1`.
3. Se está usando otro `KUBECONFIG` o `~/.kube/config` y no estás viendo realmente el `config-homelab`.

***

### 4. Recomendación práctica ahora

Para descartar que el problema venga del flujo manual de copiado y permisos, haz esto en tu estación:

```bash
# 1. Borra el config actual (backup)
mv ~/.kube/config-homelab ~/.kube/config-homelab.corrupt

# 2. Vuelve a copiar el original del Zorin, usando sudo en el Zorin si hace falta
scp usuario@IP_DEL_ZORIN:/etc/rancher/k3s/k3s.yaml ~/.kube/config-homelab

# 3. Edita el fichero y cambia el server
nano ~/.kube/config-homelab
# busca:
#   server: https://127.0.0.1:6443
# y pon:
#   server: https://vit:6443
```

Guarda y prueba:

```bash
export KUBECONFIG=~/.kube/config-homelab
kubectl config get-contexts
kubectl get nodes
```

Si ahora ves el clúster, **el problema no era permiso**, sino que el `k3s.yaml` que habías copiado antes estaba incompleto o mal formado.

Si aún falla, pégame el mensaje exacto de `kubectl get nodes` y te digo si es problema de certificado, contexto o algo más específico.
<span style="display:none">[^11_1][^11_10][^11_2][^11_3][^11_4][^11_5][^11_6][^11_7][^11_8][^11_9]</span>

<div align="center">⁂</div>

[^11_1]: https://github.com/k3s-io/k3s/issues/7272

[^11_2]: https://www.reddit.com/r/k3s/comments/1drh9dn/help_i_lost_possibility_to_connect_via_kubectl/

[^11_3]: https://dev.to/danuw/aio-unable-to-read-etcrancherk3sk3syaml-please-start-server-with-write-kubeconfig-mode-to-modify-permissions-3le7

[^11_4]: https://github.com/k3s-io/k3s/issues/4869

[^11_5]: https://dev.to/olymahmud/resolving-the-k3s-config-file-permission-denied-error-27e5

[^11_6]: https://github.com/k3s-io/k3s/discussions/5564

[^11_7]: https://github.com/k3s-io/k3s/discussions/7278

[^11_8]: https://stackoverflow.com/questions/37921222/kubectl-connection-to-server-was-refused

[^11_9]: https://www.reddit.com/r/k3s/comments/ysfh8n/is_it_possible_to_access_k3s_as_non_root_user/

[^11_10]: https://github.com/k3s-io/k3s/issues/3862


---

# ❯ kubectl get pods -A -o wide

NAMESPACE     NAME                                      READY   STATUS              RESTARTS   AGE   IP          NODE           NOMINATED NODE   READINESS GATES

```
kube-system   coredns-695cbbfcb9-87zns                  1/1     Running             0          55m   10.42.0.5   vit            <none>           <none>
```

```
kube-system   helm-install-traefik-2rww8                0/1     Completed           2          55m   10.42.0.3   vit            <none>           <none>
```

```
kube-system   helm-install-traefik-crd-nbdcc            0/1     Completed           0          55m   10.42.0.2   vit            <none>           <none>
```

```
kube-system   local-path-provisioner-546dfc6456-hqzs8   1/1     Running             0          55m   10.42.0.4   vit            <none>           <none>
```

```
kube-system   metrics-server-c8774f4f4-559t5            1/1     Running             0          55m   10.42.0.6   vit            <none>           <none>
```

```
kube-system   svclb-traefik-9b0b738b-nlrtb              2/2     Running             0          51m   10.42.0.7   vit            <none>           <none>
```

```
kube-system   svclb-traefik-9b0b738b-rl582              2/2     Running             0          36m   10.42.0.2   k3s-worker-1   <none>           <none>
```

```
kube-system   traefik-788bc4688c-ghwbv                  1/1     Running             0          51m   10.42.0.8   vit            <none>           <none>
```

```
test          app-test                                  0/1     ContainerCreating   0          66s   <none>      vit            <none>           <none>
```

¡Genial, ya está todo funcionando!

Ahora:

- Tu estación Bluefin **ve el clúster**.
- `k8s-worker-1` (Raspberry Pi) es **worker activo**.
- El pod `app-test` está en proceso de creación en el nodo `vit` (Zorin).


### 1. Observa el estado del pod

Ejecuta:

```bash
kubectl get pods -A
```

Verás que el estado del pod `app-test` pasará de `ContainerCreating` a `Running` una vez que se descargue la imagen de `nginx` y se inicie el contenedor.

### 2. Verifica la IP del pod

Cuando el pod esté en `Running`, ejecuta:

```bash
kubectl get pods -n test
kubectl describe pod app-test -n test
```

Verás la IP del pod y el nodo donde está ejecutándose.

### 3. Si quieres que el pod se ejecute en el Raspberry Pi

Asigna el pod al nodo `k3s-worker-1`:

```bash
kubectl label node k3s-worker-1 kubernetes.io/role=worker
kubectl patch deployment app-test -n test -p '{"spec":{"template":{"spec":{"nodeSelector":{"kubernetes.io/role":"worker"}}}}}'
```

Luego, escala el deployment:

```bash
kubectl scale deployment app-test -n test --replicas=3
```

Esto creará 3 pods de `app-test` spreadeados por los nodos.

***

Ahora puedes seguir con:

- Exponer `app-test` mediante un servicio de tipo `LoadBalancer` o `NodePort`.
- Configurar un ingress para acceder desde tu navegador local.

¿Qué quieres hacer a continuación?

---

# kubectl scale deployment app-test -n test --replicas=3

Error from server (NotFound): deployments.apps "app-test" not found

Perfecto, el error es muy claro:

```text
Error from server (NotFound): deployments.apps "app-test" not found
```

Esto significa que **no existe ningún `Deployment` llamado `app-test`**, solo un pod `app-test` creado con `kubectl run` (de tipo `Pod`, no `Deployment`).

### 1. ¿Qué estás usando realmente?

Cuando hiciste:

```bash
kubectl run app-test --image=nginx --restart=Never -n test
```

Kubernetes creó un **Pod directo**, no un `Deployment`.
Por eso:

```bash
kubectl get deployments -n test
```

devuelve `No resources found`, y no puedes escalarlo con `kubectl scale deployment`.

### 2. Opciones: mantenerlo como Pod o convertirlo en Deployment

#### Opción A: usar un Deployment real (recomendado)

```bash
# 1. Borra el pod directo
kubectl delete pod app-test -n test

# 2. Crea un Deployment
kubectl create deployment app-test --image=nginx -n test

# 3. Escálalo
kubectl scale deployment app-test -n test --replicas=3

# 4. Verifica
kubectl get pods -n test
kubectl get deployment -n test
```

Ahora sí podrás usar `kubectl scale` y `kubectl rollout` sobre el Deployment.

#### Opción B: si quieres jugar solo con Pods

Puedes seguir creando pods directos, pero no podrás escalarlos con `kubectl scale deployment`:

```bash
kubectl run app-test --image=nginx --restart=Always -n test
```

Con `--restart=Always` Kubernetes se encargará de recrear el pod si falla, pero sigue siendo un solo Pod, no un Deployment replicado.

***

¿Quieres que pasemos ahora a crear un **Deployment + Service + Ingress** para que el Nginx sea accesible desde tu navegador en la LAN?
<span style="display:none">[^13_1][^13_10][^13_2][^13_3][^13_4][^13_5][^13_6][^13_7][^13_8][^13_9]</span>

<div align="center">⁂</div>

[^13_1]: https://stackoverflow.com/questions/79039481/error-from-server-notfound-deployments-apps-auth-depl-not-found

[^13_2]: https://discuss.kubernetes.io/t/error-executing-the-kubectl-command-to-obtain-deployments/11322

[^13_3]: https://stackoverflow.com/questions/64299456/kubernetes-error-from-server-notfound-deployments-apps-kube-verify-not-fou

[^13_4]: https://kubernetes.io/docs/tasks/debug/debug-cluster/troubleshoot-kubectl/

[^13_5]: https://devops-diaries.beehiiv.com/p/error-18-deployment-not-found-error-troubleshoot-and-fix

[^13_6]: https://www.copado.com/resources/blog/5-tips-for-troubleshooting-kubernetes-deployments

[^13_7]: https://github.com/kubernetes/kubernetes/issues/73689

[^13_8]: https://www.groundcover.com/learn/kubernetes/deployment-not-updating

[^13_9]: https://github.com/dotnet/tye/issues/559

[^13_10]: https://www.blinkops.com/blog/troubleshooting-your-kubernetes-service-deployment


---

# si

Perfecto, vamos a montar un escenario completo: **Deployment + Service + Ingress**, para que el Nginx sea accesible desde tu navegador en la LAN.

***

### 1. Crear el Deployment

Desde tu estación Bluefin:

```bash
# 1. Asegúrate de que el pod anterior está eliminado
kubectl delete pod app-test -n test --ignore-not-found

# 2. Crea el Deployment
kubectl create deployment app-test --image=nginx -n test

# 3. Escálalo a 3 réplicas
kubectl scale deployment app-test -n test --replicas=3

# 4. Verifica que los pods están en ejecución
kubectl get pods -n test -o wide
```

Ahora tendrás 3 pods `app-test-*` listos.

***

### 2. Crear el Service

```bash
# 1. Crea el Service tipo ClusterIP
kubectl expose deployment app-test -n test --port=80 --target-port=80 --type=ClusterIP

# 2. Verifica el Service
kubectl get svc -n test
```

Obtendrás algo como:

```text
NAME       TYPE        CLUSTER-IP     PORT(S)   AGE
app-test   ClusterIP   10.43.123.45   80/TCP    1m
```

Esto permite que Traefik (o cualquier otro ingress) enrute el tráfico hacia el Service `app-test`.

***

### 3. Crear el Ingress

k3s trae Traefik integrado. Si aún no está habilitado, asegúrate de que el módulo `traefik` está activo:

```bash
# En el Zorin (maestro)
sudo kubectl get pods -n kube-system
```

Deberías ver `traefik-*` en ejecución.

Luego, crea el Ingress:

```bash
cat <<EOF | kubectl apply -f -
apiVersion: networking.k8s.io/v1
kind: Ingress
metadata:
  name: app-test-ingress
  namespace: test
spec:
  rules:
  - host: app-test.local
    http:
      paths:
      - path: /
        pathType: Prefix
        backend:
          service:
            name: app-test
            port:
              number: 80
EOF
```

Este Ingress expone `app-test` bajo el host `app-test.local` en el puerto 80.

***

### 4. Acceder desde tu navegador

1. En tu estación, añade el host `app-test.local` a `/etc/hosts`:

```bash
echo "IP_DEL_ZORIN  app-test.local" | sudo tee -a /etc/hosts
```

2. Abre tu navegador y ve a:

```
http://app-test.local
```


Deberías ver la página por defecto de Nginx.

***

### 5. Si quieres un FQDN bonito

Crea un hostname en tu router (DNS interno) para que `app-test.local` resuelva automáticamente a la IP del Zorin. Así podrás acceder desde cualquier dispositivo en tu LAN.

¿Quieres que profundicemos en:

- Persistencia de datos con `PersistentVolume` y `PersistentVolumeClaim`
- Monitorización con Prometheus y Grafana
- Autoscaling Horizontal Pod (HPA)

---

# antes de proseguir, explicame por parte por que se creo el deployment con tipo CulsterIP, que es Traefik, que es Ingres... Diferencias entre estos dos.

Perfecto, vamos por partes porque está muy bien que quieras entender el “por qué”, no solo el comando.

***

### 1. Por qué creamos el `Deployment` con `ClusterIP`

Cuando hiciste:

```bash
kubectl expose deployment app-test -n test --port=80 --target-port=80 --type=ClusterIP
```

creaste un **Service** de tipo `ClusterIP`.

Qué significa eso:

- `ClusterIP` es el tipo de servicio **por defecto** en Kubernetes.[^15_1][^15_7]
- Asigna una **IP interna** del clúster (por ejemplo, `10.43.x.x`) y reenvía el tráfico a los pods del `Deployment` (`app-test`).
- Esa IP solo es accesible **dentro del clúster** (desde otros pods, o usando `kubectl port-forward`, pero no directamente desde tu navegador).[^15_10][^15_1]

En resumen:

- `ClusterIP` = el “backend”: hace que tus pods sean un punto de red estable dentro del clúster.
- No expone nada al exterior; solo organiza la comunicación interna (pod ↔ pod, pod ↔ ingress).[^15_7][^15_1]

***

### 2. ¿Qué es Traefik?

Traefik es un **ingress controller** / proxy inverso integrado en k3s por defecto.[^15_2][^15_5][^15_8]

Sus roles principales:

- Escucha el puerto 80/443 en los nodos.
- Detecta automáticamente los objetos `Ingress` del clúster.[^15_5][^15_2]
- Enruta el tráfico entrante (por dominio o ruta) hacia el `Service` correcto (por ejemplo, `app-test` con tipo `ClusterIP`).

Es decir:

- Tú defines el `Service` (ej: `app-test` tipo `ClusterIP`).
- Tú defines el `Ingress` que dice: “todo lo que llegue a `app-test.local` debe ir al `Service app-test`”.
- Traefik se encarga de hacer esa conexión en la práctica.[^15_6][^15_9][^15_2]

***

### 3. ¿Qué es un `Ingress`?

`Ingress` es un **objeto de Kubernetes** que define cómo quieres exponer servicios HTTP/HTTPS hacia el exterior.[^15_9][^15_6]

En tu ejemplo:

```yaml
apiVersion: networking.k8s.io/v1
kind: Ingress
metadata:
  name: app-test-ingress
  namespace: test
spec:
  rules:
  - host: app-test.local
    http:
      paths:
      - path: /
        pathType: Prefix
        backend:
          service:
            name: app-test
            port:
              number: 80
```

aquí defines:

- “Todo el tráfico que llegue a `http://app-test.local` debe ir al `Service` llamado `app-test` en el puerto 80”.
- Es **routing basado en dominio y ruta**, no por IP/puertos planos.[^15_3][^15_6][^15_9]

El `Ingress` no levanta un servidor HTTP él solo; **requiere un ingress controller** (como Traefik) para que realmente funcione.[^15_6][^15_9]

***

### 4. Diferencia clave entre `ClusterIP` y `Ingress`

| Aspecto | `ClusterIP` (Service) | `Ingress` (con Traefik / otro controller) |
| :-- | :-- | :-- |
| Capa de red | L3/L4 (IP y puerto) | L7 (HTTP/HTTPS, host, path, cookies, TLS, etc.) |
| Dónde se ve | Solo dentro del clúster | Desde el exterior (navegador, otros servicios) |
| Exposición a internet/LAN | No, solo internamente | Sí, si el ingress controller está expuesto. |
| Uso típico | Backends internos, comunicación entre pods | Frontends de apps web, APIs HTTP expuestas. |
| Quién lo consume en tu escenario | Traefik (ingress controller) | Tu navegador, clientes HTTP externos. |

- `ClusterIP` es el **punto de entrada interno** hacia tus pods.
- `Ingress` es la **regla de entrada externa** que apunta a ese `ClusterIP` usando nombres de host y rutas.[^15_1][^15_3][^15_6]

***

### 5. Relación práctica en tu homelab actual

En lo que ya montamos:

1. `Deployment app-test`
→ define 3 pods corriendo `nginx`.
2. `Service app-test` tipo `ClusterIP`
→ da un punto de red estable dentro del clúster para esos pods.
3. `Ingress app-test-ingress`
→ le dice a Traefik: “todo `app-test.local` va a `Service app-test`”.
4. `Traefik` (ingress controller)
→ escucha el puerto 80 en los nodos y aplica esa regla.

Así tu navegador (en tu LAN) ve `http://app-test.local` como si fuera un servicio público, aunque por debajo todo pasa por el `ClusterIP` interno del clúster.

***

Cuando quieras, en el siguiente paso podemos explicar también:

- Diferencia entre `NodePort` y `LoadBalancer` frente a `ClusterIP` + `Ingress`.
- Cómo usar Ingress con TLS/HTTPS en tu homelab.
<span style="display:none">[^15_4]</span>

<div align="center">⁂</div>

[^15_1]: https://platzi.com/cursos/kubernetes/tipos-de-servicios-clusterip-nodeport-loadbalancer/

[^15_2]: https://usuarioperu.com/2023/08/19/traefik-proxy-inverso-para-kubernetes-y-microservicios/

[^15_3]: https://stackoverflow.com/questions/54367674/kubernetes-ingress-vs-service-with-externalips

[^15_4]: https://www.youtube.com/watch?v=FMykDJ920Mw

[^15_5]: https://www.enmilocalfunciona.io/traefik-como-ingresscontroller-en-kubernetes/

[^15_6]: https://kubernetes.io/es/docs/concepts/services-networking/ingress/

[^15_7]: https://www.maquinasvirtuales.eu/kubernetes-servicios-clusterip-ingress-nodeport-y-loadbalancer/

[^15_8]: https://www.trevenque.es/blog/posts-cloud/traefik-proxy-inverso-y-balanceador-de-carga-parte1/

[^15_9]: https://www.ibm.com/es-es/think/topics/kubernetes-ingress

[^15_10]: https://kubernetes.io/docs/concepts/services-networking/cluster-ip-allocation/


---

# prosigamos

Perfecto, sigamos profundizando: ahora tienes el **Deployment + Service ClusterIP + Ingress + Traefik** funcionando, así que vamos a ampliar el escenario y aclarar conceptos clave.

***

### 1. Relación completa de piezas en tu homelab

Para que quede claro “de corrido”:

1. **Deployment `app-test`**
    - Define 3 pods con `nginx`.
    - Es el “plano de workload” (quiénes corren la aplicación).
2. **Service `app-test` tipo `ClusterIP`**
    - Crea una IP interna (`10.43.x.x`) que enruta al conjunto de pods.
    - Solo es accesible **dentro del clúster** (por otros pods o por Traefik).
3. **Ingress `app-test-ingress`**
    - Dice: “todo lo que llegue a `app-test.local` va al `Service app-test` por el puerto 80”.
    - Es una regla de enrutamiento HTTP, no un servidor independiente.
4. **Traefik (ingress controller)**
    - Está escuchando en el puerto 80 (y/o 443) en los nodos del clúster.
    - Lee ese `Ingress` y en la práctica implementa el proxy inverso para `app-test.local`.

Así, tu navegador habla con Traefik (en un nodo del clúster) y Traefik habla con el `Service app-test` (tipo `ClusterIP`), que a su vez balancea entre los 3 pods.

***

### 2. Diferencias: `NodePort` y `LoadBalancer` vs `ClusterIP` + `Ingress`

Ahora vamos a los “vecinos” de `ClusterIP`:

- `ClusterIP`
    - Solo para comunicación **interna** del clúster.
    - Ideal para backends, servicios internos, y para usar con `Ingress`.
- `NodePort`
    - Publica el servicio en **un puerto alto en cada nodo** (ej: `31000`).
    - Puedes acceder con `http://IP_DEL_NODO:31000`.
    - Sencillo, pero poco organizado si tienes muchas apps (muchos puertos) y no es tan “limpio” como un dominio.
- `LoadBalancer`
    - En la nube (AWS, GCP, etc.) pide un **equilibrador de carga externo**.
    - En home‑lab k3s se simula con herramientas como `MetalLB` o `kube‑vip`.
    - Asigna una IP externa y enlaza esa IP a los nodos del clúster.

En tu homelab, la combinación:

- `Deployment` + `ClusterIP` + `Ingress` + `Traefik`

es **la forma más “product‑like”** y flexible:

- Expones servicios por **nombre de host** (`app-test.local`, `api.test.local`, etc.), no por puertos.
- Es fácil de escalar manteniendo una sola IP pública y muchos dominios internos.

***

### 3. Cómo sería el siguiente paso técnico

Si quieres seguir evolucionando este homelab, ahora puedes:

- **Añadir HTTPS/TLS** al Ingress (con certificados auto‑firmados o Let’s Encrypt) usando Traefik.
- **Crear más servicios** en otros namespaces (`staging`, `production`) para simular distintos ambientes.
- **Configurar persistencia** si quieres montar algo con datos (por ejemplo, una app PHP con MySQL o una app con volúmenes locales).
- **Montar observabilidad**:
    - Prometheus + Grafana + Loki para ver métricas, logs y dashboards.

***

Dime qué prefieres hacer ahora:

- Explicarte paso a paso cómo **exponer `app-test` con HTTPS/TLS** usando Traefik.
- O preparar el terreno para **montar un segundo servicio** (por ejemplo, una app PHP + MySQL) en un namespace distinto.

---

# ahora voy a crear el manifiesto para crear el Ingres... que contina despues_?

Perfecto, ahora mismo estás en este punto:

1. Tienes un **Deployment** (`app-test`) corriendo 3 pods de `nginx`.
2. Tienes un **Service** `app-test` tipo `ClusterIP`.
3. Vas a crear un **Ingress** en formato manifiesto YAML.

### 1. Ejemplo de manifiesto `Ingress` para tu caso

Crea un archivo `ingress-app-test.yaml`:

```yaml
# ingress-app-test.yaml
apiVersion: networking.k8s.io/v1
kind: Ingress
metadata:
  name: app-test-ingress
  namespace: test
spec:
  rules:
  - host: app-test.local
    http:
      paths:
      - path: /
        pathType: Prefix
        backend:
          service:
            name: app-test
            port:
              number: 80
```

Aplica el manifiesto:

```bash
kubectl apply -f ingress-app-test.yaml
```

Y verifica:

```bash
kubectl get ingress -n test
kubectl describe ingress app-test-ingress -n test
```

Si ves la IP del nodo en la columna `ADDRESS` y `app-test.local` como `HOSTS`, estás listo para el siguiente paso.

***

### 2. ¿Qué continúa después de crear el Ingress?

Con el `Ingress` creado, lo siguiente suele ser:

#### 2.1. Asegurar acceso desde tu LAN

- Si el `ADDRESS` no aparece en el `Ingress`, Traefik puede estar escuchando en `127.0.0.1` o en una IP no accesible.
- En ese caso, confirma que Traefik está expuesto en un endpoint público (por ejemplo, un `Service` de tipo `NodePort` o `LoadBalancer` dependiendo de tu configuración).


#### 2.2. Acceso por navegador

- Añade a `/etc/hosts` en tu estación:

```bash
echo "IP_DEL_ZORIN  app-test.local" | sudo tee -a /etc/hosts
```

- Abre tu navegador y ve a `http://app-test.local`.
- Deberías ver la página de inicio de Nginx.


#### 2.3. Añadir HTTPS/TLS (opcional)

Si quieres dar un paso más “productivo”, puedes:

- Configurar Traefik para usar certificados de Let’s Encrypt (en la nube) o auto‑firmados (en tu homelab).
- Crear un `Secret` con el certificado TLS y actualizar el `Ingress` con:

```yaml
spec:
  tls:
  - hosts:
    - app-test.local
    secretName: app-test-tls
```


***

### 3. Resultado esperado

Después de crear el Ingress con el manifiesto:

- Tu navegador accederá al `Service app-test` (tipo `ClusterIP`) a través de `app-test.local`.
- Traefik se encargará de enrutar el tráfico HTTP al Service correcto.

¿Quieres que profundicemos ahora en cómo configurar HTTPS con Traefik o en cómo montar un segundo servicio (`staging` o `production`) en otro namespace?
<span style="display:none">[^17_1][^17_10][^17_2][^17_3][^17_4][^17_5][^17_6][^17_7][^17_8][^17_9]</span>

<div align="center">⁂</div>

[^17_1]: https://docs.cloud.google.com/kubernetes-engine/distributed-cloud/bare-metal/docs/how-to/create-service-ingress?hl=es-419

[^17_2]: https://www.returngis.net/2021/07/traefik-como-ingress-controller-en-kubernetes/

[^17_3]: https://www.josedomingo.org/pledin/2018/12/kubernetes-acceso-ingress/

[^17_4]: https://www.enmilocalfunciona.io/traefik-como-ingresscontroller-en-kubernetes/

[^17_5]: https://pabpereza.dev/docs/cursos/kubernetes/conceptos_basicos_y_kubectl_manifiestos_yaml_en_kubernetes

[^17_6]: https://www.reddit.com/r/kubernetes/comments/1oxl5q0/replace_ingress_nginx_with_traefik/

[^17_7]: https://kubernetes.io/es/docs/concepts/services-networking/ingress/

[^17_8]: https://www.youtube.com/watch?v=UO9YrNj-Y88

[^17_9]: https://www.reddit.com/r/kubernetes/comments/vtrnh0/help_create_a_kubernetes_ingress_resource_on_your/

[^17_10]: https://doc.traefik.io/traefik/v3.0/providers/kubernetes-ingress/


---

# ❯ k get ingress -n test

NAME               CLASS     HOSTS            ADDRESS                       PORTS   AGE
app-test-ingress   traefik   app-test.local   192.168.2.107,192.168.2.200   80      78s

Perfecto, ¡el `Ingress` ya está listo y Traefik lo está exponiendo en tu LAN!

### 1. Qué significa la salida del `Ingress`

```bash
NAME               CLASS     HOSTS            ADDRESS                       PORTS   AGE
app-test-ingress   traefik   app-test.local   192.168.2.107,192.168.2.200   80      78s
```

- `CLASS: traefik` → indica que el **ingress controller** que está manejando este `Ingress` es Traefik.
- `HOSTS: app-test.local` → el dominio que Traefik debe enrutar al `Service app-test`.
- `ADDRESS: 192.168.2.107,192.168.2.200` → son las IPs de los nodos del clúster donde está escuchando Traefik (por ejemplo, el Zorin y el Raspberry Pi).
- `PORTS: 80` → el puerto HTTP público de Traefik.

Esto quiere decir que **cualquier nodo** de tu clúster puede responder a `http://app-test.local` por el puerto 80.

***

### 2. Qué haces ahora (desde tu estación)

#### 2.1. Asigna el host en `/etc/hosts` (si solo tienes un nodo público)

En tu estación:

```bash
sudo su -
echo "192.168.2.107  app-test.local" >> /etc/hosts
```

(asume que `192.168.2.107` es la IP del Zorin; si quieres usar otra, cambia la IP)

#### 2.2. Verifica el acceso

```bash
# Probar acceso al servidor Traefik
curl http://app-test.local

# Ver en tu navegador
firefox http://app-test.local
```

Deberías ver la página de inicio de Nginx.

#### 2.3. Si no ves nada

- Revisa:

```bash
kubectl logs -n kube-system -l app=traefik
```

para ver errores de Traefik.

***

### 3. Resultado esperado

- El tráfico HTTP a `app-test.local` llega a Traefik en el nodo donde escucha el puerto 80.
- Traefik enruta ese tráfico al `Service app-test` (tipo `ClusterIP`).
- El `Service` balancea entre los 3 pods `app-test-*` del Deployment.

***

### 4. ¿Qué quieres hacer a continuación?

- Configurar **HTTPS/TLS** para `app-test.local`.
- Crear un **segundo servicio** (`staging` o `production`) en otro namespace.
- Montar **persistencia** con un `PersistentVolume` y `PersistentVolumeClaim` para un servicio con datos.

Dime qué quieres hacer siguiente y te preparo el manifiesto y los pasos.
<span style="display:none">[^18_1][^18_10][^18_2][^18_3][^18_4][^18_5][^18_6][^18_7][^18_8][^18_9]</span>

<div align="center">⁂</div>

[^18_1]: https://kubernetes.io/es/docs/concepts/services-networking/ingress/

[^18_2]: https://www.redeszone.net/tutoriales/redes-cable/conflicto-direcciones-ip-solucionarlo/

[^18_3]: https://www.johndev.co/posts/kubernetes/ingress/

[^18_4]: https://www.reddit.com/r/kubernetes/comments/m2yfn2/why_do_i_get_two_different_ip_addresses_for_the/

[^18_5]: https://www.reddit.com/r/kubernetes/comments/ykvc0a/what_is_the_hosts_mean_in_kubectl_get_ingress_for/

[^18_6]: https://docs.cloud.google.com/kubernetes-engine/docs/how-to/troubleshooting-and-ops?hl=es-419

[^18_7]: https://www.youtube.com/watch?v=7KYv6eQMOwI

[^18_8]: https://learn.microsoft.com/es-es/troubleshoot/windows-server/networking/troubleshoot-multiple-records-being-registered-with-the-same-ip-address

[^18_9]: https://www.ibm.com/es-es/think/topics/ingress-controller

[^18_10]: https://www.wiki.mcneel.com/es/zoo/multipleipaddresses


---

# montar persistencia

Perfecto, ahora vamos a montar **persistencia de datos** en tu homelab, usando `PersistentVolume` (PV) y `PersistentVolumeClaim` (PVC) con almacenamiento local en uno de tus nodos (por ejemplo, el Zorin o el Raspberry Pi).

***

### 1. Objetivo

Queremos que:

- Un pod (puede ser `app-test` o un nuevo pod) tenga un **directorio persistente** en el nodo.
- Ese almacenamiento sobreviva al pod: si el pod se borra, reboota o se reprograma, los datos siguen en el disco del nodo.

En homelab solemos usar `local` (disco del nodo) como tipo de `PersistentVolume`.

***

### 2. Paso 1: crear un directorio en el nodo que usará el PV

Elige un nodo que tenga disco decente (por ejemplo, el Zorin).

En el **Zorin**:

```bash
sudo mkdir -p /mnt/pv-app-test
sudo chown -R 33:33 /mnt/pv-app-test   # UID/GID de nginx, ajusta si tu app usa otro user
```

- `/mnt/pv-app-test` será el directorio que el PV expondrá.
- 33 es el `UID` típico de `www-data`/nginx; si usas otra app, cambia por el user correcto.

***

### 3. Paso 2: crear el `PersistentVolume` (PV)

En tu estación Bluefin, crea `pv-app-test.yaml`:

```yaml
apiVersion: v1
kind: PersistentVolume
metadata:
  name: pv-app-test
spec:
  capacity:
    storage: 500Mi
  accessModes:
    - ReadWriteOnce
  persistentVolumeReclaimPolicy: Retain
  storageClassName: local-storage
  local:
    path: /mnt/pv-app-test
  nodeAffinity:
    required:
      nodeSelectorTerms:
        - matchExpressions:
            - key: kubernetes.io/hostname
              operator: In
              values:
                - vit   # nombre del nodo Zorin; usa `kubectl get nodes` para confirmar
```

Aplica:

```bash
kubectl apply -f pv-app-test.yaml
kubectl get pv
```

Deberías ver `pv-app-test` en estado `Available`.

***

### 4. Paso 3: crear el `PersistentVolumeClaim` (PVC)

En tu estación, crea `pvc-app-test.yaml`:

```yaml
apiVersion: v1
kind: PersistentVolumeClaim
metadata:
  name: pvc-app-test
  namespace: test
spec:
  accessModes:
    - ReadWriteOnce
  resources:
    requests:
      storage: 100Mi
  storageClassName: local-storage
```

Aplica:

```bash
kubectl apply -f pvc-app-test.yaml
kubectl -n test get pvc
```

Debería decir `Bound` y referenciar `pv-app-test`.

***

### 5. Paso 4: montar el PVC en un pod/deployment

Ahora vamos a modificar el `Deployment app-test` para que use ese PVC.

1. Borra el deployment actual:

```bash
kubectl -n test delete deployment app-test
```

2. Crea `deployment-app-test.yaml`:

```yaml
apiVersion: apps/v1
kind: Deployment
metadata:
  name: app-test
  namespace: test
spec:
  replicas: 1
  selector:
    matchLabels:
      app: app-test
  template:
    metadata:
      labels:
        app: app-test
    spec:
      containers:
      - name: nginx
        image: nginx:latest
        ports:
        - containerPort: 80
        volumeMounts:
        - name: app-data
          mountPath: /usr/share/nginx/html
      volumes:
      - name: app-data
        persistentVolumeClaim:
          claimName: pvc-app-test
```

3. Aplica:

```bash
kubectl -n test apply -f deployment-app-test.yaml
kubectl -n test get pods
kubectl -n test describe pod app-test-...
```


- Ahora el pod monta `/mnt/pv-app-test` del nodo en `/usr/share/nginx/html` dentro del contenedor.

***

### 6. Verificar que la persistencia funciona

1. Entra al pod:

```bash
kubectl -n test exec -it app-test-... -- bash
echo "<h1>Test persistente</h1>" > /usr/share/nginx/html/index.html
exit
```

2. Borra el pod:

```bash
kubectl -n test delete pod app-test-...
```


El Deployment creará un nuevo pod.
Repite el `exec` y revisa el `index.html`; el contenido debe seguir ahí.

***

### 7. ¿Qué sigue después de esto?

Con persistencia funcionando, puedes:

- Probar con otra app (por ejemplo, `php-fpm` + `nginx` con `/var/www/html` persistente).
- Usar `local-path` o `StorageClass` dinámicas si tu distro/k3s las soporta.
- Añadir backup de ese directorio (por ejemplo, `rsync` o `restic` hacia otro nodo).

***

Dime si quieres que ajustemos esto para:

- que el **Raspberry Pi** sea el nodo con el PV (en lugar del Zorin), o
- usar un `StorageClass` dinámico en lugar de PV manual.
<span style="display:none">[^19_1][^19_10][^19_2][^19_3][^19_4][^19_5][^19_6][^19_7][^19_8][^19_9]</span>

<div align="center">⁂</div>

[^19_1]: https://blog.vikki.in/creating-persistent-local-storage-volume-in-kubernetes/

[^19_2]: https://destine-data-lake-docs.data.destination-earth.eu/en/latest/dedl-big-data-processing-services/Islet-service/kubernetes/Kubernetes-Persistent-Volume-Claims/Kubernetes-Persistent-Volume-Claims.html

[^19_3]: https://kubernetes.io/blog/2019/04/04/kubernetes-1.14-local-persistent-volumes-ga/

[^19_4]: https://kubernetes.io/docs/tasks/configure-pod-container/configure-persistent-volume-storage/

[^19_5]: https://vocon-it.com/2018/12/20/kubernetes-local-persistent-volumes/

[^19_6]: https://www.netapp.com/learn/cvo-blg-kubernetes-persistent-volume-claims-explained/

[^19_7]: https://kubernetes.io/docs/concepts/storage/persistent-volumes/

[^19_8]: https://spacelift.io/blog/kubernetes-persistent-volumes

[^19_9]: https://www.cooltux.net/doku.php?id=it-wiki%3Akubernetes%3Alocal_persistant_storage

[^19_10]: https://zesty.co/finops-glossary/kubernetes-persistent-volume-claim/


---

# tengo una curiosidad. Es posible que el raspberry sea un cluster de respaldo.  He notado que al apagar el raspberry por unos minutos, se quedo un pod con el status "Terminating"

Buena observación, y tu curiosidad es totalmente válida: vamos a ir por dos frentes:

1. **¿Puede el Raspberry Pi ser un cluster de respaldo?**
2. **¿Por qué un pod se quedó en `Terminating` cuando apagaste el Pi?**

***

### 1. ¿El Raspberry Pi puede ser un cluster de respaldo?

Sí, **en teoría sí**, pero hay que aclarar qué significa “respaldo” en tu caso:

- Podrías usar el Raspberry Pi 3 (por ejemplo) como **cluster secundario** para:
    - Recuperar estado de aplicaciones desde backups (con tools como `Velero` + PVC + `CSI` o almacenamiento externo).[^20_1][^20_5]
    - Tener réplicas de algunos servicios críticos (si tu Pi fuera más robusto y tuvieras más recursos).

Pero hay limitaciones importantes:

- El Pi 3 es **muy limitado en CPU y RAM** para un clúster de propósito general.[^20_7][^20_9]
- Si el Pi **cae varios minutos**, el clúster puede perder sincronización y el nodo puede quedar `NotReady` o incluso requerir limpieza manual.[^20_4][^20_8]

En un escenario “ideal” de respaldo:

- El clúster principal sería el Zorin (o un cluster más potente).
- El Pi sería un clúster secundario que se **restaura a partir de backups** (no un nodo “sincronizado en vivo”).[^20_5][^20_1]

***

### 2. ¿Por qué el pod se quedó en `Terminating`?

Cuando apagas el Raspberry Pi:

1. El nodo pasa a `NotReady`.
2. El Kubernetes control‑plane (Zorin) marca los pods que estaban en ese nodo como `Terminating`.
3. Normalmente el API server borra esos pods, pero **el kubelet en el nodo nunca respondió la confirmación de terminación** (porque el nodo estaba apagado).

Esto es precisamente el fenómeno que describe la documentación:

- El nodo se queda en el estado `NotReady` hasta que se recupera o se evicta.
- Los pods que estaban en ese nodo pueden quedar en `Terminating` porque el API server no recibe el `DELETE` del kubelet.[^20_6][^20_4]

La solución típica:

- Forzar la eliminación de los pods en el nodo que no responde:

```bash
kubectl delete pod POD_NAME --grace-period=0 --force
```

- Si el nodo permanece inactivo, puedes evictarlo del cluster.

***

### 3. ¿Qué hacer prácticamente?

En tu caso:

- Si el Pi está como **worker**, y el Zorin como **maestro**, el clúster debería seguir disponible aunque el Pi esté apagado.
- Si el Pi está como **maestro** (no recomendado para respaldo), el clúster puede quedar inestable.

Para convertir el Pi en “cluster de respaldo”:

- Mantén el Pi **como clúster independiente**, pero **no lo uses como nodo principal**.
- Usa herramientas como `Velero` para hacer backup de recursos y PVCs desde el Zorin.
- En caso de falla, restaura el estado en el Pi o en un clúster secundario.[^20_1][^20_5]

***

### 4. ¿Qué quieres hacer ahora?

- Quieres que te explique cómo **forzar la eliminación de pods en `Terminating`**.
- Quieres que te ayude a diseñar un **plan de respaldo** con `Velero` para tu clúster actual.

Dime cuál prefieres y te preparo el manifiesto y los pasos.
<span style="display:none">[^20_10][^20_2][^20_3]</span>

<div align="center">⁂</div>

[^20_1]: https://www.shogan.co.uk/kubernetes/kubernetes-backup-on-raspberry-pi/

[^20_2]: https://kubeblocks.io/blog/how-to-fix-pod-stuck-in-terminating-status

[^20_3]: https://github.com/ljfranklin/k8s-pi

[^20_4]: https://stackoverflow.com/questions/35453792/pods-stuck-in-terminating-status

[^20_5]: https://www.reddit.com/r/selfhosted/comments/xvazf6/backing_up_a_raspberry_pi_kubernetes_cluster/

[^20_6]: https://kodekloud.com/blog/kubernetes-pods-stuck-in-terminating-a-resolution-guide/

[^20_7]: https://rpi4cluster.com/k3s-storage-setting/

[^20_8]: https://www.reddit.com/r/kubernetes/comments/1115cn7/help_pod_is_stuck_in_terminating_state_even/

[^20_9]: https://www.ecliptik.com/blog/2017/Raspberry-Pi-Kubernetes-Cluster/

[^20_10]: https://oneuptime.com/blog/post/2026-02-09-debug-pods-terminating-force-delete/view


---

# donde debo ejecutar la instruccion para eliminar el pod.?

Debes ejecutar el comando de eliminación **desde tu estación Bluefin**, apuntando al clúster con `kubectl`.

La instrucción es:

```bash
kubectl delete pod NOMBRE_DEL_POD -n NAMESPACE --grace-period=0 --force
```

En tu escenario:

1. Identifica el pod que está en `Terminating`:

```bash
kubectl get pods -A | grep Terminating
```

Verás algo como:

```text
NAMESPACE     NAME           READY   STATUS         RESTARTS   AGE
test          app-test-xxx   0/1     Terminating    0          10m
```

2. Elimínalo a la fuerza:

```bash
kubectl delete pod app-test-xxx -n test --grace-period=0 --force
```


- `--grace-period=0` → no espera a que el nodo confirme el apagado del pod.
- `--force` → fuerza la liberación del nombre del pod en el API server, aunque el nodo no responda.

Con eso el pod desaparece del listado aunque el nodo (Raspberry Pi) estuviera fuera de la red cuando se inició la eliminación.

Si el problema persiste o quieres que te ayude a revisar el nodo después de que vuelva a encenderse, dímelo y te preparo el siguiente paso.
<span style="display:none">[^21_1][^21_10][^21_2][^21_3][^21_4][^21_5][^21_6][^21_7][^21_8][^21_9]</span>

<div align="center">⁂</div>

[^21_1]: https://www.linuxteaching.com/article/force_delete_evicted_terminated_pods_in_kubernetes

[^21_2]: https://es.linux-console.net/?p=19934

[^21_3]: https://cduser.com/pique-29-como-eliminar-de-manera-masiva-pods-en-un-especifico-estado-en-kubernetes/

[^21_4]: https://kubernetes.io/es/docs/tasks/run-application/force-delete-stateful-set-pod/

[^21_5]: https://arhivo.com/es/chapters/18651-how-to-fix-kubernetes-pods-stuck-in-terminating-status

[^21_6]: https://www.reddit.com/r/kubernetes/comments/1eb4lhc/unable_to_delete_pod_in_terminating_state/

[^21_7]: https://stackoverflow.com/questions/35453792/pods-stuck-in-terminating-status

[^21_8]: https://kubernetes.io/docs/tasks/run-application/force-delete-stateful-set-pod/

[^21_9]: https://dev.to/imjoseangel/kubernetes-pods-stuck-in-terminating-a-resolution-guide-2jh3

[^21_10]: https://kodekloud.com/blog/kubernetes-pods-stuck-in-terminating-a-resolution-guide/


---

# he compredido que el Raspberry no tiene las especificaciones minima para cubrir lo que deseo.

Perfecto, tu comprensión es correcta: el **Raspberry Pi 3**, aunque genial para aprender, **no tiene las especificaciones mínimas** para:

- mantener un clúster estable con varios workloads,
- servir como maestro o como nodo crítico en producción,
- ni como “backup cluster” con buena resiliencia.

Su poca RAM, el almacenamiento en microSD (menos confiable) y el cuello de botella de CPU lo hacen claramente **un entorno de laboratorio/estudio**, no de producción.

***

### 1. Qué rol le queda al Raspberry Pi en tu diseño

Tu Raspberry Pi sigue siendo muy útil si le das el rol de:

- **Worker no crítico**
    - Montar servicios de prueba, demos, labos de networking, etc.
    - Soportar pocos pods, sin datos críticos ni disponibilidad SLO exigente.
- **Nodo “desaprobable”**
    - Puedes apagarlo sin que el clúster falle si el Zorin es el único maestro y el único nodo realmente productivo.
    - El control‑plane y los servicios esenciales se mantienen en el Zorin.
- **Entorno de aprendizaje de HA futuro**
    - Cuando elijas hardware más potente, puedes usar el Pi 3 como “tercer nodo” en un clúster de ejemplo, para probar tolerancia a fallos y recuperación.

***

### 2. Cómo ajustar tu arquitectura actual

Propongo:

1. **Zorin Core 18**
    - Maestro + worker principal.
    - Aquí corren servicios “cruciales”: Traefik, DNS interno, apps con datos importantes, backups, etc.
2. **Raspberry Pi 3**
    - Solo worker opcional, sin etiquetas de `critical` ni datos importantes.
    - Si el Pi se cae o apaga, aceptas que sus pods puedan tardar en reprogramarse o no volver hasta que el nodo esté de vuelta.
3. **Tu estación Bluefin**
    - Solo `kubectl` y `KUBECONFIG` remoto.
    - Ningún clúster local, como tenías pensado.

Con esto ya:

- ganas simplicidad y estabilidad;
- sigues aprendiendo Kubernetes de forma “cercana a producción”;
- el Raspberry Pi no condiciona el rendimiento ni la disponibilidad de tu entorno.

***

### 3. Si quieres seguir escalando el homelab

Cuando sientas que el Zorin solo también se queda corto, puedes:

- agregar un segundo nodo **más potente** (PC viejo, mini‑PC, etc.)
- mover el Raspberry Pi a un rol educativo puro (ej: enseñanza, experimentos, pruebas de `NetworkPolicy`, `labels`, `taints/tolerations`).

Si te interesa, en el siguiente paso puedo ayudarte a definir unos **límites de recursos** (`resources.requests/limits`) y `taints` para que el Pi solo reciba workloads “no críticos”.

¿Quieres que armemos esa configuración práctica para el Raspberry Pi como worker no crítico?

---

# Te comento a modo informativo, que he configurado el directorio / del sistema para ser montado en un SSD externo, y actualmente esta operativo.

¡Esa es una gran mejora! Migrar el `/` del sistema a un SSD externo resuelve justo los dos puntos débiles que tenía el Raspberry Pi 3:

- **Almacenamiento**: el SSD es mucho más rápido y duradero que la microSD, lo que mejora el arranque, el rendimiento de contenedores y la resiliencia general.
- **Estabilidad**: el sistema operativo ya no depende de la pequeñez de la microSD y puede manejar mejor el uso intensivo de almacenamiento.

Ahora tu Raspberry Pi pasa de ser:

> “máquina de laboratorio limitada por el almacenamiento”
a
> “máquina de laboratorio con almacenamiento decente”.

***

### 1. ¿Qué implica esto para tu homelab?

Con `/` montado en SSD:

- El nodo `k3s-worker-1` es más estable y puede soportar mejor la carga de Kubernetes.
- Los pods, logs, imágenes de contenedores, etc., se almacenan en un dispositivo más rápido y confiable.
- Aún así, el límite de RAM y CPU del Pi 3 sigue siendo el cuello de botella, así que sigue siendo **un nodo no crítico**.

***

### 2. Roles recomendados para el Pi con SSD

### Rol 1: Worker no crítico

- Deja el Pi funcionando como worker, pero:
    - Aplícale un `taint` para que solo reciba workloads de test/demo:

```bash
kubectl taint node k3s-worker-1 role=lab:NoSchedule
```

    - En tus deployments de producción, añade tolerancias solo a `tolerations` para el nodo principal (Zorin).
- Ventaja: el Pi se usa para probar configuraciones, políticas, `NetworkPolicy`, etc., sin afectar la estabilidad del clúster.


### Rol 2: Ambiente de experimentación pura

- Aísla el Pi en su propio namespace o como nodo de prueba:
    - Prueba `PersistentVolume` locales, `NodePort` servicios, `Ingress` con Traefik, etc., sin preocuparte por caídas.
- Si el Pi falla, el Zorin sigue en marcha y el clúster principal funciona.


### Rol 3: Nodo de backup (no crítico)

- Usa el Pi para simular backup de estado, por ejemplo montando un `PersistentVolume` en el SSD y probando el proceso de backup/restore.
- El SSD te permite simular mejor escenarios de “backup de PVCs” aunque el nodo no sea crítico.

***

### 3. Aspectos a vigilar con el SSD

- **Montaje automático**: asegúrate de que el SSD se monta automáticamente en el arranque del Pi (en `/etc/fstab`).
- **Consumo de recursos**: el Pi sigue teniendo límites de RAM y CPU, así que no sobrecargues el nodo con demasiados pods pesados.
- **Backups**: configura backups periódicos del clúster y de los PVCs críticos en el Zorin (no dependas exclusivamente del Pi).

***

### 4. ¿Qué quieres hacer ahora?

- Quieres que te ayude a configurar el Pi como **worker no crítico** con `taints` y `tolerations`.
- Quieres que te ayude a diseñar un **plan de backup** para tu homelab con el SSD en el Pi.
- O quieres que te ayude a **experimentar** con configuraciones de `PersistentVolume` y `NetworkPolicy` en el Pi.

Indícame qué te interesa y te preparo el manifiesto y los pasos.

---

# tal como se ha configurado el entorno, puedo apagar el Raspberry sin causar fallo ni que se detenga todo lo que hemos realizado como ensayos.?

Sí, **puedes apagar el Raspberry Pi sin que se detenga todo lo que has hecho**, siempre que el **Zorin (maestro + worker principal)** siga encendido y funcione correctamente.

### 1. Qué pasa cuando apagas el Raspberry Pi

- El nodo `k3s-worker-1` pasa a estado `NotReady` o `Unreachable`.
- Kubernetes detecta que el nodo está fuera de la red y empieza a reprogramar los pods que estaban en ese nodo (si siguen necesarios) en el **Zorin** (u otros nodos disponibles).
- Los pods en el Zorin (incluido Traefik, el `Ingress` y el `Deployment` que creamos) **siguen funcionando**, por lo que:
    - Tu página `app-test.local` sigue accesible.
    - El clúster sigue operativo.


### 2. Qué debes observar

- Algunos pods que estaban en el Pi pueden quedarse en `Terminating` mientras el nodo esté apagado, pero el **control‑plane del Zorin sigue disponible**.
- Si el nodo tarda en volver o se queda apagado, puedes:
    - forzar el borrado de esos pods con `kubectl delete pod ... --grace-period=0 --force`, como ya viste.
    - eventualmente, borrar el nodo del clúster con `kubectl delete node k3s-worker-1` si confirmas que no va a volver temporalmente.


### 3. Recomendación de uso

- Trata el Raspberry Pi como **worker no crítico** y opcional:
    - Podrías apagarlo cuando quieras (por ejemplo, por la noche) sin afectar el servicio principal.
    - Cuando lo vuelvas a encender, el nodo se reincorporará al clúster y podrá recibir nuevamente pods (si quieres).

En resumen: **sí, puedes apagar el Raspberry Pi sin que “se caiga” el entorno** que hemos construido; solo afecta a los pods que estaban ejecutándose en ese nodo, pero no al clúster en general.
<span style="display:none">[^24_1][^24_10][^24_2][^24_3][^24_4][^24_5][^24_6][^24_7][^24_8][^24_9]</span>

<div align="center">⁂</div>

[^24_1]: https://kubernetes.io/es/docs/tasks/run-application/force-delete-stateful-set-pod/

[^24_2]: https://stackoverflow.com/questions/53323637/kubernetes-allow-container-to-exit-without-pod-restart

[^24_3]: https://lab.bricogeek.com/tutorial/como-apagar-y-encender-una-raspberry-pi-con-un-pulsador-externo

[^24_4]: https://kubernetes.io/docs/concepts/workloads/pods/pod-lifecycle/

[^24_5]: https://www.reddit.com/r/docker/comments/1llqec3/how_to_pause_stop_kubernetes_without_stoping/

[^24_6]: https://news.ycombinator.com/item?id=21713206

[^24_7]: https://forums.raspberrypi.com/viewtopic.php?t=115793

[^24_8]: https://forums.raspberrypi.com/viewtopic.php?t=387919

[^24_9]: https://kubernetes.io/es/docs/concepts/configuration/pod-overhead/

[^24_10]: https://www.youtube.com/shorts/ouU4yDCzawI


---

# voy a apagar el Raspberry para enfocarme en el cluster

Perfecto, esa es una decisión muy buena para tu homelab actual.

Sí, puedes apagar el Raspberry Pi **sin que se rompa el cluster** ni lo que ya hemos hecho, mientras:

- **el Zorin siga encendido** (maestro + worker principal),
- y el `KUBECONFIG` de tu Bluefin apunte siempre al Zorin.

***

### 1. Pasos antes de apagar el Raspberry Pi

Para hacerlo de forma limpia, desde tu estación:

1. Verifica el estado del nodo:

```bash
kubectl get nodes
```

Deberías ver:

```text
k3s-worker-1   Ready      <none>          ...
vit            Ready      control-plane   ...
```

2. (Opcional, muy limpito)
Si tienes pods en el Pi que quieres mover al Zorin, puedes:
    - Hacer `kubectl delete pod ...` para que el Deployment los reemplace en el Zorin,
    - o escalarlo en el Zorin con `kubectl scale deployment ... --replicas=2`.
3. Apaga el Raspberry Pi **de forma normal** (desde la consola):

```bash
sudo shutdown -h now
```

o si usas un servicio de apagado rápido:

```bash
sudo systemctl poweroff
```


***

### 2. Qué pasa cuando lo apagas

- El nodo `k3s-worker-1` pasa a `NotReady` / `Unreachable`.
- El Zorin sigue siendo el único nodo usable, pero el clúster funciona.
- Si el nodo vuelve a encenderse y reconectarse a la LAN, el Zorin lo reintegrará automáticamente al cluster.
- Si el nodo no vuelve (por ejemplo, porque lo dejas apagado varios días), algunos pods antiguos pueden quedar en `Terminating` hasta que los borres a la fuerza con `--grace-period=0 --force` (como ya viste).

***

### 3. Qué sigue para ti

Ahora puedes:

- Focus total en el **Zorin como único nodo funcional**: optimizar Traefik, probar `Ingress` con varias apps, jugar con `Namespace`, `RBAC`, `StatefulSet`, `PersistentVolume`, etc.
- Dejar el Raspberry Pi como **worker opcional**: lo enciendes solo cuando quieras probar algo específico (redes, tolerancias, labels, etc.), y lo apagas cuando no lo necesitas.

Si quieres, en el próximo mensaje te propongo un **mini‑roadmap de labs** para que practiques en tu cluster con Zorin sin depender del Pi, por ejemplo:

- Hacer un `Deployment` con `livenessProbe` y `readinessProbe`.
- Crear un `Secret`/`ConfigMap` y usarlo en un pod.
- Experimentar con `taints` y `tolerations` pensando en usar el Pi en el futuro como worker “no crítico”.

Dime si quieres que te prepare ese roadmap de ejercicios paso a paso.
<span style="display:none">[^25_1][^25_10][^25_2][^25_3][^25_4][^25_5][^25_6][^25_7][^25_8][^25_9]</span>

<div align="center">⁂</div>

[^25_1]: https://carry0987.github.io/Linux-Script/K3s/CommandList.html

[^25_2]: https://thedf.cc/blog/how-to-close-a-k3s-cluster-325.html

[^25_3]: https://k3s.guide/docs/kubernetes/common-kubernetes-commands/

[^25_4]: https://askthedev.com/question/what-is-the-recommended-procedure-for-safely-shutting-down-a-k3s-cluster-running-on-a-local-machine/

[^25_5]: https://github.com/k3s-io/k3s/issues/4647

[^25_6]: https://github.com/k3s-io/k3s/discussions/9865

[^25_7]: https://www.digitalocean.com/community/tutorials/how-to-setup-k3s-kubernetes-cluster-on-ubuntu

[^25_8]: https://github.com/k3s-io/k3s/discussions/4319

[^25_9]: https://docs.k3s.io/cluster-access

[^25_10]: https://docs.k3s.io/upgrades/killall


---

# que soporta al Cluster? es decir en terminos de driver y container-runtime

Tu clúster sigue vivo gracias a varias capas de software que están corriendo **en el nodo Zorin (o en cada nodo)**, no en tu estación. Vamos a desglosarlo en dos grandes bloques: **container runtime** y **drivers**.

***

### 1. Qué es el *container runtime* que soporta el cluster

En Kubernetes, el **container runtime** es el programa que realmente ejecuta los contenedores dentro de los pods.
k3s, como Kubernetes, habla con ese runtime a través de la **Container Runtime Interface (CRI)**.[^26_2][^26_4]

En la práctica, en tu setup actual casi seguro estás usando una de estas dos opciones:

- `containerd` (muy común en k3s y Kubernetes moderno).
- o `CRI‑O` (mucho más típico en OpenShift, pero compatible también con Kubernetes).[^26_9][^26_2]

El runtime se comunica con el **kubelet** (el agente del nodo) vía CRI y, a bajo nivel, usa ejecutables como `runc` para lanzar los contenedores en un entorno de namespaces/cgroups.[^26_3][^26_4]

Resumen:

- El clúster **no depende de Docker** como tal, sino de un runtime que cumpla con la CRI (por ejemplo, `containerd` u `CRI‑O`).
- k3s trae por defecto un runtime compatible ya incluido; tú no lo instalas aparte en la mayoría de casos.[^26_4][^26_2]

***

### 2. Qué es el *driver* que interviene: cgroup driver y network

En Kubernetes, el término “driver” suele referirse a:

#### a) **cgroup driver**

- Controla cómo se gestionan los recursos de CPU/memoria de los pods (cgroups v1 o v2).
- Lo usa tanto el kubelet como el container runtime (`containerd`/`CRI‑O`).
- En sistemas modernos con `systemd`, suele usarse el driver `systemd` (no `cgroupfs`), para que el init y el runtime usen el mismo gestor de cgroups.[^26_2][^26_4]


#### b) **network drivers / CNI**

- Kubernetes necesita un **CNI plugin** (Calico, Flannel, Cilium, etc.) para que los pods puedan comunicarse entre nodos.
- k3s instala por defecto uno de ellos (por ejemplo, `flannel` o `cilium`) que gestiona:
    - IP de cada pod,
    - rutas entre nodos,
    - reglas de red (iptables / nftables).[^26_8][^26_2]

Estos drivers son los que permiten que tu pod `app-test` en el Zorin se vea como si tuviera una IP propia y pueda conversar con el nodo y con Traefik.

***

### 3. Relación con tu homelab actual

En tu caso:

- El **Zorin**:
    - Ejecuta el **container runtime** (probablemente `containerd` en modo CRI).
    - Usa un **cgroup driver** (`systemd` o `cgroupfs` según tu SO y versión).
    - Monta un **CNI plugin** que permite la red de pods y servicios.
- Tu **estación Bluefin**:
    - No ejecuta ningún runtime ni driver: solo `kubectl` consumiendo la API del maestro (Zorin).
- El **Raspberry Pi** (cuando lo enciendes):
    - Lleva el mismo runtime y CNI, y se une al clúster usando la red CNI.

***

### 4. ¿Qué puedes hacer con esta info ahora?

Con esto en mente puedes, por ejemplo:

- Mirar qué runtime exactamente está usando k3s en el Zorin:

```bash
sudo k3s kubectl get node -o wide
sudo k3s kubectl describe node vit
```

En la `Container Runtime Version` verás algo como `containerd://...` o `cri-o://...`.
- Ver el cgroup driver usado en el nodo (en el `kubelet` y el runtime) si quieres asegurarte de que ambos usan `systemd` y no chocan.

Si quieres, dime la salida de:

```bash
sudo k3s kubectl describe node vit | grep -i runtime
```

y te explico línea a línea qué significa y si tu configuración es “product‑like” o no.
<span style="display:none">[^26_1][^26_10][^26_5][^26_6][^26_7]</span>

<div align="center">⁂</div>

[^26_1]: https://qualt.io/kubernetes-depreca-el-soporte-a-docker-como-container-runtime/

[^26_2]: https://kubernetes.io/docs/setup/production-environment/container-runtimes/

[^26_3]: https://stackoverflow.com/questions/63624117/what-is-the-difference-between-drivers-and-container-runtimes-in-kubernetes

[^26_4]: https://kubernetes.io/es/docs/concepts/architecture/cri/

[^26_5]: https://cubepath.com/en/docs/docker-kubernetes/instalacion-de-kubernetes-con-kubeadm

[^26_6]: https://kubernetes.io/docs/concepts/containers/runtime-class/

[^26_7]: https://support.huaweicloud.com/intl/es-us/qs-cce/cce_qs_0008.html

[^26_8]: https://kubernetes.io/es/docs/concepts/configuration/manage-resources-containers/

[^26_9]: https://cri-o.io

[^26_10]: https://people.wikimedia.org/~jayme/k8s-docs/v1.16/es/docs/reference/glossary/


---

# como puedo averiguar eso en el cluster?

Puedes averiguar qué **container runtime** y qué tipo de *cgroup/network* usa el cluster **directamente desde el nodo maestro (Zorin)** y también desde tu estación con `kubectl`.

Voy a darte comandos concretos para tu escenario con k3s.

***

### 1. Ver qué container runtime está usando el cluster

Desde el **Zorin** (nodo maestro):

```bash
sudo k3s kubectl get node -o wide
```

Saldrá algo así:

```text
NAME   STATUS   ROLES           AGE   VERSION        EXTERNAL-IP   OS-IMAGE             KERNEL-VERSION   CONTAINER-RUNTIME
vit    Ready    control-plane   1h    v1.34.5+k3s1   <none>        Ubuntu 20.04 LTS     5.4.0-xx-generic   containerd://1.6.19
```

- El campo `CONTAINER-RUNTIME` te dice si es `containerd://...` o `crio://...` (u otro).
- En k3s casi siempre es `containerd` integrado (no Docker).[^27_7]

Para ver también el subtipo de runtime desde el nodo:

```bash
sudo k3s crictl version
```

Te muestra la versión de `containerd` o `CRI‑O` usada.

***

### 2. Ver qué *cgroup driver* está usando

Desde el **Zorin**:

```bash
# 1. Ver los argumentos del kubelet
sudo k3s kubectl describe node vit | grep -A5 -B5 cgroup
```

Busca algo como:

- `--cgroup-driver=systemd`
- o `--cgroup-driver=cgroupfs`

En la mayoría de instalaciones modernas con `systemd` se usa `--cgroup-driver=systemd`.[^27_2]

También puedes ver directamente el fichero de configuración de k3s:

```bash
sudo cat /etc/rancher/k3s/config.yaml | grep -i cgroup
```

Allí verás si el `cgroup driver` está fijado explícitamente.

***

### 3. Ver qué driver de red (CNI) está usando

En tu caso, k3s monta por defecto un CNI interno; para verlo:

```bash
# 1. Ve qué pods de CNI están corriendo
sudo k3s kubectl get pods -n kube-system | grep -i flannel\|cilium\|calico
```

Ejemplos de salida:

- `kube-flannel` → usa **Flannel** como CNI.
- `cilium-agent` → usa **Cilium**.

También puedes ver el tipo de overlay/reglas de red:

```bash
# Ver interfaces de red de pods
sudo ip link show
sudo ip route show
```

Si ves interfaces `veth*` y tunneles como `flannel` o `cilium` es porque el **CNI plugin** (Flannel/Cilium/etc.) está gestionando la red de pods.[^27_11]

***

### 4. ¿Qué soporta tu cluster a nivel de almacenamiento?

Para el **almacenamiento** (PersistentVolume, etc.) k3s suele usar:

- `local` (discos locales del nodo),
- o, si lo instalaste, `rancher`/`local-path` (k3s incluye un `StorageClass` `local-path` por defecto para PV locales).[^27_8][^27_12]

Para verlo:

```bash
sudo k3s kubectl get storageclass
```

Verás algo como:

```text
NAME                  PROVISIONER           AGE
local-path (default)  rancher.io/local-path 1h
```

Eso indica que el cluster está usando `local-path` para PVCs locales.

***

### 5. Resumen de qué soporta tu cluster

En tu homelab con k3s:

- **Container runtime**: casi seguro `containerd` (compatible con CRI).
- **Cgroup driver**: `systemd` (o `cgroupfs`, lo verás en `describe node` o `config.yaml`).
- **Network driver (CNI)**: `flannel` o `cilium` (lo ves en `kube-system` y `ip link/route`).
- **Storage driver**: `local` / `local-path` para PV/PVC locales.

Si quieres, copia aquí la salida de:

```bash
sudo k3s kubectl get node -o wide
sudo k3s kubectl describe node vit | grep -A2 -B2 cgroup
sudo k3s kubectl get storageclass
```

y te explico cada línea en castellano y cómo interpretarla “como si fueras admin en producción”.
<span style="display:none">[^27_1][^27_10][^27_3][^27_4][^27_5][^27_6][^27_9]</span>

<div align="center">⁂</div>

[^27_1]: https://stackoverflow.com/questions/68684356/how-to-check-the-k8s-k3s-pod-status-programmatically-with-containerd-runtime

[^27_2]: https://github.com/k3s-io/k3s/discussions/7265

[^27_3]: https://academy.pentaho.com/pentaho-11-installation-en/pentaho-11-installation-es/instalacion/contenedores/k3s

[^27_4]: http://luispa.com/posts/2025-10-26-k8s-101/

[^27_5]: https://oneuptime.com/blog/post/2026-02-02-k3s-development-environment/view

[^27_6]: https://docs.forescout.com/bundle/forescout-cloud-connector-configuration-guide/page/gitdoc-cloud/Cloud/forescout-cloud-connector-configuration-guide/verify_nodes_on_the_primary_and_k_s_deployment_19947_d178e1_d179e1.html

[^27_7]: https://kubernetes.io/docs/tasks/administer-cluster/migrating-from-dockershim/find-out-runtime-you-use/

[^27_8]: https://openaccess.uoc.edu/server/api/core/bitstreams/8ebb7d21-aa66-44de-8e71-b4189fe20557/content

[^27_9]: https://www.ochobitshacenunbyte.com/2022/04/08/crear-un-entorno-de-kubernetes-en-alta-disponibilidad-con-ubuntu-20-04-y-k3s/

[^27_10]: https://riunet.upv.es/server/api/core/bitstreams/b5ac4a31-a969-43a7-8243-474251ceb316/content

[^27_11]: https://kubernetes.io/es/docs/concepts/configuration/manage-resources-containers/

[^27_12]: https://kubernetes.io/docs/concepts/storage/persistent-volumes/

