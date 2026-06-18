#!/bin/bash

# ============================================================
# Configuración del Entorno Seguro (Modo Estricto)
# ============================================================
# -e: Termina inmediatamente si un comando falla.
# -u: Termina si se intenta usar una variable no declarada.
# -o pipefail: Captura fallos intermedios dentro de tuberías (pipelines).
set -euo pipefail

# ============================================================
# Configuración de Infraestructura Fija (Sincronizada)
# ============================================================
REGISTRY_URL="local-reg.dk.lab:5000"
REGISTRY_USER="datenmaniak"

# ============================================================
# Inicialización de Variables y Flags por Defecto
# ============================================================
IMAGE=""
TAG="latest"
DOCKERFILE=""
APP_DIR=""
ONLY_BUILD=false
ONLY_PUSH=false
NO_CACHE=false
VERBOSE=false
HELP=false
BUILD_ARGS=()
CONTAINER_ENGINE="podman"

# ============================================================
# Funciones de Ayuda y Diagnóstico
# ============================================================
show_help() {
  echo "Uso: $0 [OPCIONES]"
  echo ""
  echo "Opciones de parámetros (El orden de los flags no importa):"
  echo "  -i, --image <nombre>       Nombre de la imagen (ej: dknotes-laravel)"
  echo "  -t, --tag <version>        Tag/versión para la imagen (Por defecto: latest)"
  echo "  -f, --dockerfile <ruta>    Ruta relativa al Dockerfile (Obligatorio en Build)"
  echo "  -d, --dir <ruta>           Directorio raíz de la aplicación (Por defecto: directorio actual)"
  echo "  -m, --engine <motor>       Forzar motor: podman o docker (Por defecto: podman)"
  echo ""
  echo "Opciones de flujo (Alternativas de ejecución):"
  echo "  -b, --build-only           Alternativa 2: Solo construir imagen (bloquea el push)"
  echo "  -p, --push-only            Alternativa 3: Solo subir imagen existente (bloquea el build y no pide Dockerfile)"
  echo ""
  echo "Otras opciones:"
  echo "  -n, --no-cache             Construir sin usar la caché del motor"
  echo "  -a, --build-arg <key=val>  Argumentos de construcción para el Dockerfile (Puede repetirse)"
  echo "  -v, --verbose              Modo verboso: Muestra logs detallados de depuración"
  echo "  -h, --help                 Mostrar este manual de ayuda"
  echo ""
  echo "Nota: Si no se especifica -b ni -p, se ejecuta la Alternativa 1 (Build + Push completa)."
}

log_verbose() {
  if [ "$VERBOSE" = true ]; then
    echo "[VERBOSE] $1"
  fi
}

# ============================================================
# Parseo de Argumentos mediante Flags Flexibles
# ============================================================
if [ $# -eq 0 ]; then
  show_help
  exit 0
fi

while [ $# -gt 0 ]; do
  case "$1" in
    -i|--image)
      IMAGE="$2"
      shift 2
      ;;
    -t|--tag)
      TAG="$2"
      shift 2
      ;;
    -f|--dockerfile)
      DOCKERFILE="$2"
      shift 2
      ;;
    -d|--dir)
      APP_DIR="$2"
      shift 2
      ;;
    -m|--engine)
      CONTAINER_ENGINE="$2"
      shift 2
      ;;
    -b|--build-only)
      ONLY_BUILD=true
      shift
      ;;
    -p|--push-only)
      ONLY_PUSH=true
      shift
      ;;
    -n|--no-cache)
      NO_CACHE=true
      shift
      ;;
    -a|--build-arg)
      BUILD_ARGS+=("$2")
      shift 2
      ;;
    -v|--verbose)
      VERBOSE=true
      shift
      ;;
    -h|--help)
      HELP=true
      shift
      ;;
    *)
      echo "❌ Opción desconocida: $1"
      echo "   Usa -h o --help para ver las opciones válidas."
      exit 1
      ;;
  esac
done

if [ "$HELP" = true ]; then
  show_help
  exit 0
fi

# ============================================================
# Matriz de Validaciones Críticas de Entorno y Negocio
# ============================================================

# 1. Validación del parámetro esencial
if [ -z "$IMAGE" ]; then
  echo "❌ Error: El parámetro del nombre de la imagen (-i) es estrictamente obligatorio."
  exit 1
