
```yaml
# docker-compose.yml
.
.
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
      - ./app:/var/www:Z
    networks:
      - dk-network
    depends_on:
      - postgres

.
.
```

#### Primer intento de crear el esqueleto de Laravel
```bash
❯ podman exec -it dk-app composer create-project \
laravel/laravel . --no-scripts --no-progress --prefer-dist 
```

```error
Creating a "laravel/laravel" project at "./"

In CreateProjectCommand.php line 368:
                                                
  Project directory "/var/www/." is not empty.  
                                                
create-project [-s|--stability STABILITY] [--prefer-source] [--prefer-dist] [--prefer-install PREFER-INSTALL] [--repository REPOSITORY] [--repository-url REPOSITORY-URL] [--add-repository] [--dev] [--no-dev] [--no-custom-installers] [--no-scripts] [--no-progress] [--no-secure-http] [--keep-vcs] [--remove-vcs] [--no-install] [--no-audit] [--audit-format AUDIT-FORMAT] [--no-security-blocking] [--ignore-platform-req IGNORE-PLATFORM-REQ] [--ignore-platform-reqs] [--ask] [--] [<package> [<directory> [<version>]]]
```


## Solucionado así:

```bash
~/dk-notes 
❯ podman exec -it dk-app composer create-project laravel/laravel ./notes --no-scripts --no-progress --prefer-dist
```

> [!Success]
> Se indica un **`nombre de proyecto`**, en lugar de utilizar un punto (.) 

He optado por instalarlo en una subcarpeta llamada `notes`. Como administrador, esta decisión cambia un poco el tablero de juego, pero es totalmente válida.

Aquí tienes el análisis de lo que acaba de suceder y lo que implica para tu configuración en la estacion de desarrollo:

### 1. El estado actual de tu `/var/www`

Al ejecutar el comando con `./notes`, ahora tu estructura dentro del contenedor (y en tu carpeta `app/` local) se ve así:

- `/var/www/.composer`
    
- `/var/www/notes/` <-- **Aquí está Laravel** (con su carpeta `public`, `artisan`, etc.)
    

---

### 2. El ajuste necesario en Nginx (Crítico)

Como mencionaste antes que el volumen apunta a `./app:/var/www`, Nginx ahora mismo está buscando el punto de entrada en `/var/www/public`, pero ese directorio **no existe**. Ahora la ruta real es: `/var/www/notes/public`

**Tienes dos opciones para corregir esto:**

#### Opción A: Ajustar Nginx (Recomendado si quieres mantener la carpeta `notes`)

Modifica tu archivo de configuración de Nginx (`default.conf`) para que el `root` apunte a la nueva ubicación:


```nginx
root /var/www/notes/public;
```

#### Opción B: Mover los archivos a la raíz (Si quieres que el proyecto sea la raíz)

Si prefieres que Laravel viva directamente en `app/` sin la carpeta `notes`, ejecuta esto desde dentro del contenedor:


```bash
mv /var/www/notes/* /var/www/notes/.* /var/www/ 2>/dev/null
rmdir /var/www/notes
```

---

### 3. Siguientes pasos para el Punto 2 (Base de Datos)

Sea cual sea la opción que elijas, para que Laravel empiece a funcionar, debes entrar a la carpeta del proyecto y ejecutar los scripts que saltamos:

1. **Entrar a la carpeta (si elegiste la Opción A):** 

```bash
cd /var/www/notes
```
    
3. **Crear el archivo de entorno:** `cp .env.example .env`
    
4. **Generar la llave:** 

```bash
php artisan key:generate
```

> [!success]
> 
> ~/notes $ php artisan key:generate
> 
>    INFO  Application key set successfully.  

## Cual fue el propósito del punto 3.
### 1. Preparar el entorno (`.env`)

Laravel usa el archivo `.env` para saber dónde está la base de datos. Como usamos `--no-scripts`, el archivo no se creó automáticamente.


```bash
# Copiamos el ejemplo para crear el archivo real
podman exec -it -w /var/www/notes dk-app cp .env.example .env
```

### 2. Generar la Identidad de la App

Sin esto, Laravel dará un error de seguridad (500 Internal Server Error) porque no puede cifrar las sesiones.


```bash
podman exec -it -w /var/www/notes dk-app php artisan key:generate
```

#### Proseguimos...

### 3. Vincular con Postgres (Punto 2)

Ahora edita el archivo `app/notes/.env` (puedes usar Neovim o VS Code en tu host) y busca la sección de base de datos. Configurar así para que hable con tu contenedor `dk-db`:

**Fragmento de código:**

```
DB_CONNECTION=pgsql
DB_HOST=postgres
DB_PORT=5432
DB_DATABASE=dk_notes
DB_USERNAME=dk_user
DB_PASSWORD=dk_password
```

### 4. La "Prueba de Fuego": Migraciones

Este es el momento en que confirmamos que el contenedor de Laravel puede hablar con el de Postgres a través de la red interna de Podman:


```bash
podman exec -it -w /var/www/notes dk-app php artisan migrate
```