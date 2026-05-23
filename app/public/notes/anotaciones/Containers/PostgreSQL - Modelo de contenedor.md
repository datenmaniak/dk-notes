
## Estructura de directorios recomendada

```plaintext
proyecto/
├── docker-compose.yml
├── postgres/
│   ├── Dockerfile
│   └── init-scripts/
│       ├── 01-create-dkuser.sql
│       ├── 02-check-and-create.sql
│       └── 03-grant-permissions.sql
├── laravel/
│   └── Dockerfile
├── nginx/plr
│   └── Dockerfile
└── app/
    └── (código Laravel)
```

## Dockerfile sugerido para este caso

El `Dockerfile` para PostgreSQL en este caso es bastante simple, ya que la imagen oficial ya hace casi todo el trabajo. Aquí te muestro el sugerido:

### Dockerfile para PostgreSQL

**Ubicación:** `./postgres/Dockerfile`

```Dockerfile
FROM postgres:17-alpine
# Establecer variables de entorno por defecto (pueden sobrescribirse en docker-compose)
ENV POSTGRES_USER=postgres \
    POSTGRES_DB=dknotes \
    POSTGRES_PASSWORD=postgres_clave_segura
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
```

## ¿Por qué este Dockerfile?

### Versión Alpine

- Ligera (tamaño reducido)
- Más segura (menos superficie de ataque)
- Ya estás usando Alpine según conversación anterior
    
### Herramientas adicionales (opcional)

- `postgresql-contrib`: Extensiones extras (pg_stat_statements, uuid-ossp, etc.)
- `vim` y `bash`: Para debuggear si entras al contenedor
- `curl`: Para health checks
    

### Por qué es minimalista

La imagen oficial de PostgreSQL ya:

- Configura el entrypoint automático
- Ejecuta scripts en `/docker-entrypoint-initdb.d/`
- Maneja permisos de archivos
- Configura autenticación básica

## Dockerfile simplificado (mínimo recomendado)

Si se quiere lo más simple posible:

```Dockerfile
FROM postgres:17-alpine

# Solo añadir scripts de inicialización si los tienes
# (Asumiendo que los scripts están en ./init-scripts localmente)
COPY ./init-scripts/ /docker-entrypoint-initdb.d/

# El resto lo maneja la imagen oficial
```

> [!NOTE]
> Véase los `init-scripts` al final del documento.

## Docker compose sugerido (aplicar con Dockerfile sugerido)


```yaml
# docker-compose.yml 
services:
  # El Motor de Base de Datos
  postgres:
    build:
      context: ./postgres
    container_name: dk-db
    restart: always
    environment:
      POSTGRES_USER: postgres
      POSTGRES_PASSWORD: shogun7  # ← Cambia por tu clave real
      POSTGRES_DB: dknotes
    security_opt:
      - label:disable
    userns_mode: "keep-id"
    volumes:
      - /var/home/datenmaniak/pg-data:/var/lib/postgresql/data
	    # Scripts de inicialización
      - ./postgres/init-scripts:/docker-entrypoint-initdb.d:Z  
    networks:
      - dk-network
    ports:
      - "5432:5432"

networks:
  dk-network:
    external: true
    name: dk-network

```

## Alternativa: No usar Dockerfile personalizado

**También podrías evitar el Dockerfile por completo** usando directamente la imagen oficial en tu `docker-compose.yml`:

```yaml
services:
  postgres:
    image: postgres:17-alpine  # ← sin build
    container_name: dk-db
    restart: always
    environment:
      POSTGRES_USER: postgres
      POSTGRES_PASSWORD: postgres_clave_segura
      POSTGRES_DB: dknotes
    volumes:
      - /var/home/datenmaniak/pg-data:/var/lib/postgresql/data
      - ./postgres/init-scripts:/docker-entrypoint-initdb.d:Z  # ← scripts montados
    # ... resto igual
```

Esta es **la opción más simple y recomendada** a menos que necesites:

- Instalar extensiones extra
- Modificar configura
- Agregar herramientas de debugging

## Recomendación final

Para tu caso específico, **no necesitas Dockerfile**. Usa directamente `image: postgres:17-alpine` en el compose y monta tus scripts de inicialización como volumen.

Solo crea el Dockerfile si:

1. Necesitas instalar extensiones como `pg_cron`, `postgis`, etc.
2. Quieres modificar configuraciones por defecto (ej: `max_connections=200`)
3. Prefieres tener todo empaquetado en la imagen en lugar de montar scripts


## Archivos adicionales necesarios

### 1. Script de inicialización para crear `dkuser`

Crea esta carpeta y archivo:

```bash
mkdir -p ./postgres/init-scripts
```

