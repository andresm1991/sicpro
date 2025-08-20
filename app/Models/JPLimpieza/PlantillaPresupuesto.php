<?php

namespace App\Models\JPLimpieza;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

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

    protected function totalCategoria(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->hijos->sum('total_presupuesto')
        );
    }

    public function getTotalGeneralAttribute()
    {
        return $this->sum(function ($h) {
            // Si es una categoría padre (tiene hijos)
            if ($h->hijos->isNotEmpty()) {
                return $h->hijos->sum(function ($hijo) {
                    return optional($hijo->presupuestoProyecto)->cantidad *
                        optional($hijo->presupuestoProyecto)->precio_unitario *
                        optional($hijo->presupuestoProyecto)->meses ??
                        0;
                });
            }

            // Si es una categoría hija (sin hijos)
            return optional($h->presupuestoProyecto)->cantidad *
                optional($h->presupuestoProyecto)->precio_unitario *
                optional($h->presupuestoProyecto)->meses ??
                0;
        });
    }

    protected function totalPresupuesto(): Attribute
    {
        return Attribute::make(
            get: fn() => (optional($this->presupuestoProyecto)->cantidad ?? 0) *
                (optional($this->presupuestoProyecto)->precio_unitario ?? 0) *
                (optional($this->presupuestoProyecto)->meses ?? 0)
        );
    }
}
