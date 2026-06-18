# Generar nueva clave
NEW_KEY=$(openssl rand -base64 32)
echo "Tu nueva clave: $NEW_KEY"

# Codificarla para el secret
NEW_KEY_B64=$(echo -n "$NEW_KEY" | base64)

## kubectl patch secret dknotes-secrets -n dknotes --type='json' -p="[{'op': 'replace', 'path': '/data/app_key', 'value':'$NEW_KEY_B64'}]"
