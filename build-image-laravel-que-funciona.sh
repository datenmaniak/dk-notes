# ejecutar desde:
#   ~/dk-notes/
#
#   Si funciona. IMPORTANTE: el punto (.)
podman build -f laravel/Dockerfile-v1.43 -t local-reg.dk.lab:5000/dknotes-laravel:1.50 .

# en el transcurso de los cambios en la aplicacion, FluxCD hara el versionado semantica
# de manera automatica.
#
#
