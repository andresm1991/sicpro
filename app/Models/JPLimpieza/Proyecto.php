<?php

namespace App\Models\JPLimpieza;

use Illuminate\Database\Eloquent\Model;

class Proyecto extends Model
{
    protected $connection = 'mysql_jp_limpieza';
    protected $table = 'proyectos';

    protected $fillable = [
        'nombre_proyecto',
        'entidad',
        'metros_contratado',
        'precio_por_metro',
        'tiempo_contratado',
        'archivo_portada',
        'archivo_orden_compra',
        'archivo_acta_final',
        'fecha_inicio',
        'fecha_finalizacion',
        'observacion',
        'telefono',
        'estado',
    ];

    protected $casts = [
        'fecha_inicio' => 'datetime',
        'fecha_finalizacion' => 'datetime',
    ];
}
