#!/bin/bash

# ============================================================
# Script de construccion y subida de imagenes Docker
# ============================================================

# Configuracion del registro privado
REGISTRY_URL="local-reg.dk.lab:5000"
REGISTRY_USER="datenmaniak"
REGISTRY_PASS="REGISTRA"

# Inicializar variables
IMAGE=""
TAG=""
DOCKERFILE=""
DO_BUILD=true
DO_PUSH=true
NO_CACHE=false
VERBOSE=false
HELP=false
BUILD_ARGS=()

# ============================================================
# Funciones de ayuda
# ============================================================

show_help() {
    echo "Uso: $0 [OPCIONES]"
    echo ""
    echo "Opciones obligatorias:"
    echo "  --image <nombre>       Nombre de la imagen (ej: backend-api)"
    echo "  --tag <version>        Tag/versión (ej: v1.2.3)"
    echo "  --dockerfile <ruta>    Ruta al Dockerfile (ej: ./Dockerfile)"
    echo ""
    echo "Opciones de control:"
    echo "  --build-only           Solo construir imagen (no subir)"
    echo "  --push-only            Solo subir imagen existente (no construir)"
    echo ""
    echo "Otras opciones:"
    echo "  --no-cache             Construir sin usar caché"
    echo "  --build-arg <key=val>  Argumentos para el build (puede repetirse)"
    echo "  --verbose              Mostrar comandos ejecutados"
    echo "  --help                 Mostrar esta ayuda"
    echo ""
    echo "Comportamiento por defecto: Build + Push"
    echo ""
    echo "Ejemplos:"
    echo "  $0 --image api --tag v1.0.0 --dockerfile ./Dockerfile"
    echo "  $0 --image api --tag v1.0.0 --dockerfile ./Dockerfile --build-only"
    echo "  $0 --image api --tag v1.0.0 --dockerfile ./Dockerfile --push-only"
    echo ""
    echo "Formato posicional (compatibilidad):"
    echo "  $0 <nombre-imagen> <tag> <ruta-dockerfile> [--build-only] [--push-only]"
}

log_verbose() {
    if [ "$VERBOSE" = true ]; then
        echo "[VERBOSE] $1"
    fi
}

# ============================================================
# Parseo de argumentos
# ============================================================

# Si no hay argumentos, mostrar ayuda
if [ $# -eq 0 ]; then
    show_help
    exit 0
fi

# Detectar si es formato posicional (sin flags)
if [ $# -gt 0 ] && [[ ! "$1" =~ ^-- ]]; then
    # Modo posicional: IMAGE TAG DOCKERFILE
    IMAGE="$1"
    TAG="$2"
    DOCKERFILE="$3"
    shift 3
    # Procesar flags restantes
    while [ $# -gt 0 ]; do
        case "$1" in
            --build-only) DO_PUSH=false ;;
            --push-only)  DO_BUILD=false ;;
            --no-cache)   NO_CACHE=true ;;
            --verbose)    VERBOSE=true ;;
            --help)       HELP=true ;;
            *) ;;
        esac
        shift
    done
else
    # Modo flags
    while [ $# -gt 0 ]; do
        case "$1" in
            --image)
                IMAGE="$2"
                shift 2
                ;;
            --tag)
                TAG="$2"
                shift 2
                ;;
            --dockerfile)
                DOCKERFILE="$2"
                shift 2
                ;;
            --build-only)
                DO_PUSH=false
                shift
                ;;
            --push-only)
                DO_BUILD=false
                shift
                ;;
            --no-cache)
                NO_CACHE=true
                shift
                ;;
            --build-arg)
                BUILD_ARGS+=("$2")
                shift 2
                ;;
            --verbose)
                VERBOSE=true
                shift
                ;;
            --help)
                HELP=true
                shift
                ;;
            *)
                echo "❌ Opción desconocida: $1"
                echo "   Usa --help para ver las opciones disponibles"
                exit 1
                ;;
        esac
    done
fi

# Mostrar ayuda si se solicita
if [ "$HELP" = true ]; then
    show_help
    exit 0
fi

# ============================================================
# Validaciones
# ============================================================

