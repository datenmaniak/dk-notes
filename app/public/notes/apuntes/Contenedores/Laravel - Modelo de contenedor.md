
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

El `Dockerfile` para Laravel en este caso es bastante simple, ya que la imagen oficial ya hace casi todo el trabajo. Aquí te muestro el sugerido:

### Dockerfile para Laravel

```Dockerfile
# Usamos PHP 8.3 FPM sobre Alpine para mínima huella de seguridad y tamaño
FROM php:8.3-fpm-alpine

# Evitar que genere el archivo '.ash_history', en el 
#  WORKDIR, a fin de evitar el Conflicto del directorio vacio 
ENV HISTFILE=/tmp/
RUN echo 'export HISTFILE=/dev/null' >> /etc/profile
#

# Instalamos dependencias de sistema necesarias para PostgreSQL y utilidades
RUN apk add --no-cache \
    postgresql-dev \
    libpng-dev \
    libzip-dev \
    zip \
    unzip \
    git \
    curl

# Instalamos extensiones de PHP (pdo_pgsql es vital para tu base de datos Postgres)
RUN docker-php-ext-install pdo pdo_pgsql pgsql gd zip

# Instalamos Composer (Cerebro de dependencias de Laravel)
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Definir el HOME del usuario al crearlo
RUN adduser -D -h /home/datenmaniak datenmaniak

# Configuramos el directorio de trabajo dentro del contenedor
WORKDIR /var/www/html

# Ajustamos permisos para que el usuario de PHP pueda escribir en storage (clave en Laravel)
RUN chown -R www-data:www-data /var/www/html

# Exponemos el puerto 9000 para que Nginx pueda conectarse vía FastCGI
EXPOSE 9000

CMD ["php-fpm"]

```

## ¿Por qué este Dockerfile?

Para el contenedor de Laravel (`dk-app`), la elección de **Alpine Linux** se reduce a tres pilares fundamentales: **eficiencia, seguridad y velocidad**.

---

## 1. ¿Por qué Alpine Linux?

Alpine Linux es una distribución diseñada específicamente para contenedores. A diferencia de Debian o Ubuntu Server, no hereda herramientas de escritorio ni paquetería pesada innecesaria.

- **Tamaño mínimo:** Una imagen base de Ubuntu o Debian suele pesar entre 70 MB y 100 MB. Alpine pesa apenas **5 MB**. Al sumarle PHP y sus extensiones, el contenedor final sigue siendo increíblemente ligero, lo que acelera drásticamente la descarga, el despliegue y el inicio en tu entorno Podman.
    
- **Consumo de recursos:** Consume mucha menos memoria RAM en reposo (ideal para  trabajar en una propia estación de trabajo).
    
- **Superficie de ataque reducida:** Al no incluir herramientas que no vamos a usar (como gestores de correo, editores de texto por defecto o utilidades de red complejas), hay menos vulnerabilidades potenciales de seguridad.
    

---

## 2. El propósito de las herramientas instaladas

Para que Laravel (un framework PHP moderno) funcione correctamente sobre una base tan minimalista como Alpine, necesitamos "hidratar" el sistema con herramientas específicas. Aunque cada configuración puede variar ligeramente, un `Dockerfile` robusto para Laravel en Alpine suele instalar este grupo de herramientas esenciales:

### A. Herramientas del Sistema y Compilación

- **`bash` / `sh`:** Alpine utiliza `ash` por defecto como shell. Instalar `bash` asegura la compatibilidad con scripts de automatización comunes en el ecosistema de Laravel.
    
- **`git` / `curl` / `unzip`:** Son herramientas críticas para **Composer**. Cuando ejecutas `composer create-project` o `composer install`, Composer utiliza `curl` para descargar los paquetes, `unzip` para descomprimir los archivos `.zip` (recuerda el flag `--prefer-dist`) y `git` en caso de que necesite clonar un repositorio directamente desde GitHub.
    

### B. El Motor de Ejecución (PHP y Administrador de Procesos)

- **`php8x`:** El intérprete de PHP.
    
