<?php

use App\Http\Controllers\JPLimpieza\AdquisicionController;
use App\Http\Controllers\JPLimpieza\ProyectoController;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'jp-limpieza', 'as' => 'jp.limpieza.'], function () {
    Route::get('/', function () {
        $breadcrumbs = [
            ['name' => 'Inicio', 'url' => route('home')],
            ['name' => 'Limpieza y mantenimiento', 'url' => ''],
        ];
        return view('jp_limpieza.home', compact('breadcrumbs'));
    })->name('index');

    Route::get('/proyectos', [ProyectoController::class, 'index'])->name('proyectos.index');
    //->middleware('can:jp.limpieza.proyectos.index');
    Route::get('/nuevo-proyecto', [ProyectoController::class, 'create'])->name('proyectos.create');
    Route::post('/guardar-proyecto', [ProyectoController::class, 'store'])->name('proyectos.store');

    Route::group(['prefix' => 'proyecto', 'as' => 'proyectos.'], function () {
        Route::get('/{proyecto}', [ProyectoController::class, 'show'])->name('show');
        Route::get('/{proyecto}/editar', [ProyectoController::class, 'edit'])->name('edit');
        Route::put('/{proyecto}', [ProyectoController::class, 'update'])->name('update');
        Route::delete('/{id}', [ProyectoController::class, 'destroy']);
    });


    Route::group(['prefix' => 'adquisiciones', 'as' => 'adquisiciones.'], function () {
        Route::get('/{proyecto}', [AdquisicionController::class, 'index'])->name('index');
        Route::get('/{proyecto}/{tipo_adquisicion}', [AdquisicionController::class, 'adquisiciones'])->name('tipo.adquisicion');
        Route::get('/{proyecto}/{tipo_adquisicion}/nuevo', [AdquisicionController::class, 'create'])->name('create');
        Route::post('/{proyecto}/{tipo_adquisicion}/guardar', [AdquisicionController::class, 'store'])->name('store');
        Route::get('/{proyecto}/{tipo_adquisicion}/editar/{adquisicion}', [AdquisicionController::class, 'edit'])->name('edit');
        Route::put('/{proyecto}/{tipo_adquisicion}/editar/{adquisicion}', [AdquisicionController::class, 'update'])->name('update');
        Route::delete('/{proyecto}/eliminar/{adquisicion}', [AdquisicionController::class, 'destroy'])->name('destroy');
        Route::get('/{proyecto}/{tipo_adquisicion}/buscar', [AdquisicionController::class, 'buscar']);
    });

    Route::group(['prefix' => 'contratistas', 'as' => 'contratista.'], function () {});
});
