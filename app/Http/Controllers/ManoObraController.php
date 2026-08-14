<?php

namespace App\Http\Controllers;

use Throwable;
use Carbon\Carbon;
use App\Models\ManoObra;
use App\Models\Proyecto;
use Carbon\CarbonPeriod;
use App\Models\Proveedor;
use App\Models\CatalogoDato;
use App\Services\LogService;
use Illuminate\Http\Request;
use App\Models\DetalleManoObra;
use Illuminate\Validation\Rule;
use App\Models\ProveedorArticulo;
use PhpParser\Node\Expr\FuncCall;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use App\Http\Requests\ValidarFechasPlanificacionRequest;
use App\Models\PagoManoObra;

class ManoObraController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $title_page = 'Mano de obra';
        $route_params = $this->getRouteParameters($request);

        $breadcrumbs = [
            ['name' => 'Inicio', 'url' => route('home')],
            ['name' => $route_params['proyecto']->nombre_proyecto, 'url' => route('proyecto.adquisiciones.tipo', ['tipo' => $request->route('tipo'), 'tipo_id' => $request->route('tipo_id'), 'proyecto' => $request->route('proyecto'), 'tipo_adquisicion' => $request->route('tipo_adquisicion')])],
            ['name' => 'Mano de obra', 'url' => ''] // Último breadcrumb no tiene URL, es el actual
        ];

        $list_mano_obra = ManoObra::where('proyecto_id', $route_params['proyecto']->id)
            ->where('etapa_id', $request->tipo_adquisicion)
            ->where('tipo_etapa_id', $request->tipo_etapa)
            ->orderBy('semana', 'desc')
            ->paginate(15);

        $proyecto = Proyecto::findOrFail($request->proyecto);
        $subproyectos = $proyecto->subproyectos_unicos;


        $route_params = array_merge($route_params, ['list_mano_obra' => $list_mano_obra, 'subproyectos' => $subproyectos, 'breadcrumbs' => $breadcrumbs, 'title_page' => $title_page]);
        return view('mano_obra.index', $route_params);
    }

    /**
     * Ajax para obtener los articulos cuando se selecciona el proveedor
     */
    public function proveedorArticulos(Request $request)
    {
        if ($request->ajax()) {
            $proveedor_articulos = ProveedorArticulo::where('proveedor_id', $request->proveedor)->get();
            foreach ($proveedor_articulos as $element) {
                $proveedor[] = ['id' => $element->articulo_id, 'nombre' => $element->articulo->descripcion];
            }

            if (isset($proveedor)) {
                return response()->json(['success' => true, 'articulos' => $proveedor]);
            } else {
                return response()->json(['success' => false, 'articulos' => ['id' => '', 'nombre' => 'No existen categorias asociadas a la persona selecionada.']]);
            }
        }
    }

    /**
     * Ajax para obtener las fechas para editar detalle mano obra
     */
    public function fechasDetalleManoObra(Request $request)
    {
        if ($request->ajax()) {
            $info_mano_obra = ManoObra::find($request->mano_obra);
            $fecha_actual = Carbon::today();
            $fecha_inicio = $info_mano_obra->fecha_inicio;
            $fecha_fin = $info_mano_obra->fecha_fin; //> $fecha_actual ? $fecha_actual:  $info_mano_obra->fecha_fin;
            // Crear un rango de fechas desde el inicio hasta el fin
            $period = CarbonPeriod::create($fecha_inicio, $fecha_fin);

            // Convertir el periodo en un array de fechas
            $fechas = [];
            foreach ($period as $date) {
                $fechas[] = ['id' => $date->format('Y-m-d'), 'nombre' => $date->format('Y-m-d')];
            }

            if (isset($fechas) && count($fechas) > 0) {
                return response()->json(['success' => true, 'fechas' => $fechas]);
            } else {
                return response()->json(['success' => false, 'fechas' => ['id' => '', 'nombre' => 'No existen personal asociadas a la fecha selecionada.']]);
            }
        }
    }

    public function getAjaxActividadAcabados(Request $request)
    {
        if ($request->ajax()) {
            $slug_padre = 'actividad.acabados';
            $catalogo = CatalogoDato::getCatalogoPadre($slug_padre);
            if (!$catalogo) {
                $padre = CatalogoDato::create([
                    'descripcion' => 'Actividad Acabados',
                    'detalle' => '',
                    'slug' => $slug_padre,
                    'padre_id' => null,
                    'activo' => true,
                ]);
            }

            $children = CatalogoDato::getChildrenCatalogo($slug_padre);

            return response()->json(['actividad' => $children->pluck('descripcion', 'id')]);
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $title_page = 'Mano de obra - Nuevo';
        $route_params = $this->getRouteParameters($request);

        $breadcrumbs = [
            ['name' => 'Inicio', 'url' => route('home')],
            ['name' => 'Mano de Obra', 'url' => route('proyecto.adquisiciones.mano.obra', ['tipo' => $request->route('tipo'), 'tipo_id' => $request->route('tipo_id'), 'proyecto' => $request->route('proyecto'), 'tipo_adquisicion' => $request->route('tipo_adquisicion'), 'tipo_etapa' => $request->route('tipo_etapa')])],
            ['name' => 'Nueva Planificación', 'url' => '']
        ];

        $mano_obra = new ManoObra();

        $proveedores = Proveedor::where('categoria_proveedor_id', $route_params['tipo_etapa']->id)->pluck('razon_social', 'id');


        $route_params = array_merge($route_params, ['mano_obra' => $mano_obra, 'proveedores' => $proveedores, 'breadcrumbs' => $breadcrumbs, 'title_page' => $title_page]);
        return view('mano_obra.create', $route_params);
    }

    /**
     * Guardar o actualizar los trabajadores de la planificacion de mano de obra
     */
    public function store(Request $request)
    {
        $fecha = $request->fecha;
        $personal = $request->personal;
        $categoria = $request->categoria;
        $jornada = $request->jornada;
        $valor = $request->valor;
        $adicional = $request->adicional;
        $descuento = $request->descuento;
        $detalle_adicional = $request->detalle_adicional;
        $detalle_descuento = $request->detalle_descuento;
        $observaciones = $request->observaciones;

        try {
            DB::beginTransaction();

            // Validar si mano de obra ya esta pagada por administrativo
            $manoObraPagado = PagoManoObra::where('mano_obra_id', $request->mano_obra)->exists();
            if ($manoObraPagado) {
                return redirect()->back()->with('error', "No es posible guardar la planificación porque esta  ya se encuentra pagada.");
            }

            // Validar si el personal ya está asignado a otra planificación en la misma fecha
            foreach ($personal as $index => $proveedor_id) {
                $conflicto = DetalleManoObra::where('proveedor_id', $proveedor_id)
                    ->where('fecha', $fecha)
                    ->where('mano_obra_id', '!=', $request->mano_obra)
                    ->first();

                if ($conflicto) {
                    if (strtoupper($conflicto->jornada) === strtoupper('Completa')) {
                        return redirect()->back()->with('error', "No es posible guardar la planificación porque el trabajador {$conflicto->proveedor->razon_social} ya está asignado a otra planificación con la jornada completa en la mima fecha.");
                    }

                    if (strtoupper($conflicto->jornada) === strtoupper('Medio tiempo') && strtoupper($jornada[$index]) === strtoupper('Completa')) {
                        return redirect()->back()->with('error', "No es posible guardar la planificación porque el trabajador {$conflicto->proveedor->razon_social} ya tiene una planificación de medio tiempo y está intentando asignar una jornada completa en la misma fecha..");
                    }
                }
            }

            $personalExistente = DetalleManoObra::where('mano_obra_id', $request->mano_obra)
                ->where('fecha', $fecha)->pluck('proveedor_id')->toArray();
            // Eliminar todos los registros del personal
            if (empty($personal)) {
                DetalleManoObra::where('mano_obra_id', $request->mano_obra)
                    ->where('fecha', $fecha)->delete();
            } else {
                // Encontrar los IDs que están en la base de datos pero no en el formulario            
                $personalEliminar = array_diff($personalExistente, $personal);

                // Eliminar los registros que no están en el formulario
                if (!empty($personalEliminar)) {
                    DetalleManoObra::where('fecha', $fecha)
                        ->whereIn('proveedor_id', $personalEliminar)->delete();
                }

                foreach ($personal as $index => $value) {
                    DetalleManoObra::updateOrCreate(
                        [
                            'mano_obra_id' => $request->mano_obra,
                            'proveedor_id' => $value,
                            'fecha' => $fecha,
                        ],
                        [
                            'fecha' => $fecha,
                            'mano_obra_id' => $request->mano_obra,
                            'proveedor_id' => $value,
                            'articulo_id' => $categoria[$index],
                            'jornada' => $jornada[$index],
                            'valor' => $valor[$index],
                            'adicional' => $adicional[$index],
                            'descuento' => $descuento[$index],
                            'detalle_adicional' => $detalle_adicional[$index],
                            'detalle_descuento' => $detalle_descuento[$index],
                            'observacion' => $observaciones[$index],
                        ]
                    );
                }
            }
            DB::commit();
            LogService::log('info', 'Se creo planificación mano de obra', ['user_id' => auth()->id(), 'action' => 'create']);
            return redirect()->back()->with('success', 'Se creo planificación de mano de obra');
        } catch (Throwable $e) {
            DB::rollBack();

            LogService::log('error', 'Error al crear planificaciòn mano de obra', ['user_id' => auth()->id(), 'action' => 'create', 'message' => $e->getMessage()]);
            return redirect()->back()->with('error', 'Ocurrió un error inesperado, comuníquese con el administrador del sistema.');
        }
    }

    /**
     * Guardar nueva planificacion
     * @param Request
     * @return json
     */
    public function storePlanificacion(ValidarFechasPlanificacionRequest $request)
    {
        if ($request->ajax()) {

            // Obtener el numero de la semana segun el proyecto y tipo
            $semana = ManoObra::where('proyecto_id', $request->proyecto_id)
                ->where('etapa_id', $request->tipo_adquisicion)->count();

            $request->merge([
                'fecha_inicio' => Carbon::createFromFormat('d-m-Y', $request->fecha_inicio)->format('Y-m-d'),
                'fecha_fin' => Carbon::createFromFormat('d-m-Y', $request->fecha_fin)->format('Y-m-d'),
            ]);

            $fechaInicio = $request->fecha_inicio;
            $fechaFin = $request->fecha_fin;

            // Comprobar si existe un rango de fechas en la base de datos que se superponga con las nuevas fechas segun proyecto y subproyecto
            $fechasExistentes = ManoObra::where('etapa_id', $request->tipo_adquisicion)
                ->where('proyecto_id', $request->proyecto_id)
                ->when($request->filled('subproyecto'), function ($query) use ($request) {
                    $subproyecto = trim(mb_strtolower($request->subproyecto));
                    $query->whereRaw('LOWER(TRIM(subproyecto)) = ?', [$subproyecto]);
                })
                ->where(function ($query) use ($fechaInicio, $fechaFin) {
                    $query->whereBetween('fecha_inicio', [$fechaInicio, $fechaFin])
                        ->orWhereBetween('fecha_fin', [$fechaInicio, $fechaFin])
                        ->orWhere(function ($query) use ($fechaInicio, $fechaFin) {
                            $query->where('fecha_inicio', '<=', $fechaInicio)
                                ->where('fecha_fin', '>=', $fechaFin);
                        });
                })->exists();

            if ($fechasExistentes) {
                throw ValidationException::withMessages([
                    'fecha_inicio' => 'La fecha inicio esta dentro del rago ya registrado.',
                    'fecha_fin' => 'La fecha fin esta dentro del rago ya registrado.',
                ]);
            }

            $request->validate([
                'fecha_inicio' => [
                    'required',
                    'date',
                    Rule::unique('mano_obra', 'fecha_inicio')->where(function ($query) use ($request) {
                        return $query->where('etapa_id', $request->tipo_adquisicion)
                            ->where('proyecto_id', $request->proyecto_id)
                            ->when($request->filled('subproyecto'), function ($query) use ($request) {
                                $subproyecto = trim(mb_strtolower($request->subproyecto));
                                $query->whereRaw('LOWER(TRIM(subproyecto)) = ?', [$subproyecto]);
                            });
                    }),
                ],
                'fecha_fin' => [
                    'required',
                    'date',
                    'after:fecha_inicio',
                    Rule::unique('mano_obra', 'fecha_fin')->where(function ($query) use ($request) {
                        return $query->where('etapa_id', $request->tipo_adquisicion)
                            ->where('proyecto_id', $request->proyecto_id)
                            ->when($request->filled('subproyecto'), function ($query) use ($request) {
                                $subproyecto = trim(mb_strtolower($request->subproyecto));
                                $query->whereRaw('LOWER(TRIM(subproyecto)) = ?', [$subproyecto]);
                            });
                    }),
                ],
            ], [
                'fecha_fin.after' => 'La fecha de fin de ser mayor a la fecha de inicio.',
                'fecha_inicio.unique' => 'La fecha inicio ya se encuentra registrada.',
                'fecha_fin.unique' => 'La fecha fin ya se encuentra registrada.'
            ]);

            try {
                DB::beginTransaction();
                $mano_obra = [
                    'semana' => $semana + 1,
                    'fecha_inicio' => $fechaInicio,
                    'fecha_fin' => $fechaFin,
                    'proyecto_id' => $request->proyecto_id,
                    'etapa_id' => $request->tipo_adquisicion,
                    'tipo_etapa_id' => $request->tipo_etapa,
                    'usuario_id' => Auth::user()->id,
                    'actividad_id' => null,
                    'subproyecto' => $request->subproyecto,
                ];

                if (is_numeric($request->actividad)) {
                    $mano_obra['actividad_id'] = $request->actividad;
                } elseif (!is_null($request->actividad)) {
                    $create_actividad = newChildrenCatalogoDatos($request->actividad, 'actividad.acabados');
                    $mano_obra['actividad_id'] = $create_actividad;
                }

                if (ManoObra::create($mano_obra)) {
                    DB::commit();
                    $list_mano_obra = ManoObra::where('proyecto_id', $request->proyecto_id)
                        ->where('etapa_id', $request->tipo_adquisicion)
                        ->orderBy('semana', 'desc')
                        ->paginate(15);

                    $route_params = $this->getRouteParameters($request);
                    $response = $this->htmlTable($list_mano_obra, $route_params);

                    LogService::log('info', 'Se creo planificaciòn mano de obra', ['user_id' => auth()->id(), 'action' => 'create']);
                    return response()->json(['success' => true, 'mensaje' => 'Planificación creada correctamente.', 'planificacion' => $response]);
                }

                DB::rollBack();
                LogService::log('error', 'Error al crear planificaciòn mano de obra', ['user_id' => auth()->id(), 'action' => 'create', 'message' => 'no se creo planificacion']);
                return response()->json(['success' => false, 'mensaje' => 'Ocurrió un error por favor vuelva a intentarlo.']);
            } catch (Throwable $e) {
                DB::rollBack();
                return $e->getMessage();

                LogService::log('error', 'Error al crear planificaciòn mano de obra', ['user_id' => auth()->id(), 'action' => 'create', 'message' => $e->getMessage()]);
                return response()->json(['success' => false, 'mensaje' => 'Ocurrió un error inesperado, comuníquese con el administrador del sistema.']);
            }
        }
    }

    public function updatePlanificacion(Request $request)
    {

        if ($request->ajax()) {
            // 1. Encuentra el registro a actualizar
            $mano_obra = ManoObra::find($request->id);

            // 2. Prepara las fechas para la validación y actualización
            // Se asume que fecha_inicio no cambia en la actualización
            $fechaInicio = $mano_obra->fecha_inicio;
            // Se formatea la fecha_fin que viene del request
            $fechaFin = Carbon::createFromFormat('d-m-Y', $request->fecha_fin)->format('Y-m-d');

            // ====================================================================
            //  NUEVA Y MEJORADA LÓGICA DE VALIDACIÓN DE SUPERPOSICIÓN
            // ====================================================================
            $fechasSuperpuestas = ManoObra::where('id', '!=', $mano_obra->id)->where('proyecto_id', $request->proyecto_id) // Ignora el registro actual
                ->where(function ($query) use ($fechaInicio, $fechaFin) {
                    // La condición mágica: (A_inicio <= B_fin) Y (A_fin >= B_inicio)
                    // Se traduce a:
                    // El inicio de un registro existente es ANTES que nuestro fin
                    // Y el fin de ese mismo registro es DESPUÉS que nuestro inicio.
                    $query->where('fecha_inicio', '<=', $fechaFin)
                        ->where('fecha_fin', '>=', $fechaInicio);
                })->exists();

            if ($fechasSuperpuestas) {
                // Lanza una excepción de validación que Laravel captura y formatea como JSON.
                throw ValidationException::withMessages([
                    'fecha_fin' => 'El rango de fechas seleccionado se superpone con una planificación existente.',
                ]);
            }

            // Validación adicional (que también podría estar en un FormRequest)
            // La regla 'unique' aquí es redundante si la validación de superposición ya se hizo.
            // La mantendré por si quieres validar un caso muy específico, pero considera quitarla.
            $request->validate([
                'fecha_fin' => [
                    'required',
                    'date',
                    // 'after:fecha_inicio' no funciona bien aquí porque fecha_inicio no está en el request.
                    // Lo validaremos manualmente.
                ],
            ], [
                'fecha_fin.required' => 'La fecha de fin es obligatoria.',
            ]);

            if (Carbon::parse($fechaFin)->lessThanOrEqualTo(Carbon::parse($fechaInicio))) {
                throw ValidationException::withMessages([
                    'fecha_fin' => 'La fecha de fin debe ser posterior a la fecha de inicio.',
                ]);
            }

            // ====================================================================
            //  LÓGICA DE ACTUALIZACIÓN (TRY-CATCH)
            // ====================================================================
            try {
                DB::beginTransaction();

                $mano_obra->fecha_fin = $fechaFin;
                $mano_obra->subproyecto = $request->subproyecto;
                $mano_obra->actividad_id = $request->actividad;

                if ($mano_obra->save()) {
                    DB::commit();
                    $list_mano_obra = ManoObra::where('proyecto_id', $request->proyecto_id)
                        ->orderBy('semana', 'desc')
                        ->paginate(15);

                    $route_params = $this->getRouteParameters($request);
                    $response = $this->htmlTable($list_mano_obra, $route_params);

                    LogService::log('info', 'Se actualizo la planificación mano de obra', ['user_id' => auth()->id(), 'action' => 'update']);
                    return response()->json(['success' => true, 'mensaje' => 'Planificación actualizada correctamente.', 'planificacion' => $response]);
                }

                DB::rollBack();
                LogService::log('error', 'Error al actualizar planificaciòn mano de obra', ['user_id' => auth()->id(), 'action' => 'update', 'message' => 'no se actualizo planificacion']);
                return response()->json(['success' => false, 'mensaje' => 'Ocurrió un error por favor vuelva a intentarlo.']);
            } catch (Throwable $e) {
                DB::rollBack();
                LogService::log('error', 'Error al actualizar planificación mano de obra', ['user_id' => auth()->id(), 'action' => 'update', 'message' => $e->getMessage()]);
                // En desarrollo, puedes usar: return response()->json(['message' => $e->getMessage()], 500);
                return response()->json(['success' => false, 'mensaje' => 'Ocurrió un error inesperado, comuníquese con el administrador del sistema.']);
            }
        }
    }

    public function registroTrabajadores(Request $request)
    {
        /*// Obtener la fecha actual
        $fecha_actual = Carbon::today();
        $fechaDB = Carbon::parse($mano_obra->fecha_inicio); // Parsear la fecha si es un string
        if ($fechaDB->eq($fecha_actual)) {
            //return back()->with(['sweetalert' => true, 'title' => 'Error', 'message' => 'Ya existe una plaficicacion para este dia.', 'icon' => 'error']);
        }
        */
        $mano_obra = ManoObra::find($request->mano_obra);
        // Obtener la fecha actual
        $fecha_actual = Carbon::now()->format('Y-m-d');
        // Obtener el ultimo registro para saber la fecha
        $ultimo_registro = DetalleManoObra::where('mano_obra_id', $request->mano_obra)
            ->orderBy('fecha', 'desc')
            ->first();

        $fecha_anterior = isset($ultimo_registro->fecha) ? $ultimo_registro->fecha : $fecha_actual;
        /*
          $mano_obra = ManoObra::whereHas('detalle_mano_obra', function ($query) use ($fecha_actual) {
            $query->where('fecha', $fecha_actual);
        })->where('id', $request->mano_obra)->get();
         */


        // Comprobar si existe un rango de fechas en la base de datos que se superponga con las nuevas fechas
        $permiso = ManoObra::where('id', $request->mano_obra)
            ->where(function ($query) use ($fecha_actual) {
                $query->where('fecha_inicio', '<=', $fecha_actual)
                    ->where('fecha_fin', '>=', $fecha_actual);
            })->first();

        if (!$permiso) {
            return back()->with(['sweetalert' => true, 'title' => 'Error', 'message' => 'No puede agregar personal a esta planificación porque está fuera del rango de fechas planificadas.', 'icon' => 'error']);
        }

        $title_page = 'Mano de obra - Nuevo';
        $route_params = $this->getRouteParameters($request);

        $breadcrumbs = [
            ['name' => 'Inicio', 'url' => route('home')],
            ['name' => 'Mano de Obra', 'url' => route('proyecto.adquisiciones.mano.obra', ['tipo' => $request->route('tipo'), 'tipo_id' => $request->route('tipo_id'), 'proyecto' => $request->route('proyecto'), 'tipo_adquisicion' => $request->route('tipo_adquisicion'), 'tipo_etapa' => $request->route('tipo_etapa')])],
            ['name' => 'Planificación', 'url' => '']
        ];

        $proveedores = Proveedor::where('categoria_proveedor_id', $route_params['tipo_etapa']->id)->pluck('razon_social', 'id');


        $route_params = array_merge($route_params, ['mano_obra' => $mano_obra, 'fecha_actual' => $fecha_actual, 'fecha_anterior' => $fecha_anterior, 'proveedores' => $proveedores, 'breadcrumbs' => $breadcrumbs, 'title_page' => $title_page]);
        return view('mano_obra.create', $route_params);
    }

    /**
     * Editar la planificacion de trabajadores de fecha selecionada
     */
    public function editarPlanificacionTrabajadores(Request $request)
    {
        // Obtener la fecha actual
        $fecha = $request->fecha;
        $mano_obra = ManoObra::find($request->mano_obra);

        $title_page = 'Mano de obra - Editar';
        $route_params = $this->getRouteParameters($request);

        $breadcrumbs = [
            ['name' => 'Inicio', 'url' => route('home')],
            ['name' => 'Mano de Obra', 'url' => route('proyecto.adquisiciones.mano.obra', ['tipo' => $request->route('tipo'), 'tipo_id' => $request->route('tipo_id'), 'proyecto' => $request->route('proyecto'), 'tipo_adquisicion' => $request->route('tipo_adquisicion'), 'tipo_etapa' => $request->route('tipo_etapa')])],
            ['name' => 'Planificación', 'url' => '']
        ];

        $proveedores = Proveedor::where('categoria_proveedor_id', $route_params['tipo_etapa']->id)->pluck('razon_social', 'id');


        $route_params = array_merge($route_params, ['mano_obra' => $mano_obra, 'fecha_actual' =>  $fecha, 'fecha_anterior' =>  $fecha, 'proveedores' => $proveedores, 'breadcrumbs' => $breadcrumbs, 'title_page' => $title_page]);
        return view('mano_obra.create', $route_params);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request)
    {
        $id = $request->mano_obra;
        $mano_obra = ManoObra::find($id);

        if (!$mano_obra) {
            return response()->json(['success' => false, 'message' => 'No se pudo encontrar el registro']);
        }

        try {
            DB::beginTransaction();

            // Validar si la planificacion ya esta pagada por administrativo
            $mano_obra = ManoObra::where('id', $id)->lockForUpdate()->first();
            $manoObraPagado = PagoManoObra::where('mano_obra_id', $id)->exists();
            if ($manoObraPagado) {
                DB::rollBack();
                return response()->json(['success' => false, 'message' => 'No es posible eliminar la planificación porque esta ya se encuentra pagada.']);
            }

            // Eliminar los detalles asociados para no violar la llave foránea
            DetalleManoObra::where('mano_obra_id', $id)->delete();
            $is_delete = $mano_obra->delete();
            DB::commit();

            if ($is_delete) {
                LogService::log('info', 'Planificacion mano obra eliminado', ['user' => auth()->id(), 'action' => 'destroy ' . $id]);
                return response()->json(['success' => true, 'message' => 'Registro eliminado correctamente']);
            } else {
                return response()->json(['success' => false, 'message' => 'No se pudo eliminar el registro']);
            }
        } catch (Throwable $e) {
            DB::rollBack();
            LogService::log('error', 'Error al eliminar planificacion mano de obra', ['user' => auth()->id(), 'action' => 'destroy ' . $id, 'message' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'Ocurrió un error inesperado, comuníquese con el administrador del sistema.']);
        }
    }

    public function buscarPlanificacion(Request $request)
    {
        $buscar = $request->text;
        $proyecto_id = $request->proyecto;
        $etapa_id = $request->tipo_adquisicion;
        $tipo_adquisicion_id =  $request->tipo_etapa;

        $list_planificacion = ManoObra::with(['proyecto', 'etapa', 'tipo_etapa'])
            ->where('proyecto_id', $proyecto_id)
            ->where('etapa_id', $etapa_id)
            ->where('tipo_etapa_id', $tipo_adquisicion_id)
            ->where(function ($query) use ($buscar) {
                $query->where('fecha_inicio', 'LIKE', '%' . $buscar . '%')
                    ->orWhere('fecha_fin', 'LIKE', '%' . $buscar . '%');
            })
            ->orderBy('semana', 'asc')
            ->get();

        $route_params = $this->getRouteParameters($request);
        $output = $this->htmlTable($list_planificacion, $route_params);
        if (empty($output)) {
            $output .= '<tr>' .
                '<td colspan="6" class="text-center">' .
                '<span class="text-danger">No existen datos para mostrar.</span>' .
                '</td>' .
                '</tr>';
        }
        return Response($output);
    }

    private function getRouteParameters($request)
    {

        $tipo_etapa = is_numeric($request->route('tipo_etapa')) ? $request->route('tipo_etapa') : $request->tipo_etapa;

        $parametros = [
            'tipo' => $request->route('tipo'),
            'tipo_id' => $request->route('tipo_id'),
            'proyecto' => Proyecto::find($request->route('proyecto')),
            'tipo_adquisicion' => CatalogoDato::find($request->route('tipo_adquisicion')),
            'tipo_etapa' => CatalogoDato::find($tipo_etapa),
        ];

        return $parametros;
    }

    private function htmlTable($data, $route_params, $out = null)
    {
        foreach ($data as $mano_obra) {
            $route_params  = array_merge($route_params, ['mano_obra' => $mano_obra->id]);
            $nuevo = "<a href='" . route('proyecto.adquisiciones.mano.obra.agregar.trabajadores', $route_params) . "' class='dropdown-item'>Agregar Personal</a>";
            $editar = "<a href='javascriopt:void(0);' class='dropdown-item editar-empleados-mano-obra' id='" . $mano_obra->id . "'>Editar</a>";
            $eliminar = "<a href='#' class='dropdown-item eliminar-planificacion' id='" . $mano_obra->id . "'>Eliminar</a>";
            $pdf = "<a href='" . route('pdf.planificacion.mano.obra', $mano_obra->id) . "' class='dropdown-item' target='_blank'>PDF Planificación</a>";

            $tipo = $mano_obra->actividad->descripcion ?? $mano_obra->tipo_etapa->descripcion;

            $out .= '<tr id="' . $mano_obra->id . '">' .
                '<td class="align-middle">' . $mano_obra->semana . '</td>' .
                '<td class="align-middle editar-fecha-planificacion" style="cursor: pointer" data-fecha-inicio ="' . $mano_obra->fecha_inicio . '" data-fecha-fin = "' . $mano_obra->fecha_fin . '" >' . dateFormatHumansManoObra($mano_obra->fecha_inicio, $mano_obra->fecha_fin) . '</td>' .
                '<td class="align-middle">' . $mano_obra->proyecto->nombre_proyecto . '</td>' .
                '<td class="align-middle">' . $mano_obra->etapa->descripcion . '</td>' .
                '<td class="align-middle">' . $tipo  . '</td>' .
                '<td class="align-middle align-middle text-right text-truncate">' .
                '<button type="button" class="btn btn-outline-dark" data-container="body" data-toggle="popover" data-placement="left" data-trigger="focus" data-content ="' . $nuevo . $editar . $eliminar . $pdf . '">
                                        <i class="fas fa-caret-left font-weight-normal"></i> Opciones
                                    </button>' .
                '</td>' .
                '</tr>';
        }

        return $out;
    }
}
