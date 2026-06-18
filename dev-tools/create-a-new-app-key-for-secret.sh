# No entras al pod. Generas desde tu terminal
NEW_KEY=$(openssl rand -base64 32)

echo '1) Nueva APP KEY:'
echo $NEW_KEY

echo '--- APP KEY para insertar en el manifiesto ---'

echo "1) Antes aplicaba asi en el secret.yaml: "
BAD_WAY=$(echo -n "$NEW_KEY" | base64)
echo $BAD_WAY

echo "2) Ahora  (corregido):"
# Corregir el gran error de no haber leido la documentacion antes
echo -n "base64:$NEW_KEY" | base64