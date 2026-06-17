# curl -v --cacert /ruta/a/tu/registro.crt \

# ruta  1
#curl -v -k /home/datenmaniak/dk-notes/k3s/certificate-4local-registry/registro.crt

# Ruta 2
curl -v -k /etc/containers/certs.d/local-reg.dk.lab:5000/ca.crt \
  -u "datenmaniak:REGISTRA" \
  https://local-reg.dk.lab:5000/v2/_catalog
