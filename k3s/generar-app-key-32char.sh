# 1. Generar clave de 32 bytes
RAW_KEY=$(openssl rand -base64 32)

# 2. Agregar prefijo base64:
FULL_KEY="base64:${RAW_KEY}"

# 3. Codificar TODO para el secret
FULL_KEY_B64=$(echo -n "$FULL_KEY" | base64)

echo "Valor para secret.yaml: $FULL_KEY_B64"
