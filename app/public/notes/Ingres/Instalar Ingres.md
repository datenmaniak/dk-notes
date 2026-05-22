Esta es la solución profesional que usarías en producción:

```bash
# Instalar Ingress Controller (Traefik o NGINX)
kubectl apply -f https://raw.githubusercontent.com/traefik/traefik/v3.0/docs/content/reference/dynamic-configuration/kubernetes-crd.yaml

# Crear un recurso Ingress para nginx
kubectl create ingress nginx-ingress --rule="nginx.local/*=nginx:80"
```