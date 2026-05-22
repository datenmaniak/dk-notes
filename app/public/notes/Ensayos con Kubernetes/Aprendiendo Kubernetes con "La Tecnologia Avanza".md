Siguiendo el tutorial de: 
https://www.youtube.com/watch?v=gvqpZkdK4DU&t=1702s

### alias k='kubectl'
### alias kgp='kubectl get pod'


## Pods
### Administrar e  inspeccionar

```bash
# Crear un pod
kubectl run app2 --image=nginx:latest

k apply -f nginx-pod.yaml

# Eliminar un pod
k delete pod nginx-pod-onfailure

# Inspeccionar
k describe pod app2

# Mostrar los pods
k get pod

```

## Labels
Su objetivo es categorizar objetos con etiquetas.

```bash
# Cambiar etiqueta a 
 k label --overwrite pod nginx1  app=backend

# Mostrar todos los Pods y  sus etiquetas
kubectl get pod --show-labels

  NAME            READY   STATUS       RESTARTS       AGE     LABELS
apache          1/1     Running      0              3h40m   run=apache
app2            1/1     Running      0              5m5s    run=app2
busybox-never   0/1     StartError   0              168m    app.kubernetes.io/name=busybox-never
myapp           1/1     Running      0              131m    app=frontend,tier=web
nginx-always    1/1     Running      6 (177m ago)   3h8m    app.kubernetes.io/name=nginx-always
nginx-pod       1/1     Running      0              3h21m   app.kubernetes.io/name=nginx-pod
nginx1          1/1     Running      0              3h59m   app=backend

# Asigna nueva etiqueta y remueva otra
 k label --overwrite pod app2 app=backend run-

# Comprobar ajuste
 kgp --show-labels | grep app=
     3: app2            1/1     Running      0              9m28s   app=backend
     5: myapp           1/1     Running      0              135m    app=frontend,tier=web
     8: nginx1          1/1     Running      0              4h3m    app=backend


# Eliminar  etiqueta
k label pod nginx1 run-


```

### Selectors
Mecanismo para filtrar y seleccionar objetos basado en sus etiquetas.

```bash
 # Ejemplo: *sample-pod.yaml*
apiVersion: v1
kind: Pod
metadata:
	name: sample-pod
	labels:
		app: demo
		environment: production
		version: v1
spec:
	containers:
	- name: nginx
	image: nginx:latest
	ports:
	- containerPort: 80
```

