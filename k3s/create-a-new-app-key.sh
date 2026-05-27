# No entras al pod. Generas desde tu terminal
NEW_KEY=$(openssl rand -base64 32)

echo '> Nueva APP KEY'
echo $NEW_KEY

echo '> APP KEY para insertar en el manifiesto'
# Tu clave actual (texto plano)
echo -n "$NEW_KEY" | base64