fi

# 2. Validación y verificación del motor de contenedores seleccionado
if [ "$CONTAINER_ENGINE" != "podman" ] && [ "$CONTAINER_ENGINE" != "docker" ]; then
  echo "❌ Error: El motor especificado (-m) debe ser exclusivamente 'podman' o 'docker'."
  exit 1
fi

if ! command -v "$CONTAINER_ENGINE" &> /dev/null; then
  echo "❌ Error: El motor '$CONTAINER_ENGINE' no se encuentra instalado o disponible en el PATH."
  exit 1
fi

# 3. Validación de exclusión mutua de flujos
if [ "$ONLY_BUILD" = true ] && [ "$ONLY_PUSH" = true ]; then
  echo "❌ Conflicto: Las opciones -b (--build-only) y -p (--push-only) no pueden usarse juntas."
  exit 1
fi

# 4. Resolución y validación del Directorio Raíz de la Aplicación (-d)
if [ -z "$APP_DIR" ]; then
  APP_DIR=$(pwd)
  log_verbose "Directorio raíz de la aplicación asignado automáticamente al actual: $APP_DIR"
else
  # Convertir a ruta absoluta si es necesario para evitar fallos geográficos
  APP_DIR=$(cd "$APP_DIR" &> /dev/null && pwd || echo "")
  if [ -z "$APP_DIR" ] || [ ! -d "$APP_DIR" ]; then
    echo "❌ Error: El directorio raíz especificado (-d) no existe en el sistema o no es accesible."
    exit 1
  fi
fi

# 5. Validación condicional del Dockerfile (Se omite por completo si es Solo Push)
if [ "$ONLY_PUSH" = false ]; then
  if [ -z "$DOCKERFILE" ]; then
    echo "❌ Error: Se requiere especificar la ruta del Dockerfile (-f) para realizar la construcción."
    exit 1
  fi

  # Evaluar la ruta del Dockerfile tomando como pivote seguro el directorio de la aplicación
  REAL_DOCKERFILE_PATH="$DOCKERFILE"
  if [[ ! "$DOCKERFILE" =~ ^/ ]]; then
    REAL_DOCKERFILE_PATH="$APP_DIR/$DOCKERFILE"
  fi

  if [ ! -f "$REAL_DOCKERFILE_PATH" ]; then
    echo "❌ Error: No se encuentra el archivo Dockerfile en la ubicación esperada:"
    echo "   Ruta evaluada: $REAL_DOCKERFILE_PATH"
    exit 1
  fi
fi

# Construcción de la nomenclatura de la imagen destino uniforme
FULL_IMAGE="${REGISTRY_URL}/${IMAGE}:${TAG}"

# 6. Validar existencia local si es la Alternativa 3 (Solo Push) basado en ejecución real
if [ "$ONLY_PUSH" = true ]; then
  echo "🔍 Verificando existencia de la imagen en el almacenamiento local..."
  if ! "$CONTAINER_ENGINE" image inspect "$FULL_IMAGE" &> /dev/null; then
    echo "❌ Error: La imagen $FULL_IMAGE no existe localmente en $CONTAINER_ENGINE."
    echo "   Sugerencia: Ejecuta el script sin el flag -p para compilarla primero."
    exit 1
  fi
  echo "✅ Imagen detectada localmente y lista."
fi

# ============================================================
# EJECUCIÓN: Alternativa 1 y 2 - Bloque de Construcción (Build)
# ============================================================
if [ "$ONLY_PUSH" = false ]; then
  echo "📦 Iniciando construcción de la imagen con $CONTAINER_ENGINE..."
  echo "   Raíz de la aplicación: $APP_DIR"
  echo "   Archivo Dockerfile: $REAL_DOCKERFILE_PATH"

  # Uso estricto de Arrays nativos de Bash para mitigar inyecciones de código (Seguridad de eval)
  CMD=("$CONTAINER_ENGINE" "build" "-f" "$REAL_DOCKERFILE_PATH" "-t" "$FULL_IMAGE")

  for arg in "${BUILD_ARGS[@]}"; do
    CMD+=("--build-arg" "$arg")
  done

  if [ "$NO_CACHE" = true ]; then
    CMD+=("--no-cache")
  fi

  # Inyección segura del directorio de la aplicación como el contexto raíz absoluto al final del comando
  CMD+=("$APP_DIR")

  log_verbose "Ejecutando comando estructural: ${CMD[*]}"

  # Ejecución directa evaluando explícitamente el código de salida devuelto por el motor
  BUILD_EXIT_CODE=0
  "${CMD[@]}" || BUILD_EXIT_CODE=$?

  if [ $BUILD_EXIT_CODE -ne 0 ]; then
    echo "❌ Error: La construcción de la imagen falló con código de salida $BUILD_EXIT_CODE. Se aborta el flujo."
    exit 1
  fi
  echo "✅ Construcción finalizada con éxito."
