<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TareaController;

Route::group(['prefix' => 'agenda', 'as' => 'tarea.'], function () {
    Route::get('/', [TareaController::class, 'index'])->name('index');
    Route::post('/guardar-tarea', [TareaController::class, 'store'])->name('store');
    Route::post('/guardar-comentario', [TareaController::class, 'storeComentario']);
    Route::get('/comentarios-tarea', [TareaController::class, 'getComentariosTarea']);
    Route::put('/comentarios-tarea/{comentarioId}', [TareaController::class, 'updateComentario']);
    Route::delete('/eliminar-comentario/{comentario}', [TareaController::class, 'deleteComentario']);
    Route::delete('/eliminar-tarea/{tarea}', [TareaController::class, 'deleteTarea']);
    Route::put('/actualizar-estado-tarea/{tarea}', [TareaController::class, 'updateEstadoTarea']);
    Route::get('/exportar-tareas', [TareaController::class, 'exportarTareas']);
});
