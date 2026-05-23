## Guía de Implementación: 
Registro Privado de Contenedores en K3s con Persistencia NFS
## Introducción
En el desarrollo de infraestructuras modernas y  la gestión eficiente de imágenes de contenedores es un pilar fundamental. La implementación de un registro privado dentro de un clúster K3s responde a la necesidad de mantener el control total sobre el ciclo de vida del software, garantizando la privacidad del código y optimizando los tiempos de despliegue en entornos locales. 

El uso de una solución basada en **NFS (Network File System)** para la persistencia surge de la imperativa de desacoplar los datos de la lógica de ejecución. En entornos de contenedores, los datos son efímeros; sin una estrategia de persistencia robusta, la eliminación de un Pod resultaría en la pérdida de todas las imágenes. Al integrar una partición NFS externa (`ej. /archives`), aseguramos que el "almacén" de imágenes sea resiliente, escalable y totalmente independiente del estado del clúster.

## Objetivo y Alcance
### Objetivo
Establecer un registro de contenedores privado, seguro y persistente dentro de un clúster K3s, que permita el almacenamiento de imágenes personalizadas y facilite el flujo de trabajo DevOps profesional.

### Alcance
* **Configuración de Almacenamiento:** Mapeo de una partición NFS física en el host mediante `PersistentVolumes` (PV) y `PersistentVolumeClaims` (PVC).
* **Despliegue de Servicio:** Ejecución de una instancia de Docker Registry v2 dentro del namespace `container-registry`.
* **Exposición de Red:** Configuración de un `Service` tipo `LoadBalancer` para acceso mediante el puerto 5000 desde la red local.
* **Persistencia:** Implementación de políticas de retención (`Retain`) para garantizar la integridad de los datos ante reinicios del clúster.

## Ajustes Esenciales en la Estación de Trabajo,  firewall y en el nodo master.

> [!NOTE]
> Es un punto clave. En la estación de trabajo (tu HP Victus) configuramos Podman para que pueda **subir** imágenes, pero ahora necesitamos que el **clúster K3s** sea capaz de **descargar** esas mismas imágenes para ejecutarlas.

## Estación de trabajo remota
Para interactuar con el registro desde la estación de trabajo principal (ej. Mi estación de trabajo HP Victus), se deben realizar los siguientes ajustes:

1.  **Configuración de Dominios Locales:**
    Asegurar que el hostname `registro.local` resuelva a la dirección IP del nodo master en el archivo `/etc/hosts`.
    
```bash
192.168.1.2  registro.local
```

> [!NOTE]
> Vale mencionar que el nodo master K3s y los workers se encuentran en un red LAN privada, administrada por el firewall `(pfSense)`. Por lo tanto, la IP mencionada en la resolucion de  hosts corresponde a este.

2.  **Confianza en Registros Inseguros (HTTP):**
    Dado que el registro opera internamente por HTTP, es necesario configurar **Podman** o **Docker** para aceptar el dominio. 

    * Editar y agregar al final del archivo  `/etc/containers/registries.conf`:
    
        ```toml
        [[registry]]
        location = "registro.local:5000"
        insecure = true
        ```
    
3.  **Herramientas CLI:**
    Contar con `kubectl` configurado para el clúster K3s y `podman` para la gestión de imágenes.

## Firewall
Configurar el acceso desde la publica de mi LAN hacia la red privada del cluster K3s, para permitir la gestión de imágenes desde mi estación de trabajo

1. **Declarar un NAT / Port Forward:**

```plainttext
Interface: WAN
Protocol: TCP
Source Address: *
Source Ports: *
Dest. Address: WAN address
Dest. Ports: 5000
NAT IP: 10.0.0.100        <--- IP de nodo master
NAT Ports: 5000
Description" NAT for registry service at K3s Master
```

## Ajustes Necesarios a Nivel de Kubernetes

### El archivo de configuración en K3s

K3s utiliza un archivo específico para gestionar los espejos (mirrors) y la configuración de registros, independientemente de si usas Docker o Containerd (por cierto, este ultimo es el motor por defecto de K3s).
### Ruta del archivo

La ruta exacta en tu **nodo master** es: `/etc/rancher/k3s/registries.yaml`

> **Nota:** Es probable que el archivo no exista todavía. Si no está, debes crearlo.

### Contenido del archivo `registries.yaml`

Debes editar el archivo con permisos de superusuario (`sudo nano /etc/rancher/k3s/registries.yaml`) y añadir la siguiente estructura:


```yaml
mirrors:
  "registro.local:5000":
    endpoint:
      - "http://10.0.0.100:5000"

configs:
  "registro.local:5000":
    tls:
      insecure_skip_verify: true
```

### Explicación de los campos:

- **mirrors**: Le dice a K3s que cuando vea una imagen que empiece por `registro.local:5000`, debe buscarla en el `endpoint` definido.
    
- **endpoint**: Aquí especificamos explícitamente el protocolo `http://`.
    
- **insecure_skip_verify**: Le ordena al motor de contenedores que ignore la falta de certificados TLS/SSL.

### Aplicar los cambios

A diferencia de otros componentes de Linux, para que K3s lea este archivo de configuración, **es necesario reiniciar el servicio** en el nodo master:

```bash
sudo systemctl restart k3s
```


### ¿Por qué es esencial esta configuración?

Por defecto, Kubernetes y K3s exigen que todas las comunicaciones con un registro de imágenes se realicen a través de **HTTPS** por seguridad. Como tu registro interno corre en `http://registro.local:5000` (sin certificados SSL), K3s bloqueará cualquier intento de descarga por considerarlo inseguro.

> [!Warning]
> Si no haces estos ajustes, al intentar desplegar un Pod que use una imagen de tu registro, verás el error: `ErrImagePull` o `ImagePullBackOff` con un mensaje interno de: _“server gave HTTP response to HTTPS client”_.

## Requerimientos para el registro Privado de Contenedores en K3s con Persistencia NFS

Esta guía detalla los pasos técnicos realizados para configurar un registro de Docker privado en un clúster K3s, utilizando una partición NFS externa para garantizar la persistencia de las imágenes y una gestión profesional del almacenamiento.

