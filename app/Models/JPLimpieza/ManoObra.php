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

    public function getTotalDecimoCuartoAttribute()
    {
        return $this->detalles->sum(function ($detalle) {
            return $detalle->decimo_cuarto;
        });
    }

    public function getTotalIngresoAttribute()
    {
        return $this->detalles->sum(function ($detalle) {
            return $detalle->total_ingreso;
        });
    }

    public function getTotalIESSAttribute()
    {
        return $this->detalles->sum(function ($detalle) {
            return $detalle->iess;
        });
    }

    public function getTotalAtrasosFaltasAttribute()
    {
        return $this->detalles->sum(function ($detalle) {
            return $detalle->atrasos_faltas;
        });
    }

    public function getTotalAnticiposAttribute()
    {
        return $this->detalles->sum(function ($detalle) {
            return $detalle->anticipos;
        });
    }

    public function getTotalPrestamoIESSAttribute()
    {
        return $this->detalles->sum(function ($detalle) {
            return $detalle->prestamo_iess;
        });
    }

    public function getTotalQuincenaAttribute()
    {
        return $this->detalles->sum(function ($detalle) {
            return $detalle->quincena;
        });
    }

    public function getTotalPrestamosJPAttribute()
    {
        return $this->detalles->sum(function ($detalle) {
            return $detalle->prestamo_jp;
        });
    }

    public function getTotalDescuentosAttribute()
    {
        return $this->detalles->sum(function ($detalle) {
            return $detalle->total_descuentos;
        });
    }

    public function getTotalRecibirAttribute()
    {
        return $this->detalles->sum(function ($detalle) {
            return $detalle->total_recibir;
        });
    }

    public function getTotalSueldosFormattedAttribute()
    {
        return number_format($this->total_sueldos, 4);
    }

    public function getTotalHorasHextrasFormattedAttribute()
    {
        return number_format($this->total_horas_hextras, 4);
    }

    public function getTotalGanadoFormattedAttribute()
    {
        return number_format($this->total_ganado, 4);
    }

    public function getTotalFondosFormattedAttribute()
    {
        return number_format($this->total_fondos, 4);
    }

    public function getTotalDecimoTerceroFormattedAttribute()
    {
        return number_format($this->total_decimo_tercero, 4);
    }

    public function getTotalDecimoCuartoFormattedAttribute()
    {
        return number_format($this->total_decimo_cuarto, 4);
    }

    public function getTotalIngresoFormattedAttribute()
    {
        return number_format($this->total_ingreso, 4);
    }

    public function getTotalIessFormattedAttribute()
    {
        return number_format($this->total_iess, 4);
    }

    public function getTotalAtrasosFaltasFormattedAttribute()
    {
        return number_format($this->total_atrasos_faltas, 4);
    }

    public function getTotalAnticiposFormattedAttribute()
    {
        return number_format($this->total_anticipos, 4);
    }

    public function getTotalPrestamoIessFormattedAttribute()
    {
        return number_format($this->total_prestamo_iess, 4);
    }

    public function getTotalQuincenaFormattedAttribute()
    {
        return number_format($this->total_quincena, 4);
    }

    public function getTotalPrestamosJpFormattedAttribute()
    {
        return number_format($this->total_prestamos_jp, 4);
    }

    public function getTotalDescuentosFormattedAttribute()
    {
        return number_format($this->total_descuentos, 4);
    }

    public function getTotalRecibirFormattedAttribute()
    {
        return number_format($this->total_recibir, 4);
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