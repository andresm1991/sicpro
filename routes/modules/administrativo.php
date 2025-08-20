<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PrestamoController;
use App\Http\Controllers\AdquisicionController;
use App\Http\Controllers\AdministrativoController;
use App\Http\Controllers\ResumenPagoSemanalController;

/**
 * Rutas Modulos Administrativo
 */
Route::group(['prefix' => 'administrativo', 'as' => 'administrativo.'], function () {
    Route::get('/', [AdministrativoController::class, 'index'])->name('index');
    Route::get('/construccion', [AdministrativoController::class, 'menuConstruccion'])->name('menu.construccion');

    Route::get('/adquisiciones/{tipo}', [AdministrativoController::class, 'adquisiciones'])->name('adquisiciones');
    Route::get('/adquisicion/{tipo}/{adquisicion}/editar', [AdministrativoController::class, 'editarAdquisicion'])->name('adquisicion.edit');
    Route::get('/adquisicion/{tipo}/{adquisicion}/recepcion', [AdministrativoController::class, 'recepcionAdquisicionAdministrativo'])->name('adquisicion.recepcion');
    Route::put('/adquisicion/{tipo}/{adquisicion}', [AdministrativoController::class, 'actualizarAdquisicion'])->name('adquisicion.update');
    Route::put('/recepcion/{tipo}/{adquisicion}', [AdministrativoController::class, 'createRecepcionAdministrativo'])->name('recepcion.create');
    Route::get('/adquisiciones/{tipo}/crear', [AdquisicionController::class, 'nuevaAdquisicionAdministrativo'])->name('adquisiciones.create');
    Route::post('/adquisiciones/{tipo}/guardar', [AdquisicionController::class, 'storeAdquisicionAdministrativo'])->name('adquisiciones.store');
    Route::get('/adquisiciones/{tipo}/buscar-adquisicion', [AdquisicionController::class, 'buscarAdquisicionAdministrativo']);
    Route::put('/adquisicion/agregar-producto/{adquisicion}', [AdministrativoController::class, 'agergarProductoAdquisicion']);
    Route::delete('/adquisiciones/eliminar/{adquisicionId}', [AdministrativoController::class, 'destroyPedido']);


    Route::get('/contratistas', [AdministrativoController::class, 'indexContratistas'])->name('index.contratistas');
    Route::get('/contratistas/detalle/{contratista}', [AdministrativoController::class, 'detalleContratistas'])->name('contratista.detalle');
    Route::get('/buscar-orden-trabajo', [AdministrativoController::class, 'buscarOrdenTrabajo']);
    /** MANO DE OBRA ADMINSTRATIVO */
    Route::get('/mano-de-obra', [AdministrativoController::class, 'indexManoObra'])->name('index.mano.obra');
    Route::get('/mano-de-obra/detalle/{mano_obra}/{estado}', [AdministrativoController::class, 'detalleManoObra'])->name('mano.obra.detalle');
    Route::post('/mano-de-obra/registrar-pago', [AdministrativoController::class, 'registrarPagoManoObra'])->name('mano.obra.registrar.pago');
    Route::get('/buscar-mano-obra', [AdministrativoController::class, 'buscarManoObra']);


    //**RUTAS PRESTAMOS **
    Route::group(['prefix' => 'prestamos', 'as' => 'prestamos.'], function () {
        Route::get('/', [PrestamoController::class, 'index'])->name('index');
        Route::post('/nuevo', [PrestamoController::class, 'create']);
        Route::put('/actualizar/{prestamo}', [PrestamoController::class, 'updatePrestamo']);
        Route::get('/detalle-prestamo/{prestamo}', [PrestamoController::class, 'detallePrestamo'])->name('detalle.prestamo');
        Route::put('/pago/{pago}', [PrestamoController::class, 'registrarPago']);
        Route::post('/recalcular_pagos', [PrestamoController::class, 'recalcularPagos']);
        Route::post('/posponer-pago', [PrestamoController::class, 'posponerPago']);
        Route::get('/buscar-prestamo', [PrestamoController::class, 'buscar']);
    });

    //** RUTAS RESUMEN DE PAGOS SEMANAL */
    Route::group(['prefix' => 'resumen-pagos-semanales', 'as' => 'resumen.pagos.semanal.'], function () {
        Route::get('/', [ResumenPagoSemanalController::class, 'index'])->name('index');
        Route::post('/guardar', [ResumenPagoSemanalController::class, 'store']);
        Route::get('/editar/{id}', [ResumenPagoSemanalController::class, 'edit']);
        Route::delete('/eliminar/{id}', [ResumenPagoSemanalController::class, 'destroy']);
        Route::get('/buscar-resumen-pagos', [ResumenPagoSemanalController::class, 'buscar']);
    });

    require base_path('routes/modules/caja.php');
});
