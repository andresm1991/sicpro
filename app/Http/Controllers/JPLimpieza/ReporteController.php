<?php

namespace App\Http\Controllers\JPLimpieza;

use Illuminate\Http\Request;
use App\Models\JPLimpieza\Caja;
use App\Http\Controllers\Controller;
use App\Models\JPLimpieza\Adquisicion;
use App\Models\JPLimpieza\Producto;
use App\Models\JPLimpieza\Proyecto;
use App\Models\JPLimpieza\RevisionCaja;

class ReporteController extends Controller
{
    public function index()
    {
        $title_page = 'Reportes';
        $breadcrumbs = [
            ['name' => 'Inicio', 'url' => route('home')],
            ['name' => 'JP Limpieza', 'url' => route('jp.limpieza.index')],
            ['name' => 'Reportes', 'url' => '']
        ];

        return view('jp_limpieza.reportes.index', compact('title_page', 'breadcrumbs'));
    }

    /**
     * Reporte Adquisiciones
     */
    public function adquisiciones()
    {
        $title_page = 'Reporte Adquisiciones';
        $breadcrumbs = [
            ['name' => 'Inicio', 'url' => route('home')],
            ['name' => 'Reportes', 'url' => route('jp.limpieza.reporte.index')],
            ['name' => 'Adquisiciones', 'url' => ''],
        ];

        return view('jp_limpieza.reportes.adquisiciones', compact('title_page', 'breadcrumbs'));
    }

    /**
     * Visualizar reporte de adquisiciones
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function visualizarReporteAdquisiciones(Request $request)
    {
        if ($request->ajax()) {
            try {
                $html = '';
                $reportData = '';
                $tipo_reporte = $request->tipo_reporte;
                $grandTotal = 0;

                if ($tipo_reporte == 'operativo') {
                    $reportData = Adquisicion::dataReporteAdquisiciones($request);

                    // Calcula el gran total
                    foreach ($reportData as $categoria => $items) {
                        if ($categoria === 'Mano de Obra') {
                            // Asegúrate de que el campo 'costo' existe y es numérico
                            $grandTotal += collect($items)->sum('total_recibir');
                        } elseif ($categoria === 'Contratistas') {
                            // Asegúrate de que el campo 'monto total' existe y es numérico
                            $grandTotal += collect($items)->sum('total_contratado');
                        } else {
                            // Para Adquisiciones, asumiendo que tienes un campo numérico como 'total_general'
                            // ¡IMPORTANTE! No sumes el campo formateado ('total_general_formatted')
                            $grandTotal += collect($items)->sum('total_general_formatted');
                        }
                    }

                    $html = view('jp_limpieza.reportes.partials.table_adquisiciones', compact('reportData', 'grandTotal'))->render();
                } elseif ($tipo_reporte == 'global') {
                    $reportData = Adquisicion::dataReportePorArticulo($request);
                    $grandTotal = collect($reportData)->flatten(1)->sum('monto_total');

                    $html = view('jp_limpieza.reportes.partials.table_adquisiciones_global', compact('reportData', 'grandTotal'))->render();
                } elseif ($tipo_reporte == 'balance') {
                    $reportData = Proyecto::dataReporteBalance($request);
                    // 2. Calcular los totales generales para el pie de página del reporte
                    $totalIngresosGeneral = $reportData->sum('total_ingresos');
                    $totalGastosGeneral = $reportData->sum('total_gastos');
                    $utilidadGeneral = $reportData->sum('utilidad');

                    $html = view('jp_limpieza.reportes.partials.table_balance', compact('reportData', 'totalIngresosGeneral', 'totalGastosGeneral', 'utilidadGeneral'))->render();
                }

                return response()->json([
                    'success' => true,
                    'html' => $html,
                    'result' => $reportData,
                ]);
            } catch (\Throwable $th) {
                return response()->json([
                    'success' => false,
                    'mensaje' => 'Error al generar el reporte: ' . $th->getMessage(),
                    'error' => $th->getLine(),
                ]);
            }
        }
    }

    //** Reporte de caja (Flujo de efectivo) */
    public function caja()
    {
        $title_page = 'Reporte Caja';
        $breadcrumbs = [
            ['name' => 'Inicio', 'url' => route('home')],
            ['name' => 'Reportes', 'url' => route('jp.limpieza.reporte.index')],
            ['name' => 'Caja', 'url' => ''],
        ];

        $ultima_revision_caja = RevisionCaja::latest()->first();

        return view('jp_limpieza.reportes.caja', compact('title_page', 'breadcrumbs', 'ultima_revision_caja'));
    }
    /**
     * Visualizar reporte de caja
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function visualizarReporteCaja(Request $request)
    {
        if ($request->ajax()) {
            try {
                $query = Caja::dataReporteCaja($request);
                // return $result;
                return response()->json([
                    'success' => true,
                    'result' => $query,
                ]);
            } catch (\Throwable $th) {
                return response()->json([
                    'success' => false,
                    'mensaje' => 'Error al generar el reporte: ' . $th->getMessage(),
                    'error' => $th->getLine(),
                ]);
            }
        }
    }

    /**
     * Guardar revisión de caja
     */
    public function guardarRevisionCaja(Request $request)
    {
        try {
            $data = $request->validate([
                'fechas' => 'required',
                'fechas.*' => 'required|date',
            ]);

            list($fechaInicio, $fechaFin) = explode(' - ', $request->fechas);

            $data = [
                'fecha_revision_inicio' => $fechaInicio,
                'fecha_revision_fin' => $fechaFin,
                'usuario_id' => auth()->id(),
                'observaciones' => $request->observaciones,
            ];

            RevisionCaja::create($data);

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
