
## Introducción

Dado mi interés de aprender sobre el tema de Desarrollo Web, comencé con un sencillo proyecto que a partir  de HTML5, CSS3, y  un poco de JavaScript.  La curiosidad de emplear tecnologías actuales y poco de ayuda de la IA,  introduje Sass al proyecto para automatizar un poco el asunto de los estilos.  

Este proyecto lo bautice con el nombre de `web-template-core`, con la idea de reutilizar en el arranque de nuevos proyecto, ya que con ayuda de Gulp, Sass, Node y npm he logrado crear un **template responsivo**.
  

**dkstore** es un derivado de `web-template-core`,  que sin dudas se convirtió en una gran motivación para investigar y aprender mucho mas sobre `Desarrollo Web` y el resultado ha sido un proyecto con diversas.  Ademas, de incorporar un poco mas de JavaScript, también se ha migrado a PHP. No esta basado en ningun Framework `Laravel` u otro.  Sin embargo, incorpora **Programacion Orientada a Objeto.**


Lo considero un Kit de herramientas para futuros proyectos de Desarrollo Web.

  Este ha sido la primera etapa de migración a PHP. Aunque, resulte código espaguetis, me ha servido para aprender y pasar a otro nivel de conocimiento a nivel de Frontend y Backend.

  

---

  

### 🚀 Características principales

1. CRUD de productos.
2. Catálogo de productos
3. Inicio de sesión: 
4. Manejo de Role: user/admin
5.  Diseño totalmente responsivo
6. Compilación y bundling con Gulp
7. Generación automática de un archivo principal de estilos `main.css` y (`main.min.css`).
8. Bundling de JavaScript en un único `bundle.js` y (`bundle.min.js`).
9. Optimización de imágenes en **tres tamaños** + conversión a **WebP**.
10. Mixins SCSS avanzados
11. Arquitectura modular y escalable
12. Separación clara de bloques: header, hero, cards, navegación.
13. Documentación de comportamiento en distintos tamaños de pantalla.
14. Pensado para servir como **template base** en proyectos futuros.
15. Subida de imagen con Intervention Image


## Requerimientos:

1. Node
2. PHP, Composer

### Actualizar antes de iniciar codificación/refactorización

```bash
npm i 
```

```bash
composer install
```



##  Como se realiza la compilación con Gulp ?

```bash
npm run dev
```

Con esta instrucción, se logra ademas; los puntos 8. 9 y 10 mencionados arriba en la características.


## Containerización 

#### **Dockerfile:**

```yml
# Dockerfile

FROM php:8.3-apache
# Instalar extensiones necesarias para MariaDB

RUN apt-get update && apt-get install -y \
libzip-dev unzip git && docker-php-ext-install pdo pdo_mysql mysqli \
&& rm -rf /var/lib/apt/lists/*

# Habilitar mod_rewrite de Apache
RUN a2enmod rewrite

# Configurar Apache para usar el directorio público
ENV APACHE_DOCUMENT_ROOT /var/www/html

RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf

RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf

# Configurar permisos correctamente
RUN chown -R www-data:www-data /var/www/html \
&& chmod -R 755 /var/www/html

WORKDIR /var/www/html

# Copiar código fuente AL FINAL (mejor para caché)
#COPY . /var/www/html/

COPY ./php/ /var/www/html/

# Asegurar permisos después de copiar
RUN chown -R www-data:www-data /var/www/html \
&& find /var/www/html -type d -exec chmod 755 {} \; \
&& find /var/www/html -type f -exec chmod 644 {} \;


# Crear directorios especiales con permisos de escritura
RUN mkdir -p /var/www/html/public/uploads /var/www/html/logs \
&& chown -R www-data:www-data /var/www/html/public/uploads \
&& chown -R www-data:www-data /var/www/html/logs \
&& chmod -R 775 /var/www/html/public/uploads \
&& chmod -R 775 /var/www/html/logs

# Exponer puerto 80
EXPOSE 80

# Comando por defecto
CMD ["apache2-foreground"]
```


#### Docker Compose

```yaml
# docker-compose-2.yaml
services:

# Servidor Web y PHP (nuestro código)
web:
container_name: dkstore-web
build: . # Busca el Dockerfile en la carpeta actual
ports:
- "8080:80" # Mapea el puerto 8080 de tu máquina física al puerto 80 del contenedor

volumes:
- ./php+oop:/var/www/html:Z # Sincroniza tu código en tiempo real (si editas, se actualiza)

depends_on:
- db # No arranques la web hasta que la base de datos esté lista

# Base de Datos
db:
image: mariadb:11
container_name: dkstore-db
# mysql:8.0 # Descarga directamente la imagen oficial de MySQL 8.0
restart: always
environment:
MYSQL_DATABASE: dkstore
MYSQL_ROOT_PASSWORD: admin123
MARIADB_USER: dk
MARIADB_PASSWORD: chachita
ports:
- "3306:3306"
volumes:
- ${HOME}/wjpp/20-29_containers/28_mariadb/mariadb-data:/var/lib/mysql:Z```
```



---

  
## Nota:

  

Estoy consciente que requiere refactorizar para eliminar algunos trozos de  `"probable"` códigos redundantes y organizar la estructura Sass.

  

#### Autor

Creado por datenmaniak — IT Freelancer y Web Developer autodidacta. Enfocado en proyectos modulares y optimización de flujos de trabajo.