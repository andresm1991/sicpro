<?php

namespace App\Models\JPLimpieza;

use App\Models\User;
use App\Models\Articulo;
use App\Models\Proveedor;
use Illuminate\Database\Eloquent\Model;

class Caja extends Model
{
    protected $connection = 'mysql_jp_limpieza';
    protected $table = 'caja';

    protected $fillable = [
        'fecha',
        'proveedor_id',
        'producto_id',
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

    public function usuario()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function proveedor()
    {
        return $this->belongsTo(Proveedor::class, 'proveedor_id');
    }

    public function producto()
    {
        return $this->belongsTo(Producto::class, 'producto_id');
    }

    public function getMontoFormattedAttribute()
    {
        return '$' . number_format($this->monto, 4);
    }

    public function getSaldoFormattedAttribute()
    {
        $saldo = 0;
        if ($this->tipo === 'ingreso') {
            $saldo += $this->monto;
        } else {
            $saldo -= $this->monto;
        }

        return number_format($saldo, 4);
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
                    'producto_id' => $request->input('articulo'),
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
}
