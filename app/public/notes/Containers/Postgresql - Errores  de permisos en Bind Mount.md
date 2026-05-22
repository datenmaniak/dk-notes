## Error: unable to start container


```bash
podman-compose up -d --build    
```

```yaml
# docker-compose.yml
services:
  # El Motor de Base de Datos
  postgres:
    build:
      context: ./postgres
    container_name: dk-db
    restart: always
    environment:
      POSTGRES_DB: dk_notes
      POSTGRES_USER: dk_user
      POSTGRES_PASSWORD: dkpassword
    volumes:
      - ~/pg-data:/var/lib/postgresql/data:Z
    networks:
      - dk-network
```


> [!Error]
> 1caa516b1553c65ca046eda4d05b8410f38fb23c0ac698762cb95e3875fe915
> Error: unable to start container "2d49bce2e22e4c8107fd9c3ac87a36a0a530b8b3b3395b3311da7a92ec3cedfc": lsetxattr(label=system_u:object_r:container_file_t:s0:c71,c223) /var/home/datenmaniak/pg-data: operation not permitted


Ese error específico (`lsetxattr ... operation not permitted`) es el síntoma definitivo de un choque entre el modo **rootless** de Podman y las restricciones de tu sistema de archivos en la HP Victus.

Ocurre porque Podman intenta cambiar la etiqueta de SELinux del directorio `~/pg-data` (debido al sufijo `:Z`), pero al no ser root real, el kernel le prohíbe realizar esa operación de "etiquetado" en una carpeta de tu `$HOME` que ya tiene sus propias restricciones.

Aquí tienes las tres formas de solucionarlo, de la más recomendada a la más manual:

### 1. La Solución "Keep It Simple" (Recomendada)

En lugar de forzar a Podman a re-etiquetar tu carpeta personal, usa un **Volumen Nombrado** gestionado por Podman. Esto permite que Podman controle los permisos internamente sin pelearse con los permisos de tu `$HOME`.

Modifica tu `docker-compose.yml`:


```yaml
services:
  postgres:
    # ... resto de la config ...
    volumes:
      - dk-db-data:/var/lib/postgresql/data # Sin ruta al home, solo el nombre
# ...
volumes:
  dk-db-data: # Podman lo creará en su zona segura (~/.local/share/containers/storage)
```

### 2. La Solución para mantener `~/pg-data` (Desactivar Labeling)

Si insistes en tener los archivos visibles en esa carpeta específica de tu Home, debes quitar el sufijo `:Z` y, en su lugar, ejecutar el contenedor permitiendo que ignore la separación de etiquetas de SELinux, o simplemente usando el modo `keep-id`.

En tu `docker-compose.yml`, añade esto al servicio de `postgres`:


```yaml
    security_opt:
      - label:disable
    userns_mode: "keep-id"
    volumes:
      - ~/pg-data:/var/lib/postgresql/data:z # 'z' minúscula
```

- **`keep-id`**: Hace que el usuario dentro del contenedor tenga el mismo UID que tú fuera (datenmaniak), evitando el error de `chown`.

### 3. La Solución de Emergencia (Si nada de lo anterior funciona)

Si estás en una distribución que bloquea agresivamente los cambios de atributos (como Fedora Silverblue/Aurora que mencionaste antes), a veces la forma más rápida es inicializar la carpeta con permisos totales antes de subir el contenedor:

Bash

```
# 1. Detén todo
podman-compose down

# 2. Limpia y da permisos totales temporalmente para que Postgres tome el control
sudo rm -rf ~/pg-data/*
chmod 777 ~/pg-data

# 3. Quita el :Z del docker-compose y levanta
podman-compose up -d
```

---
## Solución:

> [!Success]
> **Aplicar los puntos 2 y 3.**
> 
> Successfully tagged localhost/dk-notes_postgres:latest
> .
> .
> dk-db
> dk-app
> dk-proxy
> 

