<?php

namespace App\Models\JPLimpieza;

use Illuminate\Database\Eloquent\Model;

class Producto extends Model
{
    protected $connection = 'mysql_jp_limpieza';
    protected $table = 'productos';

    protected $fillable = [
        'nombre',
        'unidad_medida_id',
        'precio_unitario',
        'iva',
        'activo',
    ];

    protected $casts = [
        'precio_unitario' => 'decimal:2',
        'iva' => 'integer',
    ];
}
