#!/bin/bash

# ============================================================
# sync-notes.sh - Sincroniza notas con el contenedor DKNotes
# ============================================================

# Valores por defecto
CONTAINER="dk-app"
DRY_RUN=false
DELETE_ORPHANS=false
RUTA_PERSONAL=""

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
    echo "Uso: $0 <DIRECTORIO_LOCAL> --personal <RUTA> [OPCIONES]"
    echo ""
    echo "Argumentos:"
    echo "  DIRECTORIO_LOCAL       Ruta local de las notas (requerido)"
    echo "  -p, --personal RUTA    Ruta personal (subcarpeta dentro de notes) (requerido)"
    echo ""
    echo "Opciones:"
    echo "  -d, --dry-run          Simula la sincronizacion (no copia ni importa)"
    echo "  -D, --delete-orphans   Elimina archivos en destino que no existen en origen"
    echo "  -c, --container NOMBRE Nombre del contenedor (default: dk-app)"
    echo "  -h, --help             Muestra esta ayuda"
    echo ""
    echo "Ejemplos:"
    echo "  $0 ~/notes --personal usuario@ejemplo.com"
    echo "  $0 ~/notes -p proyecto-x --delete-orphans"
    echo "  $0 ~/mis-notas -p usuario@empresa.com --dry-run"
    echo ""
    echo "Referencia para desarrolladores:"
    echo "  Ruta base fija: ${BASE_NOTES}"
    echo "  Destino final:  ${BASE_NOTES}/[RUTA_PERSONAL]"
    echo "  No se permite copiar directamente a la raiz de notes"
    echo ""
}

