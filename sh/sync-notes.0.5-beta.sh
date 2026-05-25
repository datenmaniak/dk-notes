#!/bin/bash

# ============================================================
# sync-notes.sh - Sincroniza notas con el contenedor DKNotes
# ============================================================

# Valores por defecto
CONTAINER="dk-app"
DESTINO="/var/www/html/public/notes"
DRY_RUN=false

# ============================================================
# Mostrar ayuda
# ============================================================
show_help() {
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
    echo "sync-notes.sh - Sincroniza notas con el contenedor DKNotes"
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
    echo ""
    echo "Uso: $0 <DIRECTORIO_LOCAL> [OPCIONES]"
    echo ""
    echo "Argumentos:"
    echo "  DIRECTORIO_LOCAL       Ruta local de las notas (requerido)"
    echo ""
    echo "Opciones:"
    echo "  -d, --dry-run          Simula la sincronizacion (no copia ni importa)"
    echo "  -c, --container NOMBRE Nombre del contenedor (default: dk-app)"
    echo "  -t, --destino RUTA     Ruta destino en contenedor (default: /var/www/html/public/notes)"
    echo "  -h, --help             Muestra esta ayuda"
    echo ""
    echo "Ejemplos:"
    echo "  $0 ~/notes"
    echo "  $0 ~/notes --dry-run"
    echo "  $0 ~/mis-notas -c otro-container -t /var/www/html/public/otros"
    echo ""
}

# ============================================================
# Verificar si el contenedor esta corriendo
# ============================================================
check_container() {
    if ! podman ps --format "{{.Names}}" | grep -q "^${CONTAINER}$"; then
        echo "ERROR: El contenedor '${CONTAINER}' no esta corriendo"
        echo "Contenedores disponibles:"
        podman ps --format "  - {{.Names}}"
        exit 1
    fi
    echo "OK: Contenedor '${CONTAINER}' encontrado"
}

# ============================================================
# Verificar si el directorio local existe
# ============================================================
check_local_dir() {
    if [ ! -d "$LOCAL_DIR" ]; then
        echo "ERROR: El directorio local no existe: ${LOCAL_DIR}"
        exit 1
    fi
    echo "OK: Directorio local encontrado: ${LOCAL_DIR}"
}

# ============================================================
# Mostrar resumen de archivos (modo dry-run)
# ============================================================
show_dry_run_summary() {
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
    echo "Modo simulacion (dry-run)"
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
    
    echo "Origen: ${LOCAL_DIR}"
    echo "Destino: ${CONTAINER}:${DESTINO}"
    echo ""
    
    echo "Archivos .md que seran copiados:"
    
    # Listar archivos .md en origen
    find "$LOCAL_DIR" -name "*.md" -type f | while read -r file; do
        rel_path="${file#$LOCAL_DIR/}"
        echo "  + ${rel_path}"
    done
    
    # Listar archivos .md en destino que seran eliminados (si existen)
    echo ""
    echo "Archivos que seran eliminados en destino (si no existen en origen):"
    
    podman exec "$CONTAINER" find "$DESTINO" -name "*.md" -type f 2>/dev/null | while read -r remote_file; do
        rel_path="${remote_file#$DESTINO/}"
        local_file="${LOCAL_DIR}/${rel_path}"
        if [ ! -f "$local_file" ]; then
            echo "  - ${rel_path}"
        fi
    done
    
    echo ""
    echo "Para ejecutar la sincronizacion real, omite --dry-run"
}

# ============================================================
# Crear directorio destino si no existe
# ============================================================
create_dest_dir() {
    echo "  Verificando directorio destino en el contenedor..."
    podman exec "$CONTAINER" mkdir -p "$DESTINO" 2>/dev/null
    if [ $? -eq 0 ]; then
        echo "  Directorio destino listo: ${DESTINO}"
    else
        echo "  ERROR: No se pudo crear el directorio ${DESTINO}"
        exit 1
    fi
}

