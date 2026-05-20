## Proyecto dknotes



De antemano, pido que no generes código todavía. Quisiera evaluar lo que tengo documentado aquí, y agregar cambios a medida que se discute paso a paso el proyecto.



Quiero que cualquier sugerencia, la pongas en titulo. Seguido de una explicación breve por que y para que la propones.  Si consideras necesario una lista de alternativas que yo pueda evaluar antes de pedirte  lo que vas a hacer.  A fin de evitar relleno innecesario dentro del hilo o salirnos del tema.



## Objetivo: Crear una aplicación de notas

Gestionar las notas importadas desde un directorio de notas Markdown de forma rápida y eficiente.

La aplicación sera minimalista, con estas características:

1. Login de acceso

2. CRUD 

3. Permitir filtrado rápido por pantalla (Navegación por Categorías y Hashtags).

4. Ordenamiento por fechas  (obtenida de cada archivo que contiene la nota)

5. Categorización (Generada por el nombre del subdirectorio)

6.  Permitir escanear el directorio para agregar nuevas notas (de manera manual, una opción de menu)

   



## Fundamento:

- Categorizacion:
	Se leera un directorio de notas. Seran creadas en base a los nombres de los subdirectorios.

- Base de datos:
  La base de datos en PostgreSQL.
  
- Filtrado por Pantalla:
  En lugar de un buscador de texto global, la aplicación ofrecerá un mecanismo de filtrado visual en la interfaz. El usuario podrá hacer clic en cualquier categoría (subdirectorio) o hashtag para segmentar el listado de notas activas de forma instantánea. Las consultas en la base de datos serán directas y optimizadas mediante los índices de las claves foráneas.




## Base de datos (postgres)

- Nombre: dknotes
- Usuario: dkuser
- password: <password>






## Tablas

### 📊 Estructura de la Tabla: `notes`

Esta tabla única es suficiente para arrancar el monolito. Cada fila representará un archivo `.md` importado.

| **Campo**          | **Tipo de Dato (PostgreSQL)** | **Propósito / Descripción**                                  |
| ------------------ | ----------------------------- | ------------------------------------------------------------ |
| `id`               | `BIGSERIAL` (Primary Key)     | Identificador único autoincremental de la nota.              |
| `title`            | `VARCHAR(255)`                | El título de la nota (puede tomarse del nombre del archivo o de un encabezado `#`). |
| `slug`             | `VARCHAR(255)` (Unique)       | Ruta amigable para la URL (ej. `mi-nota-de-bash` en lugar de `id=1`), ideal para buscar en la app. |
| `content_markdown` | `TEXT`                        | El contenido original en Markdown puro (útil si deseas editarlo o exportarlo después). |
| `content_html`     | `TEXT`                        | El contenido ya convertido a HTML por Laravel (es lo que se renderizará en el navegador de forma ultrarrápida, sin procesar en tiempo real). |
| `file_path`        | `VARCHAR(512)` (Nullable)     | La ruta del archivo original en tu disco. Sirve para saber de dónde vino y evitar duplicados al reimportar. |
| `checksum`         | `VARCHAR(64)` (Nullable)      | Un hash MD5/SHA256 del archivo. Permite al comando de Artisan saber si el archivo cambió en tu disco para actualizarlo o ignorarlo si es idéntico. |
| `created_at`       | `TIMESTAMP`                   | Fecha de creación de la nota en el sistema (o extraída de los metadatos del archivo). |
| `updated_at`       | `TIMESTAMP`                   | Última vez que se modificó la nota en la aplicación.         |
| category_id        | `BIGINT` (Foreign Key)        | "Categorización generada por el nombre del subdirectorio".  ID de la categoría a la que pertenece. Vincula la nota con su subdirectorio (`ON DELETE RESTRICT`). |
| user_id            | `BIGINT`, Foreign Key         | Esto formaliza que cada nota pertenece a un administrador.   |



## 📊 Estructura de la Tabla: `categories`

Esta tabla será muy ligera y estará optimizada para indexar y organizar el contenido.

