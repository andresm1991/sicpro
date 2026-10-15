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
        $query = Proyecto::dataReporteBalance($request);
        $totales = Proyecto::totalesBalanceReporte($query);

        $totalIngresosGeneral = $totales['total_ingresos'];
        $totalGatosDirectosGeneral = $totales['gastos_operativos'];
        $totalGastosIndirectosGeneral = $totales['gastos_administrativos'];
        $totalGastosGeneral = $totales['total_gastos'];
        $utilidadGeneral = $totales['utilidad'];

        // return $query;

        $vista = view('gerencia.reportes.tabla_balance', compact('query', 'totalIngresosGeneral', 'totalGastosGeneral', 'totalGatosDirectosGeneral', 'totalGastosIndirectosGeneral', 'utilidadGeneral'))->render();

        return $vista;
    }
}
