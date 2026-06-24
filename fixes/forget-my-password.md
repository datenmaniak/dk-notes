# Chequeo para comprobar la recuperacion de acceso


## Archivo .env

```plainttext
# 1. Asegurarse de que el controlador sea SMTP
MAIL_MAILER=smtp

# 2. Datos del servidor de Gmail
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587

# 3. Tus credenciales de Gmail
MAIL_USERNAME=datenmaniak@gmail.com
MAIL_PASSWORD="mypa sswo rhere"
MAIL_ENCRYPTION=tls

# 4. Dirección de envío (debe ser la misma que el usuario)
MAIL_FROM_ADDRESS=datenmaniak@gmail.com
MAIL_FROM_NAME="${APP_NAME}"
```
