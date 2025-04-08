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

    Route::get('/resumen-pago-semanal-pdf/{resumen}', [GenerarPdfController::class, 'pdfResumenPagoSemanal'])->name('resumen.pago.semanal');
    Route::post('/reporte/{tipo_reporte}', [GenerarPdfController::class, 'reportAdquisiciones'])->name('reporte.adquisiciones');
    Route::get('/exportar-tareas', [GenerarPdfController::class, 'exportarTareas'])->name('export.tareas');
});
