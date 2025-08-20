<?php

namespace App\Http\Controllers;

use Exception;
use Throwable;
use Carbon\Carbon;
use App\Models\Articulo;
use App\Models\Proyecto;
use App\Models\Proveedor;
use App\Models\Contratista;
use App\Models\CatalogoDato;
use App\Services\LogService;
use Illuminate\Http\Request;
use App\Models\DetalleContratista;
use Illuminate\Support\Facades\DB;
use App\Constants\MessagesConstant;
use App\Enums\PushNotificationsEnum;
use Illuminate\Support\Facades\Auth;
use App\Services\PushNotificationService;
use App\Models\PagoOrdenTrabajoContratista;

class ContratistaController extends Controller
{
    public function index(Request $request)
    {
        $title_page = 'Contratista';
        $route_params = $this->getRouteParameters($request);

        $breadcrumbs = [
            ['name' => 'Inicio', 'url' => route('home')],
            ['name' => $route_params['proyecto']->nombre_proyecto, 'url' => route('proyecto.adquisiciones.tipo', ['tipo' => $request->route('tipo'), 'tipo_id' => $request->route('tipo_id'), 'proyecto' => $request->route('proyecto'), 'tipo_adquisicion' => $request->route('tipo_adquisicion')])],
            ['name' => 'Contratistas', 'url' => ''] // Último breadcrumb no tiene URL, es el actual
        ];

        $orden_trabajos = Contratista::where('proyecto_id', $route_params['proyecto']->id)
            ->where('etapa_id', $request->tipo_adquisicion)
            ->where('tipo_etapa_id', $request->tipo_etapa)
            ->orderBy('id', 'desc')
            ->paginate(15);

        $route_params = array_merge($route_params, ['orden_trabajos' => $orden_trabajos, 'breadcrumbs' => $breadcrumbs, 'title_page' => $title_page]);
        return view('contratista.index', $route_params);
    }

    public function crearOrdenTrabajo(Request $request)
    {
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

        $proyecto = Proyecto::findOrFail($request->proyecto);
        $subproyectos = $proyecto->subproyectos_unicos;

        $route_params = array_merge(
            $route_params,
            [
                'numero_orden' => $numero_orden,
                'fecha' => $fecha,
                'orden_trabajo' => $orden_trabajo,
                'proveedores' => $proveedores,
                'breadcrumbs' => $breadcrumbs,
                'title_page' => $title_page,
                'unidades_medidas' => $unidades_medidas,
                'articulos' => $articulos,
                'subproyectos' => $subproyectos,
            ]
        );

        return view('contratista.create', $route_params);
    }

    public function storeOrdenTrabajo(Request $request)
    {
        $proyecto = $request->proyecto_id;
        $tipo_adquisicion = $request->tipo_adquisicion;
        $tipo_etapa = $request->tipo_etapa;
        $fecha = $request->fecha;
        $proveedor = $request->proveedor;
        $categoria = $request->categoria;
        $productos = $request->productos;
        $cantidad = $request->cantidad;
        $unidad_medida = $request->unidad_medida;
        $precio_unitario = $request->precio_unitario;
        $plazo = $request->input('plazo_semanas', 0);
        $nro_casas = $request->numero_casas;
        $subproyecto = $request->subproyecto;

        try {
            DB::beginTransaction();
            $orden_trabajo_param = [
                'fecha' => $fecha,
                'plazo_semanas' => $plazo,
                'proveedor_id' => $proveedor,
                'articulo_id' => $categoria,
                'proyecto_id' => $proyecto,
                'etapa_id' => $tipo_adquisicion,
                'tipo_etapa_id' => $tipo_etapa,
                'usuario_id' => Auth::user()->id,
                'estado_id' => 38,
                'numero_casas' => $nro_casas,
                'subproyecto' => $subproyecto,
            ];

            if ($orden_trabajo = Contratista::create($orden_trabajo_param)) {
                foreach ($productos as $index => $producto) {
                    $valor = str_replace(',', '', $precio_unitario[$index]);
                    $tipo_etapa = CatalogoDato::find($request->tipo_etapa);

                    $parametros = [
                        'unidad_medida_id' => '',
                        'contratista_id' => $orden_trabajo->id,
                        'articulo_id' => is_numeric($producto) ? $producto : registrarProducto($tipo_etapa, $producto, $valor)->id,
                        'cantidad' => $cantidad[$index],
                        'valor_unitario' => $valor,
                    ];

                    if (is_numeric($unidad_medida[$index])) {
                        $parametros['unidad_medida_id'] = $unidad_medida[$index];
                    } else {
                        $new_unidad_medida = registrarUnidadMedida($unidad_medida[$index]);
                        $parametros['unidad_medida_id'] = $new_unidad_medida;
                    }

                    DetalleContratista::create($parametros);
                }
                DB::commit();

                PushNotificationService::sendNotification(PushNotificationsEnum::OPERATIVO, 'Contratista', 'Se registro una nueva orden de trabajo para el contratisa ' . $orden_trabajo->proveedor->razon_social, route('administrativo.index.contratistas'));

                return redirect()->route('proyecto.adquisiciones.contratista', ['tipo' => $request->route('tipo'), 'tipo_id' => $request->route('tipo_id'), 'proyecto' => $request->route('proyecto'), 'tipo_adquisicion' => $request->route('tipo_adquisicion'), 'tipo_etapa' => $request->route('tipo_etapa')])->with('success', 'Orden de trabajo contratista creada con éxito.');
            }
            throw new Exception(MessagesConstant::DEFAUL_ERROR);
        } catch (Throwable $e) {
            DB::rollBack();
            LogService::log('error', 'Error al crear orden de trabajo contratista', ['user_id' => auth()->id(), 'action' => 'create', 'message' => $e->getMessage()]);

            return redirect()->back()->with('error', MessagesConstant::CATCH_ERROR);
        }
    }

