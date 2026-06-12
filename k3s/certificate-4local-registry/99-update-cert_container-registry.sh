# 1. Borras el viejo
kubectl delete secret registry-tls-secret -n container-registry

# Crear el secret con los certificados TLS
kubectl create secret tls registry-tls-secret \
  --cert=./registro.crt \
  --key=./registro.key \
  -n container-registry
