<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PagoPrestamo extends Model
{
    use HasFactory;
    protected $table = 'pagos_prestamos';
    protected $fillable = ['prestamo_id', 'fecha_pago', 'monto_pagado', 'monto_programado', 'estado_id', 'metodo_pago_id'];

    public function estado()
    {
        return $this->belongsTo(CatalogoDato::class, 'estado_id');
    }

    public function metodo_pago()
    {
        return $this->belongsTo(CatalogoDato::class, 'metodo_pago_id');
    }

    public function prestamo()
    {
        return $this->belongsTo(Prestamo::class, 'prestamo_id');
    }

    public function pago_mano_obra()
    {
        return $this->hasOne(PagoManoObra::class, 'pago_prestamo_id');
    }
}