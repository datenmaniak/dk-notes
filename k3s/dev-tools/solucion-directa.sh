# 1. Eliminar deployment
kubectl delete deployment dknotes-web -n dknotes

# 2. Verificar que el secret existe y tiene app_key
kubectl get secret dknotes-secrets -n dknotes -o yaml | grep app_key

# 3. Crear deployment nuevamente
kubectl apply -f kustomization/deploy.yaml

# 4. Verificar que el pod tiene la APP_KEY correcta
kubectl exec -n dknotes deployment/dknotes-web -c web-app -- php artisan tinker --execute="echo config('app.key');"