```bash
# Buscar con la etiqueta *app=demo*
❯ kgp --selector app=demo
 
NAME         READY   STATUS    RESTARTS   AGE
sample-pod   1/1     Running   0          4m53s

❯ kgp -l app=demo

NAME         READY   STATUS    RESTARTS   AGE
sample-pod   1/1     Running   0          10m

# Buscar el pod con la etiqueta 'demo'

kgp --show-labels -l app=demo
NAME         READY   STATUS    RESTARTS   AGE   LABELS
sample-pod   1/1     Running   0          15m   app=demo,environment=production,version=v1


# Buscar todos los pods con la etiqueta diferente a 'demo'

❯ kgp --show-labels -l app!=demo
NAME            READY   STATUS       RESTARTS        AGE     LABELS
apache          1/1     Running      0               4h13m   run=apache
app2            1/1     Running      0               38m     app=backend
busybox-never   0/1     StartError   0               3h21m   app.kubernetes.io/name=busybox-never
myapp           1/1     Running      0               164m    app=frontend,tier=web
nginx-always    1/1     Running      6 (3h31m ago)   3h41m   app.kubernetes.io/name=nginx-always
nginx-pod       1/1     Running      0               3h54m   app.kubernetes.io/name=nginx-pod
nginx1          1/1     Running      0               4h32m   app=backend

# Buscar con el operador 'in'

❯ kgp --show-labels -l 'environment in (production,staging)'
NAME         READY   STATUS    RESTARTS   AGE   LABELS
sample-pod   1/1     Running   0          19m   app=demo,environment=production,version=v1

# Buscar los excluyendo aquellos con las  etiquetas: frontend y backend
❯ kgp --show-labels -l 'app notin (backend,frontend)'

NAME            READY   STATUS       RESTARTS  AGE     LABELS
apache          1/1     Running      0         4h21m   run=apache
busybox-never   0/1     StartError   0         3h28m   app.kubernetes.io/name=busybox-never
nginx-always    1/1     Running      6 (3h38m ago)   3h49m   app.kubernetes.io/name=nginx-always
nginx-pod       1/1     Running      0               4h2m    app.kubernetes.io/name=nginx-pod
sample-pod      1/1     Running      0               23m     app=demo,environment=production,version=v1

❯ kgp -l 'version' --show-labels

NAME         READY   STATUS    RESTARTS   AGE   LABELS
sample-pod   1/1     Running   0          27m   app=demo,environment=production,version=v1

# que coincida con varias etiquetas
❯ kgp --show-labels -l app=demo,environment=production,version=v1

NAME         READY   STATUS    RESTARTS   AGE   LABELS
sample-pod   1/1     Running   0          30m   app=demo,environment=production,version=v1

# Buscar con condiciones mixtas
❯ kgp --show-labels -l " app=demo, version in (v1,v2) "

NAME         READY   STATUS    RESTARTS   AGE   LABELS
sample-pod   1/1     Running   0          33m   app=demo,environment=production,version=v1

# Eliminar los pod con la etiqueta: backend
❯ k delete pods -l app=backend

pod "app2" deleted from default namespace
pod "nginx1" deleted from default namespace


```

### Anotaciones
Se adjuntan en la seccion de metadatos para poder agregar informacion adicional sobre los recursos.

```bash
# sample-annotat.yaml

apiVersion: v1
kind: Pod
metadata:
	name: sample-annotat
	labels:
		sample: annotation
	annotations:
		created-by: "user@ejemplo.com"
		purpose: "Ejemplo de anotaciones"
		monitoring: enabled
spec:
	containers:
	- name: nginx
	image: nginx:latest
```

### Gestionar anotaciones

```bash
# listar todas las anotaciones
❯ kgp sample-annotat -o jsonpath='{.metadata.annotations}'

{"created-by":"user@ejemplo.com","kubectl.kubernetes.io/last-applied-configuration":"{\"apiVersion\":\"v1\",\"kind\":\"Pod\",\"metadata\":{\"annotations\":{\"created-by\":\"user@ejemplo.com\",\"monitoring\":\"enabled\",\"purpose\":\"Ejemplo de anotaciones\"},\"labels\":{\"sample\":\"annotation\"},\"name\":\"sample-annotat\",\"namespace\":\"default\"},\"spec\":{\"containers\":[{\"image\":\"nginx:latest\",\"name\":\"nginx\"}]}}\n","monitoring":"enabled","purpose":"Ejemplo de anotaciones"}%

# Agregar una anotacion
❯ k annotate pod sample-annotat descripcion="Subscribete a la Tecnologia"
pod/sample-annotat annotated

# Verificar la anotacion agregada
❯ k describe pod sample-annotat

Name:             sample-annotat
Namespace:        default
Priority:         0
Service Account:  default
Node:             aprendizaje/192.168.39.155
Start Time:       Mon, 23 Mar 2026 16:41:52 -0400
Labels:           sample=annotation
Annotations:      created-by: user@ejemplo.com
                  descripcion: Subscribete a la Tecnologia
                  monitoring: enabled
                  purpose: Ejemplo de anotaciones
Status:           Running


# Modificar una anotacion
❯ kubectl annotate pod sample-annotat purpose="anotaciones (MODIFICADA)" --overwrite

pod/sample-annotat annotated

# Revisar el cambio
❯ k describe pod sample-annotat

Name:             sample-annotat
Namespace:        default
Priority:         0
Service Account:  default
Node:             aprendizaje/192.168.39.155
Start Time:       Mon, 23 Mar 2026 16:41:52 -0400
Labels:           sample=annotation
Annotations:      created-by: user@ejemplo.com
                  descripcion: Subscribete a la Tecnologia
                  monitoring: enabled
                  purpose: anotaciones (MODIFICADA)
Status:           Running


# Ahora, eliminar una anotacion. La palabra monitoring.
❯  kubectl annotate pod sample-annotat monitoring-
pod/sample-annotat annotated

# Revisar
❯ k describe pod sample-annotat
Name:             sample-annotat
Namespace:        default
Priority:         0
Service Account:  default
Node:             aprendizaje/192.168.39.155
Start Time:       Mon, 23 Mar 2026 16:41:52 -0400
Labels:           sample=annotation
Annotations:      created-by: user@ejemplo.com
                  descripcion: Subscribete a la Tecnologia
                  purpose: anotaciones (MODIFICADA)
Status:           Running
IP:               10.244.0.48

# Verificacion de anotacion
❯ kubectl annotate pod sample-annotat --list

created-by=user@ejemplo.com
descripcion=Subscribete a la Tecnologia
kubectl.kubernetes.io/last-applied-configuration={"apiVersion":"v1","kind":"Pod","metadata":{"annotations":{"created-by":"user@ejemplo.com","monitoring":"enabled","purpose":"Ejemplo de anotaciones"},"labels":{"sample":"annotation"},"name":"sample-annotat","namespace":"default"},"spec":{"containers":[{"image":"nginx:latest","name":"nginx"}]}}

purpose=anotaciones (MODIFICADA)


```

