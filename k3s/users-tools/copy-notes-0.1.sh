#!/bin/bash

# ============================================================
# sync-notes.sh - Sincroniza notas con servidor NFS via SSH/rsync
# ============================================================

# Configuracion NFS/SSH
NFS_SERVER="root@pve.homelab"
NFS_BASE="/archives/dknotes/app/public"
SSH_KEY="~/.ssh/datenmaniak"
SSH_CMD="ssh -i ${SSH_KEY}"

# Valores por defecto
DRY_RUN=false
DELETE_ORPHANS=false
RUTA_PERSONAL=""
LOCAL_DIR=""

# ============================================================
# Mostrar ayuda
# ============================================================
show_help() {
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
    echo "sync-notes.sh - Sincroniza notas con servidor NFS (DKNotes)"
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
    echo ""
    echo "Uso: $0 <DIRECTORIO_LOCAL> --personal <RUTA> [OPCIONES]"
    echo ""
    echo "Argumentos:"
    echo "  DIRECTORIO_LOCAL       Ruta local de las notas (requerido)"
    echo "  -p, --personal RUTA    Ruta personal (subcarpeta) (requerido)"
    echo ""
    echo "Opciones:"
    echo "  -d, --dry-run          Simula la sincronizacion (no copia archivos)"
    echo "  -D, --delete-orphans   Elimina archivos en destino no existentes en origen"
    echo "  -h, --help             Muestra esta ayuda"
    echo ""
    echo "Ejemplos:"
    echo "  $0 ~/notes --personal usuario"
    echo "  $0 ~/notes -p proyecto-x --delete-orphans"
    echo "  $0 ~/mis-notas -p usuario --dry-run"
    echo ""
    echo "Infraestructura:"
    echo "  Servidor NFS: ${NFS_SERVER}"
    echo "  Ruta base:    ${NFS_BASE}"
    echo "  Destino final: ${NFS_BASE}/[RUTA_PERSONAL]"
    echo ""
}

