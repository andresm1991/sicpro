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
        'tipo',
    ];

    protected $casts = [
        'fecha_desde' => 'date',
        'fecha_hasta' => 'date',
    ];

    protected $appends = ['total_recibir_formatted', 'total_recibir'];

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
        return number_format($this->total_sueldos, 2);
    }

    public function getTotalHorasHextrasFormattedAttribute()
    {
        return number_format($this->total_horas_hextras, 2);
    }

    public function getTotalGanadoFormattedAttribute()
    {
        return number_format($this->total_ganado, 2);
    }

    public function getTotalFondosFormattedAttribute()
    {
        return number_format($this->total_fondos, 2);
    }

    public function getTotalDecimoTerceroFormattedAttribute()
    {
        return number_format($this->total_decimo_tercero, 2);
    }

    public function getTotalDecimoCuartoFormattedAttribute()
    {
        return number_format($this->total_decimo_cuarto, 2);
    }

    public function getTotalIngresoFormattedAttribute()
    {
        return number_format($this->total_ingreso, 2);
    }

    public function getTotalIessFormattedAttribute()
    {
        return number_format($this->total_iess, 2);
    }

    public function getTotalAtrasosFaltasFormattedAttribute()
    {
        return number_format($this->total_atrasos_faltas, 2);
    }

    public function getTotalAnticiposFormattedAttribute()
    {
        return number_format($this->total_anticipos, 2);
    }

    public function getTotalPrestamoIessFormattedAttribute()
    {
        return number_format($this->total_prestamo_iess, 2);
    }

    public function getTotalQuincenaFormattedAttribute()
    {
        return number_format($this->total_quincena, 2);
    }

    public function getTotalPrestamosJpFormattedAttribute()
    {
        return number_format($this->total_prestamos_jp, 2);
    }

    public function getTotalDescuentosFormattedAttribute()
    {
        return number_format($this->total_descuentos, 2);
    }

    public function getTotalRecibirFormattedAttribute()
    {
        return number_format($this->total_recibir, 2);
    }

    public function getFechaDesdeFormattedAttribute()
    {
        return $this->fecha_desde ? $this->fecha_desde->format('Y-m-d') : null;
    }
    public function getFechaHastaFormattedAttribute()
    {
        return $this->fecha_hasta ? $this->fecha_hasta->format('Y-m-d') : null;
    }

    public static function dataReporteManoObra($request)
    {
        $query = self::with('proyecto')
            ->when($request->proyecto, function ($q) use ($request) {
                $q->where('proyecto_id', $request->proyecto);
            })
            ->orderBy('created_at', 'asc')
            ->get();

        return $query;
    }
}
