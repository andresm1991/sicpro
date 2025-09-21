<?php

namespace App\Http\Controllers\JPLimpieza;

use Exception;
use Throwable;
use App\Models\Proveedor;
use App\Models\CatalogoDato;
use App\Services\LogService;
use Illuminate\Http\Request;
use App\Models\JPLimpieza\Caja;
use Illuminate\Support\Facades\DB;
use App\Models\JPLimpieza\Producto;
use App\Models\JPLimpieza\Proyecto;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\JPLimpieza\Inventario;
use App\Models\JPLimpieza\Adquisicion;
use Illuminate\Support\Facades\Storage;
use App\Models\JPLimpieza\DetalleAdquisicion;
use App\Models\JPLimpieza\AdquisicionAdministrativo;

class AdquisicionController extends Controller
{

    private $path_files;

    public function __construct()
    {
        $environment = env('APP_ENV');

        if ($environment === 'production') {
            $this->path_files = 'adquisiciones';
        } else {
            $this->path_files = 'pruebas/jp_limpieza/adquisiciones';
        }
    }

    public function index(Request $request)
    {
        $breadcrumbs = [
            ['name' => 'Inicio', 'url' => route('home')],
            ['name' => 'Limpieza y mantenimiento', 'url' => route('jp.limpieza.index')],
            ['name' => 'Adquisiciones', 'url' => ''],
        ];
        $proyecto = $request->route('proyecto');

        $menu_adquisiciones = CatalogoDato::getChildrenCatalogo('proveedor')->reject(function ($item) {
            return $item->slug === 'profecionales'; // Reemplaza 123 con el ID del registro que deseas excluir
        })
            ->map(function ($item) use ($proyecto) {
                $routeName = match ($item->slug) {
                    'contratista' => route('jp.limpieza.contratistas.index', $proyecto),
                    'mano.obra' => route('jp.limpieza.mano.obra.index', $proyecto),
                    default => route('jp.limpieza.adquisiciones.tipo.adquisicion', ['proyecto' => $proyecto, 'tipo_adquisicion' => $item->slug]),
                };

                // Agregar la ruta al objeto
                $item->route = $routeName;

                return $item;
            });

        return view('jp_limpieza.adquisiciones.opciones', compact('breadcrumbs', 'menu_adquisiciones', 'proyecto'));
    }

    public function adquisiciones($proyecto, $tipo_adquisicion)
    {
        $tipoAdquisicion = CatalogoDato::where('slug', $tipo_adquisicion)->first();
        if (!$tipoAdquisicion) {
            abort(404);
        }

        $breadcrumbs = [
            ['name' => 'Inicio', 'url' => route('home')],
            ['name' => 'Adquisiciones', 'url' => route('jp.limpieza.adquisiciones.index', $proyecto)],
            ['name' => $tipoAdquisicion->descripcion, 'url' => ''],
        ];

        $adquisiciones = Adquisicion::where('tipo_id', $tipoAdquisicion->id)
            ->where('proyecto_id', $proyecto)
            ->where('administrativo', false)
            ->orderBy('created_at', 'desc')
            ->paginate(15);
        // $adquisiciones->setPath(route('jp.limpieza.adquisiciones.tipo.adquisicion', $tipo_adquisicion));

        return view('jp_limpieza.adquisiciones.adquisiciones', compact('breadcrumbs', 'proyecto', 'adquisiciones', 'tipoAdquisicion'));
    }

    public function create(Proyecto $proyecto, $tipo_adquisicion)
    {

        $tipoAdquisicion = CatalogoDato::where('slug', $tipo_adquisicion)->first();
        if (!$tipoAdquisicion) {
            abort(404);
        }

        $breadcrumbs = [
            ['name' => 'Inicio', 'url' => route('home')],
            ['name' => $tipoAdquisicion->descripcion, 'url' => route('jp.limpieza.adquisiciones.tipo.adquisicion', [$proyecto->id, $tipoAdquisicion->slug])],
            ['name' => 'Nuevo', 'url' => ''],
        ];
        $numero = numeroPedido(Adquisicion::latest()->first());
        $adquisicion = new Adquisicion();

        return view('jp_limpieza.adquisiciones.create', compact('breadcrumbs', 'proyecto', 'tipoAdquisicion', 'numero', 'adquisicion'));
    }

