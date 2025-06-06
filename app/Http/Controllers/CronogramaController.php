<?php

namespace App\Http\Controllers;

use App\Constants\MessagesConstant;
use App\Models\Proyecto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\PresupuestoProyecto;
use App\Http\Requests\StoreActividadCronogramaRequest;
use App\Models\Adquisicion;
use App\Models\AdquisicionDetalle;
use App\Models\Contratista;
use App\Models\Cronograma;
use App\Models\DetalleContratista;
use App\Models\DetalleManoObra;
use App\Models\ManoObra;
use App\Models\PagoOrdenTrabajoContratista;
use App\Models\RubroCronograma;
use App\Services\LogService;

class CronogramaController extends Controller
{

    private $dias;

    public function __construct()
    {
        // Días de la semana
        $this->dias =  ['lunes', 'martes', 'miercoles', 'jueves', 'viernes', 'sabado'];
    }

    public function index(Proyecto $proyecto)
    {
        $title_page = 'Cronograma';
        $proyectoId = $proyecto->id;

        $breadcrumbs = [
            ['name' => 'Inicio', 'url' => route('home')],
            ['name' => $proyecto->nombre_proyecto, 'url' => route('proyecto.view', ['tipo' => $proyecto->catalogo_proyecto->descripcion, 'tipo_id' => $proyecto->catalogo_proyecto->id, 'proyecto' => $proyecto->id])],
            ['name' => $title_page, 'url' => ''] // Último breadcrumb no tiene URL, es el actual
        ];

        $categorias = $proyecto->presupuestoValorado($proyecto->id);
        $plazo_semanas = plazoSemanasProyecto($proyecto->fecha_inicio, $proyecto->fecha_finalizacion);

        // Consulta agrupada por rubro_cronograma_id y semana, filtrada por proyecto_id
        // Consulta inicial para obtener todos los registros filtrados por proyecto_id
        $cronogramas = Cronograma::with('rubro_cronograma')
            ->where('proyecto_id', $proyectoId)
            ->orderBy('id', 'asc')
            ->get();

        // Agrupar los datos por rubro_cronograma_id
        $cronograma = $cronogramas->groupBy('rubro_cronograma_id')->map(function ($grupo) {
            // Obtener el nombre del rubro
            $rubroCronogramaNombre = $grupo->first()->rubro_cronograma->descripcion;
            // Obtener el id del rubro_cronograma
            $rubro_cronograma_id = $grupo->first()->rubro_cronograma->id;

            // Crear un array con las semanas donde el rubro está presente
            // Organizar las semanas con sus días correspondientes
            $semanas = $grupo->groupBy('semana')->map(function ($semanaGrupo) {
                return $semanaGrupo->pluck('dia')->toArray();
            });

            return [
                'rubro_cronograma_id' => $rubro_cronograma_id,
                'rubro_cronograma_nombre' => $rubroCronogramaNombre,
                'semanas' => $semanas,
            ];
        });


        $rubros_cronograma = RubroCronograma::where('activo', true)->orderBy('descripcion', 'asc')->pluck('descripcion', 'id');

        $total_estructural = $categorias->sum(function ($categoria) {
            return $categoria->rubrosPresupuesto->sum(function ($rubro) {
                return $rubro->presupuestoProyectos
                    ->filter(function ($proyecto) {
                        // Filtrar proyectos basados en la relación etapa_construccion
                        return $proyecto->etapa_construccion &&
                            $proyecto->etapa_construccion->slug ===
                            'etapas.construccion.estructural';
                    })
                    ->sum(function ($proyecto) {
                        // Calcular cantidad * valor_unitario
                        return $proyecto->cantidad * $proyecto->valor_unitario;
                    });
            });
        });

        $total_obra_gris = $categorias->sum(function ($categoria) {
            return $categoria->rubrosPresupuesto->sum(function ($rubro) {
                return $rubro->presupuestoProyectos
                    ->filter(function ($proyecto) {
                        // Filtrar proyectos basados en la relación etapa_construccion
                        return $proyecto->etapa_construccion &&
                            ($proyecto->etapa_construccion->slug ===
                                'etapas.construccion.obra.gris');
                    })
                    ->sum(function ($proyecto) {
                        // Calcular cantidad * valor_unitario
                        return $proyecto->cantidad * $proyecto->valor_unitario;
                    });
            });
        });

        $total_acabados = $categorias->sum(function ($categoria) {
            return $categoria->rubrosPresupuesto->sum(function ($rubro) {
                return $rubro->presupuestoProyectos
                    ->filter(function ($proyecto) {
                        // Filtrar proyectos basados en la relación etapa_construccion
                        return $proyecto->etapa_construccion &&
                            $proyecto->etapa_construccion->slug ===
                            'etapas.construccion.acabados';
                    })
                    ->sum(function ($proyecto) {
                        // Calcular cantidad * valor_unitario
                        return $proyecto->cantidad * $proyecto->valor_unitario;
                    });
            });
        });

        $total = $total_estructural + $total_obra_gris + $total_acabados;


        $total_aquisiciones_estructural = AdquisicionDetalle::whereHas('adquisicion', function ($query) use ($proyectoId) {
            $query->where('proyecto_id', $proyectoId)
                ->whereHas('etapa', function ($q) {
                    $q->where('slug', 'menu.adquisciones.estructural');
                });
        })->sum('valor');


        $total_mano_obra_estructural = DetalleManoObra::whereHas('mano_obra', function ($query) use ($proyectoId) {
            $query->where('proyecto_id', $proyectoId)
                ->whereHas('etapa', function ($q) {
                    $q->where('slug', 'menu.adquisciones.estructural');
                });
        })
            ->whereHas('mano_obra.pago_mano_obra') // Filtrar solo los detalles relacionados con PagoManoObra
            ->sum('valor');

        $total_contratista_estructural = PagoOrdenTrabajoContratista::whereHas('contratista', function ($query) use ($proyectoId) {
            $query->where('proyecto_id', $proyectoId)
                ->whereHas('etapa', function ($q) {
                    $q->where('slug', 'menu.adquisciones.estructural');
                });
        })->where('pagado', true)
            ->sum('valor');

        $totales_estructural = Cronograma::TotalAdquisicionesEtapa($proyectoId, 'menu.adquisciones.estructural');
        $totales_obra_gris = Cronograma::TotalAdquisicionesEtapa($proyectoId, 'menu.adquisciones.obra.gris');
        $totales_acabados = Cronograma::TotalAdquisicionesEtapa($proyectoId, 'menu.adquisciones.acabados');

        return view('cronograma.index', compact('title_page', 'breadcrumbs', 'proyecto', 'categorias', 'plazo_semanas', 'cronograma', 'rubros_cronograma', 'total_estructural', 'total_obra_gris', 'total_acabados', 'total', 'totales_estructural', 'totales_obra_gris', 'totales_acabados'));
    }

