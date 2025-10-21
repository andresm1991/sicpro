<?php

namespace App\Http\Controllers;

use Exception;
use Throwable;
use App\Models\Articulo;
use App\Models\ManoObra;
use App\Models\Proyecto;
use App\Models\Proveedor;
use App\Models\Inventario;
use App\Models\Adquisicion;
use App\Models\Contratista;
use App\Models\CatalogoDato;
use App\Models\PagoManoObra;
use App\Services\LogService;
use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use App\Models\MovimientoCaja;
use App\Models\OrdenRecepcion;
use App\Models\AdquisicionDetalle;
use App\Models\DetalleContratista;
use Illuminate\Support\Facades\DB;
use App\Enums\PushNotificationsEnum;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Services\PushNotificationService;
use App\Models\PagoOrdenTrabajoContratista;
use App\Http\Requests\RecepcionAdquisicionAdministrativoRequest;

class AdministrativoController extends Controller
{
    public function index()
    {
        $title_page = 'Administrativo';
        $breadcrumbs = [
            ['name' => 'Inicio', 'url' => route('home')],
            ['name' => 'Administrativo', 'url' => ''],
        ];

        return view('administrativo.menu_administrativo', compact('title_page', 'breadcrumbs'));
    }

    public function menuConstruccion()
    {
        $title_page = 'Contrucciones PrimeJP';
        $breadcrumbs = [
            ['name' => 'Inicio', 'url' => route('home')],
            ['name' => 'Administrativo', 'url' => route('administrativo.index')],
            ['name' => 'Construcción', 'url' => ''],
        ];

        return view('administrativo.menu_construccion', compact('title_page', 'breadcrumbs'));
    }

    public function adquisiciones($tipo)
    {
        $title_page = ($tipo == 'operativo') ? 'Adquisiciones Operativas' : 'Adquisiciones Administrativas';
        if ($tipo == 'operativo') {
            $adquisiciones_pendientes = Adquisicion::where('tipo_adquisicion', $tipo)
                ->where('estado', 'Finalizado')
                ->orderBy('fecha', 'desc')->paginate(15);
        } else {
            $adquisiciones_pendientes = Adquisicion::where('tipo_adquisicion', $tipo)
                ->where('estado', 'En Proceso')
                ->orderBy('fecha', 'desc')->paginate(15);
        }


        $adquisiciones_completas = Adquisicion::where('tipo_adquisicion', $tipo)
            ->where('estado', 'Completado')
            ->orderBy('fecha', 'desc')->paginate(15);

        $breadcrumbs = [
            ['name' => 'Inicio', 'url' => route('home')],
            ['name' => 'Administrativo', 'url' => Route('administrativo.index')],
            ['name' => $title_page, 'url' => '']
        ];

        return view('administrativo.adquisiciones.index', compact('adquisiciones_pendientes', 'adquisiciones_completas', 'tipo', 'title_page', 'breadcrumbs'));
    }

    public function editarAdquisicion($tipo, Adquisicion $adquisicion)
    {

        $title_page = 'Editar';

        $breadcrumbs = [
            ['name' => 'Inicio', 'url' => route('home')],
            ['name' => 'Adquisiciones ' . $tipo, 'url' => route('administrativo.adquisiciones', $tipo)],
            ['name' => 'Editar', 'url' => '']
        ];

        $totalGeneral = $adquisicion->adquisiciones_detalle->sum(function ($detalle) {
            $iva = $detalle->iva ?? 0;
            $valor = calcularProcentaje($detalle->valor, $detalle->costo_indirecto, 4);
            $total = calcularTotalProducto($detalle->cantidad_solicitada, $valor, $iva);
            return $total;
        });

        if ($tipo == 'operativo') {
            return view('administrativo.adquisiciones.edit', compact('adquisicion', 'tipo', 'totalGeneral', 'title_page', 'breadcrumbs'));
        } else {

            $proyectos = Proyecto::orderBy('nombre_proyecto', 'desc')->pluck('nombre_proyecto', 'id');
            $proyectos = $proyectos->toArray(); // Convertir a array
            $proyectos['0'] = 'General'; // Añadir el nuevo elemento al final
            $proyectos = collect($proyectos); // Convertir nuevamente a colección si es necesario
            $etapa = CatalogoDato::getChildrenCatalogo('tipo.costos')->pluck('descripcion', 'id');
            $actividad = CatalogoDato::getChildrenCatalogo('proveedor')->pluck('descripcion', 'id');
            $productos = Articulo::where('activo', true)->orderBy('descripcion', 'asc')->pluck('descripcion', 'id');
            $proveedores = Proveedor::pluck('razon_social', 'id');
            $unidad_medidas = CatalogoDato::getChildrenCatalogo('unidades.medida')->pluck('descripcion', 'id');
            return view('administrativo.adquisiciones.edit_administrativo', compact('adquisicion', 'tipo', 'proyectos', 'etapa', 'actividad', 'productos', 'title_page', 'breadcrumbs', 'proveedores', 'unidad_medidas', 'totalGeneral'));
        }
    }
    public function actualizarAdquisicion(Request $request, $tipo, Adquisicion $adquisicion)
    {
        if ($tipo == 'operativo') {
            return $this->actualizarAdquisicionOperativa($request, $tipo, $adquisicion);
        } else {
            return $this->actualizarAdquisicionAdministrativa($request, $tipo, $adquisicion);
        }
    }

