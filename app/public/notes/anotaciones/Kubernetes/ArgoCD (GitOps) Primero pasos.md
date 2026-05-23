
## 🚀 Siguiente paso: Tarea 10 - ArgoCD (GitOps)

### ArgoCD te permitirá:

- Gestionar el clúster desde Git

- Sincronización automática de aplicaciones

- Rollbacks con un clic

- Interfaz web para visualizar el estado

```bash
# Instalación rápida
kubectl create namespace argocd
```

```
kubectl apply -n argocd -f https://raw.githubusercontent.com/argoproj/argo-cd/stable/manifests/install.yaml
```

Si surgen errores, como:
```
networkpolicy.networking.k8s.io/argocd-dex-server-network-policy unchanged
networkpolicy.networking.k8s.io/argocd-notifications-controller-network-policy unchanged
networkpolicy.networking.k8s.io/argocd-redis-network-policy unchanged
networkpolicy.networking.k8s.io/argocd-repo-server-network-policy configured
networkpolicy.networking.k8s.io/argocd-server-network-policy unchanged
The CustomResourceDefinition "applicationsets.argoproj.io" is invalid: metadata.annotations: Too long: may not be more than 262144 bytes
```

## 🔍 Diagnóstico del error

El error `Too long: may not be more than 262144 bytes` significa que el CRD tiene anotaciones que exceden el límite de etcd de Kubernetes. Esto puede pasar por:

|Causa|Solución|
|---|---|
|Versión de K3s muy reciente (1.34)|Usar versión específica de ArgoCD|
|Recurso limitado en etcd|Instalar versión más ligera (core-install)|
|Problema de red durante la descarga|Reintentar con Helm|

## 🔧 Soluciones posibles

## Opción 1: Limpiar e instalar versión específica (recomendado)

#### Eliminar namespace si existe
```
kubectl delete namespace argocd --ignore-not-found=true
```
#### Esperar a que se elimine completamente

```
kubectl wait --for=delete namespace/argocd --timeout=60s 2>/dev/null || true
```

#### Instalar versión específica y estable (v2.12.6)
```
kubectl create namespace argocd
```

```
kubectl apply -n argocd -f https://raw.githubusercontent.com/argoproj/argo-cd/v2.12.6/manifests/install.yaml
```

### Opción 2: Instalar con Helm (más control)

#### Agregar repositorio de Helm
```
helm repo add argo https://argoproj.github.io/argo-helm
helm repo update
```
#### Crear namespace
```
kubectl create namespace argocd
```
#### Instalar ArgoCD con Helm
```
helm install argocd argo/argo-cd -n argocd \
  --set configs.params."server\.insecure"=true \
  --set version=v2.12.6
```

### Opción 3: Instalar solo los componentes esenciales
#### Instalar solo el core (sin UI ni Dex)
```
kubectl create namespace argocd
```

```
kubectl apply -n argocd -f 
```

```
https://raw.githubusercontent.com/argoproj/argo-cd/stable/manifests/core-install.yaml
```


## ✅ Verificar después de instalar

#### Ver pods en argocd
```
kubectl get pods -n argocd
```
#### Ver CRDs instalados
```
kubectl get crd | grep argoproj
```

#### Acceder a la UI (port-forward)
```bash 
kubectl port-forward -n argocd svc/argocd-server 8080:443 &
```

#### Obtener contraseña
```bash 
kubectl -n argocd get secret argocd-initial-admin-secret -o jsonpath="{.data.password}" | base64 -d
```

#### Contrasena asignada (Argo):

```
$4rg0-git
```

### Gitlab Token:

