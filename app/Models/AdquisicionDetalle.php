<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdquisicionDetalle extends Model
{
    use HasFactory;
    protected $table = 'adquisiciones_detalle';
    protected $fillable = ['adquisicion_id', 'articulo_id', 'cantidad_solicitada', 'cantidad_recibida', 'necesidad'];

    function adquisicion()
    {
        return $this->belongsTo(Adquisicion::class, 'adquisicion_id');
    }

    function producto()
    {
        return $this->belongsTo(Articulo::class, 'articulo_id');
    }
}
