<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DetalleProformaPlano extends Model
{
    use HasFactory;
    protected $table = 'detalle_proformas_planos';
    protected $fillable = [
        'proforma_plano_id',
        'producto_id',
        'area',
        'precio_unitario',
        'costo_indirecto',
        'total'
    ];
    public function proformaPlano()
    {
        return $this->belongsTo(ProformaPlano::class, 'proforma_plano_id');
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
        return $this->area * $this->valor_unitario;
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
