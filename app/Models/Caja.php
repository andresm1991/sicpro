<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Caja extends Model
{
    use HasFactory;
    protected $table = 'cajas';

    protected $fillable = [
        'fecha',
        'descripcion',
        'saldo_nuevo',
        'saldo_inicial',
        'saldo_actual',
        'activa',
    ];

    protected $casts = [
        'fecha' => 'date',
    ];

    public function movimientos()
    {
        return $this->hasMany(MovimientoCaja::class);
    }

    // Calcula el saldo actual basado en movimientos
    public function calcularSaldoActual()
    {
        $totalMovimientos = $this->movimientos()
            ->selectRaw('SUM(CASE WHEN tipo = "ingreso" THEN monto ELSE -monto END) as total')
            ->value('total') ?? 0;

        return $this->saldo_inicial + $totalMovimientos;
    }

    // Actualiza el saldo actual en la base de datos
    public function actualizarSaldo()
    {
        $this->saldo_actual = $this->calcularSaldoActual();
        $this->save();
    }

    protected static function booted()
    {
        static::creating(function ($caja) {
            // Si es la primera caja
            if (!self::where('activa', true)->exists()) {
                $caja->saldo_inicial = $caja->saldo_nuevo;
                $caja->activa = true;
                return;
            }

            // Para cajas posteriores
            $cajaAnterior = self::where('activa', true)->first();
            $caja->saldo_inicial = $cajaAnterior->saldo_actual + $caja->saldo_nuevo;
        });

        static::created(function ($caja) {
            // Desactivar todas las demás cajas
            self::where('id', '!=', $caja->id)->update(['activa' => false]);

            // Actualizar saldo actual (por si hay movimientos inmediatos)
            $caja->actualizarSaldo();
        });
    }
}
