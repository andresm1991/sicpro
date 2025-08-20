<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EventualidadUsuario extends Model
{
    use HasFactory;
    protected $table = 'eventualidad_usuarios';
    protected $fillable = ['solicitud_id', 'usuario_id'];

    public function eventualidad()
    {
        return $this->belongsTo(Solicitud::class, 'solicitud_id');
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }
}