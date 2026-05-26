#!/bin/bash

# ============================================================
# Inicializar Base de Datos - DKNotes
# ============================================================
#
#  Requisito: Copiar este script al pod de PostgreSQL
#  1. identifique el pod:  
#       ❯ kubectl get pods -n postgres   
#  2. copiar
# kubectl cp admin/init-database.sh postgres-5f994cd449-nwq25:/tmp/init-database.sh
#
# Ejecutar dentro del pod de PostgreSQL:
#   kubectl exec -it postgres-pod -- sh   <-- ajuste el shell segun el contenedor/imagen
#   ./init-database.sh
# ============================================================

# Configuración
DB_HOST="localhost"
DB_SUPERUSER="postgres"
DB_SUPERPASSWORD="admin123"
DB_NAME="dknotes"
DB_USER="dkuser"
DB_PASSWORD="7shogun"

# Función para ejecutar comandos SQL
exec_sql() {
    PGPASSWORD=$DB_SUPERPASSWORD psql -h $DB_HOST -U $DB_SUPERUSER -tAc "$1"
}

echo ""
echo "=========================================================================="
echo "               INICIALIZAR BASE DE DATOS - DKNotes"
echo "=========================================================================="
echo ""

echo "  Configuracion:"
echo "  ------------------------------------------------------------------------"
echo "  Host:       $DB_HOST"
echo "  Superuser:  $DB_SUPERUSER"
echo "  Database:   $DB_NAME"
echo "  Usuario:    $DB_USER"
echo "  ------------------------------------------------------------------------"
echo ""

read -p "  Continuar? (s/n): " confirm
if [[ $confirm != "s" && $confirm != "S" ]]; then
    echo ""
    echo "  Cancelado."
    echo ""
    exit 0
fi

echo ""
echo "  Conectando a PostgreSQL..."

if ! PGPASSWORD=$DB_SUPERPASSWORD psql -h $DB_HOST -U $DB_SUPERUSER -c "SELECT 1" > /dev/null 2>&1; then
    echo "  ERROR: No se pudo conectar a PostgreSQL"
    echo ""
    exit 1
fi

echo "  Conexion exitosa"
echo ""

echo "  Creando usuario $DB_USER..."
if exec_sql "SELECT 1 FROM pg_roles WHERE rolname='$DB_USER'" | grep -q 1; then
    echo "  El usuario $DB_USER ya existe"
else
    exec_sql "CREATE USER $DB_USER WITH PASSWORD '$DB_PASSWORD';"
    echo "  Usuario $DB_USER creado"
fi

echo "  Creando base de datos $DB_NAME..."
if exec_sql "SELECT 1 FROM pg_database WHERE datname='$DB_NAME'" | grep -q 1; then
    echo "  La base de datos $DB_NAME ya existe"
else
    exec_sql "CREATE DATABASE $DB_NAME OWNER $DB_USER;"
    echo "  Base de datos $DB_NAME creada"
fi

echo "  Asignando privilegios..."
exec_sql "GRANT ALL PRIVILEGES ON DATABASE $DB_NAME TO $DB_USER;"
echo "  Privilegios asignados"

echo ""
echo "  Verificando..."
DB_EXISTS=$(exec_sql "SELECT 1 FROM pg_database WHERE datname='$DB_NAME'")
USER_EXISTS=$(exec_sql "SELECT 1 FROM pg_roles WHERE rolname='$DB_USER'")

if [[ "$DB_EXISTS" == "1" ]] && [[ "$USER_EXISTS" == "1" ]]; then
    echo "  Base de datos lista para usar"
    echo ""
    echo "=========================================================================="
    echo "  Inicializacion completada"
    echo "=========================================================================="
else
    echo "  ERROR: Verificacion fallida"
    echo ""
    exit 1
fi

echo ""