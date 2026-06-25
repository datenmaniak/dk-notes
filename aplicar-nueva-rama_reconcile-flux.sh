# La forma rápida (Por línea de comandos)
# Si quieres aplicar el cambio de inmediato directamente en el clúster
# sin modificar el YAML manualmente en Git, puedes usar la CLI de Flux
# para actualizar el recurso en caliente
#
NEW_BRANCH="design/fix"

flux reconcile source git flux-system --branch=${NEW_BRANCH}
