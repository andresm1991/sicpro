<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProformaAdecentamiento extends Model
{
    use HasFactory;
    protected $table = 'proforma_adecentamientos';
    protected $fillable = [
        'numero',
        'fecha',
        'cliente_id',
        'observaciones',
        'notas',
        'estado_id',
        'validez',
        'forma_pago',
        'plazo_entrega',
        'subtotal',
        'descuento',
        'iva',
        'total'
    ];

    protected $casts = [
        'fecha' => 'date',
    ];

    public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'cliente_id');
    }

    public function estado()
    {
        return $this->belongsTo(CatalogoDato::class, 'estado_id');
    }

    public function detalleAdecentamientos()
    {
        return $this->hasMany(DetalleProformaAdecentamiento::class);
    }
    public function getTotalAttribute()
    {
        return $this->subtotal - ($this->subtotal * $this->descuento / 100) + ($this->subtotal * $this->iva / 100);
    }

    public function getSubtotalFormatAttribute()
    {
        return number_format($this->subtotal, 2, ',', '.');
    }
    public function getTotalFormatAttribute()
    {
        return number_format($this->total, 2, ',', '.');
    }

    public function getFechaFormattedAttribute()
    {
        return $this->fecha ? $this->fecha->format('Y-m-d') : null;
    }
}
