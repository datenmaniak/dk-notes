#Una vez que Flux actualice la automatización dentro
#del clúster, dale un empujón al controlador de imágenes para que procese la nueva versión que subiste al registry de inmediato:

#flux reconcile image automation dknotes-laravel -n flux-system

#Destraba el controlador de automatización de imágenes ejecutando el comando de reconciliación
flux reconcile image update dknotes-laravel -n flux-system
