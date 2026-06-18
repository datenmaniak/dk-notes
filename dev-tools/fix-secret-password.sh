kubectl create secret generic dknotes-secrets \
  --from-literal=username=dkuser \
  --from-literal=password=7shogun \
  -n dknotes --dry-run=client -o yaml | kubectl apply -f -
