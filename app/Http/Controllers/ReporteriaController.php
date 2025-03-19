<?php

namespace App\Http\Controllers;

use App\Models\CatalogoDato;
use App\Models\Proyecto;
use Illuminate\Http\Request;

class ReporteriaController extends Controller
{
    public function index(Request $request)
    {
        $title_page = 'Reportes';
        $breadcrumbs = [
            ['name' => 'Inicio', 'url' => route('home')],
            ['name' => 'Reportes', 'url' => '']
        ];

        return view('reportes.index', compact('title_page', 'breadcrumbs'));
    }

    public function reporteAdquisiciones()
    {
        $title_page = 'Reportes Adquisiciones';
        $breadcrumbs = [
            ['name' => 'Inicio', 'url' => route('home')],
            ['name' => 'Reportes', 'url' => route('reporte.index')],
            ['name' => 'Adquisiciones', 'url' => ''],
        ];

        $proyectos = Proyecto::pluck('nombre_proyecto', 'id')->prepend('', '');
        $etapas = CatalogoDato::getChildrenCatalogo('menu.adquisiciones')->pluck('descripcion', 'id')->prepend('', '');
        $tipoAdquisiciones = CatalogoDato::getChildrenCatalogo('proveedor')->where('slug', '!=', 'profecionales')->pluck('descripcion', 'id')->prepend('', '');

        return view('reportes.adquisiciones', compact('title_page', 'breadcrumbs', 'proyectos', 'etapas', 'tipoAdquisiciones'));
    }
}