    /**
     * Editar orden de trabajo
     */
    public function editarOrdenTrabajo(Request $request)
    {
        $title_page = 'Editar orden de trabajo';
        $route_params = $this->getRouteParameters($request);
        $orden_trabajo = Contratista::find($request->contratista);
        $fecha = $orden_trabajo->fecha;

        $ultimo_registro = Contratista::latest()->first();
        $numero_orden = numeroOrden($ultimo_registro, false);
        $unidades_medidas = CatalogoDato::getChildrenCatalogo('unidades.medida');
        $articulos = Articulo::where('activo', true)->pluck('descripcion', 'id');

        $breadcrumbs = [
            ['name' => 'Inicio', 'url' => route('home')],
            ['name' => 'Contratistas', 'url' => route('proyecto.adquisiciones.contratista', ['tipo' => $request->route('tipo'), 'tipo_id' => $request->route('tipo_id'), 'proyecto' => $request->route('proyecto'), 'tipo_adquisicion' => $request->route('tipo_adquisicion'), 'tipo_etapa' => $request->route('tipo_etapa')])],
            ['name' => 'Editar Orden de Trabajo', 'url' => '']
        ];

        $proveedores = Proveedor::where('categoria_proveedor_id', $route_params['tipo_etapa']->id)->pluck('razon_social', 'id');

        $subTotal = $orden_trabajo->detalle_contratistas->sum(function ($detalle) {
            return $detalle->valor_unitario * $detalle->cantidad;
        });
        $totalGeneral = $orden_trabajo->detalle_contratistas->sum(function ($detalle) use ($orden_trabajo) {
            return  $orden_trabajo->numero_casas * ($detalle->valor_unitario * $detalle->cantidad);
        });

        $proyecto = Proyecto::findOrFail($request->proyecto);
        $subproyectos = $proyecto->subproyectos_unicos;

        $route_params = array_merge(
            $route_params,
            [
                'numero_orden' => $numero_orden,
                'fecha' => $fecha,
                'orden_trabajo' => $orden_trabajo,
                'proveedores' => $proveedores,
                'breadcrumbs' => $breadcrumbs,
                'title_page' => $title_page,
                'unidades_medidas' => $unidades_medidas,
                'articulos' => $articulos,
                'subTotal' => $subTotal,
                'totalGeneral' => $totalGeneral,
                'subproyectos' => $subproyectos,
            ]
        );

        return view('contratista.edit', $route_params);
    }

