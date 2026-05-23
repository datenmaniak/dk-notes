
Despues de haber migrado una  aplicacion a K3s, y observar que funciona estable, tanto  la app y la bases de datos se comunican. 

> [!NOTE]
> Al encender el cluster, he observado que el directorio uploads se ha perdido. De manera, que la Persistencia de Logs/Uploads se hace vital si se tratara de una app en produccion.  


Es una observación clave. Lo que has experimentado es la naturaleza **efímera** de los Pods en Kubernetes: cualquier archivo escrito en el sistema de archivos del contenedor (como `/var/www/html/uploads`) vive y muere con el ciclo de vida de ese proceso. Al reiniciar el clúster o el Pod, el contenedor vuelve a su estado original definido en la imagen.

Para solucionar esto, necesitamos implementar una capa de **Almacenamiento Persistente**. Aquí te detallo las opciones principales para tu entorno de Homelab con k3s:

---

## 1. Local Path Provisioner (La opción por defecto en k3s)

K3s incluye de fábrica el "Local Path Provisioner". Este utiliza un directorio del disco duro del nodo físico donde corre el Pod para guardar los datos.

- **Cómo funciona:** Kubernetes reserva una carpeta en el host (ej: `/var/lib/rancher/k3s/storage/`) y la vincula al Pod.
    
- **Ventaja:** Es extremadamente rápido y no requiere configuración extra.
    
- **Desventaja:** **No hay movilidad.** Si tu Pod se mueve del `worker1` al `worker2`, no encontrará sus archivos porque están físicamente en el disco duro del primer nodo.
    

## 2. NFS (Network File System)

Dado que ya mencionaste tener un repositorio "test-nfs", esta es una opción muy sólida para un Homelab.

- **Cómo funciona:** Un servidor externo (o un Pod dedicado) comparte una carpeta por red. Todos los nodos del clúster montan esa misma carpeta.
    
- **Ventaja:** **Alta disponibilidad.** Si el Pod se mueve de nodo, se vuelve a conectar a la carpeta por red y los archivos siguen ahí. Es ideal para aplicaciones PHP donde varios Pods podrían necesitar leer la misma carpeta de `uploads`.
    
- **Desventaja:** Depende de la velocidad de tu red y de que el servidor NFS esté siempre encendido.
    

## 3. HostPath (Manual y Estático)

Es similar al Local Path pero tú defines exactamente qué carpeta del host quieres usar.

- **Cómo funciona:** Mapeas directamente, por ejemplo, `/home/usuario/datos` del nodo físico al `/var/www/html/uploads` del contenedor.
    
- **Ventaja:** Tienes control total sobre dónde están los archivos en el disco físico.
    
- **Desventaja:** Muy rígido. Si el Pod se despliega en un nodo que no tiene esa carpeta creada, fallará.
    

## 4. Longhorn (Almacenamiento Distribuido)

Es una solución de almacenamiento empresarial creada por Rancher (los mismos de k3s) que se instala sobre el clúster.

- **Cómo funciona:** Replica los datos entre todos tus nodos. Si un disco duro falla, los datos están a salvo en los otros nodos.
    
- **Ventaja:** Es "la forma correcta" de hacer persistencia en producción real. Ofrece dashboards y copias de seguridad (snapshots).
    
- **Desventaja:** Consume más recursos (RAM/CPU) de tus nodos, lo cual puede ser pesado para un Homelab modesto.
    

---

### Conceptos que usaremos (independientemente de la opción):

Para aplicar cualquiera de estas, introduciremos dos nuevos recursos en tus manifiestos:

1. **PersistentVolume (PV):** El "pedazo" de disco duro real (la pieza de hardware o red).
    
2. **PersistentVolumeClaim (PVC):** El "ticket de reclamo" que tu aplicación PHP presenta para pedir espacio.
    

**¿Cuál de estas opciones te suena más interesante para tu arquitectura actual?** (Considerando que ya tienes algo de experiencia previa con NFS en tu registro de imágenes).