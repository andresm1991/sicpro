<?php

use App\Http\Controllers\VentaPropiedadController;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'gerencia', 'as' => 'gerencia.'], function () {
    Route::get('/', function () {
        $title_page = 'Gerencia';
        $breadcrumbs = [
            ['name' => 'Inicio', 'url' => route('home')],
            ['name' => 'Gerencia', 'url' => '']
        ];
        return view('gerencia.index', compact('title_page', 'breadcrumbs'));
    })->name('index');


    //** PROPIEDADES */
    Route::group(['prefix' => 'propiedades', 'as' => 'propiedades.'], function () {
        Route::get('/historial-de-propiedades', [VentaPropiedadController::class, 'index'])->name('index');
        Route::get('/nueva-propiedad', [VentaPropiedadController::class, 'create'])->name('create');
        Route::post('/guardar-propiedad', [VentaPropiedadController::class, 'store'])->name('store');
        Route::get('/editar-propiedad/{propiedad}', [VentaPropiedadController::class, 'edit'])->name('edit');
        Route::put('/actualizar-propiedad/{propiedad}', [VentaPropiedadController::class, 'update'])->name('update');
        Route::delete('/eliminar-propiedad/{propiedad}', [VentaPropiedadController::class, 'destroy'])->name('destroy');
        Route::get('/buscar-propiedad', [VentaPropiedadController::class, 'buscar']);
    });
});