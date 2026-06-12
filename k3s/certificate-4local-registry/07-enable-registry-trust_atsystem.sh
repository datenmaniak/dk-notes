# Configurar la confianza a nivel de Sistema
# Operativo (Opcional pero recomendado)
# Si quieres que herramientas del sistema
# (como curl o navegadores web) también confíen
# en la URL https://local-reg.dk.lab:5000, debes
# añadirlo al almacén de llaves de Fedora:

# 1. Copia el certificado a la carpeta de anclajes de confianza:
#
sudo cp /home/datenmaniak/dk-notes/k3s/certificate-4local-registry/registro.crt \
  /etc/pki/ca-trust/source/anchors/local-reg.dk.lab.crt

# 2. Actualiza el registro de confianza del sistema:

sudo update-ca-trust
