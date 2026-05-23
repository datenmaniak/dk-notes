

## Estructura de directorios (recomendada)

```plaintext
 ~/proyecto-X 
❯ tree .
.
├── app      <--  aqui se genera la estructura Laravel de la aplicacion 
                   generada por composer
├── docker-compose.yml
│ 
├── laravel
│   ├── Dockerfile
│   └── php.ini
├── nginx
│   ├── default.conf
│   └── Dockerfile
├── postgres
│   ├── Dockerfile
│   └── init-scripts
│       ├── 01-check-and-create.sql
│       └── 02-create-dkuser.sql
└── sh         <--- Scripts personalizados para simplificar testing
    └── ver-user-at-postgres.sh
```

## Dockerfile sugerido para este caso

El `Dockerfile` para  este caso es bastante simple, ya que la imagen oficial ya hace casi todo el trabajo. Aquí te muestro el sugerido:

### Dockerfile para nginx

```Dockerfile
# Usamos la imagen oficial de Nginx sobre Alpine
FROM nginx:alpine

# Eliminamos la configuración por defecto de Nginx
RUN rm /etc/nginx/conf.d/default.conf

# Copiamos tu configuración personalizada (la que tiene la lógica de "fastcgi_pass laravel:9000")
COPY default.conf /etc/nginx/conf.d/default.conf

# No es necesario WORKDIR porque Nginx no lo usa para servir archivos
# La ruta la define 'root' en default.conf

# Exponemos el puerto 80
EXPOSE 80

# El CMD por defecto de nginx:alpine ya es correcto
# CMD ["nginx", "-g", "daemon off;"]  # ← No es necesario porque la imagen base ya lo tiene
```

## Docker compose sugerido (aplicar con Dockerfile sugerido)

Este docker-compose ha sido afinado y actualmente se ha logrado crear el entorno de Desarrollo Laravel para la aplicación `dknotes`.  El objetivo de esta app esta dividido en varios puntos:

1. Crear una app de manera local utilizando contenedores
2. Migrar la app a Kuberntes
3.  Realizar ensayos de CI/CD.



```yml
services:
  # El Motor de Base de Datos
  postgres:
    build:
      context: ./postgres
    container_name: dk-db
    restart: always
    environment:
      POSTGRES_USER: postgres
      POSTGRES_PASSWORD: shogun7  # ← Cambia por tu clave real
      POSTGRES_DB: dknotes
    security_opt:
      - label:disable
    userns_mode: "keep-id"
    volumes:
      - /var/home/datenmaniak/pg-data:/var/lib/postgresql/data
      - ./postgres/init-scripts:/docker-entrypoint-initdb.d:Z  # Scripts de inicialización
    networks:
      - dk-network
    ports:
      - "5432:5432"
    healthcheck:
      test: ["CMD-SHELL", "pg_isready -U postgres -d dknotes"]
      interval: 10s
      timeout: 5s
      retries: 5
      start_period: 30s



  # El Procesador de PHP (Laravel)
  laravel:
    build:
      context: ./laravel
    container_name: dk-app
    restart: always
    security_opt:
      - label:disable
    userns_mode: "keep-id"
    volumes:
      - ./app:/var/www/html
    networks:
      - dk-network
    ports:
      - "9000:9000"
    depends_on:
      postgres:
        condition: service_healthy
    healthcheck:
      test: ["CMD", "php-fpm", "-t"]
      interval: 10s
      timeout: 5s
      retries: 5
      start_period: 30s


  # El Servidor Web (Nginx)
  nginx:
    build:
      context: ./nginx
    container_name: dk-proxy
    restart: always
#    security_opt:
#      - label:disable
#    userns_mode: "keep-id"
    ports:
      - "8080:80"
    volumes:
      - ./app:/var/www/html:Z
      # mi propia configuracion
      - ./nginx/default.conf:/etc/nginx/conf.d/default.conf:Z
    networks:
      - dk-network
    depends_on:
      laravel:
        condition: service_healthy
    healthcheck:
      test: ["CMD", "wget", "--no-verbose", "--tries=1", "--spider", "http://localhost/health"]
      interval: 15s
      timeout: 5s
      retries: 3
      start_period: 10s
      
      
  # El Servidor Web (Nginx)
  nginx:
    build:
      context: ./nginx
    container_name: dk-proxy
    restart: always
#    security_opt:
#      - label:disable
#    userns_mode: "keep-id"
    ports:
      - "8080:80"
    volumes:
      - ./app:/var/www/html:Z
      # mi propia configuracion
      - ./nginx/default.conf:/etc/nginx/conf.d/default.conf:Z
    networks:
      - dk-network
    depends_on:
      laravel:
        condition: service_healthy
    healthcheck:
      test: ["CMD", "wget", "--no-verbose", "--tries=1", "--spider", "http://localhost/health"]
      interval: 15s
      timeout: 5s
      retries: 3
      start_period: 10s

networks:
  dk-network:
    external: true
    name: dk-network

```


## Configuracion del proxy web

Se maneja desde el archivo `nginx/default.conf` e inyectado como un volumen via docker-compose.

```conf
server {
    listen 80;
    server_name localhost;
    
    root /var/www/html/public;
    index index.php index.html;

    # Healthcheck endpoint
    location = /health {
        access_log off;
        return 200 "healthy\n";
        add_header Content-Type text/plain;
    }

    # Manejo de archivos estáticos
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    # Procesamiento de PHP
    location ~ \.php$ {
        fastcgi_split_path_info ^(.+\.php)(/.+)$;
        fastcgi_pass laravel:9000;
        fastcgi_index index.php;
        include fastcgi_params;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        fastcgi_param PATH_INFO $fastcgi_path_info;
    }

    # Denegar acceso a archivos ocultos
    location ~ /\. {
        deny all;
    }

    # Denegar acceso a archivos internos de Laravel
    location ~ ^/(storage|bootstrap|config|database|routes|resources)/ {
        deny all;
    }
}

```

## Verificación del proxy

```bash
curl http://localhost:8080/health
```

```plaintext
healthy
```

## Acceso a la app


```url
http://localhost:8080
```





