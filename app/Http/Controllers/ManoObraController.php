<?php

namespace App\Http\Controllers;

use App\Models\Proyecto;
use App\Models\CatalogoDato;
use App\Models\ManoObra;
use App\Models\Proveedor;
use Illuminate\Http\Request;

class ManoObraController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $title_page = 'Mano de obra';
        $route_params = $this->getRouteParameters($request);

        $breadcrumbs = [
            ['name' => 'Inicio', 'url' => route('home')],
            ['name' => $route_params['proyecto']->nombre_proyecto, 'url' => route('proyecto.adquisiciones.tipo', ['tipo' => $request->route('tipo'), 'tipo_id' => $request->route('tipo_id'), 'proyecto' => $request->route('proyecto'), 'tipo_adquisicion' => $request->route('tipo_adquisicion')])],
            ['name' => 'Mano de obra', 'url' => ''] // Último breadcrumb no tiene URL, es el actual
        ];

        $list_mano_obra = ManoObra::where('proveedor_id', $route_params['proyecto']->id)
        ->orderBy('id', 'desc')
        ->paginate(15);

        $route_params = array_merge($route_params, ['list_mano_obra' => $list_mano_obra,'breadcrumbs' => $breadcrumbs, 'title_page' => $title_page]);
        return view('mano_obra.index', $route_params);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $title_page = 'Mano de obra - Nuevo';
        $route_params = $this->getRouteParameters($request);

        $breadcrumbs = [
            ['name' => 'Inicio', 'url' => route('home')],
            ['name' => 'Mano de Obra', 'url' => route('proyecto.adquisiciones.mano.obra', ['tipo' => $request->route('tipo'), 'tipo_id' => $request->route('tipo_id'), 'proyecto' => $request->route('proyecto'), 'tipo_adquisicion' => $request->route('tipo_adquisicion'), 'tipo_etapa' => $request->route('tipo_etapa')])],
            ['name' => 'Nueva Planificación', 'url' => '']
        ];

        $mano_obra = new ManoObra();

        $proveedores = Proveedor::where('categoria_proveedor_id',$route_params['tipo_etapa']->id)->pluck('razon_social', 'id');
        

        $route_params = array_merge($route_params, ['mano_obra' => $mano_obra, 'proveedores' => $proveedores, 'breadcrumbs' => $breadcrumbs, 'title_page' => $title_page]);
        return view('mano_obra.create', $route_params);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    private function getRouteParameters($request)
    {
        $parametros = [
            'tipo' => $request->route('tipo'),
            'tipo_id' => $request->route('tipo_id'),
            'proyecto' => Proyecto::find($request->route('proyecto')),
            'tipo_adquisicion' => CatalogoDato::find($request->route('tipo_adquisicion')),
            'tipo_etapa' => CatalogoDato::find($request->route('tipo_etapa')),
        ];

        return $parametros;
    }
}
