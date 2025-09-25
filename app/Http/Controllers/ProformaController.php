<?php

namespace App\Http\Controllers;

use App\Models\CatalogoDato;
use Illuminate\Http\Request;
use App\Models\ProformaPlano;
use Illuminate\Support\Facades\DB;
use App\Models\ProformaAdecentamiento;
use App\Services\LogService;

class ProformaController extends Controller
{
    public function index($tipo)
    {
        $title_page = $tipo === 'adecentamientos' ? 'Adecentamientos' : 'Diseño de Planos';
        $breadcrumbs = [
            ['name' => 'Inicio', 'url' => route('home')],
            ['name' => 'Proformas', 'url' => route('proformas.index')],
            ['name' => $title_page, 'url' => '']
        ];

        if ($tipo === 'adecentamientos') {
            $proformas = ProformaAdecentamiento::with(['cliente', 'estado'])->orderBy('created_at', 'desc')->paginate(15);
        } else {
            $proformas = ProformaPlano::with(['cliente', 'estado'])->orderBy('created_at', 'desc')->paginate(15);
        }

        return view('proformas.list', compact('title_page', 'breadcrumbs', 'proformas', 'tipo'));
    }


    public function create($tipo)
    {
        $title_page = 'Nueva Proforma';
        $breadcrumbs = [
            ['name' => 'Inicio', 'url' => route('home')],
            ['name' => 'Proformas', 'url' => route('proformas.tipo', $tipo)],
            ['name' => $title_page, 'url' => '']
        ];

        if ($tipo === 'adecentamientos') {
            $proforma = new ProformaAdecentamiento();
            $numero = ProformaAdecentamiento::max('id') + 1;
        } else {
            $proforma = new ProformaPlano();
            $numero = ProformaPlano::max('id') + 1; // Asumiendo que tienes un modelo ProformaPlano
        }

        return view('proformas.create', compact('title_page', 'breadcrumbs', 'tipo', 'proforma', 'numero'));
    }

    public function storeAdecentamiento(Request $request)
    {
        try {
            DB::beginTransaction();
            $numero = $request->input('numero', numeroProforma(ProformaAdecentamiento::max('id') + 1));
            $fecha = $request->input('fecha');
            $clienteId = $request->input('cliente');
            $porcentaje_iva = $request->input('porcentaje_iva', 0);
            $descuento = $request->input('descuento', 0);
            $nota = $request->input('nota');
            $validez = $request->input('validez');
            $formaPago = $request->input('forma_pago');
            $plazoEntrega = $request->input('plazo_entrega');
            $observaciones = $request->input('observaciones');
            $estado = $request->input('estado');
            $descripcion = $request->input('descripcion');

            $items = array_map(function ($producto, $cantidad, $precio_unitario, $costo_indirecto) {
                return [
                    'producto' => is_numeric($producto) ? $producto : agregarProductoProforma($producto, $precio_unitario, 'adecentamientos'),
                    'cantidad' => str_replace(',', '', $cantidad),
                    'valor' => limpiarValor($precio_unitario),
                    'costo_indirecto' => $costo_indirecto,
                ];
            }, $request->input('producto', []), $request->input('cantidad', []), $request->input('precio', []), $request->input('indirecto', []));

            $subtotal = array_reduce($items, function ($carry, $item) {
                $precio_unitario = calcularProcentaje($item['valor'], $item['costo_indirecto']);
                return $carry + ($item['cantidad'] * $precio_unitario);
            }, 0);

            $proforma = ProformaAdecentamiento::create([
                'numero' => $numero,
                'fecha' => $fecha,
                'cliente_id' => $clienteId,
                'descripcion' => $descripcion,
                'observaciones' => $observaciones,
                'notas' => $nota,
                'estado_id' => $estado,
                'validez' => $validez,
                'forma_pago' => $formaPago,
                'plazo_entrega' => $plazoEntrega,
                'subtotal' => $subtotal,
                'descuento' => $descuento,
                'iva' => $porcentaje_iva,
                'total' => calcularTotalProforma($subtotal, $porcentaje_iva, $descuento),
            ]);

            if ($proforma) {
                foreach ($items as $item) {
                    $proforma->detalleAdecentamientos()->create([
                        'producto_id' => $item['producto'],
                        'cantidad' => $item['cantidad'],
                        'precio_unitario' => $item['valor'],
                        'costo_indirecto' => $item['costo_indirecto'],
                        'total' => $item['cantidad'] * $item['valor'],
                    ]);
                }
            }
            DB::commit();

            return redirect()->route('proformas.tipo', 'adecentamientos')->with('success', 'Proforma creada exitosamente.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Error al guardar la proforma: ' . $e->getMessage());
        }
    }