fi

# ============================================================
# EJECUCIÓN: Alternativa 1 y 3 - Bloque de Distribución (Push)
# ============================================================
if [ "$ONLY_BUILD" = false ]; then

  # Gestión e inyección segura de credenciales mediante entorno o solicitud interactiva
  if [ -z "${REGISTRY_PASS:-}" ]; then
    echo "⚠️ Advertencia: No se detectó la variable de entorno de sesión REGISTRY_PASS."
    if [ -t 0 ]; then
      echo "🔐 Por favor, introduce la contraseña para acceder a $REGISTRY_URL:"
      stty -echo
      read -r REGISTRY_PASS
      stty echo
      echo ""
    else
      echo "❌ Error: Ejecución no interactiva desatendida detectada sin proveer la variable REGISTRY_PASS."
      exit 1
    fi
  fi

  echo "🔐 Autenticando en el registro privado..."
  
  # Captura estricta del flujo estándar y de error para auditoría si la ejecución falla
  LOGIN_OUTPUT=$(echo "$REGISTRY_PASS" | "$CONTAINER_ENGINE" login "$REGISTRY_URL" --username "$REGISTRY_USER" --password-stdin --tls-verify=false 2>&1) || true

  # Validación estricta analizando el resultado real expuesto por el comando
  if ! echo "$LOGIN_OUTPUT" | grep -E -q "(Login Succeeded|Identities hosted|logged in)"; then
    echo "❌ Error: La autenticación falló drásticamente contra $REGISTRY_URL."
    echo "   Detalle arrojado por el motor de contenedores:"
    echo "----------------------------------------------------------------"
    echo "$LOGIN_OUTPUT"
    echo "----------------------------------------------------------------"
    exit 1
  fi
  echo "✅ Autenticación exitosa."

  # Verificación de integridad post-build previa a la subida de red
  if ! "$CONTAINER_ENGINE" image inspect "$FULL_IMAGE" &> /dev/null; then
    echo "❌ Error de Consistencia Interna: La imagen local no se localiza en el motor para iniciar la subida."
    exit 1
  fi

  echo "📤 Subiendo la imagen al registro local..."
  log_verbose "Ejecutando de manera interactiva: $CONTAINER_ENGINE push --tls-verify=false $FULL_IMAGE"
  
  PUSH_EXIT_CODE=0
  "$CONTAINER_ENGINE" push --tls-verify=false "$FULL_IMAGE" || PUSH_EXIT_CODE=$?

  if [ $PUSH_EXIT_CODE -ne 0 ]; then
    echo "❌ Error: El comando push falló con código de salida $PUSH_EXIT_CODE. No se completó la entrega."
    exit 1
  fi

  echo "✅ Envío al registro completado con éxito."

  # Limpieza de sesión para mitigar tokens o credenciales persistentes en la máquina de desarrollo
  log_verbose "Cerrando sesión en el registro para resguardar credenciales..."
  "$CONTAINER_ENGINE" logout "$REGISTRY_URL" &>/dev/null || true
fi

# ============================================================
# Resumen Informativo Final de Operación Exitosa
# ============================================================
echo "========================================================="
if [ "$ONLY_BUILD" = false ] && [ "$ONLY_PUSH" = false ]; then
  echo "✅ Alternativa 1: Ciclo completo Build + Push finalizado."
elif [ "$ONLY_BUILD" = true ]; then
  echo "✅ Alternativa 2: Proceso exclusivo de Build completado."
elif [ "$ONLY_PUSH" = true ]; then
  echo "✅ Alternativa 3: Proceso exclusivo de Push completado."
fi
echo "📦 Motor Activo: $CONTAINER_ENGINE"
echo "📦 Imagen Destino: $FULL_IMAGE"
echo "========================================================="