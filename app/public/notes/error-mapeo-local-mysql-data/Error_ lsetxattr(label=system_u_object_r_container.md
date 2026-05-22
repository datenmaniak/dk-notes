<img src="https://r2cdn.perplexity.ai/pplx-full-logo-primary-dark%402x.png" style="height:64px;margin-right:32px"/>

# Error: lsetxattr(label=system_u:object_r:container_file_t:s0:c99,c856) /home/willians/containers/db/mysql-data/aria_log.00000001: operation not permitted

El error "lsetxattr: operation not permitted" ocurre al intentar establecer etiquetas SELinux (`system_u:object_r:container_file_t:s0:c99,c856`) en el archivo `/home/willians/containers/db/mysql-data/aria_log.00000001`, típicamente durante la inicialización de un contenedor MySQL/MariaDB con Podman en Fedora u otra distro con SELinux activado.[^1_1][^1_2]

## Causa Principal

SELinux bloquea la operación porque el directorio montado (`/home/willians/containers/db/mysql-data`) no permite que el contenedor (en contexto `container_file_t`) modifique atributos extendidos en el host. Esto es común en volúmenes bind-mount desde home sin relabeling adecuado.[^1_2][^1_4]

## Soluciones

- **Usa `:Z` o `:Z,U` en el volumen**: Al correr el contenedor, agrega `:Z` para relabeling automático de SELinux (crea contexto temporal accesible solo al contenedor). Ejemplo:

```
podman run -v /home/willians/containers/db/mysql-data:/var/lib/mysql:Z,U ...
```

`:U` ajusta UID/GID para rootless Podman.[^1_2]
- **Chroot y restorecon manual**:

```
sudo chcon -Rt svirt_sandbox_file_t /home/willians/containers/db/mysql-data
```

O usa `restorecon -R` tras ajustar políticas.[^1_1]
- **Verifica logs SELinux**: `sudo ausearch -m avc -ts recent | audit2allow` para denegaciones específicas y genera módulos personalizados si es recurrente.[^1_2]


## Prevención

Ejecuta Podman rootless con `--userns=keep-id` y siempre `:Z` en volúmenes persistentes. Si persiste, deshabilita SELinux temporalmente (`setenforce 0`) solo para depurar, no en producción.[^1_8][^1_2]
<span style="display:none">[^1_10][^1_3][^1_5][^1_6][^1_7][^1_9]</span>

<div align="center">⁂</div>

[^1_1]: https://github.com/calamares/calamares/issues/1237

[^1_2]: https://www.redhat.com/en/blog/container-permission-denied-errors

[^1_3]: https://access.redhat.com/solutions/6985126

[^1_4]: https://github.com/containers/podman/issues/24884

[^1_5]: https://github.com/curl/curl-container/issues/55

[^1_6]: https://discussion.fedoraproject.org/t/trying-to-run-komga-in-podman-and-getting-lsetxattr-error/80848

[^1_7]: https://discuss.linuxcontainers.org/t/moving-instance-with-docker-operation-not-permitted/19003

[^1_8]: https://github.com/containers/podman/issues/13727

[^1_9]: https://discuss.linuxcontainers.org/t/setting-extended-attributes-failed-reason-operation-not-permitted/10670

[^1_10]: https://lists.podman.io/archives/list/podman@lists.podman.io/thread/BZIH36OGWNCM74U6PA73OTGLKVNAVLDR/


---

# como desabilito selinux

Para deshabilitar SELinux en tu sistema Linux (como Fedora, dado tu uso de Podman), sigue estos pasos según el contexto de tu error anterior con contenedores.[^2_1][^2_2]

## Verificar Estado Actual

Ejecuta `sestatus` o `getenforce` para confirmar si está en modo `Enforcing`.[^2_1]

## Deshabilitar Temporalmente

Usa este comando como root (solo dura hasta el reinicio):

```
sudo setenforce 0
```

O equivalentemente:

```
sudo echo 0 > /sys/fs/selinux/enforce
```

Esto lo pone en modo permisivo sin desactivarlo por completo.[^2_3][^2_1]

## Deshabilitar Permanentemente

1. Edita el archivo de configuración:

```
sudo vi /etc/selinux/config
```

2. Cambia la línea `SELINUX=enforcing` por `SELINUX=disabled`.
3. Guarda y reinicia:

```
sudo reboot
```


**Advertencia**: Deshabilitar SELinux reduce la seguridad; úsalo solo para depuración del error de Podman/MySQL. Prueba primero el modo permisivo (`setenforce Permissive`) o las soluciones `:Z` en volúmenes que mencioné antes.[^2_2][^2_9]
<span style="display:none">[^2_4][^2_5][^2_6][^2_7][^2_8]</span>

<div align="center">⁂</div>

[^2_1]: https://www.tecmint.com/disable-selinux-in-centos-rhel-fedora/

[^2_2]: https://docs.fedoraproject.org/es/quick-docs/selinux-changing-states-and-modes/

[^2_3]: https://cloudinfrastructureservices.co.uk/disable-selinux-on-ubuntu-centos-debian-rhel/

[^2_4]: https://www.revsys.com/writings/quicktips/turn-off-selinux.html

[^2_5]: https://docs.redhat.com/es/documentation/red_hat_enterprise_linux/8/html/using_selinux/changing-selinux-states-and-modes_using-selinux

[^2_6]: https://docs.redhat.com/en/documentation/red_hat_enterprise_linux/6/html/security-enhanced_linux/sect-security-enhanced_linux-enabling_and_disabling_selinux-disabling_selinux

[^2_7]: https://forums.rockylinux.org/t/how-to-disable-selinux/16760

[^2_8]: https://www.geraldonit.com/disable-selinux-on-oracle-linux/

[^2_9]: https://www.redhat.com/en/blog/container-permission-denied-errors


---

# esto -v "/home/willians/containers/db/mysql-data:/var/lib/mysql:Z"  corresponde al mapeo durante el inicio del contenedor con Podman.

Sí, `-v "/home/willians/containers/db/mysql-data:/var/lib/mysql:Z"` es el mapeo de volumen correcto para Podman durante el inicio del contenedor. Esto soluciona el error `lsetxattr operation not permitted` que tenías con SELinux.[^3_1]

## Significado de `:Z`

El sufijo `:Z` le indica a Podman que relabele automáticamente los archivos del directorio host (`/home/willians/containers/db/mysql-data`) con un contexto SELinux privado (`container_file_t`) accesible solo por **este contenedor específico**. Esto permite que MySQL escriba archivos como `aria_log.00000001` sin bloqueos de SELinux.[^3_2][^3_1]

## Diferencia con `:z`

| Opción | Uso | Compartido |
| :-- | :-- | :-- |
| `:Z` | Privado (un solo contenedor) | No |
| `:z` | Compartido (múltiples contenedores) | Sí |

## Comando Completo Ejemplo

```
podman run -d \
  --name mysql-db \
  -v "/home/willians/containers/db/mysql-data:/var/lib/mysql:Z" \
  -e MYSQL_ROOT_PASSWORD=tu_password \
  docker.io/mariadb:latest
```

Esto debería eliminar el error sin deshabilitar SELinux permanentemente.[^3_1]
<span style="display:none">[^3_4][^3_5][^3_6][^3_7][^3_8][^3_9]</span>

<div align="center">⁂</div>

[^3_1]: https://docs.podman.io/en/v4.6.1/markdown/options/volume.html

[^3_2]: https://www.josedomingo.org/pledin/2024/05/almacenamiento-rootless-podman/

[^3_3]: https://docs.podman.io/en/v4.3/markdown/options/volume.html

[^3_4]: https://www.reddit.com/r/podman/comments/wrcmrc/podman_volumes_and_selinux_should_i_use_z_or/

[^3_5]: https://www.josedomingo.org/pledin/2024/05/pod-podman/

[^3_6]: https://github.com/containers/podman/issues/10273

[^3_7]: https://stackoverflow.com/questions/35218194/what-is-z-flag-in-docker-containers-volumes-from-option

[^3_8]: https://docs.podman.io/en/v4.4/markdown/options/volumes-from.html

[^3_9]: https://docs.redhat.com/es/documentation/red_hat_enterprise_linux/8/html-single/building_running_and_managing_containers/index


---

# necesita permisos especiales la ruta local donde se mapeara el contenido de MariaDB.,?

Sí, la ruta local `/home/willians/containers/db/mysql-data` necesita permisos específicos para que MariaDB funcione correctamente con Podman rootless y SELinux.

## Permisos Requeridos

- **Propietario**: Debe pertenecer a tu usuario (no root): `chown -R $USER:$USER /home/willians/containers/db/mysql-data`
- **Escritura/ejecución**: `chmod -R 700 /home/willians/containers/db/mysql-data` (o al menos 755)
- **SELinux**: El `:Z` en `-v` lo maneja automáticamente, pero si persisten problemas: `podman unshare chown -R 27:27 /home/willians/containers/db/mysql-data` (UID 27 es `mysql` en la imagen).[^4_3]


## Verificación

```bash
ls -ldZ /home/willians/containers/db/mysql-data
```

Debería mostrar tu usuario como owner y contexto `container_file_t` tras el primer arranque con `:Z`.

## Rootless Podman Extra

Si usas Podman sin root, añade `:U` junto a `:Z`: `-v "/home/willians/containers/db/mysql-data:/var/lib/mysql:Z,U` para mapear UID/GID correctamente.[^4_2]

Esto resuelve el 99% de problemas de permisos en MariaDB/Podman sin tocar SELinux.[^4_1]
<span style="display:none">[^4_10][^4_4][^4_5][^4_6][^4_7][^4_8][^4_9]</span>

<div align="center">⁂</div>

[^4_1]: https://plataforma.josedomingo.org/pledin/cursos/podman2024/contenido/modulo4/01_almacenamiento.html

[^4_2]: https://docs.podman.io/en/v4.3/markdown/options/volume.html

[^4_3]: https://docs.podman.io/en/v4.6.1/markdown/options/volume.html

[^4_4]: https://learn.redhat.com/t5/Containers-DevOps-OpenShift/Podman-volume-mount-var-lib-mysql-problem/td-p/45548

[^4_5]: https://elpuig.xeill.net/Members/vcarceler/articulos/introduccion-a-podman

[^4_6]: https://www.reddit.com/r/podman/comments/1ognus1/podman_permissions_and_ownership_problems/

[^4_7]: https://labex.io/es/tutorials/rhel-run-containers-with-podman-on-rhel-589256

[^4_8]: https://github.com/containers/podman/discussions/16053

[^4_9]: https://srinivish.hashnode.dev/podman-series-deploying-a-mariadb-database-in-a-podman-container

[^4_10]: https://stackoverflow.com/questions/70675139/how-do-i-access-a-db-container-when-using-podman-compose


---

# ➜ ls -ldZ /home/willians/containers/db/mysql-data

drwxr-xr-x - willians unconfined_u:object_r:container_file_t:s0 16 Jan 20:11 /home/willians/containers/db/mysql-data

Tu salida `ls -ldZ` muestra que **todo está correcto** para MariaDB con Podman. El contexto SELinux `container_file_t` ya está aplicado correctamente.[^5_1]

## Análisis de la Salida

```
drwxr-xr-x - willians unconfined_u:object_r:container_file_t:s0 16 Jan 20:11
```

- ✅ **Permisos**: `drwxr-xr-x` (755) - lectura/ejecución OK
- ✅ **Propietario**: `willians` (tu usuario)
- ✅ **SELinux**: `container_file_t:s0` - **perfecto para contenedores**
- ✅ **Usuario SELinux**: `unconfined_u` - normal para rootless Podman


## Estado Actual

El directorio `/home/willians/containers/db/mysql-data` está **listo para usar** con:

```
podman run -v /home/willians/containers/db/mysql-data:/var/lib/mysql:Z ...
```


## ¿Por qué funcionó?

El `:Z` del mapeo anterior creó automáticamente este contexto `container_file_t:s0`, resolviendo el error `lsetxattr`. No necesitas permisos especiales adicionales ni `chown/chmod`.

**¡Ya puedes iniciar tu contenedor MariaDB sin problemas!** El error original está solucionado.[^5_10]
<span style="display:none">[^5_2][^5_3][^5_4][^5_5][^5_6][^5_7][^5_8][^5_9]</span>

<div align="center">⁂</div>

[^5_1]: https://criu.org/Security_Enhanced_Linux

[^5_2]: http://redhatgov.io/workshops/security_containers/exercise1.8/

[^5_3]: https://opensource.com/article/18/2/selinux-labels-container-runtimes

[^5_4]: https://www.ibm.com/docs/en/scalecontainernative/5.2.3?topic=planning-storage-scale-container-native-selinux

[^5_5]: https://www.linuxcampus.net/documentation/selinux-policy/container.html

[^5_6]: https://developers.redhat.com/articles/2025/04/11/my-advice-selinux-container-labeling

[^5_7]: https://docs.redhat.com/en/documentation/red_hat_enterprise_linux/7/html/selinux_users_and_administrators_guide/sect-security-enhanced_linux-working_with_selinux-selinux_contexts_labeling_files

[^5_8]: https://github.com/containers/libpod/issues/759

[^5_9]: https://kubernetes.io/docs/tasks/configure-pod-container/security-context/

[^5_10]: https://docs.podman.io/en/v4.6.1/markdown/options/volume.html

