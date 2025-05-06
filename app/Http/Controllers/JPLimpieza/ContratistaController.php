<?php

namespace App\Http\Controllers\JPLimpieza;

use App\Models\Proveedor;
use App\Models\CatalogoDato;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\JPLimpieza\Proyecto;
use App\Http\Controllers\Controller;
use App\Models\JPLimpieza\Contratista;
use App\Models\JPLimpieza\DetalleContratista;

class ContratistaController extends Controller
{
    public function index(Request $request, $proyecto)
    {
        $proyectoInfo = Proyecto::find($proyecto);
        $breadcrumbs = [
            ['name' => 'Inicio', 'url' => route('home')],
            ['name' => $proyectoInfo->nombre_proyecto, 'url' => route('jp.limpieza.adquisiciones.index', $proyecto)],
            ['name' => 'Contratistas', 'url' => ''],
        ];


        $contratistas = Contratista::where('proyecto_id', $proyecto)
            ->orderBy('created_at', 'desc')->paginate(15);

        return view('jp_limpieza.contratistas.index', compact('contratistas', 'breadcrumbs', 'proyecto'));
    }

    public function create($proyecto)
    {
        $breadcrumbs = [
            ['name' => 'Inicio', 'url' => route('home')],
            ['name' => 'Contratistas', 'url' => route('jp.limpieza.contratistas.index', $proyecto)],
            ['name' => 'Nuevo contratista', 'url' => ''],
        ];

        $contratista = new Contratista();
        $numero = numeroPedido(Contratista::first());

        $tipo_proveedor = CatalogoDato::getIdCatalogo('contratista');

        $proveedores = Proveedor::where('categoria_proveedor_id', $tipo_proveedor)->pluck('razon_social', 'id');
        $proveedores->prepend('', '');

        return view('jp_limpieza.contratistas.create', compact('contratista', 'proyecto', 'numero', 'proveedores', 'breadcrumbs'));
    }

    public function store(Request $request)
    {
        try {
            DB::beginTransaction();
            $items = array_map(function ($producto, $cantidad, $valor, $iva, $unidad_medida) {
                $valoresLimpios = preg_replace('/[^0-9.]/', '', $valor); // Elimina $ y otros caracteres no numéricos

                return [
                    'producto' => is_numeric($producto) ? $producto : agregarProducto($producto, $valoresLimpios, $iva, $unidad_medida),
                    'cantidad' => str_replace(',', '', $cantidad),
                    'unidad_medida' => is_numeric($unidad_medida) ? $unidad_medida : registrarUnidadMedida($unidad_medida),
                    'valor' => $valoresLimpios,
                    'iva' => $iva,
                ];
            }, $request->producto, $request->cantidad, $request->precio, $request->iva, $request->unidad_medida);

            $contratista = Contratista::create([
                'numero' => $request->numero,
                'proyecto_id' => $request->proyecto,
                'proveedor_id' => $request->proveedor,
                'categoria_id' => $request->categoria,
                'fecha' => $request->fecha,
                'plazo' => $request->plazo ?? 0,
                'estado_id' => CatalogoDato::getIdCatalogo('estados.contratistas.proceso'),
            ])->id;

            if ($contratista) {
                foreach ($items as $item) {
                    DetalleContratista::create([
                        'contratista_id' => $contratista,
                        'producto_id' => $item['producto'],
                        'cantidad' => $item['cantidad'],
                        'unidad_medida_id' => $item['unidad_medida'],
                        'precio_unitario' => $item['valor'],
                        'iva' => $item['iva'],
                        'total' => calcularTotalProducto($item['cantidad'], $item['valor'], $item['iva']),
                    ]);
                }
                DB::commit();
                return redirect()->route('jp.limpieza.contratistas.index', $request->proyecto)
                    ->with('success', 'Contratista guardado correctamente.');
            }
            throw new \Exception('el proceso no se completó correctamente, por favor intente nuevamente.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Error al guardar el contratista: ' . $e->getMessage());
        }
    }

    public function show($id)
    {
        // Aquí puedes implementar la lógica para mostrar un contratista específico
    }

    public function edit($id)
    {
        // Aquí puedes implementar la lógica para mostrar el formulario de edición de un contratista específico
    }

    public function update(Request $request, $id)
    {
        // Aquí puedes implementar la lógica para actualizar un contratista específico
    }

    public function destroy($id)
    {
        // Aquí puedes implementar la lógica para eliminar un contratista específico
    }
}