    /**
     * Actualizar orden de trabajo
     */
    public function updateOrdenTrabajo(Request $request)
    {
        try {
            $route_params = $this->getRouteParameters($request);
            DB::beginTransaction();

            $plazo = $request->plazo_semanas;
            $nro_casas = $request->numero_casas;

            $items = array_map(function ($producto, $cantidad, $valor, $unidad_medida) {
                $valoresLimpios = preg_replace('/[^0-9.]/', '', $valor); // Elimina $ y otros caracteres no numéricos

                return [
                    'producto' => is_numeric($producto) ? $producto : agregarProducto($producto, $valoresLimpios, 0, $unidad_medida),
                    'cantidad' => str_replace(',', '', $cantidad),
                    'unidad_medida' => is_numeric($unidad_medida) ? $unidad_medida : registrarUnidadMedida($unidad_medida),
                    'valor' => $valoresLimpios
                ];
            }, $request->productos, $request->cantidad, $request->precio_unitario, $request->unidad_medida);

            $orden_trabajo = Contratista::find($request->contratista);
            $orden_trabajo->plazo_semanas = $plazo;
            $orden_trabajo->numero_casas = $nro_casas;
            $orden_trabajo->subproyecto = $request->subproyecto;

            $productosExistente = DetalleContratista::where('contratista_id', $orden_trabajo->id)->pluck('articulo_id')->toArray();
            $productosEliminar = array_diff($productosExistente, $request->productos);

            if ($orden_trabajo->save()) {
                foreach ($items as $index => $item) {
                    DetalleContratista::updateOrCreate(
                        [
                            'contratista_id' => $request->contratista,
                            'articulo_id' => $item['producto'],
                        ],
                        [
                            'cantidad' => $item['cantidad'],
                            'unidad_medida_id' => $item['unidad_medida'],
                            'valor_unitario' => $item['valor'],
                        ]
                    );
                }

                if (!empty($productosEliminar)) {
                    DetalleContratista::where('contratista_id', $orden_trabajo->id)
                        ->whereIn('articulo_id', $productosEliminar)->delete();
                }

                DB::commit();
                $route_params = array_merge($route_params, ['contratista' => $request->contratista]);
                return redirect()->route('proyecto.adquisiciones.contratista.editar.orden.trabajo', $route_params)->with('success', 'Orden de trabajo actualizada con éxito.');
            }
        } catch (Throwable $e) {
            DB::rollBack();
            LogService::log('error', 'Error al actualizar orden de trabajo contratista', ['user_id' => auth()->id(), 'action' => 'update', 'message' => $e->getMessage()]);

            return redirect()->back()->with('error', MessagesConstant::CATCH_ERROR);
        }
    }

    public function pagosOrdenTrabajo(Request $request)
    {
        $title_page = 'Pagos Contratista';
        $route_params = $this->getRouteParameters($request);

        $breadcrumbs = [
            ['name' => 'Inicio', 'url' => route('home')],
            ['name' => 'Contratistas', 'url' => route('proyecto.adquisiciones.contratista', ['tipo' => $request->route('tipo'), 'tipo_id' => $request->route('tipo_id'), 'proyecto' => $request->route('proyecto'), 'tipo_adquisicion' => $request->route('tipo_adquisicion'), 'tipo_etapa' => $request->tipo_etapa])],
            ['name' => 'Pagos Contratistas', 'url' => ''] // Último breadcrumb no tiene URL, es el actual
        ];

        $orden_trabajo_id = $request->contratista;
        $pagos_orden_trabajos = PagoOrdenTrabajoContratista::where('contratista_id', $orden_trabajo_id)
            ->orderBy('id', 'desc')
            ->paginate(15);

        $route_params = array_merge($route_params, [
            'pagos_orden_trabajos' => $pagos_orden_trabajos,
            'contratista' => $orden_trabajo_id,
            'title_page' => $title_page,
            'breadcrumbs' => $breadcrumbs
        ]);

        return view('contratista.pagos.index', $route_params);
    }

