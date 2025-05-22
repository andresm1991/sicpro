<?php

namespace App\Services;

use App\Models\Caja;
use App\Models\MovimientoCaja;

class CajaService
{
    public function crearNuevaCaja(float $saldoNuevo, string|null $descripcion = null)
    {
        return Caja::create([
            'descripcion' => $descripcion,
            'saldo_nuevo' => $saldoNuevo,
            'saldo_actual' => 0 // Se calcula automáticamente
        ]);
    }

    public function registrarMovimiento(array $data)
    {
        $cajaActiva = Caja::where('activa', true)->firstOrFail();

        // Validar saldo para egresos
        if ($data['tipo'] == 'egreso') {
            $saldoDisponible = $cajaActiva->saldo_actual;
            if ($data['monto'] > $saldoDisponible) {
                throw new \Exception("Saldo insuficiente. Disponible: {$saldoDisponible}");
            }
        }

        return $cajaActiva->movimientos()->create($data);
    }

    public function obtenerCajaActiva()
    {
        return Caja::where('activa', true)->with('movimientos')->firstOrFail();
    }
}