- **`php8x-fpm` (FastCGI Process Manager):** Esta es la herramienta clave. Nginx no sabe cómo interpretar código PHP; solo sabe servir archivos estáticos (HTML, CSS). `PHP-FPM` actúa como un servicio en segundo plano que escucha las peticiones que le envía Nginx, procesa el código PHP de Laravel y le devuelve el resultado a Nginx para que este lo envíe al navegador.
    

### C. Extensiones de PHP (El "Soporte" de Laravel)

Laravel requiere ciertas extensiones de PHP para interactuar con el sistema, la base de datos y manejar la seguridad:

- **`php8x-pdo_pgsql` o `php8x-pgsql`:** El driver nativo para que PHP pueda hablar el lenguaje de **PostgreSQL**. Sin esto, el comando `php artisan migrate` fallaría inmediatamente diciendo que el driver `pgsql` no se encuentra.
    
- **`php8x-mbstring` / `php8x-openssl`:** Herramientas de cifrado y manejo de cadenas de texto multibyte. Son obligatorias para que la función `php artisan key:generate` funcione y para asegurar el inicio de sesión de los usuarios.
    
- **`php8x-dom` / `php8x-xml` / `php8x-tokenizer`:** Extensiones que utiliza Composer y los componentes internos de Laravel (como el motor de plantillas Blade) para parsear código y configuraciones.
    

---

## Docker compose sugerido (aplicar con Dockerfile sugerido)


```yml
# docker-compose.yml
services:
  # El Motor de Base de Datos
  #postgres:
  #  build:
  #    context: ./postgres
  #  container_name: dk-db
  #  restart: always
  #
  #  resto de la configuracion de Postgres
  #

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
 # nginx:
 #   build:
 #     context: ./nginx
 #   container_name: dk-proxy
 #
 # resto de la configuracion nginx.
 #  
    
networks:
  dk-network:
    external: true
    name: dk-network
```

## Recomendaciones

Antes de generar el esqueleto de Laravel, es importante que el directorio de la aplicación se encuentre totalmente vació. De lo contrario, se producirán errores.

**Ejemplo de un caso:**

He tomado como ejemplo este caso que no fue completado ya que se observo un error en los argumentos al momento de crear la estructura de la aplicación en el directorio  `app/` , así queda demostrado  **El conflicto con el directorio**.

```plaintext
app
└── app
    ├── app
    ├── artisan
    ├── bootstrap
    ├── composer.json
    ├── composer.lock
    ├── config
    ├── database
    ├── package.json
    ├── phpunit.xml
    ├── public
    ├── README.md
    ├── resources
    ├── routes
    ├── storage
    ├── tests
    ├── vendor
    └── vite.config.js
```

#### Accedo al contenedor de Laravel

```bash
❯ podman exec -it dk-app sh
```

#### Verifico la ruta e intento generar la estructura de directorios de la aplicación Laravel.

```
~ $ pwd
/var/www/html
~ $ composer create-project laravel/laravel . --no-scripts --no-progress --prefer-dist
Creating a "laravel/laravel" project at "./"

In CreateProjectCommand.php line 368:
                                                     
  Project directory "/var/www/html/." is not empty.  
                                                     

create-project [-s|--stability STABILITY] [--prefer-source] [--prefer-dist] [--prefer-install PREFER-INSTALL] [--repository REPOSITORY] [--repository-url REPOSITORY-URL] [--add-repository] [--dev] [--no-dev] [--no-custom-installers] [--no-scripts] [--no-progress] [--no-secure-http] [--keep-vcs] [--remove-vcs] [--no-install] [--no-audit] [--audit-format AUDIT-FORMAT] [--no-security-blocking] [--ignore-platform-req IGNORE-PLATFORM-REQ] [--ignore-platform-reqs] [--ask] [--] [<package> [<directory> [<version>]]]
```

### El conflicto del "Directorio Vacío"

> [!NOTE]
> Composer, por seguridad, se niega a ejecutar `create-project` en una carpeta que contenga **absolutamente cualquier cosa**. Para Composer, un solo archivo oculto significa que podrías estar sobrescribiendo un proyecto existente.