| **Campo**    | **Tipo de Dato (PostgreSQL)** | **Propósito / Descripción**                                  |
| ------------ | ----------------------------- | ------------------------------------------------------------ |
| `id`         | `BIGSERIAL` (Primary Key)     | Identificador único de la categoría.                         |
| `name`       | `VARCHAR(100)` (Unique)       | El nombre de la categoría (ej: "Sistemas Operativos", "DevOps"). |
| `slug`       | `VARCHAR(100)` (Unique)       | URL amigable (ej: `sistemas-operativos`), clave para las búsquedas en la app. |
| `created_at` | `TIMESTAMP`                   | Tiempos estándar de control de Laravel.                      |
| `updated_at` | `TIMESTAMP`                   | Última vez que se modificó la categoria en la aplicación.    |



## 📊 Estructura de la Tabla: `users`

La gran ventaja de haber elegido Laravel para este proyecto es que **esta tabla ya viene completamente diseñada, configurada y lista para usar de forma nativa**. No tienes que inventar la rueda con el manejo de contraseñas.

La estructura estándar que Laravel ya creó en tu carpeta de migraciones es la siguiente:

| **Campo**           | **Tipo de Dato (PostgreSQL)** | **Propósito / Descripción**                                  |
| ------------------- | ----------------------------- | ------------------------------------------------------------ |
| `id`                | `BIGSERIAL` (Primary Key)     | Identificador único del usuario.                             |
| `name`              | `VARCHAR(255)`                | Tu nombre o alias de administrador.                          |
| `email`             | `VARCHAR(255)` (Unique)       | Correo electrónico que usarás para loguearte.                |
| `email_verified_at` | `TIMESTAMP` (Nullable)        | Control de verificación (se puede ignorar en local).         |
| `password`          | `VARCHAR(255)`                | La contraseña **encriptada automáticamente** usando el algoritmo seguro `Bcrypt` o `Argon2id`. |
| `remember_token`    | `VARCHAR(100)` (Nullable)     | Token seguro para mantener la sesión abierta ("Recuérdame"). |
| `created_at`        | `TIMESTAMP`                   | Tiempos estándar de control.                                 |
| `updated_at`        | `TIMESTAMP`                   | Última vez que se hizo login                                 |



### 📊 Estructura de la Tabla: `tags`

Esta tabla almacenará de forma única los hashtags creados desde la aplicación o extraídos de las notas.

| **Campo**    | **Tipo de Dato (PostgreSQL)** | **Propósito / Descripción**                                  |
| ------------ | ----------------------------- | ------------------------------------------------------------ |
| `id`         | `BIGSERIAL` (Primary Key)     | Identificador único de la etiqueta.                          |
| `name`       | `VARCHAR(50)` (Unique)        | El nombre del hashtag (ej: `bash`, `laravel`, `devops`).     |
| `slug`       | `VARCHAR(50)` (Unique)        | URL amigable (ej: `laravel`), ideal para filtrar u organizar en la app. |
| `created_at` | `TIMESTAMP`                   | Tiempos estándar de control de Laravel.                      |
| `updated_at` | `TIMESTAMP`                   | Última vez que se modificó la etiqueta.                      |



### 📊 Estructura de la Tabla Intermedia: `note_tag` (Tabla Pivote)

Esta tabla gestiona la relación de muchos a muchos (*Many-to-Many*) entre las notas y tus hashtags, asegurando que el rendimiento de las búsquedas sea óptimo.

| **Campo** | **Tipo de Dato (PostgreSQL)** | **Propósito / Descripción**                                  |
| --------- | ----------------------------- | ------------------------------------------------------------ |
| `note_id` | `BIGINT` (Foreign Key)        | Relación con la tabla `notes`. Si la nota se elimina, se borra automáticamente este registro (`ON DELETE CASCADE`). |
| `tag_id`  | `BIGINT` (Foreign Key)        | Relación con la tabla `tags`. Si la etiqueta se elimina, se borra automáticamente este registro (`ON DELETE CASCADE`). |

> 📌 **Nota de Diseño:** Esta tabla no requiere campos `created_at` ni `updated_at`. Para maximizar el rendimiento del filtrado dinámico, se definirá una **clave primaria compuesta** mediante `PRIMARY KEY(note_id, tag_id)`.






# Escenario actual
1. Ya tengo un entorno de desarrollo basado en Laravel. 





## Revisiones



### 1. Objetivos del Proyecto

- **Naturaleza Monolítica y Minimalista:** Aplicación limpia donde el color se reserva exclusivamente para botones y alertas.
- **Flujo de Entrada:** Se añade el objetivo de **Permitir escanear el directorio para agregar nuevas notas**, estableciendo que este proceso será **estrictamente manual** iniciado por ti desde la aplicación web.