    public function editarActividadesSemana(Proyecto $proyecto, $semana)
    {
        $title_page = 'Actividades de la semana ' . $semana;

        $breadcrumbs = [
            ['name' => 'Inicio', 'url' => route('home')],
            ['name' => 'Cronograma', 'url' => route('proyecto.cronograma.index', ['proyecto' => $proyecto->id])],
            ['name' => $title_page, 'url' => ''] // Último breadcrumb no tiene URL, es el actual
        ];

        $cronogramas = Cronograma::with('rubro_cronograma')
            ->where('proyecto_id', $proyecto->id)
            ->where('semana', $semana)
            ->get();

        // Agrupar los datos por rubro_cronograma_id
        // Agrupar los datos por rubro_cronograma_id
        $actividades = $cronogramas->groupBy('rubro_cronograma_id')->map(function ($grupo) {
            return [
                'rubro_cronograma_id' => $grupo->first()->rubro_cronograma->id,
                'rubro_cronograma_nombre' => $grupo->first()->rubro_cronograma->descripcion,
                'dias' => $grupo->mapWithKeys(function ($item) {
                    return [$item->dia => ['observacion' => $item->observacion, 'id' => $item->id]];
                })->toArray(),
            ];
        });

        // Extraer todos los días únicos (lunes a domingo)
        $diasSemana = config('app.diasSemana', []);

        // Estructurar los datos para la tabla
        $tablaDatos = [];
        foreach ($actividades as $actividad) {
            foreach ($diasSemana as $dia) {
                $tablaDatos[$actividad['rubro_cronograma_nombre']][$dia] = $actividad['dias'][$dia] ?? ['observacion' => '', 'id' => null];
            }
        }

        $rubrosPorDia = [];
        // Procesar actividades para mapear días y rubros
        foreach ($cronogramas as $cronograma) {
            $dia = $cronograma->dia;
            $rubroNombre = $cronograma->rubro_cronograma->descripcion;
            $observacion = $cronograma->observacion;

            if (!isset($rubrosPorDia[$dia])) {
                $rubrosPorDia[$dia] = [
                    'rubros' => [],
                    'observaciones' => []
                ];
            }

            $rubrosPorDia[$dia]['rubros'][] = $rubroNombre;
            $rubrosPorDia[$dia]['observaciones'][] = $observacion;
        }

        // Concatenar los rubros y observaciones para cada día
        foreach ($rubrosPorDia as $dia => $data) {
            $rubrosPorDia[$dia]['rubros'] = implode(', ', $data['rubros']);
            $rubrosPorDia[$dia]['observaciones'] = implode(', ', $data['observaciones']);
        }

        return view('cronograma.editar_actividades_semana', compact('title_page', 'breadcrumbs', 'proyecto', 'semana', 'rubrosPorDia', 'diasSemana', 'tablaDatos'));
    }

