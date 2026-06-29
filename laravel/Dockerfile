# =========================================================================
# ETAPA 1: COMPILACIÓN DE ASSETS (Frontend)
# =========================================================================
FROM node:20-alpine AS frontend-builder

# Definimos el directorio de trabajo
WORKDIR /var/www/html

# Copiamos solo los archivos necesarios para instalar dependencias de Node
# Como ejecutas Podman desde la raíz, buscamos en la carpeta 'app/'
COPY app/package*.json ./

# RUN npm ci
#  ✅ FORZAMOS las versiones correctas
RUN npm install --save-dev \
    laravel-vite-plugin@1.2.0 \
    vite@5.4.11 \
    tailwindcss@3.4.17


#   no existe en V3 de tailwindcss,    @tailwindcss/vite@4.0.0 \
# Copiamos el resto del código del frontend para la compilación de Vite
COPY app/ ./

# Compilamos los assets para producción (Genera la carpeta public/build)
RUN npm run build


# =========================================================================
# ETAPA 2: IMAGEN FINAL DE PRODUCCIÓN (PHP-FPM)
# =========================================================================
FROM php:8.4.22-fpm-alpine

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


# 1. Copiar ambis archivos para heredar las versiones seguras
# COPY app/composer.json app/composer.lock ./

# 🌟 2. INSTALAR DEPENDENCIAS DE COMPOSER EN PRODUCCIÓN
# Esto generará la carpeta /var/www/html/vendor que el contenedor tanto necesita
ENV COMPOSER_ALLOW_SUPERUSER=1
RUN composer install --no-dev --optimize-autoloader --no-interaction --no-scripts


# RUN composer update guzzlehttp/guzzle --with-dependencies 
# RUN composer update  --with-dependencies 
# RUN composer update

# 🌟 3. EL FIX DE CACHÉ: Borramos físicamente los archivos fantasmas locales
# Esto obliga a Laravel a arrancar en un estado 100% puro y virgen
RUN rm -f /var/www/html/bootstrap/cache/packages.php /var/www/html/bootstrap/cache/services.php

# 🌟 3. EL FIX DE PRODUCCIÓN: Limpiar y regenerar el manifiesto de paquetes
# Esto sobreescribirá la caché local vieja y eliminará a Pail del mapa en producción
RUN php artisan package:discover --ansi


# 2. TRAEMOS LOS ASSETS COMPILADOS DESDE LA ETAPA 1
# Corregida la ruta origen: En la Etapa 1 el WORKDIR era /var/www/html
COPY --from=frontend-builder /var/www/html/public/build /var/www/html/public/build

# Configuramos permisos estrictos para el usuario de PHP-FPM (www-data)
# 🌟 Añadimos también /var/www/html/vendor para asegurar que PHP pueda leerlo sin problemas
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache /var/www/html/vendor

EXPOSE 9000
CMD ["php-fpm"]
