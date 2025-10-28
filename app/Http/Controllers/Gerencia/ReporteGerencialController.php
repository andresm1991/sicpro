<?php

namespace App\Http\Controllers\Gerencia;

use App\Http\Controllers\Controller;
use App\Models\Proyecto;
use Illuminate\Http\Request;

class ReporteGerencialController extends Controller
{
    public function reporteBalance()
    {
        $title_page = 'Reporte de Balance Gerencial';
        $breadcrumbs = [
            ['name' => 'Inicio', 'url' => route('home')],
            ['name' => 'Gerencia', 'url' => route('gerencia.index')],
            ['name' => 'Reporte de Balance Gerencial', 'url' => '']
        ];
        return view('gerencia.reportes.balance', compact('title_page', 'breadcrumbs'));
    }

    public function generarReporteBalance(Request $request)
    {
        $tipoReporte = $request->tipo_reporte;
        $fechas = $request->fechas;
        $anio = $request->anio;
        $proyectoId = $request->proyecto;
        $etapa = $request->etapa;
        $tipoEtapa = $request->tipo_etapa;
        $query = '';

        switch ($tipoReporte) {
            case 'balance_global':
                $query = Proyecto::dataReporteBalance($request);
                break;
            case 'balance_proyecto':
                break;

            default:
                # code...
                break;
        }

        return $query;
    }
}
