<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SolicitudController;
use App\Http\Controllers\RecuperacionTiempoController;

Route::group(['prefix' => 'solicitudes', 'as' => 'solicitud.'], function () {
    Route::get('/', [SolicitudController::class, 'index'])->name('index');
    Route::group(['prefix' => 'permisos', 'as' => 'permisos.'], function () {
        Route::get('/', [SolicitudController::class, 'permisos'])->name('index');
        Route::get('/nueva-solicitud', [SolicitudController::class, 'create'])->name('create');
        Route::post('/guardar-solicitud', [SolicitudController::class, 'store'])->name('store');
        Route::get('/{solicitud}/detalle', [SolicitudController::class, 'show'])->name('show');
        Route::get('/{solicitud}/editar', [SolicitudController::class, 'edit'])->name('edit');
        Route::put('/{solicitud}', [SolicitudController::class, 'update'])->name('update');
        Route::delete('/eliminar-solicitud/{solicitud}', [SolicitudController::class, 'destroy']);
        Route::get('/buscar', [SolicitudController::class, 'buscar']);
    });
    //** RUTAS REPOSICIONE DE TIEMPO */
    Route::group(['prefix' => 'reposicion', 'as' => 'reposicion.'], function () {
        Route::get('/', [RecuperacionTiempoController::class, 'index'])->name('index');
        Route::get('/nueva-solicitud', [RecuperacionTiempoController::class, 'create'])->name('create');
        Route::post('/guardar-solicitud', [RecuperacionTiempoController::class, 'store'])->name('store');
        Route::get('/{solicitud}/detalle', [RecuperacionTiempoController::class, 'show'])->name('show');
        Route::get('/{solicitud}/editar', [RecuperacionTiempoController::class, 'edit'])->name('edit');
        Route::put('/{solicitud}', [RecuperacionTiempoController::class, 'update'])->name('update');
        Route::delete('/eliminar-solicitud/{solicitud}', [RecuperacionTiempoController::class, 'destroy']);
        Route::get('/buscar', [RecuperacionTiempoController::class, 'buscar']);
    });

    //** RUTAS AUTORIZACIONES DE SOLICITUD EDICIONES ADQUISICIONES, ETC */
    Route::group(['prefix' => 'autorizaciones', 'as' => 'autorizacion.'], function () {
        Route::get('/', [RecuperacionTiempoController::class, 'index'])->name('index');
        Route::get('/nueva-solicitud', [RecuperacionTiempoController::class, 'create'])->name('create');
        Route::post('/guardar-solicitud', [RecuperacionTiempoController::class, 'store'])->name('store');
        Route::get('/{solicitud}/detalle', [RecuperacionTiempoController::class, 'show'])->name('show');
        Route::get('/{solicitud}/editar', [RecuperacionTiempoController::class, 'edit'])->name('edit');
        Route::put('/{solicitud}', [RecuperacionTiempoController::class, 'update'])->name('update');
        Route::delete('/eliminar-solicitud/{solicitud}', [RecuperacionTiempoController::class, 'destroy']);
        Route::get('/buscar', [RecuperacionTiempoController::class, 'buscar']);
    });
});
