echo "1. Verificar que realmente estás usando la nueva imagen"
kubectl get deployment dknotes-web -n dknotes -o jsonpath="{.spec.template.spec.containers[?(@.name=='web-app')].image}"


echo "2. Verificar si el .env viene de un volumen o configMap"
kubectl exec -n dknotes deployment/dknotes-web -c web-app -- mount | grep env
kubectl exec -n dknotes deployment/dknotes-web -c web-app -- ls -la /var/www/html/.env

echo "3. Verifica la imagen real y revisar su contenido "
podman run --rm -it registro.local:5000/dknotes-laravel:1.41 ls -la /var/www/html/.env


