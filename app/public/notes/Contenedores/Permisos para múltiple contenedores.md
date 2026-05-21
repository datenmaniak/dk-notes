File Permissions Across Multiple Containers

Se ajustan los permisos de acuerdo a UID:GID del host. 

El objetivo es permitir la edición de configuraciones y la codificación de aplicaciones desde el host, con ayuda de cualquier editor local.  Todo fuera del contenedor.


### Ajustes esenciales en el Containerfile 


´´´
RUN groupadd --gid 1000 willians && \
useradd --uid 1000 --gid willians --shell /bin/bash --create-home willians

´´´
### SUDO Opcional DESPUÉS de paquetes (configurado manualmente)

RUN apt-get update && apt-get install -y sudo && \
echo "willians ALL=(ALL) NOPASSWD:ALL" >> /etc/sudoers && \
chmod 0440 /etc/sudoers && \
apt-get clean && rm -rf /var/lib/apt/lists/*

  
###  Permisos 
RUN sudo chown -R 1000:1000 /home/willians && \
sudo chmod -R 755 /home/willians