    public function storePlanos(Request $request)
    {
        try {
            DB::beginTransaction();
            $numero = $request->input('numero', numeroProforma(ProformaPlano::max('id') + 1));
            $fecha = $request->input('fecha');
            $clienteId = $request->input('cliente');
            $porcentaje_iva = $request->input('porcentaje_iva', 0);
            $descuento = $request->input('descuento', 0);
            $ubicacion = $request->input('ubicacion_lote');
            $area_lote = limpiarValor($request->input('area_lote'));
            $presupuesto = limpiarValor($request->input('presupuesto'));
            $observaciones = $request->input('observaciones');
            $estado = $request->input('estado');
            $descripcion = $request->input('descripcion');

            $incluye = $request->input('incluye');
            $formaPago = $request->input('forma_pago');
            $plazo_ejecucion = $request->input('plazo_ejecucion');
            $abono = $request->input('abono');


            $items = array_map(function ($producto, $area, $precio_unitario, $costo_indirecto) {
                return [
                    'producto' => is_numeric($producto) ? $producto : agregarProductoProforma($producto, $precio_unitario, 'planos'),
                    'area' => str_replace(',', '', $area),
                    'valor' => limpiarValor($precio_unitario),
                    'costo_indirecto' => $costo_indirecto,
                ];
            }, $request->input('producto', []), $request->input('cantidad', []), $request->input('precio', []), $request->input('indirecto', []));

            $subtotal = array_reduce($items, function ($carry, $item) {
                $precio_unitario = calcularProcentaje($item['valor'], $item['costo_indirecto']);
                return $carry + ($item['area'] * $precio_unitario);
            }, 0);

            $proforma = ProformaPlano::create([
                'numero' => $numero,
                'fecha' => $fecha,
                'cliente_id' => $clienteId,
                'descripcion' => $descripcion,
                'observaciones' => $observaciones,
                'ubicacion_lote' => $ubicacion,
                'area_lote' => $area_lote,
                'presupuesto' => $presupuesto,
                'incluye' => $incluye,
                'plazo_ejecucion' => $plazo_ejecucion,
                'estado_id' => $estado,
                'forma_pago' => $formaPago,
                'abono' => $abono,
                'subtotal' => $subtotal,
                'descuento' => $descuento,
                'iva' => $porcentaje_iva,
                'total' => calcularTotalProforma($subtotal, $porcentaje_iva, $descuento),
            ]);

            if ($proforma) {
                foreach ($items as $item) {
                    $proforma->detallePlanos()->create([
                        'producto_id' => $item['producto'],
                        'area' => $item['area'],
                        'precio_unitario' => $item['valor'],
                        'costo_indirecto' => $item['costo_indirecto'],
                        'total' => $item['area'] * $item['valor'],
                    ]);
                }
            }
            DB::commit();

            return redirect()->route('proformas.tipo', 'diseno_planos')->with('success', 'Proforma creada exitosamente.');
        } catch (\Exception $e) {
            return $e;
            DB::rollBack();
            LogService::log('error', 'Error al guardar la proforma: ' . $e->getMessage(), [
                'request' => $request->all(),
                'exception' => $e->getMessage(),
            ]);
            return redirect()->back()->with('error', 'Error al guardar la proforma: ' . $e->getMessage());
        }
    }

    public function edit($tipo, $id)
    {
        $title_page = 'Editar Proforma';
        $breadcrumbs = [
            ['name' => 'Inicio', 'url' => route('home')],
            ['name' => 'Proformas', 'url' => route('proformas.tipo', $tipo)],
            ['name' => $title_page, 'url' => '']
        ];

        if ($tipo === 'adecentamientos') {
            $proforma = ProformaAdecentamiento::with(['detalleAdecentamientos.producto'])->findOrFail($id);
        } else {
            $proforma = ProformaPlano::with(['detallePlanos.producto'])->findOrFail($id); // Asumiendo que tienes un modelo ProformaPlano
        }
        return view('proformas.edit', compact('title_page', 'breadcrumbs', 'tipo', 'proforma'));
    }

