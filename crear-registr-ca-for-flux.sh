#kubectl create secret generic registry-ca \
#  --namespace=flux-system \
#  --from-file=ca.crt=/etc/containers/certs.d/local-reg.dk.lab:5000/ca.crt

kubectl create secret docker-registry registry-auth \
  --namespace=flux-system \
  --docker-server=local-reg.dk.lab:5000 \
  --docker-username=datenmaniak \
  --docker-password=REGISTRA

# remove el Image Repository actual
kubectl delete imagerepository dknotes-laravel -n flux-system

kubectl apply -f k3s/kustomization/image-repository.yaml
