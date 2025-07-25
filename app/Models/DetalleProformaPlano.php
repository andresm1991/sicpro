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
    public function getTotalAttribute()
    {
        return $this->area * $this->precio_unitario;
    }

    public function getTotalFormatAttribute()
    {
        return number_format($this->total, 2);
    }
    public function getPrecioUnitarioFormatAttribute()
    {
        return number_format($this->precio_unitario, 2);
    }
}