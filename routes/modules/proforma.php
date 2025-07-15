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
    Route::post('/{tipo}/guardar', [ProformaController::class, 'store'])->name('store');
    Route::get('/editar/{tipo}/{proforma}', [ProformaController::class, 'edit'])->name('edit');
    Route::put('/actualizar/{tipo}/{proforma}', [ProformaController::class, 'update'])->name('update');
    Route::delete('/eliminar/{tipo}/{proforma}', [ProformaController::class, 'destroy'])->name('destroy');
    Route::get('/buscar', [ProformaController::class, 'buscar'])->name('buscar');
});