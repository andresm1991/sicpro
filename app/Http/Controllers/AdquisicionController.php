<?php

namespace App\Http\Controllers;

use PDF;
use Exception;
use Throwable;
use App\Models\Articulo;
use App\Models\Proyecto;
use App\Models\Proveedor;
use App\Models\Adquisicion;
use App\Models\CatalogoDato;
use App\Services\LogService;
use Illuminate\Http\Request;
use App\Models\OrdenRecepcion;
use Yajra\DataTables\DataTables;
use App\Models\AdquisicionDetalle;
use Illuminate\Support\Facades\DB;
use App\Constants\MessagesConstant;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\OrdenRecepcionStoreRequest;
use App\Http\Requests\OrdenRecepcionUpdateRequest;
use App\Models\DiccionarioPalabra;
use App\Models\Inventario;

class AdquisicionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function  index($tipo, $tipo_id, Proyecto $proyecto)
    {
        $title_page = $proyecto->nombre_proyecto . ' - Aquisiciones';
        $menu_adquisiciones = CatalogoDato::getChildrenCatalogo('menu.adquisiciones');

        $breadcrumbs = [
            ['name' => 'Inicio', 'url' => route('home')],
            ['name' => $proyecto->nombre_proyecto, 'url' => route('proyecto.view', ['tipo' => $tipo, 'tipo_id' => $tipo_id, 'proyecto' => $proyecto->id])],
            ['name' => 'Aquisiciones', 'url' => ''] // Último breadcrumb no tiene URL, es el actual
        ];

        return view('adquisiciones.index', compact('menu_adquisiciones', 'title_page', 'breadcrumbs', 'tipo', 'tipo_id', 'proyecto'));
    }

    public function tipoAquisicion($tipo, $tipo_id, Proyecto $proyecto, CatalogoDato $tipo_adquisicion)
    {
        $title_page = 'Aquisiciones - ' . $tipo_adquisicion->descripcion;
        $aquisiciones = CatalogoDato::getChildrenCatalogo('proveedor');

        $breadcrumbs = [
            ['name' => 'Inicio', 'url' => route('home')],
            ['name' => 'Adquisiciones', 'url' => route('proyecto.adquisiciones.menu', ['tipo' => $tipo, 'tipo_id' => $tipo_id, 'proyecto' => $proyecto->id])],
            ['name' => $tipo_adquisicion->descripcion, 'url' => ''] // Último breadcrumb no tiene URL, es el actual
        ];

        return view('adquisiciones.tipo_adquisicion', compact('aquisiciones', 'tipo_adquisicion', 'title_page', 'breadcrumbs', 'tipo', 'tipo_id', 'proyecto'));
    }

    public function listTipoAquisicion($tipo, $tipo_id, Proyecto $proyecto, CatalogoDato $tipo_adquisicion, CatalogoDato $tipo_etapa)
    {
        $title_page = 'Aquisiciones';
        $aquisiciones = CatalogoDato::getChildrenCatalogo('proveedor');

        $list_pedidos = Adquisicion::where('proyecto_id', $proyecto->id)
            ->where('etapa_id', $tipo_adquisicion->id)
            ->where('tipo_etapa_id', $tipo_etapa->id)
            ->orderBy('id', 'desc')->paginate(15);

        $breadcrumbs = [
            ['name' => 'Inicio', 'url' => route('home')],
            ['name' => $tipo_adquisicion->descripcion, 'url' => route('proyecto.adquisiciones.tipo', ['tipo' => $tipo, 'tipo_id' => $tipo_id, 'proyecto' => $proyecto->id, 'tipo_adquisicion' => $tipo_adquisicion])],
            ['name' => $tipo_etapa->descripcion, 'url' => ''] // Último breadcrumb no tiene URL, es el actual
        ];

        return view('adquisiciones.list_pedidos', compact('list_pedidos', 'aquisiciones', 'tipo_adquisicion', 'title_page', 'breadcrumbs', 'tipo', 'tipo_id', 'proyecto', 'tipo_etapa'));
    }

    /**
     * Crear nuevo pedido
     */
    public function create(Request $request)
    {
        $route_parametres = $this->getRouteParameters($request);
        $proyecto = $route_parametres['proyecto'];
        $title_page = $proyecto->nombre_proyecto;
        $tipo_adquisicion = $route_parametres['tipo_etapa']->slug != 'meteriales.herramientas' ? 19 : 18;
        $orden_pedido = new Adquisicion();

        $ultimo_registro = Adquisicion::latest()->first();
        $ultimo_id = $ultimo_registro ? $ultimo_registro->id + 1 : 1;
        $numero_orden = date('Ymd') . '-' . str_pad($ultimo_id, 3, '0', STR_PAD_LEFT);
        $productos = Articulo::where('activo', true)
            ->where('categoria_id', $tipo_adquisicion)
            ->orderBy('descripcion', 'asc')->pluck('descripcion', 'id');

        $breadcrumbs = [
            ['name' => 'Inicio', 'url' => route('home')],
            ['name' => $route_parametres['tipo_etapa']->descripcion, 'url' => route('proyecto.adquisiciones.tipo.etapa', $route_parametres)],
            ['name' => 'Nuevo', 'url' => ''] // Último breadcrumb no tiene URL, es el actual
        ];


        $route_parametres = array_merge($route_parametres, ['numero_orden' => $numero_orden, 'orden_pedido' => $orden_pedido, 'productos' => $productos, 'title_page' => $title_page, 'breadcrumbs' => $breadcrumbs]);
        return view('adquisiciones.create', $route_parametres);
    }

    /**
     * Guardar pedido
     */
    public function store(Request $request, $tipo, $tipo_id, Proyecto $proyecto, CatalogoDato $tipo_adquisicion, CatalogoDato $tipo_etapa)
    {
        $request->validate([
            'productos' => 'required|array',
            'productos.*' => 'required',
            'cantidad' => 'required|array',
            'cantidad.*' => 'required',
            'necesidad' => 'required|array',
            'necesidad.*' => 'required',
        ]);

        $fecha = $request->fecha;
        $numero_pedido = $request->numero_pedido;
        $proyecto_id = $request->proyecto_id;
        $adquisicion_id = $request->tipo_adquisicion;
        $etapa_id = $request->tipo_etapa;

        $productos = $request->productos;
        $cantidad = $request->cantidad;
        $necesidad = $request->necesidad;

        try {
            DB::beginTransaction();

            $adquisicion = Adquisicion::create([
                'fecha' => $fecha,
                'numero' => $numero_pedido,
                'proyecto_id' => $proyecto_id,
                'etapa_id' => $adquisicion_id,
                'tipo_etapa_id' => $etapa_id,
                'usuario_id' => Auth::user()->id,
                'estado' => 'En Proceso',
            ]);

            if ($adquisicion) {
                foreach ($productos as $index => $producto) {
                    $param_detalle_adquisicion = [
                        'adquisicion_id' => $adquisicion->id,
                        'articulo_id' => '',
                        'cantidad_solicitada' => str_replace(',', '', $cantidad[$index]),
                        'necesidad' => $necesidad[$index]
                    ];

                    if (is_numeric($producto)) {
                        $param_detalle_adquisicion['articulo_id'] = $producto;
                    } else {
                        $nuevo_producto = $this->registrarNuevoProducto($tipo_etapa, $producto);
                        $param_detalle_adquisicion['articulo_id'] = $nuevo_producto->id;
                    }

                    AdquisicionDetalle::create($param_detalle_adquisicion);

                    agregarPalabra($necesidad[$index]);
                }
                DB::commit();
                LogService::log('info', 'Adquisición creada', ['user_id' => auth()->id(), 'action' => 'create']);
                return redirect()->route('proyecto.adquisiciones.tipo.create', ['tipo' => $tipo, 'tipo_id' => $tipo_id, 'proyecto' => $proyecto->id, 'tipo_adquisicion' => $tipo_adquisicion, 'tipo_etapa' => $tipo_etapa])->with('success', 'Orden de pedido generada con éxito.');
            } else {
                DB::rollback();
                LogService::log('error', 'Error al crear Adquisición', ['user_id' => auth()->id(), 'action' => 'create', 'message' => 'ocurrio un error al intentar crear la adquisición']);
                return redirect()->route('proyecto.adquisiciones.tipo.create', ['tipo' => $tipo, 'tipo_id' => $tipo_id, 'proyecto' => $proyecto->id, 'tipo_adquisicion' => $tipo_adquisicion, 'tipo_etapa' => $tipo_etapa])->with('error', 'Ocurrió un error al generar el pedido, por favor vuela a intentarlo. Si el problema persiste, comuníquese con el administrador del sistema.');
            }
        } catch (Throwable $e) {
            DB::rollBack();
            LogService::log('error', 'Error al crear Adquisición', ['user_id' => auth()->id(), 'action' => 'create', 'message' => $e->getMessage()]);
            return redirect()->route('proyecto.adquisiciones.tipo.create', ['tipo' => $tipo, 'tipo_id' => $tipo_id, 'proyecto' => $proyecto->id, 'tipo_adquisicion' => $tipo_adquisicion, 'tipo_etapa' => $tipo_etapa])->with('error', 'Ocurrió un error inesperado, comuníquese con el administrador del sistema.');
        }
    }

    /**
     * Editar informacion del pedido
     */
    public function editarPedido(Request $request)
    {
        $route_params = $this->getRouteParameters($request);
        $pedido_id = $request->route('pedido');
        $pedido = Adquisicion::find($pedido_id);

        if ($pedido->estado != 'Finalizado') {
            $title_page = $route_params['proyecto']->nombre_proyecto;
            $aquisiciones = CatalogoDato::getChildrenCatalogo('proveedor');
            $orden_pedido = $pedido;
            $numero_orden = $pedido->numero;
            $productos = Articulo::where('activo', true)->orderBy('descripcion', 'asc')->pluck('descripcion', 'id');

            $breadcrumbs = [
                ['name' => 'Inicio', 'url' => route('home')],
                ['name' => $route_params['tipo_etapa']->descripcion, 'url' => route('proyecto.adquisiciones.tipo.etapa', $route_params)],
                ['name' => 'Editar', 'url' => ''] // Último breadcrumb no tiene URL, es el actual
            ];

            $route_params = array_merge($route_params, ['numero_orden' => $numero_orden, 'orden_pedido' => $orden_pedido, 'aquisiciones' => $aquisiciones, 'productos' => $productos, 'title_page' => $title_page, 'breadcrumbs' => $breadcrumbs]);
            return view('adquisiciones.edit',  $route_params);
        } else {
            return back()->with('toast_error', 'Esta orden de pedido ya esta finalizada por lo cual no puede ser editada.');
        }
    }
    /**
     * Actualizar la informacion del pedido
     */
    public function updateAdquisicion(Request $request)
    {
        $route_params = $this->getRouteParameters($request);
        try {
            DB::beginTransaction();
            $pedido_id = $request->route('pedido');
            // Combinar arrays en uno solo
            $result = array_map(function ($producto, $cantidad, $necesidad) {
                return [
                    'articulo_id' => $producto,
                    'cantidad_solicitada' => str_replace(',', '', $cantidad),
                    'necesidad' => $necesidad
                ];
            }, $request->productos, $request->cantidad, $request->necesidad);

            foreach ($result as $data) {
                AdquisicionDetalle::updateOrCreate(
                    [
                        'articulo_id' => $data['articulo_id'],
                        'adquisicion_id' => $pedido_id
                    ],
                    [
                        'cantidad_solicitada' => $data['cantidad_solicitada'],
                        'necesidad' => $data['necesidad']
                    ]
                );
            }
            // obtener producots existentes del pedido
            $productos_existente = AdquisicionDetalle::where('adquisicion_id', $pedido_id)->pluck('articulo_id')->toArray();
            // Encontrar los IDs que están en la base de datos pero no en el request            
            $productos_eliminar = array_diff($productos_existente, $request->productos);

            // Eliminar los registros que no están en el formulario
            if (!empty($productos_eliminar)) {
                AdquisicionDetalle::where('adquisicion_id', $pedido_id)
                    ->whereIn('articulo_id', $productos_eliminar)->delete();
            }

            DB::commit();
            LogService::log('info', 'Adquisición actualizada', ['user_id' => auth()->id(), 'action' => 'update']);
            $route_params = array_merge($route_params, ['pedido' => $pedido_id]);
            return redirect()->route('proyecto.adquisiciones.orden.pedido.edit', $route_params)->with('success', 'Orden de pedido actualizada con éxito.');
        } catch (Throwable $e) {
            DB::rollBack();
            LogService::log('error', 'Error al actualizar Adquisición', ['user_id' => auth()->id(), 'action' => 'update', 'message' => $e->getMessage()]);
            return back()->with('error', 'Ocurrió un error inesperado, comuníquese con el administrador del sistema.');
        }
    }

    /**
     * Eliminar pedido
     * @param request
     * return json
     */
    public function destroyPedido(Request $request)
    {
        $pedido = Adquisicion::find($request->route('pedido'));

        if ($pedido->estado != 'Finalizado') {
            if ($pedido->delete()) {
                LogService::log('info', 'Adquisición eliminada', ['user_id' => auth()->id(), 'action' => 'destroy']);
                return response()->json(['success' => true, 'message' => 'Registro eliminado correctamente.']);
            } else {
                return response()->json(['success' => false, 'message' => 'Ocurrió un error al intentar eliminar el registro, por favor vuelva a intentar si el problema persiste comuníquese con el administrador del sistema.']);
            }
        } else {
            return response()->json(['success' => false, 'message' => 'No es posible eliminar esta adquisición porque ha sido finalizada.']);
        }
    }

    /**
     * Buscar pedido
     * @param request
     * return json
     */
    public function buscarPedido(Request $request)
    {
        if ($request->ajax()) {

            $buscar = $request->buscar;
            $tipo_proyecto_id = $request->tipo_id;
            $proyecto_id = $request->proyecto;
            $etapa_id = $request->tipo_adquisicion;
            $tipo_adquisicion_id =  $request->tipo_etapa;
            $output = "";

            $list_pedidos = Adquisicion::with(['proyecto', 'etapa', 'tipo_etapa'])
                ->where('proyecto_id', $proyecto_id)
                ->where('etapa_id', $etapa_id)
                ->where('tipo_etapa_id', $tipo_adquisicion_id)
                ->where(function ($query) use ($buscar) {
                    $query->where('fecha', 'LIKE', '%' . $buscar . '%') // Búsqueda en campos de Adquisicion
                        ->orWhere('numero', 'LIKE', '%' . $buscar . '%')
                        ->orWhere('estado', 'LIKE', '%' . $buscar . '%')
                        ->orWhereHas('proyecto', function ($q) use ($buscar) {
                            $q->where('nombre_proyecto', 'LIKE', '%' . $buscar . '%'); // Búsqueda en el nombre del proyecto
                        })
                        ->orWhereHas('etapa', function ($q) use ($buscar) {
                            $q->where('descripcion', 'LIKE', '%' . $buscar . '%'); // Búsqueda en el nombre de la etapa
                        })
                        ->orWhereHas('tipo_etapa', function ($q) use ($buscar) {
                            $q->where('descripcion', 'LIKE', '%' . $buscar . '%'); // Búsqueda en el tipo de etapa
                        });
                })
                ->orderBy('fecha', 'asc')
                ->get();

            if ($list_pedidos) {
                $route_parametres = $this->getRouteParameters($request);
                foreach ($list_pedidos as $pedido) {
                    $route_parametres = array_merge($route_parametres, ['pedido' => $pedido->id]);
                    $editar_button = "<a href='" . route('proyecto.adquisiciones.orden.pedido.edit', $route_parametres) . "' class='dropdown-item'>Editar</a>";
                    $destroy_button = "<a href='#' class='dropdown-item eliminar-pedido' id='" . $pedido->id . "'>Eliminar</a>";
                    $pdf_orden_adquisicion_button = "<a href='" . route('pdf.adquisicion', $pedido->id) . "' class='dropdown-item'>PDF Orden Pedido</a>";
                    $pdf_orden_recepcion_button = "<a href='" . route('pdf.recepcion', $pedido->id) . "' class='dropdown-item'>PDF Orden Recepción</a>";
                    $orden_recepcion_button = "<a href='" . route('proyecto.adquisiciones.orden.recepcion', $route_parametres) . "' class='dropdown-item'>Orden de Recepción</a>";

                    $estado = $pedido->estado ? '<span class="badge badge-success">' . $pedido->estado . '</span>' : '<span class="badge badge-warning">' . $pedido->estado . '</span>';
                    $output .= '<tr id="' . $pedido->id . '">' .
                        '<td class="align-middle">' . $pedido->numero . '</td>' .
                        '<td class="align-middle">' . date('d-m-Y', strtotime($pedido->fecha)) . '</td>' .
                        '<td class="align-middle">' . $pedido->proyecto->nombre_proyecto . '</td>' .
                        '<td class="align-middle">' . $pedido->etapa->descripcion . '</td>' .
                        '<td class="align-middle">' . $pedido->tipo_etapa->descripcion . '</td>' .
                        '<td class="align-middle">' . $estado . '</td>' .
                        '<td class="align-middle align-middle text-right text-truncate">' .
                        '<button type="button" class="btn btn-outline-dark" data-container="body" data-toggle="popover" data-placement="left" data-trigger="focus" data-content ="' . $editar_button . $destroy_button . $pdf_orden_adquisicion_button . $pdf_orden_recepcion_button . $orden_recepcion_button . '"><i class="fas fa-caret-left font-weight-normal"></i> Opciones
                                            </button>' .
                        '</td>' .
                        '</tr>';
                }

                if (empty($output)) {
                    $output .= '<tr>' .
                        '<td colspan="7" class="text-center">' .
                        '<span class="text-danger">No existen datos para mostrar.</span>' .
                        '</td>' .
                        '</tr>';
                }
                return Response($output);
            }
        }
    }

    /** 
     * Orden de recepcion
     * @param request
     * return view
     */
    public function ordenRecepcion(Request $request)
    {
        $route_params = $this->getRouteParameters($request);

        $title_page = $route_params['proyecto']->nombre_proyecto . ' - Orden de Recepción';
        $proveedores = Proveedor::where('categoria_proveedor_id', $route_params['tipo_etapa']->id)->pluck('razon_social', 'id');
        $forma_pagos = CatalogoDato::getChildrenCatalogo('formas.pagos')->pluck('descripcion', 'id');
        $pedido = Adquisicion::find($request->route('pedido'));
        $orden = OrdenRecepcion::where('adquisicion_id', $pedido->id)->first();
        $unidad_medidas = CatalogoDato::getChildrenCatalogo('unidades.medida')->pluck('descripcion', 'id');
        $unidad_medidas = $unidad_medidas->prepend('', '');

        $breadcrumbs = [
            ['name' => 'Inicio', 'url' => route('home')],
            ['name' => $route_params['tipo_etapa']->descripcion, 'url' => route('proyecto.adquisiciones.tipo.etapa', $route_params)],
            ['name' => 'Orden de Recepción', 'url' => ''] // Último breadcrumb no tiene URL, es el actual
        ];

        $route_params = array_merge($route_params, [
            'pedido' => $pedido,
            'orden_recepcion' => $orden,
            'proveedores' => $proveedores,
            'forma_pagos' => $forma_pagos,
            'title_page' => $title_page,
            'breadcrumbs' => $breadcrumbs,
            'unidad_medidas' => $unidad_medidas,
        ]);

        return $orden ? view('adquisiciones.orden_recepcion.edit', $route_params) : view('adquisiciones.orden_recepcion.create', $route_params);
    }

    /**
     * Guardar orden de recepcion
     * @param Request
     * return view
     */
    public function storeOrdenRecepcion(OrdenRecepcionStoreRequest $request)
    {
        $routeParametres = $this->getRouteParameters($request);
        $routeParametres = array_merge($routeParametres, ['pedido' => $request->pedido]);
        $info_pedido = Adquisicion::find($request->pedido);
        $orden_completa = $request->has('orden_completa') ? true : false;
        $cantidades_recibidas = $request->cantidad_recibida;
        $cantidades_solicitadas = $info_pedido->adquisiciones_detalle->pluck('cantidad_solicitada')->toArray();
        $unidades_medidas = $request->unidad_medida;
        $precio = $request->valor;
        $inventario = $request->inventario;
        $estado_inventario =  CatalogoDato::getEstadoInventarioId('estados.inventario.nuevo');

        $param = [
            'fecha' => date('Y-m-d'),
            'adquisicion_id' => $request->pedido,
            'proveedor_id' => $request->proveedor,
            'forma_pago_id' => $request->forma_pago,
            'completado' => $orden_completa,
            'editar' => $orden_completa ? false : true,
        ];

        if ($orden_completa) {
            // Buscar el registro en la base de datos que contiene cantidad_solicitada

            foreach ($cantidades_recibidas as $index => $cantidad_recibida) {
                if ($cantidad_recibida > $cantidades_solicitadas[$index]) {
                    return back()->withErrors(['cantidad_recibida.' . $index => 'La cantidad recibida debe ser igual o menor a la cantidad solicitada.'])
                        ->withInput();
                }
            }
        }

        try {
            DB::beginTransaction();
            if ($orden_recepcion = OrdenRecepcion::create($param)) {
                /// Actualiza el estado del pedido
                if ($orden_completa) {
                    $info_pedido->estado = 'Finalizado';
                    $info_pedido->save();
                }
                // Actualiza la cantidad recibiba en el detalle del pedido
                foreach ($info_pedido->adquisiciones_detalle as $index => $detalle) {
                    $detalle->cantidad_recibida = str_replace(',', '', $cantidades_recibidas[$index]);
                    $detalle->valor = $precio[$index];

                    if (is_numeric($unidades_medidas[$index])) {
                        $detalle->unidad_medida_id = $unidades_medidas[$index];
                    } else {
                        $id = registrarUnidadMedida($unidades_medidas[$index]);
                        $detalle->unidad_medida_id = $id;
                    }

                    if ($inventario[$index] && $orden_completa) {
                        Inventario::create([
                            'orden_recepcion_id' => $orden_recepcion->id,
                            'producto_id' => $detalle->articulo_id,
                            'tipo' => 'entrada',
                            'cantidad' => str_replace(',', '', $cantidades_recibidas[$index]),
                            'fecha' => date('Y-m-d'),
                            'usuario_id' => Auth::user()->id,
                            'estado_id' => $estado_inventario,
                        ]);
                    }
                    if (!$detalle->save()) {
                        throw new Exception('Error al intentar guardar la orden de recepcion.');
                    }
                }
                DB::commit();
                LogService::log('info', 'Orden recepción creada', ['user_id' => auth()->id(), 'action' => 'create']);
                return redirect()->route('proyecto.adquisiciones.orden.recepcion', $routeParametres)->with('success', 'Orden de recepción generada con éxito.');
            } else {
                throw new Exception('Error al intentar guardar la orden de recepcion');
            }
        } catch (Throwable $e) {

            DB::rollBack();
            return $e->getMessage();
            LogService::log('error', 'Error al crear orden de recepción', ['user_id' => auth()->id(), 'action' => 'create', 'message' => $e->getMessage()]);
            return redirect()->route('proyecto.adquisiciones.orden.recepcion', $routeParametres)->with('error', 'Ocurrió un error inesperado, comuníquese con el administrador del sistema.');
        }
    }

    /**
     * Actualiza orden de recepcion
     * @param request
     * return view
     */
    public function updateOrdenRecepcion(OrdenRecepcionUpdateRequest $request)
    {
        $routeParametres = $this->getRouteParameters($request);
        $routeParametres = array_merge($routeParametres, ['pedido' => $request->pedido]);
        $info_pedido = Adquisicion::find($request->pedido);
        $orden_recepcion_id = $request->route('orden_recepcion');
        $orden_recepcion = OrdenRecepcion::find($orden_recepcion_id);
        $orden_completa = $request->has('orden_completa') ? true : false;
        $cantidades_recibidas = $request->cantidad_recibida;
        $cantidades_solicitadas = $info_pedido->adquisiciones_detalle->pluck('cantidad_solicitada')->toArray();
        $unidades_medidas = $request->unidad_medida;
        $unidade_medida_id = 0;
        $unidade_medida_text = '';
        $precio = $request->valor;
        $inventario = $request->inventario;
        $estado_inventario =  CatalogoDato::getEstadoInventarioId('estados.inventario.nuevo');

        if ($orden_completa) {
            foreach ($cantidades_recibidas as $index => $cantidad_recibida) {
                if ($cantidad_recibida > $cantidades_solicitadas[$index]) {
                    return back()->withErrors(['cantidad_recibida.' . $index => 'La cantidad recibida debe ser igual o menor a la cantidad solicitada.'])
                        ->withInput();
                }
            }
        }

        $orden_recepcion->proveedor_id = $request->proveedor;
        $orden_recepcion->forma_pago_id = $request->forma_pago;
        $orden_recepcion->completado = $orden_completa;
        $orden_recepcion->editar = $orden_completa ? false : true;

        try {
            DB::beginTransaction();
            if ($orden_recepcion->save()) {
                /// Actualiza el estado del pedido
                if ($orden_completa) {
                    $info_pedido->estado = 'Finalizado';
                    $info_pedido->save();
                }

                // Actualiza la cantidad recibiba en el detalle del pedido
                foreach ($info_pedido->adquisiciones_detalle as $index => $detalle) {
                    $detalle->cantidad_recibida = str_replace(',', '', $cantidades_recibidas[$index]);
                    $detalle->valor = $precio[$index];

                    if (is_numeric($unidades_medidas[$index])) {
                        $detalle->unidad_medida_id = $unidades_medidas[$index];
                    } elseif (!is_null($unidades_medidas[$index])) {
                        if ($unidade_medida_text != $unidades_medidas[$index]) {
                            $id = registrarUnidadMedida($unidades_medidas[$index]);
                            $unidade_medida_id = $id;
                            $unidade_medida_text = $unidades_medidas[$index];
                        }

                        $detalle->unidad_medida_id = $unidade_medida_id;
                    }

                    if ($inventario[$index] && $orden_completa) {
                        Inventario::create([
                            'orden_recepcion_id' => $orden_recepcion->id,
                            'producto_id' => $detalle->articulo_id,
                            'tipo' => 'entrada',
                            'cantidad' => str_replace(',', '', $cantidades_recibidas[$index]),
                            'fecha' => date('Y-m-d'),
                            'usuario_id' => Auth::user()->id,
                            'estado_id' => $estado_inventario,
                        ]);
                    }
                    if (!$detalle->save()) {
                        throw new Exception('Error al intentar guardar actualización de la orden de recepcion.');
                    }
                }

                DB::commit();
                LogService::log('info', 'Orden de recepción actualizada', ['user_id' => auth()->id(), 'action' => 'update']);
                return redirect()->route('proyecto.adquisiciones.orden.recepcion', $routeParametres)->with('success', 'Orden de recepción actualizada con éxito.');
            } else {
                throw new Exception('Error al intentar guardar la orden de recepcion');
            }
        } catch (Throwable $e) {
            return $e->getMessage();
            DB::rollBack();
            LogService::log('error', 'Error al actualizar orden de recepción', ['user_id' => auth()->id(), 'action' => 'update', 'message' => $e->getMessage()]);
            return redirect()->route('proyecto.adquisiciones.orden.recepcion', $routeParametres)->with('error', MessagesConstant::CATCH_ERROR);
        }
    }

    /**
     * Funcion que registra un nuevo producto
     * @param tipo
     * @param descripcion
     * @return collection
     */
    private function registrarNuevoProducto($tipo, $descripcion)
    {
        $tipo_producto = $tipo->slug == 'meteriales.herramientas' ? 'tipo.adquisiciones.bienes' : 'tipo.adquisiciones.servicios';
        $categoria = CatalogoDato::where('slug', $tipo_producto)->first();
        $type = $tipo->slug == 'meteriales.herramientas' ? 'B-' : 'S-';
        $code = generateProductCode($type);
        $create =  Articulo::create(['categoria_id' => $categoria->id, 'codigo' => $code, 'descripcion' => $descripcion, 'activo' => true]);
        LogService::log('info', 'Articulo creado', ['user_id' => auth()->id(), 'action' => 'create']);

        return $create;
    }

    /**
     * Método para obtener los parámetros comunes de la ruta
     * @param request
     * @return array
     */
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