## Preparación de la base (persistencia)
### 1. Particiones 

En el servidor Proxmox, se han mapeado tres (3) particiones de un disco secundario, destinada exclusivamente para persistencia para MariaDB y PostgreSQL, respaldos e  imágenes ISOs.

> [!NOTE]
> Para dejar espacio suficiente para gestión de  la infraestructura de Proxmox, se ha dejado el disco principal SSD para el sistema, las VMs y Contenedores. 

```plaintext  
# /etc/fstab
.
.
# HDD Seagate   Destinado para DB, ISO, Cloud Images

# MariaDB persistence
# /dev/sdb3: LABEL="mariadb" 
UUID="dedc6f7a-0708-45fb-8cd2-9ec95bacf871"  /mariadb-data ext4 defaults,noatime,discard 0 2 

# Postgres 
# /dev/sdb5: LABEL="postgres" 
UUID="e1509633-2511-409d-ab5a-d20635ef6628"  /postgres-data ext4 defaults,noatime,discard 0  2

#  ISO images Proxmox 
# /dev/sdb4: LABEL="data2" 
UUID="fdacd68b-ed12-4d57-b6fe-6e13d1596557" /archives ext4 defaults  0 2
```

### 2. Preparación de NFS

```plaintext
##  /etc/exports

/postgres-data  10.0.0.0/24(rw,sync,no_subtree_check,no_root_squash)

/mariadb-data  10.0.0.0/24(rw,sync,no_subtree_check,no_root_squash)

/archives  10.0.0.0/24(rw,sync,no_subtree_check,no_root_squash)
```


## Preparación del Host ( k3s master)

Antes de interactuar con Kubernetes, se preparó el sistema de archivos físico en el servidor que actúa como nodo master .

* **Creación del directorio:** Se definió una ruta dedicada en la partición NFS.
    ```bash
    sudo mkdir -p /archives/registry-data
    ```

- **Montajes de las particiones:**  Se mapean las particiones que han sido previamente publicadas por el servidor NFS.

Con esta declaraciones hecha en `/etc/fstab`, el sistema mapea las particiones cada vez que sea iniciado.

```bash
# /etc/fstab
.
.
# Unidades NFS
10.0.0.200:/postgres-data /postgres-pv nfs defaults,_netdev,noatime 0 0

10.0.0.200:/mariadb-data /mariadb-pv nfs defaults,_netdev,noatime 0 0

10.0.0.200:/archives /archives nfs defaults,_netdev,noatime 0 0
```

Para lograr el objetivo de esta  documentación, nos interesa: 

```plaintext
10.0.0.200:/archives /archives nfs defaults,_netdev,noatime 0 0
```

La IP   **10.0.0.200** corresponde al  Servidor Proxmox. Para refrescar la memoria, es donde existen las particiones que ha sido mapeadas en el sistema y exportada al Master K3s por medio del servicio NFS. 

* **Gestión de Permisos:** Para evitar errores de escritura (HTTP 500), se sincronizó la propiedad del directorio con el UID `1000` (el usuario interno del contenedor `registry:2`).

```bash
    sudo chown -R 1000:1000 /archives/registry-data
    sudo chmod -R 775 /archives/registry-data
```


## 2. Configuración de Infraestructura en Kubernetes (desde la estación de trabajo)

La implementación se basa en el desacoplamiento de la infraestructura física y la lógica de la aplicación mediante objetos nativos de K8s.

### Paso 1: Definición del Volumen Físico (PersistentVolume)

**01-registry-pv.yaml**
```yaml
apiVersion: v1
kind: PersistentVolume
metadata:
  name: registry-nfs-pv
spec:
  capacity:
    storage: 20Gi
  volumeMode: Filesystem
  accessModes:
    - ReadWriteOnce
  persistentVolumeReclaimPolicy: Retain
  storageClassName: local-path
  local:
    path: /archives/registry-data
  nodeAffinity:
    required:
      nodeSelectorTerms:
        - matchExpressions:
            - key: kubernetes.io/hostname
              operator: In
              values:
                - k3s-master # Asegúrate que coincida con 'kubectl get nodes'

```


Se mapeó la carpeta de la partición NFS al clúster mediante un recurso `PersistentVolume`.
* **StorageClass:** `local-path`.
* **Reclaim Policy:** `Retain` (protege los datos si el PVC es eliminado).
* **Node Affinity:** Restringido al nodo `k3s-master` para asegurar el acceso local al path `/archives`.


### Paso 2: Solicitud de Almacenamiento (PersistentVolumeClaim)

**02-registry-pvc.yaml**
```yaml
apiVersion: v1
kind: PersistentVolumeClaim
metadata:
  name: registry-pvc
  namespace: container-registry
spec:
  storageClassName: local-path
  accessModes:
    - ReadWriteOnce
  # ESTA ES LA CLAVE: Forzamos el vínculo con tu PV de NFS
  volumeName: registry-nfs-pv
  resources:
    requests:
      storage: 20Gi

```

Se configuró una solicitud de 20Gi de espacio para vincular el PV con el despliegue del registro.
* **Vínculo Estático:** Se utilizó el parámetro `volumeName: registry-nfs-pv` para forzar la conexión con el volumen NFS y evitar que K3s creara un volumen dinámico vacío en la ruta por defecto.

## 3. Despliegue del Registro (Deployment y Service)

**03-registry-app.yaml**
```yaml
apiVersion: v1
kind: Namespace
metadata:
  name: container-registry
---
apiVersion: apps/v1
kind: Deployment
metadata:
  name: docker-registry
  namespace: container-registry
spec:
  replicas: 1
  selector:
    matchLabels:
      app: registry
  template:
    metadata:
      labels:
        app: registry
    spec:
      # CONFIGURACIÓN CORRECTA: fsGroup va a nivel de Pod
      securityContext:
        fsGroup: 1000
      containers:
      - name: registry
        image: registry:2
        ports:
        - containerPort: 5000
        # runAsUser si puede ir a nivel de contenedor
        securityContext:
          runAsUser: 1000
        volumeMounts:
        - name: storage
          mountPath: /var/lib/registry
      volumes:
      - name: storage
        persistentVolumeClaim:
          claimName: registry-pvc
---
apiVersion: v1
kind: Service
metadata:
  name: registry-service
  namespace: container-registry
spec:
  selector:
    app: registry
  ports:
    - protocol: TCP
      port: 5000
      targetPort: 5000
  type: LoadBalancer

```

Se utilizó un manifiesto unificado para levantar el servicio bajo el namespace `container-registry`.

* **Seguridad (SecurityContext):** Se configuró `runAsUser: 1000` y `fsGroup: 1000` para que el contenedor tuviera permisos de escritura sobre el volumen NFS.
* **Exposición del Servicio:** Se configuró como tipo `LoadBalancer` en el puerto `5000` para permitir el acceso desde cualquier máquina de la red local (ej. tu laptop HP Victus).

#### La arquitectura requiere una sincronización precisa entre los recursos lógicos y el hardware físico:

1.  **Sincronización de Identidad (SecurityContext):**
    El registro debe ejecutarse con el UID `1000`. Es crítico que el directorio físico en el host (`/archives/registry-data`) tenga como propietario a este mismo usuario (`chown -R 1000:1000`) para evitar errores `HTTP 500`.
2.  **Vínculo Estático de Volúmenes:**
    Se debe utilizar el campo `volumeName` en el PVC para forzar la conexión exclusiva al volumen NFS definido, evitando que K3s cree volúmenes dinámicos automáticos en rutas temporales.
3.  **Afinidad de Nodo (Node Affinity):**
    Dado que el volumen es `local` al path `/archives`, el despliegue debe tener una regla de afinidad que obligue al Pod a ejecutarse únicamente en el nodo donde dicha partición está montada (ej. `k3s-master`).

## 4. Comandos de Aplicación y Mantenimiento
Para desplegar o actualizar el registro, ejecuta en orden:

```bash
kubectl apply -f 01-registry-pv.yaml
kubectl apply -f 02-registry-pvc.yaml
kubectl apply -f 03-registry-app.yaml
```



## 5. Validación y Flujo de Trabajo
Una vez desplegado, el flujo para usar el registro privado es:

1. **Etiquetar la imagen:** `podman tag mi-app:latest <IP_CLUSTER>:5000/mi-app:v1`
2. **Subir al registro:** `podman push <IP_CLUSTER>:5000/mi-app:v1`
3. **Verificación física:** Las imágenes deben aparecer en `/archives/registry-data/docker/registry/v2/repositories/`.


## Errores durante la implementación:

### Primer error: después de aplicar los manifiestos:

> [!Observación]
> Se aplicaron los manifiestos  sin errores. Sin embargo, el pod aun muestra el **STATUS:** `Pending` y el `persistentvolume/registry-nfs-pv` muestra el **STATUS:** `Available` en lugar de mostrar **Bound **
> 

Si el Pod está en `Pending` y el PV sigue en `Available`, significa que el **vínculo (Binding)** entre el reclamo y el volumen físico no se ha completado. El PVC está buscando un volumen que encaje con sus requisitos, pero por alguna razón no está "viendo" al PV que creaste.

Vamos a diagnosticar y solucionar esto rápidamente.

### 1. El síntoma: El "Reclamo" no encuentra su "Volumen"

Cuando un PV está en `Available`, está esperando ser reclamado. Si el Pod está `Pending`, es porque su PVC (`registry-pvc`) está probablemente en estado `Pending` también.

Ejecuta este comando para confirmar:

```bash
kubectl get pvc -n container-registry
```

### 2. Causas probables y soluciones

#### A. Discrepancia en el StorageClass

Para que un PV y un PVC se vinculen, deben tener el mismo `storageClassName`. En tus archivos ambos usan `local-path`, pero a veces, si el clúster tiene un `storageclass` por defecto, puede haber conflictos.

**Revisión rápida:** Asegúrate de que el PV no tenga un nombre de clase distinto al del PVC. Puedes forzar el vínculo eliminando el nombre de la clase en ambos (dejándolo como `""`) o asegurándote de que sea idéntico.

#### B. El Selector de Nodo (Affinity)

En tu archivo `01-registry-pv.yaml` definimos una `nodeAffinity` apuntando a `k3s-master`.

- Si el nombre del nodo no es exactamente `k3s-master` (por ejemplo, si tiene mayúsculas o es diferente en `kubectl get nodes`), el PV nunca se activará porque Kubernetes cree que el nodo solicitado no existe.
    
**Verificación:**

```bash
kubectl get nodes
```

_Si el nombre es distinto, debes borrar el PV y volverlo a crear con el nombre exacto._

#### C. El tamaño del reclamo

El PVC pide `20Gi`. Si el PV tiene **exactamente** `20Gi`, está bien. Pero si por algún error el PV se creó con un valor menor (ej. `19Gi`), el PVC se quedará esperando eternamente un volumen más grande.

### 3. Procedimiento de "Reconexión Force"

Si los nombres y tamaños coinciden, intenta este orden para forzar el reconocimiento:

1. **Borra el Deployment para liberar la presión:** `kubectl delete -f 03-registry-app.yaml`
    
2. **Borra el PVC:** `kubectl delete -f 02-registry-pvc.yaml`
    
3. **Verifica el PV:** `kubectl get pv` -> Debe seguir en `Available`.
    
4. **Aplica el PVC de nuevo:** `kubectl apply -f 02-registry-pvc.yaml`
    
5. **Revisa el estado:** `kubectl get pvc -n container-registry` -> **DEBE decir `Bound`**.
    
### ¿Qué dice el log de errores?

Si después de re-aplicar el PVC sigue en `Pending`, ejecuta este comando y dime qué aparece en la sección **`Events`** (al final):

```bash
kubectl describe pvc registry-pvc -n container-registry
```

El resultado de esta instrucción:
### Error 2:  No resources found in container-registry namespace.

Ahí tenemos el problema: el **PersistentVolumeClaim (PVC)** no existe en el clúster. Si el PVC no está presente, el Pod se queda en `Pending` perpetuamente porque no tiene una orden de "alquiler" para usar el volumen físico que creaste.

