<?php

namespace App\Http\Controllers;

use App\Models\Articulo;
use App\Models\CatalogoDato;
use App\Models\Contratista;
use App\Models\DetalleContratista;
use App\Models\Proveedor;
use App\Models\Proyecto;
use App\Services\LogService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Throwable;

class ContratistaController extends Controller
{
    public function index (Request $request){
        $title_page = 'Contratista';
        $route_params = $this->getRouteParameters($request);

        $breadcrumbs = [
            ['name' => 'Inicio', 'url' => route('home')],
            ['name' => $route_params['proyecto']->nombre_proyecto, 'url' => route('proyecto.adquisiciones.tipo', ['tipo' => $request->route('tipo'), 'tipo_id' => $request->route('tipo_id'), 'proyecto' => $request->route('proyecto'), 'tipo_adquisicion' => $request->route('tipo_adquisicion')])],
            ['name' => 'Contraristas', 'url' => ''] // Último breadcrumb no tiene URL, es el actual
        ];

        $orden_trabajos = Contratista::where('proyecto_id', $route_params['proyecto']->id)
            ->paginate(15);

        $route_params = array_merge($route_params, ['orden_trabajos' => $orden_trabajos, 'breadcrumbs' => $breadcrumbs, 'title_page' => $title_page]);
        return view('contratista.index', $route_params);
    }

    public function crearOrdenTrabajo (Request $request){
        $title_page = 'Nueva orden de trabajo';
        $route_params = $this->getRouteParameters($request);
        $fecha = Carbon::now()->format('Y-m-d');
        
        $ultimo_registro = Contratista::latest()->first();
        $numero_orden = numeroOrden($ultimo_registro);
        $unidades_medidas = CatalogoDato::getChildrenCatalogo('unidades.medida');
        $articulos = Articulo::where('activo', true)->pluck('descripcion', 'id');

        $breadcrumbs = [
            ['name' => 'Inicio', 'url' => route('home')],
            ['name' => 'Contratistas', 'url' => route('proyecto.adquisiciones.contratista', ['tipo' => $request->route('tipo'), 'tipo_id' => $request->route('tipo_id'), 'proyecto' => $request->route('proyecto'), 'tipo_adquisicion' => $request->route('tipo_adquisicion'), 'tipo_etapa' => $request->route('tipo_etapa')])],
            ['name' => 'Nueva Orden de Trabajo', 'url' => '']
        ];

        $orden_trabajo = new Contratista();

        $proveedores = Proveedor::where('categoria_proveedor_id', $route_params['tipo_etapa']->id)->pluck('razon_social', 'id');

        $route_params = array_merge($route_params, 
        [
            'numero_orden' => $numero_orden,
            'fecha' => $fecha, 
            'orden_trabajo' => $orden_trabajo,  
            'proveedores' => $proveedores, 
            'breadcrumbs' => $breadcrumbs, 
            'title_page' => $title_page, 
            'unidades_medidas' => $unidades_medidas,
            'articulos' => $articulos,
        ]);

        return view('contratista.create', $route_params);
    }

    public function storeOrdenTrabajo(Request $request) {
        $proyecto = $request-> proyecto_id;
        $tipo_adquisicion = $request->tipo_adquisicion;
        $tipo_etapa = $request->tipo_etapa;
        $fecha = $request->fecha;
        $proveedor = $request->proveedor;
        $categoria = $request->categoria;
        $productos = $request->productos;
        $cantidad = $request->cantidad;
        $unidad_medida = $request->unidad_medida;
        $precio_unitario = $request->precio_unitario;

        try {
            DB::beginTransaction();
            $orden_trabajo_param = [
                'fecha' => $fecha, 
                'proveedor_id' => $proveedor, 
                'articulo_id' => $categoria, 
                'proyecto_id' => $proyecto, 
                'etapa_id' => $tipo_adquisicion, 
                'tipo_etapa_id' => $tipo_etapa, 
                'usuario_id' => Auth::user()->id, 
                'estado_id' => 38,
            ];

            if($orden_trabajo = Contratista::create($orden_trabajo_param)){
                foreach ($productos as $index => $producto) {
                    DetalleContratista::create([
                        'contratista_id' => $orden_trabajo->id, 
                        'articulo_id' => $producto, 
                        'cantidad' => $cantidad[$index], 
                        'unidad_medida_id' => $unidad_medida[$index], 
                        'valor_unitario' => $precio_unitario[$index],
                    ]);
                }
            }
        } catch (Throwable $e) {
            DB::rollBack();
            LogService::log('error', 'Error al crear orden de trabajo contratista', ['user_id' => auth()->id(), 'action' => 'create', 'message' => $e->getMessage()]);

            return redirect()->back()->with('error', 'Ocurrió un error inesperado.');
        }
    }

    private function getRouteParameters($request)
    {

        $tipo_etapa = is_numeric($request->route('tipo_etapa')) ? $request->route('tipo_etapa') : $request->tipo_etapa;

        $parametros = [
            'tipo' => $request->route('tipo'),
            'tipo_id' => $request->route('tipo_id'),
            'proyecto' => Proyecto::find($request->route('proyecto')),
            'tipo_adquisicion' => CatalogoDato::find($request->route('tipo_adquisicion')),
            'tipo_etapa' => CatalogoDato::find($tipo_etapa),
        ];

        return $parametros;
    }
}
