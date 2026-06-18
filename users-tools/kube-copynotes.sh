#!/bin/bash

# ============================================================
# sync-notes.sh - Sincroniza notas via NFS/SSH con kubectl
# Alternativa C: Permisos asignados desde el pod
# ============================================================

# Configuracion NFS/SSH
NFS_SERVER="root@pve.homelab"
NFS_BASE="/archives/dknotes/app/public/notes"
SSH_KEY="~/.ssh/datenmaniak"
SSH_CMD="ssh -i ${SSH_KEY}"

# Configuracion Kubernetes
K8S_NAMESPACE="dknotes"
K8S_DEPLOY="dknotes-web"
K8S_CONTAINER="web-app"
K8S_BASE="/var/www/html/storage/app/public/notes"

# Valores por defecto
DRY_RUN=false
DELETE_ORPHANS=false
RUTA_PERSONAL=""
LOCAL_DIR=""
SKIP_K8S_CHECK=false

# ============================================================
# Mostrar ayuda
# ============================================================
show_help() {
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
    echo "sync-notes.sh - Sincroniza notas con servidor NFS (DKNotes)"
    echo "Alternativa C: Permisos asignados desde el pod via kubectl"
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
    echo "  -k, --skip-k8s-check   Omite verificacion de conexion a Kubernetes"
    echo "  -h, --help             Muestra esta ayuda"
    echo ""
    echo "Caracteristicas:"
    echo "  - Copia archivos via rsync/SSH al NFS"
    echo "  - Asigna permisos ejecutando chown dentro del pod via kubectl"
    echo "  - Requiere acceso a Kubernetes (kubeconfig configurado)"
    echo ""
    echo "Ejemplos:"
    echo "  $0 ~/notes --personal usuario"
    echo "  $0 ~/notes -p proyecto-x --delete-orphans"
    echo "  $0 ~/mis-notas -p usuario --skip-k8s-check"
    echo ""
}