### 2. Base de Datos: Arquitectura de Tablas

- **Fuente de la Verdad:** El comando de Artisan importará los archivos `.md` del disco hacia PostgreSQL una sola vez (poblamiento inicial). A partir de ahí, cualquier edición en la web se guardará **únicamente en la base de datos**, dejando el archivo físico intacto como respaldo estático. El `checksum` evitará duplicados o sobreescrituras accidentales si se vuelve a escanear el disco.
- **Relación de Categorías:** Se añade el campo `category_id` a la tabla `notes` para asociarla con la tabla `categories` (generada automáticamente según el nombre del subdirectorio).
- **Organización Transversal (Cuarta Tabla):** Se eligió el **Modelo Clásico de Etiquetas**, estructurando e integrando a la documentación las tablas `tags` y la tabla pivote `note_tag` (relación muchos a muchos) para soportar hashtags.

---

### Interfaz para el Activador Manual de Escaneo de notas

Necesitamos definir dónde colocar el botón o disparador manual para escanear tus notas en el disco:

- Un botón discreto (ej: `🔄 Sincronizar`) directo en la barra de navegación o menú lateral, visible solo si estás logueado.

---

## Definir la normalización y almacenamiento del campo `content_html`

### ¿Por qué?

El documento especifica que la tabla `notes` guardará `content_html` para renderizar las notas de forma ultrarrápida. Sin embargo, al importar Markdown que contiene rutas relativas a imágenes o enlaces locales (ej: `![mi-diagrama](imagenes/esquema.png)`), esas rutas se romperán en el navegador web si Laravel no sabe cómo interpretarlas.

### ¿Para qué?

Para garantizar que cualquier recurso multimedia o enlace interno embebido en tus archivos `.md` locales se renderice correctamente en la aplicación web tras la conversión a HTML.

### Alternativas elegida:

- **Rutas estáticas mediante enlace simbólico - Recomendada:** Mover o enlazar tu directorio de notas al directorio `storage/app/public` de Laravel. Durante la conversión a HTML, un asistente (*helper*) reemplaza las rutas relativas por la URL pública correspondiente (`asset('storage/notas/...')`). Es la forma nativa y más limpia en Laravel.

---



## Filtrado Dinámico en Pantalla (Barra Lateral o Navbar extendido)

### ¿Por qué?

En lugar de procesar complejas consultas de texto en el servidor para buscar dentro del cuerpo de la nota, la aplicación aprovechará los datos que ya tenemos estructurados en la base de datos (Categorías y Tags).

### ¿Para qué?

Para permitirte encontrar cualquier nota en cuestión de un par de clics, reduciendo visualmente el listado principal a solo lo que necesitas ver en ese momento, sin recargar la aplicación con librerías pesadas.

### Alternativa elegida  para  los filtros en la UI:

- **Barra Lateral de Navegación Fija - Recomendada):** Una columna lateral izquierda muy limpia. Arriba, el listado de **Categorías** (tus subdirectorios); abajo, una lista de **Hashtags** (etiquetas) en formato de texto plano y pequeño. Al hacer clic en una categoría o en un tag, el listado central de notas se filtra instantáneamente.

---

## En base a Control de UI y Experiencia de Usuario Predictiva, que estilo utilizar para que sea responsivo y aun asi mantener la personalidad minimalista de la app.



## Sugerencia: Estilo Responsivo basado en CSS Utilitario (Tailwind CSS)

### ¿Por qué?

Es la herramienta estándar moderna en el ecosistema de Laravel. En lugar de escribir archivos CSS masivos o usar frameworks pesados con componentes pre-diseñados (como Bootstrap), te permite controlar el diseño adaptativo directamente en las vistas de Blade mediante modificadores de pantalla (`sm:`, `md:`, `lg:`).

### ¿Para qué?

Para garantizar que la barra lateral de filtrado (Categorías y Tags) y el cuerpo de la nota se reacomoden perfectamente en móviles o tabletas, manteniendo el peso de la aplicación al mínimo y respetando tu regla estricta de no usar colores decorativos innecesarios.

### Alternativas de distribución responsiva (Layout):

