<?php

use App\Http\Controllers\CajaController;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'caja', 'as' => 'caja.'], function () {
    Route::get('/', [CajaController::class, 'index'])->name('index');
    Route::post('/guardar', [CajaController::class, 'guardarMovimiento'])->name('store');
    Route::delete('/eliminar-movimiento/{id}', [CajaController::class, 'eliminarMovimiento']);
});
