<?php

namespace App\Models\JPLimpieza;

use App\Models\CatalogoDato;
use Illuminate\Database\Eloquent\Model;

class DetalleContratista extends Model
{
    protected $connection = 'mysql_jp_limpieza';
    protected $table = 'detalle_contratista';

    protected $fillable = [
        'contratista_id',
        'producto_id',
        'cantidad',
        'unidad_medida_id',
        'precio_unitario',
        'iva',
        'total',
    ];

    protected $casts = [
        'precio_unitario' => 'decimal:4',
        'iva' => 'integer',
        'total' => 'decimal:4',
    ];
    public function contratista()
    {
        return $this->belongsTo(Contratista::class, 'contratista_id');
    }
    public function producto()
    {
        return $this->belongsTo(Producto::class, 'producto_id');
    }
    public function unidadMedida()
    {
        return $this->belongsTo(CatalogoDato::class, 'unidad_medida_id');
    }

    public function getTotalFormattedAttribute()
    {
        return number_format($this->total, 4);
    }
    public function getPrecioUnitarioFormattedAttribute()
    {
        return number_format($this->precio_unitario, 4);
    }
}
