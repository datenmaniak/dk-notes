<img src="https://r2cdn.perplexity.ai/pplx-full-logo-primary-dark%402x.png" style="height:64px;margin-right:32px"/>

# desde la consola de mariabd, quiero cambiar la clave de algunos usuarios

Para cambiar la clave de usuarios en MariaDB desde su consola, usa comandos SQL estándar como `ALTER USER` o `SET PASSWORD`, que son compatibles y recomendados en versiones modernas.[^1_1][^1_2][^1_3]

## Conexión inicial

Accede a la consola de MariaDB como root o un usuario con privilegios de administrador:

```
mysql -u root -p
```

Ingresa la contraseña actual cuando se solicite.[^1_4]

## Comando principal: ALTER USER

Este es el método más seguro y directo para cambiar la clave de un usuario específico (reemplaza `'usuario'@'host'`, como `'miusuario'@'localhost'`, y `'nueva_clave'`):

```
ALTER USER 'usuario'@'host' IDENTIFIED BY 'nueva_clave';
FLUSH PRIVILEGES;
```

Afecta solo al usuario indicado y aplica cambios inmediatamente.[^1_5][^1_6][^1_1]

## Alternativa: SET PASSWORD

Para compatibilidad o cambios simples:

```
SET PASSWORD FOR 'usuario'@'host' = PASSWORD('nueva_clave');
FLUSH PRIVILEGES;
```

Úsalo si no tienes privilegios completos con `ALTER USER`.[^1_2][^1_7][^1_8]

## Verificar usuarios y hosts

Lista usuarios para confirmar nombres y hosts:

```
SELECT User, Host FROM mysql.user;
```

Cambia solo los necesarios para evitar afectar accesos remotos o locales.[^1_9][^1_10]

## Notas importantes

- Siempre ejecuta `FLUSH PRIVILEGES;` para recargar privilegios.
- En contenedores como Podman (común en tu setup con Fedora/Ubuntu), accede vía `podman exec` al contenedor de MariaDB.
- Prueba el login con la nueva clave: `mysql -u usuario -p`.[^1_10]
<span style="display:none">[^1_11][^1_12][^1_13][^1_14][^1_15][^1_16][^1_17][^1_18][^1_19][^1_20]</span>

<div align="center">⁂</div>

[^1_1]: https://usuariodebian.blogspot.com/2022/01/mysql-mariadb-y-phpmyadmin-comandos.html

[^1_2]: https://mariadb.com/docs/server/reference/sql-statements/account-management-sql-statements/set-password

[^1_3]: https://runebook.dev/es/docs/mariadb/alter-user/index

[^1_4]: https://www.ionos.com/es-us/digitalguide/hosting/cuestiones-tecnicas/mariadb-change-user-password/

[^1_5]: https://www.ochobitshacenunbyte.com/2018/06/13/cambiar-contrasenas-de-usuarios-en-mysql-y-mariadb/

[^1_6]: https://www.youtube.com/watch?v=00o3UvMINqk

[^1_7]: https://www.ionos.mx/digitalguide/hosting/cuestiones-tecnicas/mariadb-change-user-password/

[^1_8]: https://www.kinamo.be/en/knowledge-base/how-do-i-change-the-password-of-a-mysql-mariadb-user

[^1_9]: https://parzibyte.me/blog/posts/cambiar-contrasena-usuario-mysql/

[^1_10]: https://www.arsys.es/blog/cambiar-contrasena-mysql

[^1_11]: https://www.youtube.com/watch?v=ATPri3S_aPo

[^1_12]: http://cuadernoasterisk.blogspot.com/2011/10/cambiar-la-clave-de-mysql-las-claves.html

[^1_13]: https://desarrolloweb.com/articulos/cambiar-clave-root-mariadb-mysql

[^1_14]: https://www.javierrguez.com/cambiar-la-password-de-root-en-mysql/

[^1_15]: https://www.youtube.com/watch?v=fJeS8k_56AM

[^1_16]: https://www.youtube.com/watch?v=slJ4YthWAeU

[^1_17]: https://blog.carreralinux.com.ar/2017/03/cambiar-la-clave-de-root-mysql-mariadb/

[^1_18]: https://www.hostwinds.es/tutorials/how-to-use-mysql-mariadb-from-command-line

[^1_19]: https://www.youtube.com/watch?v=cT_J4MAAI6g

[^1_20]: https://stackoverflow.com/questions/41846000/enable-password-and-unix-socket-authentication-for-mariadb-root-user


---

# aclaratorio: no del sistema sino de una tabla de una determinada BD.

Para actualizar la clave (contraseña) de usuarios de MariaDB almacenada directamente en una tabla de una base de datos específica (no la tabla `mysql.user`), accede a la consola y modifica el campo correspondiente con `UPDATE`. Esto es común en aplicaciones personalizadas donde las credenciales se guardan en tablas como `users`.[^2_1][^2_2]

## Pasos para conectar y seleccionar

Conéctate como root o usuario con permisos:

```
mysql -u root -p nombre_de_tu_bd
```

Selecciona la BD si no lo hiciste: `USE nombre_de_tu_bd;`.[^2_3][^2_4]

## Comando UPDATE básico

Actualiza la clave en la tabla (reemplaza `tabla_usuarios`, `campo_clave` como `password`, `id_usuario` o condición WHERE):

```
UPDATE tabla_usuarios 
SET campo_clave = PASSWORD('nueva_clave') 
WHERE id_usuario = 123;
```

