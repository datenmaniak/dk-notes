Se quiere migrar a Kubernetes una aplicación desarrollada con HTML, CSS, JS y PHP.

Es un proyecto personal cuyo objetivo ha sido poner el practica el Desarrollo Web con
una introducción a la programación orientada a objeto con PHP.

## 📦 Paso 1: Preparar la estructura de archivos para Docker




### Configuración de Base de datos del proyecto

```php
<?php
function conectDB() {
    $host = 'ws.homelab';  // ← Nombre contenedor
    // $host = 'db-dev';  // ← Nombre contenedor?
    $db = 'dkstore';
    $user = 'dk';
    $pass = 'chachita';

    $conn = new mysqli($host, $user, $pass, $db);
    if ($conn->connect_error) {
        die("Error: " . $conn->connect_error);
    }
    $conn->set_charset("utf8mb4");
    return $conn;
}
```

## Preparar el contenedor de la aplicación




## Verificaciones en el cluster K8s

```bash
❯ kubectl get pods,svc -n mariadb  
```

**Resultado:**

```                                         
NAME                           READY   STATUS    RESTARTS        AGE
pod/mariadb-7d6b6dd4ff-75ndj   1/1     Running   3 (4h43m ago)   3d2h

NAME                       TYPE           CLUSTER-IP      EXTERNAL-IP                        PORT(S)          AGE
service/mariadb-lb         LoadBalancer   10.43.147.77    10.0.0.100,10.0.0.101,10.0.0.102   3306:31391/TCP   3d2h
service/mariadb-nodeport   NodePort       10.43.204.20    <none>                             3306:32306/TCP   3d2h
service/mariadb-service    ClusterIP      10.43.210.155   <none>                             3306/TCP         3d2h

```

### **Paso 1: Verificar que el usuario 'dk' existe en MariaDB**


```bash
❯ kubectl exec -it -n mariadb mariadb-7d6b6dd4ff-75ndj -- mariadb -u root -p
```

### Dentro de la consola:
 
```sql
MariaDB [(none)]> 
```

```sql
SELECT User, Host FROM mysql.user WHERE User = 'dk';
```

### Si NO existe, créalo:

```sql
CREATE USER IF NOT EXISTS 'dk'@'%' IDENTIFIED BY 'chachita';
GRANT ALL PRIVILEGES ON dkstore.* TO 'dk'@'%';
FLUSH PRIVILEGES;
```

```sql
SELECT User, Host FROM mysql.user WHERE User = 'dk';
+------+------+
| User | Host |
+------+------+
| dk   | %    |
+------+------+
1 row in set (0.001 sec)
```

### Comprobar acceso a MariaDB con el usuario creado

```bash
❯ kubectl exec -it -n mariadb mariadb-7d6b6dd4ff-75ndj -- mariadb -u dk -p
```

#### Si todo hasta ahora ha sido con éxito, entonces:

```sql
Enter password: 
Welcome to the MariaDB monitor.  Commands end with ; or \g.
Your MariaDB connection id is 24
Server version: 11.4.10-MariaDB-ubu2404 mariadb.org binary distribution

Copyright (c) 2000, 2018, Oracle, MariaDB Corporation Ab and others.

Type 'help;' or '\h' for help. Type '\c' to clear the current input statement.

MariaDB [(none)]> show databases;
+--------------------+
| Database           |
+--------------------+
| dkstore            |
| information_schema |
+--------------------+
2 rows in set (0.002 sec)

MariaDB [(none)]> 
```

Aquí se observa la base de datos cuyo usuario tiene acceso. 

> [!NOTE]
> 
> Es recomendable limitar el acceso a la bases de datos, a solo  el usuario y su BD.


### **Paso 4: Crear ConfigMap y Secret en K3s**

#### **ConfigMap (datos no sensibles)**

#### **Secret (datos sensibles - usuario y contraseña)**



