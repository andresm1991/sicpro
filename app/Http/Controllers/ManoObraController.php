<?php

namespace App\Http\Controllers;

use Throwable;
use App\Models\ManoObra;
use App\Models\Proyecto;
use App\Models\Proveedor;
use App\Models\CatalogoDato;
use App\Services\LogService;
use Illuminate\Http\Request;
use App\Models\ProveedorArticulo;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

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

        $list_mano_obra = ManoObra::where('proyecto_id', $route_params['proyecto']->id)
            ->orderBy('id', 'desc')
            ->paginate(15);

        $route_params = array_merge($route_params, ['list_mano_obra' => $list_mano_obra, 'breadcrumbs' => $breadcrumbs, 'title_page' => $title_page]);
        return view('mano_obra.index', $route_params);
    }

    public function proveedorArticulos(Request $request)
    {
        if ($request->ajax()) {
            $proveedor_articulos = ProveedorArticulo::where('proveedor_id', $request->proveedor)->get();
            foreach ($proveedor_articulos as $element) {
                $proveedor[] = ['id' => $element->articulo_id, 'nombre' => $element->articulo->descripcion];
            }

            if (isset($proveedor)) {
                return response()->json(['success' => true, 'articulos' => $proveedor]);
            } else {
                return response()->json(['success' => false, 'articulos' => ['id' => '', 'nombre' => 'No existen categorias asociadas a la persona selecionada.']]);
            }
        }
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

        $proveedores = Proveedor::where('categoria_proveedor_id', $route_params['tipo_etapa']->id)->pluck('razon_social', 'id');


        $route_params = array_merge($route_params, ['mano_obra' => $mano_obra, 'proveedores' => $proveedores, 'breadcrumbs' => $breadcrumbs, 'title_page' => $title_page]);
        return view('mano_obra.create', $route_params);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $personal = $request->personal;
        $categoria = $request->categoria;
        $jornada = $request->jornada;
        $valor = $request->valor;
        $adicional = $request->adicional;
        $descuento = $request->descuento;
        $detalle_adicional = $request->detalle_adicional;
        $detalle_descuento = $request->detalle_descuento;
        $observaciones = $request->observaciones;

        $data = [];

        try {
            DB::beginTransaction();
            foreach ($personal as $index => $value) {
                $data[] = [
                    'fecha' => date('Y-m-d'),
                    'proyecto_id' => $request->proyecto_id,
                    'proveedor_id' => $value,
                    'articulo_id' => $categoria[$index],
                    'etapa_id' => $request->tipo_adquisicion,
                    'tipo_etapa_id' => $request->tipo_etapa,
                    'usuario_id' => Auth::user()->id,
                    'jornada' => $jornada[$index],
                    'valor' => $valor[$index],
                    'adicional' => $adicional[$index],
                    'descuento' => $descuento[$index],
                    'detalle_adicional' => $detalle_adicional[$index],
                    'detalle_descuento' => $detalle_descuento[$index],
                    'observacion' => $observaciones[$index],
                    'created_at' => now(),
                    'updated_at' => now()
                ];
            }

            if (ManoObra::insert($data)) {
                DB::commit();
                LogService::log('info', 'Se creo planificaciòn mano de obra', ['user_id' => auth()->id(), 'action' => 'create']);
                return redirect()->back()->with('success', 'Se creo planificación de mano de obra');
            }

            DB::rollBack();
            LogService::log('error', 'Error al crear planificaciòn mano de obra', ['user_id' => auth()->id(), 'action' => 'create', 'message' => 'no se creo planificacion']);
            return redirect()->back()->with('error', 'Ocurrió un error inesperado.');
        } catch (Throwable $e) {
            DB::rollBack();
            return $e->getMessage();

            LogService::log('error', 'Error al crear planificaciòn mano de obra', ['user_id' => auth()->id(), 'action' => 'create', 'message' => $e->getMessage()]);
            return redirect()->back()->with('error', 'Ocurrió un error inesperado, comuníquese con el administrador del sistema.');
        }
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