#### Solución: Eliminar absolutamente todo

   **Fuera del contenedor:**
```bash
find app/ -mindepth 1 -delete -print
```

   **Dentro del contenedor:** 
```bash
find $PWD -mindepth 1 -delete -print 
```

### Otra alternativa para dejar el directorio limpio.

El dilema de eliminar todo el contenido del directorio y que aun aparezca el archivo  `.ash_history` puede volverse fastidioso.

Ese archivo `.ash_history` es el historial de comandos de la shell interactiva de Alpine (`ash`). Se crea automáticamente en el `HOME` del usuario en cuanto ejecutas un comando interactivo (como `podman exec -it dk-app sh`), y como tu `WORKDIR` dentro del contenedor está apuntando al mismo directorio que el `HOME` de tu usuario `datenmaniak`, el archivo cae directamente en la raíz de tu proyecto, bloqueando a Composer.

Si lo que se quiere es que el directorio quede limpio y **no se cree** `~/.ash_history`, la opción más práctica en Alpine es definir `HISTFILE=/dev/null` desde el entorno del shell. En un `Dockerfile` puedes hacerlo así: `ENV HISTFILE=/dev/null`

```yaml
FROM php:8.3-fpm-alpine

ENV HISTFILE=/tmp/.ash_history
RUN echo 'export HISTFILE=/dev/null' >> /etc/profile
```

Si además quieres dejarlo explícito para shells interactivas, puedes añadirlo también a `/etc/profile`:

Eso hace que `ash` no escriba el historial en el home del usuario, así mantienes el directorio limpio.
## Crear la estructura Laravel para la aplicación (Composer)

Perfecto. Aquí tienes las dos alternativas para reinstalar Laravel desde cero, con la estructura correcta en `/var/www/html`.

## Alternativa 1: Desde adentro del contenedor (recomendada)

**Ventajas:**

- No requiere tener Composer instalado en el host
- Asegura compatibilidad de versiones de PHP/Composer
- Más portable y consistente
    
#### 1. Limpiar el directorio app desde el host

- **Estar seguro de entrar al directorio del proyecto**
```bash
cd ~/dk-notes 
```

- **Dejar limpio el directorio**
```bash
rm -rf /var/home/datenmaniak/dk-notes/app/*
```


> [!NOTE]
>  Si quiero una visual de lo que ocurre al limpiar el directorio, sugiero usar cualquiera de las instrucciones mencionada para **Eliminar absolutamente todo**

#### 2. Recrear los contenedores para asegurar volúmenes limpios
```bash
podman-compose down && \
podman-compose up -d --build
```

##### 3. Instalar Laravel dentro del contenedor (en el directorio correcto)

```bash
podman exec -it dk-app composer create-project laravel/laravel . --no-scripts --no-progress --prefer-dist
```

Si vemos algo semejante, entonces todo va bien hasta ahora:

```plaintext
Creating a "laravel/laravel" project at "./"
Installing laravel/laravel (v13.6.0)
  - Downloading laravel/laravel (v13.6.0)
  - Installing laravel/laravel (v13.6.0): Extracting archive
Created project in /var/www/html/.
Loading composer repositories with package information
Updating dependencies
Lock file operations: 110 installs, 0 updates, 0 removals
  - Locking brick/math (0.14.8)
  - Locking carbonphp/carbon-doctrine-types (3.2.0)
  - Locking dflydev/dot-access-data (v3.0.3)
  - Locking doctrine/inflector (2.1.0)
```

Así termina la creación del esqueleto de `Laravel`:

```plaintext
  - Installing phar-io/version (3.2.1): Extracting archive
  - Installing phar-io/manifest (2.0.4): Extracting archive
  - Installing myclabs/deep-copy (1.13.4): Extracting archive
  - Installing phpunit/phpunit (12.5.25): Extracting archive
70 package suggestions were added by new dependencies, use `composer suggest` to see details.
Generating optimized autoload files
79 packages you are using are looking for funding.
Use the `composer fund` command to find out more!
No security vulnerability advisories found.
```

