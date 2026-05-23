# Dockerfile template - Primero ensayo y modelo

```yaml
FROM debian:bookworm-slim
ENV DEBIAN_FRONTEND=noninteractive
  

# Configurar umask temprano para TODOS los archivos/directorios futuros (0022 = 755 dirs, 644 files)

RUN echo 'umask 0022' > /etc/profile.d/umask.sh && \
echo 'umask 0022' >> /etc/bash.bashrc && \
umask 0022

# Crear usuario ANTES de instalar paquetes (evita root-owned files)

RUN groupadd --gid 1000 willians && \
useradd --uid 1000 --gid willians --shell /bin/bash --create-home willians

# Instalar paquetes como root (necesario para algunos)

RUN apt-get update && \
apt-get install -y procps sudo git curl wget bc \
php php-mbstring php-curl php-zip curl vim nano \
iproute2 iputils-ping p7zip-full php-mysql zsh \
php-cli php-dom mariadb-client && \
apt-get clean && \
rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*


# SUDO Opcional DESPUÉS de paquetes (configurado manualmente)

RUN apt-get update && apt-get install -y sudo && \
echo "willians ALL=(ALL) NOPASSWD:ALL" >> /etc/sudoers && \
chmod 0440 /etc/sudoers && \
apt-get clean && rm -rf /var/lib/apt/lists/*


USER willians

WORKDIR /home/willians


# Crear TODOS los directorios con permisos correctos (umask los manejará automáticamente)

RUN mkdir -p laravel legacy pkg ensayos backup


# Volver a root solo para chown final (por si acaso)


RUN sudo chown -R 1000:1000 /home/willians && \
	sudo chmod -R 755 /home/willians


# Volver al usuario final

USER willians:willians


# Copiar archivos DESPUÉS de permisos (preserva umask)

COPY --chown=willians:willians . .

CMD ["bash"]
```

  

