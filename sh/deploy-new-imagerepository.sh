# Comprobación y Despliegue Limpio
# Para asegurarnos de que el controlador de Flux
# no use ninguna configuración antigua que haya quedado en caché, ejecuta estos comandos en tu terminal:

# 1. Borramos el recurso actual para limpiar el estado
kubectl delete imagerepository dknotes-laravel -n flux-system

# 2. Aplicamos el nuevo manifiesto sin la referencia al certificado
kubectl apply -f k3s/kustomization/image-repository.yaml

# 3. Forzamos a Flux a escanear el registro inmediatamente
flux reconcile image repository dknotes-laravel -n flux-system
