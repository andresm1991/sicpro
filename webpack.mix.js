const mix = require('laravel-mix');

/*
 |--------------------------------------------------------------------------
 | Mix Asset Management
 |--------------------------------------------------------------------------
 |
 | Mix provides a clean, fluent API for defining some Webpack build steps
 | for your Laravel application. By default, we are compiling the Sass
 | file for the application as well as bundling up all the JS files.
 |
 */

mix.js('resources/js/app.js', 'public/js')
    .js('resources/js/administrativo_scripts.js', 'public/js')
    .js('resources/js/adquisiciones_script.js', 'public/js')
    .js('resources/js/articulo_scripts.js', 'public/js')
    .js('resources/js/cronograma_scripts.js', 'public/js')
    .js('resources/js/datatables.js', 'public/js')
    .js('resources/js/dynamic_form.js', 'public/js')
    .js('resources/js/helpers.js', 'public/js')
    .js('resources/js/inventario_scripts.js', 'public/js')
    .js('resources/js/mano_obra_scripts.js', 'public/js')
    .js('resources/js/orden_trabajo_contratistas_scripts.js', 'public/js')
    .js('resources/js/prestamos_scripts.js', 'public/js')
    .js('resources/js/presupuesto_scripts.js', 'public/js')
    .js('resources/js/proveedor_scripts.js', 'public/js')
    .js('resources/js/proyectos_scripts.js', 'public/js')
    .js('resources/js/reposicion_tiempo_scripts.js', 'public/js')
    .js('resources/js/solicitud_scripts.js', 'public/js')
    .js('resources/js/treeview.js', 'public/js')
    .js('resources/js/user_scripts.js', 'public/js')
    .sass('resources/css/app.scss', 'public/css')
    .sourceMaps();
