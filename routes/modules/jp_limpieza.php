<?php

use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'jp-limpieza', 'as' => 'jp.limpieza.'], function () {
    Route::get('/', function () {
        $breadcrumbs = [
            ['name' => 'Inicio', 'url' => route('home')],
            ['name' => 'Limpieza y mantenimiento', 'url' => ''],
        ];
        return view('jp_limpieza.home', compact('breadcrumbs'));
    })->name('index');
});
