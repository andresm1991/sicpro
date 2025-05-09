<?php

namespace App\Models\JPLimpieza;

use Illuminate\Database\Eloquent\Model;

class RubroPresupuesto extends Model
{
    protected $connection = 'mysql_jp_limpieza';
    protected $table = 'rubros_presupuesto';

    protected $fillable = [
        'categoria_presupuesto_id',
        'nombre',
        'precio_unitario',
        'activo',
    ];

    protected $casts = [
        'precio_unitario' => 'decimal:2',
    ];

    public function categoriaPresupuesto()
    {
        return $this->belongsTo(CategoriaPresupuesto::class, 'categoria_presupuesto_id');
    }
}