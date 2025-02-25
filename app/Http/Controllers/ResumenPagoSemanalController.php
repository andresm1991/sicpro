<?php

namespace App\Http\Controllers;

use App\Models\CatalogoDato;
use App\Models\DetalleResumenPagoSemanal;
use Illuminate\Http\Request;
use App\Models\ResumenPagoSemanal;
use App\Services\PushNotificationService;
use Illuminate\Support\Facades\DB;

class ResumenPagoSemanalController extends Controller
{
    public function index()
    {
        $title_page = 'Agenda';
        $breadcrumbs = [
            ['name' => 'Inicio', 'url' => route('home')],
            ['name' => 'administrativo', 'url' => route('administrativo.menu.construccion')],
            ['name' => 'resumen pagos semanales', 'url' => '']
        ];

        $resumen_pagos = ResumenPagoSemanal::paginate(15);

        return view('resumen_pagos_semanales.index', compact('title_page', 'breadcrumbs', 'resumen_pagos'));
    }

    public function store(Request $request)
    {
        try {
            DB::beginTransaction();
            $resumen_id = $request->resumen_id;
            $items = $request->items;
            // Extraer los valores y limpiarlos
            $valoresLimpios = array_map('limpiarValor', array_column($items, 'valor'));

            // Calcular el total
            $total = array_sum($valoresLimpios);

            if ($resumen_id != null) {
                $resumen = ResumenPagoSemanal::find($resumen_id);
                $resumen->total = $total;
                $resumen->save();
            } else {
                $resumen = ResumenPagoSemanal::create([
                    'fecha' => date('Y-m-d'),
                    'total' => $total,
                    'estado_id' => CatalogoDato::getIdCatalogo('estados.resumen.pagos.semanales.pendiente'),
                ]);
            }
            $detale_actuales = $resumen->detalle_resumen_pago_semanal()->pluck('id')->toArray();
            $detalle_mantener = [];

            foreach ($items as $key => $item) {
                $descripcion = $item['descripcion'];
                $valor = preg_replace('/[^0-9.-]/', '', $item['valor']);

                if ($resumen) {
                    $detalle_id =  DetalleResumenPagoSemanal::create([
                        'resumen_pago_semanal_id' => $resumen->id,
                        'descripcion' => $descripcion,
                        'monto' => $valor,
                    ])->id;
                    $detalle_mantener[] = $detalle_id;
                }
            }

            $detallesAEliminar = array_diff($detale_actuales, $detalle_mantener);
            if (!empty($detallesAEliminar)) {
                DetalleResumenPagoSemanal::where('resumen_pago_semanal_id', $resumen->id)
                    ->whereIn('id', $detallesAEliminar)
                    ->delete();
            }

            DB::commit();

            PushNotificationService::sendNotification(auth()->user(), 'Resumen de pagos semanales', 'Se ha generado el resumenen de pagos', route('pdf.resumen.pago.semanal', $resumen->id));

            return response()->json([
                'success' => true,
                'message' => 'Resumen de pago semanal creado correctamente',
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Ocurrió un error al crear el resumen de pago semanal',
                'error' => $e->getMessage(),
            ]);
        }
    }

    public function edit(Request $request)
    {
        $resumen_pago = ResumenPagoSemanal::with('detalle_resumen_pago_semanal')->find($request->id);
        // $detalle = $resumen_pago->detalle_resumen_pago_semanal;
        return response()->json([
            'success' => true,
            'resumen_pago' => $resumen_pago,
            // 'detalle' => $detalle,
        ]);
    }

    public function destroy(Request $request)
    {
        try {
            DB::beginTransaction();
            ResumenPagoSemanal::find($request->id)->delete();
            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'Resumen de pago semanal eliminado correctamente',
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Ocurrió un error al eliminar el resumen de pago semanal',
                'error' => $e->getMessage(),
            ]);
        }
    }


    public function buscar(Request $request)
    {
        if ($request->ajax()) {
            $buscar = $request->text;
            $output = "";
            $resumen_pagos = ResumenPagoSemanal::where('fecha', 'LIKE', '%' . $buscar . '%')
                ->orWhere('total', 'LIKE', '%' . $buscar . '%')
                //->orWhereHas('estado', function ($query) use ($buscar) {
                //  $query->where('descripcion', 'LIKE', '%' . $buscar . '%');
                //})
                ->get();

            if ($resumen_pagos) {
                foreach ($resumen_pagos as $resumen) {
                    if ($resumen->estado->slug == 'estados.resumen.pagos.semanales.pendiente') {
                        $estado = '<span class="badge badge-warning">' . $resumen->estado->descripcion . '</span>';
                    } elseif ($resumen->estado->slug == 'estados.resumen.pagos.semanales.aprobado') {
                        $estado = '<span class="badge badge-success">' . $resumen->estado->descripcion . '</span>';
                    } elseif ($resumen->estado->slug == 'estados.resumen.pagos.semanales.cancelado') {
                        $estado = '<span class="badge badge-danger">' . $resumen->estado->descripcion . '</span>';
                    } else {
                        $estado = 'Sin definir';
                    }

                    $output .= '<tr id="' . $resumen->id . '">' .
                        '<th class="align-middle">' . $resumen->id . '</th>' .
                        '<td class="align-middle">' . dateFormat('Y-m-d', 'd-m-Y', $resumen->fecha) . '</td>' .
                        '<td class="align-middle">$ ' . number_format($resumen->total, 4) . '</td>' .
                        '<td class="align-middle"><span class="badge badge-success">Generado</span></td>' .
                        '<td class="align-middle table-actions">' .
                        '<a href="javascript:void(0);" class="btn btn-dark btn-sm editar-resumen mr-1"
                                            data-toggle="modal" data-backdrop="static" data-keyboard="false"
                                            data-target="#pagoSemanalModal" id="' . $resumen->id . '">
                                            <i class="fa-light fa-edit"></i>
                                        </a>' .
                        '<a href="' . route('pdf.resumen.pago.semanal', $resumen->id) . '"
                                            class="btn btn-dark btn-sm mr-1" target="__blank">
                                            <i class="fa-solid fa-file-pdf"></i>
                                        </a>' .
                        '<a href="javascript:void(0);" class="btn btn-dark btn-sm eliminar-resumen"
                                            id="' . $resumen->id . '">
                                            <i class="fa-light fa-trash"></i>
                                        </a>' .
                        '</td>' .
                        '</tr>';
                }

                if (empty($output)) {
                    $output .= '<tr>' .
                        '<td colspan="5" class="text-center text-danger">No existen datos para mostrar.</td>' .
                        '</tr>';
                }
                return Response($output);
            }
        }
    }
}
