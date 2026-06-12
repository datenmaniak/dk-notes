# /Actualizar Kubernetes y Flux
# Una vez que el registro esté usando el nuevo
# certificado, debemos darle el archivo
# registro.crt (que al ser autofirmado actúa como su propia CA) a Flux.

kubectl delete secret registry-ca -n flux-system

kubectl create secret generic registry-ca \
  --namespace=flux-system \
  --from-file=ca.crt=./registro.crt

# 3. Reiniciar el pod para destruir cualquier caché en memoria
kubectl rollout restart deployment/image-reflector-controller -n flux-system
