<?php

namespace App\Http\Controllers;

use PDF;
use Exception;
use Throwable;
use App\Models\User;
use App\Models\Articulo;
use App\Models\Proyecto;
use App\Models\Proveedor;
use App\Models\Inventario;
use App\Models\Adquisicion;
use App\Models\CatalogoDato;
use App\Services\LogService;
use Illuminate\Http\Request;
use App\Models\MovimientoCaja;
use App\Models\OrdenRecepcion;
use Yajra\DataTables\DataTables;
use App\Models\AdquisicionDetalle;
use App\Models\DiccionarioPalabra;
use Illuminate\Support\Facades\DB;
use App\Constants\MessagesConstant;
use Illuminate\Support\Facades\Log;
use App\Enums\PushNotificationsEnum;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Services\PushNotificationService;
use App\Http\Requests\AdquisicionStoreRequest;
use App\Http\Requests\OrdenRecepcionStoreRequest;
use App\Http\Requests\OrdenRecepcionUpdateRequest;
use App\Http\Requests\AdquisicionAdministrativoRequest;

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
            ->where('tipo_adquisicion', 'operativo')
            ->orderBy('fecha', 'desc')->paginate(15);

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

        $proveedores = Proveedor::where('categoria_proveedor_id', $route_parametres['tipo_etapa']->id)->pluck('razon_social', 'id');
        $forma_pagos = CatalogoDato::getChildrenCatalogo('formas.pagos')->pluck('descripcion', 'id');
        $unidad_medidas = CatalogoDato::getChildrenCatalogo('unidades.medida')->pluck('descripcion', 'id');
        $unidad_medidas = $unidad_medidas->prepend('', '');

        $proyecto = Proyecto::findOrFail($proyecto->id);
        $subproyectos = $proyecto->subproyectos_unicos;




        $breadcrumbs = [
            ['name' => 'Inicio', 'url' => route('home')],
            ['name' => $route_parametres['tipo_etapa']->descripcion, 'url' => route('proyecto.adquisiciones.tipo.etapa', $route_parametres)],
            ['name' => 'Nuevo', 'url' => ''] // Último breadcrumb no tiene URL, es el actual
        ];


        $route_parametres = array_merge($route_parametres, ['numero_orden' => $numero_orden, 'orden_pedido' => $orden_pedido, 'productos' => $productos, 'title_page' => $title_page, 'breadcrumbs' => $breadcrumbs, 'proveedores' => $proveedores, 'forma_pagos' => $forma_pagos, 'unidad_medidas' => $unidad_medidas, 'subproyectos' => $subproyectos]);
        return view('adquisiciones.create', $route_parametres);
    }

    /**
     * Guardar pedido
     */
    public function store(AdquisicionStoreRequest $request, $tipo, $tipo_id, Proyecto $proyecto, CatalogoDato $tipo_adquisicion, CatalogoDato $tipo_etapa)
    {
        // Limpia el símbolo de dólar de cada elemento en el arreglo 'valor'
        $valoresLimpios = array_map(function ($value) {
            return preg_replace('/[^0-9.]/', '', $value); // Elimina $ y otros caracteres no numéricos
        }, $request->input('precio', []));

        // Reemplaza los valores en el request con los valores limpios
        $request->merge(['precio' => $valoresLimpios]);

        $fecha = $request->fecha;
        $numero_pedido = $request->numero_pedido;
        $proyecto_id = $request->proyecto_id;
        $adquisicion_id = $request->tipo_adquisicion;
        $etapa_id = $request->tipo_etapa;

        $productos = $request->productos;
        $cantidad = $request->cantidad;
        $necesidad = $request->necesidad;

        $km = $request->km;
        $unidad_medida = $request->unidad_meddia;
        $precio = $request->precio;

        $orden_completa = isset($request->orden_completa) ? true : false;
        $inventario = $request->inventario;
        $subproyecto = $request->subproyecto;

        try {
            DB::beginTransaction();

            $adquisicion = Adquisicion::create([
                'fecha' => $fecha,
                'numero' => $numero_pedido,
                'proyecto_id' => $proyecto_id,
                'etapa_id' => $adquisicion_id,
                'tipo_etapa_id' => $etapa_id,
                'usuario_id' => Auth::user()->id,
                'estado' => $orden_completa ? 'Finalizado' : 'En Proceso',
                'subproyecto' => $subproyecto,
            ]);

            if ($adquisicion) {
                foreach ($productos as $index => $producto) {
                    // Validar si el producto es 504 y si $km[$index] tiene un valor
                    if ($producto == 504 && (is_null($km[$index]) || $km[$index] === '')) {
                        return redirect()->back()->with('error', "El kilometraje es obligatorio.");
                    }
                    $param_detalle_adquisicion = [
                        'adquisicion_id' => $adquisicion->id,
                        'articulo_id' => '',
                        'cantidad_solicitada' => str_replace(',', '', $cantidad[$index]),
                        'necesidad' => $necesidad[$index],
                        'kilometraje' => $km[$index],
                        'unidad_medida_id' => isset($unidad_medida) ? $unidad_medida[$index] : null,
                        'valor' => !empty($precio) ? $precio[$index] : null,
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

                /// Guardar en tabla orden_recepcion

                $recepcion = [
                    'fecha' => $fecha,
                    'adquisicion_id' => $adquisicion->id,
                    'proveedor_id' => $request->proveedor,
                    'forma_pago_id' => $request->forma_pago,
                    'completado' => $orden_completa,
                    'editar' => $orden_completa ? false : true,
                ];

                if ($orden_recepcion = OrdenRecepcion::create($recepcion)) {
                    /// recorrer el detalle de la adquisicon para ver si hay que agregar al inventario
                    if (isset($inventario)) {
                        foreach ($adquisicion->adquisiciones_detalle as $index => $detalle) {
                            if ($inventario[$index] && $orden_completa) {
                                Inventario::create([
                                    'orden_recepcion_id' => $orden_recepcion->id,
                                    'producto_id' => $detalle->articulo_id,
                                    'cantidad' => str_replace(',', '', $cantidad[$index]),
                                    'fecha' => date('Y-m-d'),
                                    'usuario_id' => Auth::user()->id,
                                    'estado' => 10,
                                ]);
                            }
                        }
                    }
                } else {
                    throw new Exception('Error al intentar guardar la adquisición.');
                }

                if ($request->hasFile('archivo')) {
                    $path_archivo = $this->subriArchivo($request->file('archivo'), $adquisicion->numero);
                    $adquisicion->archivo = $path_archivo;
                    $adquisicion->save();
                }

                DB::commit();

                PushNotificationService::sendNotification(PushNotificationsEnum::OPERATIVO, 'Adquisicion Operativa NRO. ' . $adquisicion->numero, 'El usuario ' . Auth::user()->nombre . ' registro una nueva adquisision', route('pdf.recepcion', $adquisicion->id));

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

        $proveedores = Proveedor::where('categoria_proveedor_id', $route_params['tipo_etapa']->id)->pluck('razon_social', 'id');
        $forma_pagos = CatalogoDato::getChildrenCatalogo('formas.pagos')->pluck('descripcion', 'id');
        $unidad_medidas = CatalogoDato::getChildrenCatalogo('unidades.medida')->pluck('descripcion', 'id');
        $unidad_medidas = $unidad_medidas->prepend('', '');

        $subproyectos = Adquisicion::where('proyecto_id', $request->proyecto)->where('subproyecto', '!=', null)->pluck('subproyecto', 'subproyecto');
        $subproyectos = $subproyectos->prepend('', '');

        $route_params = array_merge($route_params, ['numero_orden' => $numero_orden, 'orden_pedido' => $orden_pedido, 'aquisiciones' => $aquisiciones, 'productos' => $productos, 'title_page' => $title_page, 'breadcrumbs' => $breadcrumbs, 'proveedores' => $proveedores, 'forma_pagos' => $forma_pagos, 'unidad_medidas' => $unidad_medidas, 'subproyectos' => $subproyectos]);

        return view('adquisiciones.edit',  $route_params);
    }
    /**
     * Actualizar la informacion del pedido
     */
    public function updateAdquisicion(Request $request)
    {

        $tipo_etapa = CatalogoDato::find($request->tipo_etapa);
        $route_params = $this->getRouteParameters($request);
        $orden_completa = isset($request->orden_completa) ? true : false;

        // Limpia el símbolo de dólar de cada elemento en el arreglo 'valor'
        $valoresLimpios = array_map(function ($value) {
            return preg_replace('/[^0-9.]/', '', $value); // Elimina $ y otros caracteres no numéricos
        }, $request->input('precio', []));

        // Reemplaza los valores en el request con los valores limpios
        $request->merge(['precio' => $valoresLimpios]);

        try {
            DB::beginTransaction();
            $pedido_id = $request->route('pedido');
            $pedido = Adquisicion::find($pedido_id);
            $unidad_medida = isset($request->unidad_medida) ? $request->unidad_medida : [];
            $precio = $request->precio;
            $inventario = $request->inventario;

            // obtener producots existentes del pedido
            $productos_existente = AdquisicionDetalle::where('adquisicion_id', $pedido_id)->pluck('articulo_id')->toArray();
            // Encontrar los IDs que están en la base de datos pero no en el request
            $productos_eliminar = array_diff($productos_existente, $request->productos);

            // Combinar arrays en uno solo
            $result = array_map(function ($producto, $cantidad, $necesidad, $km, $unidad, $precio) use ($tipo_etapa) {
                return [
                    'articulo_id' => $producto,
                    'cantidad_solicitada' => str_replace(',', '', $cantidad),
                    'necesidad' => $necesidad,
                    'kilometraje' => $km,
                    'unidad' => $unidad,
                    'precio' => $precio,
                ];
            }, $request->productos, $request->cantidad, $request->necesidad, $request->km, $unidad_medida, $precio);

            $pedido->estado = $orden_completa ? 'Finalizado' : 'En Proceso';
            $pedido->subproyecto = $request->subproyecto;

            if (!$pedido->save()) {
                throw new Exception('Error al intentar actualizar el estado de la adquisición.');
            }
            foreach ($result as $index => $data) {
                $articulo_id = is_numeric($data['articulo_id'])
                    ? $data['articulo_id']
                    : $this->registrarNuevoProducto($tipo_etapa, $data['articulo_id'])->id;

                $detalle = AdquisicionDetalle::updateOrCreate(
                    [
                        'articulo_id' => $articulo_id,
                        'adquisicion_id' => $pedido_id
                    ],
                    [
                        'cantidad_solicitada' => $data['cantidad_solicitada'],
                        'necesidad' => $data['necesidad'],
                        'kilometraje' => $data['kilometraje'],
                        'unidad_medida_id' => $data['unidad'],
                        'valor' => $data['precio']
                    ]
                );

                if (!$detalle) {
                    throw new Exception('Error al guardar el detalle de adquisición.');
                }

                agregarPalabra($data['necesidad']);

                if (isset($inventario)) {
                    if ($inventario[$index] && $orden_completa) {
                        Inventario::create([
                            'orden_recepcion_id' => $pedido->orden_recepcion->id,
                            'producto_id' => $data['articulo_id'],
                            'cantidad' => $data['cantidad_solicitada'],
                            'fecha' => date('Y-m-d'),
                            'usuario_id' => Auth::user()->id,
                            'estado' => 10,
                        ]);
                    }
                }
            }

            // Eliminar los registros que no están en el formulario
            if (!empty($productos_eliminar)) {
                AdquisicionDetalle::where('adquisicion_id', $pedido_id)
                    ->whereIn('articulo_id', $productos_eliminar)->delete();
            }

            /// Actualizar tabla ordenm_recepcion
            $orden = OrdenRecepcion::where('adquisicion_id', $pedido_id)->first();

            if ($orden) {
                $orden->completado = $orden_completa;
                $orden->editar = $orden_completa ? false : true;
                $orden->proveedor_id = $request->proveedor;

                if (!$orden->save()) {
                    throw new Exception('Error al intentar actualizar la adquisición.');
                }
            } else {
                $recepcion = [
                    'fecha' => date('Y-m-d'),
                    'adquisicion_id' => $pedido_id,
                    'proveedor_id' => $request->proveedor,
                    'forma_pago_id' => $request->forma_pago,
                    'completado' => $orden_completa,
                    'editar' => $orden_completa ? false : true,
                ];

                if (!OrdenRecepcion::create($recepcion)) {
                    throw new Exception('Error al intentar guardar la adquisición.');
                }
            }

            if ($request->hasFile('archivo')) {
                $path_archivo = $this->subriArchivo($request->file('archivo'), $pedido->numero);
                $pedido->archivo = $path_archivo;
                $pedido->save();
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

        if ($pedido->estado != 'Finalizado' && $pedido->estado != 'Completado') {
            $info_pedido = $pedido;
            if ($pedido->delete()) {
                if (Storage::disk('digitalocean')->exists($info_pedido->archivo)) {
                    Storage::disk('digitalocean')->delete($info_pedido->archivo);
                }
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
                ->where('tipo_adquisicion', 'operativo')
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
                ->orderBy('fecha', 'desc')
                ->get();

            if ($list_pedidos) {
                $route_parametres = $this->getRouteParameters($request);
                foreach ($list_pedidos as $pedido) {
                    $route_parametres = array_merge($route_parametres, ['pedido' => $pedido->id]);
                    $editar_button = "<a href='" . route('proyecto.adquisiciones.orden.pedido.edit', $route_parametres) . "' class='dropdown-item'>Editar</a>";
                    $destroy_button = "<a href='#' class='dropdown-item eliminar-pedido' id='" . $pedido->id . "'>Eliminar</a>";
                    $pdf_orden_adquisicion_button = "<a href='" . route('pdf.adquisicion', $pedido->id) . "' class='dropdown-item' target='_blank'>PDF Orden Pedido</a>";
                    $pdf_orden_recepcion_button = "<a href='" . route('pdf.recepcion', $pedido->id) . "' class='dropdown-item' target='_blank'>Generar PDF</a>";

                    $estado = $pedido->estado == 'Finalizado' || $pedido->estado == 'Completado' ? '<span class="badge badge-success">Finalizado</span>' : '<span class="badge badge-warning">' . $pedido->estado . '</span>';
                    $output .= '<tr id="' . $pedido->id . '">' .
                        '<td class="align-middle">' . $pedido->numero . '</td>' .
                        '<td class="align-middle">' . date('d-m-Y', strtotime($pedido->fecha)) . '</td>' .
                        '<td class="align-middle">' . $pedido->proyecto->nombre_proyecto . '</td>' .
                        '<td class="align-middle">' . $pedido->etapa->descripcion . '</td>' .
                        '<td class="align-middle">' . $pedido->tipo_etapa->descripcion . '</td>' .
                        '<td class="align-middle">' . $estado . '</td>' .
                        '<td class="align-middle align-middle text-right text-truncate">' .
                        '<button type="button" class="btn btn-outline-dark" data-container="body" data-toggle="popover" data-placement="left" data-trigger="focus" data-content ="' . $editar_button . $destroy_button .  $pdf_orden_recepcion_button . '"><i class="fas fa-caret-left font-weight-normal"></i> Opciones
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
        $unidade_medida_id = 0;
        $unidade_medida_text = '';
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

                    // si la adquisiciones un servicio
                    if (strtoupper($info_pedido->tipo_etapa->slug) == 'SERVICIOS') {
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
                    }


                    if ($inventario[$index] && $orden_completa) {
                        Inventario::create([
                            'orden_recepcion_id' => $orden_recepcion->id,
                            'producto_id' => $detalle->articulo_id,
                            'cantidad' => str_replace(',', '', $cantidades_recibidas[$index]),
                            'fecha' => date('Y-m-d'),
                            'usuario_id' => Auth::user()->id,
                            'estado' => 10,
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
                    // si la adquisiciones un servicio
                    if (strtoupper($info_pedido->tipo_etapa->slug) == 'SERVICIOS') {
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
                    }

                    if ($inventario[$index] && $orden_completa) {
                        Inventario::create([
                            'orden_recepcion_id' => $orden_recepcion->id,
                            'producto_id' => $detalle->articulo_id,
                            'cantidad' => str_replace(',', '', $cantidades_recibidas[$index]),
                            'fecha' => date('Y-m-d'),
                            'usuario_id' => Auth::user()->id,
                            'estado' => 10,
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
     * 
     */
    public function buscarAdquisicionAdministrativo(Request $request, $tipo)
    {

        if ($request->ajax()) {
            $buscar = $request->buscar;
            $tipo_busqueda  = $request->tipo == 'pendientes' ? 'Finalizado' : 'Completado';
            $output = '';

            $adquisiciones = Adquisicion::where(function ($query) use ($buscar) {
                $query->where('numero', 'LIKE', '%' . $buscar . '%')
                    ->orWhereHas('proyecto', function ($q) use ($buscar) {
                        $q->where('nombre_proyecto', 'LIKE', '%' . $buscar . '%');
                    });
            })
                ->where('tipo_adquisicion', $tipo)
                ->where('estado', $tipo_busqueda)
                ->orderBy('fecha', 'desc')
                ->get();
            foreach ($adquisiciones as $index => $adquisicion) {
                $opciones_boton = '';
                if ($tipo == 'operativo') {
                    $editar = "<a href='" . route('administrativo.adquisicion.edit', ['tipo' => $tipo, 'adquisicion' => $adquisicion->id]) . "' class='dropdown-item'>Detalle</a>";
                    $pdf = "<a href='" . route('pdf.recepcion', $adquisicion->id) . "' class='dropdown-item' target='_blank'>Generar PDF</a>";
                    $opciones_boton .= $editar . $pdf;
                } else {
                    $editar = "<a href='" . route('administrativo.adquisicion.edit', ['tipo' => $tipo, 'adquisicion' => $adquisicion->id]) . "' class='dropdown-item'>Editar Pedido</a>";
                    $recepcion = "<a href='" . route('administrativo.adquisicion.recepcion', ['tipo' => $tipo, 'adquisicion' => $adquisicion->id]) . "' class='dropdown-item'>Recepción</a>";
                    $pdf = "<a href='" . route('pdf.recepcion', $adquisicion->id) . "' class='dropdown-item' target='_blank'>Generar PDF</a>";

                    $opciones_boton .= $editar . $recepcion . $pdf;
                }

                $proyecto = $adquisicion->proyecto_id > 0 ? strtoupper($adquisicion->proyecto->nombre_proyecto) : 'GENERAL';
                $output .= '<tr id="' . $adquisicion->id . '">' .
                    '<td class="align-middle">' . $adquisicion->numero . '</td>' .
                    '<td class="align-middle">' . date('d-m-Y', strtotime($adquisicion->fecha)) . '</td>' .
                    '<td class="align-middle">' . $proyecto . '</td>' .
                    '<td class="align-middle">' . strtoupper($adquisicion->etapa->descripcion) . '</td>' .
                    '<td class="align-middle">' . strtoupper($adquisicion->tipo_etapa->descripcion) . '</td>' .
                    '<td class="align-middle align-middle text-right text-truncate">' .
                    '<button type="button" class="btn btn-outline-dark" data-container="body"
                                        data-toggle="popover" data-placement="left" data-trigger="focus"
                                        data-content ="' . $opciones_boton . '">
                                        <i class="fas fa-caret-left font-weight-normal"></i> Opciones
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

    //** Funciones para el modulo de Adquisisciones administrativas */
    public function nuevaAdquisicionAdministrativo($tipo)
    {
        $title_page = 'Nueva Adquisición';
        $adquisicion = new Adquisicion();

        $numero_orden = generarNumeroOrden();
        $proyectos = Proyecto::orderBy('nombre_proyecto', 'desc')->pluck('nombre_proyecto', 'id');
        $proyectos = $proyectos->toArray(); // Convertir a array
        $proyectos['0'] = 'GENERAL'; // Añadir el nuevo elemento al final
        $proyectos = collect($proyectos); // Convertir nuevamente a colección si es necesario
        $etapa = CatalogoDato::getChildrenCatalogo('tipo.costos')->pluck('descripcion', 'id');
        $actividad = CatalogoDato::getChildrenCatalogo('proveedor')->pluck('descripcion', 'id');
        $productos = Articulo::where('activo', true)
            ->orderBy('descripcion', 'asc')->pluck('descripcion', 'id');

        $proveedores = Proveedor::pluck('razon_social', 'id');
        $unidad_medidas = CatalogoDato::getChildrenCatalogo('unidades.medida')->pluck('descripcion', 'id');

        $breadcrumbs = [
            ['name' => 'Inicio', 'url' => route('home')],
            ['name' => 'Adquisiciones', 'url' => route('administrativo.adquisiciones', $tipo)],
            ['name' => $title_page, 'url' => '']
        ];

        return view('administrativo.adquisiciones.create', compact('adquisicion', 'numero_orden', 'tipo', 'title_page', 'breadcrumbs', 'productos', 'proyectos', 'etapa', 'actividad', 'proveedores', 'unidad_medidas'));
    }

    public function storeAdquisicionAdministrativo(AdquisicionAdministrativoRequest $request, $tipo)
    {
        $fecha =  date('Y-m-d');
        $numero_pedido = $request->numero_orden;
        $proyecto_id = $request->proyecto;
        $adquisicion_id = $request->actividad;
        $etapa_id = $request->etapa;
        $proveedor = $request->proveedor;
        $numero_factura = $request->numero_factura;

        $productos = $request->productos;
        $cantidad = $request->cantidad;
        $unidad_medida = $request->unidad_medida;
        $valor_unitario = $request->valor_unitario;
        $iva_producto = $request->iva_producto;
        $necesidad = $request->necesidad;
        $inventario = $request->inventario;
        $orden_completa = $request->has('orden_completa') ? true : false;
        $forma_pago = $request->forma_pago;
        $estado = $orden_completa ? 'Completado' : 'En Proceso';

        try {
            DB::beginTransaction();

            $adquisicion = Adquisicion::create([
                'fecha' => $fecha,
                'numero' => $numero_pedido,
                'proyecto_id' => $proyecto_id,
                'etapa_id' => $etapa_id,
                'tipo_etapa_id' => $adquisicion_id,
                'usuario_id' => Auth::user()->id,
                'tipo_adquisicion' => $tipo,
                'estado' => $estado,
                'factura' => $numero_factura,
            ]);

            if ($adquisicion) {
                foreach ($productos as $index => $producto) {
                    $param_detalle_adquisicion = [
                        'adquisicion_id' => $adquisicion->id,
                        'articulo_id' => '',
                        'cantidad_solicitada' => str_replace(',', '', $cantidad[$index]),
                        'cantidad_recibida' => str_replace(',', '', $cantidad[$index]),
                        'unidad_medida_id' => $unidad_medida[$index],
                        'valor' => $valor_unitario[$index],
                        'necesidad' => $necesidad[$index]
                    ];

                    if (is_numeric($producto)) {
                        $param_detalle_adquisicion['articulo_id'] = $producto;
                    } else {
                        $etapa = CatalogoDato::find($adquisicion_id);
                        $nuevo_producto = $this->registrarNuevoProducto($etapa, $producto);
                        $param_detalle_adquisicion['articulo_id'] = $nuevo_producto->id;
                    }

                    $detalle = AdquisicionDetalle::create($param_detalle_adquisicion);

                    agregarPalabra($necesidad[$index]);

                    // registrar precio unitario del producto y el iva
                    $articulo = Articulo::find($detalle->articulo_id);
                    $articulo->valor_unitario = $valor_unitario[$index];
                    $articulo->iva = $iva_producto[$index];
                    $articulo->save();
                }
                /// Crear orden de recepcion
                $orden_recepcion_param = [
                    'fecha' => $fecha,
                    'adquisicion_id' => $adquisicion->id,
                    'proveedor_id' => $proveedor,
                    'forma_pago_id' => $forma_pago,
                    'completado' => $orden_completa,
                    'editar' => $orden_completa ? false : true,
                ];

                $orden_recepcion = OrdenRecepcion::updateOrCreate(
                    [
                        'adquisicion_id' => $adquisicion->id
                    ],
                    $orden_recepcion_param
                );

                if ($orden_completa) {
                    foreach ($inventario as $index => $item) {
                        if ($item) {
                            Inventario::create([
                                'orden_recepcion_id' => $orden_recepcion->id,
                                'producto_id' => $productos[$index],
                                'cantidad' => str_replace(',', '', $cantidad[$index]),
                                'fecha' => date('Y-m-d'),
                                'usuario_id' => Auth::user()->id,
                                'estado' => 10,
                            ]);
                        }
                    }


                    // Registrar en caja si el pago esta completado y si fue pagado en efectivo
                    if ($adquisicion->orden_recepcion && $adquisicion->orden_recepcion->forma_pago->slug == 'forma.pago.contado') {
                        $detalles = AdquisicionDetalle::where('adquisicion_id', $adquisicion->id)->get();

                        foreach ($detalles as $detalle) {

                            $request->merge([
                                'proveedor' => $adquisicion->orden_recepcion->proveedor_id,
                                'articulo' => $detalle->articulo_id,
                                'tipo_movimiento' => 'egreso',
                                'monto'        => calcularTotalProducto($detalle->cantidad_solicitada, $detalle->valor, $detalle->iva),
                                'detalle'  =>  $detalle->necesidad,
                                'referencia'   => $adquisicion->factura,
                                'origen_type' => 'adquisicion_administrativa',
                                'origen_id' => $adquisicion->id,
                            ]);

                            MovimientoCaja::registrarMovimiento($request);
                        }
                    }
                }


                DB::commit();
                LogService::log('info', 'Adquisición administrativa creada', ['user_id' => auth()->id(), 'action' => 'create']);
                return redirect()->route('administrativo.adquisiciones.create', $tipo)->with('success', 'Pedido generado con éxito.');
            } else {
                DB::rollback();
                LogService::log('error', 'Error al crear Adquisición', ['user_id' => auth()->id(), 'action' => 'create', 'message' => 'ocurrio un error al intentar crear la adquisición']);
                return redirect()->route('administrativo.adquisiciones.create', $tipo)->with('error', 'Ocurrió un error al generar el pedido, por favor vuela a intentarlo. Si el problema persiste, comuníquese con el administrador del sistema.');
            }
        } catch (Throwable $e) {
            DB::rollBack();
            LogService::log('error', 'Error al crear Adquisición', ['user_id' => auth()->id(), 'action' => 'create', 'message' => $e->getMessage()]);
            return redirect()->route('administrativo.adquisiciones.create', $tipo)->with('error', 'Ocurrió un error inesperado, comuníquese con el administrador del sistema.');
        }
    }

    private function subriArchivo($file, $name)
    {
        $extension = $file->getClientOriginalExtension();
        $path_file = Storage::disk('digitalocean')->putFileAs('adquisiciones', $file, $name . '.' . $extension);

        return $path_file;
    }
}
