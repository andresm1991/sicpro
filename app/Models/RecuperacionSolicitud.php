<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RecuperacionSolicitud extends Model
{
    use HasFactory;
    protected $table = 'recuperacion_solicitudes';
    protected $fillable = ['solicitud_id', 'fecha', 'hora_desde', 'hora_hasta', 'total'];

    public function solicitud () {
        return $this->belongsTo(Solicitud::class, 'solicitud_id');
    }
}
