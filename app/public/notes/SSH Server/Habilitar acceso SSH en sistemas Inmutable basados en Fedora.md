<img src="https://r2cdn.perplexity.ai/pplx-full-logo-primary-dark%402x.png" style="height:64px;margin-right:32px"/>

# como habilito el acceso SSH en sistemas como bluefin, Silverblue o Fedora Cosmic.?

Para habilitar el acceso SSH en sistemas inmutables como Bluefin, Silverblue o Fedora COSMIC Atomic (basados en Fedora Atomic), el paquete OpenSSH suele venir preinstalado, por lo que solo necesitas activar y iniciar el servicio.[^1_1][^1_2][^1_3]

## Pasos principales

Ejecuta estos comandos en la terminal:

```
sudo systemctl enable --now sshd.service
```

Esto habilita el servicio para que inicie automáticamente al arrancar y lo pone en marcha inmediatamente.[^1_4][^1_2][^1_1]

## Configurar firewall

Abre el puerto SSH en firewalld (por defecto puerto 22):

```
sudo firewall-cmd --permanent --add-service=ssh
sudo firewall-cmd --reload
```

Esto permite conexiones entrantes SSH de forma segura.[^1_5][^1_6]

## Verificar y conectar

- Comprueba el estado: `systemctl status sshd`.
- Desde otra máquina: `ssh usuario@tu-ip` (usa `ip addr show` para ver tu IP).[^1_1][^1_5]

En Bluefin (Universal Blue), usuarios confirman que funciona directamente sin capas adicionales. Si usas GNOME, también puedes activarlo desde Configuración > Compartir > Acceso remoto.[^1_7][^1_2]
<span style="display:none">[^1_10][^1_11][^1_12][^1_13][^1_14][^1_15][^1_16][^1_17][^1_18][^1_19][^1_20][^1_21][^1_22][^1_23][^1_24][^1_25][^1_26][^1_27][^1_8][^1_9]</span>

<div align="center">⁂</div>

[^1_1]: https://noenieto.com/blog/2022/fedora-silverblue-sshd-server

[^1_2]: https://universal-blue.discourse.group/t/enabling-sshd-on-bluefin/8554

[^1_3]: https://www.fedoraproject.org/atomic-desktops/cosmic/

[^1_4]: https://kb.nomachine.com/AR03V01225

[^1_5]: https://docs.fedoraproject.org/es/fedora/f29/system-administrators-guide/infrastructure-services/OpenSSH/

[^1_6]: https://linuxcapable.com/how-to-install-and-enable-ssh-on-fedora-linux/

[^1_7]: https://discussion.fedoraproject.org/t/ssh-into-a-toolbox/2155

[^1_8]: https://universal-blue.discourse.group/t/how-to-replace-gnome-keyring-ssh-agent/8648

[^1_9]: https://docs.fedoraproject.org/es/fedora-coreos/tutorial-containers/

[^1_10]: https://discussion.fedoraproject.org/t/generating-ssh-key-in-silverblue/71283

[^1_11]: https://www.dropvps.com/blog/fedora-enable-ssh-root-login/

[^1_12]: https://ugeek.github.io/blog/post/2019-12-11-instalando-ssh-en-fedora-centos-o-redhat.html

[^1_13]: https://github.com/ublue-os/bluefin/blob/main/just/bluefin-apps.just

[^1_14]: https://discussion.fedoraproject.org/t/kickstart-instal-dependancies-with-rpm-ostree/92079

[^1_15]: https://www.adelton.com/docs/containers/fedora-atomic-sudo

[^1_16]: https://linuxconfig.org/how-to-install-start-and-connect-to-ssh-server-on-fedora-linux

[^1_17]: https://miabbott.github.io/2019/01/22/silverblue-day-1.html

[^1_18]: https://www.adelton.com/docs/containers/fedora-atomic-sssd-container

[^1_19]: https://www.youtube.com/watch?v=EBH-k_cuiYw

[^1_20]: https://universal-blue.discourse.group/t/activate-sshd-on-bluefin/3855

[^1_21]: https://copr.fedorainfracloud.org/coprs/ryanabx/cosmic-epoch/

[^1_22]: https://www.youtube.com/watch?v=_RgTs6axOV8

[^1_23]: https://www.tecmint.com/install-cosmic-desktop-fedora/

[^1_24]: https://fedoraproject.org/spins/cosmic/

[^1_25]: https://www.systutorials.com/how-to-enable-ssh-service-on-fedora-linux/

[^1_26]: https://www.youtube.com/watch?v=_MCbi4d0D5Q

[^1_27]: https://www.youtube.com/watch?v=z1YVaNLToFw

