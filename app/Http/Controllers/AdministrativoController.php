<?php

namespace App\Http\Controllers;

use App\Http\Requests\RecepcionAdquisicionAdministrativoRequest;
use App\Models\Adquisicion;
use App\Models\AdquisicionDetalle;
use App\Models\Articulo;
use App\Models\CatalogoDato;
use App\Models\Inventario;
use App\Models\OrdenRecepcion;
use App\Models\Proveedor;
use App\Models\Proyecto;
use App\Services\LogService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Throwable;

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

    public function menuConstruccion () {
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
        }else{
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

        if ($tipo == 'operativo') {
           

            $totalGeneral = $adquisicion->adquisiciones_detalle->sum(function ($detalle) {
                return $detalle->cantidad_recibida * $detalle->valor;
            });

            return view('administrativo.adquisiciones.edit', compact('adquisicion', 'tipo', 'totalGeneral', 'title_page', 'breadcrumbs'));
        } else {
            if (isset($adquisicion->orden_recepcion) && $adquisicion->orden_recepcion->completado) {
                return redirect()->route('administrativo.adquisiciones', $tipo)->with('toast_error', 'El pedido ya fue recibido y completado, no puede ser modificado.');
            }
            $proyectos = Proyecto::orderBy('nombre_proyecto', 'desc')->pluck('nombre_proyecto', 'id');
            $etapa = CatalogoDato::getChildrenCatalogo('menu.adquisiciones')->pluck('descripcion', 'id');
            $actividad = CatalogoDato::getChildrenCatalogo('proveedor')->pluck('descripcion', 'id');
            $productos = Articulo::where('activo', true)->orderBy('descripcion', 'asc')->pluck('descripcion', 'id');

            $orden_pedido = $adquisicion;
            return view('administrativo.adquisiciones.edit_administrativo', compact('orden_pedido', 'tipo', 'proyectos', 'etapa', 'actividad', 'productos', 'title_page', 'breadcrumbs'));
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
     * Funcion que actualiza solo los valores de las aquisicones operativas
     */
    private function actualizarAdquisicionOperativa(Request $request, $tipo, Adquisicion $adquisicion)
    {
        // Limpia el símbolo de dólar de cada elemento en el arreglo 'valor'
        $valoresLimpios = array_map(function ($value) {
            return preg_replace('/[^0-9.]/', '', $value); // Elimina $ y otros caracteres no numéricos
        }, $request->input('valor', []));

        // Reemplaza los valores en el request con los valores limpios
        $request->merge(['valor' => $valoresLimpios]);

        $rules = [
            'unidad_medida' => 'required|array',
            'unidad_medida.*' => 'required',
            'valor' => 'required|array',
            'valor.*' => 'required|numeric',
            'iva_producto' => 'required|array',
            'iva_producto.*' => 'required|numeric',
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
            'iva_producto.required' => 'Ingrese el valor.',
            'iva_producto.*.required' => 'Ingrese el valor .',
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
        $array_unidad_medida = $request->unidad_medida;
        $array_valor_unidatrio = $request->valor;
        $array_iva_producto = $request->iva_producto;
        $unidadMedidaCase = "CASE";
        $valorCase = "CASE";
        $ids = [];
        try {
            foreach ($adquisicion->adquisiciones_detalle as $index => $detalle) {
                if (!is_numeric($array_unidad_medida[$index])) {
                    $id = registrarUnidadMedida($array_unidad_medida[$index]);
                } else {
                    $id = $array_unidad_medida[$index];
                }
                $valor = quitarSimboloUSD($array_valor_unidatrio[$index]);

                $unidadMedidaCase .= " WHEN id = {$detalle->id} THEN '{$id}'";
                $valorCase .= " WHEN id = {$detalle->id} THEN {$valor}";
                $ids[] = $detalle->id;

                // registrar precio unitario del producto y el iva
                $articulo = Articulo::find($detalle->articulo_id);
                $articulo->valor_unitario = $array_valor_unidatrio[$index];
                $articulo->iva = $array_iva_producto[$index];
                $articulo->save();
            }

            $unidadMedidaCase .= " END";
            $valorCase .= " END";

            DB::beginTransaction();
            DB::table('adquisiciones_detalle')
                ->whereIn('id', $ids)
                ->update([
                    'unidad_medida_id' => DB::raw($unidadMedidaCase),
                    'valor' => DB::raw($valorCase)
                ]);
            DB::commit();

            // Actualizar el estado a completado 
            if ($estado) {    
                $adquisicion->estado = "Completado";
                $adquisicion->save();
                
            }

            LogService::log('info', 'Actualizacion la informacion de la adquisicion #' . $adquisicion->id, ['user_id' => auth()->id(), 'action' => 'update']);

            return redirect()->route('administrativo.adquisicion.edit', ['tipo' => $tipo, 'adquisicion' => $adquisicion->id])->with('success', 'Se actualizó la información de la adquisición con éxito.');
        } catch (Throwable $e) {
            DB::rollBack();
            LogService::log('error', 'Error al actualizar la inforacion de la adquisicion #' . $adquisicion->id, ['user_id' => auth()->id(), 'action' => 'update', 'message' => $e->getMessage()]);
            return redirect()->route('administrativo.adquisicion.edit', ['tipo' => $tipo, 'adquisicion' => $adquisicion->id])->with('error', 'Ocurrió un error inesperado, comuníquese con el administrador del sistema.');
        }
    }

    private function actualizarAdquisicionAdministrativa(Request $request, $tipo, Adquisicion $adquisicion)
    {
        try {
            $pedido_id = $adquisicion->id;
            // Combinar arrays en uno solo
            $result = array_map(function ($producto, $cantidad, $necesidad) {
                return [
                    'articulo_id' => $producto,
                    'cantidad_solicitada' => str_replace(',', '', $cantidad),
                    'necesidad' => $necesidad
                ];
            }, $request->productos, $request->cantidad, $request->necesidad);

            DB::beginTransaction();
            foreach ($result as $data) {
                AdquisicionDetalle::updateOrCreate(
                    [
                        'articulo_id' => $data['articulo_id'],
                        'adquisicion_id' => $adquisicion->id
                    ],
                    [
                        'cantidad_solicitada' => $data['cantidad_solicitada'],
                        'necesidad' => $data['necesidad']
                    ]
                );

                agregarPalabra($data['necesidad']);
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
            $iva = ($detalle->valor * $detalle->producto->iva) / 100;
            return ($detalle->cantidad_recibida * $detalle->valor) + $iva;
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
            if ($orden_recepcion = OrdenRecepcion::create($param)) {
                /// Actualiza el estado del pedido
                if ($orden_completa) {
                    $adquisicion->estado = 'Completado';
                    $adquisicion->save();
                }
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
}
