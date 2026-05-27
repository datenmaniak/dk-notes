kubectl get secret dknotes-secrets -n dknotes -o jsonpath='{.data.password}' | base64 --decode
