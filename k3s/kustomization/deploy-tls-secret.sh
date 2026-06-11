kubectl create secret tls dknotes-tls-secret \
    --cert=dknotes.dk.lab.crt \
    --key=dknotes.dk.lab.key \
    -n dknotes
