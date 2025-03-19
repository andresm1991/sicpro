<?php

use App\Models\CatalogoDato;
use App\Models\AdquisicionDetalle;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\SistemaController;
use App\Http\Controllers\ArticuloController;
use App\Http\Controllers\ManoObraController;
use App\Http\Controllers\PrestamoController;
use App\Http\Controllers\ProyectoController;
use App\Http\Controllers\ProveedorController;
use App\Http\Controllers\GenerarPdfController;
use App\Http\Controllers\InventarioController;
use App\Http\Controllers\AdquisicionController;
use App\Http\Controllers\ContratistaController;
use App\Http\Controllers\CatalogoDatoController;
use App\Http\Controllers\ConfiguracionController;
use App\Http\Controllers\AdministrativoController;
use App\Http\Controllers\CronogramaController;
use App\Http\Controllers\PresupuestoController;
use App\Http\Controllers\PushNotificationController;
use App\Http\Controllers\RecuperacionTiempoController;
use App\Http\Controllers\ReporteriaController;
use App\Http\Controllers\ResumenPagoSemanalController;
use App\Http\Controllers\RubroController;
use App\Http\Controllers\SolicitudController;
use App\Http\Controllers\TareaController;
use App\Models\PresupuestoProyecto;

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

        //** RUTAS PRESUPUESTO */
        Route::group(['prefix' => '/presupuesto', 'as' => 'presupuesto.'], function () {
            Route::get('/{proyecto}/home', [PresupuestoController::class, 'index'])->name('index');
            Route::post('{proyecto}/store', [PresupuestoController::class, 'store']);
            Route::put('{proyecto}/actualizar-rubro-presupuesto/{rubroPresupuesto}', [PresupuestoController::class, 'putAjaxRubroPresupuesto']);
        });

        //** RUTAS CRONOGRAMA VALORADO */
        Route::group(['prefix' => '/cronograma', 'as' => 'cronograma.'], function () {
            Route::get('/{proyecto}/home', [CronogramaController::class, 'index'])->name('index');
            Route::get('/{proyecto}/semana/{semana}', [CronogramaController::class, 'editarActividadesSemana'])->name('actividades.semana');
            Route::post('/{proyecto}/semana/{semana}/rubro/{rubro}', [CronogramaController::class, 'storeActividadesDiaSemana'])->name('store.actividad.dias.semana');
            Route::get('/{proyecto}/semana/{semana}/rubro/{rubro}/editar', [CronogramaController::class, 'editarCronogramaDiaSemana'])->name('edit.actividad.dia.semana');
            Route::put('/{proyecto}/semana/{semana}', [CronogramaController::class, 'updateActividadesDiaSemana'])->name('update.actividades.semana');

            Route::post('{proyecto}/guardar/rubro-cronograma', [CronogramaController::class, 'ajaxStoreRubrosCronograma']);
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

        //** RUTAS RESUMEN DE PAGOS SEMANAL */
        Route::group(['prefix' => 'resumen-pagos-semanales', 'as' => 'resumen.pagos.semanal.'], function () {
            Route::get('/', [ResumenPagoSemanalController::class, 'index'])->name('index');
            Route::post('/guardar', [ResumenPagoSemanalController::class, 'store']);
            Route::get('/editar/{id}', [ResumenPagoSemanalController::class, 'edit']);
            Route::delete('/eliminar/{id}', [ResumenPagoSemanalController::class, 'destroy']);
            Route::get('/buscar-resumen-pagos', [ResumenPagoSemanalController::class, 'buscar']);
        });
    });

    //** RUTAS SOLICITUDES */
    Route::group(['prefix' => 'solicitudes', 'as' => 'solicitud.'], function () {
        Route::get('/', [SolicitudController::class, 'index'])->name('index');
        Route::group(['prefix' => 'permisos', 'as' => 'permisos.'], function () {
            Route::get('/', [SolicitudController::class, 'permisos'])->name('index');
            Route::get('/nueva-solicitud', [SolicitudController::class, 'create'])->name('create');
            Route::post('/guardar-solicitud', [SolicitudController::class, 'store'])->name('store');
            Route::get('/{solicitud}/detalle', [SolicitudController::class, 'show'])->name('show');
            Route::get('/{solicitud}/editar', [SolicitudController::class, 'edit'])->name('edit');
            Route::put('/{solicitud}', [SolicitudController::class, 'update'])->name('update');
            Route::delete('/eliminar-solicitud/{solicitud}', [SolicitudController::class, 'destroy']);
            Route::get('/buscar', [SolicitudController::class, 'buscar']);
        });
        //** RUTAS REPOSICIONE DE TIEMPO */
        Route::group(['prefix' => 'reposicion', 'as' => 'reposicion.'], function () {
            Route::get('/', [RecuperacionTiempoController::class, 'index'])->name('index');
            Route::get('/nueva-solicitud', [RecuperacionTiempoController::class, 'create'])->name('create');
            Route::post('/guardar-solicitud', [RecuperacionTiempoController::class, 'store'])->name('store');
            Route::get('/{solicitud}/detalle', [RecuperacionTiempoController::class, 'show'])->name('show');
            Route::get('/{solicitud}/editar', [RecuperacionTiempoController::class, 'edit'])->name('edit');
            Route::put('/{solicitud}', [RecuperacionTiempoController::class, 'update'])->name('update');
            Route::delete('/eliminar-solicitud/{solicitud}', [RecuperacionTiempoController::class, 'destroy']);
            Route::get('/buscar', [RecuperacionTiempoController::class, 'buscar']);
        });

        //** RUTAS AUTORIZACIONES DE SOLICITUD EDICIONES ADQUISICIONES, ETC */
        Route::group(['prefix' => 'autorizaciones', 'as' => 'autorizacion.'], function () {
            Route::get('/', [RecuperacionTiempoController::class, 'index'])->name('index');
            Route::get('/nueva-solicitud', [RecuperacionTiempoController::class, 'create'])->name('create');
            Route::post('/guardar-solicitud', [RecuperacionTiempoController::class, 'store'])->name('store');
            Route::get('/{solicitud}/detalle', [RecuperacionTiempoController::class, 'show'])->name('show');
            Route::get('/{solicitud}/editar', [RecuperacionTiempoController::class, 'edit'])->name('edit');
            Route::put('/{solicitud}', [RecuperacionTiempoController::class, 'update'])->name('update');
            Route::delete('/eliminar-solicitud/{solicitud}', [RecuperacionTiempoController::class, 'destroy']);
            Route::get('/buscar', [RecuperacionTiempoController::class, 'buscar']);
        });
    });

    //** RUTAS AGENDA */
    Route::group(['prefix' => 'agenda', 'as' => 'tarea.'], function () {
        Route::get('/', [TareaController::class, 'index'])->name('index');
        Route::post('/guardar-tarea', [TareaController::class, 'store'])->name('store');
        Route::post('/guardar-comentario', [TareaController::class, 'storeComentario']);
        Route::get('/comentarios-tarea', [TareaController::class, 'getComentariosTarea']);
        Route::put('/comentarios-tarea/{comentarioId}', [TareaController::class, 'updateComentario']);
        Route::delete('/eliminar-comentario/{comentario}', [TareaController::class, 'deleteComentario']);
        Route::delete('/eliminar-tarea/{tarea}', [TareaController::class, 'deleteTarea']);
        Route::put('/actualizar-estado-tarea/{tarea}', [TareaController::class, 'updateEstadoTarea']);
    });

    //**  RUTAS REPORTES */
    Route::group(['prefix' => 'reportes', 'as' => 'reporte.'], function () {
        Route::get('/', [ReporteriaController::class, 'index'])->name('index');
        Route::get('/adquisiciones', [ReporteriaController::class, 'reporteAdquisiciones'])->name('adquisiciones');
    });

    //** PETICIONES AJAX **
    Route::put('/pago_orden_trabajo/{pago}', [AdministrativoController::class, 'pagoOrdenTrabajo']);
    Route::get('/rubros-presupuesto', [PresupuestoController::class, 'getAjaxRubrosPresupuesto']);
    Route::put('/actualizar-costo-indirecto/{proyecto}', [PresupuestoController::class, 'putAjaxCostoIndirecto']);
    Route::get('/actividades-cronograma', [CronogramaController::class, 'getAjaxActividades']);
    Route::get('/tiempo-permisos', [RecuperacionTiempoController::class, 'getAjaxTiempoPermisosUsuario']);

    // Eliminar  rubros del presupuesto
    Route::delete('/eliminar-rubro-presupesto/{rubro}', [PresupuestoController::class, 'destroyAjaxRubroPresupuesto']);
    Route::delete('/eliminar-categoria-presupesto/{categoria}', [PresupuestoController::class, 'destroyAjaxCategoriaPresupuesto']);
    Route::get('/filtrar-rubros-presupuesto', [PresupuestoController::class, 'filtrarRubrosPresupuesto']);
    //** RUTAS GENERAR PDF */
    Route::group(['prefix' => 'generar-pdf', 'as' => 'pdf.'], function () {
        Route::get('/adquisicion-pdf/{pedido}', [GenerarPdfController::class, 'generarPdfPedido'])->name('adquisicion');
        Route::get('/recepcion-pdf/{pedido}', [GenerarPdfController::class, 'generarPdfRecepcion'])->name('recepcion');
        Route::get('/mano-obra-pdf/{mano_obra}', [GenerarPdfController::class, 'planificacionManoObraPDF'])->name('planificacion.mano.obra');
        Route::get('/orden-trabajo-contratista-pdf/{orden_trabajo}', [GenerarPdfController::class, 'ordenTrabajoContratistaPDF'])->name('orden.trabajo.contratista');
        Route::get('/exportar-presupuesto-pdf/{proyecto}', [GenerarPdfController::class, 'exportarPresupuestoPDFD'])->name('export.presupuesto');
        Route::get('/exportar-cronograma-pdf/{proyecto}', [GenerarPdfController::class, 'exportarCronogramaToPDF'])->name('export.cronograma');
        Route::get('/exportar-cronograma-actividades-dias-pdf/{proyecto}/{semana}', [GenerarPdfController::class, 'exportarActividadesDiasCronogramaToPDF'])->name('export.cronograma.actividades.dias');

        Route::get('/resumen-pago-semanal-pdf/{resumen}', [GenerarPdfController::class, 'pdfResumenPagoSemanal'])->name('resumen.pago.semanal');
    });
    //** FIN RUTAS GENERAR PDF */
    Route::get('/bancos', [CatalogoDatoController::class, 'getBancos']);
    Route::get('/tipo-cuenta', [CatalogoDatoController::class, 'getTipoCuentas']);
    Route::get('/articulos-proveedor', [ArticuloController::class, 'getArticulosProveedor']);
    Route::delete('/orden-trabajo/eliminar/{id}', [ContratistaController::class, 'eliminarOrdenTrabajo']);
    Route::get('/proveedores', [ProveedorController::class, 'getProveedores']);
    Route::get('/forma-pago-prestamo', [CatalogoDatoController::class, 'getFormasPagoPrestamo']);

    // Start Push Notification==========================================================
    Route::view('push-notification', 'PushNotification.Index');
    Route::post('save-push-notification-sub', [PushNotificationController::class, 'saveSubscription']);
    Route::post('send-push-notification', [PushNotificationController::class, 'sendNotification']);
    // End Push Notification==========================================================


    //** RUTAS NOTIFICACION */
    Route::get('/orde-pago-mano-obra/{mano_obra}/{pago}/view', [GenerarPdfController::class, 'planificacionManoObraPDF'])->name('pago.mano.obra');
    //** FIN RUTAS NOTIFICACIONES */

    Route::get('/no-access', function () {
        return view('errors.no-access');
    })->name('no.access');
});