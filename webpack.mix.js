const mix = require('laravel-mix');

/*
 |--------------------------------------------------------------------------
 | Front-end de SICPRO
 |--------------------------------------------------------------------------
 |
 | Las vistas cargan los scripts como modulos ES (type="module") desde /js/ y
 | el navegador resuelve sus imports; la hoja de estilos es CSS escrito a mano.
 | No hay bundling: resources/js y resources/css son la fuente y este build
 | solo los copia a public/.
 |
 | No convertir estas entradas a mix.js() ni mix.sass():
 |
 |  - un bundle de webpack no expone los exports con nombre que usan los 11
 |    archivos que hacen `import { ... } from './helpers.js'`
 |    (reportes_scripts, gerencial_scripts, caja, administrativo_scripts,
 |    mano_obra_scripts, prestamos_scripts, presupuesto_scripts,
 |    tareas_scripts, resumen_pagos_semanales_scripts, cronograma_scripts,
 |    articulo_scripts), asi que se rompen en runtime;
 |
 |  - resources/sass/app.scss son 8 lineas del esqueleto de Laravel y
 |    reemplazaria la hoja de estilos real de public/css/app.css.
 |
 */

mix.copyDirectory('resources/js', 'public/js')
    .copyDirectory('resources/css', 'public/css')
    // El service worker debe servirse desde la raiz: solo controla el
    // directorio desde el que se sirve, y el registro apunta a
    // /service-worker.js. Por eso vive fuera de resources/js, que se copia
    // entero a public/js.
    .copy('resources/service-worker.js', 'public/service-worker.js');
