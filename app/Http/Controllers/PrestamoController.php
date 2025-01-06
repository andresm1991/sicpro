<?php

namespace App\Http\Controllers;

use App\Models\PagoPrestamo;
use App\Models\Prestamo;
use Carbon\Carbon;
use Illuminate\Http\Request;

class PrestamoController extends Controller
{
    public function index()
    {
        $title_page = 'Prestamos';

        $breadcrumbs = [
            ['name' => 'Inicio', 'url' => route('home')],
            ['name' => 'Administrativo', 'url' => route('administrativo.index')],
            ['name' => 'Prestamos', 'url' => '']
        ];

        $prestamos = Prestamo::orderBy('fecha_solicitud', 'desc')->paginate(15);

        $route_params = ['prestamos' => $prestamos, 'breadcrumbs' => $breadcrumbs, 'title_page' => $title_page];
        return view('administrativo.prestamos.index', $route_params);
    }

    public function create(Request $request)
    {
        // quitar $ del parametro monto
        $request->merge(['monto' => preg_replace('/[^0-9.]/', '', $request->monto)]);
        $fecha_actual = Carbon::now();
        $fecha_vencimiento = $request->estado == 53 ? $fecha_actual->addWeeks($request->plazo) : null;

        $parametros = [
            'trabajador_id' => $request->proveedor,
            'fecha_solicitud' => $request->fecha_solicitud,
            'fecha_aprobacion' => $request->estado == 53 ? $request->fecha_solicitud : null,
            'fecha_vencimiento' => $fecha_vencimiento,
            'monto' => $request->monto,
            'interes' => $request->interes,
            'plazo' => $request->plazo,
            'estado_id' => $request->estado,
            'motivo' => $request->motivo,
        ];

        if ($prestamo = Prestamo::create($parametros)) {
            if ($prestamo->estado->id == 53) {
                $monto_semanal = round($prestamo->monto / $prestamo->plazo, 2);
                PagoPrestamo::create([
                    'prestamo_id' => $prestamo->id,
                    'fecha_pago' => '',
                    'monto_pagado' => '',
                    'monto_programado' => $monto_semanal,
                    'estado_id' => '',
                    'metodo_pago_id'
                ]);
            }
        }
    }
}