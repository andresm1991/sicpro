<?php

namespace App\Http\Controllers\JPLimpieza;

use Illuminate\Http\Request;
use App\Models\JPLimpieza\Caja;
use App\Http\Controllers\Controller;
use App\Models\JPLimpieza\Adquisicion;
use App\Models\JPLimpieza\Producto;
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
                $reportData = Adquisicion::dataReporteAdquisiciones($request);

                // Calcula el gran total
                $grandTotal = 0;
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

    public function filtroReporteAdquisiciones(Request $request)
    {
        if ($request->ajax()) {
            try {
                $tipo = $request->input('tipo');
                $proveedores = [];

                switch ($tipo) {
                    case 'mano_de_obra':
                        $cargos = Producto::where('activo', true)->whereHas('categoria_articulo', function ($query) {
                            $query->where('slug', 'tipo.adquisiciones.servicios');
                        })->orderBy('descripcion', 'asc')->get(['id', 'descripcion']);
                        $result['cargos'] = $cargos;
                        break;
                    case 'contratistas':
                        // Si necesitas cargar datos específicos para contratistas, hazlo aquí
                        break;
                    default:
                        // Manejo para tipos no reconocidos
                        break;
                }

                return response()->json([
                    'success' => true,
                    'proveedores' => $proveedores,
                    'productos' => $productos,
                    'cargos' => $cargos,
                ]);
            } catch (\Throwable $th) {
                return response()->json([
                    'success' => false,
                    'mensaje' => 'Error al filtrar los datos: ' . $th->getMessage(),
                    'error' => $th->getLine(),
                ]);
            }
        }
    }
}
