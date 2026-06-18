#!/usr/bin/env bash
# Sctip para Build + Push imagen 




## Version funcional (no parametrizadas)

**build_push.sh**

```bash
# Configuracion de variables fijas
TARGET_DIR="$HOME/dk-notes"
REGISTRY="local-reg.dk.lab:5000"
IMAGE_NAME="dknotes-laravel"

# Funcion para mostrar la ayuda del script
mostrar_ayuda() {
  echo "Uso: $0 <version> <ruta_del_dockerfile> <usuario_registro> <contrasena_registro>"
  echo ""
  echo "Parametros:"
  echo "  <version>              La version para el tag de la imagen (ej. 1.50)"
  echo "  <ruta_del_dockerfile>  Ruta relativa al Dockerfile (ej. laravel/Dockerfile-v1.43)"
  echo "  <usuario_registro>     Usuario para autenticarse en el registro local"
  echo "  <contrasena_registro>  Contrasena para autenticarse en el registro local"
  echo ""
  echo "Ejemplo:"
  echo "  $0 1.50 laravel/Dockerfile-v1.43 mi_usuario mi_secreto"
}

# 1. VALIDACION: Control de argumentos iniciales (Si no hay argumentos, muestra ayuda)
if [ $# -eq 0 ]; then
  mostrar_ayuda
  exit 0
fi

echo "Iniciando validaciones de entorno..."

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
REG_USER=$3
REG_PASS=$4

if [ -z "$VERSION" ] || [ -z "$DOCKERFILE" ] || [ -z "$REG_USER" ] || [ -z "$REG_PASS" ]; then
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

# Definicion del tag unico e identico para todo el proceso
IMAGE_TAG="$REGISTRY/$IMAGE_NAME:$VERSION"

echo "Todas las validaciones pasaron con exito."
echo "------------------------------------------------------------"
echo "Ejecutando construccion de la imagen: $IMAGE_TAG"
echo "------------------------------------------------------------"

# Ejecucion del Build
podman build -f "$DOCKERFILE" -t "$IMAGE_TAG" .

# Control de flujo Post-Build
if [ $? -ne 0 ]; then
  echo "Error: La construccion de la imagen fallo. Se aborta el proceso."
  exit 1
fi

echo "------------------------------------------------------------"
echo "Autenticando en el registro local..."
echo "------------------------------------------------------------"

# Autenticacion segura pasando la contrasena por stdin y omitiendo verificacion TLS
echo "$REG_PASS" | podman login "$REGISTRY" --username "$REG_USER" --password-stdin --tls-verify=false

# Control de flujo Post-Login: Si falla la autenticacion, NO se hace el push
if [ $? -ne 0 ]; then
  echo "Error: Autenticacion fallida en $REGISTRY. Se cancela el push por seguridad."
  exit 1
fi

echo "------------------------------------------------------------"
echo "Autenticacion exitosa. Iniciando push al registro local..."
echo "------------------------------------------------------------"

# Ejecucion del Push omitiendo verificacion TLS
podman push --tls-verify=false "$IMAGE_TAG"

# Validacion del resultado del push
if [ $? -eq 0 ]; then
  echo "------------------------------------------------------------"
  echo "Proceso finalizado correctamente. Imagen disponible para FluxCD."
  echo "------------------------------------------------------------"

  # Opcional: Cerrar sesion en el registro al terminar para no dejar credenciales activas
  podman logout "$REGISTRY" &>/dev/null
else
  echo "Error: No se pudo subir la imagen al registro local."
  exit 1
fi
```