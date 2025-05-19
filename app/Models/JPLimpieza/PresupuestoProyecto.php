<?php

namespace App\Models\JPLimpieza;

use Illuminate\Database\Eloquent\Model;

class PresupuestoProyecto extends Model
{
    protected $connection = 'mysql_jp_limpieza';
    protected $table = 'presupuesto_proyecto';

    protected $fillable = [
        'proyecto_id',
        'plantilla_id',
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

    public function plantilla()
    {
        return $this->belongsTo(PlantillaPresupuesto::class, 'plantilla_id');
    }

    public function getPrecioUnitarioFormattedAttribute()
    {
        return number_format($this->precio_unitario, 4);
    }
}
