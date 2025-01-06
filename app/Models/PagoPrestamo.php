<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PagoPrestamo extends Model
{
    use HasFactory;
    protected $table = 'pagos_prestamos';
    protected $fillable = ['prestamo_id', 'fecha_pago', 'monto_pagado', 'monto_programado', 'estado_id', 'metodo_pago_id'];
}