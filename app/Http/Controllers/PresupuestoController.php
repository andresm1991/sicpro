<?php

namespace App\Http\Controllers;

use App\Models\Proyecto;
use App\Models\CatalogoDato;
use Illuminate\Http\Request;
use App\Models\RubroPresupuesto;
use Illuminate\Support\Facades\DB;
use App\Models\PresupuestoProyecto;
use App\Models\CategoriaPresupuesto;

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

        $proyectoId = $proyecto->id;
        $categorias = CategoriaPresupuesto::whereHas('rubrosPresupuesto.presupuestoProyectos', function ($query) use ($proyectoId) {
            $query->where('proyecto_id', $proyectoId);
        })
            ->with(['rubrosPresupuesto' => function ($query) use ($proyectoId) {
                $query->whereHas('presupuestoProyectos', function ($q) use ($proyectoId) {
                    $q->where('proyecto_id', $proyectoId);
                })->with('presupuestoProyectos'); // Cargar presupuestoProyectos dentro de rubrosPresupuesto
            }])
            ->get();

        $unidades_medidas = CatalogoDato::getChildrenCatalogo('unidades.medida')->pluck('descripcion', 'id');
        $categorias_presupuesto = CategoriaPresupuesto::where('activo', 1)->pluck('nombre', 'id');

        return view('presupuesto_proyecto.index', compact('title_page', 'breadcrumbs', 'proyecto', 'categorias', 'unidades_medidas', 'categorias_presupuesto'));
    }


    public function store(Request $request, Proyecto $proyecto)
    {
        if ($request->ajax()) {
            $categoria_rubro = $request->categoria_rubro;
            $rubro = $request->rubro;
            $unidad_medida = $request->unidad_medida;
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

                if (!is_numeric($rubro)) {
                    if (!is_numeric($unidad_medida)) {
                        $unidad_medida = registrarUnidadMedida($unidad_medida);
                    }
                    $rubro = RubroPresupuesto::create([
                        'categoria_presupuesto_id' => $categoria_rubro,
                        'nombre' => $rubro,
                        'unidad_medida_id' => $unidad_medida,
                        'valor_unitario' => $valor_unitario,
                        'activo' => 1
                    ])->id;
                }


                PresupuestoProyecto::create([
                    'proyecto_id' => $proyecto->id,
                    'rubro_presupuesto_id' => $rubro,
                    'cantidad' => $cantidad,
                    'valor_unitario' => $valor_unitario
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
            $rubros = RubroPresupuesto::where('categoria_presupuesto_id', $categoria_id)->where('activo', 1)->pluck('nombre', 'id');
            return response()->json(['rubros' => $rubros]);
        }
    }
}