    public function updateActividadesDiaSemana(Request $request)
    {
        return $request->all();
        try {
            DB::beginTransaction();
            $proyecto = $request->proyecto;
            $semana = $request->semana;
            $dias = $request->dias;
            foreach ($dias as $dia => $data) {
                // Verificar si el día tiene un rubro asignado
                if (!empty($data['rubo'])) {
                    // Buscar o crear el cronograma con el día, semana y rubro
                    Cronograma::updateOrCreate(
                        [
                            'proyecto_id' => $proyecto,
                            'semana' => $semana,
                            'dia' => $dia
                        ],
                        [
                            'rubro_cronograma_id' => $data['rubo'],
                            'observacion' => $data['observacion'] ?? null
                        ]
                    );
                }
            }

            DB::commit();
            return redirect()->route('proyecto.cronograma.actividades.semana', ['proyecto' => $proyecto, 'semana' => $semana])->with('success', MessagesConstant::UPDATE);
        } catch (\Throwable $e) {
            DB::rollBack();
            LogService::log('ERROR', 'Actualizar actividades semana cronograma', ['error' => $e]);
            return redirect()->back()->with('error', MessagesConstant::CATCH_ERROR);
        }
    }

    public function ajaxStoreRubrosCronograma(Request $request)
    {
        try {
            if ($request->ajax()) {
                DB::beginTransaction();
                $proyecto = $request->proyecto;
                $rubro_cronograma = $request->rubro_cronograma;
                $semana = $request->semana;
                $dias = $request->dias;

                // validar si es numerico el rubro_cronograma
                if (!is_numeric($rubro_cronograma)) {
                    // Verificar si el rubro ya existe en la base de datos
                    $existe = RubroCronograma::where('descripcion', $rubro_cronograma)->exists();
                    if (!$existe) {
                        // Guardar el rubro si no existe y obtener el id
                        $rubro_cronograma = RubroCronograma::create([
                            'descripcion' => $rubro_cronograma,
                            'activo' => true,
                        ])->id;
                    }
                }

                // Filtrar solo los días marcados (checked = true)
                foreach ($dias as $dia => $info) {
                    if (isset($info['checked'])) { // Solo procesar si el día está marcado
                        Cronograma::updateOrCreate(
                            [
                                'proyecto_id' => $proyecto,
                                'semana' => $semana,
                                'dia' => $dia,
                                'rubro_cronograma_id' => $rubro_cronograma
                            ],
                            [
                                'observacion' => $info['observacion'] ?? null
                            ]
                        );
                    } else {
                        // Si el rubro no esta checked, eliminar el registro del cronograma para ese día y semana del proyecto
                        Cronograma::where('proyecto_id', $proyecto)
                            ->where('semana', $semana)
                            ->where('dia', $dia)
                            ->where('rubro_cronograma_id', $rubro_cronograma)
                            ->delete();
                    }
                }

                DB::commit();
                return response()->json(['success' => true, 'message' => MessagesConstant::INSERT]);
            }
        } catch (\Throwable $e) {
            DB::rollBack();
            LogService::log('ERROR', 'ajaxStoreRubrosCronograma', ['error' => $e]);
            return response()->json(['success' => false, 'message' => MessagesConstant::CATCH_ERROR, 'error' => $e->getMessage()]);
        }
    }

