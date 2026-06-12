
echo " Abrir en otra ventana de consola:"
echo " "
echo " kubectl logs -n flux-system deployment/image-reflector-controller --tail=20 --follow
"

kubectl apply -f ~/dk-notes/k3s/kustomization/image-repository.yaml
flux reconcile image repository dknotes-laravel -n flux-system
