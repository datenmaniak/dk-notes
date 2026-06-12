# Generar la Clave y el Certificado
#
# 1. Generar la clave privada del servidor
openssl genrsa -out registro.key 2048

# 2. Generar la solicitud de firma (CSR)
openssl req -new -key registro.key -out registro.csr -subj "/CN=local-reg.dk.lab"

# 3. Firmar el certificado usando el archivo .ext (Válido por 3 años)
openssl x509 -req -days 1095 -in registro.csr -signkey registro.key -out registro.crt -extfile registro.ext
