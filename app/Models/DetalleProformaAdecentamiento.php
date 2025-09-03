<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DetalleProformaAdecentamiento extends Model
{
    use HasFactory;

    protected $table = 'detalle_proforma_adecentamientos';
    protected $fillable = [
        'proforma_adecentamiento_id',
        'producto_id',
        'cantidad',
        'precio_unitario',
        'costo_indirecto',
        'total'
    ];
    public function proformaAdecentamiento()
    {
        return $this->belongsTo(ProformaAdecentamiento::class, 'proforma_adecentamiento_id');
    }
    public function producto()
    {
        return $this->belongsTo(ProformaProducto::class, 'producto_id');
    }

    public function getValorUnitarioAttribute()
    {
        return calcularProcentaje($this->precio_unitario, $this->costo_indirecto);
    }

    public function getTotalAttribute()
    {
        return $this->cantidad * $this->valor_unitario;
    }

    public function getTotalFormatAttribute()
    {
        return number_format($this->total, 2);
    }
    public function getPrecioUnitarioFormatAttribute()
    {
        return number_format($this->valor_unitario, 2);
    }
}
