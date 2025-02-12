<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Solicitud extends Model
{
    use HasFactory;

    protected $table = 'solicitudes';
    protected $fillable = ['usuario_id', 'fecha_solicitud', 'fecha_desde', 'fecha_hasta', 'hora_desde', 'hora_hasta', 'total_tiempo', 'tipo_id', 'estado_id', 'recuperable', 'detalle'];

    public function usuario () {
        return $this->belongsTo(User::class, 'usuario_id');
    }
    public function recuperacion_solicitudes () {
        return $this->hasMany(RecuperacionSolicitud::class);
    }

    public function tipo_solicitud () {
        return $this->belongsTo(CatalogoDato::class, 'tipo_id');
    }

    public function estado_solicitud () {
        return $this->belongsTo(CatalogoDato::class, 'estado_id');
    }
}