# ============================================================
# Eliminar archivos huérfanos en destino
# ============================================================
delete_orphan_files() {
    echo "  Eliminando archivos huérfanos en destino..."
    
    # Verificar que el directorio existe antes de buscar
    podman exec "$CONTAINER" test -d "$DESTINO" || return 0
    
    deleted=0
    podman exec "$CONTAINER" find "$DESTINO" -name "*.md" -type f 2>/dev/null | while read -r remote_file; do
        rel_path="${remote_file#$DESTINO/}"
        local_file="${LOCAL_DIR}/${rel_path}"
        if [ ! -f "$local_file" ]; then
            podman exec "$CONTAINER" rm "$remote_file"
            echo "    Eliminado: ${rel_path}"
            deleted=$((deleted + 1))
        fi
    done
    
    echo "  Archivos eliminados: ${deleted}"
}

# ============================================================
# Copiar archivos nuevos/modificados
# ============================================================
copy_files() {
    echo "  Copiando archivos nuevos/modificados..."
    
    # Contar archivos .md
    total=$(find "$LOCAL_DIR" -name "*.md" -type f | wc -l)
    
    if [ $total -eq 0 ]; then
        echo "  No se encontraron archivos .md para copiar"
        return 0
    fi
    
    # Crear array de archivos para evitar subshell
    files=()
    while IFS= read -r -d '' file; do
        files+=("$file")
    done < <(find "$LOCAL_DIR" -name "*.md" -type f -print0)
    
    copied=0
    for file in "${files[@]}"; do
        # Obtener ruta relativa
        rel_path="${file#$LOCAL_DIR/}"
        rel_path="${rel_path#/}"
        
        # Obtener directorio destino
        dest_dir="${DESTINO}/$(dirname "$rel_path")"
        
        # Crear directorio destino si no existe
        podman exec "$CONTAINER" mkdir -p "$dest_dir" 2>/dev/null
        
        # Copiar archivo
        copied=$((copied + 1))
        echo "  [$copied/$total] Copiando: ${rel_path}"
        podman cp "$file" "${CONTAINER}:${dest_dir}/"
    done
    
    echo "  Archivos copiados: ${copied}"
}

# ============================================================
# Ejecutar sincronizacion real
# ============================================================
run_sync() {
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
    echo "Copiando notas al contenedor..."
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
    
    # Crear directorio destino si no existe
    create_dest_dir

    # Copiar archivos nuevos/modificados
    copy_files
    
    # Eliminar archivos huérfanos
    delete_orphan_files
    
    echo ""
    echo "Copia completada exitosamente"
    
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
    echo "Importando notas en la aplicacion..."
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
    
    # Ejecutar importacion en el contenedor
    podman exec "$CONTAINER" php artisan notes:import
    
    if [ $? -eq 0 ]; then
        echo ""
        echo "Sincronizacion completada exitosamente"
    else
        echo "ERROR: Durante la importacion de notas"
        exit 1
    fi
}

# ============================================================
# Procesar argumentos
# ============================================================
while [[ $# -gt 0 ]]; do
    case $1 in
        -h|--help)
            show_help
            exit 0
            ;;
        -d|--dry-run)
            DRY_RUN=true
            shift
            ;;
        -c|--container)
            CONTAINER="$2"
            shift 2
            ;;
        -t|--destino)
            DESTINO="$2"
            shift 2
            ;;
        -*)
            echo "ERROR: Opcion desconocida: $1"
            echo "Usa -h o --help para ver las opciones disponibles"
            exit 1
            ;;
        *)
            LOCAL_DIR="$1"
            shift
            ;;
    esac
done

# ============================================================
# Validar que se proporciono el directorio local
# ============================================================
if [ -z "$LOCAL_DIR" ]; then
    echo "ERROR: Debes especificar el directorio local de notas"
    echo ""
    show_help
    exit 1
fi

# ============================================================
# Ejecutar script
# ============================================================
echo ""
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "DKNotes - Sincronizacion de notas"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo ""

# Validaciones
check_container
check_local_dir

# Ejecutar segun modo
if [ "$DRY_RUN" = true ]; then
    show_dry_run_summary
else
    run_sync
fi

echo ""