### Visualizacion de recursos

``` bash
minikube dashboard
```

En el dashboard se puede visualizar todos los componentes de un Pod.

![[Pasted image 20260323170829.png]]


![[Pasted image 20260323170749.png]]


### Controllers 
Monitorean el estado deseado de los recursos en el cluster.  Actuan como un mecanismo de control y gestion automatizada.

- Supervision / mantenimiento
- Escalabilidad
- Actualizaciones
- Recuperaciones automaticas

### Workloads
Representan las aplicaciones que se ejecutan en el cluster.

- Ejecucion de aplicaciones
- Configuracion de despliegue
- Escalabilidad y replicacion
- Persistencia y estado


### Workload & Controllers

- **Controllers**: Deployment, ReplicaSet, StatefulSet, DaemonSet, Job controller y CronJob Controller.
- **Workloads**: Pods, ReplicaSets, Deployments, StatefulSets, DaemonSets, Jobs, CronJobs


### Introduccion a Deployments & ReplicaSets

### Deployments
Es un recurso que define la manera como debe desplegarse una aplicacion. Definen el estado deseado, numero de replicas, imagen del contenedor.

### ReplicaSets
Recurso que asegura que un numero especifico de replicas de un Pod se mantengan en ejecucion en todo momento.

**Ejemplo practico**:  Definir un archivo *yaml* que defina el deployment y lo aplique en el cluster.

el API server almacena la definicion del deployment en **etcd**.

### Manejo de deployments

