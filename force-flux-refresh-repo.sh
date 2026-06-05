# 1. Suspender la sincronización
flux suspend kustomization flux-system

# 2. Forzar a Flux a re-leer el repositorio
flux reconcile source git flux-system

# 3. Reanudar la sincronización
flux resume kustomization flux-system

# 4. Verificar nuevamente
flux build kustomization flux-system --dry-run