Aunque hayas ejecutado el comando, es probable que no se haya procesado o que hubiera un error de sintaxis previo. Vamos a solucionarlo ahora mismo siguiendo estos pasos exactos:

### 1. Aplicar el PVC (El paso que falta)

Asegúrate de que el archivo `02-registry-pvc.yaml` esté guardado y ejecuta:

```bash
kubectl apply -f 02-registry-pvc.yaml
```

### 2. Verificar la conexión (Binding)

Inmediatamente después, comprueba que el PVC aparezca y se haya "atado" al volumen físico (PV) que ya tienes en `Available`:

```bash
kubectl get pvc -n container-registry
```

**Resultado esperado:**

- **STATUS:** Debe decir `Bound`.
    
- **VOLUME:** Debe decir `registry-nfs-pv`.
    

### 3. ¿Por qué el Pod sigue en Pending?

Una vez que el PVC esté en `Bound`, el Pod debería intentar arrancar automáticamente. Si sigue en `Pending`, es probable que el **Deployment** no esté encontrando el nombre exacto del PVC.

Revisemos la coherencia de nombres en tus archivos:

- En tu **`02-registry-pvc.yaml`**, el nombre en `metadata.name` debe ser **`registry-pvc`**.
    
- En tu **`03-registry-app.yaml`**, la sección `volumes.persistentVolumeClaim.claimName` debe ser **`registry-pvc`**.
    

---

### Solución rápida en un solo paso

Si quieres asegurar que todo se sincronice, puedes aplicar este bloque de comandos para limpiar y re-lanzar la solicitud:


```bash
# Borrar la solicitud fallida si existiera rastro
kubectl delete pvc registry-pvc -n container-registry --ignore-not-found

# Aplicar el reclamo de nuevo
kubectl apply -f 02-registry-pvc.yaml

# Forzar el reinicio del despliegue para que detecte el nuevo PVC
kubectl rollout restart deployment docker-registry -n container-registry
```

**¿Qué estado (STATUS) te muestra ahora el comando `kubectl get pvc -n container-registry`?** Si dice `Bound`, habremos superado el cuello de botella del almacenamiento.

¡Excelente noticia! El estado **`Bound`** significa que el "puente" entre tu infraestructura física (NFS en `/archives`) y el clúster de Kubernetes se ha construido con éxito. Ahora el almacenamiento ya no es un problema.

Si el Pod sigue apareciendo como `Pending` o si quieres confirmar que ya está en marcha, esto es lo que está ocurriendo y lo que debes hacer:

### 1. El Pod debería pasar a `Running` automáticamente

Ahora que el PVC está vinculado, Kubernetes tiene el permiso necesario para montar el volumen en el nodo maestro y arrancar el contenedor.

Verifica el estado del Pod con:

```bash
kubectl get pods -n container-registry
```

### 2. ¿Qué pasa si el Pod sigue en `Pending`?

Si el PVC dice `Bound` pero el Pod no arranca, la causa más probable es el **Scheduler** (el encargado de decidir dónde corre el Pod). Como definimos una **`nodeAffinity`** en el PV, el Pod está obligado a correr en el nodo que especificaste (ej: `k3s-master`).

- **Causa:** El nombre del nodo en el archivo `01-registry-pv.yaml` no coincide exactamente con el nombre real del nodo en el clúster.
    
- **Comprobación:** 

```bash
   kubectl describe pod -n container-registry
```


  Busca al final en la sección `Events`. Si ves un mensaje como **"1 node(s) had volume node affinity conflict"**, confirma el nombre exacto de tu nodo con `kubectl get nodes`.
    

### 3. Verificación de la escritura en NFS

Una vez que el Pod esté en `STATUS: Running`, es vital confirmar que realmente está escribiendo en tu partición NFS. Ejecuta esto en la terminal de tu servidor:


```bash
ls -R /archives/registry-data/
```

**Resultado esperado:** Deberías empezar a ver una estructura de carpetas llamada `docker/registry/v2/`. Esto confirma que el registro está usando el almacenamiento externo de forma profesional.

---
### Resumen del estado actual de PV y PVC

- **PV:** `Bound` (Conectado al disco).
    
- **PVC:** `Bound` (Conectado al PV).
    
- **Siguiente paso:** Confirmar que el Pod está `Running` y hacer tu primer `podman push` hacia la nueva ruta.
    

¿Qué te muestra el comando `kubectl get pods -n container-registry` en este momento?

El Pod, muestra el **STATUS:** `Running`

Con el Pod en **Running** y el PVC en **Bound**, se ha completado con éxito la migración de la infraestructura de almacenamiento de tu registro hacia la partición NFS en `/archives`.


--- 
## Validación de la persistencia y acceso 

Ahora que el sistema está operativo, es el momento de validar que la persistencia física en el servidor y el acceso a la red funcionan como un conjunto profesional.

### 1. Validación de la Persistencia en el Host

Como ahora el almacenamiento es externo (NFS), los archivos deben aparecer directamente en tu carpeta de archivos. Verifica la estructura con este comando en el servidor:


```bash
ls -R /archives/registry-data/docker/registry/v2/repositories/
```

- **Resultado esperado:** Si el registro está vacío, verás la estructura de carpetas básica. Si ya realizaste un push, verás los nombres de tus imágenes.
    

### 2. Prueba de "Fuego" (Simulacro de Fallo)

Para confirmar que tu configuración de `PersistentVolume` con política `Retain` y el montaje NFS funcionan, puedes probar la resiliencia del sistema:

1. **Elimina el Pod:** 
```bash
kubectl delete pod -n container-registry -l app=registry
```
   
2. **Observa la recuperación:** Kubernetes creará un nuevo Pod automáticamente.
    
3. **Verifica los datos:** Al subir el nuevo Pod, este se conectará al mismo PVC y los datos en `/archives` seguirán intactos.
    

---

### 3. Verificación del Servicio (Acceso Externo)

Dado que configuramos el servicio como `LoadBalancer` en el archivo `03-registry-app.yaml`, comprueba qué IP ha asignado el clúster:

Bash

```
kubectl get svc -n container-registry
```

