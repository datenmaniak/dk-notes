kubectl create secret generic registry-ca \
  --namespace=flux-system \
  --from-file=ca.crt=/etc/containers/certs.d/local-reg.dk.lab:5000/ca.crt
