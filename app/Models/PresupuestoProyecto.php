<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PresupuestoProyecto extends Model
{
    use HasFactory;
    protected $table = 'presupuesto_proyecto';
    protected $fillable = [
        'proyecto_id',
        'rubro_presupuesto_id',
        'cantidad',
        'valor_unitario'
    ];

    public function proyecto()
    {
        return $this->belongsTo(Proyecto::class);
    }

    public function rubroPresupuesto()
    {
        return $this->belongsTo(RubroPresupuesto::class, 'rubro_presupuesto_id');
    }

    public function rubros_presupuesto()
    {
        return $this->hasMany(RubroPresupuesto::class, 'rubro_presupuesto_id');
    }
}
