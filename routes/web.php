<?php

use App\Http\Controllers\AdministrativoController;
use App\Http\Controllers\AdquisicionController;
use App\Http\Controllers\ArticuloController;
use App\Http\Controllers\CatalogoDatoController;
use App\Http\Controllers\ConfiguracionController;
use App\Http\Controllers\ContratistaController;
use App\Http\Controllers\GenerarPdfController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\InventarioController;
use App\Http\Controllers\ManoObraController;
use App\Http\Controllers\PrestamoController;
use App\Http\Controllers\ProveedorController;
use App\Http\Controllers\ProyectoController;
use App\Http\Controllers\SistemaController;
use App\Http\Controllers\UserController;
use App\Models\AdquisicionDetalle;
use App\Models\CatalogoDato;

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
            //Route::get('/{tipo_adquisicion}/list/{tipo_etapa}/data-adquisiciones', [AdquisicionController::class, 'getAdquisiciones']);
            Route::get('/{tipo_adquisicion}/etapa/{tipo_etapa}/nuevo', [AdquisicionController::class, 'create'])->name('tipo.create');
            Route::post('/{tipo_adquisicion}/{tipo_etapa}/guardar', [AdquisicionController::class, 'store'])->name('store');
            Route::put('/{tipo_adquisicion}/{tipo_etapa}/actualizar/{pedido}', [AdquisicionController::class, 'updateAdquisicion'])->name('update');
            Route::get('/{tipo_adquisicion}/etapa/{tipo_etapa}/orden-recepcion/{pedido}', [AdquisicionController::class, 'ordenRecepcion'])->name('orden.recepcion');
            Route::post('/{tipo_adquisicion}/{tipo_etapa}/orden-recepcion/{pedido}/guardar', [AdquisicionController::class, 'storeOrdenRecepcion'])->name('orden.recepcion.store');
            Route::put('/{tipo_adquisicion}/{tipo_etapa}/orden-recepcion/{pedido}/actualizar/{orden_recepcion}', [AdquisicionController::class, 'updateOrdenRecepcion'])->name('orden.recepcion.update');
            Route::get('/{tipo_adquisicion}/{tipo_etapa}/orden-adquisicion/{pedido}/editar', [AdquisicionController::class, 'editarPedido'])->name('orden.pedido.edit');
            Route::delete('/{tipo_adquisicion}/{tipo_etapa}/eliminar-adquisicion/{pedido}', [AdquisicionController::class, 'destroyPedido']);
            Route::get('/{tipo_adquisicion}/list/{tipo_etapa}/buscar-pedido', [AdquisicionController::class, 'buscarPedido']);

            /** RUTAS MANO DE OBRA */
            Route::get('/{tipo_adquisicion}/mano-obra/{tipo_etapa}', [ManoObraController::class, 'index'])->name('mano.obra');
            Route::get('/{tipo_adquisicion}/mano-obra/{tipo_etapa}/nuevo', [ManoObraController::class, 'create'])->name('mano.obra.create');
            Route::get('/{tipo_adquisicion}/mano-obra/{tipo_etapa}/edit/{mano_obra}', [ManoObraController::class, 'create'])->name('mano.obra.edit');
            Route::get('/{tipo_adquisicion}/mano-obra/{tipo_etapa}/agergar-trabajadores/{mano_obra}', [ManoObraController::class, 'registroTrabajadores'])->name('mano.obra.agregar.trabajadores');
            Route::get('/{tipo_adquisicion}/mano-obra/{tipo_etapa}/editar-planificacion-trabajadores/{mano_obra}/{fecha}', [ManoObraController::class, 'editarPlanificacionTrabajadores']);
            Route::get('/{tipo_adquisicion}/mano-obra/{tipo_etapa}/proveedor-articulos', [ManoObraController::class, 'proveedorArticulos']); // ajax provedor_articulos
            Route::get('/{tipo_adquisicion}/mano-obra/{tipo_etapa}/fechas-planificacion', [ManoObraController::class, 'fechasDetalleManoObra']); // ajax fechasDetalleManoObra
            Route::post('/{tipo_adquisicion}/{tipo_etapa}/crear-planificacion', [ManoObraController::class, 'storePlanificacion']); // ajax post crear planificacion
            Route::put('/{tipo_adquisicion}/{tipo_etapa}/actualizar-planificacion/{mano_obra}', [ManoObraController::class, 'updatePlanificacion']);
            Route::get('/{tipo_adquisicion}/mano-obra/{tipo_etapa}/buscar-planificacion', [ManoObraController::class, 'buscarPlanificacion']);
            Route::post('/{tipo_adquisicion}/mano-obra/{tipo_etapa}/guardar/{mano_obra}', [ManoObraController::class, 'store'])->name('mano.obra.store');
            Route::delete('/{tipo_adquisicion}/mano-obra/{tipo_etapa}/eliminar-mano-obra/{mano_obra}', [ManoObraController::class, 'destroy']);
            Route::get('/{tipo_adquisicion}/mano-obra/{tipo_etapa}/actividad-mano-obra-acabados', [ManoObraController::class, 'getAjaxActividadAcabados']);
            /** RUTAS CONTRATISTA */
            Route::get('/{tipo_adquisicion}/contratista/{tipo_etapa}', [ContratistaController::class, 'index'])->name('contratista');
            Route::get('/{tipo_adquisicion}/contratista/{tipo_etapa}/nueva-orden-trabajo', [ContratistaController::class, 'crearOrdenTrabajo'])->name('contratista.crear.orden.trabajo');
            Route::post('/{tipo_adquisicion}/contratista/{tipo_etapa}/guardar-orden-trabajo', [ContratistaController::class, 'storeOrdenTrabajo'])->name('contratista.guardar.orden.trabajo');
            Route::get('/{tipo_adquisicion}/contratista/{tipo_etapa}/editar-orden-trabajo/{contratista}', [ContratistaController::class, 'editarOrdenTrabajo'])->name('contratista.editar.orden.trabajo');
            Route::put('/{tipo_adquisicion}/contratista/{tipo_etapa}/actualizar-orden-trabajo/{contratista}', [ContratistaController::class, 'updateOrdenTrabajo'])->name('contratista.update.orden.trabajo');
            /** RUTAS PAGOS */
            Route::get('/{tipo_adquisicion}/contratista/{tipo_etapa}/pagos-orden-trabajo/{contratista}', [ContratistaController::class, 'pagosOrdenTrabajo'])->name('contratista.pagos.orden.trabajo');
            Route::get('/{tipo_adquisicion}/contratista/{tipo_etapa}/nuevo-pago-orden-trabajo/{contratista}', [ContratistaController::class, 'nuevoPagoOrdenTrabajo'])->name('contratista.nuevo.pago.orden.trabajo');
            Route::post('/{tipo_adquisicion}/contratista/{tipo_etapa}/guardar-pago-orden-trabajo/{contratista}', [ContratistaController::class, 'guardarPagoOrdenTrabajo'])->name('contratista.guardar.pago.orden.trabajo');
            Route::get('/{tipo_adquisicion}/contratista/{tipo_etapa}/orden-trabajo/buscar', [ContratistaController::class, 'buscarPagoOrdenTrabajo']);
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

        // Configuraciones
        Route::group(['prefix' => 'configuraciones', 'as' => 'config.'], function () {
            Route::get('/', [ConfiguracionController::class, 'index'])->name('index');
            Route::get('/configuraciones/detalle/{config}', [ConfiguracionController::class, 'detalle'])->name('detalle');
        });
    });
    // fin rutas sistema

    /**
     * Rutas Modulos Administrativo
     */
    Route::group(['prefix' => 'administrativo', 'as' => 'administrativo.'], function () {
        Route::get('/', [AdministrativoController::class, 'index'])->name('index');
        Route::get('/construccion', [AdministrativoController::class, 'menuConstruccion'])->name('menu.construccion');

        Route::get('/adquisiciones/{tipo}', [AdministrativoController::class, 'adquisiciones'])->name('adquisiciones');
        Route::get('/adquisicion/{tipo}/{adquisicion}/editar', [AdministrativoController::class, 'editarAdquisicion'])->name('adquisicion.edit');
        Route::get('/adquisicion/{tipo}/{adquisicion}/recepcion', [AdministrativoController::class, 'recepcionAdquisicionAdministrativo'])->name('adquisicion.recepcion');
        Route::put('/adquisicion/{tipo}/{adquisicion}', [AdministrativoController::class, 'actualizarAdquisicion'])->name('adquisicion.update');
        Route::put('/recepcion/{tipo}/{adquisicion}', [AdministrativoController::class, 'createRecepcionAdministrativo'])->name('recepcion.create');
        Route::get('/adquisiciones/{tipo}/crear', [AdquisicionController::class, 'nuevaAdquisicionAdministrativo'])->name('adquisiciones.create');
        Route::post('/adquisiciones/{tipo}/guardar', [AdquisicionController::class, 'storeAdquisicionAdministrativo'])->name('adquisiciones.store');
        Route::get('/adquisiciones/{tipo}/buscar-adquisicion', [AdquisicionController::class, 'buscarAdquisicionAdministrativo']);

        Route::get('/contratistas', [AdministrativoController::class, 'indexContratistas'])->name('index.contratistas');
        Route::get('/contratistas/detalle/{contratista}', [AdministrativoController::class, 'detalleContratistas'])->name('contratista.detalle');
        Route::get('/buscar-orden-trabajo', [AdministrativoController::class, 'buscarOrdenTrabajo']);
        /** MANO DE OBRA ADMINSTRATIVO */
        Route::get('/mano-de-obra', [AdministrativoController::class, 'indexManoObra'])->name('index.mano.obra');
        Route::get('/mano-de-obra/detalle/{mano_obra}/{estado}', [AdministrativoController::class, 'detalleManoObra'])->name('mano.obra.detalle');
        Route::post('/mano-de-obra/registrar-pago', [AdministrativoController::class, 'registrarPagoManoObra'])->name('mano.obra.registrar.pago');
        Route::get('/buscar-mano-obra', [AdministrativoController::class, 'buscarManoObra']);


        //**RUTAS PRESTAMOS **
        Route::group(['prefix' => 'prestamos', 'as' => 'prestamos.'], function () {
            Route::get('/', [PrestamoController::class, 'index'])->name('index');
            Route::post('/nuevo', [PrestamoController::class, 'create']);
            Route::put('/actualizar/{prestamo}', [PrestamoController::class, 'updatePrestamo']);
            Route::get('/detalle-prestamo/{prestamo}', [PrestamoController::class, 'detallePrestamo'])->name('detalle.prestamo');
            Route::put('/pago/{pago}', [PrestamoController::class, 'registrarPago']);
            Route::post('/recalcular_pagos', [PrestamoController::class, 'recalcularPagos']);
            Route::post('/posponer-pago', [PrestamoController::class, 'posponerPago']);
            Route::get('/buscar-prestamo', [PrestamoController::class, 'buscar']);
        });
    });
    //** PETICIONES AJAX **
    Route::put('/pago_orden_trabajo/{pago}', [AdministrativoController::class, 'pagoOrdenTrabajo']);

    // Controllador para generar los pdf del sistema
    Route::group(['prefix' => 'generar-pdf', 'as' => 'pdf.'], function () {
        Route::get('/adquisicion-pdf/{pedido}', [GenerarPdfController::class, 'generarPdfPedido'])->name('adquisicion');
        Route::get('/recepcion-pdf/{pedido}', [GenerarPdfController::class, 'generarPdfRecepcion'])->name('recepcion');
        Route::get('/mano-obra-pdf/{mano_obra}', [GenerarPdfController::class, 'planificacionManoObraPDF'])->name('planificacion.mano.obra');
        Route::get('/orden-trabajo-contratista-pdf/{orden_trabajo}', [GenerarPdfController::class, 'ordenTrabajoContratistaPDF'])->name('orden.trabajo.contratista');
    });

    Route::get('/bancos', [CatalogoDatoController::class, 'getBancos']);
    Route::get('/tipo-cuenta', [CatalogoDatoController::class, 'getTipoCuentas']);
    Route::get('/articulos-proveedor', [ArticuloController::class, 'getArticulosProveedor']);
    Route::delete('/orden-trabajo/eliminar/{id}', [ContratistaController::class, 'eliminarOrdenTrabajo']);
    Route::get('/proveedores', [ProveedorController::class, 'getProveedores']);
    Route::get('/forma-pago-prestamo', [CatalogoDatoController::class, 'getFormasPagoPrestamo']);

    Route::get('/no-access', function () {
        return view('errors.no-access');
    })->name('no.access');
});
