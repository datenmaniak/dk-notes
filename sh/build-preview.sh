#!/usr/bin/env bash

# Configuracion de variables fijas
TARGET_DIR="$HOME/dk-notes"
REGISTRY="local-reg.dk.lab:5000"
IMAGE_NAME="dknotes-laravel"

# Funcion para mostrar la ayuda del script
mostrar_ayuda() {
  echo "Uso: $0 <version> <ruta_del_dockerfile>"
  echo ""
  echo "Parametros:"
  echo "  <version>              La version para el tag de la imagen (ej. 1.50)"
  echo "  <ruta_del_dockerfile>  Ruta relativa al Dockerfile (ej. laravel/Dockerfile-v1.43)"
  echo ""
  echo "Ejemplo:"
  echo "  $0 1.50 laravel/Dockerfile-v1.43"
}

# 1. VALIDACION: Control de argumentos iniciales (Si no hay argumentos, muestra ayuda)
if [ $# -eq 0 ]; then
  mostrar_ayuda
  exit 0
fi

echo "[Beta] Iniciando validaciones de entorno..."

# 2. VALIDACION: Ubicacion actual
CURRENT_DIR=$(pwd)
if [ "$CURRENT_DIR" != "$TARGET_DIR" ]; then
  echo "Error: Este script debe ejecutarse desde: $TARGET_DIR"
  echo "Ubicacion actual: $CURRENT_DIR"
  exit 1
fi

# 3. VALIDACION: Asignacion y chequeo de parametros obligatorios
VERSION=$1
DOCKERFILE=$2

if [ -z "$VERSION" ] || [ -z "$DOCKERFILE" ]; then
  echo "Error: Faltan parametros obligatorios."
  echo ""
  mostrar_ayuda
  exit 1
fi

# 4. VALIDACION: Existencia del Dockerfile proporcionado
if [ ! -f "$DOCKERFILE" ]; then
  echo "Error: No se encuentra el archivo $DOCKERFILE"
  echo "Asegurate de que la ruta sea correcta respecto a la raiz del proyecto."
  exit 1
fi

# 5. VALIDACION: Existencia de Podman en el sistema
if ! command -v podman &>/dev/null; then
  echo "Error: 'podman' no esta instalado o no se encuentra en el PATH."
  exit 1
fi

# -----------------------------------------------------------------
# Salida de simulacion (Modo Beta)
# -----------------------------------------------------------------
echo ""
echo "Todas las validaciones pasaron con exito."
echo "------------------------------------------------------------"
echo "Simulacion de los comandos generados para la version $VERSION:"
echo "------------------------------------------------------------"

# El uso estricto de la variable $VERSION garantiza que el tag sea idéntico en ambos pasos
IMAGE_TAG="$REGISTRY/$IMAGE_NAME:$VERSION"

# Instrucciones finales simuladas
echo "podman build -f $DOCKERFILE -t $IMAGE_TAG ."
echo "podman push $IMAGE_TAG"

echo "------------------------------------------------------------"
echo "[Beta] Los comandos no han sido ejecutados."
echo ""
