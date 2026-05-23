

## Importar la BD origen

```bash
mysqladmin -u root -p dkstore_db > dkstore.sql
```

**En algunos casos, sera necesario indicar el puerto:**

```bash
mysqldump -P3306 -u root -p dkstore_db > dkstore.sql
```

## Identificar el Pod


```bash
✗ kubectl get pods -n mariadb
NAME                       READY   STATUS    RESTARTS        AGE
mariadb-7d6b6dd4ff-75ndj   1/1     Running   3 (3h58m ago)   3d1h
```

## Comprobar el acceso

```bash
❯ kubectl -n mariadb exec -it mariadb-7d6b6dd4ff-75ndj -- bash
root@mariadb-7d6b6dd4ff-75ndj:/# 
```

```bash
exit
```

## Copiar el archivo `.sql` al Pod

```bash
❯ kubectl -n mariadb cp ./dkstore.sql mariadb-7d6b6dd4ff-75ndj:/tmp/dump-dkstore.sql
```

## Realizar comprobación del `.sql` en el Pod

```bash
❯ kubectl -n mariadb exec -it mariadb-7d6b6dd4ff-75ndj -- bash
```

**Verificar el archivo: **

```bash
ls -l /tmp/
```

```bash
-rw-r--r-- 1 1000 1000 9966 May  4 21:24 dump-dkstore.sql
root@mariadb-7d6b6dd4ff-75ndj:/# 
```

## Crear la base de datos

***Dentro del Pod:***

```bash
 mariadb -u root -p 
```

**Listar las actuales BD**

(opcional, solo para visualizar antes de la importación):**

```bash
 show databases;
+--------------------+
| Database           |
+--------------------+
| blog               |
| dkstore_db         |
| information_schema |
| laravel12          |
| mysql              |
| performance_schema |
| sys                |
+--------------------+
7 rows in set (0.002 sec)
```

## Con esta instrucción, se crea la base de datos


```sql
CREATE DATABASE dkstore;EXIT;
```


## Luego, ejecuta la importación:

```bash
mariadb -u root -p dkstore < /tmp/dump-dkstore.sql
```


## Confirmación final

```bash
mariadb -u root -p dkstore < /tmp/dump-dkstore.sql
```

```sql
show databases;
```

```plaintext
MariaDB [(none)]> show databases;
+--------------------+
| Database           |
+--------------------+
| blog               |
| dkstore            |    <--- he aqui la nueva
| dkstore_db         |
| information_schema |
| laravel12          |
| mysql              |
| performance_schema |
| sys                |
+--------------------+
8 rows in set (0.001 sec)
```










