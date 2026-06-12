# Configurar la confianza para Podman (A nivel de aplicación)
# Para que Podman confíe en el registro sin que proteste
# por el TLS, debes colocar el nuevo certificado en la
# ruta estructurada de contenedores:
#
sudo mv /etc/containers/certs.d/local-reg.dk.lab:5000/ca.crt \
  /etc/containers/certs.d/local-reg.dk.lab:5000/ca.crt.old

sudo cp /home/datenmaniak/dk-notes/k3s/certificate-4local-registry/registro.crt \
  /etc/containers/certs.d/local-reg.dk.lab:5000/ca.crt
