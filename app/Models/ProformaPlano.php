<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProformaPlano extends Model
{
    use HasFactory;
    protected $table = 'proformas_planos';

    protected $fillable = [
        'numero',
        'fecha',
        'cliente_id',
        'descripcion',
        'ubicacion_lote',
        'area_lote',
        'presupuesto',
        'incluye',
        'plazo_ejecucion',
        'observaciones',
        'estado_id',
        'forma_pago',
        'abono',
        'subtotal',
        'descuento',
        'iva',
        'total'
    ];

    protected $casts = [
        'fecha' => 'date',
        'incluye' => 'array',
        'forma_pago' => 'array',
        'abono' => 'array',
    ];

    protected $appends = [
        'total_iva_formatted',
        'total_descuento_formatted',
        'total',
        'subtotal_formatted',
        'total_formatted',
        'fecha_formatted'
    ];

    public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'cliente_id');
    }

    public function estado()
    {
        return $this->belongsTo(CatalogoDato::class, 'estado_id');
    }

    public function detallePlanos()
    {
        return $this->hasMany(DetalleProformaPlano::class);
    }

    public function programaArquitectonico()
    {
        return $this->hasOne(ProgramaArquitectonico::class, 'proforma_id');
    }

    public function getTotalIvaAttribute()
    {
        $subtotal = $this->subtotal;
        if ($this->descuento > 0) {
            $subtotal = $subtotal - ($subtotal * $this->descuento / 100);
        }
        return $subtotal * ($this->iva / 100);
    }

    public function getTotalDescuentoAttribute()
    {
        $descuento = $this->subtotal;
        if ($this->descuento > 0) {
            $descuento = $descuento - ($descuento * $this->descuento / 100);
        } else {
            $descuento = 0;
        }
        return $descuento;
    }

    public function getTotalAttribute()
    {
        return $this->subtotal + $this->total_iva;
    }

    public function getSubtotalFormattedAttribute()
    {
        return number_format($this->subtotal, 2);
    }
    public function getTotalFormattedAttribute()
    {
        return '$ ' . number_format($this->total, 2);
    }

    public function getTotalIvaFormattedAttribute()
    {
        return '$ ' . number_format($this->total_iva, 2);
    }

    public function getTotalDescuentoFormattedAttribute()
    {
        return '$ ' . number_format($this->total_descuento, 2);
    }

    public function getFechaFormattedAttribute()
    {
        return $this->fecha ? $this->fecha->format('d-m-Y') : null;
    }
}
