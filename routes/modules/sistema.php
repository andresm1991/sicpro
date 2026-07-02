<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SistemaController;
use App\Http\Controllers\ProveedorController;
use App\Http\Controllers\ArticuloController;
use App\Http\Controllers\InventarioController;
use App\Http\Controllers\RubroController;
use App\Http\Controllers\ConfiguracionController;
use App\Http\Controllers\UserController;


// Sistema — protected: only Administrador role can access
Route::group(['prefix' => 'sistema', 'as' => 'sistema.', 'middleware' => 'role:Administrador'], function () {
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

    // Inventario
    Route::group(['prefix' => 'inventario', 'as' => 'inventario.'], function () {
        Route::get('/', [InventarioController::class, 'index'])->name('index');
        Route::get('/detalle/{producto}', [InventarioController::class, 'detalle'])->name('detalle');
        Route::post('/nuevo', [InventarioController::class, 'store']);
        Route::post('/detalle/dar-de-baja', [InventarioController::class, 'darDeBajaProducto']);
        Route::get('/buscar', [InventarioController::class, 'buscar']);
        Route::delete('/detalle/eliminar/{id}', [InventarioController::class, 'destroy']);
        Route::delete('/eliminar/{id}', [InventarioController::class, 'destroyInventario']);
    });

    //** RUBROS */
    Route::group(['prefix' => 'rubros', 'as' => 'rubros.'], function () {
        Route::get('/', [RubroController::class, 'index'])->name('index');
        Route::delete('/eliminar/{rubro}', [RubroController::class, 'destrory'])->name('destroy');
    });

    // Configuraciones
    Route::group(['prefix' => 'configuraciones', 'as' => 'config.'], function () {
        Route::get('/', [ConfiguracionController::class, 'index'])->name('index');
        Route::get('/configuraciones/detalle/{config}', [ConfiguracionController::class, 'detalle'])->name('detalle');
    });

    require base_path('routes/modules/cliente.php');
});