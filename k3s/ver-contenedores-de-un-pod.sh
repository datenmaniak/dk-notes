kubectl get pod dknotes-web-594f64799b-qk6ld -n dknotes -o jsonpath='{.spec.containers[*].name}' 
