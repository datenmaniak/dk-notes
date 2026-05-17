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
