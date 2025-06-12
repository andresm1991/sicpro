<?php

namespace App\Models\JPLimpieza;

use App\Models\Proveedor;
use Illuminate\Database\Eloquent\Model;

class DetalleManoObra extends Model
{
    protected $connection = 'mysql_jp_limpieza';
    protected $table = 'detalle_mano_obra';

    protected $fillable = [
        'mano_obra_id',
        'proveedor_id',
        'sueldo',
        'horas_extras',
        'total_ganado',
        'fondos',
        'decimo_tercero',
        'decimo_cuarto',
        'total_ingreso',
        'iess',
        'atrasos_faltas',
        'anticipos',
        'prestamo_iess',
        'quincena',
        'prestamo_jp',
        'total_descuentos',
        'total_recibir',
    ];

    protected $casts = [
        'sueldo' => 'decimal:4',
        'horas_extras' => 'decimal:4',
        'total_ganado' => 'decimal:4',
        'fondos' => 'decimal:4',
        'decimo_tercero' => 'decimal:4',
        'decimo_cuarto' => 'decimal:4',
        'total_ingreso' => 'decimal:4',
        'iess' => 'decimal:4',
        'atrasos_faltas' => 'decimal:4',
        'anticipos' => 'decimal:4',
        'prestamo_iess' => 'decimal:4',
        'quincena' => 'decimal:4',
        'prestamo_jp' => 'decimal:4',
        'total_descuentos' => 'decimal:4',
        'total_recibir' => 'decimal:4',
    ];

    public function proveedor()
    {
        return $this->belongsTo(Proveedor::class, 'proveedor_id');
    }

    public function mano_obra()
    {
        return $this->belongsTo(ManoObra::class, 'mano_obra_id');
    }

    public function getTotalManoObraAttribute()
    {
        return $this->sueldo + $this->horas_extras + $this->fondos + $this->decimo_tercero + $this->decimo_cuarto - $this->iess - $this->atrasos_faltas - $this->anticipos - $this->prestamo_iess - $this->quincena - $this->prestamo_jp;
    }


    public function getSueldoFormattedAttribute()
    {
        return number_format($this->sueldo, 2);
    }
    public function getHorasExtrasFormattedAttribute()
    {
        return number_format($this->horas_extras, 2);
    }

    public function getTotalGanadoFormattedAttribute()
    {
        return number_format($this->total_ganado, 2);
    }
    public function getFondoFormattedAttribute()
    {
        return number_format($this->fondos, 2);
    }
    public function getDecimoTerceroFormattedAttribute()
    {
        return number_format($this->decimo_tercero, 2);
    }
    public function getDecimoCuartoFormattedAttribute()
    {
        return number_format($this->decimo_cuarto, 2);
    }
    public function getTotalIngresosFormattedAttribute()
    {
        return number_format($this->total_ingreso, 2);
    }
    public function getIessFormattedAttribute()
    {
        return number_format($this->iess, 2);
    }
    public function getAtrasosFaltasFormattedAttribute()
    {
        return number_format($this->atrasos_faltas, 2);
    }
    public function getAnticiposFormattedAttribute()
    {
        return number_format($this->anticipos, 2);
    }
    public function getPrestamoIessFormattedAttribute()
    {
        return number_format($this->prestamo_iess, 2);
    }
    public function getQuincenaFormattedAttribute()
    {
        return number_format($this->quincena, 2);
    }
    public function getPrestamoJPFormattedAttribute()
    {
        return number_format($this->prestamo_jp, 2);
    }
    public function getTotalDescuentosFormattedAttribute()
    {
        return number_format($this->total_descuentos, 2);
    }
    public function getTotalRecibirFormattedAttribute()
    {
        return number_format($this->total_recibir, 2);
    }
}