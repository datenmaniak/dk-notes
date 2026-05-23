
## Como puedo replicar una bd en mariadb

Para replicar una base de datos en MariaDB, configura la replicación maestro-esclavo (o primaria-réplica) habilitando los binlogs en el nodo primario y sincronizando con el secundario.

Este método asegura alta disponibilidad y respaldo automático de cambios en la BD.

### Requisitos previos

- Dos servidores MariaDB (ej. Ubuntu/Fedora) con conectividad de red.
- Acceso root a ambos.
- Copia de seguridad inicial de la BD del maestro (usa `mysqldump` para BD existentes).


### Configuración nodo maestro (primario)

Edita `/etc/mysql/mariadb.conf.d/50-server.cnf` (o `/etc/my.cnf` en Fedora):

```
bind-address = 0.0.0.0
server-id = 1
log_bin = /var/log/mysql/mysql-bin.log
max_binlog_size = 100M
binlog-format = mixed
```

Reinicia: `systemctl restart mariadb`.

Crea usuario réplica:

```
mysql -u root -p
CREATE USER 'replication'@'%' IDENTIFIED BY 'tu_contraseña_segura';
GRANT REPLICATION SLAVE ON *.* TO 'replication'@'%';
FLUSH PRIVILEGES;
```

Obtén posición binlog: `SHOW MASTER STATUS;` (anota File y Position).

### Configuración nodo esclavo (secundario)

Edita el archivo de config similarmente, pero con `server-id = 2`.

Restaura la copia de la BD: `mysql -u root -p < backup.sql`.

Conecta al maestro:

```
mysql -u root -p
STOP SLAVE;
CHANGE MASTER TO 
  MASTER_HOST='IP_MAESTRO',
  MASTER_USER='replication',
  MASTER_PASSWORD='tu_contraseña_segura',
  MASTER_LOG_FILE='mysql-bin.000001',  -- Del SHOW MASTER STATUS
  MASTER_LOG_POS=786;  -- Del SHOW MASTER STATUS
START SLAVE;
```

Verifica: `SHOW SLAVE STATUS\G` (busca Slave_IO_Running: Yes y Slave_SQL_Running: Yes).

### Verificación

Inserta datos en el maestro (ej. `CREATE DATABASE prueba; USE prueba; CREATE TABLE test...; INSERT...`), verifica en esclavo con `SELECT * FROM...`.

Si hay errores, revisa logs en `/var/log/mysql/error.log` y ajusta firewalls/red.
<span style="display:none">[^1_10][^1_3][^1_4][^1_5][^1_6][^1_7][^1_8][^1_9]</span>

---

## Quise decir, hacer una copia de una bd

Para hacer una copia (backup) de una base de datos en MariaDB, usa la herramienta `mysqldump` (o `mariadb-dump`), que genera un archivo SQL con la estructura y datos.

Es el método lógico más simple y portable, ideal para migraciones o respaldos.

### Requisitos

- Acceso a MariaDB como usuario con privilegios (ej. root).
- Instala si falta: `dnf install mariadb` (Fedora) o `apt install mariadb-client` (Ubuntu).


### Copia de una BD específica

Ejecuta en terminal:

```
mysqldump -u root -p nombre_bd > /ruta/backup.sql
```

- `-u root -p`: Usuario y contraseña.
- `nombre_bd`: Reemplaza por tu BD (ej. `empresa_db`).
- `> backup.sql`: Archivo de salida.[^2_1][^2_3]

Para solo estructura (sin datos): `mysqldump --no-data -u root -p nombre_bd > estructura.sql`.[^2_3]

### Copia de todas las BDs

```
mysqldump -u root -p --all-databases > /ruta/backup_completo.sql
```

## Útil para respaldos totales.

### Restaurar la copia

Crea la BD si no existe: `mysql -u root -p -e "CREATE DATABASE nombre_bd;"`.

Luego:

```
mysql -u root -p nombre_bd < /ruta/backup.sql
```

Verifica con `SHOW DATABASES;` y `USE nombre_bd; SHOW TABLES;`.

### Opciones avanzadas

- `--single-transaction`: Para InnoDB sin bloqueos largos.[^2_3]
- Automatiza con cron: `0 2 * * * mysqldump ... > $(date +\%Y\%m\%d).sql` (diario a las 2AM).
Para BDs grandes, considera Mariabackup (física, en caliente).


---

