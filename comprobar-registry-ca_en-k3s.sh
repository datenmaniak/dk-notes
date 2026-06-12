kubectl get secret registry-ca -n flux-system -o jsonpath='{.data.ca\.crt}' | base64 -d | openssl x509 -text -noout
