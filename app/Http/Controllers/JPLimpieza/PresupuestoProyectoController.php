<?php

namespace App\Http\Controllers\JPLimpieza;

use App\Http\Controllers\Controller;
use App\Models\JPLimpieza\Proyecto;
use Illuminate\Http\Request;

class PresupuestoProyectoController extends Controller
{
    public function index(Request $request)
    {
        $breadcrumbs = [
            ['name' => 'Inicio', 'url' => route('home')],
            ['name' => 'Limpieza y mantenimiento', 'url' => route('jp.limpieza.index')],
            ['name' => 'Proyectos', 'url' => route('jp.limpieza.proyectos.index')],
            ['name' => 'Presupuesto', 'url' => ''],
        ];

        $proyecto = Proyecto::findOrFail($request->proyecto);
        $proyecto->load('presupuesto');

        return view('jp_limpieza.presupuesto.index', compact('breadcrumbs', 'proyecto'));
    }
}