- **EXTERNAL-IP:** Debería ser la IP de tu nodo master (o la IP que maneje tu balanceador en el homelab).
    
- **Prueba de red:** Desde tu estación de trabajo (HP Victus), intenta acceder al catálogo: 

```bash
curl http://registro.local:5000/v2/_catalog
```

**Resultado:* 
```bash
{"repositories":[]}
```

¡Ese JSON vacío es la mejor noticia que podíamos recibir! Significa que el sistema está **perfectamente configurado** y el servicio es totalmente funcional.

El hecho de que recibas `{"repositories":[]}` confirma tres cosas fundamentales:

- **Conectividad de Red**: Tu estación de trabajo resuelve `registro.local` y llega al clúster K3s con éxito.
    
- **Servicio Activo**: El pod del registro está escuchando y respondiendo peticiones a través del `LoadBalancer`.
    
- **Estado Limpio**: Como acabas de migrar el almacenamiento a la partición NFS en `/archives`, es lógico que el catálogo esté vacío.

---

### Resumen de la Arquitectura Actual

Tu entorno de consultaría **datenmaniak** ahora cuenta con un flujo de trabajo de nivel empresarial:

- **Almacenamiento:** Centralizado en `/archives` vía NFS, fuera del ciclo de vida de los contenedores.
    
- **Seguridad de Datos:** Política de retención definida para evitar borrados accidentales del volumen físico.
    
- **Rendimiento:** Ejecución en el nodo master con afinidad garantizada.
    

**¿Qué sigue?** Ahora que el registro está estable en el almacenamiento definitivo, ¿quieres que procedamos a subir (push) una de tus aplicaciones PHP o HTML para probar el despliegue completo desde esta nueva ubicación?

## Procedimiento para llenar tu nuevo registro

Ahora vamos a realizar la primera subida ("push") para confirmar que los datos se guardan físicamente en `/archives/registry-data`.

#### 1. Etiquetar una imagen local

Desde tu laptop (HP Victus), toma cualquier imagen que tengas (por ejemplo, `nginx` o una de tus pruebas previas) y etiquétala para el nuevo registro:


```bash
podman tag nginx:latest registro.local:5000/test-nfs:v1
```

#### 2. Subir la imagen al clúster

Realiza el push. Recuerda que ya configuramos el clúster para confiar en este dominio por HTTP:


```bash
podman push registro.local:5000/test-nfs:v1
```

#### 3. Verificar el catálogo nuevamente

Ejecuta el comando que usaste antes:


```bash
curl http://registro.local:5000/v2/_catalog
```

**Resultado esperado:** `{"repositories":["test-nfs"]}`

---

### Verificación final en el Master (Control Plane)

Para cerrar el círculo y estar 100% seguros de que la persistencia en el **NFS** funciona, verifica la ruta física en tu servidor:


```bash
sudo ls -R /archives/registry-data/docker/registry/v2/repositories/
```

Si ves la carpeta `test-nfs`, has terminado la configuración de infraestructura con éxito. Tu entorno de consultoría **datenmaniak** ya tiene su "almacén" de imágenes profesional y persistente.

> [!Error]
> 
> No se ha registrado movimientos en el directorio `/archives/registry-data`

Por otra parte, se observa: 

```plaintext
pvc-992676b8-105a-4d1a-8d7c-a81290f6b292   20Gi       RWO            Delete           Bound       container-registry/registry-pvc   local-path     <unset>                          19m   Filesystem
registry-nfs-pv                            20Gi       RWO            Retain           Available                                     local-path     <unset>                          28m   Filesystem

```

Ese mensaje revela exactamente lo que está pasando: tienes un **vínculo fantasma**.

El `registry-pvc` se ha conectado a un volumen dinámico creado automáticamente por K3s (llamado `pvc-992676b8...`) en lugar de conectarse a tu `registry-nfs-pv`. 

Por eso se observa el PV manual como `Available` (libre) y no ves movimiento en `/archives/registry-data`.

Esto ocurre porque el "Provisioner" de K3s fue más rápido y creó un volumen nuevo para satisfacer la demanda del PVC antes de que este encontrara tu PV manual.

### Cómo corregirlo (El "Re-vínculo" Forzado)

Para obligar a Kubernetes a usar tu partición NFS, debemos hacer que el PVC sea mucho más específico.

#### 1. Limpieza total de los recursos actuales

Primero, eliminamos lo que está mal conectado para que el nombre quede libre:


```bash
kubectl delete deployment docker-registry -n container-registry
kubectl delete pvc registry-pvc -n container-registry
```

_(Espera a que se borren por completo)._

#### 2. Modifica el archivo `02-registry-pvc.yaml`

Para que el PVC no acepte cualquier volumen dinámico, vamos a añadirle un **`volumeName`**. Esto es como ponerle nombre y apellido al destinatario:

```yaml
# ESTA ES LA CLAVE: Forzamos el vínculo con tu PV de NFS
volumeName: registry-nfs-pv
```
#### 3. Aplica los cambios en orden

Ejecuta la secuencia nuevamente:

1. **Aplica el PVC:** `kubectl apply -f 02-registry-pvc.yaml`
    
2. **Verifica el estado:** `kubectl get pv,pvc -n container-registry`
    
    - **Ahora AMBOS deben decir `Bound`** y aparecer emparejados por nombre.
        
3. **Aplica el Deployment:** `kubectl apply -f 03-registry-app.yaml`
    

### ¿Por qué pasó esto?

Kubernetes, por defecto, intenta ser eficiente. Al ver que el PVC pedía `local-path`, el sistema creó un directorio automáticamente en la ruta por defecto de K3s (probablemente en `/var/lib/rancher/k3s/storage/`). Al añadir `volumeName` en el PVC, le quitas esa libertad y lo obligas a mirar exclusivamente hacia tu montaje en `/archives`.

**Haz el cambio en el YAML del PVC y verifica con `kubectl get pv`. ¿Ya aparecen los dos como `Bound`?**

Ahora que ambos están en **`Bound`**, la "tubería" está conectada correctamente: tu `PersistentVolumeClaim` (PVC) está oficialmente atado a la partición NFS en `/archives/registry-data`.

