<?php

namespace App\Http\Controllers\JPLimpieza;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\JPLimpieza\Proyecto;
use App\Http\Controllers\Controller;
use App\Models\JPLimpieza\RubroPresupuesto;
use App\Models\JPLimpieza\PresupuestoProyecto;
use App\Models\JPLimpieza\CategoriaPresupuesto;
use App\Http\Resources\JPLimpieza\PresupuestoResource;

class PresupuestoProyectoController extends Controller
{
    public function index(Request $request)
    {
        $proyectoInfo = Proyecto::findOrFail($request->proyecto);

        $breadcrumbs = [
            ['name' => 'Inicio', 'url' => route('home')],
            ['name' => 'Limpieza y mantenimiento', 'url' => route('jp.limpieza.index')],
            ['name' => $proyectoInfo->nombre_proyecto, 'url' => route('jp.limpieza.proyectos.show', $proyectoInfo->id)],
            ['name' => 'Presupuesto', 'url' => ''],
        ];

        $proyecto = Proyecto::with([
            'presupuesto.rubroPresupuesto.categoriaPresupuesto'
        ])->findOrFail($request->proyecto);

        $proyectoResource  = (new PresupuestoResource($proyecto))->toArray(request());
        return view('jp_limpieza.presupuesto.index', ['proyecto' => $proyectoResource, 'breadcrumbs' => $breadcrumbs]);
    }


    public function ajaxRubros(Request $request)
    {
        if ($request->ajax()) {
            $categoria_id = $request->categoria;
            $rubros = RubroPresupuesto::where('categoria_presupuesto_id', $categoria_id)->where('activo', 1)->get();
            return response()->json(['rubros' => $rubros]);
        }
    }

    public function AjaxStoreRubro(Request $request)
    {
        if ($request->ajax()) {
            try {
                DB::beginTransaction();
                $categoria = $request->categoria;
                $rubro = $request->rubro;
                $precioUnitario = str_replace(',', '', $request->valor);
                $cantidad = str_replace(',', '', $request->cantidad);
                $meses = $request->meses;

                if (!is_numeric($request->categoria)) {
                    $categoria = CategoriaPresupuesto::create([
                        'nombre' => $request->categoria,
                        'activo' => true,
                    ])->id;
                }


                if (!is_numeric($request->rubro)) {
                    $rubro = RubroPresupuesto::create([
                        'categoria_presupuesto_id' => $categoria,
                        'nombre' => $request->rubro,
                        'precio_unitario' => $precioUnitario,
                        'activo' => true,
                    ])->id;
                } else {
                    RubroPresupuesto::findOrFail($rubro)->update([
                        'categoria_presupuesto_id' => $categoria,
                        'precio_unitario' => $precioUnitario,
                    ]);
                }

                PresupuestoProyecto::create([
                    'proyecto_id' => $request->proyecto,
                    'rubro_presupuesto_id' => $rubro,
                    'cantidad' => $cantidad,
                    'precio_unitario' => $precioUnitario,
                    'iva' => 0,
                    'meses' => $meses,
                ]);

                DB::commit();

                return response()->json(['success' => true, 'mensaje' => 'Rubro registrado correctamente']);
            } catch (\Exception $e) {
                DB::rollBack();
                return response()->json(['success' => false, 'mensaje' => $e->getMessage()]);
            }
        }
    }
}