    public function store(Request $request)
    {
        try {
            $request->validate([
                'numero' => 'required|string|unique:mysql_jp_limpieza.adquisiciones,numero',
            ], [
                'numero.unique' => 'El número de adquisición ya existe. Por favor, ingrese un número diferente.',
            ]);

            DB::beginTransaction();

            $administrativo = $request->administrativo ?? false;
            $proyectoId = $request->proyecto;
            $tipo_adquisicion = $request->tipo_adquisicion;
            $proyecto = Proyecto::find($proyectoId);

            if (is_numeric($tipo_adquisicion)) {
                $tipoAdquisicion = CatalogoDato::find($tipo_adquisicion);
            } else {
                $tipoAdquisicion = CatalogoDato::where('slug', $tipo_adquisicion)->first();
            }

            if (!$tipoAdquisicion) {
                abort(404);
            }

            $proveedor = $request->input('proveedor');
            $numero = $request->input('numero');
            $fecha = $request->input('fecha');
            $completado = $request->input('orden_completa');
            $formaPago = $request->input('forma_pago');
            $factura = $request->input('factura');
            $archivo = $request->file('archivo');

            $items = array_map(function ($producto, $cantidad, $valor, $iva, $unidad_medida, $necesidad, $inventario) {
                $valoresLimpios = preg_replace('/[^0-9.]/', '', $valor); // Elimina $ y otros caracteres no numéricos

                return [
                    'producto' => is_numeric($producto) ? $producto : agregarProducto($producto, $valoresLimpios, $iva, $unidad_medida),
                    'cantidad' => str_replace(',', '', $cantidad),
                    'unidad_medida' => is_numeric($unidad_medida) ? $unidad_medida : registrarUnidadMedida($unidad_medida),
                    'valor' => $valoresLimpios,
                    'iva' => $iva,
                    'necesidad' => $necesidad,
                    'inventario' => $inventario,
                ];
            }, $request->producto, $request->cantidad, $request->precio, $request->iva, $request->unidad_medida, $request->necesidad, $request->input('inventario', []));

            $adquisicion = Adquisicion::create([
                'fecha' => $fecha,
                'numero' => $numero,
                'proyecto_id' => $proyecto ? $proyecto->id : null,
                'proveedor_id' => $proveedor,
                'tipo_id' => $tipoAdquisicion->id,
                'estado' => $completado ? 'completado' : 'pendiente',
                'nro_factura' => $factura,
                'archivo' => $archivo ? Storage::disk('digitalocean')->put($this->path_files, $archivo) : null,
                'forma_pago_id' => $formaPago,
                'administrativo' => $administrativo,
            ]);

            if ($adquisicion) {
                foreach ($items as $item) {
                    if (empty($item['producto']) || empty($item['cantidad']) || empty($item['valor'])) {
                        return redirect()->back()->with('error', 'Por favor, complete todos los campos requeridos.');
                    }

                    $cantidad = $item['cantidad'];
                    $valor = str_replace(',', '', $item['valor']);
                    $iva = $item['iva'];

                    DetalleAdquisicion::create([
                        'adquisicion_id' => $adquisicion->id,
                        'producto_id' => $item['producto'],
                        'cantidad' => $cantidad,
                        'unidad_medida_id' => $item['unidad_medida'],
                        'precio_unitario' => $valor,
                        'iva' => $iva,
                        'necesidad' => $item['necesidad'],
                    ]);

                    if (is_numeric($item['producto'])) {
                        // registrar precio unitario del producto y el iva
                        $articulo = Producto::find($item['producto']);
                        $articulo->precio_unitario = str_replace(',', '', $item['valor']);
                        $articulo->iva = $item['iva'];
                        $articulo->save();
                    }

                    if ($completado) {
                        if ($item['inventario']) {
                            Inventario::create([
                                'adquisicion_id' => $adquisicion->id,
                                'producto_id' => $item['producto'],
                                'cantidad' => str_replace(',', '', $item['cantidad']),
                                'fecha_ingreso' => date('Y-m-d'),
                                'usuario_id' => Auth::user()->id,
                                'estado' => 10,
                            ]);
                        }

                        // Registrar en caja si el pago esta completado y si fue pagado en efectivo
                        if ($adquisicion->formaPago->slug == 'forma.pago.contado') {
                            $request->merge([
                                'proveedor' => $proveedor,
                                'articulo' => $item['producto'],
                                'tipo_movimiento' => 'egreso',
                                'monto'        => calcularTotalProducto($cantidad, $valor, $iva),
                                'detalle'  =>  $item['necesidad'],
                                'referencia'   => $factura,
                                'origen_type' => 'adquisicion_jplimpieza',
                                'origen_id' => $adquisicion->id,
                            ]);

                            Caja::registrarMovimiento($request);
                        }
                    }
                }

                DB::commit();
                if (!$request->administrativo) {
                    return redirect()->route('jp.limpieza.adquisiciones.create', [$proyecto->id, $tipoAdquisicion->slug])->with('success', 'Adquisición creada exitosamente.');
                } else {
                    return redirect()->route('jp.limpieza.adquisiciones.administrativas.index')->with('success', 'Adquisición creada exitosamente.');
                }
            }

            throw new Exception('el proceso no se completó correctamente, por favor intente nuevamente.');
        } catch (Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    public function edit(Proyecto $proyecto, $tipo_adquisicion, Adquisicion $adquisicion)
    {
        $tipoAdquisicion = CatalogoDato::where('slug', $tipo_adquisicion)->first();
        if (!$tipoAdquisicion) {
            abort(404);
        }

        $breadcrumbs = [
            ['name' => 'Inicio', 'url' => route('home')],
            ['name' => $tipoAdquisicion->descripcion, 'url' => route('jp.limpieza.adquisiciones.tipo.adquisicion', [$proyecto->id, $tipoAdquisicion->slug])],
            ['name' => 'Editar', 'url' => ''],
        ];

        return view('jp_limpieza.adquisiciones.edit', compact('breadcrumbs', 'proyecto', 'tipoAdquisicion', 'adquisicion'));
    }

    public function update(Request $request)
    {
        $proyecto = Proyecto::find($request->proyecto);
        $adquisicion = Adquisicion::find($request->adquisicion);
        try {
            DB::beginTransaction();

            $tipo_adquisicion = $request->tipo_adquisicion;
            if (is_numeric($tipo_adquisicion)) {
                $tipoAdquisicion = CatalogoDato::find($request->tipo_adquisicion);
            } else {
                $tipoAdquisicion = CatalogoDato::where('slug', $request->tipo_adquisicion)->first();
            }


            if (!$tipoAdquisicion) {
                abort(404);
            }

            $proveedor = $request->input('proveedor');
            $completado = $request->input('orden_completa');
            $formaPago = $request->input('forma_pago');
            $factura = $request->input('factura');
            $archivo = $request->file('archivo');
            $productosExistente = DetalleAdquisicion::where('adquisicion_id', $adquisicion->id)->pluck('producto_id')->toArray();
            $productosEliminar = array_diff($productosExistente, $request->producto);

            $adquisicion->proveedor_id = $proveedor;
            $adquisicion->estado = $completado ? 'completado' : 'pendiente';
            $adquisicion->nro_factura = $factura;
            $adquisicion->forma_pago_id = $formaPago;

            if ($request->administrativo) {
                $adquisicion->proyecto_id = $request->proyecto;
            }

            $items = array_map(function ($producto, $cantidad, $valor, $iva, $unidad_medida, $necesidad, $inventario) {
                $valoresLimpios = preg_replace('/[^0-9.]/', '', $valor); // Elimina $ y otros caracteres no numéricos

                return [
                    'producto' => is_numeric($producto) ? $producto : agregarProducto($producto, $valoresLimpios, $iva, $unidad_medida),
                    'cantidad' => str_replace(',', '', $cantidad),
                    'unidad_medida' => is_numeric($unidad_medida) ? $unidad_medida : registrarUnidadMedida($unidad_medida),
                    'valor' => $valoresLimpios,
                    'iva' => $iva,
                    'necesidad' => $necesidad,
                    'inventario' => $inventario,
                ];
            }, $request->producto, $request->cantidad, $request->precio, $request->iva, $request->unidad_medida, $request->necesidad, $request->input('inventario', []));

            if ($archivo) {
                Storage::disk('digitalocean')->delete($adquisicion->archivo);
                $adquisicion->archivo = Storage::disk('digitalocean')->put($this->path_files, $archivo);
            }

            $adquisicion->save();

            foreach ($items as $index => $producto) {
                if (empty($producto['producto']) || empty($producto['cantidad']) || empty($producto['valor'])) {
                    return redirect()->back()->with('error', 'Por favor, complete todos los campos requeridos.');
                }

                $cantidad = str_replace(',', '', $producto['cantidad']);
                $valor = str_replace(',', '', $producto['valor']);
                $iva = $producto['iva'];

                DetalleAdquisicion::updateOrCreate(
                    ['adquisicion_id' => $adquisicion->id, 'producto_id' => $producto['producto']],
                    [
                        'cantidad' => $cantidad,
                        'unidad_medida_id' => $producto['unidad_medida'],
                        'precio_unitario' => $valor,
                        'iva' => $iva,
                        'necesidad' => $producto['necesidad'],
                    ]
                );

                if (is_numeric($producto['producto'])) {
                    // registrar precio unitario del producto y el iva
                    $articulo = Producto::find($producto['producto']);
                    $articulo->precio_unitario = str_replace(',', '', $producto['valor']);
                    $articulo->iva = $producto['iva'];
                    $articulo->save();
                }

                if ($completado) {

                    if ($producto['inventario']) {
                        Inventario::create([
                            'adquisicion_id' => $adquisicion->id,
                            'producto_id' => $producto['producto'],
                            'cantidad' => str_replace(',', '', $producto['cantidad']),
                            'fecha_ingreso' => date('Y-m-d'),
                            'usuario_id' => Auth::user()->id,
                            'estado' => 10,
                        ]);
                    }

                    if ($adquisicion->formaPago->slug == 'forma.pago.contado') {
                        $request->merge([
                            'proveedor' => $proveedor,
                            'articulo' =>  $producto['producto'],
                            'tipo_movimiento' => 'egreso',
                            'monto'        => calcularTotalProducto($cantidad, $valor, $iva),
                            'detalle'  =>  $producto['necesidad'],
                            'referencia'   => $factura,
                            'origen_type' => 'adquisicion_jplimpieza',
                            'origen_id' => $adquisicion->id,
                        ]);

                        Caja::registrarMovimiento($request);
                    }
                }
            }

            /// Eliminr los articulos que no estan en $array_productos
            if (!empty($productosEliminar)) {
                DetalleAdquisicion::where('adquisicion_id', $adquisicion->id)
                    ->whereIn('producto_id', $productosEliminar)->delete();
            }

            DB::commit();
            if (!$request->administrativo) {
                return redirect()->route('jp.limpieza.adquisiciones.edit', ['proyecto' => $proyecto->id, 'tipo_adquisicion' => $tipoAdquisicion->slug, 'adquisicion' => $adquisicion->id])->with('success', 'Adquisición actualizada exitosamente.');
            } else {
                return redirect()->route('jp.limpieza.adquisiciones.administrativas.edit', $adquisicion->id)->with('success', 'Adquisición actualizada exitosamente.');
            }
        } catch (Throwable $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        try {
            DB::beginTransaction();
            $adquisicion = Adquisicion::find($id);
            if ($adquisicion) {
                if ($adquisicion->archivo && Storage::disk('digitalocean')->exists($adquisicion->archivo)) {
                    Storage::disk('digitalocean')->delete($adquisicion->archivo);
                }
                $adquisicion->delete();
                LogService::log('info', 'Adquisición JPLimpieza eliminada', ['user_id' => auth()->id(), 'action' => 'destroy']);
                DB::commit();
                return response()->json(['success' => true, 'message' => 'Registro eliminado correctamente.']);
            }

            throw new Exception('el proceso no se completó correctamente, por favor intente nuevamente.');
        } catch (Throwable $e) {
            DB::rollBack();
            LogService::log('error', 'Adquisición JPLimpieza eliminada', ['message' => $e->getMessage(), 'action' => 'destroy', 'user_id' => auth()->id()]);
            return response()->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
        }
    }

    public function buscar(Request $request)
    {
        $buscar = $request->input('text');
        $proyecto = $request->route('proyecto');
        $tipoAdquisicion = CatalogoDato::where('slug', $request->tipo_adquisicion)->first();
        $output = '';

        // Paso 1: Obtener los IDs de los proveedores en la conexión 'mysql'
        $proveedorIds = Proveedor::where('razon_social', 'LIKE', "%$buscar%")
            ->pluck('id');
        // Obeter lps IDs de formas de pago
        $formaPagoIds = CatalogoDato::where('descripcion', 'LIKE', "%$buscar%")->pluck('id');

        // 1. Inicia la consulta
        $query = Adquisicion::query();

        // 2. Aplica filtros condicionales de forma limpia con when()
        $query->when($tipoAdquisicion, function ($q) use ($tipoAdquisicion) {
            return $q->where('tipo_id', $tipoAdquisicion->id);
        });

        // Tu lógica original (que es correcta para tu requisito)
        if ($proyecto) {
            // Si $proyecto existe (no es null, 0, false, ''),
            // busca las adquisiciones con ese ID de proyecto.
            $query->where('proyecto_id', $proyecto);
        } else {
            // Si $proyecto NO existe,
            // busca solo las adquisiciones SIN proyecto.
            $query->whereNull('proyecto_id');
        }

        // 3. Aplica el grupo de filtros solo si hay algo que buscar
        // Esto evita que el `where` falle si todas las entradas están vacías.
        $query->when($buscar || !empty($proveedorIds) || !empty($formaPagoIds), function ($q) use ($buscar, $proveedorIds, $formaPagoIds) {
            $q->where(function ($subQuery) use ($buscar, $proveedorIds, $formaPagoIds) {
                $subQuery->when($buscar, function ($sq) use ($buscar) {
                    return $sq->where('numero', 'LIKE', "%$buscar%");
                })
                    ->when(!empty($proveedorIds), function ($sq) use ($proveedorIds) {
                        return $sq->orWhereIn('proveedor_id', $proveedorIds);
                    })
                    ->when(!empty($formaPagoIds), function ($sq) use ($formaPagoIds) {
                        return $sq->orWhereIn('forma_pago_id', $formaPagoIds);
                    });
            });
        });


        // 4. Ordena y ejecuta la consulta, ASIGNANDO EL RESULTADO
        $adquisiciones = $query->orderBy('created_at', 'desc')->get();


        if ($adquisiciones) {
            foreach ($adquisiciones as $adquisicion) {

                if ($proyecto) {
                    $editar =  "<a href='" . route('jp.limpieza.adquisiciones.edit', ['proyecto' => $proyecto, 'tipo_adquisicion' => $tipoAdquisicion->slug, 'adquisicion' => $adquisicion->id]) . "' class='dropdown-item'>Editar</a>";
                } else {
                    $editar =  "<a href='" . route('jp.limpieza.adquisiciones.administrativas.edit', $adquisicion->id) . "' class='dropdown-item'>Editar</a>";
                }

                $eliminar = "<a href='#' class='dropdown-item eliminar-adquisicion' id='" . $adquisicion->id . "'>Eliminar</a>";
                $pdf = "<a href='" . route('pdf.recepcion', $adquisicion->id) . "' class='dropdown-item' target='_blank'>Generar PDF</a>";

                if (preg_match('/\b(completado|finalizado)\b/i', $adquisicion->estado)) {
                    $estado = '<span class="badge badge-success ">Finalizado</span>';
                } else {
                    $estado = '<span class="badge badge-warning ">' . $adquisicion->estado . '</span>';
                }

                $output .= '<tr id="' . $adquisicion->id . '">';
                $output .= '<td class="align-middle">' . $adquisicion->numero . '</td>';
                $output .= '<td class="align-middle">' . date('d-m-Y', strtotime($adquisicion->fecha)) . '</td>';
                $output .= '<td class="align-middle">' . $adquisicion->formaPago->descripcion . '</td>';
                $output .= '<td class="align-middle">' . $adquisicion->proveedor->razon_social . '</td>';
                $output .= '<td class="align-middle">' . $estado . '</td>';
                $output .= '<td class="align-middle text-right text-truncate">';
                $output .= '<button type="button" class="btn btn-outline-dark" data-container="body" data-toggle="popover" data-placement="left" data-trigger="focus" data-content ="' . $editar . $eliminar . $pdf . '"> <i class="fas fa-caret-left font-weight-normal"></i> Opciones </button>';
                $output .= '</td>';
                $output .= '</tr>';
            }

            if (empty($output)) {
                $output .= '<tr>' .
                    '<td colspan="6" class="text-center">' .
                    '<span class="text-danger">No existen datos para mostrar.</span>' .
                    '</td>' .
                    '</tr>';
            }
            return Response($output);
        }
    }


    /**
     * Adquisiciones Administrativas
     */
    public function indexAdministrativo()
    {
        $breadcrumbs = [
            ['name' => 'Inicio', 'url' => route('home')],
            ['name' => 'Limpieza y mantenimiento', 'url' => route('jp.limpieza.index')],
            ['name' => 'Adquisiciones administrativas', 'url' => ''],
        ];

        $adquisiciones = Adquisicion::where('administrativo', true)->orderBy('created_at', 'desc')->paginate('15');
        $administrativo = true;

        return view('jp_limpieza.adquisiciones.adquisiciones', compact('breadcrumbs', 'adquisiciones', 'administrativo'));
    }

    public function createAdministrativo()
    {
        $breadcrumbs = [
            ['name' => 'Inicio', 'url' => route('home')],
            ['name' => 'Adquisiciones administrativas', 'url' => route('jp.limpieza.adquisiciones.administrativas.index')],
            ['name' => 'Nuevo', 'url' => ''],
        ];

        $numero = numeroPedido(Adquisicion::latest()->first());
        $tipoAdquisiciones = CatalogoDato::getChildrenCatalogo('proveedor')->pluck('descripcion', 'id')->prepend("", "");
        $proyectos = Proyecto::orderBy('nombre_proyecto')->pluck('nombre_proyecto', 'id')->prepend("", "");

        $adquisicion = new Adquisicion();
        return view('jp_limpieza.adquisiciones.create', compact('breadcrumbs', 'tipoAdquisiciones', 'numero', 'adquisicion', 'proyectos'));
    }

    public function editAdministrativo(Adquisicion $adquisicion)
    {

        $breadcrumbs = [
            ['name' => 'Inicio', 'url' => route('home')],
            ['name' => 'Adquisiciones administrativas', 'url' => route('jp.limpieza.adquisiciones.administrativas.index')],
            ['name' => 'Editar', 'url' => ''],
        ];

        $tipoAdquisiciones = CatalogoDato::getChildrenCatalogo('proveedor')->pluck('descripcion', 'id')->prepend("", "");
        $proyectos = Proyecto::orderBy('nombre_proyecto')->pluck('nombre_proyecto', 'id')->prepend("", "");

        return view('jp_limpieza.adquisiciones.edit', compact('breadcrumbs', 'tipoAdquisiciones', 'adquisicion', 'proyectos'));
    }
}