Esto significa que cualquier dato que el registro escriba en su carpeta interna `/var/lib/registry` ahora viajará físicamente a tu disco externo.

### 1. Aplicar el Deployment Final

Ejecuta el archivo que ya teníamos preparado para levantar el servicio:


```bash
kubectl apply -f 03-registry-app.yaml
```

### 2. Verificación de "Movimiento" en el Disco

Para confirmar que ahora sí hay actividad en tu partición `/archives`, realiza una prueba rápida desde tu laptop **HP Victus**:

1. **Sube una imagen de prueba:**
    
    
    ```bash
    podman tag nginx:latest registro.local:5000/test-nfs:v1
    podman push registro.local:5000/test-nfs:v1
    ```
    
2. **Revisa la carpeta física en el servidor (Master):**
    
    ```bash
    sudo ls -R /archives/registry-data/docker/registry/v2/repositories/
    ```
    

### 3. ¿Qué verás ahora?

A diferencia de antes, ahora deberías ver la carpeta `test-nfs` dentro de ese directorio. Como configuraste el PV con la política **`Retain`**, incluso si borras el Deployment o el PVC en el futuro, tus imágenes permanecerán seguras en `/archives` hasta que tú decidas borrarlas manualmente.

**¿Te aparece ya la carpeta `test-nfs` (o el nombre de la imagen que subiste) dentro de `/archives/registry-data`?** Con esto habrás validado la persistencia real de tu infraestructura **datenmaniak**.

> [!Error]
> Error: writing blob: initiating layer upload to /v2/test-nfs/blobs/uploads/ in registro.local:5000: received unexpected HTTP status: 500 Internal Server Error
> 

El error **500 Internal Server Error** es muy común al mover el almacenamiento a una partición NFS y suele tener una causa principal: **permisos de escritura en el sistema de archivos del host**.

Aunque el Pod esté en `Running`, el proceso interno del registro no tiene los privilegios necesarios para crear las carpetas de los "blobs" (los datos de la imagen) en tu directorio `/archives/registry-data`.

### 1. La Causa Técnica

El contenedor del registro corre internamente con el usuario `1000`. Cuando intenta escribir en `/archives/registry-data`, el sistema de archivos de tu servidor (Debian Trixie) bloquea la acción porque, probablemente, esa carpeta pertenece al usuario `root`.

### 2. La Solución Inmediata (En el Servidor Master)

Ejecuta estos tres comandos en la terminal de tu nodo master para preparar el terreno:


```bash
# Crear la estructura si no existe
sudo mkdir -p /archives/registry-data

# Cambiar el dueño al ID 1000 (el que usa el contenedor)
sudo chown -R 1000:1000 /archives/registry-data

# Dar permisos de lectura/escritura/ejecución
sudo chmod -R 775 /archives/registry-data
```

### 3. Verificar los Logs del Pod

Si después de aplicar los permisos el error persiste, el Pod te dirá exactamente por qué falló el guardado del "blob". Ejecuta:


```bash
kubectl logs -f deployment/docker-registry -n container-registry
```

Busca líneas que digan `filesystem: mkdir /var/lib/registry/docker: permission denied`. Si ves eso, confirma que el montaje NFS en `/archives` no tenga la opción `ro` (read-only) en tu archivo `/etc/fstab`.

### 4. ¿Por qué 500 y no 403?

En el protocolo de Docker Registry, cuando el motor de almacenamiento (storage driver) falla al intentar inicializar un archivo en el disco, el servidor no lo maneja como un error de "Acceso Denegado" de red, sino como un fallo crítico del sistema de archivos, devolviendo un error genérico 500.

**Prueba el `podman push` nuevamente tras aplicar el `chown`.** Ahora deberías ver cómo las capas (blobs) comienzan a subirse correctamente y, por fin, verás aparecer datos reales dentro de tu partición NFS.

---

> [!Success]
> Ha funcionado. Ahora si observo el contenido del directorio en `/archives/registry-data`

Ese "Ahora si observo el contenido" es la confirmación definitiva de que el ciclo se ha cerrado con éxito.

Ahora tienes una infraestructura de nivel profesional: el registro de contenedores está separando correctamente la **lógica** (el Pod en K3s) de los **datos** (tu partición NFS en `/archives`).

### ¿Qué hemos logrado exactamente?

- **Persistencia Real:** Al usar `/archives/registry-data` con una política de `Retain`, tus imágenes sobrevivirán incluso si reinstalas todo el clúster.
    
- **Permisos Alineados:** El error 500 se solucionó al sincronizar el UID `1000` del contenedor con los permisos del sistema de archivos de tu servidor Debian.
    
- **Acceso Centralizado:** Tu marca personal, **datenmaniak**, ya tiene un lugar seguro y privado donde almacenar sus desarrollos de DevOps.
    

### Una última comprobación de "salud"

Para quedarte totalmente tranquilo, puedes ejecutar este comando una última vez para ver la estructura de las capas que acabas de subir:

```bash
sudo find /archives/registry-data/docker/registry/v2/repositories/ -maxdepth 2
```

Deberías ver una jerarquía de carpetas que representa tus imágenes y sus etiquetas.

```plaintext
/archives/registry-data/docker/registry/v2/repositories/
/archives/registry-data/docker/registry/v2/repositories/test-nfs
/archives/registry-data/docker/registry/v2/repositories/test-nfs/_manifests
/archives/registry-data/docker/registry/v2/repositories/test-nfs/_layers
/archives/registry-data/docker/registry/v2/repositories/test-nfs/_uploads
```

---
### Comandos de Verificación:

- **Estado de los volúmenes:** 
```bash
kubectl get pv,pvc -n container-registry
```

` (Ambos deben estar en `Bound`).
    
- **Estado del Pod:**  (Debe estar en `Running`).
```bash
kubectl get pods -n container-registry
```
    
- **Prueba de catálogo:** 

```bash
  curl http://registro.local:5000/v2/_catalog
