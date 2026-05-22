<img src="https://r2cdn.perplexity.ai/pplx-full-logo-primary-dark%402x.png" style="height:64px;margin-right:32px"/>

# como activo ssh server en mi estacion co bluefin

Para activar el servidor SSH (sshd) en tu estación Bluefin (basada en Fedora Atomic/Universal Blue), usa el comando principal recomendado por la comunidad. Este paso habilita y inicia el servicio de forma persistente, ya que openssh-server suele estar preinstalado en imágenes como Bluefin.[^1_1]

## Pasos principales

- Ejecuta: `sudo systemctl enable --now sshd`
- Verifica el estado: `systemctl status sshd` (debe mostrar "active (running)").[^1_2][^1_1]


## Configuración de firewall

Permite el tráfico SSH en el firewall (puerto 22/TCP):

- `sudo firewall-cmd --add-service=ssh --permanent`
- `sudo firewall-cmd --reload`[^1_2]


## Solución común a errores

Si ves "no hostkeys available", reinicia el servicio: `sudo systemctl restart sshd`. Las claves se generan automáticamente en sistemas Fedora como Bluefin.[^1_3]

## Uso en contenedores (opcional)

Dado tu uso de Distrobox/Podman, puedes activar SSH dentro de un contenedor Fedora/Ubuntu: `distrobox enter --additional-flags "--service-ssh" fedora -- sudo systemctl enable --now sshd`.[^1_4]
<span style="display:none">[^1_10][^1_11][^1_12][^1_13][^1_14][^1_15][^1_16][^1_17][^1_18][^1_19][^1_5][^1_6][^1_7][^1_8][^1_9]</span>

<div align="center">⁂</div>

[^1_1]: https://universal-blue.discourse.group/t/enabling-sshd-on-bluefin/8554

[^1_2]: https://www.server-world.info/en/note?os=Fedora_43\&p=ssh\&f=1

[^1_3]: https://universal-blue.discourse.group/t/activate-sshd-on-bluefin/3855

[^1_4]: https://bluevps.com/blog/how-to-install-and-enable-ssh-on-ubuntu

[^1_5]: https://www.youtube.com/watch?v=z1YVaNLToFw

[^1_6]: https://rpmfusion.org

[^1_7]: https://wiki.deepin.org/en/System_Management/Service_Management/SSH_service

[^1_8]: https://documentation.ubuntu.com/server/how-to/security/openssh-server/

[^1_9]: https://github.com/aguslr/bluefusion

[^1_10]: https://www.youtube.com/watch?v=UBGLqN1JcPc

[^1_11]: https://docs.projectbluefin.io/administration/

[^1_12]: https://discussion.fedoraproject.org/t/kickstart-instal-dependancies-with-rpm-ostree/92079

[^1_13]: https://docs.projectbluefin.io/installation/

[^1_14]: https://forums.ubports.com/topic/8631/howto-autostart-ssh

[^1_15]: https://joshrnoll.com/bluefin-linux-a-just-works-distro-for-devs/

[^1_16]: https://universal-blue.discourse.group/t/some-lessons-learned-on-installing-bluefin-for-devcontainers-docker-development/1019

[^1_17]: https://docs.projectbluefin.io/introduction/

[^1_18]: https://forums.opensuse.org/t/cant-start-sshd-service-with-systemctl/105122

[^1_19]: https://github.com/orgs/ublue-os/packages/container/bluefin/646723082

