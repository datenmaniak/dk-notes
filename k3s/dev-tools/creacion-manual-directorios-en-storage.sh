# El initContainer no creó los directorios porque el volumen storage-persistence
# se monta después del initContainer. El initContainer solo ve el emptyDir (app-code),
# no el volumen persistente de NFS.

# Crear los directorios directamente en el volumen NFS
kubectl exec -n dknotes deployment/dknotes-web -c web-app -- mkdir -p /var/www/html/storage/framework/{cache,sessions,views,testing}

# Establecer permisos
kubectl exec -n dknotes deployment/dknotes-web -c web-app -- chown -R 33:33 /var/www/html/storage/framework
kubectl exec -n dknotes deployment/dknotes-web -c web-app -- chmod -R 775 /var/www/html/storage/framework

# Verificar
kubectl exec -n dknotes deployment/dknotes-web -c web-app -- ls -la /var/www/html/storage/framework/
