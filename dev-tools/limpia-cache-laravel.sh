# 1. Limpiar toda la caché de Laravel
kubectl exec -n dknotes deployment/dknotes-web -c web-app -- php artisan config:clear
kubectl exec -n dknotes deployment/dknotes-web -c web-app -- php artisan cache:clear
kubectl exec -n dknotes deployment/dknotes-web -c web-app -- php artisan view:clear
kubectl exec -n dknotes deployment/dknotes-web -c web-app -- php artisan route:clear

# 2. Recachear con la nueva clave
kubectl exec -n dknotes deployment/dknotes-web -c web-app -- php artisan config:cache

# 3. Reiniciar el pod
kubectl delete pod -n dknotes -l app=dknotes

# 4. Probar
# kubectl port-forward -n dknotes svc/dknotes-service 8080:80