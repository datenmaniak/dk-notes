# Usamos PHP 8.3 FPM sobre Alpine para mínima huella de seguridad y tamaño
FROM php:8.3-fpm-alpine

# Evitar que genere el archivo '.ash_history', en el 
#  WORKDIR, a fin de evitar el Conflicto del directorio vacio 
#
ENV HISTFILE=/tmp/
RUN echo 'export HISTFILE=/dev/null' >> /etc/profile

# Instalamos dependencias de sistema necesarias para PostgreSQL y utilidades
RUN apk add --no-cache \
    postgresql-dev \
    libpng-dev \
    libzip-dev \
    zip \
    unzip \
    git \
    curl

# Instalamos extensiones de PHP (pdo_pgsql es vital para tu base de datos)
RUN docker-php-ext-install pdo pdo_pgsql pgsql gd zip intl

# Instalamos Composer (Cerebro de dependencias de Laravel)
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Definir el HOME del usuario al crearlo
RUN adduser -D -h /home/datenmaniak datenmaniak


# Configuramos el directorio de trabajo dentro del contenedor
WORKDIR /var/www/html

RUN chown -R www-data:www-data /var/www/
# Con userns_mode: "keep-id", en el docker-compose.yml,
# el usuario www-data (UID 82) se mapea de manera diferente. 
# Esto puede causar permisos incorrectos en los archivos montados. 
# De manera, que no se aplica.

# Exponemos el puerto 9000 para que Nginx pueda conectarse vía FastCGI
EXPOSE 9000

CMD ["php-fpm"]
