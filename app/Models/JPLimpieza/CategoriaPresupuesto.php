<?php

namespace App\Models\JPLimpieza;

use Illuminate\Database\Eloquent\Model;

class CategoriaPresupuesto extends Model
{
    protected $connection = 'mysql_jp_limpieza';
    protected $table = 'categorias_presupuestos';

    protected $fillable = [
        'nombre',
        'activo',
    ];
}