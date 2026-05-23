# Ajustes para sincronizar notas desde la estacion local

## Objetivo del script

Copiar archivos .md desde un DIRECTORIO LOCAL  a un DIRECTORIO DESTINO.

## Acerca del entorno:

- El DIRECTORIO BASE tiene la ruta fija en: /var/www/html/public/notes



## Ajustes o cambios:

- Agregar un nuevo parametro para indicar el directorio. Este le llamaremos RUTA PERSONAL.


## Validaciones

- Debe existir la RUTA PERSONAL para iniciar el copiado

    En caso contrario, una alerta que sugiera al usuario definir la ruta en la seccion de Configuracion de la aplicacion Web,  y regresar a la consola.


## Consideraciones:

- Se mantienen en el script, los argumentos previos y las opciones
- No puede copiarse archivos en el DIRECTORIO BASE
- El DIRECTORIO DESTINO se conforma por la concatenacion del DIRECTORIO BASE y la RUTA PERSONAL.
- Solo seran copiados archivos .md

# Analiza este script. NO genere cambios ni codigo..Dame alternativas y recomendaciones