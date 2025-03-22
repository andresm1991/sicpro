<?php

namespace App\Http\Controllers;

use App\Models\Proyecto;
use App\Models\CatalogoDato;
use Illuminate\Http\Request;
use App\Models\DetalleManoObra;
use App\Models\RubroPresupuesto;
use Illuminate\Support\Facades\DB;
use App\Models\PresupuestoProyecto;
use App\Models\CategoriaPresupuesto;
use App\Models\PagoOrdenTrabajoContratista;

class PresupuestoController extends Controller
{
    public function index(Proyecto $proyecto)
    {
        $title_page = 'Presupuesto';

        $breadcrumbs = [
            ['name' => 'Inicio', 'url' => route('home')],
            ['name' => $proyecto->nombre_proyecto, 'url' => route('proyecto.view', ['tipo' => $proyecto->catalogo_proyecto->descripcion, 'tipo_id' => $proyecto->catalogo_proyecto->id, 'proyecto' => $proyecto->id])],
            ['name' => $title_page, 'url' => ''] // Último breadcrumb no tiene URL, es el actual
        ];

        $categorias = $proyecto->presupuestoValorado($proyecto->id);


        $unidades_medidas = CatalogoDato::getChildrenCatalogo('unidades.medida')->pluck('descripcion', 'id');
        $etapas_construccion = CatalogoDato::getChildrenCatalogo('etapas.construccion')->pluck('descripcion', 'id');
        $categorias_presupuesto = CategoriaPresupuesto::where('activo', 1)->pluck('nombre', 'id');

        $total_adquisiciones = $proyecto->adquisiciones
            ->where('estado', 'Completado')
            ->sum(function ($item) {
                $sumAdquisiciones = $item->adquisiciones_detalle->sum(function ($item) {
                    $iva = $item->producto->iva ? $item->producto->iva : 0;
                    return calcularTotalProducto(
                        $item->cantidad_solicitada,
                        $item->valor,
                        $iva,
                    );
                });

                return $sumAdquisiciones;
            });

        $total_mano_obra = DetalleManoObra::whereHas('mano_obra', function ($query) use ($proyecto) {
            $query->where('proyecto_id', $proyecto->id);
        })
            ->whereHas('mano_obra.pago_mano_obra') // Filtrar solo los detalles relacionados con PagoManoObra
            ->sum('valor');

        $total_contratista = PagoOrdenTrabajoContratista::whereHas('contratista', function ($query) use ($proyecto) {
            $query->where('proyecto_id', $proyecto->id);
        })->where('pagado', true)
            ->sum('valor');

        $total_gatos = $total_adquisiciones + $total_mano_obra + $total_contratista;
        return view('presupuesto_proyecto.index', compact('title_page', 'breadcrumbs', 'proyecto', 'categorias', 'unidades_medidas', 'categorias_presupuesto', 'etapas_construccion', 'total_gatos'));
    }


    public function store(Request $request, Proyecto $proyecto)
    {
        if ($request->ajax()) {
            $categoria_rubro = $request->categoria_rubro;
            $rubro = $request->rubro;
            $unidad_medida = $request->unidad_medida;
            $etapa_construccion = $request->etapa_construccion;
            $cantidad = str_replace(',', '', $request->cantidad);
            $valor_unitario = str_replace(',', '', $request->valor);

            try {
                DB::beginTransaction();

                if (!is_numeric($categoria_rubro)) {
                    $categoria_rubro = CategoriaPresupuesto::create([
                        'nombre' => $categoria_rubro,
                        'activo' => 1
                    ])->id;
                }
                if (!is_numeric($unidad_medida)) {
                    $unidad_medida = registrarUnidadMedida($unidad_medida);
                }
                if (!is_numeric($rubro)) {
                    $rubro = RubroPresupuesto::create([
                        'categoria_presupuesto_id' => $categoria_rubro,
                        'nombre' => $rubro,
                        'unidad_medida_id' => $unidad_medida,
                        'valor_unitario' => $valor_unitario,
                        'activo' => 1,
                        'etapa_id' => $etapa_construccion,
                    ])->id;
                } else {
                    // actualizar valor unitario y unidad de medida si ya existe el rubro
                    $update_rubro = RubroPresupuesto::find($rubro);
                    $update_rubro->valor_unitario = $valor_unitario;
                    $update_rubro->unidad_medida_id = $unidad_medida;
                    $update_rubro->save();
                }


                PresupuestoProyecto::create([
                    'proyecto_id' => $proyecto->id,
                    'rubro_presupuesto_id' => $rubro,
                    'cantidad' => $cantidad,
                    'valor_unitario' => $valor_unitario,
                    'etapa_id' => $etapa_construccion,
                ]);

                DB::commit();

                return response()->json(['success' => true, 'mensaje' => 'Rubro registrado correctamente']);
            } catch (\Exception $e) {
                DB::rollBack();
                return response()->json(['success' => false, 'mensaje' => $e->getMessage()]);
            }
        }
    }

