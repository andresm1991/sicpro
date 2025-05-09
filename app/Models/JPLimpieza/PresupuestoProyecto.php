<?php

namespace App\Models\JPLimpieza;

use Illuminate\Database\Eloquent\Model;

class PresupuestoProyecto extends Model
{
    protected $connection = 'mysql_jp_limpieza';
    protected $table = 'presupuesto_proyecto';

    protected $fillable = [
        'proyecto_id',
        'rubro_presupuesto_id',
        'cantidad',
        'precio_unitario',
        'iva',
        'meses',
    ];

    protected $casts = [
        'precio_unitario' => 'decimal:4',
        'iva' => 'integer',
    ];

    public function proyecto()
    {
        return $this->belongsTo(Proyecto::class, 'proyecto_id');
    }
    public function rubroPresupuesto()
    {
        return $this->belongsTo(RubroPresupuesto::class, 'rubro_presupuesto_id');
    }
}