<?php

use App\Http\Controllers\CajaController;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'caja', 'as' => 'caja.'], function () {
    Route::get('/', [CajaController::class, 'index'])->name('index');
});