    public function updateAdecentamientos(Request $request, $id)
    {
        try {
            DB::beginTransaction();
            $proforma = ProformaAdecentamiento::findOrFail($id);

            $productosExistente = $proforma->detalleAdecentamientos()->pluck('producto_id')->toArray();
            $productosEliminar = array_diff($productosExistente, $request->producto);

            $fecha = $request->input('fecha');
            $clienteId = $request->input('cliente');
            $porcentaje_iva = $request->input('porcentaje_iva', 0);
            $descuento = $request->input('descuento', 0);
            $nota = $request->input('nota');
            $validez = $request->input('validez');
            $formaPago = $request->input('forma_pago');
            $plazoEntrega = $request->input('plazo_entrega');
            $observaciones = $request->input('observaciones');
            $estado = $request->input('estado');
            $descripcion = $request->input('descripcion');

            $items = array_map(function ($producto, $cantidad, $precio_unitario, $costo_indirecto) {
                return [
                    'producto' => is_numeric($producto) ? $producto : agregarProductoProforma($producto, $precio_unitario, 'adecentamientos'),
                    'cantidad' => str_replace(',', '', $cantidad),
                    'valor' => limpiarValor($precio_unitario),
                    'costo_indirecto' => $costo_indirecto,
                ];
            }, $request->input('producto', []), $request->input('cantidad', []), $request->input('precio', []), $request->input('indirecto', []));

            $subtotal = array_reduce($items, function ($carry, $item) {
                $precio_unitario = calcularProcentaje($item['valor'], $item['costo_indirecto']);
                return $carry + ($item['cantidad'] * $precio_unitario);
            }, 0);

            $proforma->update([
                'fecha' => $fecha,
                'cliente_id' => $clienteId,
                'observaciones' => $observaciones,
                'notas' => $nota,
                'validez' => $validez,
                'forma_pago' => $formaPago,
                'plazo_entrega' => $plazoEntrega,
                'subtotal' => $subtotal,
                'descuento' => $descuento,
                'iva' => $porcentaje_iva,
                'total' => calcularTotalProforma($subtotal, $porcentaje_iva, $descuento),
                'estado_id' => $estado,
                'descripcion' => $descripcion,
            ]);



            // Actualizar o crear los detalles de la proforma
            foreach ($items as $item) {
                $detalle = $proforma->detalleAdecentamientos()->updateOrCreate(
                    ['producto_id' => $item['producto']],
                    [
                        'cantidad' => $item['cantidad'],
                        'precio_unitario' => $item['valor'],
                        'costo_indirecto' => $item['costo_indirecto'],
                        'total' => $item['cantidad'] * $item['valor'],
                    ]
                );
            }

            if (!empty($productosEliminar)) {
                $proforma->detalleAdecentamientos()->whereIn('producto_id', $productosEliminar)->delete();
            }

            DB::commit();
            return redirect()->route('proformas.tipo', 'adecentamientos')->with('success', 'Proforma actualizada exitosamente.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Error al actualizar la proforma: ' . $e->getMessage());
        }
    }

