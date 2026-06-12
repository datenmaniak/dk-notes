openssl req -x509 -nodes -days 1095 -newkey rsa:2048 \
  -keyout registro.key \
  -out registro.crt \
  -config registro.ext
