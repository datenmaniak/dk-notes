#!/bin/bash

# ============================================================
# sync-notes.sh - Sincroniza notas con el contenedor DKNotes
# ============================================================

# Valores por defecto
CONTAINER="dk-app"
DESTINO="/var/www/html/public/notes"
DRY_RUN=false
DELETE_ORPHANS=false

# Directorio base fijo (no se puede copiar directamente a esta ruta)
BASE_NOTES="/var/www/html/public/notes"

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
    echo "  -D, --delete-orphans   Elimina archivos en destino que no existen en origen"
    echo "  -c, --container NOMBRE Nombre del contenedor (default: dk-app)"
    echo "  -t, --destino RUTA     Ruta destino en contenedor (default: /var/www/html/public/notes)"
    echo "  -h, --help             Muestra esta ayuda"
    echo ""
    echo "Ejemplos:"
    echo "  $0 ~/notes"
    echo "  $0 ~/notes --dry-run"
    echo "  $0 ~/notes --delete-orphans"
    echo "  $0 ~/mis-notas -c otro-container -t /var/www/html/public/notes/mi-carpeta"
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
# Verificar si el directorio destino existe en el contenedor
# ============================================================
check_dest_dir() {
    echo "  Verificando directorio destino en el contenedor..."
    if ! podman exec "$CONTAINER" test -d "$DESTINO" 2>/dev/null; then
        echo ""
        echo "ERROR: El directorio destino no existe en el contenedor"
        echo ""
        echo "Ruta buscada: ${CONTAINER}:${DESTINO}"
        echo ""
        echo "Este directorio debe ser creado previamente desde la aplicacion web DKNotes."
        echo ""
        echo "Por favor:"
        echo "  1. Accede a la aplicacion web"
        echo "  2. Configura tu espacio de notas"
        echo "  3. Vuelve a ejecutar este script"
        echo ""
        echo "El script no creara el directorio automaticamente."
        exit 1
    fi
    echo "  Directorio destino encontrado: ${DESTINO}"
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
    
    # Mostrar archivos que serian eliminados (si la opcion esta activada)
    if [ "$DELETE_ORPHANS" = true ]; then
        echo ""
        echo "Archivos que seran eliminados (huérfanos):"
        podman exec "$CONTAINER" find "$DESTINO" -name "*.md" -type f 2>/dev/null | while read -r remote_file; do
            rel_path="${remote_file#$DESTINO/}"
            local_file="${LOCAL_DIR}/${rel_path}"
            if [ ! -f "$local_file" ]; then
                echo "  - ${rel_path}"
            fi
        done
        echo ""
        echo "Eliminacion de huerfanos: ACTIVADA"
    else
        echo ""
        echo "Eliminacion de huerfanos: DESACTIVADA (usa --delete-orphans para activar)"
    fi
    
    echo ""
    echo "Para ejecutar la sincronizacion real, omite --dry-run"
}

# ============================================================
# Eliminar archivos huérfanos en destino
# ============================================================
delete_orphan_files() {
    echo "  Eliminando archivos huerfanos en destino..."
    
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
    
    # Verificar que el directorio destino existe
    check_dest_dir

    # Copiar archivos nuevos/modificados
    copy_files
    
    # Eliminar archivos huérfanos solo si se solicita
    if [ "$DELETE_ORPHANS" = true ]; then
        delete_orphan_files
    else
        echo "  Eliminacion de huerfanos: DESACTIVADA (usa --delete-orphans para activar)"
    fi
    
    echo ""
    echo "Copia completada exitosamente"
    
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
    echo "Las notas han sido copiadas al contenedor."
    echo ""
    echo "Ahora ve a la aplicacion web DKNotes para:"
    echo "  - Importar las notas"
    echo "  - Revisar el contenido"
    echo "  - Gestionar tus notas"
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
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
        -D|--delete-orphans)
            DELETE_ORPHANS=true
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