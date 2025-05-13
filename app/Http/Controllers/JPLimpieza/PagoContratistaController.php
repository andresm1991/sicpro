<?php

namespace App\Http\Controllers\JPLimpieza;

use App\Models\CatalogoDato;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\JPLimpieza\Proyecto;
use App\Http\Controllers\Controller;
use App\Models\JPLimpieza\Contratista;
use App\Models\JPLimpieza\PagoContratista;
use App\Http\Requests\JPLimpieza\PagoContratistaStoreRequest;

class PagoContratistaController extends Controller
{
    public function pagos($proyecto, $contratista)
    {
        $proyectoInfo = Proyecto::findOrFail($proyecto);
        $breadcrumbs = [
            ['name' => 'Inicio', 'url' => route('home')],
            ['name' => $proyectoInfo->nombre_proyecto, 'url' => route('jp.limpieza.adquisiciones.index', $proyecto)],
            ['name' => 'Contratistas', 'url' => route('jp.limpieza.contratistas.index', $proyecto)],
            ['name' => 'Pagos', 'url' => ''],
        ];

        $pagos = PagoContratista::where('contratista_id', $contratista)
            ->with(['contratista.proyecto', 'tipoPago', 'estado'])
            ->orderBy('fecha', 'desc')
            ->paginate(15);

        return view('jp_limpieza.contratistas.pagos.index', compact('pagos', 'proyecto', 'contratista', 'breadcrumbs'));
    }

    public function crear($proyecto, $contratista)
    {
        $proyectoInfo = Proyecto::findOrFail($proyecto);
        $contratista = Contratista::findOrFail($contratista);

        $breadcrumbs = [
            ['name' => 'Inicio', 'url' => route('home')],
            ['name' => $proyectoInfo->nombre_proyecto, 'url' => route('jp.limpieza.adquisiciones.index', $proyecto)],
            ['name' => 'Pagos', 'url' => route('jp.limpieza.contratistas.pagos', [$proyecto, $contratista])],
            ['name' => 'Nuevo Pago', 'url' => ''],
        ];

        $pago = new PagoContratista();
        $numero = numeroPedido(PagoContratista::first());
        $formaPagos = CatalogoDato::getChildrenCatalogo('metodos.pagos');
        $estados = CatalogoDato::getChildrenCatalogo('estados.pagos.prestamos');

        return view('jp_limpieza.contratistas.pagos.create', compact('numero', 'proyecto', 'contratista', 'pago', 'formaPagos', 'estados', 'breadcrumbs'));
    }

    public function store(PagoContratistaStoreRequest $request, $proyecto, $contratista)
    {


        try {
            $infoContratista = Contratista::findOrFail($contratista);
            $monto = str_replace(',', '', $request->monto);
            $totalPendiente = $infoContratista->total_pagos;

            if ($monto > $totalPendiente) {
                return redirect()->back()->with('error', 'El monto del pago no puede ser mayor al total de los pagos registrados.');
            } elseif ($monto == $totalPendiente && strtolower($request->tipo_pago) == 'avance') {
                return redirect()->back()->withInput()->with('error', 'El monto del pago es igual al total del saldo pendiente, por lo que no se puede registrar como avance.');
            }

            DB::beginTransaction();
            PagoContratista::create([
                'contratista_id' => $contratista,
                'monto' => $monto,
                'tipo_pago' => $request->tipo_pago,
                'forma_pago_id' => $request->forma_pago,
                'estado_id' => $request->estado,
                'fecha' => $request->fecha,
                'observaciones' => $request->detalle,
                'usuario_id' => auth()->user()->id,
            ]);

            if ($monto == $totalPendiente) {
                $infoContratista->update(['estado_id' => CatalogoDato::getIdCatalogo('estados.contratistas.completado')]);
                $infoContratista->save();
            }

            DB::commit();
            return redirect()->route('jp.limpieza.contratistas.pagos', [$proyecto, $contratista])->with('success', 'Pago registrado exitosamente.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Error al registrar el pago: ' . $e->getMessage());
        }
    }

    public function edit($proyecto, $contratista, PagoContratista $pago)
    {
        $proyectoInfo = Proyecto::findOrFail($proyecto);
        $contratista = Contratista::findOrFail($contratista);


        $breadcrumbs = [
            ['name' => 'Inicio', 'url' => route('home')],
            ['name' => $proyectoInfo->nombre_proyecto, 'url' => route('jp.limpieza.adquisiciones.index', $proyecto)],
            ['name' => 'Pagos Contratista', 'url' => route('jp.limpieza.contratistas.pagos', [$proyecto, $contratista])],
            ['name' => 'Editar Pago', 'url' => ''],
        ];

        $formaPagos = CatalogoDato::getChildrenCatalogo('metodos.pagos');
        $estados = CatalogoDato::getChildrenCatalogo('estados.pagos.prestamos');

        return view('jp_limpieza.contratistas.pagos.edit', compact('pago', 'proyecto', 'contratista', 'formaPagos', 'estados', 'breadcrumbs'));
    }

