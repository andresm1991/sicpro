<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UsuarioTarea extends Model
{
    use HasFactory;
    protected $table = 'usuario_tareas';
    protected $fillable = ['usuario_id', 'tarea_id'];

    public function usuario()
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    public function tarea()
    {
        return $this->belongsTo(Tarea::class, 'tarea_id');
    }
}
