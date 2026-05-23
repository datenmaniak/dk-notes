La forma correcta es crear o habilitar el usuario `root` para el host `%`, que representa cualquier IP. En MariaDB el permiso se define como `'usuario'@'host'`, así que `root@'%'` no es lo mismo que `root@'localhost'`.

## Comandos

Entra a MariaDB y ejecuta algo como esto:

```sql
CREATE USER 'root'@'%' IDENTIFIED BY 'tu_clave'; GRANT ALL PRIVILEGES ON *.* TO 'root'@'%' WITH GRANT OPTION; FLUSH PRIVILEGES;
```


Si `root` ya existe, en vez de `CREATE USER` usa `ALTER USER` o vuelve a crear la entrada correcta para `%`.
## Si usas contenedor

Hazlo desde dentro del contenedor:

```bash
docker exec -it <contenedor> mariadb -u root -p

```

Luego aplica los `GRANT` anteriores. En contenedores, esto suele ser más fiable que intentar entrar desde fuera con `localhost`.

## Mejor práctica

Aunque puedes permitir `root` desde cualquier IP, **no es recomendable** por seguridad; lo habitual es crear un usuario admin específico y limitarlo al host o IP necesarios. La documentación y guías de administración recomiendan dar acceso remoto solo al usuario que lo necesite, no a `root`.

## Ejemplo más seguro


```sql

`CREATE USER 'admin'@'%' IDENTIFIED BY 'clave_fuerte'; GRANT ALL PRIVILEGES ON mibd.* TO 'admin'@'%'; FLUSH PRIVILEGES;`
```


Eso te da acceso administrativo sin exponer `root`.

