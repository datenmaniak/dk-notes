# Identificacion del problema para reiniciar la aplicacion dknotes


Despues de migrarse el Proxmox, se ajusto la particion NFS `/archives` del viejo Proxmox y se ajusto  a `/pool` en el Nuevo Proxmox. Este publica las particiones a traves de NFS al cluster k3s.

**IP de nuevo Proxmox:** 10.0.0.200


## Especificaciones del evento
No levanta el Pod de la aplicacion dknotes. Presenta STATUS: Init:0/1



```bash
❯ kubectl describe -n dknotes pod/dknotes-web-84979989c5-djzst
```



**Output:**

```yaml
Name:             dknotes-web-84979989c5-djzst
Namespace:        dknotes
Priority:         0
Service Account:  default
Node:             worker2/10.0.0.102
Start Time:       Tue, 14 Jul 2026 12:19:29 -0400
Labels:           app=dknotes
                  pod-template-hash=84979989c5
Annotations:      <none>
Status:           Pending
IP:               
IPs:              <none>
Controlled By:    ReplicaSet/dknotes-web-84979989c5
Init Containers:
  copy-code:
    Container ID:  
    Image:         local-reg.dk.lab:5000/dknotes-laravel:1.58.3
    Image ID:      
    Port:          <none>
    Host Port:     <none>
    Command:
      sh
      -c
      echo "Copiando código de la aplicación..."
      cp -r /var/www/html/. /app-code/
      
      # echo "Código copiado exitosamente"
      # ls -la /app-code/
      
      # Crear estructura de framework si no existe (para nuevos deployments)
      mkdir -p /app-code/storage/framework/{cache,sessions,views,testing}
      
      chown -R 33:33 /app-code/storage || true
      chmod -R 775 /app-code/storage || true
      
      echo "Setup completado"
      ls -la /app-code/storage/framework/
      
    State:          Waiting
      Reason:       PodInitializing
    Ready:          False
    Restart Count:  0
    Environment:    <none>
    Mounts:
      /app-code from app-code (rw)
      /var/run/secrets/kubernetes.io/serviceaccount from kube-api-access-vrghs (ro)
Containers:
  nginx:
    Container ID:   
    Image:          nginx:alpine
    Image ID:       
    Port:           80/TCP (http)
    Host Port:      0/TCP (http)
    State:          Waiting
      Reason:       PodInitializing
    Ready:          False
    Restart Count:  0
    Liveness:       http-get http://:80/health delay=10s timeout=1s period=10s #success=1 #failure=3
    Readiness:      http-get http://:80/health delay=5s timeout=1s period=5s #success=1 #failure=3
    Environment:    <none>
    Mounts:
      /etc/nginx/conf.d/default.conf from nginx-config-volume (rw,path="default.conf")
      /var/run/secrets/kubernetes.io/serviceaccount from kube-api-access-vrghs (ro)
      /var/www/html from app-code (ro)
  web-app:
    Container ID:   
    Image:          local-reg.dk.lab:5000/dknotes-laravel:1.58.3
    Image ID:       
    Port:           9000/TCP (fastcgi)
    Host Port:      0/TCP (fastcgi)
    State:          Waiting
      Reason:       PodInitializing
    Ready:          False
    Restart Count:  0
    Limits:
      cpu:     1
      memory:  1Gi
    Requests:
      cpu:      400m
      memory:   512Mi
    Liveness:   tcp-socket :9000 delay=30s timeout=1s period=10s #success=1 #failure=3
    Readiness:  tcp-socket :9000 delay=10s timeout=1s period=5s #success=1 #failure=3
    Environment:
      APP_ENV:        production
      APP_DEBUG:      true
      APP_URL:        https://dknotes.dk.lab
      APP_KEY:        <set to the key 'app_key' in secret 'dknotes-secrets'>  Optional: false
      DB_CONNECTION:  pgsql
      DB_HOST:        postgres-service.postgres.svc.cluster.local
      DB_PORT:        5432
      DB_DATABASE:    dknotes
      DB_USERNAME:    <set to the key 'username' in secret 'dknotes-secrets'>  Optional: false
      DB_PASSWORD:    <set to the key 'password' in secret 'dknotes-secrets'>  Optional: false
    Mounts:
      /var/run/secrets/kubernetes.io/serviceaccount from kube-api-access-vrghs (ro)
      /var/www/html from app-code (rw)
      /var/www/html/storage from storage-persistence (rw)
Conditions:
  Type                        Status
  PodReadyToStartContainers   False 
  Initialized                 False 
  Ready                       False 
  ContainersReady             False 
  PodScheduled                True 
Volumes:
  app-code:
    Type:       EmptyDir (a temporary directory that shares a pod's lifetime)
    Medium:     
    SizeLimit:  <unset>
  storage-persistence:
    Type:       PersistentVolumeClaim (a reference to a PersistentVolumeClaim in the same namespace)
    ClaimName:  dknotes-uploads-pvc
    ReadOnly:   false
  nginx-config-volume:
    Type:      ConfigMap (a volume populated by a ConfigMap)
    Name:      nginx-config-498kgbfgdk
    Optional:  false
  kube-api-access-vrghs:
    Type:                    Projected (a volume that contains injected data from multiple sources)
    TokenExpirationSeconds:  3607
    ConfigMapName:           kube-root-ca.crt
    Optional:                false
    DownwardAPI:             true
QoS Class:                   Burstable
Node-Selectors:              <none>
Tolerations:                 node.kubernetes.io/not-ready:NoExecute op=Exists for 300s
                             node.kubernetes.io/unreachable:NoExecute op=Exists for 300s
Events:
  Type     Reason            Age                 From               Message
  ----     ------            ----                ----               -------
  Warning  FailedScheduling  13m                 default-scheduler  0/3 nodes are available: 3 Insufficient cpu. no new claims to deallocate, preemption: 0/3 nodes are available: 3 No preemption victims found for incoming pod.
  Warning  FailedScheduling  10m (x14 over 13m)  default-scheduler  0/3 nodes are available: 3 Insufficient cpu. no new claims to deallocate, preemption: 0/3 nodes are available: 3 No preemption victims found for incoming pod.
  Normal   Scheduled         10m                 default-scheduler  Successfully assigned dknotes/dknotes-web-84979989c5-djzst to worker2
  Warning  FailedMount       31s (x13 over 10m)  kubelet            MountVolume.SetUp failed for volume "dknotes-uploads-pv" : mount failed: exit status 32
Mounting command: mount
Mounting arguments: -t nfs 10.0.0.200:/archives/dknotes /var/lib/kubelet/pods/13b6b7a3-9de9-4f57-949b-bf28218fe3c0/volumes/kubernetes.io~nfs/dknotes-uploads-pv
Output: mount.nfs: access denied by server while mounting 10.0.0.200:/archives/dknotes
```
