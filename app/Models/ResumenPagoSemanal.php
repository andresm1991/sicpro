<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ResumenPagoSemanal extends Model
{
    use HasFactory;
    protected $table = 'resumen_pago_semanales';
    protected $fillable = ['fecha', 'total', 'estado_id'];

    public function estado()
    {
        return $this->belongsTo(CatalogoDato::class, 'estado_id');
    }
    public function detalle_resumen_pago_semanal()
    {
        return $this->hasMany(DetalleResumenPagoSemanal::class, 'resumen_pago_semanal_id');
    }
}
