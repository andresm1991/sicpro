<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SistemaController;
use App\Http\Controllers\ProveedorController;
use App\Http\Controllers\ArticuloController;
use App\Http\Controllers\InventarioController;
use App\Http\Controllers\RubroController;
use App\Http\Controllers\ConfiguracionController;
use App\Http\Controllers\Sistema\RoleController;
use App\Http\Controllers\UserController;


// Sistema — requires sistema.ver permission to access the module
Route::group(['prefix' => 'sistema', 'as' => 'sistema.', 'middleware' => 'permission:sistema.ver'], function () {
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

    // Usuarios — permission-based (sistema.usuarios.*)
    Route::group(['prefix' => 'usuarios', 'as' => 'users.'], function () {
        Route::get('/', [UserController::class, 'index'])->name('index')->middleware('permission:sistema.usuarios.ver');
        Route::get('/crear-usuario', [UserController::class, 'create'])->name('create')->middleware('permission:sistema.usuarios.crear');
        Route::post('/store', [UserController::class, 'store'])->name('store')->middleware('permission:sistema.usuarios.crear');
        Route::get('/{user}/editar', [UserController::class, 'edit'])->name('edit')->middleware('permission:sistema.usuarios.editar');
        Route::put('/update/{user}', [UserController::class, 'update'])->name('update')->middleware('permission:sistema.usuarios.editar');
        Route::delete('/eliminar/{user}', [UserController::class, 'destroy'])->middleware('permission:sistema.usuarios.eliminar');
        Route::get('/buscar', [UserController::class, 'buscar'])->middleware('permission:sistema.usuarios.ver');
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

    // Rubros — permission-based (sistema.rubros.ver)
    Route::group(['prefix' => 'rubros', 'as' => 'rubros.', 'middleware' => 'permission:sistema.rubros.ver'], function () {
        Route::get('/', [RubroController::class, 'index'])->name('index');
        Route::delete('/eliminar/{rubro}', [RubroController::class, 'destrory'])->name('destroy');
    });

    // Configuraciones — permission-based (sistema.configuracion.ver)
    Route::group(['prefix' => 'configuraciones', 'as' => 'config.', 'middleware' => 'permission:sistema.configuracion.ver'], function () {
        Route::get('/', [ConfiguracionController::class, 'index'])->name('index');
        Route::get('/configuraciones/detalle/{config}', [ConfiguracionController::class, 'detalle'])->name('detalle');
    });

    // Roles y Permisos — Admin only (also enforced in RoleController constructor)
    Route::group(['prefix' => 'roles', 'as' => 'roles.', 'middleware' => 'role:Administrador'], function () {
        Route::get('/', [RoleController::class, 'index'])->name('index');
        Route::get('/{role}/editar', [RoleController::class, 'edit'])->name('edit');
        Route::put('/{role}/update', [RoleController::class, 'update'])->name('update');
    });

    require base_path('routes/modules/cliente.php');
});