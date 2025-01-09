<?php

namespace App\Http\Controllers;

use Exception;
use Throwable;
use Carbon\Carbon;
use App\Models\Prestamo;
use App\Models\CatalogoDato;
use App\Models\PagoPrestamo;
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

    private function htmlTable($data, $request = null)
    {
        foreach ($data as $key => $element) {
            $estado = $element->estado->slug == 'estados.prestamos.pagado' ? '<span class="badge badge-success">' . $element->estado->descripcion . '</span>' : '<span class="badge badge-warning">' . $element->estado->descripcion . '</span>';
            $editar = "<a href='javascriopt:void(0);' class='dropdown-item editar' id='" . $element->id . "'>Editar</a>";
            $eliminar = "<a href='#' class='dropdown-item eliminar' id='" . $element->id . "'>Eliminar</a>";
            $detalle = "<a href='" . route('administrativo.prestamos.detalle.prestamo', $element->id) . "' class='dropdown-item'>Detalle</a>";

            $request .= ' <tr id="' . $element->id . '">' .
                '<td class="align-middle">' . strtoupper($element->trabajador->razon_social) . '</td>' .
                '<td class="align-middle">$ ' . number_format($element->monto, 2) . '</td>' .
                '<td class="align-middle">' . $element->plazo . ' semanas</td>' .
                '<td class="align-middle">$ ' . number_format($element->saldo, 2) . '</td>' .
                '<td class="align-middle">' . dateFormatHumans($element->fecha_vencimiento) . '</td>' .
                '<td class="align-middle text-capitalize">' . $estado . '</td>' .
                '<td class="align-middle align-middle text-right text-truncate">' .
                '<button type="button" class="btn btn-outline-dark" data-container="body" data-toggle="popover" data-placement="left" data-trigger="focus" data-content ="' . $detalle . $editar . $eliminar  . '">
                        <i class="fas fa-caret-left font-weight-normal"></i> Opciones
                    </button>' .
                '</td>' .
                '</tr>';
        }

        return $request;
    }
}