```

---


**Sugerencia de mantenimiento:** Como ahora estás usando una partición NFS, recuerda monitorear el espacio en disco de `/archives`. Kubernetes no siempre avisa cuando el disco físico del host se llena si el volumen es externo.


## Prueba para verificar que el clúster ya descarga imágenes correctamente

Para verificar que el clúster K3s ya puede descargar imágenes desde tu nuevo registro privado de forma fluida, aquí tienes un archivo YAML de prueba.

### Archivo: `04-test-pull.yaml`

Este manifiesto desplegará un Pod sencillo utilizando una imagen que tú mismo hayas subido previamente.


```yaml
apiVersion: v1
kind: Pod
metadata:
  name: test-registry-pull
  namespace: container-registry
spec:
  containers:
  - name: test-container
    # Reemplaza 'test-nfs:v1' por el nombre de la imagen que subiste
    image: registro.local:5000/test-nfs:v1
    imagePullPolicy: Always
    command: ["sh", "-c", "echo 'Pull exitoso desde el registro privado'; sleep 3600"]
```

### Pasos para la validación final:

1. **Asegúrate de haber reiniciado K3s** después de crear el archivo `registries.yaml`:
    
    ```bash
    sudo systemctl restart k3s
    ```
    
2. **Aplica el Pod de prueba:**
    
    ```bash
    kubectl apply -f 04-test-pull.yaml
    ```
    
3. **Monitorea el estado:**
    
    ```bash
    kubectl get pod test-registry-pull -n container-registry -w
    ```
    

### ¿Qué verificar?

- Si el estado pasa rápidamente a **`Running`**, significa que el nodo master confió en el protocolo HTTP y descargó la imagen sin problemas.
    
- Si ves un error **`ImagePullBackOff`**, ejecuta:
```bash
 kubectl describe pod test-registry-pull -n container-registry
```

- Si el error menciona `certificate signed by unknown authority` o `connection refused`, es probable que el archivo `/etc/rancher/k3s/registries.yaml` tenga algún error de indentación o falte el reinicio del servicio.


### Hacer troubleshooting del error: **`ImagePullBackOff`**

1. **Ajustar el archivo: `/etc/rancher/k3s/registries.yml`**

```yaml
mirrors:
  "registro.local:5000":
    endpoint:
      - "http://registro.local:5000"
configs:
  "registro.local:5000":
    tls:
      insecure_skip_verify: true
```


El error `ImagePullBackOff` después de configurar el `registries.yaml` suele reducirse a tres causas comunes. Vamos a inspeccionar el error exacto y a revisar los puntos críticos del manifiesto y la configuración.

### 1. Diagnóstico del error

Para saber exactamente por qué falla, ejecuta el siguiente comando y busca la sección **`Events`** al final:

```bash
kubectl describe pod test-registry-pull -n container-registry
```

- **Si el error dice `connection refused`**: El nombre `registro.local` no está siendo resuelto por el nodo donde corre el Pod.
    
- **Si el error dice `certificate signed by unknown authority`**: K3s no está leyendo correctamente el archivo `registries.yaml` o no se reinició el servicio.
    

---

### 2. Revisión del Manifiesto `04-test-pull.yaml`

Asegúrate de que el manifiesto tenga exactamente el nombre de la imagen que subiste. Un error común es la falta de coincidencia en el nombre o el tag.


```yaml
apiVersion: v1
kind: Pod
metadata:
  name: test-registry-pull
  namespace: container-registry
spec:
  containers:
  - name: test-container
    # IMPORTANTE: El nombre debe coincidir exactamente con el que ves en el catálogo
    image: registro.local:5000/test-nfs:v1 
    imagePullPolicy: Always
```

---

### 3. Verificaciones Críticas en el Nodo Master

#### A. Resolución de Nombres (DNS/Hosts)

El nodo master debe saber quién es `registro.local`. Ejecuta en el master: `ping registro.local`. Si no responde, debes añadir la IP de tu nodo master al archivo `/etc/hosts` **del propio servidor**:


```bash
# Ejemplo (usa tu IP real)
10.0.0.100  registro.local     <-- IP del nodo master
```

#### B. Formato de `registries.yaml`

K3s es muy estricto con la indentación (espacios) en `/etc/rancher/k3s/registries.yaml`. Asegúrate de que no haya tabulaciones y que use espacios:


```yaml
mirrors:
  "registro.local:5000":
    endpoint:
      - "http://registro.local:5000"
configs:
  "registro.local:5000":
    tls:
      insecure_skip_verify: true
```

#### C. Reinicio Completo

A veces, un `restart` rápido no es suficiente para que Containerd (el motor interno) recargue la configuración de los registros. Intenta:


```bash
sudo systemctl restart k3s
```

```bash
sudo pkill -9 containerd || true
```

---

### 4. Prueba de Fuego desde la Consola

Puedes verificar si el motor de contenedores de K3s ve tu registro usando `ctr` (la herramienta de diagnóstico de containerd):

**Ejecutar en el nodo master:** 
```bash
sudo k3s ctr images pull localhost:5000/test-nfs:v1
```

Si OK, una salida semejante sera así:

```plaintext
localhost:5000/test nfs:v1              	saved	
└──manifest (f380adac81b2)              	already exists	
   └──config (5dfe511714e1)             	already exists	
application/vnd.oci.image.manifest.v1+json sha256:f380adac81b22991c4664a7e607b2f3540e35549eab91148279178f0dcd12898
Pulling from OCI Registry (localhost:5000/test-nfs:v1)	elapsed: 0.0 s	total:  10.8 K	(476.8 KiB/s)	
```


**¿Qué mensaje te devuelve el comando `kubectl describe` o el comando `ctr`?** Eso nos dará la clave final para corregir el despliegue de **datenmaniak**.



## La solución final:

**En el nodo master: **

```yaml
mirrors:
  "10.0.0.100:5000":
    endpoint:
      - "http://127.0.0.1:5000"
  "registro.local:5000":
    endpoint:
      - "http://127.0.0.1:5000"

configs:
  "10.0.0.100:5000":
    tls:
      insecure_skip_verify: true
  "registro.local:5000":
    tls:
      insecure_skip_verify: true
