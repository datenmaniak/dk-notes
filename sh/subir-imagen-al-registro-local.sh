# 2. Etiquetar para tu registro
echo "

podman tag dknotes-laravel:1.41 registro.local:5000/dknotes-laravel:1.41

"

# 3. Subir al registro
echo "
podman push registro.local:5000/dknotes-laravel:1.41
.
"
