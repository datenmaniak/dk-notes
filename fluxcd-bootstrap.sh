flux bootstrap github \
  --owner=datenmaniak \
  --repository=dk-notes \
  --branch=laravel-k3s \
  --path=./k3s/kustomization \
  --components-extra=image-reflector-controller,image-automation-controller
#\
#  --force

# Forzar actualización de Flux
#flux reconcile source git flux-system

# Esperar activamente a que la kustomization se aplique con éxito
#echo "Esperando a que Flux aplique los cambios en el clúster..."
#flux reconcile kustomization flux-system --with-source
