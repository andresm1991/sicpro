<?php

namespace App\Http\Controllers\JPLimpieza;

use App\Constants\MessagesConstant;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\JPLimpieza\Proyecto;
use App\Http\Controllers\Controller;
use App\Models\JPLimpieza\RubroPresupuesto;
use App\Models\JPLimpieza\PresupuestoProyecto;
use App\Models\JPLimpieza\CategoriaPresupuesto;
use App\Models\JPLimpieza\PlantillaPresupuesto;
use App\Http\Resources\JPLimpieza\PresupuestoResource;
use App\Services\LogService;

class PresupuestoProyectoController extends Controller
{
    public function index(Request $request)
    {
        $proyecto = Proyecto::with(['adquisiciones', 'adquisiciones.detalles', 'manoObra', 'manoObra.detalles', 'contratistas', 'contratistas.detalles'])->findOrFail($request->proyecto);

        $breadcrumbs = [
            ['name' => 'Inicio', 'url' => route('home')],
            ['name' => 'Limpieza y mantenimiento', 'url' => route('jp.limpieza.index')],
            ['name' => $proyecto->nombre_proyecto, 'url' => route('jp.limpieza.proyectos.show', $proyecto->id)],
            ['name' => 'Presupuesto', 'url' => ''],
        ];

        /* Seccion de codigo que obtiene los datos de adentro hacia afuera
        $proyecto = Proyecto::with([
            'presupuesto.rubroPresupuesto.categoriaPresupuesto'
        ])->findOrFail($request->proyecto);

        $proyectoResource  = (new PresupuestoResource($proyecto))->toArray(request());*/

        // Obtener todas las plantillas padres (donde padre_id es NULL)
        $categorias = PlantillaPresupuesto::with([
            'hijos',
            // Aquí está la magia: añadimos una condición a la relación anidada
            'hijos.presupuestoProyecto' => function ($query) use ($proyecto) {
                // Le decimos que solo traiga el presupuesto que pertenezca al proyecto actual
                $query->where('proyecto_id', $proyecto->id);
            }
        ])
            ->whereNull('padre_id')
            ->where('activo', true)
            ->get();

        $totalAdquisiciones = $proyecto->adquisiciones->sum(function ($item) {
            return $item->detalles->sum(function ($detalle) {
                return calcularTotalProducto($detalle->cantidad, $detalle->precio_unitario, $detalle->iva);
            });
        });

        $totalManoObra = $proyecto->manoObra->sum(function ($item) {
            $aporte_patronal = $item->aporte_patronal ?? 0;
            $total_ingreso = $item->total_ingreso ?? 0;
            return $total_ingreso + $aporte_patronal;
        });

        $totalContratistas = $proyecto->contratistas->sum(function ($item) {
            $total = calcularTotalProducto($item->cantidad, $item->precio_unitario, $item->iva);
            return $total;
        });

        $totalGastos = $totalAdquisiciones + $totalManoObra + $totalContratistas;


        return view('jp_limpieza.presupuesto.index', compact('categorias', 'proyecto', 'breadcrumbs', 'totalGastos'));
    }

    public function store(Request $request)
    {
        try {
            DB::beginTransaction();
            foreach ($request->presupuesto as $key => $presupuesto) {
                $plantilla_id = $key;
                if (!is_numeric($plantilla_id)) {
                    $plantilla_id = PlantillaPresupuesto::firstOrCreate(
                        [
                            'id' => $key
                        ],
                        [
                            'descripcion' => '',
                            'detalle' => '',
                            'slug' => $key,
                            'padre_id' => null,
                            'activo' => true,
                        ]
                    )->id;
                }

                // Verificar que los datos no sean todos cero (opcional)
                if ($presupuesto['cantidad'] == 0 && $presupuesto['precio_unitario'] == 0 && $presupuesto['meses'] == 0) {
                    continue; // Saltar registros con valores cero
                }

                PresupuestoProyecto::updateOrCreate(
                    [
                        'proyecto_id' => $request->proyecto,
                        'plantilla_id' => $plantilla_id,
                    ],
                    [
                        'cantidad' => $presupuesto['cantidad'],
                        'precio_unitario' => $presupuesto['precio_unitario'],
                        'meses' => $presupuesto['meses'],
                    ]
                );
            }
            DB::commit();
            return redirect()->route('jp.limpieza.presupuesto.index', $request->proyecto)->with('success', 'Presupuesto registrado exitosamente.');
        } catch (\Exception $e) {
            DB::rollBack();
            LogService::log('error', 'Error al guardar presupuesto JP', ['error' => $e->getMessage()]);
            return redirect()->back()->with('error', MessagesConstant::CATCH_ERROR);
        }
    }


    public function ajaxRubros(Request $request)
    {
        if ($request->ajax()) {
            $categoria_id = $request->categoria;
            $rubros = PlantillaPresupuesto::where('padre_id', $categoria_id)->where('activo', 1)->get();
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

                if (!is_numeric($request->categoria) && !is_numeric($request->rubro)) {
                    $categoria = PlantillaPresupuesto::create([
                        'descripcion' => $request->categoria,
                        'detalle' => $request->detalle,
                        'slug' => Str::slug($request->categoria),
                        'padre_id' => null,
                        'activo' => true,
                    ])->id;

                    PlantillaPresupuesto::create([
                        'descripcion' => $request->rubro,
                        'detalle' => '',
                        'slug' => Str::slug($request->categoria) . '.' . Str::slug($request->rubro),
                        'padre_id' => $categoria,
                        'activo' => true,
                    ])->id;
                } elseif (is_numeric($request->categoria) && !is_numeric($request->rubro)) {
                    $rubro = PlantillaPresupuesto::create([
                        'descripcion' => $request->rubro,
                        'detalle' => '',
                        'slug' => Str::slug($request->categoria) . '.' . Str::slug($request->rubro),
                        'padre_id' => $request->categoria,
                        'activo' => true,
                    ])->id;
                } else {
                    throw new \Exception("Los datos ingresados ya existen");
                }

                PresupuestoProyecto::create([
                    'proyecto_id' => $request->proyecto,
                    'plantilla_id' => $rubro,
                    'cantidad' => $cantidad,
                    'precio_unitario' => $precioUnitario,
                    'iva' => 0,
                    'meses' => $meses,
                ]);

                DB::commit();

                return response()->json(['success' => true, 'mensaje' => 'Rubro registrado correctamente']);
            } catch (\Exception $e) {
                DB::rollBack();
                LogService::log('error', 'Error al guardar rubro', ['error' => $e->getMessage()]);
                return response()->json(['success' => false, 'mensaje' => MessagesConstant::CATCH_ERROR]);
            }
        }
    }

    public function updateRubroDescripcion(Request $request, $proyecto,  PlantillaPresupuesto $rubro)
    {
        if ($request->ajax()) {
            $rubro->descripcion = $request->descripcion;
            if ($rubro->save()) {
                return response()->json(['success' => true, 'mensaje' => 'Descripción actualizada correctamente']);
            } else {
                return response()->json(['success' => false, 'mensaje' => 'Error al actualizar la descripción']);
            }
        }
    }
    public function updateCategoriaDescripcion(Request $request, $proyecto,  PlantillaPresupuesto $categoria)
    {
        if ($request->ajax()) {
            $categoria->descripcion = $request->descripcion;
            $categoria->detalle = $request->detalle;
            if ($categoria->save()) {
                return response()->json(['success' => true, 'mensaje' => 'Descripción actualizada correctamente']);
            } else {
                return response()->json(['success' => false, 'mensaje' => 'Error al actualizar la descripción']);
            }
        }
    }
}
