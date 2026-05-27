# Renombrar .env dentro del pod para que Laravel no lo use
kubectl exec -n dknotes deployment/dknotes-web -c web-app -- mv /var/www/html/.env /var/www/html/.env.bak

# Reiniciar
kubectl delete pod -n dknotes -l app=dknotes