Crea el archivo `./postgres/init-scripts/02-create-dkuser.sql`:

```sql
-- Crear el usuario dkuser con su propia contraseña
CREATE USER dkuser WITH PASSWORD 'dkuser_clave_segura';  -- ← Cambia por tu clave real

-- Otorgar permisos de conexión a la base de datos dknotes
GRANT CONNECT ON DATABASE dknotes TO dkuser;

-- Conectar a la base de datos dknotes
\c dknotes

-- Otorgar permisos en el esquema public
GRANT USAGE ON SCHEMA public TO dkuser;

-- Otorgar permisos de manipulación de datos (SELECT, INSERT, UPDATE, DELETE)
GRANT SELECT, INSERT, UPDATE, DELETE ON ALL TABLES IN SCHEMA public TO dkuser;

-- Otorgar permisos para secuencias (autoincrementales)
GRANT USAGE, SELECT ON ALL SEQUENCES IN SCHEMA public TO dkuser;

-- Opcional: Permitir a dkuser crear tablas (descomenta si lo necesitas)
-- GRANT CREATE ON SCHEMA public TO dkuser;

-- Configurar permisos para tablas futuras
ALTER DEFAULT PRIVILEGES IN SCHEMA public 
GRANT SELECT, INSERT, UPDATE, DELETE ON TABLES TO dkuser;

ALTER DEFAULT PRIVILEGES IN SCHEMA public 
GRANT USAGE, SELECT ON SEQUENCES TO dkuser;

-- Verificar que se creó correctamente
\du
```

### 2. Opcional: Script para evitar conflictos (si ya existe dkuser)

Crea `./postgres/init-scripts/01-check-and-create.sql` (se ejecuta antes por el nombre):

```sql
-- Eliminar usuario si ya existe (para recrearlo limpio)
-- DROP USER IF EXISTS dkuser;

-- O mejor: mantenerlo y solo actualizar contraseña/pemisos
DO $$
BEGIN
   IF NOT EXISTS (SELECT FROM pg_user WHERE usename = 'dkuser') THEN
      CREATE USER dkuser WITH PASSWORD 'dkuser_clave_segura';
   ELSE
      ALTER USER dkuser WITH PASSWORD 'dkuser_clave_segura';
   END IF;
END
$$;
```

## Instrucciones para aplicar los cambios

### Opción 1: Desde cero (recomendado si puedes perder los datos actuales)

```bash
# Detener servicios
podman-compose down
```

```
# Eliminar los datos antiguos (bind mount)
sudo rm -rf /var/home/datenmaniak/pg-data/*
```

```
# Crear los scripts de inicialización
mkdir -p ./postgres/init-scripts
```

####  Copia los scripts SQL de arriba en esa carpeta

```
# Levantar de nuevo
podman-compose up -d
```
### Verificar que ambos usuarios existen

```bash
podman exec -it dk-db psql -U postgres -c "\du"
```

### Opción 2: Conservar datos actuales y solo agregar dkuser

```bash
# Crear los scripts (pero PostgreSQL no los ejecutará porque la DB ya existe)
mkdir -p ./postgres/init-scripts
```

```
# Ejecutar manualmente el script SQL
docker exec -it dk-db psql -U postgres -f - << 'EOF'
CREATE USER dkuser WITH PASSWORD 'dkuser_clave_segura';
GRANT CONNECT ON DATABASE dknotes TO dkuser;
\c dknotes
GRANT USAGE ON SCHEMA public TO dkuser;
GRANT SELECT, INSERT, UPDATE, DELETE ON ALL TABLES IN SCHEMA public TO dkuser;
GRANT USAGE, SELECT ON ALL SEQUENCES IN SCHEMA public TO dkuser;
ALTER DEFAULT PRIVILEGES IN SCHEMA public GRANT SELECT, INSERT, UPDATE, DELETE ON TABLES TO dkuser;
ALTER DEFAULT PRIVILEGES IN SCHEMA public GRANT USAGE, SELECT ON SEQUENCES TO dkuser;
EOF
```

```
# Luego hacer el docker-compose up -d para aplicar el resto de cambios
docker-compose up -d
```


## Verificación de que todo funciona

#### Probar conexión como postgres (superusuario)

```bash
podman exec -it dk-db psql -U postgres -d dknotes -c "SELECT current_user;"
```

#### Probar conexión como dkuser (usuario limitado)
```bash
podman exec -it dk-db psql -U dkuser -d dknotes -c "SELECT current_user;"
```

#### Verificar que dkuser NO puede conectarse a otra base (ej: 
postgres)

```bash
podman exec -it dk-db psql -U dkuser -d postgres -c "SELECT current_user;"
```
**Esto debería fallar por permiso**