```bash
# Crear un deployment
❯ k create deploy apache --image=httpd
deployment.apps/apache created

# Crear un Deployment de manera declarativa ( my-deploy.yaml)
apiVersion: apps/v1
kind: Deployment
metadata:
  name: nginx-dep
spec:
  selector:
    matchLabels:
      app: nginx
  template:
    metadata:
      labels:
        app: nginx
    spec:
      containers:
      - name: nginx
        image: nginx:latest
        resources:
          limits:
            memory: "128Mi"
            cpu: "500m"
        ports:
        - containerPort: 80



❯ k apply -f my-deploy.yaml
deployment.apps/nginx-dep created

# Crear otro Deployment con replicas (my-deploy-repl.yaml)
apiVersion: apps/v1
kind: Deployment
metadata:
  name: nginx-deploy-replica
spec:
  replicas: 3
  selector:
    matchLabels:
      app: nginx
  template:
    metadata:
      labels:
        app: nginx
    spec:
      containers:
      - name: nginx
        image: nginx:latest
        resources:
          limits:
            memory: "128Mi"
            cpu: "500m"
        ports:
        - containerPort: 80

# Consultar las replicas
❯ k get rs -o wide

NAME                              DESIRED   CURRENT   READY   AGE     CONTAINERS   IMAGES         SELECTOR
apache-5959974c77                 1         1         1       20m     httpd        httpd          app=apache,pod-template-hash=5959974c77
nginx-dep-7f5dfb844c              1         1         0       8m55s   nginx        nginx:latest   app=nginx,pod-template-hash=7f5dfb844c
nginx-deploy-replica-7f5dfb844c   3         3         0       2m20s   nginx        nginx:latest   app=nginx,pod-template-hash=7f5dfb844c

# Consultar con selector de etiquetas
❯ k get deploy,po,rs -l app=nginx --show-labels

NAME                                        READY   STATUS    RESTARTS   AGE    LABELS
pod/nginx-dep-7f5dfb844c-kxm56              0/1     Pending   0          14m    app=nginx,pod-template-hash=7f5dfb844c
pod/nginx-deploy-replica-7f5dfb844c-h67df   0/1     Pending   0          8m5s   app=nginx,pod-template-hash=7f5dfb844c
pod/nginx-deploy-replica-7f5dfb844c-tx5zl   0/1     Pending   0          8m5s   app=nginx,pod-template-hash=7f5dfb844c
pod/nginx-deploy-replica-7f5dfb844c-xp928   0/1     Pending   0          8m5s   app=nginx,pod-template-hash=7f5dfb844c

NAME                                              DESIRED   CURRENT   READY   AGE    LABELS
replicaset.apps/nginx-dep-7f5dfb844c              1         1         0       14m    app=nginx,pod-template-hash=7f5dfb844c
replicaset.apps/nginx-deploy-replica-7f5dfb844c   3         3         0       8m5s   app=nginx,pod-template-hash=7f5dfb844c


```

### Escalado Horizontal:
Cambiar el numero de replicas del Pod

### Escalado Vertical
Ajustar los recursos: CPU, Memoria, etc. asignados a un Pod.

```bash
# Agregar un Deploy con 2 replias
apiVersion: apps/v1
kind: Deployment
metadata:
  name: my-app
spec:
  replicas: 2  # Replicas aplicada al momento de creacion
  selector:
    matchLabels:
      app: my-app
  template:
    metadata:
      labels:
        app: my-app
    spec:
      containers:
      - name: nginx
        image: nginx:latest
        resources:
          limits:
            memory: "128Mi"
            cpu: "500m"
        ports:
        - containerPort: 80

# Consultar
❯ k get deploy,po,rs -l app=my-app --show-labels

NAME                          READY   STATUS    RESTARTS   AGE     LABELS
pod/my-app-744c6b8dc5-qhx8z   0/1     Pending   0          3m49s   app=my-app,pod-template-hash=744c6b8dc5
pod/my-app-744c6b8dc5-wjx65   0/1     Pending   0          3m49s   app=my-app,pod-template-hash=744c6b8dc5

NAME                                DESIRED   CURRENT   READY   AGE     LABELS
replicaset.apps/my-app-744c6b8dc5   2         2         0       3m49s   app=my-app,pod-template-hash=744c6b8dc5

```

### Ahora realizamos un escalado horizontal de manera manual.

```bash
❯ k scale deployment my-app  --replicas=5
deployment.apps/my-app scaled

# Verificar
❯ k get deploy --show-labels

NAME                   READY   UP-TO-DATE   AVAILABLE   AGE    LABELS
apache                 1/1     1            1           45m    app=apache
my-app                 0/5     5            0           6m8s   <none>
nginx-dep              0/1     1            0           33m    <none>
nginx-deploy-replica   0/3     3            0           26m    <none>

```
