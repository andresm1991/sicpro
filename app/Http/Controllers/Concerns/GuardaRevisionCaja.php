<?php

namespace App\Http\Controllers\Concerns;

use App\Support\RangoFechas;
use Illuminate\Http\Request;

/**
 * Guardado de la revision de caja.
 *
 * El metodo estaba duplicado, identico, en ReporteriaController y en
 * JPLimpieza\ReporteController: la unica diferencia es el modelo, porque cada
 * modulo vive en su propia base.
 */
trait GuardaRevisionCaja
{
    /**
     * Modelo de RevisionCaja de la base que corresponde al controlador.
     *
     * @return class-string<\Illuminate\Database\Eloquent\Model>
     */
    abstract protected function modeloRevisionCaja(): string;

    /**
     * Guardar revision de caja
     */
    public function guardarRevisionCaja(Request $request)
    {
        try {
            $request->validate([
                'fechas' => 'required',
            ]);

            [$fechaInicio, $fechaFin] = RangoFechas::requerido($request->input('fechas'));

            $data = [
                'fecha_revision_inicio' => $fechaInicio,
                'fecha_revision_fin' => $fechaFin,
                'usuario_id' => auth()->id(),
                'observaciones' => $request->observaciones,
            ];

            $modelo = $this->modeloRevisionCaja();
            $modelo::create($data);

            return response()->json([
                'success' => true,
                'mensaje' => 'Revisión de caja guardada exitosamente.',
                'data' => $data,
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'mensaje' => 'Error al guardar la revisión de caja: ' . $th->getMessage(),
                'error' => $th->getLine(),
            ]);
        }
    }
}
