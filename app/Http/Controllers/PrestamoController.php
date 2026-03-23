<?php

namespace App\Http\Controllers;

use Exception;
use Throwable;
use Carbon\Carbon;
use App\Models\Prestamo;
use App\Models\CatalogoDato;
use App\Models\PagoPrestamo;
use App\Services\LogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
        if ($request->ajax()) {
            // quitar $ del parametro monto
            $request->merge(['monto' => preg_replace('/[^0-9.]/', '', $request->monto)]);
            // obtener estado del catalogo de datos
            $estado = CatalogoDato::find($request->estado);
            $fecha_actual = Carbon::now();

            $fecha_vencimiento = $estado->slug == 'estados.prestamos.aprobado' ? $fecha_actual->addWeeks($request->plazo) : null;

            $parametros = [
                'trabajador_id' => $request->proveedor,
                'fecha_solicitud' => $request->fecha_solicitud,
                'fecha_aprobacion' => $request->estado == 53 ? $request->fecha_solicitud : null,
                'fecha_vencimiento' => $fecha_vencimiento,
                'monto' => $request->monto,
                'saldo' => $request->monto,
                'interes' => $request->interes,
                'plazo' => $request->plazo,
                'estado_id' => $request->estado,
                'motivo' => $request->motivo,
            ];

            try {
                DB::beginTransaction();
                if ($prestamo = Prestamo::create($parametros)) {
                    if ($prestamo->estado->slug == 'estados.prestamos.aprobado') {
                        $estado_pago = CatalogoDato::getIdCatalogo('estados.pagos.prestamos.pendiente');
                        $metodo_pago = CatalogoDato::getIdCatalogo('metodos.pagos.otro');

                        $cuota_semanal = calcularCuotaSemanalPrestamo($request->monto, $request->interes, $request->plazo);
                        $fechasPago = calcularFechasPago(Carbon::now(), $request->plazo);

                        foreach ($fechasPago as $fecha) {
                            PagoPrestamo::create([
                                'prestamo_id' => $prestamo->id,
                                'fecha_pago' => $fecha,
                                'monto_pagado' => 0,
                                'monto_programado' => $cuota_semanal,
                                'estado_id' => $estado_pago,
                                'metodo_pago_id' => $metodo_pago,
                            ]);
                        }
                    }
                    DB::commit();
                    $prestamos = Prestamo::orderBy('fecha_solicitud', 'desc')->paginate(15);
                    $list_prestamos = $this->htmlTable($prestamos);

                    return response()->json(['success' => true, 'mensaje' => 'Datos guardado correctamente.', 'prestamos' => $list_prestamos]);
                } else {
                    throw new Exception("Error al intentar guardar la información.");
                }
            } catch (Throwable $e) {
                DB::rollBack();
                return response()->json(['success' => false, 'mensaje' => $e->getMessage()]);
            }
        }
    }

    public function updatePrestamo(Request $request, Prestamo $prestamo)
    {
        if ($request->ajax()) {
            try {
                if ($prestamo->estado->descripcion == 'Pagado') {
                    return response()->json(['success' => false, 'mensaje' => 'No es posible actualizar la información del préstamo porque está pagado.']);
                }

                DB::transaction(function () use ($request, $prestamo) {
                    // Campos editables
                    $prestamo->trabajador_id = $request->proveedor ?? $prestamo->trabajador_id;
                    $prestamo->monto = $request->monto !== null ? preg_replace('/[^0-9.]/', '', $request->monto) : $prestamo->monto;
                    $prestamo->interes = $request->interes ?? $prestamo->interes;
                    $prestamo->motivo = $request->motivo ?? $prestamo->motivo;

                    $estadoAprobadoId = CatalogoDato::getIdCatalogo('estados.prestamos.aprobado');
                    $estadoAnteriorId = $prestamo->estado_id;
                    $nuevoEstadoId = $request->estado ?? $prestamo->estado_id;

                    $prestamo->estado_id = $nuevoEstadoId;
                    $prestamo->fecha_solicitud = $request->fecha_solicitud ?? $prestamo->fecha_solicitud;

                    // Actualizar fechas de aprobación/vencimiento cuando se aprueba el préstamo o está pendiente de fechas
                    if (is_null($prestamo->fecha_aprobacion)) {
                        $fechaAprobacion = Carbon::now();
                        $prestamo->fecha_aprobacion = $fechaAprobacion;

                        $plazo = $request->plazo ? (int) $request->plazo : $prestamo->plazo;
                        $prestamo->fecha_vencimiento = $fechaAprobacion->copy()->addWeeks($plazo);
                    } else {
                        $prestamo->fecha_aprobacion = $request->fecha_aprobacion ?? $prestamo->fecha_aprobacion;
                        $prestamo->fecha_vencimiento = $request->fecha_vencimiento ?? $prestamo->fecha_vencimiento;
                    }

                    // Plazo y ajustes de pago
                    $plazoActual = $prestamo->plazo;
                    $nuevoPlazo = (int) $request->plazo;
                    $prestamo->plazo = $nuevoPlazo;

                    // Si el plazo se incrementa, calcular nuevos pagos adicionales
                    if ($nuevoPlazo > $plazoActual) {
                        $saldoRestante = $prestamo->saldo > 0 ? $prestamo->saldo : ($prestamo->monto - $prestamo->pago_prestamo->sum('monto_pagado'));
                        if ($saldoRestante <= 0) {
                            throw new Exception('No hay saldo pendiente para recalcular.');
                        }

                        $pagosPendientes = $prestamo->pago_prestamo()
                            ->whereHas('estado', function ($query) {
                                $query->where('descripcion', 'Pendiente');
                            })
                            ->orderBy('fecha_pago', 'asc')
                            ->get();

                        $semanasAdicionales = $nuevoPlazo - $plazoActual;

                        if ($pagosPendientes->count() > 0) {
                            $montoYaProgramado = $pagosPendientes->sum('monto_programado');
                            $saldoParaAdicionales = $saldoRestante - $montoYaProgramado;

                            if ($saldoParaAdicionales <= 0) {
                                throw new Exception('No es posible agregar semanas adicionales porque el saldo ya está cubierto por los pagos pendientes.');
                            }

                            $montoPorSemanaAdicional = round($saldoParaAdicionales / $semanasAdicionales, 2);
                            $ultimaFechaPago = Carbon::parse($pagosPendientes->last()->fecha_pago);
                            $this->agregarPagosAdicionales($prestamo, $semanasAdicionales, $montoPorSemanaAdicional, $ultimaFechaPago);
                        } else {
                            $pagosRealizados = $prestamo->pago_prestamo()
                                ->whereHas('estado', function ($query) {
                                    $query->where('descripcion', 'Pagado');
                                })
                                ->count();

                            if ($pagosRealizados > 0) {
                                $ultimoPago = $prestamo->pago_prestamo()->orderBy('fecha_pago', 'desc')->first();

                                if ($ultimoPago) {
                                    $montoPorSemanaAdicional = round($saldoRestante / $semanasAdicionales, 2);
                                    $ultimaFechaPago = Carbon::parse($ultimoPago->fecha_pago);
                                    $this->agregarPagosAdicionales($prestamo, $semanasAdicionales, $montoPorSemanaAdicional, $ultimaFechaPago);
                                } else {
                                    $montoPorSemana = round($saldoRestante / $nuevoPlazo, 2);
                                    $this->agregarTodosPagos($prestamo, $nuevoPlazo, $montoPorSemana);
                                }
                            } else {
                                $montoPorSemana = round($saldoRestante / $nuevoPlazo, 2);
                                $this->agregarTodosPagos($prestamo, $nuevoPlazo, $montoPorSemana);
                            }
                        }
                    } elseif ($nuevoPlazo < $plazoActual) {
                        // Si se desea reducir plazo, bloqueamos para mantener integridad de pagos
                        throw new Exception('El nuevo plazo no puede ser menor al actual.');
                    }

                    // Recalcular saldo si se modifica monto
                    if ($request->monto !== null) {
                        $prestamo->saldo = $prestamo->monto - $prestamo->pago_prestamo->sum('monto_pagado');
                    }

                    $prestamo->save();
                });

                return response()->json(['success' => true, 'mensaje' => 'Préstamo actualizado correctamente.']);
            } catch (\Throwable $e) {
                LogService::log('error', 'Error al actualizar el prestamo', [
                    'user_id' => auth()->id(),
                    'action' => 'update',
                    'message' => $e->getMessage(),
                    'prestamo_id' => $prestamo->id
                ]);

                return response()->json(['success' => false, 'mensaje' => 'Error al actualizar el préstamo.', 'error' => $e->getMessage()]);
            }
        }

        abort(404);
    }

    /**
     * Agrega pagos adicionales para el aumento del plazo
     */
    private function agregarPagosAdicionales($prestamo, $semanasAdicionales, $montoSemanal, Carbon $ultimaFechaPago)
    {
        // Si no hay semanas adicionales, no hacer nada
        if ($semanasAdicionales <= 0) {
            return;
        }

        // Validar que el monto no sea negativo o cero
        if ($montoSemanal <= 0) {
            throw new Exception('El monto por semana adicional no puede ser cero o negativo.');
        }

        // Generar fechas para las semanas adicionales
        for ($i = 1; $i <= $semanasAdicionales; $i++) {
            $nuevaFecha = $ultimaFechaPago->copy()->addWeeks($i);

            $prestamo->pago_prestamo()->create([
                'fecha_pago' => $nuevaFecha,
                'monto_programado' => $montoSemanal,
                'estado_id' => CatalogoDato::getIdCatalogo('estados.pagos.prestamos.pendiente'),
                'metodo_pago_id' => CatalogoDato::getIdCatalogo('metodos.pagos.otro'),
                'monto_pagado' => 0,
            ]);
        }
    }

    /**
     * Agrega todos los pagos del nuevo plazo (solo cuando no hay pagos existentes)
     */
    private function agregarTodosPagos($prestamo, $nuevoPlazo, $montoSemanal)
    {
        // Generar todas las fechas de pago desde hoy
        $nuevasFechasPago = calcularFechasPago(Carbon::now(), $nuevoPlazo);

        // Crear todos los pagos
        foreach ($nuevasFechasPago as $fecha) {
            $prestamo->pago_prestamo()->create([
                'fecha_pago' => $fecha,
                'monto_programado' => $montoSemanal,
                'estado_id' => CatalogoDato::getIdCatalogo('estados.pagos.prestamos.pendiente'),
                'metodo_pago_id' => CatalogoDato::getIdCatalogo('metodos.pagos.otro'),
                'monto_pagado' => 0,
            ]);
        }
    }

    public function detallePrestamo(Prestamo $prestamo)
    {
        $title_page = 'Detalle Prestamo';

        $breadcrumbs = [
            ['name' => 'Inicio', 'url' => route('home')],
            ['name' => 'Prestamos', 'url' => route('administrativo.prestamos.index')],
            ['name' => 'Detalle', 'url' => '']
        ];

        $pagos = PagoPrestamo::where('prestamo_id', $prestamo->id)->orderBy('fecha_pago', 'asc')->paginate(15);

        $route_params = ['prestamo' => $prestamo, 'pagos' => $pagos, 'breadcrumbs' => $breadcrumbs, 'title_page' => $title_page];
        return view('administrativo.prestamos.pagos', $route_params);
    }


    public function registrarPago(Request $request, PagoPrestamo $pago)
    {
        if ($request->ajax()) {
            $prestamo = Prestamo::find($pago->prestamo_id);
            $monto_pagado = str_replace(',', '', $request->monto_pagado);
            $forma_pago = $request->forma_pago;

            try {
                DB::beginTransaction();

                // Validar que el monto no exceda el saldo pendiente del préstamo
                if ($monto_pagado > $prestamo->saldo) {
                    throw new Exception("El monto a pagar no puede exceder el saldo restante del préstamo.");
                }
                // Validar si ya esta pagado
                if ($pago->estado->descripcion == 'Pagado') {
                    throw new Exception("Ya se ecuentra registrado el pago.");
                }

                // Actualizar el estado del pago y el monto pagado
                $pago->monto_pagado = $monto_pagado;
                $pago->estado_id = CatalogoDato::getIdCatalogo('estados.pagos.prestamos.pagado');
                $pago->metodo_pago_id = $forma_pago;

                $pago->save();

                // Actualizar el saldo del préstamo
                $prestamo->saldo = $prestamo->saldo - $monto_pagado;
                $prestamo->estado_id = $prestamo->saldo - $monto_pagado <= 0 ? CatalogoDato::getIdCatalogo('estados.prestamos.pagado') : CatalogoDato::getIdCatalogo('estados.prestamos.pendiente');
                $prestamo->save();

                DB::commit();
                return response()->json(['success' => true, 'mensaje' => 'Pago registrado correctamente.']);
            } catch (Throwable $e) {
                DB::rollBack();
                return response()->json(['success' => false, 'mensaje' => $e->getMessage()]);
            }
        }
    }

    public function recalcularPagos(Request $request)
    {
        if ($request->ajax()) {
            $prestamoId = $request->prestamo;
            $prestamo = Prestamo::with('pago_prestamo')->findOrFail($prestamoId);

            // Calcular saldo restante del préstamo
            $saldoRestante = $prestamo->saldo;

            // Obtener pagos pendientes
            $pagosPendientes = $prestamo->pago_prestamo()->whereHas('estado', function ($query) {
                $query->where('descripcion', 'Pendiente');
            })->orderBy('fecha_pago', 'asc')->get();

            return $pagosPendientes->count();

            if ($saldoRestante <= 0) {
                return response()->json(['success' => false, 'mensaje' => 'No es posible recalcular los pagos porque no existe pagos pendiente.']);
            } else if ($pagosPendientes->count() == 0) {
                $cuota_semanal = calcularCuotaSemanalPrestamo($prestamo->monto, $prestamo->interes, $prestamo->plazo);
                $fechasPago = calcularFechasPago($prestamo->fecha_solicitud, $prestamo->plazo);

                $estado_pago = CatalogoDato::getIdCatalogo('estados.pagos.prestamos.pendiente');
                $metodo_pago = CatalogoDato::getIdCatalogo('metodos.pagos.otro');


                foreach ($fechasPago as $fecha) {
                    PagoPrestamo::updateOrCreate([
                        'prestamo_id' => $prestamo->id,
                        'fecha_pago' => $fecha,
                    ], [
                        'monto_pagado' => 0,
                        'monto_programado' => $cuota_semanal,
                        'estado_id' => $estado_pago,
                        'metodo_pago_id' => $metodo_pago,
                    ]);
                }
                return response()->json(['success' => true, 'mensaje' => 'Se han generado los pagos programados para el préstamo.']);
            }

            // Distribuir saldo restante proporcionalmente entre los pagos pendientes
            $nuevaCuota = round($saldoRestante / $pagosPendientes->count(), 2);

            try {
                DB::transaction(function () use ($pagosPendientes, $nuevaCuota, $saldoRestante) {
                    foreach ($pagosPendientes as $pago) {
                        // Actualizar el monto de la cuota
                        $pago->monto_programado = $nuevaCuota;
                        $pago->save();

                        // Restar el monto del saldo restante
                        $saldoRestante -= $nuevaCuota;
                    }
                });

                $pagos = PagoPrestamo::where('prestamo_id', $prestamoId)->orderBy('fecha_pago', 'asc')->paginate(15);
                $htmlPagos = '';
                foreach ($pagos as $pago) {
                    $estado = $pago->estado->slug == 'estados.pagos.prestamos.pagado' ? 'success' : ($pago->estado->slug == 'estados.pagos.prestamos.pendiente' ? 'warning' : 'danger');
                    $htmlPagos .= '<tr id="' . $pago->id . '">' .
                        '<td class="align-middle">' . $pago->fecha_pago . '</td>' .
                        '<td class="align-middle">$ ' . number_format($pago->monto_programado, 2) . '</td>' .
                        '<td class="align-middle">$ ' . number_format($pago->monto_pagado, 2) . '</td>' .
                        '<td class="align-middle">' . $pago->metodo_pago->descripcion . '</td>' .
                        '<td class="align-middle"><span class="badge badge-' . $estado . '">' . $pago->estado->descripcion . '</span> </td>' .
                        '<td class="align-middle">' .
                        '<div class="btn-group  dropleft">' .
                        '<button type="button" class="btn btn-outline-dark dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"> Opciones </button>' .
                        '<div class="dropdown-menu">' .
                        '<a class="dropdown-item registrar-pago" href="javascript:void(0)" data-monto-programado="' . $pago->monto_programado . '" data-estado="' . $pago->estado->descripcion . '" data-pago="' . $pago->id . '">Registrar Pago</a>' .
                        '<a class="dropdown-item posponer-pago" data-pago="' . $pago->id . '" href="javascript:void(0)">Posponer Pago</a>' .
                        '</div>' .
                        '</div>' .
                        '</td>' .
                        '</tr>';
                }

                return response()->json(['success' => true, 'mensaje' => 'Proceso realizado con éxito.', 'listPagos' => $htmlPagos]);
            } catch (Throwable $e) {
                LogService::log('error', 'Error al recalcular pagos prestamos', ['user_id' => auth()->id(), 'action' => 'update', 'message' => $e->getMessage()]);
                return response()->json(['success' => false, 'mensaje' => 'Ocurrió un error en la transacción.']);
            }
        }

        abort(404);
    }

    public function posponerPago(Request $request)
    {
        if ($request->ajax()) {
            $pago_id = $request->pago;
            $pago = PagoPrestamo::find($pago_id);
            if ($pago->estado_id == CatalogoDato::getIdCatalogo('estados.pagos.prestamos.pendiente')) {
                $pago->estado_id = CatalogoDato::getIdCatalogo('estados.pagos.prestamos.postergado');
                if ($pago->save()) {
                    return response()->json(['success' => true, 'mensaje' => 'Proceso realizado con éxito.', 'nuevoEstado' => 'Postergado']);
                } else {
                    return response()->json(['success' => false, 'mensaje' => 'Ocurrió un error, por favor vuelva a intentarlo.']);
                }
            } else {
                return response()->json(['success' => false, 'mensaje' => 'No es posible posponer un pago que no esté pendiente.']);
            }
        }
        abort(404);
    }


    public function buscar(Request $request)
    {
        if ($request->ajax()) {
            $buscar = $request->text;

            $prestamos = Prestamo::whereHas('trabajador', function ($query) use ($buscar) {
                return $query->where('razon_social', 'LIKE', '%' . $buscar . "%");
            })
                ->orWhereHas('estado', function ($query) use ($buscar) {
                    return $query->where('descripcion', 'LIKE', '%' . $buscar . "%");
                })
                ->orderBy('fecha_solicitud', 'asc')
                ->get();



            $output = $this->htmlTable($prestamos);
            if (empty($output)) {
                $output .= '<tr>' .
                    '<td colspan="7" class="text-center">' .
                    '<span class="text-danger">No existen datos para mostrar.</span>' .
                    '</td>' .
                    '</tr>';
            }
            return Response($output);
        }

        abort(404);
    }

    private function htmlTable($data, $request = null)
    {
        foreach ($data as $key => $element) {
            $estado = $element->estado->slug == 'estados.prestamos.pagado' ? '<span class="badge badge-success">' . $element->estado->descripcion . '</span>' : '<span class="badge badge-warning">' . $element->estado->descripcion . '</span>';
            $editar = "<a href='javascriopt:void(0);' class='dropdown-item editar' data-prestamo='" . $element->id . "' data-monto='" . $element->monto . "' data-plazo='" . $element->plazo . "' data-saldo='" . $element->saldo . "' data-interes='" . $element->interes . "' data-motivo='" . $element->motivo . "' data-estado='" . $element->estado_id . "' data-trabajador='" . $element->trabajador_id . "' data-fecha-solicitud='" . $element->fecha_solicitud . "' data-fecha-aprobacion='" . $element->fecha_aprobacion . "' data-fecha-vencimiento='" . $element->fecha_vencimiento . "'>Editar</a>";
            $eliminar = "<a href='#' class='dropdown-item eliminar' id='" . $element->id . "'>Eliminar</a>";
            $detalle = "<a href='" . route('administrativo.prestamos.detalle.prestamo', $element->id) . "' class='dropdown-item'>Detalle</a>";

            $request .= ' <tr id="' . $element->id . '">' .
                '<td class="align-middle">' . strtoupper($element->trabajador->razon_social) . '</td>' .
                '<td class="align-middle">$ ' . number_format($element->monto, 2) . '</td>' .
                '<td class="align-middle">' . $element->plazo . ' semanas</td>' .
                '<td class="align-middle">$ ' . number_format($element->saldo, 2) . '</td>' .
                '<td class="align-middle">' . dateFormatHumans($element->fecha_vencimiento) . '</td>' .
                '<td class="align-middle text-capitalize">' . $estado . '</td>' .
                '<td class="align-middle ">' .
                '<div class="btn-group  dropleft">' .
                '<button type="button" class="btn btn-outline-dark dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Opciones</button>' .
                '<div class="dropdown-menu">' . $detalle . $editar . '</div>' .
                '</div>' .
                '</td>' .
                '</tr>';
        }

        return $request;
    }
}
