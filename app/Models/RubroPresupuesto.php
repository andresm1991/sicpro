<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RubroPresupuesto extends Model
{
    use HasFactory;
    protected $table = 'rubros_presupuesto';
    protected $fillable = [
        'categoria_presupuesto_id',
        'nombre',
        'unidad_medida_id',
        'valor_unitario',
        'activo',
        'etapa_id',
    ];

    public function presupuestoProyectos()
    {
        return $this->hasMany(PresupuestoProyecto::class, 'rubro_presupuesto_id');
    }

    public function categoria_presupuesto()
    {
        return $this->belongsTo(CategoriaPresupuesto::class, 'categoria_presupuesto_id');
    }

    public function unidad_medida()
    {
        return $this->belongsTo(CatalogoDato::class, 'unidad_medida_id');
    }
}
