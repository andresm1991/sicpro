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
        'subtotal',
        'iva',
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
    public function getSubtotalAttribute()
    {
        return $this->cantidad * $this->precio_unitario;
    }
    public function getTotalAttribute()
    {
        $subtotal = $this->subtotal;
        $ivaAmount = ($subtotal * $this->iva) / 100;
        return $subtotal + $ivaAmount;
    }
    public function getSubtotalFormatAttribute()
    {
        return number_format($this->subtotal, 2, ',', '.');
    }
    public function getTotalFormatAttribute()
    {
        return number_format($this->total, 2, ',', '.');
    }
    public function getPrecioUnitarioFormatAttribute()
    {
        return number_format($this->precio_unitario, 2, ',', '.');
    }
}