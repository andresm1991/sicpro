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
                    'monto'        => limpiarValor($request->input('monto')),
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
                    'monto'        => limpiarValor($request->input('monto')),
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
        list($fechaInicioStr, $fechaFinStr) = explode(' - ', $fechas);
        $fechaInicio = Carbon::createFromFormat('m/d/Y', trim($fechaInicioStr))->startOfDay();
        $fechaFin = Carbon::createFromFormat('m/d/Y', trim($fechaFinStr))->endOfDay();


        // ---- 1. CALCULAR EL SALDO INICIAL ----
        // Suma todos los ingresos y resta todos los egresos ANTES de la fecha de inicio del reporte.
        $saldo_inicial = self::where('fecha', '<', $fechaInicio->format('Y-m-d'))
            ->get()
            ->reduce(function ($carry, $mov) {
                return $carry + ($mov->tipo === 'ingreso' ? $mov->monto : -$mov->monto);
            }, 0);

        // ---- 2. OBTENER LOS MOVIMIENTOS DEL PERIODO ----
        // Obtiene solo los movimientos dentro del rango de fechas seleccionado.
        $movimientos_periodo = self::with(['usuario', 'proveedor', 'articulo'])
            ->whereBetween('fecha', [$fechaInicio, $fechaFin])
            ->orderBy('fecha', 'asc')
            ->get();

        // ---- 3. CALCULAR EL SALDO ACUMULADO PARA CADA MOVIMIENTO ----
        // El saldo acumulado comienza con el saldo_inicial que ya calculamos.
        $saldoAcumulado = $saldo_inicial;
        foreach ($movimientos_periodo as $movimiento) {
            if ($movimiento->tipo === 'ingreso') {
                $saldoAcumulado += $movimiento->monto;
            } else {
                $saldoAcumulado -= $movimiento->monto;
            }
            // Asignamos el saldo actualizado a cada movimiento para mostrarlo en la tabla.
            $movimiento->saldo_acumulado = number_format($saldoAcumulado, 4);
        }

        // ---- 4. CALCULAR LOS TOTALES DEL PERIODO ----
        $totalIngresosPeriodo = $movimientos_periodo->where('tipo', 'ingreso')->sum('monto');
        $totalEgresosPeriodo = $movimientos_periodo->where('tipo', 'egreso')->sum('monto');

        // El saldo final es el último valor del saldo acumulado.
        $saldo_final = $saldoAcumulado;

        // ---- 5. PREPARAR LA RESPUESTA ----
        return [
            'fecha_reporte' => 'del ' . $fechaInicio->subDay()->translatedFormat('d \d\e F \d\e Y') . ' al ' . $fechaFin->translatedFormat('d \d\e F \d\e Y'),
            // Datos para la primera fila (saldo inicial)
            'saldo_inicial' => number_format($saldo_inicial, 4),
            'fecha_saldo_inicio' => 'SALDO AL ' . $fechaInicio->subDay()->translatedFormat('d \d\e F \d\e Y'),

            // Lista de movimientos para el cuerpo de la tabla
            'movimientos' => $movimientos_periodo,

            // Datos para el pie de tabla
            'total_ingresos' => number_format($totalIngresosPeriodo, 4),
            'total_egresos' => number_format($totalEgresosPeriodo, 4),
            'fecha_saldo_fin' => 'SALDO AL ' . $fechaFin->translatedFormat('d \d\e F \d\e Y'),
            'saldo_final' => number_format($saldo_final, 4),
        ];
    }
}
