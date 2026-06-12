#  

# Autenticacion + CA para flux

## Secret auth 
```bash
kubectl create secret docker-registry registry-auth \
  --namespace=flux-system \
  --docker-server=local-reg.dk.lab:5000 \
  --docker-username=datenmaniak \
  --docker-password=REGISTRA
```

## registry-ca
```bash
kubectl create secret generic registry-ca \
  --namespace=flux-system \
  --from-file=ca.crt=/etc/containers/certs.d/local-reg.dk.lab:5000/ca.crt
```


## ImageRepository
```yaml
#k3s/kustomization/image-repository.yaml 
apiVersion: image.toolkit.fluxcd.io/v1beta2
#apiVersion: image.toolkit.fluxcd.io/v1
kind: ImageRepository
metadata:
  name: dknotes-laravel
  namespace: flux-system
spec:
  image: local-reg.dk.lab:5000/dknotes-laravel
  interval: 5m
  insecure: true
  secretRef:
    name: registry-auth
  certSecretRef:
    name: registry-ca
```