    public function getAjaxRubrosPresupuesto(Request $request)
    {
        if ($request->ajax()) {
            $categoria_id = $request->categoria;
            $rubros = RubroPresupuesto::where('categoria_presupuesto_id', $categoria_id)->where('activo', 1)->get();
            return response()->json(['rubros' => $rubros]);
        }
    }

    public function putAjaxCostoIndirecto(Request $request, Proyecto $proyecto)
    {
        if ($request->ajax()) {
            try {
                $costo_indirecto = $request->porcentaje;
                $proyecto->costo_indirecto = $costo_indirecto;
                $proyecto->save();
                return response()->json(['success' => true, 'mensaje' => 'Costo indirecto actualizado correctamente']);
            } catch (\Exception $e) {
                return response()->json(['success' => false, 'mensaje' => $e->getMessage()]);
            }
        }
    }

    public function putAjaxRubroPresupuesto(Request $request)
    {
        if ($request->ajax()) {
            try {
                $presupuestoProyecto = PresupuestoProyecto::find($request->rubro_presupuesto_id);
                $presupuestoProyecto->cantidad = $request->cantidad;
                $presupuestoProyecto->valor_unitario = $request->valor;
                $presupuestoProyecto->etapa_id = $request->etapa_construccion;
                $presupuestoProyecto->save();

                $rubro = RubroPresupuesto::find($presupuestoProyecto->rubro_presupuesto_id);
                $rubro->valor_unitario = $request->valor;
                $rubro->unidad_medida_id = $request->unidad_medida;
                $rubro->etapa_id = $request->etapa_construccion;
                $rubro->save();

                return response()->json(['success' => true, 'mensaje' => 'Rubro actualizado correctamente']);
            } catch (\Throwable $th) {
                return response()->json(['success' => false, 'mensaje' => 'Error al actualizar el rubro']);
            }
        }
    }

    public function destroyAjaxRubroPresupuesto(Request $request)
    {
        if ($request->ajax()) {
            $id = $request->rubro;
            try {
                DB::beginTransaction();
                PresupuestoProyecto::find($id)->delete();
                DB::commit();
                return response()->json(['success' => true, 'mensaje' => 'Rubro eliminado correctamente']);
            } catch (\Exception $e) {
                DB::rollBack();
                return response()->json(['success' => false, 'mensaje' => $e->getMessage()]);
            }
        }
    }

    public function destroyAjaxCategoriaPresupuesto(Request $request)
    {
        if ($request->ajax()) {
            $categoria_id = $request->categoria;
            $proyecto = $request->proyecto;

            try {
                DB::beginTransaction();
                $rubros = RubroPresupuesto::where('categoria_presupuesto_id', $categoria_id)->pluck('id');
                PresupuestoProyecto::where('proyecto_id', $proyecto)->whereIn('rubro_presupuesto_id', $rubros)->delete();
                DB::commit();
                return response()->json(['success' => true, 'mensaje' => 'Categoría eliminada correctamente']);
            } catch (\Exception $e) {
                DB::rollBack();
                return response()->json(['success' => false, 'mensaje' => $e->getMessage()]);
            }
        }
    }

    public function filtrarRubrosPresupuesto(Request $request)
    {
        if ($request->ajax()) {
            $proyectoId = $request->proyecto;
            $filtro = $request->filtro;

            $categorias = CategoriaPresupuesto::whereHas('rubrosPresupuesto.presupuestoProyectos', function ($query) use ($proyectoId) {
                $query->where('proyecto_id', $proyectoId);
            })
                ->where(function ($query) use ($filtro) {
                    // Filtrar por categoría o por rubro
                    $query->where('nombre', 'LIKE', '%' . $filtro . '%') // Filtrar por categoría
                        ->orWhereHas('rubrosPresupuesto', function ($subQuery) use ($filtro) {
                            $subQuery->where('nombre', 'LIKE', '%' . $filtro . '%'); // Filtrar por rubro
                        });
                })
                ->with(['rubrosPresupuesto' => function ($query) use ($proyectoId, $filtro) {
                    $query->whereHas('presupuestoProyectos', function ($q) use ($proyectoId) {
                        $q->where('proyecto_id', $proyectoId); // Relación con el proyecto
                    });

                    // Aplicar filtro a los rubros solo si el filtro coincide con un rubro, no con una categoría
                    if (!CategoriaPresupuesto::where('nombre', 'LIKE', '%' . $filtro . '%')->exists()) {
                        $query->where('nombre', 'LIKE', '%' . $filtro . '%');
                    }
                }])->with('rubrosPresupuesto.unidad_medida')
                ->get();

            return response()->json(['categorias' => $categorias]);
        }
    }
}
