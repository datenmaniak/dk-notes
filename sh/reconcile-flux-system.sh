#
#Forzar una resincronización (El "reinicio" de Flux)

flux reconcile image repository dknotes-laravel -n flux-system

flux reconcile image policy dknotes-laravel -n flux-system

flux reconcile source git flux-system

flux reconcile kustomizacion flux-system --with-source
