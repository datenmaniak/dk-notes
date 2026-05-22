<img src="https://r2cdn.perplexity.ai/pplx-full-logo-primary-dark%402x.png" style="height:64px;margin-right:32px"/>

# la tabla tiene 3 columna: username, email y password_hash. quiero resignar la contrasena. estoy en mariadb

Sí: en MariaDB puedes **actualizar solo el hash** de la contraseña en esa columna, sin tocar `username` ni `email`. La forma exacta depende de si ya tienes el hash listo o si quieres que MariaDB lo genere por ti; la sintaxis para cambiar contraseñas de usuarios en MariaDB suele hacerse con `SET PASSWORD` o con `ALTER USER`, pero para una tabla propia normalmente haces `UPDATE` sobre `password_hash`.[^1_1][^1_2]

## Si ya tienes el nuevo hash

```sql
UPDATE tu_tabla
SET password_hash = 'nuevo_hash_aqui'
WHERE username = 'usuario';
```

Si prefieres identificar al usuario por email:

```sql
UPDATE tu_tabla
SET password_hash = 'nuevo_hash_aqui'
WHERE email = 'correo@dominio.com';
```


## Si quieres generar el hash fuera de MariaDB

Lo más común es generar un hash fuerte en tu aplicación o script y guardar ese resultado en `password_hash`. MariaDB no está pensada para guardar contraseñas en texto plano, y el valor almacenado debe ser un hash, no la contraseña original.[^1_3][^1_1]

## Si querías cambiar la clave de un usuario de MariaDB

Eso es distinto de actualizar una tabla propia. Para una cuenta de MariaDB, el patrón es algo como `SET PASSWORD FOR 'user'@'host' = PASSWORD('newpassword');` o `ALTER USER ... IDENTIFIED BY ...` según el caso.[^1_4][^1_1]

## Ejemplo completo

```sql
UPDATE users
SET password_hash = '$2y$10$....'
WHERE username = 'juan';
```

Después puedes comprobarlo con:

```sql
SELECT username, email, password_hash
FROM users
WHERE username = 'juan';
```

Si quieres, te paso el comando exacto para **bcrypt**, **argon2** o **SHA-256** según cómo estés generando `password_hash`.
<span style="display:none">[^1_10][^1_5][^1_6][^1_7][^1_8][^1_9]</span>

<div align="center">⁂</div>

[^1_1]: https://www.ionos.es/digitalguide/hosting/cuestiones-tecnicas/mariadb-change-user-password/

[^1_2]: https://www.ionos.com/es-us/digitalguide/hosting/cuestiones-tecnicas/mariadb-change-user-password/

[^1_3]: https://mariadb.com/kb/en/password/

[^1_4]: https://www.ionos.com/es-us/digitalguide/hosting/cuestiones-tecnicas/restablecer-la-contrasena-root-de-mariadb-o-mysql/

[^1_5]: https://www.ochobitshacenunbyte.com/2018/06/13/cambiar-contrasenas-de-usuarios-en-mysql-y-mariadb/

[^1_6]: https://www.ionos.mx/digitalguide/hosting/cuestiones-tecnicas/mariadb-change-user-password/

[^1_7]: https://www.youtube.com/watch?v=fJeS8k_56AM

[^1_8]: https://www.youtube.com/watch?v=DJPSpAIZMFM

[^1_9]: https://www.dbtech.digibeatrix.com/es/mariadb/mariadb-basic/mariadb-manage-users/

[^1_10]: https://kb.hostdime.com.mx/mysql-mariadb/actualizando-hash-contrasena-de-mysql-v4-1


---

# con bcrypt

Con bcrypt, lo normal es guardar un hash que empiece por algo como `$2y$...` o `$2b$...`, y luego reemplazar ese valor en tu columna `password_hash`.[^2_1][^2_5]

### Ejemplo en MariaDB

```sql
UPDATE users
SET password_hash = '$2y$10$TU_HASH_BCRYPT_AQUI'
WHERE username = 'juan';
```


### Cómo generar el hash

MariaDB no genera bcrypt nativamente para uso típico de aplicación; lo habitual es generarlo en tu app o en PHP con `password_hash($password, PASSWORD_BCRYPT)` y guardar el resultado en la tabla. Luego, para verificarlo al iniciar sesión, se usa `password_verify()` contra ese hash almacenado.[^2_5][^2_1]

