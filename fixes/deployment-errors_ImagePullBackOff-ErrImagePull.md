# Errores de deployment, aplicacion dknotes-laravel

## Error de inicializacion del Pod

- ImagePullBackOff
- ErrImagePull

```bash
❯ kubectl describe pod dknotes-web-7df48c8c49-j9zwj -n dknotes
```


```plaintext
Events:
  Type     Reason            Age                     From               Message
  ----     ------            ----                    ----               -------
  Warning  FailedScheduling  7m59s                   default-scheduler  0/3 nodes are available: pod has unbound immediate PersistentVolumeClaims. not found
  Normal   Scheduled         7m44s                   default-scheduler  Successfully assigned dknotes/dknotes-web-7df48c8c49-j9zwj to worker1
  Normal   Pulling           4m45s (x5 over 7m44s)   kubelet            spec.initContainers{copy-code}: Pulling image "local-reg.dk.lab:5000/dknotes-laravel:1.51"
  Warning  Failed            4m45s (x5 over 7m44s)   kubelet            spec.initContainers{copy-code}: Failed to pull image "local-reg.dk.lab:5000/dknotes-laravel:1.51": failed to pull and unpack image "local-reg.dk.lab:5000/dknotes-laravel:1.51": failed to resolve reference "local-reg.dk.lab:5000/dknotes-laravel:1.51": failed to do request: Head "https://local-reg.dk.lab:5000/v2/dknotes-laravel/manifests/1.51": tls: failed to verify certificate: x509: certificate signed by unknown authority
  Warning  Failed            4m45s (x5 over 7m44s)   kubelet            spec.initContainers{copy-code}: Error: ErrImagePull
  Normal   BackOff           2m35s (x21 over 7m44s)  kubelet            spec.initContainers{copy-code}: Back-off pulling image "local-reg.dk.lab:5000/dknotes-laravel:1.51"
  Warning  Failed            2m35s (x21 over 7m44s)  kubelet            spec.initContainers{copy-code}: Error: ImagePullBackOff

```

## Soluciones

### Alternativa A:

**Paso 1: Obtener el certificado de la CA**

Necesitas el archivo del certificado de la Autoridad de Certificación (o el certificado raíz autoafirmado) que genera la confianza para local-reg.dk.lab. Normalmente es un archivo con extensión .crt o .pem.

**Paso 2: Instalar la CA en cada nodo (Master y Workers)**

Debes ejecutar estos pasos en todos los nodos de tu clúster k3s, ya que cualquier nodo podría recibir la tarea de levantar el Pod dknotes-web y necesitará descargar la imagen.

Copia el archivo del certificado (llamémoslo mi-ca.crt) al directorio de certificados compartidos de Ubuntu:

```bash
sudo cp mi-ca.crt /usr/local/share/ca-certificates/mi-ca.crt
```
Nota: Es importante que la extensión sea estrictamente .crt para que Ubuntu lo reconozca.

Actualiza el almacén de certificados del sistema operativo:

Bash
sudo update-ca-certificates
Deberías ver una salida en la terminal que indique que se agregó 1 certificado (1 added).

Paso 3: Reiniciar el servicio de K3s
Para que el runtime de contenedores incorporado en K3s (containerd) se entere de que el sistema operativo ahora confía en esa entidad emisora, debes reiniciar el servicio.

En el nodo Master:

Bash
sudo systemctl restart k3s
En los nodos Workers:

Bash
sudo systemctl restart k3s-agent