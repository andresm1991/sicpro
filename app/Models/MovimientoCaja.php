<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MovimientoCaja extends Model
{
    use HasFactory;

    protected $table = 'movimientos_caja';

    protected $fillable = [
        'caja_id',
        'tipo',
        'monto',
        'descripcion',
        'referencia',
        'user_id',
        'origen_type', // Ej: 'App\Models\Venta', 'App\Models\Gasto'
        'origen_id'   // ID del modelo relacionado
    ];

    // Relación polimórfica para registrar de dónde viene el movimiento
    public function origen()
    {
        return $this->morphTo();
    }

    public function caja()
    {
        return $this->belongsTo(Caja::class);
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    protected static function booted()
    {
        static::created(function ($movimiento) {
            // Solo actualizamos si es la caja activa
            if ($movimiento->caja->activa) {
                $movimiento->caja->actualizarSaldo();
            }
        });

        static::updated(function ($movimiento) {
            if ($movimiento->caja->activa) {
                $movimiento->caja->actualizarSaldo();
            }
        });

        static::deleted(function ($movimiento) {
            if ($movimiento->caja->activa) {
                $movimiento->caja->actualizarSaldo();
            }
        });
    }
}
