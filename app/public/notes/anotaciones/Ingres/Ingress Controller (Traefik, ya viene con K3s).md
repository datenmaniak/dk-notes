
## 🎯 Objetivo

Instalar y configurar **Ingress Controller** (Traefik, que ya viene con K3s) para exponer ArgoCD y tus aplicaciones de forma profesional.

### Paso 1: Verificar que Traefik está corriendo (viene con K3s)

####  Verificar pods de Traefik en kube-system

```bash
kubectl get pods -n kube-system | grep traefik
```
####  Ver servicio de Traefik
```bash

kubectl get svc -n kube-system traefik
```


### Paso 2: Exponer Traefik como NodePort (para acceso desde tu red local)

#### Editar el servicio de Traefik
```bash
kubectl patch svc traefik -n kube-system -p '{"spec": {"type": "NodePort"}}'
```

#### Ver el puerto asignado (ej: 3xxxx)
```bash
kubectl get svc -n kube-system traefik
```

### Paso 3: Crear Ingress para ArgoCD


#### Crear archivo ingress-argocd.yaml

```bash
cat > ~/ingress-argocd.yaml << 'EOF'
apiVersion: networking.k8s.io/v1
kind: Ingress
metadata:
  name: argocd
  namespace: argocd
  annotations:
    # Para Traefik
    traefik.ingress.kubernetes.io/router.entrypoints: web
    # Redirigir HTTP a HTTPS (opcional)
    ingress.kubernetes.io/ssl-redirect: "false"
spec:
  rules:
  - host: argocd.homelab.local
    http:
      paths:
      - path: /
        pathType: Prefix
        backend:
          service:
            name: argocd-server
            port:
              number: 80
EOF
```

#### Aplicar el Ingress

```
kubectl apply -f ~/ingress-argocd.yaml
```

### Paso 4: Configurar DNS local (en tu laptop)

#### Agregar entrada a /etc/hosts (necesita sudo)
```bash
echo "192.168.1.100 argocd.homelab.local" | sudo tee -a /etc/hosts
```

#### Verificar
```bash
ping argocd.homelab.local
```

### Paso 5: Probar acceso a ArgoCD

#### Obtener puerto de Traefik
```bash

NODEPORT=$(kubectl get svc -n kube-system traefik -o jsonpath='{.spec.ports[0].nodePort}')
```

#### Probar acceso
```bash
curl -v http://argocd.homelab.local:$NODEPORT
```

#### O desde el navegador:
```bash
http://argocd.homelab.local:3xxxx
```


### Paso 6: Crear Ingress para nginx-demo

#### Crear archivo ingress-nginx.yaml
```bash
cat > ~/ingress-nginx.yaml << 'EOF'
apiVersion: networking.k8s.io/v1
kind: Ingress
metadata:
  name: nginx-demo
  namespace: default
spec:
  rules:
  - host: nginx.homelab.local
    http:
      paths:
      - path: /
        pathType: Prefix
        backend:
          service:
            name: nginx-demo
            port:
              number: 80
EOF
```

#### Aplicar
```bash
kubectl apply -f ingress-nginx.yaml
```

#### Agregar a /etc/hosts  

La IP corresponde al k8s-master. 

```bash
echo "192.168.1.100 nginx.homelab.local" | sudo tee -a /etc/hosts
```

## Verificar los puertos de Traefik

```bash
kubectl get svc -n kube-system traefik
```

#### Resultado esperado (ejemplo):
```bash
# NAME      TYPE       CLUSTER-IP     PORT(S)                      AGE
# traefik   NodePort   10.43.xxx.xxx  80:30740/TCP,443:31245/TCP   5d
```