    public function ajaxUpdateActividadCronograma(Request $request)
    {
        if ($request->ajax()) {
            try {
                DB::beginTransaction();
                $id = $request->rubro;
                $descripcion = $request->nombre;

                // Actualizar o crear la actividad del cronograma
                RubroCronograma::updateOrCreate(
                    ['id' => $id],
                    ['descripcion' => $descripcion]
                );
                DB::commit();
                return response()->json(['success' => true, 'message' => MessagesConstant::UPDATE]);
            } catch (\Throwable $e) {
                DB::rollBack();
                LogService::log('ERROR', 'ajaxUpdateActividadCronograma', ['error' => $e]);
                return response()->json(['success' => false, 'message' => MessagesConstant::CATCH_ERROR, 'error' => $e->getMessage()]);
            }
        }
    }

    public function destroyCronogramaRubro(Request $request)
    {
        if ($request->ajax()) {
            try {
                DB::beginTransaction();
                $id = $request->rubro;
                // Eliminar el rubro del cronograma

                Cronograma::where('rubro_cronograma_id', $id)
                    ->where('proyecto_id', $request->proyecto)->delete();

                DB::commit();
                return response()->json(['success' => true, 'message' => MessagesConstant::DELETE]);
            } catch (\Throwable $e) {
                DB::rollBack();
                LogService::log('ERROR', 'destroyRubroCronograma', ['error' => $e]);
                return response()->json(['success' => false, 'message' => MessagesConstant::CATCH_ERROR, 'error' => $e->getMessage()]);
            }
        }
    }
    /*
    public function crearCronogramaDiaSemana(Proyecto $proyecto, $semana, $rubro)
    {
        $title_page = 'Actividades de la semana ' . $semana;

        $breadcrumbs = [
            ['name' => 'Inicio', 'url' => route('home')],
            ['name' => 'Cronograma', 'url' => route('proyecto.cronograma.index', ['proyecto' => $proyecto->id])],
            ['name' => $title_page, 'url' => ''] // Último breadcrumb no tiene URL, es el actual
        ];

        $actividades = ActividadCronograma::pluck('descripcion', 'id');

        return view('cronograma.crear_actividad_dia_semana', compact('title_page', 'breadcrumbs', 'proyecto', 'semana', 'rubro', 'actividades'));
    }

    public function storeActividadesDiaSemana(StoreActividadCronogramaRequest $request)
    {
        try {
            // Obtener los datos validados
            $validated = $request->validated();
            $proyecto = $request->proyecto;
            $semana = $request->semana;
            $rubro = $request->rubro;

            DB::beginTransaction();

            $cronograma = Cronograma::create([
                'proyecto_id' => $proyecto,
                'rubro_id' => $rubro,
                'etapa_id' => 1,
                'semana' => $semana,
            ])->id;

            foreach ($this->dias as $dia) {
                // Verificar si el día existe en el request y tiene actividades
                if (isset($validated[$dia]) && is_array($validated[$dia])) {
                    foreach ($validated[$dia] as $actividad) {
                        $actividad_id = $actividad;
                        if (!is_numeric($actividad_id)) {
                            // Verificar si la actividad ya existe en la base de datos
                            $existe = ActividadCronograma::where('descripcion', $actividad)->exists();
                            if (!$existe) {
                                // Guardar la actividad si no existe
                                $actividad_id = ActividadCronograma::create([
                                    'descripcion' => $actividad,
                                    'activo' => true,
                                ])->id;
                            }
                        }

                        ActividadDiaCronograma::create([
                            'cronograma_id' => $cronograma,
                            'actividad_cronograma_id' => $actividad_id,
                            'dia' => $dia,
                        ]);
                    }
                }
            }
            DB::commit();
            return redirect()->route('proyecto.cronograma.index', ['proyecto' => $request->proyecto])->with(['sweetalert' => true, 'title' => 'Aviso', 'message' => 'Actividades guardadas correctamente.', 'icon' => 'success']);
        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Ocurrió un error al guardar las actividades');
        }
    }

    public function editarCronogramaDiaSemana(Proyecto $proyecto, $semana, $rubro)
    {
        $title_page = 'Actividades de la semana ' . $semana;

        $breadcrumbs = [
            ['name' => 'Inicio', 'url' => route('home')],
            ['name' => 'Cronograma', 'url' => route('proyecto.cronograma.index', ['proyecto' => $proyecto->id])],
            ['name' => $title_page, 'url' => ''] // Último breadcrumb no tiene URL, es el actual
        ];

        $actividades = ActividadCronograma::pluck('descripcion', 'id');
        $cronograma = Cronograma::where('proyecto_id', $proyecto->id)->where('semana', $semana)->where('rubro_id', $rubro)->first();

        return view('cronograma.editar_actividad_dia_semana', compact('title_page', 'breadcrumbs', 'proyecto', 'semana', 'rubro', 'actividades', 'cronograma'));
    }

    public function updateActividadesDiaSemana(StoreActividadCronogramaRequest $request)
    {
        $validated = $request->validated();
        $proyecto = $request->proyecto;
        $semana = $request->semana;
        $rubro = $request->rubro;
        $cronograma = Cronograma::find($request->cronograma);
        $actividades_completadas = $request->completadas ? true : false;

        try {
            DB::beginTransaction();

            $cronograma->completado = $actividades_completadas;
            $cronograma->save();

            // Iterar sobre los días de la semana
            foreach ($this->dias as $dia) {
                // Obtener las actividades existentes en la base de datos para este día
                $actividadesExistentes = ActividadDiaCronograma::where('cronograma_id', $cronograma->id)
                    ->where('dia', $dia)
                    ->pluck('actividad_cronograma_id')
                    ->toArray();

                // Crear un array para almacenar los IDs de las actividades que se mantienen
                $actividadesMantener = [];

                // Verificar si el día existe en el request y tiene actividades
                if (isset($validated[$dia]) && is_array($validated[$dia])) {
                    foreach ($validated[$dia] as $actividad) {
                        $actividad_id = $actividad;
                        if (!is_numeric($actividad_id)) {
                            // Verificar si la actividad ya existe en la base de datos
                            $existe = ActividadCronograma::where('descripcion', $actividad)->exists();
                            if (!$existe) {
                                // Guardar la actividad si no existe
                                $actividad_id = ActividadCronograma::create([
                                    'descripcion' => $actividad,
                                    'activo' => true,
                                ])->id;
                            }
                        }

                        // Agregar la actividad al array de actividades a mantener
                        $actividadesMantener[] = $actividad_id;

                        // Crear el registro en ActividadDiaCronograma si no existe
                        $existeRegistro = ActividadDiaCronograma::where('cronograma_id', $cronograma->id)
                            ->where('actividad_cronograma_id', $actividad_id)
                            ->where('dia', $dia)
                            ->exists();

                        if (!$existeRegistro) {
                            ActividadDiaCronograma::create([
                                'cronograma_id' => $cronograma->id,
                                'actividad_cronograma_id' => $actividad_id,
                                'dia' => $dia,
                            ]);
                        }
                    }
                }

                // Eliminar los registros que no están en el request
                $actividadesAEliminar = array_diff($actividadesExistentes, $actividadesMantener);
                if (!empty($actividadesAEliminar)) {
                    ActividadDiaCronograma::where('cronograma_id', $cronograma->id)
                        ->where('dia', $dia)
                        ->whereIn('actividad_cronograma_id', $actividadesAEliminar)
                        ->delete();
                }
            }

            DB::commit();
            return redirect()->route('proyecto.cronograma.index', ['proyecto' => $request->proyecto])->with(['sweetalert' => true, 'title' => 'Aviso', 'message' => 'Actividades actualizadas correctamente.', 'icon' => 'success']);
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->with('error', 'Ocurrió un error al actualizar las actividades');
        }
    }

    public function getAjaxActividades(Request $request)
    {
        if ($request->ajax()) {
            $actividades = ActividadCronograma::where('activo', true)->pluck('descripcion', 'id');
            //$actividades = $actividades->prepend('', '');
            return response()->json(['actividades' => $actividades]);
        }
    }
      
*/
}