<?php

namespace App\Http\Controllers\JPLimpieza;

use App\Models\Articulo;
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

    public function edit(Proyecto $proyecto, Contratista $contratista)
    {
        $breadcrumbs = [
            ['name' => 'Inicio', 'url' => route('home')],
            ['name' => $proyecto->nombre_proyecto, 'url' => route('jp.limpieza.adquisiciones.index', $proyecto->id)],
            ['name' => 'Contratistas', 'url' => route('jp.limpieza.contratistas.index', $proyecto->id)],
            ['name' => 'Editar contratista', 'url' => ''],
        ];

        $proveedores = Proveedor::pluck('razon_social', 'id');
        $proveedores->prepend('', '');

        return view('jp_limpieza.contratistas.edit', compact('contratista', 'proveedores', 'proyecto', 'breadcrumbs'));
    }


    public function update(Request $request, Proyecto $proyecto, Contratista $contratista)
    {
        try {
            if ($contratista->estado_id == CatalogoDato::getIdCatalogo('estados.contratistas.completado')) {
                return redirect()->back()->with('error', 'El contratista ya fue pagado y no es posible modificarlo.');
            }

            DB::beginTransaction();

            $plazo = $request->plazo ?? 0;

            $productosExistente = DetalleContratista::where('contratista_id', $contratista->id)->pluck('producto_id')->toArray();
            $productosEliminar = array_diff($productosExistente, $request->producto);

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

            $contratista->plazo = $plazo;
            $contratista->save();

            foreach ($items as $item) {
                if (empty($item['producto']) || empty($item['cantidad']) || empty($item['valor'])) {
                    return redirect()->back()->with('error', 'Por favor, complete todos los campos requeridos.');
                }

                DetalleContratista::updateOrCreate(
                    ['contratista_id' => $contratista->id, 'producto_id' => $item['producto']],
                    [
                        'cantidad' => $item['cantidad'],
                        'unidad_medida_id' => $item['unidad_medida'],
                        'precio_unitario' => $item['valor'],
                        'iva' => $item['iva'],
                        'total' => calcularTotalProducto($item['cantidad'], $item['valor'], $item['iva']),
                    ]
                );
            }

            /// Eliminr los articulos que no estan en $array_productos
            if (!empty($productosEliminar)) {
                DetalleContratista::where('contratista_id', $contratista->id)
                    ->whereIn('producto_id', $productosEliminar)->delete();
            }

            DB::commit();
            return redirect()->route('jp.limpieza.contratistas.edit', ['proyecto' => $proyecto->id, 'contratista' => $contratista->id])->with('success', 'Información actualizada exitosamente.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Error al actualizar el contratista: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        try {
            DB::beginTransaction();
            $contratista = Contratista::findOrFail($id);
            if ($contratista->estado_id ==  CatalogoDato::getIdCatalogo('estados.contratistas.completado')) {
                return response()->json(['success' => false, 'message' =>  'El contratista ya fue pagado y no es posible eliminarlo.']);
            }
            $contratista->delete();
            DB::commit();
            return response()->json(['success' => true, 'message' => 'Registro eliminado exitosamente.']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Error al eliminar el registro: ' . $e->getMessage()]);
        }
    }

    public function buscar(Request $request)
    {
        $output = '';
        $buscar = $request->input('buscar');
        $proveedorIds = Proveedor::where('razon_social', 'LIKE', "%$buscar%")->pluck('id');
        $categoriaIds = Articulo::where('descripcion', 'LIKE', "%$buscar%")->pluck('id');

        $contratista = Contratista::where('proyecto_id', $request->proyecto)->where(function ($q) use ($buscar, $proveedorIds, $categoriaIds) {
            $q->whereIn('proveedor_id', $proveedorIds)
                ->orWhereIn('categoria_id', $categoriaIds)
                ->orWhere('numero', 'LIKE', "%$buscar%")
                ->orWhere('fecha', 'LIKE', "%$buscar%")
                ->orWhere('plazo', 'LIKE', "%$buscar%");
        })->get();

        if ($contratista) {
            foreach ($contratista as $contratista) {
                $pagos = "<a href='" . route('jp.limpieza.contratistas.pagos', [$request->proyecto, $contratista->id]) . "' class='dropdown-item'>Pagos</a>";
                $editar = " <a href='" . route('jp.limpieza.contratistas.edit', [$request->proyecto, $contratista->id]) . "' class='dropdown-item'>Editar</a>";
                $eliminar = "<a href='#' class='dropdown-item eliminar-contratista' id='" . $contratista->id . "'>Eliminar</a>";

                $output .= '<tr id="' . $contratista->id . '">';
                $output .= '<td class="align-middle">' . $contratista->numero . '</td>';
                $output .= '<td class="align-middle">' . $contratista->fecha_formateada . '</td>';
                $output .= '<td class="align-middle">' . $contratista->proveedor->razon_social . '</td>';
                $output .= '<td class="align-middle">' . $contratista->categoria->descripcion . '</td>';
                $output .= '<td class="align-middle">' . $contratista->plazo . '</td>';
                $output .= '<td class="align-middle">$ ' . $contratista->total_contratado_formatted . '</td>';
                $output .= '<td class="align-middle">$ ' . $contratista->total_pagado_formatted . '</td>';
                $output .= '<td class="align-middle">$ ' . $contratista->total_pendiente_formatted . '</td>';
                $output .= '<td class="align-middle text-right text-truncate">';
                $output .= '<button type="button" class="btn btn-outline-dark" data-container="body" data-toggle="popover" data-placement="left" data-trigger="focus" data-content ="' . $pagos . $editar . $eliminar . '"> <i class="fas fa-caret-left font-weight-normal"></i> Opciones </button>';
                $output .= '</td>';
                $output .= '</tr>';
            }

            if (empty($output)) {
                $output .= '<tr>' .
                    '<td colspan="9" class="text-center">' .
                    '<span class="text-danger">No existen datos para mostrar.</span>' .
                    '</td>' .
                    '</tr>';
            }
            return Response($output);
        }
    }
}
