<?php

use App\Http\Controllers\AdquisicionController;
use App\Http\Controllers\ArticuloController;
use App\Http\Controllers\CatalogoDatoController;
use App\Http\Controllers\GenerarPdfController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ProveedorController;
use App\Http\Controllers\ProyectoController;
use App\Http\Controllers\SistemaController;
use App\Http\Controllers\UserController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', [App\Http\Controllers\Auth\LoginController::class, 'showLoginForm']);

Auth::routes();

Route::group(['middleware' => ['auth']], function () {
    Route::get('/home', [HomeController::class, 'index'])->name('home');
    // Proyectos
    Route::group(['prefix' => 'proyectos', 'as' => 'proyecto.'], function () {
        Route::get('/', [ProyectoController::class, 'index'])->name('index');
        Route::get('/{tipo}/{tipo_id}', [ProyectoController::class, 'proyectos'])->name('list');
        Route::get('/{tipo}/{tipo_id}/nuevo', [ProyectoController::class, 'create'])->name('create');
        Route::post('/{tipo}/{tipo_id}/crear', [ProyectoController::class, 'store'])->name('store');
        Route::get('/{tipo}/{tipo_id}/view/{proyecto}', [ProyectoController::class, 'opcionesProyecto'])->name('view');
        Route::get('/{tipo}/{tipo_id}/informacion-general/{proyecto}', [ProyectoController::class, 'show_informacion_general'])->name('informacion.general');
        Route::get('/{tipo}/{tipo_id}/editar/{proyecto}', [ProyectoController::class, 'edit'])->name('edit');
        Route::put('/{tipo}/{tipo_id}/actualizar/{proyecto}', [ProyectoController::class, 'update'])->name('update');
        Route::post('/download-file', [ProyectoController::class, 'downloadFiles'])->name('download.files');
        // Adquisiciones
        Route::group(['prefix' => '/{tipo}/{tipo_id}/adquisiciones/{proyecto}/tipo-adquisicion', 'as' => 'adquisiciones.'], function () {
            Route::get('/', [AdquisicionController::class, 'index'])->name('menu');
            Route::get('/{tipo_adquisicion}', [AdquisicionController::class, 'tipoAquisicion'])->name('tipo');
            Route::get('/{tipo_adquisicion}/list/{tipo_etapa}', [AdquisicionController::class, 'listTipoAquisicion'])->name('tipo.etapa');
            Route::get('/{tipo_adquisicion}/etapa/{tipo_etapa}/nuevo', [AdquisicionController::class, 'create'])->name('tipo.create');
            Route::post('/{tipo_adquisicion}/{tipo_etapa}/guardar', [AdquisicionController::class, 'store'])->name('store');
            Route::put('/{tipo_adquisicion}/{tipo_etapa}/actualizar/{pedido}', [AdquisicionController::class, 'updateAdquisicion'])->name('update');
            Route::get('/{tipo_adquisicion}/etapa/{tipo_etapa}/orden-recepcion/{pedido}', [AdquisicionController::class, 'ordenRecepcion'])->name('orden.recepcion');
            Route::post('/{tipo_adquisicion}/{tipo_etapa}/orden-recepcion/{pedido}/guardar', [AdquisicionController::class, 'storeOrdenRecepcion'])->name('orden.recepcion.store');
            Route::put('/{tipo_adquisicion}/{tipo_etapa}/orden-recepcion/{pedido}/actualizar/{orden_recepcion}', [AdquisicionController::class, 'updateOrdenRecepcion'])->name('orden.recepcion.update');
            Route::get('/{tipo_adquisicion}/{tipo_etapa}/orden-adquisicion/{pedido}/editar', [AdquisicionController::class, 'editarPedido'])->name('orden.pedido.edit');
        });
    });
    // Perfil Usuario
    Route::group(['prefix' => 'mi-perfil', 'as' => 'perfil.'], function () {
        Route::get('/', [UserController::class, 'perfil'])->name('show');
        Route::put('/{user}/actualizar', [UserController::class, 'updatePerfil'])->name('update');
    });
    // Sistema
    Route::group(['prefix' => 'sistema', 'as' => 'sistema.'], function () {
        Route::get('/', [SistemaController::class, 'index'])->name('index');

        // Proveedores
        Route::group(['middleware' => 'decrypt.param', 'prefix' => 'proveedores', 'as' => 'proveedor.'], function () {
            Route::get('/', [SistemaController::class, 'proveedores'])->name('menu');
            Route::get('/{menu_id}/index', [ProveedorController::class, 'index'])->name('index');
            Route::get('/{menu_id}/nuevo-proveedor', [ProveedorController::class, 'create'])->name('create');
            Route::post('/{menu_id}/guardar', [ProveedorController::class, 'store'])->name('store');
            Route::get('/{menu_id}/actualizar-proveedor/{proveedor}', [ProveedorController::class, 'edit'])->name('edit');
            Route::put('/{menu_id}/update/{proveedor}', [ProveedorController::class, 'update'])->name('update');
            Route::get('/{menu_id}/buscar', [ProveedorController::class, 'buscar']);
            Route::delete('/{menu_id}/eliminar/{proveedor}', [ProveedorController::class, 'delete']);
        });
        // Usuarios
        Route::group(['prefix' => 'usuarios', 'as' => 'users.'], function () {
            Route::get('/', [UserController::class, 'index'])->name('index');
            Route::get('/crear-usuario', [UserController::class, 'create'])->name('create');
            Route::post('/store', [UserController::class, 'store'])->name('store');
            Route::get('/{user}/editar', [UserController::class, 'edit'])->name('edit');
            Route::put('/update/{user}', [UserController::class, 'update'])->name('update');
            Route::delete('/eliminar/{user}', [UserController::class, 'destroy']);
            Route::get('/buscar', [UserController::class, 'buscar']);
        });

        // Productos
        Route::group(['prefix' => 'productos', 'as' => 'articulo.'], function () {
            Route::get('/', [ArticuloController::class, 'index'])->name('index');
            Route::post('/nuevo', [ArticuloController::class, 'store']);
            Route::put('/actualizar/{articulo}', [ArticuloController::class, 'update']);
            Route::get('/buscar', [ArticuloController::class, 'buscar']);
            Route::delete('/eliminar/{id}', [ArticuloController::class, 'destroy']);
        });
    });


    // Controllador para generar los pdf del sistema
    Route::group(['prefix' => 'generar-pdf', 'as' => 'pdf.'], function () {
        Route::get('/adquisicion-pdf/{pedido}', [GenerarPdfController::class, 'generarPdfPedido'])->name('adquisicion');
        Route::get('/recepcion-pdf/{pedido}', [GenerarPdfController::class, 'generarPdfRecepcion'])->name('recepcion');
    });



    //Route::get('/base-de-datos/{id}/view', [BaseDatoController::class, 'showForm'])->name('base.datos.form');
    //
    Route::get('/bancos', [CatalogoDatoController::class, 'getBancos']);
    Route::get('/tipo-cuenta', [CatalogoDatoController::class, 'getTipoCuentas']);

    Route::get('/no-access', function () {
        return view('errors.no-access');
    })->name('no.access');
});
