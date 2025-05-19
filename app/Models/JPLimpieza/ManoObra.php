<?php

namespace App\Models\JPLimpieza;

use App\Models\Proveedor;
use Illuminate\Database\Eloquent\Model;

class ManoObra extends Model
{
    protected $connection = 'mysql_jp_limpieza';
    protected $table = 'mano_obra';

    protected $fillable = [
        'proyecto_id',
        'fecha_desde',
        'fecha_hasta',
    ];

    protected $casts = [
        'fecha_desde' => 'date',
        'fecha_hasta' => 'date'
    ];

    public function proyecto()
    {
        return $this->belongsTo(Proyecto::class, 'proyecto_id');
    }

    public function detalles()
    {
        return $this->hasMany(DetalleManoObra::class, 'mano_obra_id');
    }

    ///** TOTALES GENERALES DE DETALLES */

    public function getTotalSueldosAttribute()
    {
        return $this->detalles->sum(function ($detalle) {
            return $detalle->sueldo;
        });
    }

    public function getTotalHorasHextrasAttribute()
    {
        return $this->detalles->sum(function ($detalle) {
            return $detalle->horas_extras;
        });
    }

    public function getTotalGanadoAttribute()
    {
        return $this->detalles->sum(function ($detalle) {
            return $detalle->total_ganado;
        });
    }

    public function getTotalFondosAttribute()
    {
        return $this->detalles->sum(function ($detalle) {
            return $detalle->fondos;
        });
    }

    public function getTotalDecimoTerceroAttribute()
    {
        return $this->detalles->sum(function ($detalle) {
            return $detalle->decimo_tercero;
        });
    }



    public function getTotalSueldosFormattedAttribute()
    {
        return number_format($this->total_sueldos, 2);
    }

    public function getFechaDesdeFormattedAttribute()
    {
        return $this->fecha_desde ? $this->fecha_desde->format('Y-m-d') : null;
    }
    public function getFechaHastaFormattedAttribute()
    {
        return $this->fecha_hasta ? $this->fecha_hasta->format('Y-m-d') : null;
    }
}