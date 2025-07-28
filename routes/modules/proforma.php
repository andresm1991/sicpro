<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProformaController;
use App\Http\Controllers\VentaPropiedadController;

Route::group(['prefix' => 'proformas', 'as' => 'proformas.'], function () {
    Route::get('/', function () {
        $title_page = 'Proformas';
        $breadcrumbs = [
            ['name' => 'Inicio', 'url' => route('home')],
            ['name' => 'Proformas', 'url' => '']
        ];
        return view('proformas.tipo', compact('title_page', 'breadcrumbs'));
    })->name('index');

    Route::get('{tipo}/', [ProformaController::class, 'index'])->name('tipo');
    Route::get('/{tipo}/nuevo', [ProformaController::class, 'create'])->name('create');
    Route::post('/adecentamientos/guardar', [ProformaController::class, 'storeAdecentamiento'])->name('store.adecentamientos');
    Route::post('/diseno_planos/guardar', [ProformaController::class, 'storePlanos'])->name('store.planos');
    Route::get('/editar/{tipo}/{id}', [ProformaController::class, 'edit'])->name('edit');
    Route::put('/actualizar/adecentamientos/{id}', [ProformaController::class, 'updateAdecentamientos'])->name('update.adecentamientos');
    Route::put('/actualizar/diseno_planos/{id}', [ProformaController::class, 'updatePlanos'])->name('update.planos');
    Route::delete('/eliminar/{tipo}/{id}', [ProformaController::class, 'destroy']);
    Route::get('/buscar/{tipo}', [ProformaController::class, 'buscar'])->name('buscar');

    Route::get('/{tipo}/{id}/programa-arquitectonico', [ProformaController::class, 'buscar'])->name('programa.arquitectonico');
});
