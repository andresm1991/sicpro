<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ComentarioTarea extends Model
{
    use HasFactory;
    protected $table = 'comentario_tareas';
    protected $fillable = ['tarea_id', 'usuario_id', 'comentario'];

    public function tarea()
    {
        return $this->belongsTo(Tarea::class, 'tarea_id');
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }
}