- **Barra Lateral Colapsable a Menú Superior - Recomendada:** En pantallas grandes (escritorio), la barra lateral de Categorías y Tags se mantiene fija a la izquierda. En pantallas pequeñas (móviles), esa barra lateral se oculta automáticamente y sus elementos se transforman en una fila horizontal discreta de enlaces o botones planos justo debajo del Navbar.
  - *Ventaja:* No requiere JavaScript pesado ni menús laterales desplegables (*drawers*) que tapen la pantalla. Todo fluye de arriba hacia abajo de forma natural.



### Sugerencia: Tipografía Fluida y Contenedores de Lectura Maximizados

### ¿Por qué?

El contenido de tus notas importadas es Markdown convertido a HTML. Si el contenedor del texto se estira demasiado en pantallas ultra-anchas o se comprime de forma incómoda en móviles, la lectura se vuelve tediosa, rompiendo el propósito de una aplicación eficiente.

### ¿Para qué?

Para que el texto mantenga un tamaño óptimo y una longitud de línea cómoda para el ojo humano (entre 60 y 80 caracteres por línea) sin importar el dispositivo.

### Alternativas de implementación:

- **Contenedor Centrado con Límites Rígidos - Recomendada:** Usar la clase `.prose` (de la librería oficial Tailwind Typography) o limitar el ancho del contenido de la nota con un máximo estricto (ej. `max-w-3xl` o `max-w-screen-md`) y centrarlo en la pantalla.
  - *Resultado:* En escritorio verás un lienzo central de lectura perfecto con márgenes limpios a los lados, y en móvil el texto se adaptará hasta los bordes con un acolchado (*padding*) sutil. Es el estándar de plataformas de lectura como Medium o los visores de código como GitHub.



##  Sugerencia: Sistema de Modo Oscuro Automático por CSS

### ¿Por qué?

Una aplicación minimalista se basa en el contraste puro (fondos claros/oscuros y texto legible). Añadir un botón para cambiar de tema (Sol/Luna) añade un elemento extra a la interfaz que puede romper la estética limpia del Navbar.

### ¿Para qué?

Para ofrecer comodidad visual en cualquier dispositivo (especialmente móviles de noche) sin añadir botones ni configuraciones adicionales en la base de datos.

### Alternativas de comportamiento:

- **(Media Query `prefers-color-scheme` - Recomendada):** Configurar el CSS para que detecte automáticamente las preferencias del sistema operativo del usuario (si tu Linux Fedora o tu móvil están en modo oscuro, la app se vuelve oscura automáticamente). No requiere JavaScript ni botones en el menú.





---



## 🚀 Plan de Ejecución (Roadmap)

### 🔲 Fase 1: Infraestructura y Datos [En progreso]

- [x] Configurar las variables de entorno en el archivo `.env` para la conexión a PostgreSQL (`dknotes`, `dkuser`, `7shogun`).
- [x]  Crear la migración para la tabla `categories`.
- [x] Crear la migración para la tabla `notes` con su restricción de clave foránea hacia `categories`.
- [x]  Crear la migración para la tabla `tags`.
- [x]  Crear la migración para la tabla pivote `note_tag` definiendo la clave primaria compuesta `PRIMARY KEY(note_id, tag_id)`.
- [x] Generar los Modelos de Eloquent (`Note`, `Category`, `Tag`, `User`) y definir sus relaciones estructurales (`belongsTo`, `hasMany`, `belongsToMany`).



### 🔲 Fase 2: El Motor de Importación (Artisan)

- [x]  Crear el comando personalizado de Artisan (ej: `php artisan notes:import`).
- [x]  Implementar la lógica de lectura física del directorio de Markdown (mapeo de subdirectorios como categorías).
- [x]  Integrar el procesador/librería para convertir el contenido de Markdown a HTML.
- [x]  Implementar la lógica de cálculo y verificación de `checksum` para evitar duplicados o sobreescrituras.
- [x]  Validar la normalización de las imágenes locales en el campo `content_html` usando el enlace simbólico del *Filesystem*.

### 🔲 Fase 3: Autenticación y Rutas

- [x] Instalar o configurar el sistema de autenticación nativo de Laravel para la tabla `users`.
- [x] Definir el archivo de rutas (`web.php`) protegiendo el CRUD y las acciones de sincronización bajo el middleware `auth`.
- [x] Crear el controlador para el manejo de las vistas de las notas.

