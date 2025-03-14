<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tarea extends Model
{
    use HasFactory;
    protected $table = 'tareas';

    protected $fillable = ['usuario_id', 'titulo', 'descripcion', 'estado_id', 'categoria_id'];

    public function usuario()
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }
    public function usuario_tareas()
    {
        return $this->belongsToMany(User::class, 'usuario_tareas', 'tarea_id', 'usuario_id');
    }

    public function comentarios()
    {
        return $this->hasMany(ComentarioTarea::class);
    }

    public function estado()
    {
        return $this->belongsTo(CatalogoDato::class, 'estado_id');
    }

    public function categoria()
    {
        return $this->belongsTo(CatalogoDato::class, 'categoria_id');
    }
    // Accesor para formatear created_at
    public function getCreatedAtFormateadoAttribute()
    {
        return $this->created_at->diffForHumans();
    }
}