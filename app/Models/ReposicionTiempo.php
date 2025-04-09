<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReposicionTiempo extends Model
{
    use HasFactory;

    protected $table = 'reposicion_tiempos';
    protected $fillable = ['usuario_id', 'fecha', 'hora_desde', 'hora_hasta', 'total', 'estado_id', 'detalle'];

    public function usuario()
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    /**
     * Relación inversa con el modelo Solicitud.
     */
    public function solicitud()
    {
        return $this->belongsTo(Solicitud::class, 'usuario_id', 'usuario_id');
    }

    public function estado()
    {
        return $this->belongsTo(CatalogoDato::class, 'estado_id');
    }
}
