<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CategoriaPresupuesto extends Model
{
    use HasFactory;

    protected $table = 'categorias_presupuesto';
    protected $fillable = [
        'nombre',
        'activo',
    ];

    public function rubrosPresupuesto()
    {
        return $this->hasMany(RubroPresupuesto::class, 'categoria_presupuesto_id');
    }
}