# Validar parametros obligatorios
if [ -z "$IMAGE" ] || [ -z "$TAG" ] || [ -z "$DOCKERFILE" ]; then
    echo "❌ Faltan parámetros obligatorios"
    echo "   Usa --help para ver las opciones disponibles"
    exit 1
fi

# Validar mutua exclusion
if [ "$DO_BUILD" = false ] && [ "$DO_PUSH" = false ]; then
    echo "❌ --build-only y --push-only son mutuamente excluyentes"
    exit 1
fi

# Validar existencia del Dockerfile
if [ ! -f "$DOCKERFILE" ]; then
    echo "❌ Dockerfile no encontrado: $DOCKERFILE"
    exit 1
fi

# Variables finales
FULL_IMAGE="${REGISTRY_URL}/${IMAGE}:${TAG}"

# ============================================================
# Validacion especifica para Solo Push
# ============================================================

if [ "$DO_PUSH" = true ] && [ "$DO_BUILD" = false ]; then
    echo "🔍 Verificando imagen local: $FULL_IMAGE"
    if ! podman image inspect "$FULL_IMAGE" > /dev/null 2>&1; then
        echo "❌ Imagen no encontrada localmente: $FULL_IMAGE"
        echo "   Sugerencia: Ejecuta sin --push-only para construirla primero"
        exit 1
    fi
    echo "✅ Imagen encontrada localmente"
fi

# ============================================================
# Ejecucion
# ============================================================

# Build
if [ "$DO_BUILD" = true ]; then
    echo "📦 Construyendo: $FULL_IMAGE"
    log_verbose "podman build -t $FULL_IMAGE -f $DOCKERFILE . ${BUILD_ARGS[@]/#/--build-arg } $([ "$NO_CACHE" = true ] && echo "--no-cache")"
    
    BUILD_CMD="podman build -t $FULL_IMAGE -f $DOCKERFILE ."
    
    # Agregar build-args
    for arg in "${BUILD_ARGS[@]}"; do
        BUILD_CMD="$BUILD_CMD --build-arg $arg"
    done
    
    # Agregar no-cache
    if [ "$NO_CACHE" = true ]; then
        BUILD_CMD="$BUILD_CMD --no-cache"
    fi
    
    log_verbose "Ejecutando: $BUILD_CMD"
    if ! eval "$BUILD_CMD"; then
        echo "❌ Falló la construcción de la imagen"
        exit 1
    fi
    echo "✅ Build completado"
fi

# Push
if [ "$DO_PUSH" = true ]; then
    # Autenticar solo si se va a hacer push
    echo "🔐 Autenticando en: $REGISTRY_URL"
    log_verbose "echo \"$REGISTRY_PASS\" | podman login $REGISTRY_URL -u $REGISTRY_USER --password-stdin"
    
    if ! echo "$REGISTRY_PASS" | podman login "$REGISTRY_URL" -u "$REGISTRY_USER" --password-stdin > /dev/null 2>&1; then
        echo "❌ Error en autenticación con el registro"
        exit 1
    fi
    echo "✅ Autenticación exitosa"
    
    # Verificar nuevamente existencia local (por si build fallo o no se hizo)
    if ! podman image inspect "$FULL_IMAGE" > /dev/null 2>&1; then
        echo "❌ Imagen no encontrada localmente: $FULL_IMAGE"
        exit 1
    fi
    
    echo "📤 Subiendo: $FULL_IMAGE"
    log_verbose "podman push $FULL_IMAGE"
    
    if ! podman push "$FULL_IMAGE"; then
        echo "❌ Falló la subida de la imagen"
        exit 1
    fi
    echo "✅ Push completado"
fi

# ============================================================
# Resumen final
# ============================================================

echo "========================================="
if [ "$DO_BUILD" = true ] && [ "$DO_PUSH" = true ]; then
    echo "✅ Build + Push completado"
elif [ "$DO_BUILD" = true ]; then
    echo "✅ Build completado"
elif [ "$DO_PUSH" = true ]; then
    echo "✅ Push completado"
fi
echo "📦 Imagen: $FULL_IMAGE"
echo "========================================="