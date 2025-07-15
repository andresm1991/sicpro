<?php

namespace App\Http\Controllers;

use App\Models\CatalogoDato;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\ProformaDisenoPlano;
use App\Models\ProformaAdecentamiento;

class ProformaController extends Controller
{
    public function index($tipo)
    {
        $title_page = $tipo === 'adecentamientos' ? 'Adecentamientos' : 'Diseño de Planos';
        $breadcrumbs = [
            ['name' => 'Inicio', 'url' => route('home')],
            ['name' => 'Proformas', 'url' => route('proformas.tipo', $tipo)],
            ['name' => $title_page, 'url' => '']
        ];

        if ($tipo === 'adecentamientos') {
            $proformas = ProformaAdecentamiento::with(['cliente', 'estado'])->orderBy('created_at', 'desc')->paginate(15);
        } else {
            $proformas = []; // Reemplaza esto con la consulta adecuada
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
            $proforma = new ProformaDisenoPlano();
            $numero = ProformaDisenoPlano::max('numero') + 1; // Asumiendo que tienes un modelo ProformaDisenoPlano
        }

        return view('proformas.create', compact('title_page', 'breadcrumbs', 'tipo', 'proforma', 'numero'));
    }

    public function store(Request $request, $tipo)
    {
        try {
            DB::beginTransaction();
            $numero = $request->input('numero');
            $fecha = $request->input('fecha');
            $clienteId = $request->input('cliente');
            $porcentaje_iva = $request->input('porcentaje_iva', 0);
            $descuento = $request->input('descuento', 0);
            $nota = $request->input('nota');
            $validez = $request->input('validez');
            $formaPago = $request->input('forma_pago');
            $plazoEntrega = $request->input('plazo_entrega');
            $observaciones = $request->input('observaciones');

            $items = array_map(function ($producto, $cantidad, $precio_unitario, $iva, $unidad_medida) use ($tipo) {
                return [
                    'producto' => is_numeric($producto) ? $producto : agregarProductoProforma($producto, $precio_unitario, $iva),
                    'cantidad' => str_replace(',', '', $cantidad),
                    'unidad_medida' => is_numeric($unidad_medida) ? $unidad_medida : ($tipo == 'diseno_planos' ? registrarUnidadMedida($unidad_medida) : null),
                    'valor' => limpiarValor($precio_unitario),
                    'iva' => str_replace(',', '', $iva),
                ];
            }, $request->input('producto', []), $request->input('cantidad', []), $request->input('precio', []), $request->input('iva', []), $request->input('unidad_medida', []));

            if ($tipo === 'adecentamientos') {
                $subtotal = array_reduce($items, function ($carry, $item) {
                    return $carry + ($item['cantidad'] * $item['valor']) + (($item['cantidad'] * $item['valor']) * $item['iva'] / 100);
                }, 0);

                $proforma = ProformaAdecentamiento::create([
                    'numero' => $numero,
                    'fecha' => $fecha,
                    'cliente_id' => $clienteId,
                    'observaciones' => $observaciones,
                    'notas' => $nota,
                    'estado_id' => CatalogoDato::getIdCatalogo('estados.solicitud.pendiente'), // Estado por defecto
                    'validez' => $validez,
                    'forma_pago' => $formaPago,
                    'plazo_entrega' => $plazoEntrega,
                    'subtotal' => $subtotal,
                    'descuento' => $descuento,
                    'iva' => $porcentaje_iva,
                    'total' => $subtotal + ($subtotal * $porcentaje_iva / 100) - $descuento,
                ]);

                if ($proforma) {
                    foreach ($items as $item) {
                        $proforma->detalleAdecentamientos()->create([
                            'producto_id' => $item['producto'],
                            'cantidad' => $item['cantidad'],
                            'precio_unitario' => $item['valor'],
                            'subtotal' => $item['cantidad'] * $item['valor'],
                            'iva' =>  $item['iva'],
                            'total' => ($item['cantidad'] * $item['valor']) + (($item['cantidad'] * $item['valor']) * $item['iva'] / 100),
                        ]);
                    }
                }
                DB::commit();
            } else if ($tipo === 'diseno_planos') {
            }

            return redirect()->route('proformas.tipo', $tipo)->with('success', 'Proforma creada exitosamente.');
        } catch (\Exception $e) {
            return $e;
            return redirect()->back()->with('error', 'Error al guardar la proforma: ' . $e->getMessage());
        }
    }
}