Usa `PASSWORD()` para hashear con el algoritmo nativo de MariaDB (mysql_native_password por defecto).[^2_3][^2_1]

## Opciones de hash

- Para mysql_native_password (recomendado moderno): `PASSWORD('nueva_clave')`.
- Para ed25519 (más seguro, si el plugin está activo): `ALTER USER` no aplica aquí; usa plugin específico o SHA2(512, ...).
- Verifica el hash actual: `SELECT campo_clave FROM tabla_usuarios WHERE ...;`.
Ejecuta para múltiples usuarios ajustando el WHERE.[^2_4][^2_2]


## Confirmar cambios

```
SELECT id_usuario, campo_clave FROM tabla_usuarios WHERE id_usuario = 123;
```

Prueba el login en tu app. Siempre haz backup: `CREATE TABLE backup_tabla AS SELECT * FROM tabla_usuarios;` antes.[^2_3]
<span style="display:none">[^2_10][^2_11][^2_12][^2_5][^2_6][^2_7][^2_8][^2_9]</span>

<div align="center">⁂</div>

[^2_1]: https://parzibyte.me/blog/posts/cambiar-contrasena-usuario-mysql/

[^2_2]: https://www.youtube.com/watch?v=ATPri3S_aPo

[^2_3]: https://blog.carreralinux.com.ar/2017/03/cambiar-la-clave-de-root-mysql-mariadb/

[^2_4]: https://www.ochobitshacenunbyte.com/2018/06/13/cambiar-contrasenas-de-usuarios-en-mysql-y-mariadb/

[^2_5]: https://www.infomaniak.com/es/asistencia/faq/846/cambiar-la-contrasena-de-usuario-de-mysql-mariadb

[^2_6]: https://www.ionos.com/es-us/digitalguide/hosting/cuestiones-tecnicas/mariadb-change-user-password/

[^2_7]: https://www.youtube.com/watch?v=fJeS8k_56AM

[^2_8]: https://www.youtube.com/watch?v=00o3UvMINqk

[^2_9]: https://www.ionos.com/es-us/digitalguide/hosting/cuestiones-tecnicas/restablecer-la-contrasena-root-de-mariadb-o-mysql/

[^2_10]: https://cubepath.com/en/docs/gestión-de-bases-de-datos/gestion-de-usuarios-y-permisos-en-mysql-mariadb

[^2_11]: https://www.reddit.com/r/mariadb/comments/1nq828i/mariadb_change_password_at_first_login/

[^2_12]: https://serverspace.io/es/support/help/how-to-reset-mysql-and-mariadb-root-password-on-ubuntu-20-04-lts/


---

# la clave debe coincidir con el hash que produce PHP al cambiar la clave.

Para que el hash de la clave en la tabla de MariaDB coincida exactamente con el generado por PHP, usa el mismo algoritmo: típicamente `password_hash()` con `PASSWORD_DEFAULT` o `PASSWORD_BCRYPT` (bcrypt), que produce un string de 60 caracteres comenzando por `$2y$`. No uses `PASSWORD()` de MariaDB, ya que genera mysql_native_password (incompatible).[^3_1][^3_2]

## Generar hash en PHP

Ejecuta este script PHP para obtener el hash exacto (ajusta cost si tu app lo usa):

```php
<?php
$nueva_clave = 'tu_nueva_clave';
$hash = password_hash($nueva_clave, PASSWORD_DEFAULT);  // O PASSWORD_BCRYPT
echo $hash;
?>
```

Copia el output (ej: `$2y$10$abc...`). Usa `PASSWORD_ARGON2ID` solo si tu PHP lo soporta y la app lo verifica.[^3_3][^3_1]

## Actualizar en MariaDB

En la consola de MariaDB (selecciona tu BD primero):

```
UPDATE tabla_usuarios 
SET campo_clave = '$2y$10$abc...'  -- Pega el hash de PHP aquí
WHERE id_usuario = 123;
COMMIT;
```

El campo debe ser VARCHAR(255) para bcrypt. Verifica: `SELECT * FROM tabla_usuarios WHERE id_usuario = 123;`.[^3_1]

## Verificación en app PHP

Tu app usa `password_verify($clave_input, $hash_bd)` para validar; con este hash coincidirá perfectamente. Evita SHA256 plano (inseguro sin salt); bcrypt es estándar seguro.[^3_4][^3_1]
<span style="display:none">[^3_10][^3_5][^3_6][^3_7][^3_8][^3_9]</span>

<div align="center">⁂</div>

[^3_1]: https://www.php.net/manual/es/function.password-hash.php

[^3_2]: http://micmap.org/php-by-example/manual/es/function.password-hash.html

[^3_3]: https://php-hash.polilla.studio

[^3_4]: https://es.linkedin.com/learning/domina-php-programacion-segura/uso-de-password-hash-y-password-verify-para-asegurar-contrasenas-almacenadas

[^3_5]: https://www.php.net/manual/en/function.password-hash.php

[^3_6]: https://nelkodev.com/php/protege-tus-datos-con-algoritmos-de-hashing-en-php/

[^3_7]: https://learntutorials.net/it/php/topic/530/funzioni-di-hashing-della-password

[^3_8]: https://codeshack.io/php-password-hash-generator/

[^3_9]: https://www.geeksforgeeks.org/php/how-to-use-bcrypt-for-hashing-passwords-in-php/

[^3_10]: https://certidevs.com/seguridad-cifrado-de-claves-password-contrasena

