


```bash
# 1. Asegúrate de que tu clave privada está actualizada
# (la que ya tiene la nueva passphrase)
ls -l ~/.ssh/id_ed25519

# 2. Cifra de nuevo con GPG
gpg --armor --encrypt \
  --recipient "tu‑usuario@tu‑email.com" \
  ~/.ssh/id_ed25519

# Esto crea:
# ~/.ssh/id_ed25519.asc

```

```bash
# Cifra de nuevo
gpg --armor --encrypt \
  --recipient "tu‑usuario@tu‑email.com" \
  ~/.ssh/id_rsa

```
