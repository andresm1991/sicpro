<?php

namespace App\Models\JPLimpieza;

use Illuminate\Database\Eloquent\Model;

class Proyecto extends Model
{
    protected $connection = 'mysql_jp_limpieza';
    protected $table = 'proyectos';

    protected $fillable = [
        'nombre_proyecto',
        'entidad',
        'metros_contratado',
        'precio_por_metro',
        'tiempo_contratado',
        'archivo_portada',
        'archivo_orden_compra',
        'archivo_acta_final',
        'fecha_inicio',
        'fecha_finalizacion',
        'observacion',
        'telefono',
        'estado',
    ];

    protected $casts = [
        'fecha_inicio' => 'date',
        'fecha_finalizacion' => 'date',
    ];

    public function presupuesto()
    {
        return $this->hasMany(PresupuestoProyecto::class, 'proyecto_id');
    }

    public function getValorContratadoMensualAttribute()
    {
        return $this->precio_por_metro * $this->metros_contratado;
    }
    public function getTotalContratadoAttribute()
    {
        return $this->valor_contratado_mensual * $this->tiempo_contratado;
    }

    public function getValorContratadoMensualFormattedAttribute()
    {
        return number_format($this->valor_contratado_mensual, 4);
    }
    public function getTotalContratadoFormattedAttribute()
    {
        return number_format($this->total_contratado, 4);
    }
}
