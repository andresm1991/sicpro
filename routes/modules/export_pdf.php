<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\GenerarPdfController;

Route::group(['prefix' => 'generar-pdf', 'as' => 'pdf.'], function () {
    Route::get('/adquisicion-pdf/{pedido}', [GenerarPdfController::class, 'generarPdfPedido'])->name('adquisicion');
    Route::get('/recepcion-pdf/{pedido}', [GenerarPdfController::class, 'generarPdfRecepcion'])->name('recepcion');
    Route::get('/mano-obra-pdf/{mano_obra}', [GenerarPdfController::class, 'planificacionManoObraPDF'])->name('planificacion.mano.obra');
    Route::get('/orden-trabajo-contratista-pdf/{orden_trabajo}', [GenerarPdfController::class, 'ordenTrabajoContratistaPDF'])->name('orden.trabajo.contratista');
    Route::get('/exportar-presupuesto-pdf/{proyecto}', [GenerarPdfController::class, 'exportarPresupuestoPDFD'])->name('export.presupuesto');
    Route::get('/exportar-cronograma-pdf/{proyecto}', [GenerarPdfController::class, 'exportarCronogramaToPDF'])->name('export.cronograma');
    Route::get('/exportar-cronograma-actividades-dias-pdf/{proyecto}/{semana}', [GenerarPdfController::class, 'exportarActividadesDiasCronogramaToPDF'])->name('export.cronograma.actividades.dias');

    Route::get('/pago-orden-trabajo-pdf/{pago}', [GenerarPdfController::class, 'pagoOrdenTrabajoPDF'])->name('pago.orden.trabajo');

    Route::get('/resumen-pago-semanal-pdf/{resumen}', [GenerarPdfController::class, 'pdfResumenPagoSemanal'])->name('resumen.pago.semanal');
    Route::post('/reporte/{tipo_reporte}', [GenerarPdfController::class, 'reportAdquisiciones'])->name('reporte.adquisiciones');
    Route::post('/reporte-gasolina/{tipo_reporte}', [GenerarPdfController::class, 'reportGaolinaCamioneta'])->name('reporte.gasolina.camnoneta');
    Route::get('/exportar-tareas', [GenerarPdfController::class, 'exportarTareas'])->name('export.tareas');

    Route::post('/reporte-solicitudes/{tipo_reporte}', [GenerarPdfController::class, 'reportSolicitudes'])->name('reporte.solicitudes');
    Route::post('/reporte-caja/{tipo_reporte}', [GenerarPdfController::class, 'reportCaja'])->name('reporte.caja');


    //** PROFORMAS */
    Route::get('/proformas-pdf/{tipo}/{id}', [GenerarPdfController::class, 'proformas'])->name('proformas');
    Route::get('/programa-arquitectonico-pdf/{programa}', [GenerarPdfController::class, 'programaArquitectonicoPDF'])->name('programa.arquitectonico');

    //** JPLIMPIEZA */
    Route::get('/presupuesto-jplimpieza/{proyecto}', [GenerarPdfController::class, 'presupuestoJPLimpiezaPDF'])->name('jp.limpieza.presupuesto');
    Route::get('/jp-limpieza/mano-obra/{mano_obra}', [GenerarPdfController::class, 'manoObraJPLimpiezaPDF'])->name('jp.limpieza.mano.obra');
    Route::get('/jp-limpieza/adquisicion-pdf/{adquisicion}', [GenerarPdfController::class, 'adquisicionJPLimpiezaPDF'])->name('jp.limpieza.adquisicion');
    Route::post('/jp-limpieza/reporte-caja/{tipo_reporte}', [GenerarPdfController::class, 'reportCajaJPLimpieza'])->name('reporte.jp.limpieza.caja');
});
