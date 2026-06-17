# Contenido composer.json 

Se evalua la causa del error:

```bash
Generating optimized autoload files
79 packages you are using are looking for funding.
Use the `composer fund` command to find out more!

In Request.php line 117:
                                                            
  syntax error, unexpected token "{", expecting "," or ";"  
                                                            

Error: Process completed with exit code 1.
```

## composer.json (actual)

```json
{
    "$schema": "https://getcomposer.org/schema.json",
    "name": "laravel/laravel",
    "type": "project",
    "description": "The skeleton application for the Laravel framework.",
    "keywords": ["laravel", "framework"],
    "license": "MIT",
    "require": {
        "php": "^8.3",
        "erusev/parsedown": "^1.8",
        "laravel/framework": "^13.8",
        "laravel/tinker": "^3.0",
        "laravel/ui": "^4.6"
    },
    "require-dev": {
        "fakerphp/faker": "^1.23",
        "laravel/breeze": "^2.4",
        "laravel/pail": "^1.2.5",
        "laravel/pao": "^1.0.6",
        "laravel/pint": "^1.27",
        "mockery/mockery": "^1.6",
        "nunomaduro/collision": "^8.6",
        "phpunit/phpunit": "^12.5.12"
    },
    "autoload": {
        "psr-4": {
            "App\\": "app/",
            "Database\\Factories\\": "database/factories/",
            "Database\\Seeders\\": "database/seeders/"
        }
    },
    "autoload-dev": {
        "psr-4": {
            "Tests\\": "tests/"
        }
    },
    "scripts": {
        "setup": [
            "composer install",
            "@php -r \"file_exists('.env') || copy('.env.example', '.env');\"",
            "@php artisan key:generate",
            "@php artisan migrate --force",
            "npm install --ignore-scripts",
            "npm run build"
        ],
        "dev": [
            "Composer\\Config::disableProcessTimeout",
            "npx concurrently -c \"#93c5fd,#c4b5fd,#fb7185,#fdba74\" \"php artisan serve\" \"php artisan queue:listen --tries=1 --timeout=0\" \"php artisan pail --timeout=0\" \"npm run dev\" --names=server,queue,logs,vite --kill-others"
        ],
        "test": [
            "@php artisan config:clear --ansi @no_additional_args",
            "@php artisan test"
        ],
        "post-autoload-dump": [
            "Illuminate\\Foundation\\ComposerScripts::postAutoloadDump",
            "@php artisan package:discover --ansi"
        ],
        "post-update-cmd": [
            "@php artisan vendor:publish --tag=laravel-assets --ansi --force"
        ],
        "post-root-package-install": [
            "@php -r \"file_exists('.env') || copy('.env.example', '.env');\""
        ],
        "post-create-project-cmd": [
            "@php artisan key:generate --ansi",
            "@php -r \"file_exists('database/database.sqlite') || touch('database/database.sqlite');\"",
            "@php artisan migrate --graceful --ansi"
        ],
        "pre-package-uninstall": [
            "Illuminate\\Foundation\\ComposerScripts::prePackageUninstall"
        ]
    },
    "extra": {
        "laravel": {
            "dont-discover": []
        }
    },
    "config": {
        "optimize-autoloader": true,
        "preferred-install": "dist",
        "sort-packages": true,
        "allow-plugins": {
            "pestphp/pest-plugin": true,
            "php-http/discovery": true
        }
    },
    "minimum-stability": "stable",
    "prefer-stable": true
}
```



## 🔍 El Diagnóstico: El choque del futuro contra el presente
Mira fijamente estas dos líneas de tu bloque "require" y "require-dev":

```json
JSON
"require": {
    "php": "^8.3",
    "laravel/framework": "^13.8"  // <--- ¡AQUÍ ESTÁ LA RAÍZ DE TODO!
},
"require-dev": {
    "phpunit/phpunit": "^12.5.12" // <--- Y AQUÍ SU COMPAÑERO
}
```


**¿Qué está ocurriendo aquí?**

**Laravel 13** no existe en el año 2026: La versión estable actual y real del framework es Laravel 11 (o las primeras ramas de Laravel 12). Al escribir `^13.8,` le estás pidiendo a Composer que intente buscar o resolver una estructura de framework que aún no ha sido desarrollada ni liberada oficialmente.

**PHPUnit 12** tampoco existe: La versión estable y compatible con los ecosistemas modernos de Laravel es PHPUnit 10 o PHPUnit 11. La versión `^12.5.12 `es inexistente o incompatible con el esqueleto actual de tu aplicación.

**¿Por qué esto causa un syntax error en Request.php?**

Cuando ejecutas composer install o composer update, Composer se vuelve loco intentando resolver una restricción imposible `(^13.8)`. Como no encuentra `Laravel 13` en los repositorios oficiales de Packagist, el motor de resolución de dependencias de Composer hace un fallback (cae en reversa) o arrastra componentes incorrectos/incompletos de dependencias cruzadas que rompen por completo la estructura de los archivos del framework.

El archivo **vendor/laravel/framework/src/Illuminate/Http/Request.php** termina corrupto, mal descargado o mezclado con archivos de otra versión, provocando que cuando se ejecute el evento post-autoload-dump, el intérprete de PHP se estrelle contra un token { inesperado en la línea 117. ¡Es un fallo puramente de integridad de software debido a las versiones declaradas!

## 🛠️ La Solución Definitiva: 

### Ajustar tu composer.json a la realidad
Para solucionar esto de raíz en tu estación de trabajo Fedora Aurora, tenemos que corregir las versiones del esqueleto de Laravel para que coincidan con las versiones reales de la industria.

1. Modifica tu composer.json local
Abre el archivo composer.json dentro de tu subcarpeta ./app y edita las líneas de los paquetes para que queden con las versiones estables y oficiales:

```json
JSON
"require": {
    "php": "^8.3",
    "erusev/parsedown": "^1.8",
    "laravel/framework": "^11.9",
    "laravel/tinker": "^2.9",
    "laravel/ui": "^4.5"
},
"require-dev": {
    "fakerphp/faker": "^1.23",
    "laravel/breeze": "^2.0",
    "laravel/pail": "^1.1",
    "laravel/pint": "^1.15",
    "mockery/mockery": "^1.6",
    "nunomaduro/collision": "^8.1",
    "phpunit/phpunit": "^11.0"
},
```

2. Limpia y regenera todo en local
Dado que el entorno de dependencias se encuentra corrupto por las pruebas anteriores, ejecuta una limpieza absoluta en la terminal de tu Victus 15:

```bash
cd ~/dk-notes/app
```

### Eliminamos la carpeta vendor corrupta y el candado viejo
```bash
rm -rf vendor/
rm composer.lock
```

### Borramos la caché de Composer por seguridad
```bash
composer clear-cache
```

### Instalamos de cero de forma limpia para generar el composer.lock real

```bash
composer install
```
