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

    public function usuarios_tareas()
    {
        return $this->hasMany(UsuarioTarea::class, 'tarea_id');
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

    public static function filtroTareas($request)
    {
        $estado = $request->input('estado');
        $usuario = $request->input('usuario');
        $categoria = $request->input('categoria_tarea');

        $query = self::query();

        $query->when($estado, function ($query) use ($estado) {
            $query->where('estado_id', $estado);
        });

        $query->when($usuario, function ($query) use ($usuario) {
            $query->where('usuario_id', $usuario)
                ->orWhereHas('usuarios_tareas', function ($query) use ($usuario) {
                    $query->where('usuario_id', $usuario);
                });
        });

        $query->when($categoria, function ($query) use ($categoria) {
            $query->where('categoria_id', $categoria);
        });
        $query->with('usuarios_tareas', 'estado', 'categoria', 'usuarios_tareas.usuario');
        $query->orderBy('created_at', 'desc');

        return $query->get();
    }
}