    public function updatePlanos(Request $request, $id)
    {
        try {
            DB::beginTransaction();
            $proforma = ProformaPlano::findOrFail($id);

            $productosExistente = $proforma->detallePlanos()->pluck('producto_id')->toArray();
            $productosEliminar = array_diff($productosExistente, $request->producto);

            $fecha = $request->input('fecha');
            $clienteId = $request->input('cliente');
            $porcentaje_iva = $request->input('porcentaje_iva', 0);
            $descuento = $request->input('descuento', 0);
            $ubicacion = $request->input('ubicacion_lote');
            $area_lote = $request->input('area_lote');
            $presupuesto = $request->input('presupuesto');
            $observaciones = $request->input('observaciones');
            $incluye = $request->input('incluye');
            $formaPago = $request->input('forma_pago');
            $plazo_ejecucion = $request->input('plazo_ejecucion');
            $abono = $request->input('abono');
            $estado = $request->input('estado');
            $descripcion = $request->input('descripcion');

            $items = array_map(function ($producto, $area, $precio_unitario, $costo_indirecto) {
                return [
                    'producto' => is_numeric($producto) ? $producto : agregarProductoProforma($producto, $precio_unitario, 'planos'),
                    'area' => str_replace(',', '', $area),
                    'valor' => limpiarValor($precio_unitario),
                    'costo_indirecto' => $costo_indirecto,
                ];
            }, $request->input('producto', []), $request->input('cantidad', []), $request->input('precio', []), $request->input('indirecto', []));

            $subtotal = array_reduce($items, function ($carry, $item) {
                $precio_unitario = calcularProcentaje($item['valor'], $item['costo_indirecto']);
                return $carry + ($item['area'] * $precio_unitario);
            }, 0);

            $proforma->update([
                'fecha' => $fecha,
                'cliente_id' => $clienteId,
                'observaciones' => $observaciones,
                'ubicacion_lote' => $ubicacion,
                'area_lote' => $area_lote,
                'presupuesto' => $presupuesto,
                'incluye' => $incluye,
                'plazo_ejecucion' => $plazo_ejecucion,
                'forma_pago' => $formaPago,
                'abono' => $abono,
                'subtotal' => $subtotal,
                'descuento' => $descuento,
                'iva' => $porcentaje_iva,
                'total' => calcularTotalProforma($subtotal, $porcentaje_iva, $descuento),
                'estado_id' => $estado,
                'descripcion' => $descripcion,
            ]);

            // Actualizar o crear los detalles de la proforma
            foreach ($items as $item) {
                $detalle = $proforma->detallePlanos()->updateOrCreate(
                    ['producto_id' => $item['producto']],
                    [
                        'area' => $item['area'],
                        'precio_unitario' => $item['valor'],
                        'costo_indirecto' => $item['costo_indirecto'],
                        'total' => $item['area'] * $item['valor'],
                    ]
                );
            }

            if (!empty($productosEliminar)) {
                $proforma->detalleAdecentamientos()->whereIn('producto_id', $productosEliminar)->delete();
            }

            DB::commit();
            return redirect()->route('proformas.tipo', 'diseno_planos')->with('success', 'Proforma actualizada exitosamente.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Error al actualizar la proforma: ' . $e->getMessage());
        }
    }

    public function destroy($tipo, $id)
    {
        try {
            DB::beginTransaction();
            $proforma = $tipo === 'adecentamientos' ? ProformaAdecentamiento::findOrFail($id) : ProformaPlano::findOrFail($id);
            $proforma->delete();
            DB::commit();
            return response()->json(['success' => true, 'message' => 'Proforma eliminada exitosamente.']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Error al eliminar la proforma: ' . $e->getMessage()], 500);
        }
    }


    public function buscar(Request $request, $tipo)
    {
        if ($request->ajax()) {
            $buscar = $request->input('text');
            $output = "";

            if ($tipo === 'adecentamientos') {
                $proformas = ProformaAdecentamiento::where('numero', 'like', "%{$buscar}%")
                    ->orWhereHas('cliente', function ($query) use ($buscar) {
                        $query->where('nombre', 'like', "%{$buscar}%");
                    })
                    ->orderBy('created_at', 'desc')->paginate(15);
            } else {
                // Implementar búsqueda para otros tipos de proformas si es necesario
                $proformas = [];
            }

            if ($proformas) {
                foreach ($proformas as $proforma) {
                    $output .= "<tr id='{$proforma->id}'>
                        <td class='align-middle'>{$proforma->numero}</td>
                        <td class='align-middle'>{$proforma->fecha_formatted}</td>
                        <td class='align-middle'>{$proforma->cliente->nombre}</td>
                        <td class='align-middle'>{$proforma->total_formatted}</td>
                        <td class='align-middle'>{$proforma->estado->descripcion}</td>
                        <td class='align-middle'>
                            <div class='btn-group dropleft'>
                                <button type='button' class='btn btn-outline-dark dropdown-toggle' data-toggle='dropdown' aria-haspopup='true' aria-expanded='false'>
                                    Opciones
                                </button>
                                <div class='dropdown-menu'>
                                    <a class='dropdown-item' href='" . route('proformas.edit', [$tipo, $proforma->id]) . "'>Editar</a>
                                    <a class='dropdown-item eliminar' href='javascript:void(0);' id='" . $proforma->id . "'>Eliminar</a>
                                    <a class='dropdown-item' href='" . route('pdf.proformas', [$tipo, $proforma->id]) . "'  target='_blank'>Generar PDF</a>
                                </div>
                            </div>
                        </td>
                    </tr>";
                }

                if (empty($output)) {
                    $output .= '<tr>' .
                        '<td colspan="6" class="text-center">' .
                        '<span class="text-danger">No existen datos para mostrar.</span>' .
                        '</td>' .
                        '</tr>';
                }
            }

            return Response($output);
        }
    }
}
