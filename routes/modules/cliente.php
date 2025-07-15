<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ClienteController;

Route::group(['prefix' => 'clientes', 'as' => 'clientes.'], function () {
    Route::get('/', [ClienteController::class, 'index'])->name('index');
    Route::get('/nuevo', [ClienteController::class, 'create'])->name('create');
    Route::post('/guardar', [ClienteController::class, 'store'])->name('store');
    Route::get('/editar/{cliente}', [ClienteController::class, 'edit'])->name('edit');
    Route::put('/actualizar/{cliente}', [ClienteController::class, 'update'])->name('update');
    Route::delete('/eliminar/{cliente}', [ClienteController::class, 'destroy']);
    Route::get('/buscar', [ClienteController::class, 'buscar']);
});