    public function nuevoPagoOrdenTrabajo(Request $request)
    {
        $title_page = 'Nuevo Pago Contratista';
        $route_params = $this->getRouteParameters($request);
        $orden_trabajo = Contratista::find($request->contratista);

        if ($this->validarPago($request->contratista)) {
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

    public function guardarPagoOrdenTrabajo(Request $request)
    {
        try {
            $route_params = $this->getRouteParameters($request);
            $route_params = array_merge($route_params, ['contratista' => $request->contratista]);
            $orden_trabajo = Contratista::find($request->orden_trabajo);
            $valor = str_replace(',', '', $request->valor);
            $pagos = $orden_trabajo->pagos_contratistas + $valor;

            $total_pagar = $orden_trabajo->total_contratistas;

            if ($pagos > $total_pagar) {
                return redirect()->back()->with('error', 'No es posible registrar el pago, por favor, verifique que el monto a pagar no supere el saldo pendiente de pago de la orden de trabajo.');
            }

            DB::beginTransaction();
            $pago = [
                'fecha' => $request->fecha,
                'contratista_id' => $request->orden_trabajo,
                'tipo_pago' => $request->tipo,
                'forma_pago' => $request->forma_pago,
                'valor' => str_replace(',', '', $request->valor),
                'detalle' => $request->detalle,
            ];

            if (PagoOrdenTrabajoContratista::create($pago)) {
                DB::commit();
                PushNotificationService::sendNotification(PushNotificationsEnum::OPERATIVO, 'Pago contratista', 'Se genero un nuevo pago para el contratisa ' . $orden_trabajo->proveedor->razon_social, route('administrativo.index.contratistas'));
                LogService::log('success', 'Pago orden de trabajo contratista registrado', ['user_id' => auth()->id(), 'action' => 'create']);

                return redirect()->route('proyecto.adquisiciones.contratista.nuevo.pago.orden.trabajo', $route_params)->with('success', 'Pago a la Orden de trabajo contratista registrado con éxito.');
            } else {
                throw new Exception(MessagesConstant::DEFAUL_ERROR);
            }
        } catch (Throwable $e) {
            DB::rollBack();
            LogService::log('error', 'Error al crear orden de trabajo contratista', ['user_id' => auth()->id(), 'action' => 'create', 'message' => $e->getMessage()]);

            return redirect()->back()->with('error', MessagesConstant::CATCH_ERROR);
        }
    }

    public function editarPagoOrdenTrabajo(Request $request)
    {
        $title_page = 'Editar Pago Contratista';
        $route_params = $this->getRouteParameters($request);
        $pago_orden_trabajo = PagoOrdenTrabajoContratista::find($request->pago_contratista);

        $breadcrumbs = [
            ['name' => 'Inicio', 'url' => route('home')],
            ['name' => 'Pagos Contratista', 'url' => route('proyecto.adquisiciones.contratista.pagos.orden.trabajo', ['tipo' => $request->route('tipo'), 'tipo_id' => $request->route('tipo_id'), 'proyecto' => $request->route('proyecto'), 'tipo_adquisicion' => $request->route('tipo_adquisicion'), 'tipo_etapa' => $request->tipo_etapa, 'contratista' => $pago_orden_trabajo->contratista_id])],
            ['name' => 'Editar Pago Contratistas', 'url' => ''] // Último breadcrumb no tiene URL, es el actual
        ];

        $route_params = array_merge($route_params, [
            'orden_trabajo' => $pago_orden_trabajo->contratista,
            'pago_orden_trabajo' => $pago_orden_trabajo,
            'title_page' => $title_page,
            'breadcrumbs' => $breadcrumbs
        ]);


        return view('contratista.pagos.edit', $route_params);
    }

    public function actualizarPagoOrdenTrabajo(Request $request)
    {
        try {
            $route_params = $this->getRouteParameters($request);
            $route_params = array_merge($route_params, ['pago_contratista' => $request->pago_contratista]);

            DB::beginTransaction();

            $pago_orden_trabajo = PagoOrdenTrabajoContratista::find($request->pago_contratista);
            if ($pago_orden_trabajo->pagado) {
                return redirect()->back()->with('error', 'No es posible actualizar el pago, porque ya fue registrado como pagado en administrativo.');
            }
            $pago_orden_trabajo->fecha = $request->fecha;
            $pago_orden_trabajo->tipo_pago = $request->tipo;
            $pago_orden_trabajo->forma_pago = $request->forma_pago;
            $pago_orden_trabajo->valor = str_replace(',', '', $request->valor);
            $pago_orden_trabajo->detalle = $request->detalle;

            if ($pago_orden_trabajo->save()) {
                DB::commit();
                LogService::log('success', 'Pago orden de trabajo contratista actualizado', ['user_id' => auth()->id(), 'action' => 'update']);

                return redirect()->route('proyecto.adquisiciones.contratista.editar.pago.orden.trabajo', $route_params)->with('success', 'Pago actualizado con éxito.');
            } else {
                throw new Exception(MessagesConstant::DEFAUL_ERROR);
            }
        } catch (Throwable $e) {
            DB::rollBack();
            LogService::log('error', 'Error al actualizar orden de trabajo contratista', ['user_id' => auth()->id(), 'action' => 'update', 'message' => $e->getMessage()]);

            return redirect()->back()->with('error', MessagesConstant::CATCH_ERROR);
        }
    }

    public function eliminarOrdenTrabajo($id)
    {
        $is_delete = Contratista::find($id)->delete();
        if ($is_delete) {
            return response()->json(['success' => true, 'message' => 'Registro eliminado correctamente']);
        } else {
            return response()->json(['success' => false, 'message' => 'No se pudo eliminar el registro']);
        }
    }

    public function eliminarPagoOrdenTrabajo(Request $request)
    {
        $is_delete = PagoOrdenTrabajoContratista::find($request->id)->delete();
        if ($is_delete) {
            return response()->json(['success' => true, 'message' => 'Registro eliminado correctamente']);
        } else {
            return response()->json(['success' => false, 'message' => 'No se pudo eliminar el registro']);
        }
    }

    private function validarPago($orden_trabajo)
    {
        $orden_trabajo = Contratista::find($orden_trabajo);
        $pagos = $orden_trabajo->pagos_contratistas;
        $total_pagar = $orden_trabajo->total_contratistas;

        return $total_pagar == $pagos ? true : false;
    }

    public function buscarPagoOrdenTrabajo(Request $request)
    {
        if ($request->ajax()) {
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
            foreach ($orden_trabajos as $orden_trabajo) {

                $avances = "<a href='" . route('proyecto.adquisiciones.contratista.pagos.orden.trabajo', ['tipo' => $request->tipo, 'tipo_id' => $request->tipo_id, 'proyecto' => $proyecto_id, 'tipo_etapa' => $tipo_adquisicion_id, 'tipo_adquisicion' => $etapa_id, 'contratista' => $orden_trabajo->id]) . "' class='dropdown-item'>Avances</a>";
                $editar = "<a href='" . route('proyecto.adquisiciones.contratista.editar.orden.trabajo', ['tipo' => $request->tipo, 'tipo_id' => $request->tipo_id, 'proyecto' => $proyecto_id, 'tipo_etapa' => $tipo_adquisicion_id, 'tipo_adquisicion' => $etapa_id, 'contratista' => $orden_trabajo->id]) . "' class='dropdown-item'>Editar</a>";
                $eliminar = "<a href='#' class='dropdown-item eliminar-orden-trabajo' id='" . $orden_trabajo->id . "'>Eliminar</a>";
                $pdf = "<a href='" . route('pdf.orden.trabajo.contratista', $orden_trabajo->id) . "' class='dropdown-item'>PDF Orden Trabajo</a>";

                $estado = $orden_trabajo->tipo_pago_contratista ? $orden_trabajo->tipo_pago_contratista : 'NUEVO';

                $output .= '<tr id="' . $orden_trabajo->id . '">' .
                    '<td class="align-middle">' . numeroOrden($orden_trabajo, false) . '</td>' .
                    '<td class="align-middle text-uppercase">' . $orden_trabajo->proveedor->razon_social . '</td>' .
                    '<td class="align-middle text-uppercase">' . $orden_trabajo->articulo->descripcion . '</td>' .
                    '<td class="align-middle">$' . number_format($orden_trabajo->total_contratistas, 2) . '</td>' .
                    '<td class="align-middle">$' . number_format($orden_trabajo->pagos_contratistas, 2) . '</td>' .
                    '<td class="align-middle">$ ' . number_format(($orden_trabajo->total_contratistas - $orden_trabajo->pagos_contratistas), 2) . '</td>' .
                    '<td class="align-middle">' .
                    '<span class="badge badge-secondary">' . $estado . '</span>' .
                    '</td>' .
                    '<td class="align-middle align-middle text-right text-truncate">' .
                    '<button type="button" class="btn btn-outline-dark" data-container="body"
                                        data-toggle="popover" data-placement="left" data-trigger="focus"
                                        data-content ="' . $avances . $editar . $eliminar . $pdf . '">
                                            <i class="fas fa-caret-left font-weight-normal"></i> Opciones
                                        </button>' .
                    '</td>' .
                    '</tr>';
            }
            if (empty($output)) {
                $output .= '<tr>' .
                    '<td colspan="8" class="text-center">' .
                    '<span class="text-danger">No existen datos para mostrar.</span>' .
                    '</td>' .
                    '</tr>';
            }
            return Response($output);
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
