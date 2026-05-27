# Crear un contenedor temporal desde la nueva imagen
podman run --rm -it dknotes-laravel:1.41 ls -la /var/www/html/.env

echo "# Si muestra "No such file or directory", está bien "