# ============================================================
# Normalizar ruta personal (solo a-z)
# ============================================================
normalize_personal_path() {
    local original="$RUTA_PERSONAL"
    local normalized
    
    # Convertir a minusculas
    normalized=$(echo "$original" | tr '[:upper:]' '[:lower:]')
    
    # Eliminar acentos
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
# Verificar conectividad SSH con el servidor NFS
# ============================================================
check_ssh_connection() {
    echo -n "  Verificando conexion SSH con servidor NFS... "
    
    if ${SSH_CMD} -o ConnectTimeout=5 "${NFS_SERVER}" "exit" 2>/dev/null; then
        echo "OK"
        return 0
    else
        echo "ERROR"
        echo ""
        echo "❌ No se pudo conectar al servidor NFS: ${NFS_SERVER}"
        echo ""
        echo "Verifica:"
        echo "  - La clave SSH existe en: ${SSH_KEY}"
        echo "  - El servidor es accesible"
        echo "  - La clave esta autorizada en el servidor"
        echo ""
        exit 1
    fi
}

# ============================================================
# Verificar si el directorio local existe
# ============================================================
check_local_dir() {
    if [ ! -d "$LOCAL_DIR" ]; then
        echo "ERROR: El directorio local no existe: ${LOCAL_DIR}"
        exit 1
    fi
    echo "  OK: Directorio local encontrado: ${LOCAL_DIR}"
}

# ============================================================
# Verificar/Crear directorio destino en el NFS
# ============================================================
check_and_create_dest_dir() {
    DESTINO_FINAL="${NFS_BASE}/${RUTA_PERSONAL}"
    
    echo -n "  Verificando directorio destino: ${DESTINO_FINAL}... "
    
    # Verificar si existe
    if ${SSH_CMD} "${NFS_SERVER}" "test -d '${DESTINO_FINAL}'" 2>/dev/null; then
        echo "existe"
        return 0
    else
        echo "no existe"
        echo ""
        echo "⚠️  El directorio destino no existe. Creándolo automáticamente..."
        
        # Crear directorio (incluyendo padres si es necesario)
        if ${SSH_CMD} "${NFS_SERVER}" "mkdir -p '${DESTINO_FINAL}'" 2>/dev/null; then
            echo "  ✅ Directorio creado: ${DESTINO_FINAL}"
            echo ""
            echo "💡 Nota: Este directorio fue creado automaticamente en el NFS."
            echo "   Los pods de Kubernetes lo verán cuando lo monten."
            return 0
        else
            echo ""
            echo "❌ ERROR: No se pudo crear el directorio destino en el NFS"
            echo ""
            echo "Verifica permisos de escritura en: ${NFS_BASE}"
            exit 1
        fi
    fi
}

# ============================================================
# Mostrar resumen de archivos (modo dry-run)
# ============================================================
show_dry_run_summary() {
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
    echo "Modo simulacion (dry-run)"
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
    
    echo "Origen: ${LOCAL_DIR}"
    echo "Destino: ${NFS_SERVER}:${NFS_BASE}/${RUTA_PERSONAL}"
    echo ""
    echo "Archivos que seran sincronizados:"
    echo ""
    
    # Construir comando rsync para dry-run
    RSYNC_CMD="rsync -avzn"
    
    if [ "$DELETE_ORPHANS" = true ]; then
        RSYNC_CMD="${RSYNC_CMD} --delete"
        echo "Eliminacion de huerfanos: ACTIVADA"
    else
        echo "Eliminacion de huerfanos: DESACTIVADA (usa --delete-orphans para activar)"
    fi
    
    RSYNC_CMD="${RSYNC_CMD} -e '${SSH_CMD}'"
    RSYNC_CMD="${RSYNC_CMD} '${LOCAL_DIR}/' '${NFS_SERVER}:${NFS_BASE}/${RUTA_PERSONAL}/'"
    
    echo ""
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
    
    # Ejecutar rsync en modo dry-run
    eval ${RSYNC_CMD}
    
    echo ""
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
    echo "Para ejecutar la sincronizacion real, omite --dry-run"
}

# ============================================================
# Ejecutar sincronizacion real
# ============================================================
run_sync() {
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
    echo "Sincronizando notas con servidor NFS"
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
    
    # Construir comando rsync
    RSYNC_CMD="rsync -avz --progress"
    
    if [ "$DELETE_ORPHANS" = true ]; then
        RSYNC_CMD="${RSYNC_CMD} --delete"
        echo "  Modo: Sincronizacion completa (con eliminacion de huerfanos)"
    else
        echo "  Modo: Sincronizacion incremental (sin eliminar huerfanos)"
    fi
    
    RSYNC_CMD="${RSYNC_CMD} -e '${SSH_CMD}'"
    RSYNC_CMD="${RSYNC_CMD} '${LOCAL_DIR}/' '${NFS_SERVER}:${NFS_BASE}/${RUTA_PERSONAL}/'"
    
    echo "  Origen:  ${LOCAL_DIR}"
    echo "  Destino: ${NFS_SERVER}:${NFS_BASE}/${RUTA_PERSONAL}"
    echo ""
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
    
    # Ejecutar rsync
    eval ${RSYNC_CMD}
    
    # Verificar resultado
    if [ $? -eq 0 ]; then
        echo ""
        echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
        echo "✅ Sincronizacion completada exitosamente"
        echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
        echo ""
        echo "📌 IMPORTANTE:"
        echo "   Las notas han sido copiadas al NFS."
        echo "   Para que los cambios se reflejen en la aplicacion web:"
        echo ""
        echo "   1. Accede a la aplicacion web DKNotes"
        echo "   2. Ve a la seccion de 'Sincronizacion'"
        echo "   3. Haz clic en 'Sincronizar notas'"
        echo "   4. Revisa tus notas en 'Mis Notas'"
        echo ""
        echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
    else
        echo ""
        echo "❌ ERROR: La sincronizacion fallo"
        echo "   Revisa los mensajes de error arriba"
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
        -D|--delete-orphans)
            DELETE_ORPHANS=true
            shift
            ;;
        -p|--personal)
            RUTA_PERSONAL="$2"
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
# Validaciones iniciales
# ============================================================
if [ -z "$LOCAL_DIR" ]; then
    echo "ERROR: Debes especificar el directorio local de notas"
    echo ""
    show_help
    exit 1
fi

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
echo "DKNotes - Sincronizacion de notas (NFS/SSH)"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo ""

# Validaciones
check_local_dir
check_ssh_connection
normalize_personal_path

# Verificar/Crear directorio destino (incluso en dry-run para validar)
if [ "$DRY_RUN" = true ]; then
    # En dry-run solo verificamos, no creamos
    DESTINO_FINAL="${NFS_BASE}/${RUTA_PERSONAL}"
    if ! ${SSH_CMD} "${NFS_SERVER}" "test -d '${DESTINO_FINAL}'" 2>/dev/null; then
        echo ""
        echo "⚠️  El directorio destino no existe: ${DESTINO_FINAL}"
        echo "   En modo dry-run no se crea automaticamente."
        echo "   La sincronizacion real lo creara si es necesario."
        echo ""
    fi
else
    # En modo real, creamos si no existe
    check_and_create_dest_dir
fi

# Ejecutar segun modo
if [ "$DRY_RUN" = true ]; then
    show_dry_run_summary
else
    run_sync
fi

echo ""