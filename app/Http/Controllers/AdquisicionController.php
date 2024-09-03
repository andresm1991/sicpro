<?php

namespace App\Http\Controllers;

use Exception;
use Throwable;
use App\Models\Articulo;
use App\Models\Proyecto;
use App\Models\Adquisicion;
use App\Models\CatalogoDato;
use Illuminate\Http\Request;
use App\Models\AdquisicionDetalle;
use App\Models\OrdenRecepcion;
use App\Models\Proveedor;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use PDF;

class AdquisicionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function  index($tipo, $tipo_id, Proyecto $proyecto)
    {
        $title_page = $proyecto->nombre_proyecto . ' - Aquisiciones';
        $back_route = route('proyecto.view', ['tipo' => $tipo, 'tipo_id' => $tipo_id, 'proyecto' => $proyecto->id]);
        $menu_adquisiciones = CatalogoDato::getChildrenCatalogo('menu.adquisiciones');
        return view('adquisiciones.index', compact('menu_adquisiciones', 'title_page', 'back_route', 'tipo', 'tipo_id', 'proyecto'));
    }

    public function tipoAquisicion($tipo, $tipo_id, Proyecto $proyecto, CatalogoDato $tipo_adquisicion)
    {
        $title_page = 'Aquisiciones - ' . $tipo_adquisicion->descripcion;
        $back_route = route('proyecto.adquisiciones.menu', ['tipo' => $tipo, 'tipo_id' => $tipo_id, 'proyecto' => $proyecto->id]);
        $aquisiciones = CatalogoDato::getChildrenCatalogo('proveedor');
        return view('adquisiciones.tipo_adquisicion', compact('aquisiciones', 'tipo_adquisicion', 'title_page', 'back_route', 'tipo', 'tipo_id', 'proyecto'));
    }

    public function listTipoAquisicion($tipo, $tipo_id, Proyecto $proyecto, CatalogoDato $tipo_adquisicion, CatalogoDato $tipo_etapa)
    {
        $title_page = $tipo_adquisicion->descripcion . ' - ' . $tipo_etapa->descripcion;
        $back_route = route('proyecto.adquisiciones.tipo', ['tipo' => $tipo, 'tipo_id' => $tipo_id, 'proyecto' => $proyecto->id, 'tipo_adquisicion' => $tipo_adquisicion]);
        $aquisiciones = CatalogoDato::getChildrenCatalogo('proveedor');

        $list_pedidos = Adquisicion::where('proyecto_id', $proyecto->id)
            ->where('etapa_id', $tipo_adquisicion->id)
            ->where('tipo_etapa_id', $tipo_etapa->id)
            ->orderBy('fecha', 'asc')->paginate(15);

        return view('adquisiciones.list_pedidos', compact('list_pedidos', 'aquisiciones', 'tipo_adquisicion', 'title_page', 'back_route', 'tipo', 'tipo_id', 'proyecto', 'tipo_etapa'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create($tipo, $tipo_id, Proyecto $proyecto, CatalogoDato $tipo_adquisicion, CatalogoDato $tipo_etapa)
    {
        $title_page = $proyecto->nombre_proyecto;
        $back_route = route('proyecto.adquisiciones.tipo.etapa', ['tipo' => $tipo, 'tipo_id' => $tipo_id, 'proyecto' => $proyecto->id, 'tipo_adquisicion' => $tipo_adquisicion, 'tipo_etapa' => $tipo_etapa]);
        $aquisiciones = CatalogoDato::getChildrenCatalogo('proveedor');
        $orden_pedido = new Adquisicion();
        $numero_orden = date('Ymd') . '-' . str_pad((Adquisicion::all()->count() + 1), 3, '0', STR_PAD_LEFT);
        $productos = Articulo::where('activo', true)->orderBy('descripcion', 'asc')->pluck('descripcion', 'id');


        return view('adquisiciones.create',  compact('numero_orden', 'orden_pedido', 'productos', 'aquisiciones', 'tipo_adquisicion', 'title_page', 'back_route', 'tipo', 'tipo_id', 'proyecto', 'tipo_etapa'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, $tipo, $tipo_id, Proyecto $proyecto, CatalogoDato $tipo_adquisicion, CatalogoDato $tipo_etapa)
    {
        $fecha = date('Y-m-d');
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
                    $param_detalle_adquisicion = ['adquisicion_id' => $adquisicion->id,];
                    if (is_numeric($producto)) {
                        $param_detalle_adquisicion = array_merge($param_detalle_adquisicion, [
                            'articulo_id' => $producto,
                            'cantidad_solicitada' => $cantidad[$index],
                            'necesidad' => $necesidad[$index],
                        ]);
                    } else {
                        $nuevo_producto = $this->registrarNuevoProducto($tipo_etapa, $producto);
                        $param_detalle_adquisicion = array_merge($param_detalle_adquisicion, [
                            'articulo_id' => $nuevo_producto->id,
                            'cantidad_solicitada' => $cantidad[$index],
                            'necesidad' => $necesidad[$index],
                        ]);
                    }
                    AdquisicionDetalle::create($param_detalle_adquisicion);
                }
                DB::commit();
                return redirect()->route('proyecto.adquisiciones.tipo.create', ['tipo' => $tipo, 'tipo_id' => $tipo_id, 'proyecto' => $proyecto->id, 'tipo_adquisicion' => $tipo_adquisicion, 'tipo_etapa' => $tipo_etapa])->with('success', 'Orden de pedido generada con éxito.');
            } else {
                DB::rollback();
                return redirect()->route('proyecto.adquisiciones.tipo.create', ['tipo' => $tipo, 'tipo_id' => $tipo_id, 'proyecto' => $proyecto->id, 'tipo_adquisicion' => $tipo_adquisicion, 'tipo_etapa' => $tipo_etapa])->with('error', 'Ocurrió un error al generar el pedido, por favor vuela a intentarlo. Si el problema persiste, comuníquese con el administrador del sistema.');
            }
        } catch (Throwable $e) {
            DB::rollBack();
            return redirect()->route('proyecto.adquisiciones.tipo.create', ['tipo' => $tipo, 'tipo_id' => $tipo_id, 'proyecto' => $proyecto->id, 'tipo_adquisicion' => $tipo_adquisicion, 'tipo_etapa' => $tipo_etapa])->with('error', 'Ocurrió un error inesperado, comuníquese con el administrador del sistema.');
        }
    }

    public function ordenRecepcion(Request $request)
    {
        $params = $this->getRouteParameters($request);
        $title_page = $params['proyecto']->nombre_proyecto . ' - Orden de Recepción';
        $back_route = route('proyecto.adquisiciones.tipo.etapa', $params);
        $proveedores = Proveedor::pluck('razon_social', 'id');
        $forma_pagos = CatalogoDato::getChildrenCatalogo('formas.pagos')->pluck('descripcion', 'id');
        $pedido = Adquisicion::find($request->route('pedido'));
        $orden = OrdenRecepcion::where('adquisicion_id', $pedido->id)->first();
        $params = array_merge($params, ['pedido' => $pedido, 'orden_recepcion' => $orden, 'proveedores' => $proveedores, 'forma_pagos' => $forma_pagos, 'title_page' => $title_page, 'back_route' => $back_route]);

        return view('adquisiciones.orden_recepcion', $params);
    }

    public function storeOrdenRecepcion(Request $request)
    {
        $request->validate(
            ['proveedor' => 'required|numeric', 'pedido' => 'required|numeric', 'cantidad_recibida' => 'required|numeric', 'forma_pago' => 'required'],
            ['proveedor.required' => 'Selecione el proveedor.', 'cantidad_recibida.required' => 'Ingrese un valor.', 'cantidad_recibida.numeric' => 'El valor debe ser numerico.', 'forma_pago' => 'Seleccione la forma de pago.'],
        );
        $routeParametres = $this->getRouteParameters($request);
        $routeParametres = array_merge($routeParametres, ['pedido' => $request->pedido]);
        $param = [
            'fecha' => date('Y-m-d'),
            'adquisicion_id' => $request->pedido,
            'proveedor_id' => $request->proveedor,
            'forma_pago_id' => $request->forma_pago,
        ];

        try {
            DB::beginTransaction();
            if (OrdenRecepcion::create($param)) {
                /// Actualiza el estado del pedido
                Adquisicion::find($request->pedido)->update(['estado' => 'Finalizado']);
                // Actualiza la cantidad recibiba en el detalle del pedido
                $update_detalle_pedido = AdquisicionDetalle::where('adquisicion_id', $request->pedido)->update(['cantidad_recibida' => $request->cantidad_recibida]);
                // Si todo esta correcto retorna a la vista de los pedidos con el mensaje de ok
                if ($update_detalle_pedido) {
                    DB::commit();
                    return redirect()->route('proyecto.adquisiciones.orden.recepcion', $routeParametres)->with('success', 'Orden de recepción generada con éxito.');
                } else {
                    throw new Exception('Error al intentar guardar la orden de recepcion origen al intentar actualizar la cantidad recibida.');
                }
            } else {
                throw new Exception('Error al intentar guardar la orden de recepcion');
            }
        } catch (Throwable $e) {
            DB::rollBack();
            return redirect()->route('proyecto.adquisiciones.orden.recepcion', $routeParametres)->with('error', 'Ocurrió un error inesperado, comuníquese con el administrador del sistema.');
        }
        return $request->all();
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

    private function registrarNuevoProducto($tipo, $descripcion)
    {
        $tipo_producto = $tipo->slug == 'meteriales.herramientas' ? 'tipo.adquisiciones.bienes' : 'tipo.adquisiciones.servicios';
        $categoria = CatalogoDato::where('slug', $tipo_producto)->first();
        $type = $tipo->slug == 'meteriales.herramientas' ? 'B-' : 'S-';
        $code = generateProductCode($type);
        $create =  Articulo::create(['categoria_id' => $categoria->id, 'codigo' => $code, 'descripcion' => $descripcion, 'activo' => true]);

        return $create;
    }

    // Método para obtener los parámetros comunes
    private function getRouteParameters($request)
    {
        return [
            'tipo' => $request->route('tipo'),
            'tipo_id' => $request->route('tipo_id'),
            'proyecto' => Proyecto::find($request->route('proyecto')),
            'tipo_adquisicion' => CatalogoDato::find($request->route('tipo_adquisicion')),
            'tipo_etapa' => CatalogoDato::find($request->route('tipo_etapa')),
        ];
    }
}
