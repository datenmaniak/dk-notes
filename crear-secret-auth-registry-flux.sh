kubectl create secret docker-registry registry-auth \
  --namespace=flux-system \
  --docker-server=local-reg.dk.lab:5000 \
  --docker-username=datenmaniak \
  --docker-password=REGISTRA
