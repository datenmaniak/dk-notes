#!/bin/bash

# ============================================
# log-in_pod.sh - Acceso y listado de pods
# ============================================

set -euo pipefail

# Variables globales
NAMESPACE=""
POD=""
CONTAINER=""
MODO="listar" # "listar" o "exec"

# ============================================
# Funciones de ayuda
# ============================================

mostrar_ayuda() {
  cat <<EOF
Uso:
  $0 -n NAMESPACE POD [CONTENEDOR]
  $0 -n NAMESPACE -c CONTENEDOR POD

Opciones:
  -n, --namespace NAMESPACE    Namespace del pod (requerido)
  -c, --container CONTENEDOR   Contenedor para acceder (opcional)
  -h, --help                   Mostrar esta ayuda

Modos:
  Sin -c: Lista todos los contenedores del pod
  Con -c:  Accede al contenedor especificado

Ejemplos:
  $0 -n production mi-app                    # Listar contenedores
  $0 -n default mi-app -c nginx              # Entrar a nginx
  $0 mi-app -n staging -c sidecar            # Orden flexible
  $0 production mi-app                       # Formato antiguo (fallback)
  $0 production mi-app nginx                 # Formato antiguo con contenedor
EOF
}

mostrar_error() {
  echo "Error: $1" >&2
  echo ""
  mostrar_ayuda
  exit 1
}

verificar_dependencias() {
  if ! command -v kubectl &>/dev/null; then
    mostrar_error "kubectl no está instalado o no se encuentra en el PATH"
  fi

  if ! command -v jq &>/dev/null; then
    mostrar_error "jq no está instalado. Instálalo con: apt-get install jq (o equivalente)"
  fi
}

verificar_pod() {
  if ! kubectl get pod "$POD" -n "$NAMESPACE" &>/dev/null; then
    mostrar_error "Pod '$POD' no encontrado en namespace '$NAMESPACE'"
  fi
}

obtener_estado_contenedor() {
  local pod="$1"
  local namespace="$2"
  local container="$3"

  kubectl get pod "$pod" -n "$namespace" -o json |
    jq -r ".status.containerStatuses[] | select(.name==\"$container\") | .state | keys[0]"
}

listar_contenedores() {
  echo "Pod: $POD (namespace: $NAMESPACE)"
  echo "===================================="

  # Obtener datos del pod
  local pod_json
  pod_json=$(kubectl get pod "$POD" -n "$NAMESPACE" -o json)

  # Verificar que tiene contenedores
  local total_contenedores
  total_contenedores=$(echo "$pod_json" | jq -r '.spec.containers | length')

  if [[ "$total_contenedores" -eq 0 ]]; then
    echo "El pod '$POD' no tiene contenedores definidos"
    exit 0
  fi

  # Listar cada contenedor
  local contador=0
  while IFS= read -r container_name; do
    contador=$((contador + 1))

    # Obtener imagen
    local image
    image=$(echo "$pod_json" | jq -r ".spec.containers[] | select(.name==\"$container_name\") | .image")

    # Obtener estado
    local estado
    estado=$(obtener_estado_contenedor "$POD" "$NAMESPACE" "$container_name")

    # Si el estado es null o vacío, mostrar "Desconocido"
    if [[ -z "$estado" || "$estado" == "null" ]]; then
      estado="Desconocido"
    fi

    echo ""
    echo "Contenedor: $container_name"
    echo "  Imagen: $image"
    echo "  Estado: $estado"

  done < <(echo "$pod_json" | jq -r '.spec.containers[].name')

  echo ""
  echo "===================================="
  echo "Total: $contador contenedores"
  echo ""
  echo "Sugerencia: $0 -n $NAMESPACE $POD -c [contenedor]"
}

exec_contenedor() {
  echo "Accediendo al contenedor '$CONTAINER' del pod '$POD' en namespace '$NAMESPACE'..."
  echo "===================================="

  # Verificar que el contenedor existe
  local pod_json
  pod_json=$(kubectl get pod "$POD" -n "$NAMESPACE" -o json)

  local container_exists
  container_exists=$(echo "$pod_json" | jq -r ".spec.containers[] | select(.name==\"$CONTAINER\") | .name")

  if [[ -z "$container_exists" ]]; then
    echo "Error: Contenedor '$CONTAINER' no encontrado en el pod '$POD'"
    echo ""
    echo "Contenedores disponibles:"
    echo "$pod_json" | jq -r '.spec.containers[].name' | sed 's/^/  - /'
    exit 1
  fi

  # Intentar bash primero, si falla usar sh
  echo "Intentando bash... (si falla, usará sh)"
  if ! kubectl exec -it "$POD" -n "$NAMESPACE" -c "$CONTAINER" -- bash 2>/dev/null; then
    echo "bash no disponible, usando sh..."
    kubectl exec -it "$POD" -n "$NAMESPACE" -c "$CONTAINER" -- sh
  fi
}

# ============================================
# Parseo de parámetros
# ============================================

parsear_parametros() {
  # Si no hay parámetros, mostrar ayuda
  if [[ $# -eq 0 ]]; then
    mostrar_ayuda
    exit 0
  fi

  # Verificar si es formato antiguo (sin flags)
  if [[ $# -ge 2 ]] && [[ "$1" != "-"* ]] && [[ "$2" != "-"* ]]; then
    # Formato: NAMESPACE POD [CONTAINER]
    NAMESPACE="$1"
    POD="$2"
    if [[ $# -eq 3 ]]; then
      CONTAINER="$3"
      MODO="exec"
    fi
    return 0
  fi

  # Parseo con flags
  local args=()
  while [[ $# -gt 0 ]]; do
    case "$1" in
    -n | --namespace)
      if [[ -z "$2" || "$2" == "-"* ]]; then
        mostrar_error "Se requiere un valor para $1"
      fi
      NAMESPACE="$2"
      shift 2
      ;;
    -c | --container)
      if [[ -z "$2" || "$2" == "-"* ]]; then
        mostrar_error "Se requiere un valor para $1"
      fi
      CONTAINER="$2"
      MODO="exec"
      shift 2
      ;;
    -h | --help)
      mostrar_ayuda
      exit 0
      ;;
    -*)
      mostrar_error "Opción desconocida: $1"
      ;;
    *)
      # Argumento posicional (POD)
      args+=("$1")
      shift
      ;;
    esac
  done

  # El primer argumento posicional es el POD
  if [[ ${#args[@]} -gt 0 ]]; then
    POD="${args[0]}"
  fi

  # Validaciones
  if [[ -z "$NAMESPACE" ]]; then
    mostrar_error "Namespace es requerido (usar -n NAMESPACE)"
  fi

  if [[ -z "$POD" ]]; then
    mostrar_error "Pod es requerido"
  fi
}

# ============================================
# Función principal
# ============================================

main() {
  parsear_parametros "$@"
  verificar_dependencias
  verificar_pod

  case "$MODO" in
  listar)
    listar_contenedores
    ;;
  exec)
    exec_contenedor
    ;;
  *)
    mostrar_error "Modo no reconocido"
    ;;
  esac
}

# ============================================
# Ejecución
# ============================================

main "$@"
