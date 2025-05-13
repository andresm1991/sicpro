<?php

namespace App\Models\JPLimpieza;

use Illuminate\Database\Eloquent\Model;

class PlantillaPresupuesto extends Model
{
    protected $connection = 'mysql_jp_limpieza';
    protected $table = 'plantilla_presupuesto';

    protected $fillable = [
        'descripcion',
        'detalle',
        'slug',
        'padre_id',
        'activo',
    ];

    public function hijos()
    {
        return $this->hasMany(PlantillaPresupuesto::class, 'padre_id');
    }

    public function presupuestoProyecto()
    {
        return $this->hasOne(PresupuestoProyecto::class, 'plantilla_id');
    }

    public function presupuestosProyecto()
    {
        return $this->hasMany(PresupuestoProyecto::class, 'plantilla_id');
    }

    public function getTotalCategoriaAttribute()
    {
        // Si es una categoría padre (suma todos sus hijos)
        if (is_null($this->padre_id)) {
            return $this->hijos->sum(function ($hijo) {
                return $hijo->presupuestosProyecto->sum(function ($presupuesto) {
                    return $presupuesto->cantidad * $presupuesto->precio_unitario * $presupuesto->meses;
                });
            });
        }

        // Si es una categoría hijo (suma solo sus presupuestos)
        return $this->presupuestosProyecto->sum(function ($presupuesto) {
            return $presupuesto->cantidad * $presupuesto->precio_unitario * $presupuesto->meses;
        });
    }

    public function getTotalCategoriaFormattedAttribute()
    {
        return number_format($this->total_categoria, 4);
    }
}