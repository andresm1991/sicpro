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
        'numero',
        'proyecto_id',
        'proveedor_id',
        'categoria_id',
        'fecha',
        'plazo',
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

    public function pagosContratista()
    {
        return $this->hasMany(PagoContratista::class, 'contratista_id');
    }

    public function getTotalContratadoAttribute()
    {
        return $this->detalles->sum(function ($detalle) {
            return $detalle->total_formatted;
        });
    }

    public function getTotalPagadoAttribute()
    {
        $estado = CatalogoDato::getIdCatalogo('estados.pagos.prestamos.pagado');
        return $this->pagosContratista->where('estado_id', $estado)->sum(function ($pago) {
            return $pago->monto;
        });
    }

    public function getTotalPagosRegistradosAttribute()
    {
        return $this->pagosContratista->sum(function ($pago) {
            return $pago->monto;
        });
    }

    public function getTotalPendienteAttribute()
    {

        return $this->total_contratado - $this->total_pagado;
    }

    public function getTotalContratadoFormattedAttribute()
    {
        return number_format($this->total_contratado, 4);
    }

    public function getTotalPagadoFormattedAttribute()
    {
        return number_format($this->total_pagado, 4);
    }


    public function getTotalPendienteFormattedAttribute()
    {
        return number_format($this->total_pendiente, 4);
    }

    public function getTotalPagosRegistradosFormattedAttribute()
    {
        return number_format($this->total_pagos_registrados, 4);
    }

    public function getFechaFormateadaAttribute()
    {
        return $this->fecha ? $this->fecha->format('Y-m-d') : null;
    }
}
