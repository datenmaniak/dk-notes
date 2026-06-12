# Genera un playbook 

## Objetivo

- Actualizar en el cluster el certificado del registro privado


## Que ajuste se requiere:

```text
ca_file: /etc/rancher/k3s/local-reg-ca.crt # <--- Ruta al nuevo registro.crt
```

## Ubicacion del nuevo certificado:

```text
/home/datenmaniak/dk-notes/k3s/certificate-4local-registry/registro.crt
```

## Ubicacion del inventory.yml

```text
  ~/homelab-infra_ansible/inventory.yml
```



