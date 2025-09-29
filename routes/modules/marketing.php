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

    Route::get('/seguimiento-ventas', [SeguimientoVentaController::class, 'index'])->name('seguimiento.ventas.index');
    Route::get('/seguimiento-ventas/create', [SeguimientoVentaController::class, 'create'])->name('seguimiento.ventas.create');
    Route::post('/seguimiento-ventas', [SeguimientoVentaController::class, 'store'])->name('seguimiento.ventas.store');
});
