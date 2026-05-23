<img src="https://r2cdn.perplexity.ai/pplx-full-logo-primary-dark%402x.png" style="height:64px;margin-right:32px"/>

# npm run build

sh: npm: not found

`npm` no está instalado o no está en el `PATH` del entorno donde ejecutas `npm run build`.[^1_6][^1_8]

## Qué revisar

- Verifica que Node.js esté instalado, porque normalmente incluye `npm`.[^1_8][^1_6]
- Ejecuta `node -v` y `npm -v`; si ambos fallan, falta la instalación o el `PATH` está mal configurado.[^1_3][^1_8]
- Si estás en un contenedor o CI, usa una imagen que ya traiga Node, como una base oficial de Node.js.[^1_1]


## Solución rápida

- Instala Node.js LTS en tu sistema.[^1_3][^1_8]
- Si ya está instalado, agrega la ruta de Node/npm al `PATH` y abre una nueva terminal para que se apliquen los cambios.[^1_9][^1_3]
- Si el proyecto está en un contenedor, cambia la base a una imagen de Node y vuelve a construir.[^1_1]


## Si sigue fallando

- Asegúrate de estar en la carpeta correcta del proyecto, donde existe `package.json` y el script `build`.[^1_5][^1_7]
- Revisa `package.json` para confirmar que exista `"build"` en la sección `scripts`.[^1_7][^1_5]
- Si el entorno ya tiene Node pero no encuentra el comando, probablemente sea un problema de variables de entorno o de una instalación duplicada.[^1_10][^1_8]


## Ejemplo

En Linux/macOS, una comprobación típica sería:

```bash
node -v
npm -v
```

Si `node` funciona pero `npm` no, el problema suele ser el `PATH`; si ninguno funciona, falta instalar Node.js.[^1_6][^1_8]
<span style="display:none">[^1_2][^1_4]</span>

<div align="center">⁂</div>

[^1_1]: https://station.railway.com/questions/giving-bin-sh-1-npm-not-found-erro-499cabbd

[^1_2]: https://github.com/mysticatea/npm-run-all/issues/96

[^1_3]: https://www.codewithharry.com/blogpost/solving-npm-not-recognized-error-windows

[^1_4]: https://community.atlassian.com/forums/Bitbucket-questions/NPM-is-not-found-in-script/qaq-p/2503926

[^1_5]: https://stackoverflow.com/questions/71260030/errors-when-running-npm-run-build

[^1_6]: https://stackoverflow.com/questions/45585029/bash-npm-command-not-found

[^1_7]: https://www.reddit.com/r/react/comments/1h2mwtv/npm_run_build_and_start_is_not_working_in_the/

[^1_8]: https://www.geeksforgeeks.org/node-js/how-to-resolve-npm-command-not-found-error-in-node-js/

[^1_9]: https://www.youtube.com/watch?v=c7ptpvp1CYw

[^1_10]: https://phoenixnap.com/kb/npm-command-not-found


---

# estoy en un contenedor php-alpine

En un contenedor `php-alpine`, eso es normal: la imagen suele venir muy mínima y no incluye Node.js ni `npm`.[^2_1][^2_4]

## Qué hacer

- Instala Node.js y npm dentro del contenedor con `apk`, por ejemplo:

```sh
apk add --no-cache nodejs npm
```


[^2_4]

- Si tu proyecto necesita una versión más nueva, prueba:

```sh
apk add --no-cache nodejs-current npm
```


[^2_4]

- Luego verifica:

```sh
node -v
npm -v
```


[^2_4]

## Dockerfile típico

Si quieres compilar assets dentro de la imagen PHP, añade algo así:

```dockerfile
FROM php:alpine
RUN apk add --no-cache nodejs npm
WORKDIR /var/www/html
```

Eso hace que `npm run build` pueda ejecutarse dentro del contenedor.[^2_1][^2_4]

## Alternativa mejor

Si solo necesitas construir assets, suele ser más limpio usar una etapa aparte con una imagen de Node y copiar el resultado final al contenedor PHP.[^2_7][^2_1]
Eso evita inflar la imagen PHP con dependencias de frontend.[^2_7][^2_1]

## Si aún falla

- Confirma que estás dentro del contenedor correcto.
- Asegúrate de estar en el directorio donde existe `package.json`.
- Si `apk add` no está disponible, entonces no estás realmente en Alpine o la imagen está restringida.[^2_2][^2_1]

Puedo darte un `Dockerfile` exacto para `php:alpine` con build de Vite, Laravel Mix o React.
<span style="display:none">[^2_3][^2_5][^2_6][^2_8]</span>

<div align="center">⁂</div>

[^2_1]: https://github.com/actions/setup-node/issues/109

[^2_2]: https://forums.docker.com/t/php-8-1-4-fpm-alpine-install-npm-as-none-root-causing-issues/122566

[^2_3]: https://stackoverflow.com/questions/74015060/npm-not-working-on-docker-image-php8-1-fpm-alpine

[^2_4]: https://btholt.github.io/complete-intro-to-containers/making-our-own-alpine-nodejs-container/

[^2_5]: https://www.youtube.com/watch?v=PzEID2e7Ay4

[^2_6]: https://alpinejs.dev/essentials/installation

[^2_7]: https://stackoverflow.com/questions/44447821/how-to-create-a-docker-image-for-php-and-node

[^2_8]: https://www.youtube.com/watch?v=Jg-C3c31oFY

