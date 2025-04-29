<?php

namespace App\Models\JPLimpieza;

use App\Models\Proveedor;
use App\Models\CatalogoDato;
use Illuminate\Database\Eloquent\Model;

class Adquisicion extends Model
{
    protected $connection = 'mysql_jp_limpieza';
    protected $table = 'adquisiciones';

    protected $fillable = [
        'fecha',
        'numero',
        'proyecto_id',
        'proveedor_id',
        'tipo_id',
        'estado',
        'nro_factura',
        'archivo',
        'forma_pago_id',
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
    public function tipo()
    {
        return $this->belongsTo(CatalogoDato::class, 'tipo_id');
    }
    public function formaPago()
    {
        return $this->belongsTo(CatalogoDato::class, 'forma_pago_id');
    }
    public function detalles()
    {
        return $this->hasMany(DetalleAdquisicion::class, 'adquisicion_id');
    }

    public function getTotalGeneralAttribute()
    {
        return $this->detalles->sum(function ($detalle) {
            return $detalle->total_con_iva;
        });
    }

    public function getTotalGeneralFormattedAttribute()
    {
        return number_format($this->total_general, 4);
    }

    public function getFechaFormateadaAttribute()
    {
        return $this->fecha ? $this->fecha->format('Y-m-d') : null;
    }
}