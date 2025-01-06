<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Prestamo extends Model
{
    use HasFactory;
    protected $table = 'prestamos';
    protected $fillable = ['trabajador_id', 'fecha_solicitud', 'fecha_aprobacion', 'fecha_vencimiento', 'monto', 'interes', 'plazo', 'estado_id', 'motivo'];

    public function trabajador()
    {
        return $this->belongsTo(Proveedor::class, 'trabajador_id');
    }

    public function estado()
    {
        return $this->belongsTo(CatalogoDato::class, 'estado_id');
    }
}