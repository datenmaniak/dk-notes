FROM postgres:17-alpine
# Establecer variables de entorno por defecto (pueden sobrescribirse en docker-compose)
ENV POSTGRES_USER=postgres \
    POSTGRES_DB=dknotes \
    POSTGRES_PASSWORD=shogun7
# Instalar herramientas adicionales útiles (opcional)
RUN apk add --no-cache \
    postgresql-contrib \
    vim \
    curl \
    bash
# Crear directorio para scripts de inicialización personalizados
# (La imagen oficial ya usa /docker-entrypoint-initdb.d/ por defecto)
# Este directorio se usa automáticamente para scripts .sh, .sql, .sql.gz
# Copiar scripts de inicialización personalizados (si quieres embeberlos en la imagen)
# COPY ./init-scripts/ /docker-entrypoint-initdb.d/
# Exponer el puerto por defecto de PostgreSQL
EXPOSE 5432
# El CMD ya está definido por la imagen base
# CMD ["postgres"]
# Nota: La imagen base ya tiene el entrypoint correcto