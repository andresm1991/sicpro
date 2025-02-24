<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ResumenPagoSemanal extends Model
{
    use HasFactory;
    protected $table = 'resumen_pago_semanales';
    protected $fillable = ['fecha', 'descripcion', 'monto'];
}