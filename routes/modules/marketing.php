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
    });
});
