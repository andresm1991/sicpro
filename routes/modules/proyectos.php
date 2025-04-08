<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ManoObraController;
use App\Http\Controllers\ProyectoController;
use App\Http\Controllers\CronogramaController;
use App\Http\Controllers\AdquisicionController;
use App\Http\Controllers\ContratistaController;
use App\Http\Controllers\PresupuestoController;


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

        Route::get('/{tipo_adquisicion}/contratista/{tipo_etapa}/editar-pago-orden-trabajo/{pago_contratista}', [ContratistaController::class, 'editarPagoOrdenTrabajo'])->name('contratista.editar.pago.orden.trabajo');

        Route::post('/{tipo_adquisicion}/contratista/{tipo_etapa}/guardar-pago-orden-trabajo/{contratista}', [ContratistaController::class, 'guardarPagoOrdenTrabajo'])->name('contratista.guardar.pago.orden.trabajo');

        Route::put('/{tipo_adquisicion}/contratista/{tipo_etapa}/actualizar-pago-orden-trabajo/{pago_contratista}', [ContratistaController::class, 'actualizarPagoOrdenTrabajo'])->name('contratista.update.pago.orden.trabajo');

        Route::get('/{tipo_adquisicion}/contratista/{tipo_etapa}/orden-trabajo/buscar', [ContratistaController::class, 'buscarPagoOrdenTrabajo']);
        Route::delete('/{tipo_adquisicion}/contratista/{tipo_etapa}/eliminar-pago-orden-trabajo/{id}', [ContratistaController::class, 'eliminarPagoOrdenTrabajo']);
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
