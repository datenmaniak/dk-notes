kubectl exec -n dknotes deployment/dknotes-web -c web-app -- php artisan tinker --execute="echo strlen(config('app.key'));"


 kubectl get secret dknotes-secrets -n dknotes -o jsonpath="{.data.app_key}" | base64 -d && echo 


 kubectl exec -n dknotes deployment/dknotes-web -c web-app -- cat /var/www/html/.env 2>/dev/null | grep APP_KEY

 kubectl exec -n dknotes deployment/dknotes-web -c web-app -- env | grep -i key

 
