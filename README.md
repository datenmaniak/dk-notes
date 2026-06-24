# 📓 dk-notes

**dk-notes** es un sistema minimalista de gestión de conocimiento y portal web autohospedado, diseñado para automatizar la ingesta, procesamiento y visualización de notas personales escritas en formato **Markdown (.md)**.

El propósito central de este proyecto no es solo resolver una necesidad funcional de organización de notas, sino servir como un laboratorio evolutivo de infraestructura moderna. El proyecto está diseñado para nacer como un monolito contenedorizado en entornos locales y escalar hacia una arquitectura nativa de la nube, completamente orquestada y automatizada.

------

## 🚀 Arquitectura y Stack Técnico

El núcleo de la aplicación está construido sobre un stack robusto y eficiente:

- **Backend & Core:** Laravel 12 / PHP 8.3 (Ejecutándose sobre imágenes minimalistas de **Alpine Linux**).
- **Base de Datos:** PostgreSQL (Con soporte nativo para almacenamiento relacional e indexación de contenido).
- **Servidor Web:** Nginx (Optimizado para la entrega de assets estáticos y proxy inverso hacia PHP-FPM).
- **Entorno de Desarrollo:** Podman & Podman-Compose (Garantizando un entorno local *rootless*, aislado y ligero).

------

## 🗺️ Hoja de Ruta del Proyecto (Roadmap)

El desarrollo de **dk-notes** está estructurado en cuatro fases críticas que reflejan el ciclo de vida de un entorno de software profesional:

### 🔹 Fase 1: El Monolito Local (Estado Actual)

- Configuración del entorno de microservicios locales mediante contenedores independientes (`dk-app`, `dk-db`, `dk-web`).
- Implementación de un sistema perimetral de autenticación (Login seguro).
- Diseño del modelo relacional en PostgreSQL (`users`, `categories`, `notes`).
- Desarrollo de un motor de ingesta vía CLI (Custom Artisan Command) capaz de leer archivos `.md` locales, validar su integridad mediante *checksums* (evitando duplicados), parsear los metadatos (*Front Matter*) e inyectar el contenido renderizado en HTML para su consumo inmediato.

### 🔹 Fase 2: Preparación "Cloud-Native"

- Desacoplamiento total del sistema operativo host.
- Externalización y centralización de logs hacia la salida estándar (`stdout`/`stderr`).
- Optimización del `Dockerfile` de producción para compilar imágenes inmutables con el código embebido.

### 🔹 Fase 3: Orquestación en Kubernetes (`k3s`)

- Traducción de la arquitectura de red local a manifiestos de Kubernetes (`Deployments`, `StatefulSets`, `Services`).
- Configuración de acceso externo mediante un controlador de Ingress.
- Gestión de la persistencia de los archivos Markdown originales utilizando almacenamiento compartido (NFS) mediante `PersistentVolumes` (PV) y `PersistentVolumeClaims` (PVC).

### 🔹 Fase 4: Automatización e Integración Continua (CI/CD)

- Diseño de un pipeline de **Integración Continua (CI)** para la ejecución automatizada de linters de código y pruebas unitarias/funcionales ante cada cambio en el repositorio.
- Implementación de **Despliegue Continuo (CD)** para automatizar la construcción de imágenes, su publicación en un registro privado y el *rollout* controlado de la nueva versión dentro del clúster GitOps.

------

## 🛠️ Requisitos del Entorno de Desarrollo

- Linux (Probado y optimizado en entornos Fedora Atomic / Silverblue / Aurora).
- Podman & Podman-Compose.
- Git.


------

## 🗺️ Hoja de Ruta del Proyecto (Roadmap)

El desarrollo de **dk-notes** ha madurado a través de cuatro fases críticas de infraestructura:

| Fase | Nombre | Objetivo | Estado |
|---|---|---|---|
| 1 | Monolito Local | Desarrollo de la aplicación: Podman, Laravel | ✅ Completada |
| 2 | Cloud-Native | Preparación para la nube (configuración, logs a stdout) | ✅ Completada |
| 3 | Orquestación | Despliegue en Kubernetes (k3s, PV/PVC, Ingress) | ✅ Completada |
| 4 | CI/CD | Automatización de despliegues (Pipelines y GitOps) | ✅ Completada |

---
## 🔬 Estado Actual: Laboratorio de Funcionalidades y Portabilidad (⚙️ Activo / Experimental 👨‍🎓)

Con la base de infraestructura y el ciclo de vida de software (CI/CD) completamente automatizados y estables, **dk-notes** opera actualmente como un **proyecto piloto activo**. El enfoque principal ha dejado de ser la configuración del entorno para centrarse en:

1. **Evolución del Producto:** Iteración rápida y agregación de nuevas funcionalidades de usuario.
2. **Refactorización Estética:** Ajustes finos de estilos y mejora de la experiencia de usuario (UX/UI).
3. **Migración hacia la Portabilidad:** Desacoplamiento progresivo de la dependencia estricta del backend tradicional en PHP para transformar el proyecto en una arquitectura hiper-portátil (Frontend SPA/PWA separado de una API agnóstica).

---
## 🛠️ Próximas Implementaciones (Backlog)

### 🎨 UI/UX & Estilos
- [✅] Implementación de un sistema de diseño desacoplado (Tailwind/Componentes limpios).
- [✅ ] Optimización de la vista de lectura y renderizado Markdown para dispositivos móviles.

### 🚀 Funcionalidades de Gestión de Notas
- [✅] Buscador de notas por Categoria y/o etiqueta aprovechando la indexación nativa de PostgreSQL.


---


*Desarrollado bajo la firma de **datenmaniak**.*