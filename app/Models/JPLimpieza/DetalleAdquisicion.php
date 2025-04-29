<?php

namespace App\Models\JPLimpieza;

use App\Models\CatalogoDato;
use Illuminate\Database\Eloquent\Model;

class DetalleAdquisicion extends Model
{
    protected $connection = 'mysql_jp_limpieza';
    protected $table = 'detalle_adquisiciones';

    protected $fillable = [
        'adquisicion_id',
        'producto_id',
        'cantidad',
        'unidad_medida_id',
        'precio_unitario',
        'iva',
        'necesidad',
    ];

    protected $casts = [
        'precio_unitario' => 'decimal:4',
        'iva' => 'integer',
    ];

    public function adquisicion()
    {
        return $this->belongsTo(Adquisicion::class, 'adquisicion_id');
    }
    public function producto()
    {
        return $this->belongsTo(Producto::class, 'producto_id');
    }

    public function unidadMedida()
    {
        return $this->belongsTo(CatalogoDato::class, 'unidad_medida_id');
    }

    public function inventario()
    {
        return $this->hasMany(Inventario::class, 'adquisicion_id', 'adquisicion_id');
    }

    public function getPrecioUnitarioFormattedAttribute()
    {
        return number_format($this->precio_unitario, 4);
    }
    public function getIvaFormattedAttribute()
    {
        return number_format($this->iva, 2);
    }
    public function getTotalFormattedAttribute()
    {
        return number_format($this->total, 4);
    }
    public function getTotalAttribute()
    {
        return $this->precio_unitario * $this->cantidad;
    }
    public function getTotalIvaAttribute()
    {
        return $this->iva * $this->total / 100;
    }
    public function getTotalConIvaAttribute()
    {
        return $this->total + $this->total_iva;
    }
    public function getTotalFormattedConIvaAttribute()
    {
        return number_format($this->total_con_iva, 4);
    }
    public function getTotalFormattedSinIvaAttribute()
    {
        return number_format($this->total, 4);
    }
    public function getTotalFormattedIvaAttribute()
    {
        return number_format($this->total_iva, 4);
    }

    public function getTotalGeneralAttribute()
    {
        // Sumar el total del IVA de todos los detalles relacionados con la misma adquisición
        return $this->where('adquisicion_id', $this->adquisicion_id)
            ->get()
            ->sum(function ($detalle) {
                return $detalle->total_iva; // Usar el atributo total_iva
            });
    }
}