# ============================================================
# Normalizar ruta personal
# ============================================================
normalize_personal_path() {
    local original="$RUTA_PERSONAL"
    local normalized
    
    normalized=$(echo "$original" | tr '[:upper:]' '[:lower:]')
    normalized=$(echo "$normalized" | sed 's/[áäâà]/a/g; s/[éëêè]/e/g; s/[íïîì]/i/g; s/[óöôò]/o/g; s/[úüûù]/u/g')
    normalized=$(echo "$normalized" | sed 's/ñ/n/g')
    normalized=$(echo "$normalized" | sed 's/[^a-z]//g')
    
    if [ -z "$normalized" ]; then
        echo ""
        echo "ERROR: La ruta personal no contiene caracteres validos"
        echo "Original: \"$original\" → Despues de normalizar: (vacio)"
        echo ""
        exit 1
    fi
    
    if [ ${#normalized} -gt 100 ]; then
        normalized="${normalized:0:100}"
        echo "  (Ruta personal truncada a 100 caracteres)"
    fi
    
    if [ "$normalized" != "$original" ]; then
        echo "  Ruta personal normalizada: \"$original\" → \"$normalized\""
    fi
    
    RUTA_PERSONAL="$normalized"
    echo "  OK: Ruta personal: $RUTA_PERSONAL"
}

# ============================================================
# Verificar conectividad SSH
# ============================================================
check_ssh_connection() {
    echo -n "  Verificando conexion SSH con servidor NFS... "
    
    if ${SSH_CMD} -o ConnectTimeout=5 "${NFS_SERVER}" "exit" 2>/dev/null; then
        echo "OK"
        return 0
    else
        echo "ERROR"
        echo "❌ No se pudo conectar al servidor NFS"
        exit 1
    fi
}

# ============================================================
# Verificar conectividad Kubernetes
# ============================================================
check_k8s_connection() {
    if [ "$SKIP_K8S_CHECK" = true ]; then
        echo "  ⚠️  Verificacion de Kubernetes omitida (--skip-k8s-check)"
        return 0
    fi
    
    echo -n "  Verificando conexion a Kubernetes... "
    
    if ! command -v kubectl &> /dev/null; then
        echo "ERROR"
        echo "❌ kubectl no esta instalado o no esta en el PATH"
        exit 1
    fi
    
    if ! kubectl cluster-info &> /dev/null; then
        echo "ERROR"
        echo "❌ No se puede conectar al cluster Kubernetes"
        echo "   Verifica tu archivo kubeconfig"
        exit 1
    fi
    echo "OK"
    
    echo -n "  Verificando deployment ${K8S_DEPLOY} en namespace ${K8S_NAMESPACE}... "
    if ! kubectl -n "${K8S_NAMESPACE}" get deployment "${K8S_DEPLOY}" &> /dev/null; then
        echo "ERROR"
        echo "❌ Deployment no encontrado: ${K8S_DEPLOY}"
        echo "   Namespace: ${K8S_NAMESPACE}"
        exit 1
    fi
    echo "OK"
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
# Verificar/Crear directorio destino en NFS
# ============================================================
check_and_create_dest_dir() {
    DESTINO_FINAL="${NFS_BASE}/${RUTA_PERSONAL}"
    
    echo -n "  Verificando directorio destino en NFS... "
    
    if ${SSH_CMD} "${NFS_SERVER}" "test -d '${DESTINO_FINAL}'" 2>/dev/null; then
        echo "existe"
        return 0
    else
        echo "no existe, creando..."
        ${SSH_CMD} "${NFS_SERVER}" "mkdir -p '${DESTINO_FINAL}'"
        
        if [ $? -eq 0 ]; then
            echo "  ✅ Directorio creado en NFS"
            return 0
        else
            echo "❌ ERROR: No se pudo crear el directorio destino"
            exit 1
        fi
    fi
}

# ============================================================
# Asignar permisos via kubectl desde el pod
# ============================================================
apply_permissions_via_kubectl() {
    echo ""
    echo "  Asignando permisos via kubectl (desde el pod)..."
    
    K8S_DEST_DIR="${K8S_BASE}/${RUTA_PERSONAL}"
    
    echo -n "    Ejecutando chown -R www-data:www-data ${K8S_DEST_DIR} ... "
    
    if kubectl -n "${K8S_NAMESPACE}" exec "deploy/${K8S_DEPLOY}" -c "${K8S_CONTAINER}" \
        -- chown -R www-data:www-data "${K8S_DEST_DIR}" 2>/dev/null; then
        echo "OK"
        echo -n "    Estableciendo permisos (directorios=755, archivos=644)... "
        
        # Establecer permisos estandar
        kubectl -n "${K8S_NAMESPACE}" exec "deploy/${K8S_DEPLOY}" -c "${K8S_CONTAINER}" \
            -- bash -c "find ${K8S_DEST_DIR} -type d -exec chmod 755 {} \; && find ${K8S_DEST_DIR} -type f -exec chmod 644 {} \;" 2>/dev/null
        echo "OK"
        return 0
    else
        echo "ERROR"
        echo ""
        echo "⚠️  No se pudieron asignar permisos via kubectl"
        echo "   Posibles causas:"
        echo "   - El pod no esta corriendo"
        echo "   - La ruta ${K8S_DEST_DIR} no existe dentro del pod"
        echo "   - El usuario www-data no existe en el pod"
        echo ""
        
        if [ "$SKIP_K8S_CHECK" = false ]; then
            echo "   Sugerencia: Ejecuta manualmente:"
            echo "   kubectl -n ${K8S_NAMESPACE} exec deploy/${K8S_DEPLOY} -c ${K8S_CONTAINER} -- chown -R www-data:www-data ${K8S_DEST_DIR}"
        fi
        return 1
    fi
}

# ============================================================
# Mostrar resumen (dry-run)
# ============================================================
show_dry_run_summary() {
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
    echo "Modo simulacion (dry-run)"
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
    
    echo "Origen: ${LOCAL_DIR}"
    echo "Destino NFS: ${NFS_SERVER}:${NFS_BASE}/${RUTA_PERSONAL}"
    echo "Destino Pod: ${K8S_BASE}/${RUTA_PERSONAL}"
    echo ""
    
    RSYNC_CMD="rsync -avzn"
    
    if [ "$DELETE_ORPHANS" = true ]; then
        RSYNC_CMD="${RSYNC_CMD} --delete"
        echo "Eliminacion de huerfanos: ACTIVADA"
    else
        echo "Eliminacion de huerfanos: DESACTIVADA"
    fi
    
    RSYNC_CMD="${RSYNC_CMD} -e '${SSH_CMD}'"
    RSYNC_CMD="${RSYNC_CMD} '${LOCAL_DIR}/' '${NFS_SERVER}:${NFS_BASE}/${RUTA_PERSONAL}/'"
    
    echo ""
    echo "Archivos que seran copiados:"
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
    eval ${RSYNC_CMD}
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
    
    if [ "$SKIP_K8S_CHECK" = false ]; then
        echo ""
        echo "Post-sincronizacion:"
        echo "  Se ejecutara: chown -R www-data:www-data ${K8S_BASE}/${RUTA_PERSONAL}"
        echo "  (via kubectl dentro del pod)"
    fi
}

# ============================================================
# Ejecutar sincronizacion real
# ============================================================
run_sync() {
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
    echo "Sincronizando notas (copia a NFS + permisos via kubectl)"
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
    
    # Construir comando rsync (sin --chown)
    RSYNC_CMD="rsync -avz --progress"

    # Patrones de inclusión (solo .md y directorios)
    RSYNC_CMD="${RSYNC_CMD} --include='*.md'"
    RSYNC_CMD="${RSYNC_CMD} --include='*/'"

    # Exclusiones de directorios
    for dir in $EXCLUDE_DIRS; do
        RSYNC_CMD="${RSYNC_CMD} --exclude='${dir}'"
    done

    # Exclusiones de archivos
    for pattern in $EXCLUDE_FILES; do
        RSYNC_CMD="${RSYNC_CMD} --exclude='${pattern}'"
    done

    # Excluir todo lo demás
    RSYNC_CMD="${RSYNC_CMD} --exclude='*'"
    
    if [ "$DELETE_ORPHANS" = true ]; then
        RSYNC_CMD="${RSYNC_CMD} --delete"
        echo "  Modo: Sincronizacion completa (con eliminacion de huerfanos)"
    else
        echo "  Modo: Sincronizacion incremental (sin eliminar huerfanos)"
    fi
    
    RSYNC_CMD="${RSYNC_CMD} -e '${SSH_CMD}'"
    RSYNC_CMD="${RSYNC_CMD} '${LOCAL_DIR}/' '${NFS_SERVER}:${NFS_BASE}/${RUTA_PERSONAL}/'"
    
    echo "  Origen:   ${LOCAL_DIR}"
    echo "  Destino:  ${NFS_SERVER}:${NFS_BASE}/${RUTA_PERSONAL}"
    echo ""
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
    
    # Ejecutar rsync
    eval ${RSYNC_CMD}
    
    if [ $? -ne 0 ]; then
        echo ""
        echo "❌ ERROR: La sincronizacion via rsync fallo"
        exit 1
    fi
    
    # Asignar permisos via kubectl (solo si no es dry-run)
    if [ "$DRY_RUN" = false ] && [ "$SKIP_K8S_CHECK" = false ]; then
        apply_permissions_via_kubectl
        PERMISSIONS_OK=$?
    else
        PERMISSIONS_OK=0
    fi
    
    echo ""
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
    
    if [ $PERMISSIONS_OK -eq 0 ]; then
        echo "✅ Sincronizacion completada exitosamente"
        echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
        echo ""
        echo "📌 Las notas han sido copiadas y los permisos han sido asignados."
        echo ""
        echo "   Si las notas no aparecen inmediatamente en la app:"
        echo "   1. Accede a la aplicacion web DKNotes"
        echo "   2. Ve a 'Sincronizacion' y haz clic en 'Sincronizar notas'"
        echo "   3. Revisa 'Mis Notas'"
        echo ""
    else
        echo "⚠️  Sincronizacion completada pero con advertencias"
        echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
        echo ""
        echo "📌 Los archivos fueron copiados al NFS, pero los permisos"
        echo "   no pudieron asignarse automaticamente."
        echo ""
        echo "   Para asignarlos manualmente, ejecuta:"
        echo "   kubectl -n ${K8S_NAMESPACE} exec deploy/${K8S_DEPLOY} -c ${K8S_CONTAINER} -- \\"
        echo "     chown -R www-data:www-data ${K8S_BASE}/${RUTA_PERSONAL}"
        echo ""
    fi
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
        -p|--personal)
            RUTA_PERSONAL="$2"
            shift 2
            ;;
        -k|--skip-k8s-check)
            SKIP_K8S_CHECK=true
            shift
            ;;
        -*)
            echo "ERROR: Opcion desconocida: $1"
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
    show_help
    exit 1
fi

if [ -z "$RUTA_PERSONAL" ]; then
    echo "ERROR: Debes especificar la ruta personal con --personal"
    echo "Ejemplo: $0 ~/notes --personal usuario"
    exit 1
fi

# ============================================================
# Ejecutar script
# ============================================================
echo ""
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "DKNotes - Sincronizacion de notas (Alternativa C)"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo ""

check_local_dir
check_ssh_connection

if [ "$SKIP_K8S_CHECK" = false ]; then
    check_k8s_connection
else
    echo "  ⚠️  Verificacion de Kubernetes omitida (--skip-k8s-check)"
fi

normalize_personal_path

if [ "$DRY_RUN" = true ]; then
    DESTINO_FINAL="${NFS_BASE}/${RUTA_PERSONAL}"
    if ! ${SSH_CMD} "${NFS_SERVER}" "test -d '${DESTINO_FINAL}'" 2>/dev/null; then
        echo ""
        echo "⚠️  El directorio destino no existe (dry-run)"
        echo "   En modo real se creara automaticamente"
        echo ""
    fi
    show_dry_run_summary
else
    check_and_create_dest_dir
    run_sync
fi

echo ""