# ============================================================
# Normalizar ruta personal (solo a-z, sin acentos, sin numeros, sin caracteres especiales)
# ============================================================
normalize_personal_path() {
    local original="$RUTA_PERSONAL"
    local normalized
    
    # Convertir a minusculas
    normalized=$(echo "$original" | tr '[:upper:]' '[:lower:]')
    
    # Eliminar acentos (áéíóú → aeiou)
    normalized=$(echo "$normalized" | sed 's/[áäâà]/a/g; s/[éëêè]/e/g; s/[íïîì]/i/g; s/[óöôò]/o/g; s/[úüûù]/u/g')
    
    # Convertir ñ a n
    normalized=$(echo "$normalized" | sed 's/ñ/n/g')
    
    # Eliminar todo lo que no sea a-z
    normalized=$(echo "$normalized" | sed 's/[^a-z]//g')
    
    # Verificar que no quede vacio
    if [ -z "$normalized" ]; then
        echo ""
        echo "ERROR: La ruta personal no contiene caracteres validos"
        echo ""
        echo "Original: \"$original\""
        echo "Despues de normalizar: (vacio)"
        echo ""
        echo "La ruta personal debe contener al menos una letra (a-z)."
        echo "Ejemplo: --personal misnotas"
        echo ""
        exit 1
    fi
    
    # Limitar longitud a 100 caracteres
    if [ ${#normalized} -gt 100 ]; then
        normalized="${normalized:0:100}"
        echo "  (Ruta personal truncada a 100 caracteres)"
    fi
    
    # Mostrar normalizacion si hubo cambios
    if [ "$normalized" != "$original" ]; then
        echo "  Ruta personal normalizada: \"$original\" → \"$normalized\""
    fi
    
    RUTA_PERSONAL="$normalized"
    echo "  OK: Ruta personal: $RUTA_PERSONAL"
}

# ============================================================
# Validar formato de ruta personal
# ============================================================
# validate_personal_path() {
#     # Verificar que no este vacia
#     if [ -z "$RUTA_PERSONAL" ]; then
#         echo "ERROR: Debes especificar la ruta personal con --personal"
#         echo ""
#         echo "Ejemplo:"
#         echo "  $0 ~/notes --personal usuario@ejemplo.com"
#         echo ""
#         echo "La ruta personal es la subcarpeta dentro de notes donde se copiaran tus archivos."
#         echo "Consulta la ayuda con --help para mas informacion."
#         exit 1
#     fi
    
#     # Verificar que no contenga ".." (path traversal)
#     if [[ "$RUTA_PERSONAL" == *".."* ]]; then
#         echo "ERROR: Ruta personal no valida: \"${RUTA_PERSONAL}\""
#         echo ""
#         echo "La ruta personal no puede contener \"..\""
#         echo "Esto evitara salir del directorio de notas permitido."
#         echo ""
#         echo "Usa un valor como: usuario@ejemplo.com, proyecto-x, o notas-trabajo"
#         exit 1
#     fi
    
#     # Verificar que no sea "." o "/" o "./"
#     if [[ "$RUTA_PERSONAL" == "." || "$RUTA_PERSONAL" == "/" || "$RUTA_PERSONAL" == "./" ]]; then
#         echo "ERROR: Ruta personal no valida: \"${RUTA_PERSONAL}\""
#         echo ""
#         echo "No se permite copiar a la raiz de notas."
#         echo "Debes especificar una subcarpeta valida."
#         echo ""
#         echo "Usa un valor como: usuario@ejemplo.com, proyecto-x, o notas-trabajo"
#         exit 1
#     fi
    
#     # Eliminar slash al inicio si existe
#     if [[ "$RUTA_PERSONAL" == /* ]]; then
#         RUTA_PERSONAL="${RUTA_PERSONAL#/}"
#         echo "  (Normalizando ruta: eliminado slash inicial)"
#     fi
    
#     # Eliminar slash al final si existe
#     RUTA_PERSONAL="${RUTA_PERSONAL%/}"
    
#     echo " ✅ OK: Ruta personal valida: ${RUTA_PERSONAL}"
# }

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
    echo " ✅ OK: Contenedor '${CONTAINER}' encontrado"
}

# ============================================================
# Verificar si el directorio local existe
# ============================================================
check_local_dir() {
    if [ ! -d "$LOCAL_DIR" ]; then
        echo "ERROR: El directorio local no existe: ${LOCAL_DIR}"
        exit 1
    fi
    echo " ✅ OK: Directorio local encontrado: ${LOCAL_DIR}"
}

# ============================================================
# Construir destino final y verificar si existe
# ============================================================
check_dest_dir() {
    DESTINO_FINAL="${BASE_NOTES}/${RUTA_PERSONAL}"
    # echo "  Destino final: ${CONTAINER}:${DESTINO_FINAL}"
    
    if ! podman exec "$CONTAINER" test -d "$DESTINO_FINAL" 2>/dev/null; then
        echo ""
        echo "❌  ERROR: El directorio destino no existe en el contenedor "
        echo ""
        # echo "Ruta buscada: ${CONTAINER}:${DESTINO_FINAL}"
        echo ""
        echo "⚠️  Este directorio debe ser creado previamente desde la aplicacion web DKNotes."
        echo ""
        echo " 💡  Sugerencia:"
        echo "  1. Accede a la aplicacion web."
        echo "  2. Define  '${RUTA_PERSONAL}' como su directorio personal, guarde los cambios. "
        echo "  3. Vuelve a ejecutar este script."
        echo ""
        echo "⚠️  Por seguridad de los datos se ha omitido crear el directorio automaticamente."
        exit 1
    fi
    echo "  Directorio destino encontrado"
}

# ============================================================
# Mostrar resumen de archivos (modo dry-run)
# ============================================================
show_dry_run_summary() {
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
    echo "Modo simulacion (dry-run)"
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
    
    echo "Origen: ${LOCAL_DIR}"
    echo "Destino: ${CONTAINER}:${BASE_NOTES}/${RUTA_PERSONAL}"
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
        podman exec "$CONTAINER" find "${BASE_NOTES}/${RUTA_PERSONAL}" -name "*.md" -type f 2>/dev/null | while read -r remote_file; do
            rel_path="${remote_file#${BASE_NOTES}/${RUTA_PERSONAL}/}"
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
    
    DESTINO_FINAL="${BASE_NOTES}/${RUTA_PERSONAL}"
    
    # Verificar que el directorio existe antes de buscar
    podman exec "$CONTAINER" test -d "$DESTINO_FINAL" || return 0
    
    deleted=0
    podman exec "$CONTAINER" find "$DESTINO_FINAL" -name "*.md" -type f 2>/dev/null | while read -r remote_file; do
        rel_path="${remote_file#$DESTINO_FINAL/}"
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
    
    DESTINO_FINAL="${BASE_NOTES}/${RUTA_PERSONAL}"
    
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
        dest_dir="${DESTINO_FINAL}/$(dirname "$rel_path")"
        
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
    # echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
    # echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
    
    # Verificar que el directorio destino existe
    check_dest_dir

    echo "Copiando notas al contenedor..."
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
    echo "Ahora acceda a la aplicacion web DKNotes :"
    echo "  - Elija 'Sincronizar' "
    echo "  - Revise en 'Mis Notas' u obtenga un listado"
    echo "  - Esta todo listo para gestionar el contenido de sus notas."
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
}

# ============================================================
# Procesar argumento
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
        -p|--personal)
            RUTA_PERSONAL="$2"
            shift 2
            ;;
        -c|--container)
            CONTAINER="$2"
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
# Validar que se proporciono la ruta personal
# ============================================================
if [ -z "$RUTA_PERSONAL" ]; then
    echo "ERROR: Debes especificar la ruta personal con --personal"
    echo ""
    echo "Ejemplo:"
    echo "  $0 ~/notes --personal usuario"
    echo ""
    echo "Consulta la ayuda con --help para mas informacion."
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
# validate_personal_path
normalize_personal_path

# Ejecutar segun modo
if [ "$DRY_RUN" = true ]; then
    show_dry_run_summary
else
    run_sync
fi

echo ""