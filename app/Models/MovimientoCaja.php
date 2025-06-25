<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class MovimientoCaja extends Model
{
    use HasFactory;

    protected $table = 'movimientos_caja';

    protected $fillable = [
        'fecha',
        'proveedor_id',
        'articulo_id',
        'articulo_manual',
        'tipo',
        'monto',
        'descripcion',
        'referencia',
        'user_id',
        'origen_type', // Ej: 'App\Models\Venta', 'App\Models\Gasto'
        'origen_id'   // ID del modelo relacionado
    ];

    protected $casts = [
        'fecha' => 'date',
    ];
    protected $appends = ['monto_formatted', 'fecha_formateada'];

    // Relación polimórfica para registrar de dónde viene el movimiento
    public function origen()
    {
        return $this->morphTo();
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function proveedor()
    {
        return $this->belongsTo(Proveedor::class, 'proveedor_id');
    }

    public function articulo()
    {
        return $this->belongsTo(Articulo::class, 'articulo_id');
    }

    public function getMontoFormattedAttribute()
    {
        return '$' . number_format($this->monto, 4);
    }


    public function getFechaFormateadaAttribute()
    {
        return $this->fecha ? $this->fecha->format('d-m-Y') : null;
    }

    public static function registrarMovimiento($request)
    {
        if (!$request->input('articulo')) {
            return self::create(
                [
                    'fecha'        => date('Y-m-d'),
                    'proveedor_id' => $request->input('proveedor'),
                    'articulo_manual' => $request->input('detalle'),
                    'tipo'         => $request->input('tipo_movimiento', 'egreso'),
                    'monto'        => $request->input('monto'),
                    'descripcion'  => $request->input('detalle'),
                    'referencia'   => $request->input('referencia'),
                    'user_id'      => auth()->user()->id,
                    'origen_type' => $request->input('origen_type'),
                    'origen_id' => $request->input('origen_id'),
                ]
            );
        } else {
            return self::updateOrCreate(
                [
                    'articulo_id' => $request->input('articulo'),
                    'origen_id' => $request->input('origen_id'),
                ],
                [
                    'fecha'        => date('Y-m-d'),
                    'proveedor_id' => $request->input('proveedor'),
                    'tipo'         => $request->input('tipo_movimiento', 'egreso'),
                    'tipo'         => $request->input('tipo_movimiento', 'egreso'),
                    'monto'        => $request->input('monto'),
                    'descripcion'  => $request->input('detalle'),
                    'referencia'   => $request->input('referencia'),
                    'user_id'      => auth()->user()->id,
                    'origen_type' => $request->input('origen_type'),
                ]
            );
        }
    }

    public static function dataReporteCaja($request)
    {
        $fechas = $request->input('fechas');
        $query = self::with(['usuario', 'proveedor', 'articulo']);

        // Filtrar por rango de fechas
        $fechaInicioFormatted = null;
        $fechaFinFormatted = null;
        if ($fechas) {
            list($fechaInicio, $fechaFin) = explode(' - ', $fechas);
            $fechaInicioFormatted = Carbon::createFromFormat('m/d/Y', trim($fechaInicio))->format('Y-m-d');
            $fechaFinFormatted = Carbon::createFromFormat('m/d/Y', trim($fechaFin))->format('Y-m-d');
            $query->whereBetween('fecha', [$fechaInicioFormatted, $fechaFinFormatted]);
        }

        // Obtener movimientos filtrados
        $movimientos = $query->orderBy('fecha', 'asc')->get();

        // Calcular saldo acumulado hasta la fecha final
        $saldo = self::where('fecha', '<=', $fechaFinFormatted ?? now()->format('Y-m-d'))
            ->get()
            ->reduce(function ($carry, $mov) {
                return $carry + ($mov->tipo === 'ingreso' ? $mov->monto : -$mov->monto);
            }, 0);

        // Calcular totales de ingresos y egresos en el rango
        $totalIngresos = $movimientos->where('tipo', 'ingreso')->sum('monto');
        $totalEgresos = $movimientos->where('tipo', 'egreso')->sum('monto');

        // Calcular saldo acumulado en cada movimiento del rango
        $saldoAcumulado = 0;
        foreach ($movimientos as $movimiento) {
            if ($movimiento->tipo === 'ingreso') {
                $saldoAcumulado += $movimiento->monto;
            } else {
                $saldoAcumulado -= $movimiento->monto;
            }
            $movimiento->saldo_acumulado = '$' . number_format($saldoAcumulado, 2);
        }

        // Puedes retornar todos los valores
        return [
            'saldo_al' => number_format($saldo, 2),
            'fecha_saldo_inicio' => $fechaInicioFormatted
                ? 'Saldo al ' . Carbon::parse($fechaInicioFormatted)->translatedFormat('d \d\e F \d\e Y')
                : 'Saldo al ' . now()->translatedFormat('d \d\e F \d\e Y'),
            'fecha_saldo_fin' => $fechaFinFormatted
                ? 'Saldo al ' . Carbon::parse($fechaFinFormatted)->translatedFormat('d \d\e F \d\e Y')
                : 'Saldo al ' . now()->translatedFormat('d \d\e F \d\e Y'),
            'movimientos' => $movimientos,
            'total_ingresos' => '$' . number_format($totalIngresos, 2),
            'total_egresos' => '$' . number_format($totalEgresos, 2),
            'total_general' => '$' . number_format($totalIngresos - $totalEgresos, 2),
        ];
    }
}
