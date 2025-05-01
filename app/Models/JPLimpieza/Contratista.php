<?php

namespace App\Models\JPLimpieza;

use App\Models\Articulo;
use App\Models\Proveedor;
use App\Models\CatalogoDato;
use Illuminate\Database\Eloquent\Model;

class Contratista extends Model
{
    protected $connection = 'mysql_jp_limpieza';
    protected $table = 'contratistas';

    protected $fillable = [
        'proyecto_id',
        'proveedor_id',
        'categoria_id',
        'fecha',
        'plazo',
        'precio_total',
        'estado_id',
    ];

    protected $casts = [
        'fecha' => 'date',
    ];

    public function proyecto()
    {
        return $this->belongsTo(Proyecto::class, 'proyecto_id');
    }
    public function proveedor()
    {
        return $this->belongsTo(Proveedor::class, 'proveedor_id');
    }
    public function categoria()
    {
        return $this->belongsTo(Articulo::class, 'categoria_id');
    }
    public function estado()
    {
        return $this->belongsTo(CatalogoDato::class, 'estado_id');
    }
    public function detalles()
    {
        return $this->hasMany(DetalleContratista::class, 'contratista_id');
    }

    public function getPrecioTotalFormattedAttribute()
    {
        return number_format($this->precio_total, 4);
    }

    public function getFechaFormateadaAttribute()
    {
        return $this->fecha ? $this->fecha->format('Y-m-d') : null;
    }
}
