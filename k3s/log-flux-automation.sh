# Terminal 2 (Monitorear los logs de automatización de Flux):
# Ejecuta esto para ver el momento exacto en que Flux
# decida escribir en tu repositorio de Git:

kubectl logs -n flux-system deployment/image-automation-controller --tail=10 --follow
