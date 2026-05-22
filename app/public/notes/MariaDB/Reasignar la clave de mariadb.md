<img src="https://r2cdn.perplexity.ai/pplx-full-logo-primary-dark%402x.png" style="height:64px;margin-right:32px"/>

# puedo reasignar la clave de mariadb, si entro dentro de contenedor.?

Sí, puedes reasignar la clave de MariaDB entrando al contenedor, **pero** depende de cómo esté arrancado el servicio y de si tienes privilegios dentro del contenedor. En Docker es común hacerlo con `exec` al contenedor y luego cambiar la contraseña desde el cliente `mysql`/`mariadb` o arrancando temporalmente en modo seguro si no recuerdas la clave actual.[^1_1][^1_2]

## Caso normal

Si puedes entrar al contenedor y autenticarte con un usuario con permisos, basta con ejecutar algo como:

```sql
ALTER USER 'root'@'localhost' IDENTIFIED BY 'NuevaClaveSegura';
FLUSH PRIVILEGES;
```

Ese método es el enfoque habitual para MariaDB/MySQL cuando ya tienes acceso administrativo.[^1_3][^1_4]

## Si no recuerdas la clave

Si no puedes entrar con ninguna cuenta válida, normalmente hay que reiniciar MariaDB temporalmente con `--skip-grant-tables` para saltarse la autenticación, cambiar la clave y luego reiniciar el servicio normalmente. En contenedores, esto suele hacerse sobre el proceso de MariaDB dentro del propio contenedor o ajustando el comando de arranque del contenedor.[^1_5][^1_2][^1_3][^1_1]

## En contenedor Docker

Si tu MariaDB corre en Docker, el flujo práctico suele ser:

1. `docker exec -it <contenedor> bash`
2. Entrar al cliente `mysql` o `mariadb`.
3. Ejecutar `ALTER USER ...` para cambiar la clave.[^1_2][^1_1]

Si el contenedor fue creado con variables como `MYSQL_ROOT_PASSWORD`, eso solo sirve al primer inicio con un volumen nuevo; no cambia la contraseña después de que el directorio de datos ya existe.[^1_6]

## Precaución

Si la base de datos usa volumen persistente, cambiar la contraseña dentro del contenedor sí afecta a los datos persistidos, porque la cuenta está guardada en la propia instancia de MariaDB, no en el contenedor efímero. Por eso conviene revisar si estás tocando solo el contenedor o también el volumen de datos.[^1_6]

Si quieres, te paso los comandos exactos para tu caso: Docker simple, Docker Compose, o Podman.
<span style="display:none">[^1_10][^1_7][^1_8][^1_9]</span>

<div align="center">⁂</div>

[^1_1]: https://stackoverflow.com/questions/65715870/how-to-reset-mariadb-password-in-docker-compose

[^1_2]: https://gist.github.com/rordi/ab55c92848fd06884172ed22ae88d7df

[^1_3]: https://www.ionos.com/es-us/digitalguide/hosting/cuestiones-tecnicas/restablecer-la-contrasena-root-de-mariadb-o-mysql/

[^1_4]: https://serverspace.io/es/support/help/how-to-reset-mysql-and-mariadb-root-password-on-ubuntu-20-04-lts/

[^1_5]: https://www.digitalocean.com/community/tutorials/how-to-reset-your-mysql-or-mariadb-root-password

[^1_6]: https://www.ochobitshacenunbyte.com/2017/12/19/instalar-mariadb-via-docker/

[^1_7]: https://nubecolectiva.com/recuperar-password-mariadb-terminal/

[^1_8]: https://raiolanetworks.com/blog/recuperar-contrasena-root-mysql-mariadb/

[^1_9]: https://synay.net/es/support/kb/resetting-root-password-mariadb-server

[^1_10]: https://github.com/MariaDB/mariadb-docker/issues/99


---

# ✗ mariadb -u root -h localhost -p

Enter password:
ERROR 2002 (HY000): Can't connect to local server through socket '/var/lib/mysql/mysql.sock' (2)

Ese error significa que el **cliente está buscando el socket en `/var/lib/mysql/mysql.sock`, pero el servidor no está ahí** o directamente MariaDB no está levantado.[^2_1][^2_2]

## Qué revisar

- Verifica si MariaDB está corriendo dentro del contenedor.
- Comprueba la ruta real del socket, porque en MariaDB suele ser algo como `/run/mysqld/mysqld.sock` o `/var/run/mysqld/mysqld.sock`, no necesariamente `/var/lib/mysql/mysql.sock`.[^2_3][^2_1]
- Si el servidor está activo pero el cliente usa otra ruta, conecta indicando el socket correcto o usa TCP con `127.0.0.1`.[^2_1][^2_3]


## Comandos útiles

Prueba esto dentro del contenedor:

```bash
ps aux | grep mariadbd
ls -l /run/mysqld /var/run/mysqld /var/lib/mysql
mariadb -u root -p --protocol=tcp -h 127.0.0.1
```

Si encuentras el socket real, conecta así:

```bash
mariadb -u root -p --socket=/run/mysqld/mysqld.sock
```


## Si no arranca

Si `mariadbd` no está en ejecución, el problema no es la contraseña sino que el servicio no arrancó o falló al iniciar. En ese caso hay que revisar logs del contenedor y estado del proceso antes de intentar cambiar la clave.[^2_4][^2_5][^2_1]

## Para cambiar la clave

Una vez que logres entrar, puedes reasignar la contraseña con:

```sql
ALTER USER 'root'@'localhost' IDENTIFIED BY 'NuevaClave';
FLUSH PRIVILEGES;
```

Si quieres, te doy una secuencia exacta para **Docker** o **Podman** según cómo esté creado tu contenedor.
<span style="display:none">[^2_10][^2_6][^2_7][^2_8][^2_9]</span>

<div align="center">⁂</div>

[^2_1]: https://mariadb.com/docs/server/mariadb-quickstart-guides/mariadb-connection-troubleshooting-guide

[^2_2]: https://stackoverflow.com/questions/4448467/cant-connect-to-local-mysql-server-through-socket-var-lib-mysql-mysql-sock

[^2_3]: https://builder.ai2sql.io/blog/mysql-error-2002-cant-connect

[^2_4]: https://www.singlestore.com/blog/mysql-error-2022/

[^2_5]: https://discourse.ubuntu.com/t/mariadb-error-2002-hy000-cant-connect-to-local-server-through-socket-run-mysqld-mysqld-sock-2/53941

[^2_6]: https://stackoverflow.com/questions/11657829/error-2002-hy000-cant-connect-to-local-mysql-server-through-socket-var-run

[^2_7]: https://runebook.dev/en/docs/mariadb/troubleshooting-connection-issues/index

[^2_8]: https://stackoverflow.com/questions/64734786/mariadb-refusing-localhost-connections

[^2_9]: https://www.youtube.com/watch?v=lgp4c6GGqls

[^2_10]: https://troubleshoot.docs.webally.co.za/mariadbsocket/

