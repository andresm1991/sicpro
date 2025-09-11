<?php

namespace App\Http\Controllers\JPLimpieza;

use App\Http\Controllers\Controller;
use App\Models\JPLimpieza\Caja;
use Illuminate\Http\Request;

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

    //** Reporte de caja (Flujo de efectivo) */
    public function caja()
    {
        $title_page = 'Reporte Caja';
        $breadcrumbs = [
            ['name' => 'Inicio', 'url' => route('home')],
            ['name' => 'Reportes', 'url' => route('jp.limpieza.reporte.index')],
            ['name' => 'Caja', 'url' => ''],
        ];

        return view('jp_limpieza.reportes.caja', compact('title_page', 'breadcrumbs'));
    }

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
}