#### 4. Verificar estructura
```bash
podman exec dk-app ls -la /var/www/html/
```

```
podman exec dk-app ls -la /var/www/html/public/
```

#### 5.  Configurar el Entorno de Laravel (`.env`)

Como usamos `--no-scripts`, el framework está "en coma". Necesitamos despertarlo:

1. Clonar el archivo de configuración: Crear el `.env` a partir del `.env.example`.
2. Generar la clave de seguridad de la app: `php artisan key:generate`.

##### Dos alternativas:

**1. Desde el contenedor:**

```bash
podman exec -it dk-app sh
```

```bash
cp .env.example .env
```

**2. Fuera del contenedor / en la estación de desarrollo:**

```bash
 ~/dk-notes 
❯ cd app 
```

```bash
cp .env.example .env
```

#### 6.  Generar la Identidad de la App

Sin esto, Laravel dará un error de seguridad (500 Internal Server Error) porque no puede cifrar las sesiones.


```bash
podman exec -it  dk-app php artisan key:generate 
```

```info
   INFO  Application key set successfully.  
```

#### 7.  Vincular con Postgres

Ahora edita el archivo `app/notes/.env` (puedes usar Neovim o VS Code en tu host) y busca la sección de base de datos. Configurar así para que hable con tu contenedor `dk-db`:

```code
DB_CONNECTION=pgsql
DB_HOST=postgres
DB_PORT=5432
DB_DATABASE=dknotes
DB_USERNAME=dkuser
DB_PASSWORD=dkpassword
```

### La "Prueba de Fuego": Migraciones

Este es el momento en que confirmamos que el contenedor de Laravel puede hablar con el de Postgres a través de la red interna de Podman:

```bash
podman exec -it  dk-app php artisan migrate
```

> [!ERROR]
> 
>    INFO  Preparing database.  
> 
>   Creating migration table ................................................................ 4.62ms FAIL
> 
>    Illuminate\Database\QueryException 
> 
>   SQLSTATE[42501]: Insufficient privilege: 7 ERROR:  permission denied for schema public at character 14 (Connection: pgsql, Host: postgres, Port: 5432, Database: dknotes, SQL: create table "migrations" ("id" serial not null primary key, "migration" varchar(255) not null, "batch" integer not null))

Este error es muy común al trabajar con **PostgreSQL**. La buena noticia es que la red de Podman está funcionando perfectamente: el contenedor `dk-app` (Laravel) logró encontrar a `dk-db` (Postgres), se autenticó correctamente con tus credenciales y el driver `pdo_pgsql` que compilamos en el Dockerfile está haciendo su trabajo.

El problema actual es estrictamente de **permisos dentro de la base de datos**.

### 🔍 ¿Por qué ocurrió este error?

En PostgreSQL 15 y versiones superiores (las que probablemente está usando tu contenedor), el comportamiento por defecto del motor cambió por seguridad: **el usuario administrador (`postgres`) es el único dueño del esquema `public`, y a los usuarios nuevos (`dkuser`) ya no se les otorgan permisos de escritura de forma automática en ese esquema.** Como tu script de migración intenta crear la tabla `migrations` dentro del esquema `public`, Postgres le deniega el acceso con el error `Insufficient privilege`.

---

### 🛠️ La Solución (Conceder privilegios al usuario)

Como SysAdmin, debes entrar un momento al contenedor de la base de datos y otorgarle explícitamente el control del esquema `public` a tu usuario de desarrollo.

Ejecuta estos comandos en tu terminal de la estación local:

#### 1. Entra a la consola de Postgres (`psql`) dentro de tu contenedor de base de datos:

```bash
podman exec -it dk-db psql -U postgres -d dknotes
```

_(Nota: Si el usuario administrador de tu compose tiene otro nombre, cambia `-U postgres` por tu usuario root de BD)._

#### 2. Una vez que veas el prompt de Postgres (`dknotes=#`), ejecuta esta consulta SQL:

```sql
ALTER SCHEMA public OWNER TO dkuser;
```

