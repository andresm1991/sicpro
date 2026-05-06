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

        $query = Proyecto::dataReporteBalance($request);
        $totalIngresosGeneral = $query->sum('total_ingresos');
        $totalGatosDirectosGeneral = $query->sum('gastos_operativos');
        $totalGastosIndirectosGeneral = $query->sum('gastos_administrativos');
        $totalGastosGeneral = $query->sum('total_gastos');
        $utilidadGeneral = $query->sum('utilidad');

        // return $query;

        $vista = view('gerencia.reportes.tabla_balance', compact('query', 'totalIngresosGeneral', 'totalGastosGeneral', 'totalGatosDirectosGeneral', 'totalGastosIndirectosGeneral', 'utilidadGeneral'))->render();

        return $vista;
    }
}