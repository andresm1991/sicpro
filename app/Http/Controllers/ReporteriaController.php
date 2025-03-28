<?php

namespace App\Http\Controllers;

use App\Models\Articulo;
use App\Models\CatalogoDato;
use App\Models\DiccionarioPalabra;
use App\Models\Proveedor;
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
        $necesidades = DiccionarioPalabra::pluck('palabra', 'palabra')->prepend('', '');

        return view('reportes.adquisiciones', compact('title_page', 'breadcrumbs', 'proyectos', 'etapas', 'tipoAdquisiciones', 'necesidades'));
    }

    public function filtroReportesAdquisiciones(Request $request)
    {
        if ($request->ajax()) {
            $tipo = $request->input('tipo');
            $result = ['proveedores' => null, 'productos' => null, 'cargos' => null];

            $tipo_proveedor = $tipo == 'materiales_y_herramientas' ? 'meteriales.herramientas' : ($tipo == 'servicios' ? 'servicios' : ($tipo == 'contratistas' ? 'contratista' : ($tipo == 'mano_de_obra' ? 'mano.obra' : '')));

            $proveedores = Proveedor::whereHas('categoria_proveedor', function ($query) use ($tipo_proveedor) {
                $query->where('slug', $tipo_proveedor);
            })->orderBy('razon_social', 'asc')
                ->get(['id', 'razon_social']);

            if ($tipo == 'materiales_y_herramientas' || $tipo == 'servicios' || $tipo == 'contratistas') {
                $result['proveedores'] = $proveedores;
                if ($tipo != 'contratistas') {
                    $productos = Articulo::where('activo', true)->whereHas('categoria_articulo', function ($query) use ($tipo) {
                        $query->where('slug', $tipo == 'materiales_y_herramientas' ? 'tipo.adquisiciones.bienes' : 'tipo.adquisiciones.servicios');
                    })->orderby('descripcion', 'asc')->get(['id', 'descripcion']);
                    $result['productos'] = $productos;
                }
            } elseif ($tipo == 'mano_de_obra') {
                $cargos = Articulo::where('activo', true)->whereHas('categoria_articulo', function ($query) {
                    $query->where('slug', 'tipo.adquisiciones.servicios');
                })->orderBy('descripcion', 'asc')->get(['id', 'descripcion']);
                $result['cargos'] = $cargos;
            }

            return response()->json([
                'success' => true,
                'result' => $result,
            ]);
        }
    }
}