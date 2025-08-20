<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DetalleResumenPagoSemanal extends Model
{
    use HasFactory;
    protected $table = 'detalle_resumen_pago_semanales';
    protected $fillable = ['resumen_pago_semanal_id', 'descripcion', 'monto'];

    public function resumen_pago_semanal()
    {
        return $this->belongsTo(ResumenPagoSemanal::class, 'resumen_pago_semanal_id');
    }
}
