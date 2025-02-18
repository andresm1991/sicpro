<?php

namespace App\Http\Controllers;

use App\Constants\MessagesConstant;
use App\Models\Proyecto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\PresupuestoProyecto;
use App\Http\Requests\StoreActividadCronogramaRequest;
use App\Models\Cronograma;
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

        $breadcrumbs = [
            ['name' => 'Inicio', 'url' => route('home')],
            ['name' => $proyecto->nombre_proyecto, 'url' => route('proyecto.view', ['tipo' => $proyecto->catalogo_proyecto->descripcion, 'tipo_id' => $proyecto->catalogo_proyecto->id, 'proyecto' => $proyecto->id])],
            ['name' => $title_page, 'url' => ''] // Último breadcrumb no tiene URL, es el actual
        ];

        $categorias = $proyecto->presupuestoValorado($proyecto->id);
        $plazo_semanas = plazoSemanasProyecto($proyecto->fecha_inicio, $proyecto->fecha_finalizacion);
        $cronograma = Cronograma::with('rubroCronograma')
            ->selectRaw('rubro_cronograma_id, semana, proyecto_id')
            ->groupBy('rubro_cronograma_id', 'semana', 'proyecto_id')
            ->where('proyecto_id', $proyecto)
            ->get()
            ->map(function ($grupo) {
                // Obtener los días asociados al grupo
                $dias = Cronograma::where('rubro_cronograma_id', $grupo->rubro_cronograma_id)
                    ->where('semana', $grupo->semana)
                    ->pluck('dia');

                // Agregar los días al grupo
                $grupo->dias = $dias;

                // Cargar la descripción del rubro_cronograma
                $grupo->rubro_cronograma_nombre = $grupo->rubroCronograma->nombre;

                return $grupo;
            });
        return $cronograma;
        $rubros_cronograma = RubroCronograma::where('activo', true)->orderBy('descripcion', 'asc')->pluck('descripcion', 'id');

        return view('cronograma.index', compact('title_page', 'breadcrumbs', 'proyecto', 'categorias', 'plazo_semanas', 'cronograma', 'rubros_cronograma'));
    }

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
                        Cronograma::create([
                            'proyecto_id' => $proyecto,
                            'rubro_cronograma_id' => $rubro_cronograma,
                            'semana' => $semana,
                            'dia' => $dia,
                            'observacion' => $info['observacion'] ?? null,
                        ]);
                    }
                }

                DB::commit();
                return response()->json(['success' => true, 'message' => MessagesConstant::INSERT]);
            }
        } catch (\Throwable $e) {
            DB::rollBack();
            LogService::log('ERROR', 'ajaxStoreRubrosCronograma', ['error' => $e]);
            return response()->json(['success' => false, 'message' => MessagesConstant::CATCH_ERROR]);
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
}
