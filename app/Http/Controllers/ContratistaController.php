<?php

namespace App\Http\Controllers;

use App\Constants\MessagesConstant;
use App\Models\Articulo;
use App\Models\CatalogoDato;
use App\Models\Contratista;
use App\Models\DetalleContratista;
use App\Models\PagoOrdenTrabajoContratista;
use App\Models\Proveedor;
use App\Models\Proyecto;
use App\Services\LogService;
use Carbon\Carbon;
use Exception;
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
            ['name' => 'Contratistas', 'url' => ''] // Último breadcrumb no tiene URL, es el actual
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
                        'valor_unitario' => str_replace(',','',$precio_unitario[$index]),
                    ]);
                }
                DB::commit();
                return redirect()->back()->with('success', 'Orden de trabajo contratista creada con éxito.');
            }
            throw new Exception(MessagesConstant::DEFAUL_ERROR);
        } catch (Throwable $e) {
            DB::rollBack();
            LogService::log('error', 'Error al crear orden de trabajo contratista', ['user_id' => auth()->id(), 'action' => 'create', 'message' => $e->getMessage()]);

            return redirect()->back()->with('error', MessagesConstant::CATCH_ERROR);
        }
    }

    public function pagosOrdenTrabajo (Request $request){
        $title_page = 'Pagos Contratista';
        $route_params = $this->getRouteParameters($request);

        $breadcrumbs = [
            ['name' => 'Inicio', 'url' => route('home')],
            ['name' => 'Contratistas', 'url' => route('proyecto.adquisiciones.contratista', ['tipo' => $request->route('tipo'), 'tipo_id' => $request->route('tipo_id'), 'proyecto' => $request->route('proyecto'), 'tipo_adquisicion' => $request->route('tipo_adquisicion'), 'tipo_etapa' => $request->tipo_etapa])],
            ['name' => 'Pagos Contratistas', 'url' => ''] // Último breadcrumb no tiene URL, es el actual
        ];

        $orden_trabajo_id = $request->contratista;
        $pagos_orden_trabajos = PagoOrdenTrabajoContratista::where('contratista_id', $orden_trabajo_id)->paginate(15);
        
        $route_params = array_merge($route_params, [
            'pagos_orden_trabajos' => $pagos_orden_trabajos, 
            'contratista' => $orden_trabajo_id, 
            'title_page' => $title_page, 
            'breadcrumbs' => $breadcrumbs
        ]);
        
        return view('contratista.pagos.index', $route_params);
    }

    public function nuevoPagoOrdenTrabajo (Request $request){
        $title_page = 'Nuevo Pago Contratista';
        $route_params = $this->getRouteParameters($request);
        $orden_trabajo = Contratista::find($request->contratista);
        
        if($this->validarPago($request->contratista)){
            return redirect()->back()->with(['sweetalert' => true, 'title' => 'Aviso', 'message' => 'No es posible realizar nuevo pago porque la orden de trabajo del contratista no tiene valores pendientes de pago.', 'icon' => 'warning']);
        }

        $breadcrumbs = [
            ['name' => 'Inicio', 'url' => route('home')],
            ['name' => 'Pagos Contratista', 'url' => route('proyecto.adquisiciones.contratista.pagos.orden.trabajo', ['tipo' => $request->route('tipo'), 'tipo_id' => $request->route('tipo_id'), 'proyecto' => $request->route('proyecto'), 'tipo_adquisicion' => $request->route('tipo_adquisicion'), 'tipo_etapa' => $request->tipo_etapa, 'contratista' => $request->contratista])],
            ['name' => 'Nuevo Pago Contratistas', 'url' => ''] // Último breadcrumb no tiene URL, es el actual
        ];

        $route_params = array_merge($route_params, [
            'orden_trabajo' => $orden_trabajo,
            'title_page' => $title_page,
            'breadcrumbs' => $breadcrumbs
        ]);
        return view('contratista.pagos.create', $route_params);
    }

    public function guardarPagoOrdenTrabajo (Request $request){
        try {
            $orden_trabajo = Contratista::find($request->orden_trabajo);
            $valor = str_replace(',', '', $request->valor);
            $pagos = $orden_trabajo->pagos_contratistas + $valor;
            $total_pagar = $orden_trabajo->total_contratistas;

            if($pagos > $total_pagar){
                return redirect()->back()->with('error', 'No es posible registrar el pago, por favor, verifique que el monto a pagar no supere el saldo pendiente de pago de la orden de trabajo.');
            }
      
            DB::beginTransaction();
            $pago = [
                'fecha'=> date('Y-m-d'), 
                'contratista_id' => $request->orden_trabajo,
                'tipo_pago' => $request->tipo, 
                'forma_pago' => $request->forma_pago, 
                'valor' => str_replace(',', '', $request->valor), 
                'detalle' => $request->detalle,
            ];

            if(PagoOrdenTrabajoContratista::create($pago)){
                DB::commit();
                LogService::log('success', 'Pago orden de trabajo contratista registrado', ['user_id' => auth()->id(), 'action' => 'create']);

                return redirect()->back()->with('success', 'Pago a la Orden de trabajo contratista registrado con éxito.');
            }else{
                throw new Exception(MessagesConstant::DEFAUL_ERROR);
            }
        } catch (Throwable $e) {
            DB::rollBack();
            LogService::log('error', 'Error al crear orden de trabajo contratista', ['user_id' => auth()->id(), 'action' => 'create', 'message' => $e->getMessage()]);

            return redirect()->back()->with('error', MessagesConstant::CATCH_ERROR);
        }
    }

    public function eliminarOrdenTrabajo ($id){
        $is_delete = Contratista::find($id)->delete();
        if ($is_delete) {
            return response()->json(['success' => true, 'message' => 'Registro eliminado correctamente']);
        } else {
            return response()->json(['success' => false, 'message' => 'No se pudo eliminar el registro']);
        }
    }

    private function validarPago ($orden_trabajo) {
        $orden_trabajo = Contratista::find($orden_trabajo);
        $pagos = $orden_trabajo->pagos_contratistas;
        $total_pagar = $orden_trabajo->total_contratistas;
        
        return $total_pagar == $pagos ? true:false;
    }

    public function buscarPagoOrdenTrabajo(Request $request){
        if($request->ajax()){
            $buscar = $request->text;
            $proyecto_id = $request->proyecto;
            $etapa_id = $request->tipo_adquisicion;
            $tipo_adquisicion_id =  $request->tipo_etapa;
            $output = "";

            $orden_trabajos = Contratista::with(['proveedor', 'articulo'])
            ->where('proyecto_id', $proyecto_id)
            ->where('etapa_id', $etapa_id)
            ->where('tipo_etapa_id', $tipo_adquisicion_id)
            ->where(function ($query) use ($buscar) {
                $query->where('fecha', 'LIKE', '%' . $buscar . '%')
                    ->orWhereHas('proveedor', function ($q) use ($buscar) {
                        $q->where('razon_social', 'LIKE', '%' . $buscar . '%');
                    })
                    ->orWhereHas('articulo', function ($q) use ($buscar) {
                        $q->where('descripcion', 'LIKE', '%' . $buscar . '%');
                    });
            })
            ->orderBy('fecha', 'asc')
            ->get();

            return $orden_trabajos;
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
