<?php

namespace App\Models\JPLimpieza;

use Carbon\Carbon;
use App\Models\Proveedor;
use App\Models\CatalogoDato;
use Illuminate\Database\Eloquent\Model;

class Adquisicion extends Model
{
    protected $connection = 'mysql_jp_limpieza';
    protected $table = 'adquisiciones';

    protected $fillable = [
        'fecha',
        'numero',
        'proyecto_id',
        'proveedor_id',
        'tipo_id',
        'estado',
        'nro_factura',
        'archivo',
        'forma_pago_id',
        'administrativo',
    ];

    protected $casts = [
        'fecha' => 'date',
    ];

    public function proyecto()
    {
        return $this->belongsTo(Proyecto::class, 'proyecto_id');
    }
    public function proveedor()
    {
        return $this->belongsTo(Proveedor::class, 'proveedor_id');
    }
    public function tipo()
    {
        return $this->belongsTo(CatalogoDato::class, 'tipo_id');
    }
    public function formaPago()
    {
        return $this->belongsTo(CatalogoDato::class, 'forma_pago_id');
    }
    public function detalles()
    {
        return $this->hasMany(DetalleAdquisicion::class, 'adquisicion_id');
    }

    public function getTotalGeneralAttribute()
    {
        return $this->detalles->sum(function ($detalle) {
            return $detalle->total_con_iva;
        });
    }

    public function getTotalGeneralFormattedAttribute()
    {
        return number_format($this->total_general, 4);
    }

    public function getFechaFormateadaAttribute()
    {
        return $this->fecha ? $this->fecha->format('Y-m-d') : null;
    }

    /**
     * Datos para el reporte de adquisiciones
     * @param Request $request
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function dataReporteAdquisiciones($request)
    {
        $query = self::with(['proyecto', 'proveedor', 'tipo', 'formaPago', 'detalles'])
            ->when($request->input('fechas'), function ($q) use ($request) {
                list($fechaInicio, $fechaFin) = explode(' - ', $request->input('fechas'));
                $q->whereBetween('fecha', [
                    Carbon::createFromFormat('m/d/Y', trim($fechaInicio))->format('Y-m-d'),
                    Carbon::createFromFormat('m/d/Y', trim($fechaFin))->format('Y-m-d')
                ]);
            })
            ->when($request->input('proyecto'), function ($q) use ($request) {
                $q->where('proyecto_id', $request->input('proyecto'));
            })
            ->when($request->input('tipo'), function ($q) use ($request) {
                $q->where('tipo_id', $request->input('tipo'));
            })
            ->when($request->input('proveedor'), function ($q) use ($request) {
                $q->where('proveedor_id', $request->input('proveedor'));
            })
            ->when($request->input('estado'), function ($q) use ($request) {
                $q->where('estado', $request->input('estado'));
            })
            ->when($request->input('forma_pago'), function ($q) use ($request) {
                $q->where('forma_pago_id', $request->input('forma_pago'));
            })
            ->orderBy('fecha', 'desc');

        return $query->get();
    }
}
