<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ReporteriaController;

Route::group(['prefix' => 'reportes', 'as' => 'reporte.'], function () {
    Route::get('/', [ReporteriaController::class, 'index'])->name('index');
    Route::get('/adquisiciones', [ReporteriaController::class, 'reporteAdquisiciones'])->name('adquisiciones');
    Route::get('/datos-filtro-reporte-adquisiciones', [ReporteriaController::class, 'filtroReportesAdquisiciones']);
    Route::post('/visulizar-reporte-adquisiciones', [ReporteriaController::class, 'visualizarReporteAdquisiciones']);

    Route::get('/gasolina-camioneta', [ReporteriaController::class, 'reporteGasolinaCamioneta'])->name('gasolina.camioneta');
    Route::post('/visulizar-reporte-gasolina', [ReporteriaController::class, 'visualizarReporteGasolinaCamioneta']);
});