### Importante

- No guardes la contraseña en texto plano.
- Un hash bcrypt ya incluye la sal dentro del propio valor.
- Si cambias la contraseña, solo actualizas `password_hash` con el nuevo hash.[^2_1][^2_5]


### En PHP

```php
$hash = password_hash('nueva_clave', PASSWORD_BCRYPT);
```

Y luego:

```sql
UPDATE users
SET password_hash = 'el_valor_de_$hash'
WHERE email = 'correo@dominio.com';
```

Si quieres, te doy un ejemplo exacto para **PHP + MariaDB** para actualizar la contraseña por `username` o por `email`.
<span style="display:none">[^2_2][^2_3][^2_4][^2_6][^2_7][^2_8][^2_9]</span>

<div align="center">⁂</div>

[^2_1]: https://codesignal.com/learn/courses/securing-your-symfony-app/lessons/secure-user-registration-and-login-with-bcrypt

[^2_2]: https://bcrypt-generator.com

[^2_3]: https://bcrypt.online

[^2_4]: https://en.wikipedia.org/wiki/Bcrypt

[^2_5]: https://auth0.com/blog/hashing-in-action-understanding-bcrypt/

[^2_6]: https://www.freecodecamp.org/news/how-to-hash-passwords-with-bcrypt-in-nodejs/

[^2_7]: https://www.browserling.com/tools/bcrypt

[^2_8]: https://www.youtube.com/watch?v=SMS_BN9IyO0

[^2_9]: https://www.webempresa.com/blog/bcrypt-que-es-y-para-que-funciona.html


---

# es posible hacer esto dentro de Mariadb?

Sí, **pero no directamente con bcrypt nativo dentro de MariaDB** en la forma habitual. MariaDB puede gestionar datos y hacer `UPDATE`, pero el hash bcrypt normalmente se genera fuera de la base de datos y luego se guarda en la columna `password_hash`.[^3_2][^3_6]

## Qué sí puedes hacer

Puedes actualizar el valor de la columna con el hash bcrypt ya generado:

```sql
UPDATE users
SET password_hash = '$2y$10$...'
WHERE username = 'juan';
```

Eso funciona perfecto si el hash ya viene desde tu aplicación o script.[^3_2]

## Qué no suele hacer MariaDB

MariaDB no es la herramienta normal para **crear bcrypt** desde SQL puro. Lo típico es generar el hash en PHP, Node, Python u otra capa de aplicación, y después escribir ese resultado en la tabla.[^3_6][^3_2]

## Flujo recomendado

1. Generas la nueva contraseña con bcrypt en tu app.
2. Guardas el string resultante en `password_hash`.
3. Comparas luego con el mismo mecanismo en el login.

Ejemplo en PHP:

```php
$hash = password_hash('nueva_clave', PASSWORD_BCRYPT);
```

Luego:

```sql
UPDATE users
SET password_hash = '$2y$10$...'
WHERE email = 'correo@dominio.com';
```


## Respuesta corta

Dentro de MariaDB, **sí puedes guardar y actualizar** un hash bcrypt, pero **no es lo normal generar bcrypt allí mismo**; eso se hace fuera y luego se persiste en la tabla.[^3_6][^3_2]
<span style="display:none">[^3_1][^3_10][^3_3][^3_4][^3_5][^3_7][^3_8][^3_9]</span>

<div align="center">⁂</div>

[^3_1]: https://soporte.donweb.com/hc/es/articles/24249696583444--Cómo-crear-y-gestionar-una-base-de-datos-MariaDB-en-mi-Cloud-Server

[^3_2]: https://soporte.donweb.com/hc/es/articles/24249696583444--Cómo-crear-y-gestionar-una-base-de-datos-en-MariaDB

[^3_3]: https://www.ionos.com/es-us/digitalguide/servidores/know-how/que-es-mariadb/

[^3_4]: https://emanuelpeg.blogspot.com/2019/10/procedimientos-almacenados-y-funciones.html

[^3_5]: https://www.reddit.com/r/Database/comments/1oqs22a/mariadb_vs_postgresql_understanding_the/

[^3_6]: https://www.arsys.es/blog/mariadb

[^3_7]: https://aws.amazon.com/es/compare/the-difference-between-mariadb-vs-mysql/

[^3_8]: https://groups.google.com/g/publicesvfoxpro/c/Y5xn45NzMno

[^3_9]: https://www.youtube.com/watch?v=RwvpitfevNU

[^3_10]: https://www.youtube.com/watch?v=puXq9hYuvBY


---

# hazme un script en bash que pida un argumento (clave) y genera la el hash

Claro, aquí va un script en Bash que recibe la contraseña como argumento y genera su hash con `bcrypt` usando `python3` y `bcrypt` (si lo tienes instalado).[^4_1][^4_5]

### Requisito

Primero asegúrate de tener `bcrypt` en Python:

```bash
pip3 install bcrypt
```


### Script `gen-bcrypt.sh`

Guarda esto como `gen-bcrypt.sh`:

```bash
#!/bin/bash

# Uso: ./gen-bcrypt.sh "tu_contraseña"

if [ $# -eq 0 ]; then
    echo "Error: falta la contraseña."
    echo "Uso: $0 \"contraseña\""
    exit 1
fi

PASSWORD="$1"

python3 -c "
import sys
import bcrypt

password = sys.argv[^4_1].encode('utf-8')
salt = bcrypt.gensalt(rounds=12)
hash_bcrypt = bcrypt.hashpw(password, salt)
print(hash_bcrypt.decode('utf-8'))
" "$PASSWORD"
```


### Permisos y ejecución

```bash
chmod +x gen-bcrypt.sh
./gen-bcrypt.sh "mipassword"
```

Eso imprimirá algo como:

```text
$2b$12$....
```

Que puedes copiar directamente en la columna `password_hash` de tu tabla en MariaDB.[^4_2][^4_5]
<span style="display:none">[^4_3][^4_4][^4_6][^4_7][^4_8][^4_9]</span>

<div align="center">⁂</div>

[^4_1]: https://itsfoss.com/es/pasando-argumentos-bash/

[^4_2]: https://www.reiser.cl/2017/05/24/bash-script-para-mostrar-2-formas-de-indicar-que-falta-ingresar-algun-argumento/

[^4_3]: https://es.stackoverflow.com/questions/228519/como-pasar-parametros-con-referencia-a-un-script-bash

[^4_4]: https://www.reddit.com/r/linuxadmin/comments/1cjalnq/how_do_you_secure_passwords_in_bash_scripts/

[^4_5]: https://www.datacamp.com/es/tutorial/how-to-write-bash-script-tutorial

[^4_6]: https://labex.io/es/tutorials/shell-passing-arguments-to-the-script-388811

[^4_7]: https://atareao.es/tutorial/scripts-en-bash/preguntar-al-usuario-en-bash/

[^4_8]: https://www.hostingtg.com/blog/bash-script/

[^4_9]: https://www.youtube.com/watch?v=pXUgBQs4CPg


---

# mejor genera todo el script en PHP...

Perfecto, aquí tienes un script en **PHP** que recibe la contraseña como argumento y genera el hash bcrypt listo para meter en la columna `password_hash` de MariaDB.

### Script `gen-bcrypt.php`

```php
#!/usr/bin/env php
<?php

// Uso: php gen-bcrypt.php "tu_contraseña"

if ($argc < 2) {
    fwrite(STDERR, "Error: falta la contraseña.\n");
    fwrite(STDERR, "Uso: {$argv[^5_0]} \"contraseña\"\n");
    exit(1);
}

$password = $argv[^5_1];

$hash = password_hash($password, PASSWORD_BCRYPT);

echo $hash . "\n";
```


### Cómo usarlo

1. Guárdalo como `gen-bcrypt.php`.
2. Dale permiso de ejecución si quieres llamarlo directamente:
```bash
chmod +x gen-bcrypt.php
```

3. Ejecutar:
```bash
php gen-bcrypt.php "mipassword"
```

o, si has puesto el shebang adecuado y permisos:

```bash
./gen-bcrypt.php "mipassword"
```


### Salida

Te devolverá algo como:

```text
$2y$10$U.G2T1...etc
```

que puedes copiar en la columna `password_hash` de tu tabla en MariaDB.[^5_1][^5_2]

<div align="center">⁂</div>

[^5_1]: https://codesignal.com/learn/courses/securing-your-symfony-app/lessons/secure-user-registration-and-login-with-bcrypt

[^5_2]: https://auth0.com/blog/hashing-in-action-understanding-bcrypt/

