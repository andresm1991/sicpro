<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cronograma extends Model
{
    use HasFactory;
    protected $table = 'cronograma';
    protected $fillable = ['proyecto_id', 'rubro_id', 'etapa_id', 'semana', 'completado'];

    public function rubro()
    {
        return $this->belongsTo(RubroPresupuesto::class, 'rubro_id');
    }

    public function etapa()
    {
        return $this->belongsTo(CatalogoDato::class, 'etapa_id');
    }

    public function actividad_dias()
    {
        return $this->hasMany(ActividadDiaCronograma::class);
    }

    public function proyecto()
    {
        return $this->belongsTo(Proyecto::class, 'proyecto_id');
    }
}