    public function update(PagoContratistaStoreRequest $request, $proyecto, Contratista $contratista, PagoContratista $pago)
    {
        try {
            DB::beginTransaction();
            $monto = str_replace(',', '', $request->monto);
            $totalPendiente = $contratista->total_pagos;

            // Verificar si el monto del pago es mayor al saldo pendiente
            if ($monto > $totalPendiente) {
                return redirect()->back()->with('error', 'El monto del pago no puede ser mayor al saldo pendiente.');
            } elseif ($monto == $totalPendiente && strtolower($request->tipo_pago) == 'avance') {
                return redirect()->back()->withInput()->with('error', 'El monto del pago es igual al total del saldo pendiente, por lo que no se puede registrar como avance.');
            }

            if ($pago->estado_id == CatalogoDato::getIdCatalogo('estados.pagos.prestamos.pagado') && !auth()->user()->hasRole(['Administrador', 'Gerencial'])) {
                return redirect()->back()->with('error', 'El pago ya fue registrado y no es posible modificarlo.');
            }

            $pago->update([
                'monto' => $monto,
                'tipo_pago' => $request->tipo_pago,
                'forma_pago_id' => $request->forma_pago,
                'estado_id' => $request->estado,
                'fecha' => $request->fecha,
                'observaciones' => $request->detalle,
            ]);

            if ($monto == $totalPendiente) {
                $contratista->update(['estado_id' => CatalogoDato::getIdCatalogo('estados.contratistas.completado')]);
                $contratista->save();
            }

            DB::commit();
            return redirect()->route('jp.limpieza.contratistas.pagos', [$proyecto, $contratista])->with('success', 'Pago actualizado exitosamente.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Error al actualizar el pago: ' . $e->getMessage());
        }
    }

    public function destroy(Request $request)
    {
        try {
            $pago = PagoContratista::findOrFail($request->pago);
            DB::beginTransaction();
            if ($pago->estado_id == CatalogoDato::getIdCatalogo('estados.pagos.prestamos.pagado')) {
                return response()->json(['success' => false, 'message' =>  'El pago ya fue registrado y no es posible eliminarlo.']);
            }
            $pago->delete();
            DB::commit();
            return response()->json(['success' => true, 'message' => 'Pago eliminado exitosamente.']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Error al eliminar el pago: ' . $e->getMessage()]);
        }
    }

    public function buscar(Request $request)
    {
        $output = '';
        $buscar = $request->input('buscar');
        $formaPagoIds = CatalogoDato::where('descripcion', 'LIKE', "%$buscar%")->pluck('id');
        $estadosIds = CatalogoDato::where('descripcion', 'LIKE', "%$buscar%")->pluck('id');

        $pagos = PagoContratista::where('contratista_id', $request->contratista)->where(function ($q) use ($buscar, $estadosIds, $formaPagoIds) {
            $q->where('monto', 'LIKE', "%$buscar%")
                ->orWhere('fecha', 'LIKE', "%$buscar%")
                ->orWhere('tipo_pago', 'LIKE', "%$buscar%")
                ->orWhereIn('estado_id', $estadosIds)
                ->orWhereIn('forma_pago_id', $formaPagoIds)
                // Añadir la condición para buscar en el número generado
                ->orWhereRaw("CONCAT(DATE_FORMAT(fecha, '%Y%m%d'), '-', LPAD(id, 3, '0')) LIKE ?", ["%$buscar%"]);
        })->get();

        if ($pagos) {
            foreach ($pagos as $pago) {
                $editar = " <a href='" . route('jp.limpieza.contratistas.pagos.edit', [$request->proyecto, $request->contratista, $pago->id]) . "' class='dropdown-item'>Editar</a>";
                $eliminar = "<a href='javascript:void(0);' class='dropdown-item eliminar-pago' role='button' id='" . $pago->id . "'>Eliminar</a>";

                $output .= '<tr id="' . $pago->id . '">';
                $output .= '<td class="align-middle">' . $pago->numero_formatted . '</td>';
                $output .= '<td class="align-middle">' . $pago->fecha_formatted . '</td>';
                $output .= '<td class="align-middle">' . $pago->monto . '</td>';
                $output .= '<td class="align-middle">' . $pago->tipo_pago . '</td>';
                $output .= '<td class="align-middle">' . $pago->formaPago->descripcion . '</td>';
                $output .= '<td class="align-middle">' . $pago->estado->descripcion . '</td>';
                $output .= '<td class="align-middle text-right text-truncate">';
                $output .= '<button type="button" class="btn btn-outline-dark" data-container="body" data-toggle="popover" data-placement="left" data-trigger="focus" data-content ="' . $editar . $eliminar . '"> <i class="fas fa-caret-left font-weight-normal"></i> Opciones </button>';
                $output .= '</td>';
                $output .= '</tr>';
            }

            if (empty($output)) {
                $output .= '<tr>' .
                    '<td colspan="7" class="text-center">' .
                    '<span class="text-danger">No existen datos para mostrar.</span>' .
                    '</td>' .
                    '</tr>';
            }
            return Response($output);
        }
    }
}