    /**
     * Funcion que actualiza aquisicones operativas
     */
    private function actualizarAdquisicionOperativa(Request $request, $tipo, Adquisicion $adquisicion)
    {
        if (!auth()->user()->hasRole(['Administrador', 'Gerencial'])) {
            if ($adquisicion->estado == 'Completado') {
                if (is_null($adquisicion->factura) && !empty($request->numero_factura)) {
                    $adquisicion->factura = $request->numero_factura;
                    $adquisicion->save();
                    LogService::log('info', 'Actualizacion factura de la adquisicion #' . $adquisicion->id, ['user_id' => auth()->id(), 'action' => 'update']);
                    return redirect()->route('administrativo.adquisicion.edit', ['tipo' => $tipo, 'adquisicion' => $adquisicion->id])->with('success', 'Se actualizó la información de la factura con éxito.');
                } elseif (is_null($adquisicion->nro_proforma) && !empty($adquisicion->subproyecto) && !empty($request->nro_proforma)) {
                    $adquisicion->nro_proforma = $request->nro_proforma;
                    $adquisicion->save();
                    LogService::log('info', 'Actualizacion adquisicion #' . $adquisicion->id, ['user_id' => auth()->id(), 'mensaje' => 'se actualizo adquisicion']);
                    return redirect()->route('administrativo.adquisicion.edit', ['tipo' => $tipo, 'adquisicion' => $adquisicion->id])->with('success', 'Se actualizó la información de la adquisición con éxito.');
                }
                return redirect()->route('administrativo.adquisicion.edit', ['tipo' => $tipo, 'adquisicion' => $adquisicion->id])->with('error', 'No es posible actualizar la información de una adquisición que está completada, si desea modificar los datos comuníquese con el administrador del sistema.');
            }
        }
        // Limpia el símbolo de dólar de cada elemento en el arreglo 'valor'
        $valoresLimpios = array_map(fn($value) => preg_replace('/[^0-9.]/', '', $value), $request->input('valor', []));
        // Reemplaza los valores en el request con los valores limpios
        $request->merge(['valor' => $valoresLimpios]);

        $rules = [
            'unidad_medida' => 'required|array',
            'unidad_medida.*' => 'required',
            'valor' => 'required|array',
            'valor.*' => 'required|numeric',
            'iva_producto' => 'nullable|array',
            'iva_producto.*' => 'nullable|numeric',
        ];

        $messages = [
            'unidad_medida.required' => 'Seleccione una opción.',
            'unidad_medida.*.required' => 'Seleccione una opción.',
            'valor.required' => 'Ingrese el valor',
            'valor.*.required' => 'Ingrese el valor',
            'valor.*.numeric' => 'El valor ingresado es inválido,',
            'cantidad_recibida.required' => 'Ingrese al menos un valor de cantidad recibida.',
            'cantidad_recibida.*.required' => 'Ingrese el valor de cantidad recibida.',
            'cantidad_recibida.*.numeric' => 'El valor de cada cantidad recibida debe ser numérico.',
            'iva_producto.*.numeric' => 'El valor de debe ser numérico.',
        ];

        if ($tipo == "administrativo") {
            $rules = array_merge($rules, [
                'cantidad_recibida' => 'required|array',
                'cantidad_recibida.*' => 'required|numeric'
            ]);
        }

        $request->validate($rules, $messages);

        $estado = $request->orden_completa;
        $array_productos = $request->productos;

        $adquisicionesActuales = AdquisicionDetalle::where('adquisicion_id', $adquisicion->id)->pluck('articulo_id')->toArray();
        $productos_eliminar = array_diff($adquisicionesActuales, $array_productos);

        try {
            DB::beginTransaction();

            $items = array_map(function ($producto, $cantidad, $precio_unitario, $iva, $unidad_medida, $costo_indirecto, $kilometraje, $necesidad) {
                return [
                    'producto' => $producto,
                    'cantidad' => str_replace(',', '', $cantidad),
                    'valor' => limpiarValor($precio_unitario),
                    'costo_indirecto' => $costo_indirecto,
                    'iva' => $iva ?? 0,
                    'unidad_medida' => is_numeric($unidad_medida) ? $unidad_medida : registrarUnidadMedida($unidad_medida),
                    'kilometraje' => $kilometraje,
                    'necesidad' => $necesidad
                ];
            }, $request->input('productos', []), $request->input('cantidad', []), $request->input('valor', []), $request->input('iva_producto', []), $request->input('unidad_medida', []), $request->input('indirecto', []), $request->input('kilometraje', []), $request->input('necesidad', []));

            foreach ($items as $item) {
                AdquisicionDetalle::updateOrCreate(
                    ['adquisicion_id' => $adquisicion->id, 'articulo_id' => $item['producto']],
                    [
                        'cantidad_solicitada' => $item['cantidad'],
                        'unidad_medida_id' => $item['unidad_medida'],
                        'valor' => $item['valor'],
                        'costo_indirecto' => $item['costo_indirecto'],
                        'iva' => $item['iva'],
                        'kilometraje' => $item['kilometraje'],
                        'necesidad' => $item['necesidad'],
                    ]
                );

                // registrar precio unitario del producto y el iva
                $articulo = Articulo::find($item['producto']);
                $articulo->valor_unitario = $item['valor'];
                $articulo->iva = $item['iva'];
                $articulo->save();
            }

            $adquisicion->factura = $request->numero_factura;
            $adquisicion->nro_proforma = $request->nro_proforma;

            if ($estado) {
                $adquisicion->estado = "Completado";

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
                            'origen_type' => 'App\\Models\\Adquisicion',
                            'origen_id' => $adquisicion->id,
                        ]);

                        MovimientoCaja::registrarMovimiento($request);
                    }
                }

                PushNotificationService::sendNotification(PushNotificationsEnum::ADMINISTRATIVO, 'Administrativo. Actualización de Adquisición', 'El usuario ' . Auth::user()->nombre . ' completó la información de la adquisición operativa #' . $adquisicion->numero, route('pdf.recepcion', $adquisicion->id));
            }
            $adquisicion->save();
            /// Eliminr los articulos que no estan en $array_productos
            if (!empty($productos_eliminar)) {
                AdquisicionDetalle::where('adquisicion_id', $adquisicion->id)
                    ->whereIn('articulo_id', $productos_eliminar)->delete();
            }

            DB::commit();

            LogService::log('info', 'Actualizacion la informacion de la adquisicion #' . $adquisicion->id, ['user_id' => auth()->id(), 'action' => 'update']);

            return redirect()->route('administrativo.adquisicion.edit', ['tipo' => $tipo, 'adquisicion' => $adquisicion->id])->with('success', 'Se actualizó la información de la adquisición con éxito.');
        } catch (Throwable $e) {
            DB::rollBack();
            return $e;
            LogService::log('error', 'Error al actualizar la inforacion de la adquisicion #' . $adquisicion->id, ['user_id' => auth()->id(), 'action' => 'update', 'message' => $e->getMessage()]);
            return redirect()->route('administrativo.adquisicion.edit', ['tipo' => $tipo, 'adquisicion' => $adquisicion->id])->with('error', 'Ocurrió un error inesperado, comuníquese con el administrador del sistema.');
        }
    }

    private function actualizarAdquisicionAdministrativa(Request $request, $tipo, Adquisicion $adquisicion)
    {
        try {
            if (!auth()->user()->hasRole(['Administrador', 'Gerencial']) && $adquisicion->orden_recepcion && $adquisicion->orden_recepcion->completado) {
                return redirect()->route('administrativo.adquisicion.edit', ['tipo' => $tipo, 'adquisicion' => $adquisicion->id])->with('error', 'No es posible modificar la recepción porque esta esta completada.');
            }
            $orden_completa = $request->has('orden_completa') ? true : false;
            $pedido_id = $adquisicion->id;
            // Combinar arrays en uno solo
            $result = array_map(function ($producto, $cantidad, $necesidad, $unidad_medida, $iva, $valor) {
                $valoresLimpios = preg_replace('/[^0-9.]/', '', $valor); // Elimina $ y otros caracteres no numéricos

                return [
                    'articulo_id' => $producto,
                    'cantidad_solicitada' => str_replace(',', '', $cantidad),
                    'unidad_medida_id' => $unidad_medida,
                    'valor' => $valoresLimpios,
                    'iva' => $iva,
                    'necesidad' => $necesidad
                ];
            }, $request->productos, $request->cantidad, $request->necesidad, $request->unidad_medida, $request->iva_producto, $request->valor);

            DB::beginTransaction();

            $adquisicion->proyecto_id = $request->proyecto;
            $adquisicion->etapa_id = $request->etapa;
            $adquisicion->tipo_etapa_id = $request->actividad;
            $adquisicion->nro_proforma = $request->nro_proforma;
            $adquisicion->factura = $request->numero_factura;
            $adquisicion->subproyecto = $request->subproyecto;
            $adquisicion->tipo_costo_id = $request->tipo_costo;

            if ($orden_completa) {
                $adquisicion->estado = 'Completado';
            }
            $adquisicion->save();

            foreach ($result as $data) {
                AdquisicionDetalle::updateOrCreate(
                    [
                        'articulo_id' => $data['articulo_id'],
                        'adquisicion_id' => $adquisicion->id
                    ],
                    [
                        'cantidad_solicitada' => $data['cantidad_solicitada'],
                        'cantidad_recibida' => $data['cantidad_solicitada'],
                        'unidad_medida_id' => $data['unidad_medida_id'],
                        'necesidad' => $data['necesidad']
                    ]
                );

                agregarPalabra($data['necesidad']);

                // registrar precio unitario del producto y el iva
                $articulo = Articulo::find($data['articulo_id']);
                $articulo->valor_unitario = $data['valor'];
                $articulo->iva = $data['iva'];
                $articulo->save();
            }

            $orden_recepcion = OrdenRecepcion::updateOrCreate([
                'adquisicion_id' => $pedido_id,
            ], [
                'fecha' => date('Y-m-d'),
                'proveedor_id' => $request->proveedor,
                'forma_pago_id' => $request->forma_pago,
                'completado' => $orden_completa,
                'editar' => $orden_completa ? false : true,
            ]);

            if ($orden_completa) {

                foreach ($request->inventario as $index => $item) {
                    if ($item) {
                        Inventario::create([
                            'orden_recepcion_id' => $orden_recepcion->id,
                            'producto_id' => $request->productos[$index],
                            'cantidad' => str_replace(',', '', $request->cantidad[$index]),
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
            LogService::log('info', 'Actualizacion la informacion de la adquisicion #' . $adquisicion->id, ['user_id' => auth()->id(), 'action' => 'update']);

            return redirect()->route('administrativo.adquisicion.edit', ['tipo' => $tipo, 'adquisicion' => $adquisicion->id])->with('success', 'Se actualizó la información de la adquisición con éxito.');
        } catch (Throwable $e) {
            DB::rollBack();
            LogService::log('error', 'Error al actualizar la inforacion de la adquisicion #' . $adquisicion->id, ['user_id' => auth()->id(), 'action' => 'update', 'message' => $e->getMessage()]);
            return redirect()->route('administrativo.adquisicion.edit', ['tipo' => $tipo, 'adquisicion' => $adquisicion->id])->with('error', 'Ocurrió un error inesperado, comuníquese con el administrador del sistema.');
        }
    }

    public function recepcionAdquisicionAdministrativo($tipo, Adquisicion $adquisicion)
    {
        $title_page = 'Editar';

        $breadcrumbs = [
            ['name' => 'Inicio', 'url' => route('home')],
            ['name' => 'Adquisiciones ' . $tipo, 'url' => route('administrativo.adquisiciones', $tipo)],
            ['name' => 'Editar', 'url' => '']
        ];

        $proveedores = Proveedor::where('categoria_proveedor_id', $adquisicion->tipo_etapa_id)->pluck('razon_social', 'id');

        $totalGeneral = $adquisicion->adquisiciones_detalle->sum(function ($detalle) {
            $iva = $detalle->producto->iva ?? 0;
            $total = calcularTotalProducto($detalle->cantidad_solicitada, $detalle->valor, $iva);
            return $total;
        });

        return view('administrativo.adquisiciones.edit', compact('adquisicion', 'tipo', 'totalGeneral', 'proveedores', 'title_page', 'breadcrumbs'));
    }

    public function createRecepcionAdministrativo(RecepcionAdquisicionAdministrativoRequest $request, $tipo, Adquisicion $adquisicion)
    {
        if (isset($adquisicion->orden_recepcion) && $adquisicion->orden_recepcion->completado) {
            return redirect()->route('administrativo.adquisicion.recepcion', ['tipo' => $tipo, 'adquisicion' => $adquisicion->id])->with('error', 'No es posible modificar la recepción porque esta esta completada.');
        }

        $valoresLimpios = array_map(function ($value) {
            return preg_replace('/[^0-9.]/', '', $value); // Elimina $ y otros caracteres no numéricos
        }, $request->input('valor', []));

        // Reemplaza los valores en el request con los valores limpios
        $request->merge(['valor' => $valoresLimpios]);

        $orden_completa = $request->has('orden_completa') ? true : false;
        $cantidades_recibidas = $request->cantidad_recibida;
        $unidades_medidas = $request->unidad_medida;
        $unidade_medida_id = 0;
        $unidade_medida_text = '';
        $precio = $request->valor;
        $inventario = $request->inventario;

        $param = [
            'fecha' => date('Y-m-d'),
            'adquisicion_id' => $adquisicion->id,
            'proveedor_id' => $request->proveedor,
            'forma_pago_id' => $request->forma_pago,
            'completado' => $orden_completa,
            'editar' => $orden_completa ? false : true,
        ];

        try {
            DB::beginTransaction();
            $orden_recepcion = OrdenRecepcion::updateOrCreate(
                [
                    'adquisicion_id' => $adquisicion->id
                ],
                $param
            );
            if ($orden_recepcion) {
                /// Actualiza el estado del pedido
                if ($orden_completa) {
                    $adquisicion->estado = 'Completado';
                }
                $adquisicion->factura = $request->numero_factura;
                $adquisicion->nro_proforma = $request->nro_proforma;
                $adquisicion->save();
                // Actualiza la cantidad recibiba en el detalle del pedido
                foreach ($adquisicion->adquisiciones_detalle as $index => $detalle) {
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
                return redirect()->route('administrativo.adquisicion.recepcion', ['tipo' => $tipo, 'adquisicion' => $adquisicion->id])->with('success', 'Orden de recepción generada con éxito.');
            } else {
                throw new Exception('Error al intentar guardar la orden de recepcion');
            }
        } catch (Throwable $e) {
            DB::rollBack();
            LogService::log('error', 'Error al crear orden de recepción', ['user_id' => auth()->id(), 'action' => 'create', 'message' => $e->getMessage()]);
            return redirect()->route('administrativo.adquisicion.recepcion', ['tipo' => $tipo, 'adquisicion' => $adquisicion->id])->with('error', 'Ocurrió un error inesperado, comuníquese con el administrador del sistema.');
        }
    }

    public function destroyPedido(Request $request, $adquisicionId)
    {
        if ($request->ajax()) {
            // Buscar la adquisición por ID
            $pedido = Adquisicion::find($adquisicionId);

            if ($pedido) {
                // Eliminar el registro de la base de datos
                if ($pedido->delete()) {
                    // Verificar si hay un archivo asociado
                    if ($pedido->archivo && Storage::disk('digitalocean')->exists($pedido->archivo)) {
                        // Eliminar el archivo
                        Storage::disk('digitalocean')->delete($pedido->archivo);
                    }

                    // Registrar el log
                    LogService::log('info', 'Adquisición eliminada', ['user_id' => auth()->id(), 'action' => 'destroy']);

                    return response()->json(['success' => true, 'message' => 'Registro eliminado correctamente.']);
                } else {
                    return response()->json([
                        'success' => false,
                        'message' => 'Ocurrió un error al intentar eliminar el registro, por favor vuelva a intentar si el problema persiste comuníquese con el administrador del sistema.'
                    ]);
                }
            } else {
                return response()->json(['success' => false, 'message' => 'No se encontró el registro solicitado.']);
            }
        }
    }

    public function agergarProductoAdquisicion(Request $request, Adquisicion $adquisicion)
    {
        if ($request->ajax()) {
            return $request->all();
        }
    }

    /**
     * Contratistas
     */

    public function indexContratistas()
    {
        $title_page = 'Contratistas';

        $breadcrumbs = [
            ['name' => 'Inicio', 'url' => route('home')],
            ['name' => 'Administrativo', 'url' => route('administrativo.index')],
            ['name' => 'Contratistas', 'url' => '']
        ];

        // --- Obtener Contratos Pendientes ---
        $orden_trabajos_pendientes = Contratista::with(['proveedor', 'proyecto']) // Carga las relaciones necesarias
            ->withTotals()        // 1. Añade las columnas calculadas
            ->pendientes()        // 2. Filtra por los pendientes
            ->orderBy('fecha', 'asc')
            ->paginate(15, ['*'], 'pendientes_page');

        // --- Obtener Contratos Completos ---
        $orden_trabajos_completas = Contratista::with(['proveedor', 'proyecto'])
            ->withTotals()        // 1. Añade las columnas calculadas
            ->completos()         // 2. Filtra por los completos
            ->orderBy('fecha', 'asc')
            ->paginate(15, ['*'], 'completas_page');

        $route_params = ['orden_trabajos_pendientes' => $orden_trabajos_pendientes, 'orden_trabajos_completas' => $orden_trabajos_completas, 'breadcrumbs' => $breadcrumbs, 'title_page' => $title_page];
        return view('administrativo.contratista.index', $route_params);
    }

    public function editarContratista(Contratista $contratista)
    {
        $title_page = 'Editar Contratista';

        $breadcrumbs = [
            ['name' => 'Inicio', 'url' => route('home')],
            ['name' => 'Contratistas', 'url' => route('administrativo.index.contratistas')],
            ['name' => 'Editar', 'url' => '']
        ];

        $orden_trabajo = $contratista;
        $proyecto = Proyecto::findOrFail($contratista->proyecto_id);
        $subproyectos = $proyecto->subproyectos_unicos;
        $unidades_medidas = CatalogoDato::getChildrenCatalogo('unidades.medida');
        $articulos = Articulo::where('activo', true)->pluck('descripcion', 'id');
        $subTotal = $orden_trabajo->detalle_contratistas->sum(function ($detalle) {
            $valor_unitario = calcularProcentaje($detalle->valor_unitario, $detalle->costo_indirecto, 4);
            return $valor_unitario * $detalle->cantidad;
        });
        $totalGeneral = $orden_trabajo->numero_casas * $subTotal;
        $administrativo = true;

        return view('administrativo.contratista.edit', compact('orden_trabajo', 'subproyectos', 'title_page', 'breadcrumbs', 'articulos', 'unidades_medidas', 'administrativo', 'subTotal', 'totalGeneral'));
    }

    public function actualizarContratista(Request $request, Contratista $contratista)
    {
        try {
            DB::beginTransaction();

            $items = array_map(function ($producto, $cantidad, $precio_unitario, $costo_indirecto, $unidad_medida) {
                return [
                    'producto' => $producto,
                    'cantidad' => str_replace(',', '', $cantidad),
                    'valor_unitario' => limpiarValor($precio_unitario),
                    'costo_indirecto' => $costo_indirecto,
                    'unidad_medida' => is_numeric($unidad_medida) ? $unidad_medida : registrarUnidadMedida($unidad_medida)
                ];
            }, $request->input('productos', []), $request->input('cantidad', []), $request->input('precio_unitario', []), $request->input('costo_indirecto', []), $request->input('unidad_medida', []));

            foreach ($items as $item) {
                DetalleContratista::updateOrCreate(
                    ['contratista_id' => $contratista->id, 'articulo_id' => $item['producto']],
                    [
                        'cantidad' => $item['cantidad'],
                        'unidad_medida_id' => $item['unidad_medida'],
                        'valor_unitario' => $item['valor_unitario'],
                        'costo_indirecto' => $item['costo_indirecto'],
                    ]
                );

                // registrar precio unitario del producto y el iva
                $articulo = Articulo::find($item['producto']);
                $articulo->valor_unitario = $item['valor_unitario'];
                $articulo->save();
            }

            DB::commit();
            LogService::log('info', 'Actualizacion la informacion del contratista #' . $contratista->id, ['user_id' => auth()->id(), 'action' => 'update']);
            return redirect()->route('administrativo.contratista.editar', $contratista->id)->with('success', 'Se actualizó la información del contratista con éxito.');
        } catch (Throwable $e) {
            DB::rollBack();
            LogService::log('error', 'Error al actualizar la inforacion del contratista #' . $contratista->id, ['user_id' => auth()->id(), 'action' => 'update', 'message' => $e->getMessage()]);
            return redirect()->route('administrativo.contratista.editar', $contratista->id)->with('error', 'Ocurrió un error inesperado, comuníquese con el administrador del sistema.');
        }
    }

    public function detalleContratistas(Contratista $contratista)
    {
        $title_page = 'Detalle Orden de trabajo';

        $breadcrumbs = [
            ['name' => 'Inicio', 'url' => route('home')],
            ['name' => 'Contratistas', 'url' => route('administrativo.index.contratistas')],
            ['name' => 'Detalle', 'url' => '']
        ];

        $detalle_pagos = PagoOrdenTrabajoContratista::where('contratista_id', $contratista->id)->orderBy('fecha', 'desc')->paginate(15);

        $route_params = ['contratista' => $contratista, 'detalle_pagos' => $detalle_pagos, 'breadcrumbs' => $breadcrumbs, 'title_page' => $title_page];
        return view('administrativo.contratista.detalle_pagos', $route_params);
    }

    public function pagoOrdenTrabajo(Request $request, PagoOrdenTrabajoContratista $pago)
    {
        if ($request->ajax()) {
            try {
                DB::beginTransaction();
                $pago->pagado = true;

                if ($pago->save()) {
                    $contratista = Contratista::find($pago->contratista_id);
                    $total_pagado = $pago::where('contratista_id', $pago->contratista_id)->where('pagado', true)->sum('valor');

                    if ($contratista->total_contratistas == $total_pagado) {
                        $estado_id = CatalogoDato::getIdCatalogo('estados.contratistas.pagado');
                        $contratista->estado_id = $estado_id;
                        $contratista->save();
                    }
                    DB::commit();
                    LogService::log('info', 'Pago de contratista actualizado', ['user_id' => auth()->id(), 'action' => 'update']);
                    PushNotificationService::sendNotification(PushNotificationsEnum::ADMINISTRATIVO, 'Pago orden de trabajo', 'El usuario ' . Auth::user()->nombre . ' genero pago a contratista ' . $contratista->proveedor->razon_social, route('pdf.pago.orden.trabajo', $pago->id));
                    return response()->json(['success' => true, 'message' => 'Pago registrado con éxito.']);
                } else {
                    DB::rollBack();
                    LogService::log('error', 'Error al actualizar el pago del contratista', ['user_id' => auth()->id(), 'action' => 'update']);
                    return response()->json(['success' => false, 'message' => 'Opps, ocurrió un error al intentar registrar el pago.']);
                }
            } catch (Throwable $e) {
                DB::rollBack();
                LogService::log('error', 'Error al actualizar el pago', ['user_id' => auth()->id(), 'action' => 'update', 'message' => $e->getMessage()]);
                return response()->json(['success' => false, 'message' => 'Error al intentar registrar el pago']);
            }
        }
    }

    public function buscarOrdenTrabajo(Request $request)
    {
        if ($request->ajax()) {
            $output = "";
            $buscar = $request->buscar;
            $tipo = $request->tipo;
            $query = Contratista::with(['proveedor', 'proyecto'])
                ->withTotals();

            if ($tipo === 'proceso') {
                $query->pendientes();
            } elseif ($tipo === 'pagado') {
                $query->completos();
            }

            $query->when($buscar, function ($q, $busqueda) {
                $q->where(function ($subQuery) use ($busqueda) {
                    // Búsqueda por ID formateado (Ej: 20231128-001)
                    $subQuery->whereRaw("CONCAT(DATE_FORMAT(fecha, '%Y%m%d'), '-', LPAD(id, 3, '0')) LIKE ?", ['%' . $busqueda . '%'])
                        // Búsqueda por fecha
                        ->orWhere('fecha', 'LIKE', '%' . $busqueda . '%')
                        // Búsqueda en la relación del proveedor
                        ->orWhereHas('proveedor', function ($proveedorQuery) use ($busqueda) {
                            $proveedorQuery->where('razon_social', 'LIKE', '%' . $busqueda . '%');
                        });
                });
            });

            // 4. Aplicar el ordenamiento.
            $query->orderBy('fecha', 'asc');
            $orden_trabajos = $query->get();

            foreach ($orden_trabajos as $orden_trabajo) {
                $editar = "<a href='" . route('administrativo.contratista.editar', $orden_trabajo->id) . "' class='dropdown-item'>Editar</a>";
                $pagos = "<a href='" . route('administrativo.contratista.detalle', $orden_trabajo->id) . "' class='dropdown-item'>Pagos</a>";
                $pdf = "<a href='" . route('pdf.orden.trabajo.contratista', $orden_trabajo->id) . "' class='dropdown-item' target='_blank'>PDF orden trabajo</a>";

                $output .= '<tr id="' . $orden_trabajo->id . '" class="' . ($orden_trabajo->pagosOrdenTrabajoContratista->contains('pagado', false) ? 'table-warning' : 'clase-no-existe') . '">' .
                    '<td class="align-middle">' . numeroOrden($orden_trabajo, false) . '</td>' .
                    '<td class="align-middle text-uppercase">' . $orden_trabajo->proveedor->razon_social . '</td>' .
                    '<td class="align-middle text-uppercase">' . $orden_trabajo->articulo->descripcion . '</td>' .
                    '<td class="align-middle">$' . number_format($orden_trabajo->total_contratistas, 2) . '</td>' .
                    '<td class="align-middle">$' . number_format($orden_trabajo->pagos_contratistas, 2) . '</td>' .
                    '<td class="align-middle">$ ' . number_format(($orden_trabajo->total_contratistas - $orden_trabajo->pagos_contratistas), 2) . '</td>' .
                    '<td class="align-middle align-middle text-right text-truncate">' .
                    '<button type="button" class="btn btn-outline-dark" data-container="body" data-toggle="popover" data-placement="left" data-trigger="focus" data-content ="' . $editar . $pagos . $pdf . ' "> <i class="fas fa-caret-left font-weight-normal"></i> Opciones </button>' .
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

    /** Mano de Obra */
    public function indexManoObra()
    {
        $title_page = 'Mano de Obra';

        $breadcrumbs = [
            ['name' => 'Inicio', 'url' => route('home')],
            ['name' => 'Administrativo', 'url' => route('administrativo.index')],
            ['name' => 'Mano Obra', 'url' => '']
        ];

        $mano_obra_pendientes = ManoObra::whereDoesntHave('pago_mano_obra', function ($query) {
            $query->whereNotNull('mano_obra_id'); // Validar que no exista un registro relacionado
        })->orderBy('fecha_inicio', 'desc')->paginate(15);

        $mano_obra_completos = ManoObra::whereHas('pago_mano_obra', function ($query) {
            $query->whereNotNull('mano_obra_id'); // Validar que exista un registro relacionado
        })->orderBy('fecha_inicio', 'desc')->paginate(15);

        $route_params = ['mano_obra_pendientes' => $mano_obra_pendientes, 'mano_obra_completos' => $mano_obra_completos, 'breadcrumbs' => $breadcrumbs, 'title_page' => $title_page];
        return view('administrativo.mano_obra.index', $route_params);
    }

    public function detalleManoObra(ManoObra $mano_obra, $estado)
    {
        $title_page = 'Mano de Obra - Detalle';

        $breadcrumbs = [
            ['name' => 'Inicio', 'url' => route('home')],
            ['name' => 'mano de obra', 'url' => route('administrativo.index.mano.obra')],
            ['name' => 'detalle', 'url' => '']
        ];

        $detalle_mano_obra =  $mano_obra->getDetalleManoObraGroupTrabajador($mano_obra->id, $estado);


        $route_params = ['mano_obra' => $mano_obra, 'detalle_mano_obra' => $detalle_mano_obra, 'breadcrumbs' => $breadcrumbs, 'title_page' => $title_page, 'tipo' => $estado];

        return view('administrativo.mano_obra.detalle', $route_params);
    }

    public function registrarPagoManoObra(Request $request)
    {
        // Decodificar el array de `pago_ids`
        $pagoIds = json_decode($request->input('pago_ids'), true);
        $mano_obra_id = $request->mano_obra;

        try {

            DB::beginTransaction();
            // Insertar registros en la tabla `pago_mano_obras`
            if (!empty($pagoIds)) {
                foreach ($pagoIds as $pagoId) {
                    PagoManoObra::create([
                        'pago_prestamo_id' => $pagoId,
                        'mano_obra_id' => $mano_obra_id,
                    ]);
                }
            } else {
                PagoManoObra::create([
                    'mano_obra_id' => $mano_obra_id,
                ]);
            }

            DB::commit();
            PushNotificationService::sendNotification(PushNotificationsEnum::ADMINISTRATIVO, 'Pago Mano de obra', 'El usuario ' . Auth::user()->nombre . ' genero pago para la mano de obra.', route('pago.mano.obra', ['mano_obra' => $mano_obra_id, 'pago' => true]));
            return redirect()->route('administrativo.index.mano.obra')->with('success', 'Pago de mano obra generada con éxito.');
        } catch (Throwable $e) {
            DB::rollBack();
            LogService::log('error', 'Error al crear pago mano obra', ['user_id' => auth()->id(), 'message' => $e->getMessage()]);
            return redirect()->route('administrativo.index.mano.obra')->with('error', 'Ocurrió un error inesperado, comuníquese con el administrador del sistema.');
        }
    }

    public function buscarManoObra(Request $request)
    {
        if ($request->ajax()) {
            $output = "";
            $buscar = $request->buscar;
            $tipo = $request->tipo;

            $query = ManoObra::with(['proyecto', 'etapa', 'tipo_etapa', 'pago_mano_obra'])
                ->where(function ($query) use ($buscar) {
                    $query->where('fecha_inicio', 'LIKE', '%' . $buscar . '%')
                        ->orWhere('fecha_fin', 'LIKE', '%' . $buscar . '%');
                });

            // Filtrar según el tipo
            if ($tipo == 'pendiente') {
                $query->whereDoesntHave('pago_mano_obra', function ($query) {
                    $query->whereNotNull('mano_obra_id');
                });
            } else if ($tipo == 'completo') {
                $query->whereHas('pago_mano_obra', function ($query) {
                    $query->whereNotNull('mano_obra_id');
                });
            }

            // Ejecutar la consulta
            $resultado = $query->orderBy('semana', 'asc')->get();

            foreach ($resultado as $mano_obra) {
                $output .= '<tr id="{{ $mano_obra->id }}">' .
                    '<td class="align-middle">' . $mano_obra->semana . '</td>' .
                    '<td class="align-middle">' . strtoupper($mano_obra->proyecto->nombre_proyecto) . '</td>' .
                    '<td class="align-middle">' . dateFormatHumansManoObra($mano_obra->fecha_inicio, $mano_obra->fecha_fin) . '</td>' .
                    '<td class="align-middle">' . $mano_obra->etapa->descripcion . '</td>' .
                    '<td class="align-middle">' . $mano_obra->proyecto->tipo_proyecto->descripcion . '</td>' .
                    '<td class="align-middle align-middle text-right text-truncate">' .
                    ' <a href="' . route('pago.mano.obra', ['mano_obra' => $mano_obra->id, 'pago' => true]) . '"
                            class="btn btn-outline-dark" target="__blank">
                            Detalle <i class="fas fa-caret-right font-weight-normal mx-2"></i>
                        </a>' .
                    '</a>' .
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

    public function subproyectosPorProyecto(Request $request)
    {
        if ($request->ajax()) {
            $proyecto_id = $request->proyecto_id;
            $subproyectos = Proyecto::find($proyecto_id)->subproyectos_unicos;

            return response()->json(['subproyectos' => $subproyectos]);
        }
    }
}