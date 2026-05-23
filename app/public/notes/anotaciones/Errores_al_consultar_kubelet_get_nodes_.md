<img src="https://r2cdn.perplexity.ai/pplx-full-logo-primary-dark%402x.png" style="height:64px;margin-right:32px"/>

# ❯ k get nodes

E0321 10:48:40.031459   27234 memcache.go:265] "Unhandled Error" err="couldn't get current server API group list: Get \\"https://192.168.39.155:8443/api?timeout=32s\\": dial tcp 192.168.39.155:8443: i/o timeout"

El error indica que `kubectl` no puede contactar al API‑server de Kubernetes en `https://192.168.39.155:8443` (timeout de conexión).[^1_1][^1_2]

### 1. Verifica conectividad básica

Desde el nodo/máquina donde ejecutas `kubectl`:

```bash
ping 192.168.39.155
nc -v 192.168.39.155 8443
curl -vk https://192.168.39.155:8443
```

- Si `ping` falla: revisa red/VLAN, firewall, routing.[^1_2]
- Si `nc` o `curl` dan timeout: el API‑server no está escuchando en esa IP:puerto o está caído/bloqueado.[^1_1]


### 2. Confirma el contexto de kubeconfig

```bash
kubectl config current-context
kubectl config view -o yaml
```

- Asegúrate de que el contexto apunta a la IP correcta del API‑server y que el certificado/token son válidos.[^1_3][^1_1]
- Si estás usando un cluster “local” (MicroK8s, minikube, kind, etc.), revisa si el cluster está realmente corriendo:

```bash
sudo systemctl status kubelet          # si usas kubeadm
microk8s status                       # si usas MicroK8s
minikube status                       # si usas minikube
```


### 3. Revisa el API‑server del cluster

Desde el nodo control‑plane (192.168.39.155 o el que corresponda):

```bash
sudo systemctl status kube-apiserver
sudo journalctl -u kube-apiserver -n 100
```

- Si el servicio está caído, arranca:

```bash
sudo systemctl start kube-apiserver
```

- Si el API‑server está en otro contenedor (CRI, etc.), revisa contenedores:

```bash
sudo crictl ps | grep kube-apiserver
sudo crictl logs <apiserver-container-id>
```


### 4. Firewall / NAT

En el control‑plane:

```bash
sudo iptables -L -n -v
sudo netstat -tulnp | grep 8443
```

- Asegúrate de que el API‑server escucha en `0.0.0.0:8443` o la IP correcta, y que no hay firewall bloqueando el puerto.[^1_2][^1_1]

***

Si me dices qué tipo de cluster es (kubeadm, MicroK8s, minikube, EKS, etc.), con eso puedo darte pasos mucho más específicos para tu escenario.
<span style="display:none">[^1_10][^1_4][^1_5][^1_6][^1_7][^1_8][^1_9]</span>

<div align="center">⁂</div>

[^1_1]: https://stackoverflow.com/questions/79473008/unable-to-connect-to-the-server-dial-tcp-192-168-64-38443-i-o-timeout

[^1_2]: https://stackoverflow.com/questions/49260135/unable-to-connect-to-the-server-dial-tcp-i-o-time-out

[^1_3]: https://signoz.io/guides/kubectl-error-memcache-go-265-couldnt-get-current-server-api-group-list-get/

[^1_4]: https://discuss.kubernetes.io/t/kubectl-error-couldnt-get-current-server-api-group-list/24707

[^1_5]: https://www.reddit.com/r/kubernetes/comments/182w0ye/kubectl_get_nodes_times_out/

[^1_6]: https://www.devopsschool.com/forum/d/70-unhandled-error-errcouldnt-get-current-server-api-group-list

[^1_7]: https://www.koskila.net/any-kubectl-command-throws-unhandled-error-errcouldnt-get-current-server-api-group-list-get-http-localhost8080-apitimeout32s-dial-tcp-18080-connect-connection-refused/

[^1_8]: https://knowledge.broadcom.com/external/article/387272/error-couldnt-get-current-server-api-gro.html

[^1_9]: https://learn.microsoft.com/en-us/answers/questions/1341691/how-do-i-fix-couldnt-get-current-server-api-group

[^1_10]: https://xcp-ng.org/forum/topic/7115/kubernetes-cluster-recipes-not-seeing-nodes


---

# si he iniciado minikube con driver=kvm2, debo iniciar el demonio libvirtd antes?