## Quiero asignar los permisos a un determinado usuario para una determinada bd.

Para asignar permisos a un usuario específico en una base de datos determinada de MariaDB, primero crea el usuario si no existe y luego usa `GRANT` para conceder privilegios precisos.[^3_1][^3_2]

Esto sigue el principio de menor privilegio, limitando accesos a lo necesario.[^3_3]

###  Crear usuario

Conéctate como root: `mysql -u root -p` y ejecuta:

```
CREATE USER 'mi_usuario'@'localhost' IDENTIFIED BY 'contraseña_segura';
```

- `'mi_usuario'@'localhost'`: Usuario local (usa '%' para cualquier host).[^3_1][^3_3]


### Asignar todos los permisos en BD específica

```
GRANT ALL PRIVILEGES ON nombre_bd.* TO 'mi_usuario'@'localhost';
```

- `nombre_bd.*`: A todas las tablas de esa BD.
- `ALL PRIVILEGES`: Incluye SELECT, INSERT, UPDATE, DELETE, etc.[^3_2][^3_4]


### Asignar permisos específicos

Ejemplos comunes:

```
GRANT SELECT, INSERT, UPDATE ON nombre_bd.* TO 'mi_usuario'@'localhost';  -- Lectura/escritura sin eliminar
GRANT SELECT ON nombre_bd.* TO 'solo_lectura'@'localhost';  -- Solo lectura
```

Separa privilegios con comas.[^3_5][^3_3]

### Aplicar y verificar

Siempre ejecuta:

```
FLUSH PRIVILEGES;
```

Verifica: `SHOW GRANTS FOR 'mi_usuario'@'localhost';`.[^3_2][^3_1]

### Revocar permisos (si es necesario)

```
REVOKE DELETE ON nombre_bd.* FROM 'mi_usuario'@'localhost';
FLUSH PRIVILEGES;
```

Para eliminar usuario: `DROP USER 'mi_usuario'@'localhost';`


---

## ❯ mysql -u dk -pchachita -P3306 -hlocalhost

ERROR 1045 (28000): Access denied for user 'dk'@'10.89.1.2' (using password: YES)

El error 1045 indica que el usuario 'dk' no existe para la conexión desde '10.89.1.2' (IP de tu máquina), o la contraseña 'chachita' es incorrecta, o no tiene permisos para conectar desde ahí.

**MariaDB distingue usuarios por host:**

(ej. 'dk'@'localhost' vs 'dk'@'%'), por lo que 'localhost' no aplica desde IP remota.

### Verificar usuarios existentes

Conéctate como root (si puedes):

```
mysql -u root -p -h localhost
```

Lista usuarios: `SELECT User, Host FROM mysql.user WHERE User='dk';`.[^4_1]

### Solución 1: Crear/Actualizar usuario con host correcto

**Como root en MariaDB:**

```bash
CREATE USER IF NOT EXISTS 'dk'@'10.89.1.2' IDENTIFIED BY 'chachita';
```

**Para permitir para cualquier IP:**

```sql
 CREATE USER 'dk'@'%' IDENTIFIED BY 'chachita';
GRANT ALL PRIVILEGES ON *.* TO 'dk'@'%';  -- Ajusta según BD
FLUSH PRIVILEGES;
```

**Prueba conexión.**

## Solución 2: Corregir contraseña si existe

```sql
ALTER USER 'dk'@'10.89.1.2' IDENTIFIED BY 'chachita';
FLUSH PRIVILEGES;
```

**O reset: **

`SET PASSWORD FOR 'dk'@'%' = PASSWORD('chachita');` (versión antigua).

## Solución 3: Acceso root si bloqueado

Si root falla, para en servicio: `systemctl stop mariadb`, inicia sin grants: `mysqld_safe --skip-grant-tables &`, conecta sin pass, actualiza password:

```bash
UPDATE mysql.user SET Password=PASSWORD('nueva_pass') WHERE User='root';
FLUSH PRIVILEGES;
```

Reinicia servicio.[^4_7][^4_6]

### Consejos adicionales

- Verifica bind-address en `/etc/my.cnf` (debe ser 0.0.0.0 o tu IP para conexiones remotas).[^4_1]
- Revisa logs: `tail -f /var/log/mysql/error.log`.
- Prueba con `-h 127.0.0.1` en vez de localhost si es loopback.[^4_2]
<span style="display:none">[^4_10][^4_8][^4_9]</span>

