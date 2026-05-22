
## 🗑️ Eliminar todo lo relacionado con MariaDB

### Paso 1: Eliminar StatefulSet y Pods


#### Eliminar StatefulSet (esto también elimina los pods)

```bash
kubectl delete statefulset mariadb --ignore-not-found
```

####  Eliminar cualquier pod suelto

```bash
kubectl delete pod mariadb-0 --ignore-not-found
```

### Paso 2: Eliminar Services

#### Eliminar servicios relacionados

```
kubectl delete svc mariadb --ignore-not-found
```

```
kubectl delete svc mariadb-lb --ignore-not-found
```

### Paso 3: Eliminar PVCs

#### Eliminar todos los PVCs relacionados con mariadb
```
kubectl delete pvc mariadb-pvc --ignore-not-found
```

```
kubectl delete pvc data-mariadb-0 --ignore-not-found
```


### Paso 4: Eliminar PVs

#### Eliminar PersistentVolumes

```
kubectl delete pv mariadb-pv --ignore-not-found
```

### Paso 5: Eliminar Secret

#### Eliminar secret (opcional, puedes conservarlo para la nueva instalación)

```
kubectl delete secret mariadb-secret --ignore-not-found
```

### Paso 6: Verificar limpieza total

#### Verificar que no quede nada
```
echo "=== StatefulSets ==="
kubectl get statefulset | grep mariadb
echo "=== Pods ==="
kubectl get pods | grep mariadb
echo "=== Services ==="
kubectl get svc | grep mariadb
echo "=== PVCs ==="
kubectl get pvc | grep mariadb
echo "=== PVs ==="
kubectl get pv | grep mariadb
echo "=== Secrets ==="
kubectl get secret | grep mariadb
```

**Todo debe estar vacío (sin resultados).**

## ✅ Estado después de la limpieza


✅ Cluster limpio
✅ Sin recursos residuales de MariaDB
✅ Listo para comenzar desde cero

## 🚀 ¿Preparado para la nueva instalación?

Una vez confirmes que la limpieza fue exitosa, podemos comenzar de nuevo con:

1. PersistentVolume
2. PersistentVolumeClaim
3. Secret
4. StatefulSet (con nodeSelector al master)
5. Service
    

**¿Ejecutamos la limpieza y luego comenzamos de nuevo?** Confírmame cuando hayas ejecutado los comandos.