_(Asegúrate de cambiar `dkuser` por el nombre exacto del usuario que definiste en tu archivo `.env`)._

#### 3. Sal de la consola de Postgres:

```sql
\q
```

### 🚀 La Prueba de Fuego

Ahora que tu usuario es el dueño legítimo del esquema `public`, vuelve a lanzar las migraciones desde Laravel:

```bash
podman exec -it  dk-app php artisan migrate
```

Esta vez deberías ver la lista de tablas (`users`, `password_reset_tokens`, `failed_jobs`, etc.) creándose en verde con el estado `DONE`. 

```bash
   INFO  Preparing database.  

  Creating migration table ............................................................... 20.07ms DONE

   INFO  Running migrations.  

  0001_01_01_000000_create_users_table ................................................... 14.76ms DONE
  0001_01_01_000001_create_cache_table .................................................... 7.91ms DONE
  0001_01_01_000002_create_jobs_table .................................................... 15.22ms DONE
```

## Listo. Ya tenemos operativo el entorno Laravel...

¡Excelente hito! Haber logrado que el contenedor Alpine con PHP 8.3 nativo compile sus extensiones, se comunique a través de la red interna de Podman y tome el control del esquema en PostgreSQL es una victoria total de arquitectura e infraestructura. El **Punto 1** (Laravel hidratado) y el **Punto 2** (Persistencia y migración en Base de Datos) están oficialmente **conquistados**.

Ahora que la infraestructura base responde con éxito, este es el panorama de lo que tienes operativo en tu HP Victus y cómo se conecta el ecosistema:

### 📊 Estado de la Infraestructura "dk-notes"

- **`dk-db` (PostgreSQL):** Base de datos estructurada, con el esquema `public` correctamente delegado a tu usuario de la app y las tablas de sistema de Laravel listas para recibir datos.
    
- **`dk-app` (PHP 8.3 FPM / Alpine):** Entorno mínimo, optimizado y sin residuos de historial (`.ash_history`). El comando `php artisan` responde perfectamente en la ruta `/var/www/html`.



---

## Alternativa 2: Fuera del contenedor (usando Composer del host)

**Ventajas:**

- Velocidad (sin exec dentro del contenedor)
- Aprovecha caché local de Composer
    
**Desventajas:**

- Requiere Composer instalado en el host
- Puede haber diferencias de versión de PHP

#### 1. Limpiar y preparar directorio
```bash
cd /var/home/datenmaniak/dk-notes
rm -rf app/*
mkdir -p app
```

#### 2. Crear proyecto Laravel directamente en ./app
```
composer create-project laravel/laravel . --no-scripts --no-progress --prefer-dist
````

> [!NOTE]
> 
> ####  < notes >  nombre del proyecto


#### 4. Volver al directorio raíz y levantar contenedores
```bash
cd ..
podman-compose down
podman-compose up -d --build
```

##### 5. Verificar estructura dentro del contenedor
```
podman exec dk-app ls -la /var/www/html/
podman exec dk-app ls -la /var/www/html/public/
```

---

## Mi recomendación: **Alternativa 1 (dentro del contenedor)**

### ¿Por qué?

1. **Consistencia:** Usa el PHP/Composer que está dentro de tu contenedor Laravel
2. **Menos dependencias:** No necesitas instalar Composer en el host
3. **Idéntico entorno:** Lo que ves dentro del contenedor es exactamente lo que se ejecutará
4. **Evita problemas de permisos:** `userns_mode: "keep-id"` maneja los UIDs automáticamente
5. **Reproducible:** Cualquier persona con tu compose puede recrear el mismo entorno




--- 
# REVISIONES

## Movida aqui  durante la verificacion de secuencia de ejecucion de instrucciones.

No necesarias durante el procedimiento.

```bash
podman exec -it dk-app sh
```

##### 4. Ajustar permisos (opcional, dependiendo de userns_mode)
```bash
podman exec -it dk-app chown -R $(id -u):$(id -g) /var/www/html
```

##### 5. Ejecutar scripts de post-instalación
```bash
podman exec -it dk-app composer run-script post-autoload-dump -d /var/www/html
```