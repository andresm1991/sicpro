<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Marketing\SeguimientoVentaController;

Route::group(['prefix' => 'marketing', 'as' => 'marketing.'], function () {
    Route::get('/', function () {
        $title_page = 'Marketing';
        $breadcrumbs = [
            ['name' => 'Inicio', 'url' => route('home')],
            ['name' => 'Marketing', 'url' => '']
        ];
        return view('marketing.index', compact('title_page', 'breadcrumbs'));
    })->name('index');

    Route::group(['prefix' => 'seguimiento-ventas', 'as' => 'seguimiento.ventas.'], function () {
        Route::get('/', [SeguimientoVentaController::class, 'index'])->name('index');
        Route::get('/create', [SeguimientoVentaController::class, 'create'])->name('create');
        Route::post('/etapa-reserva', [SeguimientoVentaController::class, 'storeEtapaReserva']);
        Route::post('/verificar-documento-identidad', [SeguimientoVentaController::class, 'verificarDocumentoIdentidad']);
        Route::get('/editar/{seguimiento}', [SeguimientoVentaController::class, 'editar'])->name('edit');
        Route::put('/actualizar-etapa-reserva/{seguimiento}', [SeguimientoVentaController::class, 'actualizarEtapaReserva']);
        Route::post('/agregar-item-documentacion/{proceso}', [SeguimientoVentaController::class, 'agregarItemDocumentacion'])->name('agregar.item.documentacion');
        Route::post('/agregar-item-escrituracion/{proceso}', [SeguimientoVentaController::class, 'agregarItemEscrituracion'])->name('agregar.item.escrituracion');
        Route::patch('/documentacion-items/{item}', [SeguimientoVentaController::class, 'updateDocumentoItem'])->name('documentacion.items.update');
        Route::delete('/documentacion-items/{item}', [SeguimientoVentaController::class, 'destroyDocumentoItem'])->name('documentacion.items.destroy');
        Route::patch('/escrituracion-items/{item}', [SeguimientoVentaController::class, 'updateEscrituracionItem'])->name('escrituracion.items.update');
        Route::delete('/escrituracion-items/{item}', [SeguimientoVentaController::class, 'destroyEscrituracionItem'])->name('escrituracion.items.destroy');
        Route::post('/etapa-peritaje-aprobacion/{proceso}', [SeguimientoVentaController::class, 'storeEtapaPeritajeAprobacion'])->name('etapa.peritaje.aprobacion');
        Route::post('/etapa-desembolso/{proceso}', [SeguimientoVentaController::class, 'storeEtapaDesembolso'])->name('etapa.desembolso');

        Route::post('/guardar-valor-saldo-reserva', [SeguimientoVentaController::class, 'storeValorSaldoReserva']);
        Route::patch('/actualizar-etapa/{proceso}', [SeguimientoVentaController::class, 'actualizaEtapa']);
    });
});