Sí, si usas `minikube` con `--driver=kvm2`, el servicio/libvirt (daemon `libvirtd`) debe estar corriendo y accesible; el driver `kvm2` se comunica con `libvirt` para gestionar la VM.[^2_1][^2_2][^2_10]

### Cuándo y cómo iniciarlo

- En sistemas basados en systemd (Fedora, Ubuntu, etc.):

```bash
sudo systemctl enable libvirtd        # para que inicie en el arranque
sudo systemctl start libvirtd         # para iniciarlo ahora
```

- Luego verifica que `libvirtd` está levantado y que tu usuario está en el grupo `libvirt`:

```bash
sudo usermod -aG libvirt $(whoami)
groups                    # debe mostrar libvirt
virsh list --all          # debe poder listar máquinas sin error
```


Si `libvirtd` no está corriendo, `minikube start --driver=kvm2` suele fallar con errores de conexión o permisos contra libvirt.[^2_2][^2_6][^2_1]
<span style="display:none">[^2_3][^2_4][^2_5][^2_7][^2_8][^2_9]</span>

<div align="center">⁂</div>

[^2_1]: https://vorozhko.net/how-to-enable-minikube-kvm2-driver-on-ubuntu-18-04

[^2_2]: https://minikube.sigs.k8s.io/docs/drivers/kvm2/

[^2_3]: https://github.com/idvoretskyi/minikube-kvm

[^2_4]: https://technology.amis.nl/platform/kubernetes/minikube-on-kvm2-on-linux-mint-19-1/

[^2_5]: https://cravencode.com/post/kubernetes/setup-minikube-on-ubuntu-kvm2/

[^2_6]: https://release-1-12.docs.kubeedge.io/ko/docs/getting-started-minikube/

[^2_7]: https://kubernetes.io/blog/2019/03/28/running-kubernetes-locally-on-linux-with-minikube-now-with-kubernetes-1.14-support/

[^2_8]: https://github.com/kubernetes/minikube/issues/5801

[^2_9]: https://groups.google.com/g/kubernetes-dev/c/1-PBYOujaCU

[^2_10]: https://minikube.sigs.k8s.io/docs/drivers/


---

# si ya habia creado un perfil con mikikube, cuyo nombre es 'aprendizaje'. Debo reiniciarlo antes de usarlo

No necesitas reiniciarlo “a priori”; solo que el perfil esté **corriendo** cuando quieras usarlo.[^3_1][^3_2]

### Pasos recomendados

1. Verifica si el perfil `aprendizaje` está arriba:

```bash
minikube profile aprendizaje      # cambia el contexto a ese perfil
minikube status
```

    - Si ves `host: Running`, `kubelet: Running`, `apiserver: Running`, entonces ya está iniciado y listo para usar `kubectl`.[^3_3][^3_1]
2. Si el perfil está parado:

```bash
minikube start --profile aprendizaje
```

Esto inicia el cluster asociado al perfil `aprendizaje` (reutilizando la configuración previa).[^3_2][^3_1]

En resumen:

- Si el perfil ya existe y ya lo habías iniciado antes, solo debes **verificar el estado** y, si está parado, **hacer `minikube start --profile aprendizaje`**; no hace falta “reiniciarlo” a menos que quieras un reset completo (`minikube delete --profile aprendizaje` + `start`).[^3_4][^3_1][^3_2]
<span style="display:none">[^3_10][^3_5][^3_6][^3_7][^3_8][^3_9]</span>

<div align="center">⁂</div>

[^3_1]: https://dev.to/hridyeshbisht/getting-started-with-minikube-for-kubernetes-40a6

[^3_2]: https://minikube.sigs.k8s.io/docs/faq/

[^3_3]: https://github.com/kubernetes/minikube/issues/4598

[^3_4]: https://www.reddit.com/r/kubernetes/comments/1ewwube/minikube_is_not_able_to_start/

[^3_5]: https://codigoelectronica.com/blog/guia-completa-de-comandos-minikube

[^3_6]: https://www.youtube.com/watch?v=1jENLtHuyUI

[^3_7]: https://minikube.sigs.k8s.io/docs/commands/profile/

[^3_8]: https://minikube.sigs.k8s.io/docs/handbook/config/

[^3_9]: https://es.stackoverflow.com/questions/626950/error-al-querer-iniciar-en-kubernetes

[^3_10]: https://itnext.io/is-minikubes-profile-a-kubernetes-cluster-c0f134c5e684