```

**En los workers *(k3s-agent):**

```yaml
mirrors:
  "registro.local:5000":
    endpoint:
      - "http://10.0.0.100:5000"
configs:
  "registro.local:5000":
    tls:
      insecure_skip_verify: true
```

> [!Success]
> Aunque se  aplicaron diversos ajustes en **`/etc/rancher/k3s/registries.yaml`**,  el error  **`ImagePullBackOff`** persistia debido a que solo se habia aplicado estos ajustes en el nodo master.

## ¿Por qué es necesario declarar `registries.yaml` también en los workers?

La respuesta corta es: **porque cada nodo ejecuta su propio demonio `containerd` de forma independiente**, y es ese demonio el que realmente descarga y almacena las imágenes de contenedores.

### 🔍 Explicación detallada

En K3s, la arquitectura de descarga de imágenes funciona así:

1. **El control plane (servidor K3s)** no centraliza las imágenes. Cada nodo (tanto master como worker) ejecuta su propia instancia de `containerd`.
    
2. Cuando creas un Pod que se programa en un worker, es el `containerd` de **ese worker específico** quien debe descargar la imagen desde el registro.
    
3. El archivo `registries.yaml` es leído **exclusivamente por `containerd`** al iniciarse en cada nodo.
    

### 🎯 Consecuencias prácticas

Si **solo configuras `registries.yaml` en el nodo master**:

|Escenario|¿Funciona?|¿Por qué?|
|---|---|---|
|Pods que se ejecutan en el master|✅ Sí|El containerd del master tiene la configuración|
|Pods que se ejecutan en cualquier worker|❌ No|El containerd del worker no sabe cómo autenticarse con tu registro privado|

### 📋 Ejemplo concreto

Imagina que tienes:

- **1 nodo master** (k3s-server)
    
- **3 nodos worker** (k3s-agent-1, k3s-agent-2, k3s-agent-3)
    
- Un registro privado en `myregistry.local:5000` con autenticación
    

```yaml
# /etc/rancher/k3s/registries.yaml (configuración válida)
mirrors:
  "myregistry.local:5000":
    endpoint:
      - "https://myregistry.local:5000"
configs:
  "myregistry.local:5000":
    auth:
      username: "admin"
      password: "secreto123"
```

**Resultado:**

- ✅ Pods en `k3s-server` → funcionan
    
- ❌ Pods en `k3s-agent-1,2,3` → fallan con error `ImagePullBackOff` o `ErrImagePull`
    

El error típico sería:

```plaintext
Failed to pull image "myregistry.local:5000/app:v1":
  failed to authorize: failed to fetch anonymous token: 
  unexpected status: 401 Unauthorized
```

### 🚀 Mejores prácticas para gestionar la configuración

#### Opción 1: Configuración manual (ideal para pocos nodos)

```bash
# En CADA nodo (master y workers)
sudo mkdir -p /etc/rancher/k3s/
```

```bash
sudo tee /etc/rancher/k3s/registries.yaml > /dev/null << 'EOF'
mirrors:
  "myregistry.local:5000":
    endpoint:
      - "https://myregistry.local:5000"
configs:
  "myregistry.local:5000":
    auth:
      username: "admin"
      password: "secreto123"
EOF
```

### Reiniciar K3s en cada nodo
```bash
sudo systemctl restart k3s  # En master
sudo systemctl restart k3s-agent  # En workers
```


#### Opción 2: Automatización con Ansible (recomendada para producción)

```yaml
# playbook.yml
- name: Configurar registries.yaml en todos los nodos K3s
  hosts: k3s_cluster
  become: yes
  tasks:
    - name: Crear directorio
      file:
        path: /etc/rancher/k3s
        state: directory
        mode: '0755'
    
    - name: Copiar archivo registries.yaml
      copy:
        src: files/registries.yaml
        dest: /etc/rancher/k3s/registries.yaml
        mode: '0644'
    
    - name: Reiniciar servicio K3s
      systemd:
        name: "{{ 'k3s' if 'master' in inventory_hostname else 'k3s-agent' }}"
        state: restarted
```

#### Opción 3: Usar configuraciones por nodo con variables

```yaml

# group_vars/all.yml
registry_username: "admin"
registry_password: "secreto123"
registry_endpoint: "https://myregistry.local:5000"
# Template para registries.yaml.j2
mirrors:
  "{{ registry_endpoint | replace('https://', '') }}":
    endpoint:
      - "{{ registry_endpoint }}"
configs:
  "{{ registry_endpoint | replace('https://', '') }}":
    auth:
      username: "{{ registry_username }}"
      password: "{{ registry_password }}"
```

### 🧠 Casos especiales donde NO se necesita en workers

**Solo una excepción relevante:**

- Si usas **sistemas de archivos compartidos** (NFS, GlusterFS, Ceph) donde la caché de imágenes de containerd está en un volumen compartido. **No recomendado** por problemas de concurrencia y rendimiento.
    

### 🔧 Verificar la configuración aplicada

Puedes confirmar que un worker tiene la configuración correcta:


### En el worker
```bash
sudo cat /etc/rancher/k3s/registries.yaml
```

### Ver los logs para confirmar que se cargó
```bash
sudo journalctl -u k3s-agent | grep -i registry
```

**Salida esperada:** "Configured registry mirror endpoint"

### ✅ Resumen

|¿Es necesario en workers?|**SÍ, siempre**|
|---|---|
|¿Por qué?|Cada worker ejecuta su propio containerd|
|¿Qué pasa si no lo pongo?|ImagePullBackOff en pods de workers|
|¿Se hereda automáticamente del master?|No, K3s no sincroniza este archivo|
|¿Solución recomendada?|Automatizar con Ansible, Chef, Puppet, o scripts|

La regla de oro es: **si un nodo puede recibir Pods que necesiten imágenes de tu registro privado, ese nodo debe tener el archivo `registries.yaml` configurado correctamente.**


---

Con esto completas el ciclo total de tu infraestructura **datenmaniak**:

1. **Persistencia** en disco físico.
    
2. **Push** desde tu estación de trabajo.
    
3. **Pull** confiable desde el clúster.
    

---
*Documentación generada para el proyecto **datenmaniak**.*


