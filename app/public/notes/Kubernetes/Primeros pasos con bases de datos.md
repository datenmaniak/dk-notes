

1. **Persistencia** con el disco HDD montado en Proxmox
    
2. **Base de datos** MariaDB/MySQL en un contenedor (StatefulSet)
    
3. **Aplicación de ejemplo** conectada a la base de datos
    
4. **Volumen persistente** usando el directorio `/mnt/databases/mariadb_data`

## 🏗️ Arquitectura propuesta

```bash
Proxmox host (192.168.1.201)
  └── /mnt/databases/mariadb_data (HDD montado)
        ↓
Clúster K3s (10.0.0.100, .101, .102)
  └── PersistentVolume (hostPath)
        ↓
  └── PersistentVolumeClaim
        ↓
  └── StatefulSet (MariaDB)
        ↓
  └── Service (ClusterIP)
        ↓
  └── Application Pod (conectada a DB)
```

### 🚀 Paso 1: Crear el PersistentVolume (PV) en Kubernetes

El PV apunta al directorio HDD montado en Proxmox.

#### Crear archivo pv-mariadb.yaml
   
```bash
cat  <<EOF >> 01-pv-mariadb.yaml
apiVersion: v1
kind: PersistentVolume
metadata:
  name: mariadb-pv
spec:
  capacity:
    storage: 50Gi
  volumeMode: Filesystem
  accessModes:
    - ReadWriteOnce
  persistentVolumeReclaimPolicy: Retain
  storageClassName: manual
  hostPath:
    path: /mnt/databases/mariadb_data
    type: DirectoryOrCreate
EOF
```


```bash
kubectl apply -f 01-pv-mariadb.yaml
```

 **Verificar:**
 ```bash
 kubectl get pv
 ```

### 🚀 Paso 2: Crear el PersistentVolumeClaim (PVC)

```bash
cat <<EOF>>  02-pvc-mariadb.yaml
apiVersion: v1
kind: PersistentVolumeClaim
metadata:
  name: mariadb-pvc
  namespace: default
spec:
  accessModes:
    - ReadWriteOnce
  volumeMode: Filesystem
  resources:
    requests:
      storage: 50Gi
  storageClassName: manual
  volumeName: mariadb-pv
EOF
```

**Aplicar y verificar:**

```bash
kubectl apply -f 02-pvc-mariadb.yaml
```

```bash
kubectl get pvc
```


### 🚀 Paso 3: Crear Secret para la base de datos (seguridad)

**Crear secret con contraseñas**

```bash
kubectl create secret generic mariadb-secret \
  --from-literal=MYSQL_ROOT_PASSWORD=r00t1ng23 \
  --from-literal=MYSQL_DATABASE=appdb \
  --from-literal=MYSQL_USER=appuser \
  --from-literal=MYSQL_PASSWORD=appass123
```

**Verificar**
```bash

kubectl get secret mariadb-secret
```

### 🚀 Paso 4: Crear StatefulSet para MariaDB

```bash
cat <<EOF>> 04-statefulset-mariadb.yaml
apiVersion: apps/v1
kind: StatefulSet
metadata:
  name: mariadb
  namespace: default
spec:
  serviceName: mariadb
  replicas: 1
  selector:
    matchLabels:
      app: mariadb
  template:
    metadata:
      labels:
        app: mariadb
    spec:
      containers:
      - name: mariadb
        image: mariadb:10.11
        ports:
        - containerPort: 3306
          name: mysql
        env:
        - name: MYSQL_ROOT_PASSWORD
          valueFrom:
            secretKeyRef:
              name: mariadb-secret
              key: MYSQL_ROOT_PASSWORD
        - name: MYSQL_DATABASE
          valueFrom:
            secretKeyRef:
              name: mariadb-secret
              key: MYSQL_DATABASE
        - name: MYSQL_USER
          valueFrom:
            secretKeyRef:
              name: mariadb-secret
              key: MYSQL_USER
        - name: MYSQL_PASSWORD
          valueFrom:
            secretKeyRef:
              name: mariadb-secret
              key: MYSQL_PASSWORD
        volumeMounts:
        - name: data
          mountPath: /var/lib/mysql
        resources:
          requests:
            memory: "512Mi"
            cpu: "250m"
          limits:
            memory: "1Gi"
            cpu: "500m"
      volumes:
      - name: data
        persistentVolumeClaim:
          claimName: mariadb-pvc
  volumeClaimTemplates:
  - metadata:
      name: data
    spec:
      accessModes: [ "ReadWriteOnce" ]
      resources:
        requests:
          storage: 50Gi
      storageClassName: manual
EOF
```

**Aplica:**

```bash
 kubectl apply -f 04-statefulset-mariadb.yaml
```

**Verificar:**
```bash
kubectl get statefulset
```

```bash
kubectl get pods -l app=mariadb
```


### 🚀 Paso 5: Crear Service para MariaDB

```bash
cat <<EOF>> 05-service-mariadb.yaml
apiVersion: v1
kind: Service
metadata:
  name: mariadb
  namespace: default
spec:
  selector:
    app: mariadb
  ports:
  - port: 3306
    targetPort: 3306
    name: mysql
  clusterIP: None
EOF
```

```bash 
kubectl apply -f 05-service-mariadb.yaml
```

**Verificar:**

```bash
kubectl get svc mariadb
```
