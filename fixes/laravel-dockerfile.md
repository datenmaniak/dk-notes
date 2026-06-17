# version actual Dockerfile (Laravel)

## Error
```plaintext

Fatal error: Uncaught RuntimeException: Composer detected issues in your platform: Your Composer dependencies require a PHP version ">= 8.4.1". You are running 8.3.31. in /var/www/html/vendor/composer/platform_check.php:22 Stack trace: #0 /var/www/html/vendor/composer/autoload_real.php(25): require() #1 /var/www/html/vendor/autoload.php(22): ComposerAutoloaderInit19ec9d4fbaa403b37d91c12abce4c745::getLoader() #2 /var/www/html/public/index.php(14): require('/var/www/html/v...') #3 {main} thrown in /var/www/html/vendor/composer/platform_check.php on line 22
```





```dockefile
# =========================================================================
# ETAPA 1: COMPILACIÓN DE ASSETS (Frontend)
# =========================================================================
FROM node:20-alpine AS frontend-builder

# Definimos el directorio de trabajo
WORKDIR /var/www/html

# Copiamos solo los archivos necesarios para instalar dependencias de Node
# Como ejecutas Podman desde la raíz, buscamos en la carpeta 'app/'
COPY app/package*.json ./

RUN npm ci

# Copiamos el resto del código del frontend para la compilación de Vite
COPY app/ ./

# Compilamos los assets para producción (Genera la carpeta public/build)
RUN npm run build


# =========================================================================
# ETAPA 2: IMAGEN FINAL DE PRODUCCIÓN (PHP-FPM)
# =========================================================================
FROM php:8.3.31-fpm-alpine

# Evitar que genere el archivo '.ash_history'
ENV HISTFILE=/tmp/
RUN echo 'export HISTFILE=/dev/null' >> /etc/profile

# Instalamos dependencias de sistema para PostgreSQL y utilidades PHP
RUN apk add --no-cache \
    postgresql-dev \
    libpng-dev \
    libzip-dev \
    zip \
    unzip \
    git \
    curl \
    icu-dev

# Instalamos extensiones de PHP vitales
RUN docker-php-ext-install pdo pdo_pgsql pgsql gd zip intl

# Instalamos Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Configuramos el directorio de trabajo coincidiendo con el root de Nginx
WORKDIR /var/www/html

# 1. Copiamos todo el código fuente de la aplicación PHP
# Gracias al .dockerignore, el archivo .env local NO se copiará aquí.
COPY --chown=www-data:www-data ./app /var/www/html

# 2. TRAEMOS LOS ASSETS COMPILADOS DESDE LA ETAPA 1
# Corregida la ruta origen: En la Etapa 1 el WORKDIR era /var/www/html
COPY --from=frontend-builder /var/www/html/public/build /var/www/html/public/build

# Configuramos permisos estrictos para el usuario de PHP-FPM (www-data)
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

EXPOSE 9000
CMD ["php-fpm"]
```