### 🔲 Fase 4: Interfaz Web y Sincronización Manual

- [x] Construir el diseño minimalista base (respetando el uso exclusivo de color para botones y alertas).
- [x] Diseñar la barra lateral de navegación fija para listar y filtrar dinámicamente por Categorías y Hashtags.
- [x] Desarrollar la vista de lectura rápida de notas renderizando el campo `content_html`.
- [x] Integrar el botón discreto de actualización (`🔄 Sincronizar`) en el Navbar.
- [x] Conectar el botón del Navbar con el comando de Artisan para disparar el escaneo manualmente y retornar la alerta con el feedback de color.

------



## Lista de Áreas de Ajustes

------

### 1. Visuales ✅ (Completado)

- [x] Barra lateral responsiva (botón hamburguesa)
- [x] Padding superior en móvil
- [x] Resaltado de sintaxis en bloques de código (tema atom-one-dark)

------

### 2. Barra Lateral (Pendiente)

- [x] Agregar elementos al menú (categorías destacadas, estadísticas rápidas)
- [x] Cambiar íconos
- [x] Ajustar espaciado y tamaño de fuente
- [x] Colapsable manualmente (usuario puede contraer/expandir)

------

### 3. Listado de Notas (Pendiente)

- [x] Paginación (mostrar 10, 20, 50 notas por página)
- [ ] Ordenamiento (por fecha, título, categoría)
- [ ] Búsqueda rápida por título
- [x] Vista en tarjetas vs vista en lista (toggle)
- [ ] Notas destacadas o fijadas

------

### 4. Vista de Detalle (Pendiente)

- [ ] Navegación entre notas (anterior/siguiente)
- [ ] Tabla de contenidos automática (TOC)
- [ ] Botón para copiar enlace directo
- [ ] Mostrar metadatos (fecha creación, última modificación, ruta del archivo)
- [x] Botón para editar rápida

------

### 5. Editor (Pendiente)

- [x] Vista previa en vivo (Markdown vs HTML)
- [ ] Atajos de teclado (Ctrl+B negrita, Ctrl+I cursiva, etc.)
- [ ] Selector de categorías mejorado (con búsqueda)
- [ ] Autoguardado (borrador automático)
- [ ] Subida de imágenes (arrastrar y soltar)

------

### 6. Filtros y Búsqueda (Pendiente)

- [ ] Búsqueda por texto en título y contenido
- [x] Filtros múltiples (categoría + etiquetas combinados)
- [ ] Búsqueda por fecha (rango)
- [ ] Guardar filtros favoritos
- [ ] Búsqueda en tiempo real (mientras escribes)

------

### 7. Notificaciones (Pendiente)

- [ ] Estilo personalizado (colores, animaciones, posición)
- [ ] Duración configurable (desaparece automáticamente)
- [ ] Historial de notificaciones
- [ ] Sonido opcional para alertas importantes
- [ ] Posición (superior derecha, inferior izquierda, etc.)

------

### 8. Dashboard / Estadísticas (Pendiente)

- [ ] - Gráfico de notas por categoría (barras o pastel)
  - Notas por mes (actividad)
  - Notas más recientes
  - Notas más editadas
  - Categorías con más notas
  - Tiempo desde última sincronización

- [ ] ------

- [ ] ### 9. Otras Mejoras (Pendiente)

- [ ] Modo oscuro / claro (toggle)
- [ ] Exportar nota a PDF
- [ ] Copiar nota al portapapeles
- [ ] Historial de versiones (basado en checksum)
- [ ] Etiquetas (tags) por nota
- [ ] Notas relacionadas (por contenido similar)

------

**¿Cuál de estas áreas te interesa trabajar ahora?**



---

## Estado actual del proyecto (20.05.2026)

| **Área**                      | **Estado** |
| :---------------------------- | :--------- |
| Fase 1: Infraestructura y BD  | ✅ Completa |
| Fase 2: Motor de importación  | ✅ Completa |
| Fase 3: Autenticación y rutas | ✅ Completa |
| Fase 4: Interfaz web          | ✅ Completa |
| Ajustes visuales              | ✅ Completa |
| Paginación                    | ✅ Completa |
| Selector de categorías        | ✅ Completa |
| Altura automática             | ✅ Completa |
| Corrección total notas        | ✅ Completa |
