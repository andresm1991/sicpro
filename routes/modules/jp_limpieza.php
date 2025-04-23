<?php

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
});
