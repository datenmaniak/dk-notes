# Limpia y regenera todo en local
#Dado que el entorno de dependencias se encuentra corrupto por las pruebas anteriores, ejecuta una limpieza absoluta en la terminal de tu Victus 15:

#cd ~/dk-notes/app

# Eliminamos la carpeta vendor corrupta y el candado viejo
rm -rf vendor
rm composer.lock

# Borramos la caché de Composer por seguridad
composer clear-cache

# Instalamos de cero de forma limpia para generar el composer.lock real
composer install
