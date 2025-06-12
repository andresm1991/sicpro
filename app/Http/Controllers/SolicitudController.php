<?php

namespace App\Http\Controllers;

use App\Constants\MessagesConstant;
use App\Enums\PushNotificationsEnum;
use App\Http\Requests\SolicitudStoreRequest;
use App\Models\CatalogoDato;
use App\Models\EventualidadUsuario;
use App\Models\Solicitud;
use App\Models\User;
use App\Services\LogService;
use App\Services\PushNotificationService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Throwable;

class SolicitudController extends Controller
{
    public function index()
    {
        $title_page = 'Comunicación';

        $breadcrumbs = [
            ['name' => 'Inicio', 'url' => route('home')],
            ['name' => $title_page, 'url' => ''] // Último breadcrumb no tiene URL, es el actual
        ];

        return view('solicitudes.opciones', compact('title_page', 'breadcrumbs'));
    }

    public function permisos()
    {
        $title_page = 'Ausencias';
        $solicitudes = Solicitud::getSolicitudesPorUsuario('tipo.solicitudes.ausencia');

        $breadcrumbs = [
            ['name' => 'Inicio', 'url' => route('home')],
            ['name' => 'Opciones', 'url' => route('solicitud.index')],
            ['name' => $title_page, 'url' => ''] // Último breadcrumb no tiene URL, es el actual
        ];

        return view('solicitudes.index', compact('title_page', 'breadcrumbs', 'solicitudes'));
    }

    public function create()
    {
        $title_page = 'Nueva Solicitud';
        $breadcrumbs = [
            ['name' => 'Inicio', 'url' => route('home')],
            ['name' => 'Solicitudes', 'url' => route('solicitud.permisos.index')],
            ['name' => $title_page, 'url' => ''] // Último breadcrumb no tiene URL, es el actual
        ];

        $solicitud = new Solicitud();
        $tipo_solicitudes = CatalogoDato::getChildrenCatalogo('tipo.solicitudes')->pluck('descripcion', 'id');
        $estados_solicitud = CatalogoDato::getChildrenCatalogo('estados.solicitud')->pluck('descripcion', 'id');
        $users = User::getUsusarios()->pluck('nombre', 'id');
        //$tipo_solicitud = $tipo_solicitudes->prepend('', '');

        return view('solicitudes.create', compact('title_page', 'breadcrumbs', 'solicitud', 'users', 'tipo_solicitudes', 'estados_solicitud'));
    }

    public function store(SolicitudStoreRequest $request)
    {
        try {
            DB::beginTransaction();
            $user = $request->user;
            $tipo_solicitud = $request->tipo_solicitud;
            $estado_solicitud = $request->estado_solicitud ?? CatalogoDato::getIdCatalogo('estados.solicitud.pendiente');
            $solicitud = CatalogoDato::find($tipo_solicitud);

            $fecha_desde = Carbon::createFromFormat('d-m-Y', $request->fecha_desde)->format('Y-m-d');
            $hora_desde = $request->hora_inicio;
            $fecha_hasta = Carbon::createFromFormat('d-m-Y', $request->fecha_hasta)->format('Y-m-d');
            $hora_hasta = $request->hora_fin;
            $total_horas = calcularTiempoTotal($fecha_desde, $hora_desde, $fecha_hasta, $hora_hasta);


            $detalle = $request->detalle;
            $recuperable = $request->recuperable ? true : false;

            $store_solicitud = Solicitud::create([
                'usuario_id' => $user,
                'fecha_solicitud' => date('Y-m-d'),
                'fecha_desde' => $fecha_desde,
                'fecha_hasta' => $fecha_hasta,
                'hora_desde' => $hora_desde,
                'hora_hasta' => $hora_hasta,
                'total_tiempo' => $total_horas,
                'tipo_id' => CatalogoDato::getIdCatalogo('tipo.solicitudes.ausencia'),
                'estado_id' => $estado_solicitud,
                'recuperable' => $recuperable,
                'detalle' => $detalle
            ]);

            DB::commit();

            PushNotificationService::sendNotification(PushNotificationsEnum::ADMINISTRATIVO, "Solicitud", "Se genero una solicitud de {$store_solicitud->tipo_solicitud->descripcion} para el colaborador {$store_solicitud->usuario->nombre}", route('solicitud.permisos.show', $store_solicitud->id));

            return redirect()->route('solicitud.permisos.create')->with('success', MessagesConstant::INSERT);
        } catch (Throwable $e) {
            DB::rollBack();
            LogService::log('ERROR', 'Error al crear solicitud', ['execption' => $e, 'message' => $e->getMessage()]);
            return redirect()->route('solicitud.permisos.create')->with('success', MessagesConstant::DEFAUL_ERROR);
        }
    }

    public function edit(Solicitud $solicitud)
    {
        $title_page = 'Detalle Solicitud';
        $breadcrumbs = [
            ['name' => 'Inicio', 'url' => route('home')],
            ['name' => 'Solicitudes', 'url' => route('solicitud.permisos.index')],
            ['name' => $title_page, 'url' => ''] // Último breadcrumb no tiene URL, es el actual
        ];

        $tipo_solicitudes = CatalogoDato::getChildrenCatalogo('tipo.solicitudes')->pluck('descripcion', 'id');
        $estados_solicitud = CatalogoDato::getChildrenCatalogo('estados.solicitud')->pluck('descripcion', 'id');
        $users = User::getUsusarios()->pluck('nombre', 'id');

        return view('solicitudes.edit', compact('title_page', 'breadcrumbs', 'solicitud', 'users', 'tipo_solicitudes', 'estados_solicitud'));
    }

    public function update(Request $request, Solicitud $solicitud)
    {
        try {
            DB::beginTransaction();
            $solicitud->estado_id = $request->estado_solicitud;
            $solicitud->fecha_desde = Carbon::createFromFormat('d-m-Y', $request->fecha_desde)->format('Y-m-d');
            $solicitud->fecha_hasta = Carbon::createFromFormat('d-m-Y', $request->fecha_hasta)->format('Y-m-d');
            $solicitud->hora_desde = $request->hora_inicio;
            $solicitud->hora_hasta = $request->hora_fin;
            $solicitud->detalle = $request->detalle;
            $solicitud->recuperable = $request->recuperable ? true : false;

            $solicitud->save();
            DB::commit();

            return redirect()->route('solicitud.permisos.edit', $solicitud->id)->with('success', MessagesConstant::UPDATE);
        } catch (Throwable $e) {
            LogService::log('ERROR', 'Error al actualizar solicitud de permiso', ['message' => $e->getMessage()]);
            return redirect()->back()->with('error', MessagesConstant::DEFAUL_ERROR);
        }
    }

    public function show(Solicitud $solicitud)
    {
        $title_page = 'Detalle Solicitud';

        $breadcrumbs = [
            ['name' => 'Inicio', 'url' => route('home')],
            ['name' => 'Solicitudes', 'url' => route('solicitud.permisos.index')],
            ['name' => $title_page, 'url' => ''] // Último breadcrumb no tiene URL, es el actual
        ];

        return view('solicitudes.show', compact('title_page', 'breadcrumbs', 'solicitud'));
    }

    public function destroy(Request $request)
    {
        if ($request->ajax()) {
            $id = $request->solicitud;
            $delete = Solicitud::find($id)->delete();
            if ($delete) {
                return response()->json(['success' => true, 'message' => MessagesConstant::DELETE]);
            } else {
                return response()->json(['success' => false, 'message' => MessagesConstant::DEFAUL_ERROR]);
            }
        }
    }

    /**
     * EVENTUALIDADES
     */

    public function eventualidad()
    {
        $title_page = 'Eventualidades';
        $solicitudes = Solicitud::getSolicitudesPorUsuario('tipo.solicitudes.eventualidad');

        $breadcrumbs = [
            ['name' => 'Inicio', 'url' => route('home')],
            ['name' => 'Opciones', 'url' => route('solicitud.index')],
            ['name' => $title_page, 'url' => ''] // Último breadcrumb no tiene URL, es el actual
        ];

        return view('solicitudes.eventualidad.index', compact('title_page', 'breadcrumbs', 'solicitudes'));
    }

    public function createEventualidad()
    {
        $title_page = 'Nueva Eventualidad';
        $breadcrumbs = [
            ['name' => 'Inicio', 'url' => route('home')],
            ['name' => 'Eventualidades', 'url' => route('solicitud.eventualidad.index')],
            ['name' => $title_page, 'url' => ''],
        ];
        $solicitud = new Solicitud();
        $estados_solicitud = CatalogoDato::getChildrenCatalogo('estados.solicitud')->pluck('descripcion', 'id');
        $users = User::where('activo', true)->where('id', '!=', auth()->user()->id)->pluck('nombre', 'id');

        return view('solicitudes.eventualidad.create', compact('title_page', 'breadcrumbs', 'solicitud', 'users', 'estados_solicitud'));
    }

    public function storeEventualidad(Request $request)
    {
        $request->validate(
            [
                'users' => 'required|array',
                'detalle' => 'required',
            ],
            [
                'users.required' => 'Seleccione al menos un colaborador',
                'detalle.required' => 'Ingrese el detalle de la eventualidad'
            ]
        );

        try {

            DB::beginTransaction();
            $users = $request->input('users', []);
            $estado_solicitud = $request->estado_solicitud ?? CatalogoDato::getIdCatalogo('estados.solicitud.pendiente');

            $fecha_desde = date('Y-m-d');
            $hora_desde = '08:00';
            $fecha_hasta = date('Y-m-d');
            $hora_hasta = '08:00';
            $total_horas = '0:00';

            $detalle = $request->detalle;
            $recuperable = false;

            $store_solicitud = Solicitud::create([
                'usuario_id' => auth()->user()->id,
                'fecha_solicitud' => date('Y-m-d'),
                'fecha_desde' => $fecha_desde,
                'fecha_hasta' => $fecha_hasta,
                'hora_desde' => $hora_desde,
                'hora_hasta' => $hora_hasta,
                'total_tiempo' => $total_horas,
                'tipo_id' => CatalogoDato::getIdCatalogo('tipo.solicitudes.eventualidad'),
                'estado_id' => $estado_solicitud,
                'recuperable' => $recuperable,
                'detalle' => $detalle
            ]);

            foreach ($users as $user) {
                EventualidadUsuario::create([
                    'solicitud_id' => $store_solicitud->id,
                    'usuario_id' => $user,
                ]);
            }

            DB::commit();

            PushNotificationService::sendNotification(PushNotificationsEnum::EVENTUALIDAD, "Solicitud", "Se genero una solicitud de {$store_solicitud->tipo_solicitud->descripcion} para el colaborador {$store_solicitud->usuario->nombre}", route('solicitud.eventualidad.show', $store_solicitud->id), $users);

            return redirect()->route('solicitud.eventualidad.index')->with('success', MessagesConstant::INSERT);
        } catch (Throwable $e) {
            DB::rollBack();
            LogService::log('ERROR', 'Error al crear solicitud', ['execption' => $e, 'message' => $e->getMessage()]);
            return redirect()->back()->with('success', MessagesConstant::DEFAUL_ERROR);
        }
    }

    public function editEventualidad(Solicitud $solicitud)
    {
        $title_page = 'Detalle Eventualidad';
        $breadcrumbs = [
            ['name' => 'Inicio', 'url' => route('home')],
            ['name' => 'Eventualidades', 'url' => route('solicitud.eventualidad.index')],
            ['name' => $title_page, 'url' => ''] // Último breadcrumb no tiene URL, es el actual
        ];

        $estados_solicitud = CatalogoDato::getChildrenCatalogo('estados.solicitud')->pluck('descripcion', 'id');
        $users = User::where('activo', true)->where('id', '!=', auth()->user()->id)->pluck('nombre', 'id');

        return view('solicitudes.eventualidad.edit', compact('title_page', 'breadcrumbs', 'solicitud', 'users', 'estados_solicitud'));
    }

    public function updateEventualidad(Request $request, Solicitud $solicitud)
    {
        $request->validate(
            [
                'users' => 'required|array',
                'detalle' => 'required',
            ],
            [
                'users.required' => 'Seleccione al menos un colaborador',
                'detalle.required' => 'Ingrese el detalle de la eventualidad'
            ]
        );

        try {
            DB::beginTransaction();
            $solicitud->estado_id = $request->estado_solicitud;
            $solicitud->detalle = $request->detalle;

            $solicitud->save();
            DB::commit();

            PushNotificationService::sendNotification(PushNotificationsEnum::EVENTUALIDAD, "Eventualidad", "La eventualidad fue {$solicitud->estado_solicitud->descripcion}", route('solicitud.eventualidad.show', $solicitud->id), [$solicitud->usuario_id]);

            return redirect()->route('solicitud.eventualidad.index')->with('success', MessagesConstant::UPDATE);
        } catch (Throwable $e) {
            LogService::log('ERROR', 'Error al actualizar solicitud de permiso', ['message' => $e->getMessage()]);
            return redirect()->back()->with('error', MessagesConstant::DEFAUL_ERROR);
        }
    }
    public function showEventualidad(Solicitud $solicitud)
    {
        $title_page = 'Detalle Eventualidad';

        $breadcrumbs = [
            ['name' => 'Inicio', 'url' => route('home')],
            ['name' => 'Eventualidades', 'url' => route('solicitud.eventualidad.index')],
            ['name' => $title_page, 'url' => ''] // Último breadcrumb no tiene URL, es el actual
        ];

        return view('solicitudes.eventualidad.show', compact('title_page', 'breadcrumbs', 'solicitud'));
    }

    public function buscar(Request $request)
    {
        if ($request->ajax()) {
            $buscar = $request->text;
            $tipo = $request->tipo == 'ausencia' ? 'tipo.solicitudes.ausencia' : 'tipo.solicitudes.eventualidad';
            $output = "";

            // Construir la consulta
            $query = Solicitud::buscarAusencias($buscar, $tipo);
            $solicitudes = $query->orderBy('fecha_solicitud', 'desc')->get();

            if ($solicitudes) {
                if ($request->tipo == 'ausencia') {
                    foreach ($solicitudes as $solicitud) {
                        $recuperable = $solicitud->recuperable ? 'SI' : 'NO';
                        $editar = "<a href='" . route('solicitud.permisos.edit', $solicitud->id) . "' class='dropdown-item'>Editar</a>";
                        $eliminar = "<a href='#' class='dropdown-item eliminar-solicitud' id='" . $solicitud->id . "'>Eliminar</a>";

                        if ($solicitud->estado_solicitud->descripcion == 'Aprobado') {
                            $editar = "<a href='" . route('solicitud.permisos.show', $solicitud->id) . "' class='dropdown-item'>Detalle</a>";
                        }

                        $output .= '<tr id="' . $solicitud->id . '">' .
                            '<td class="align-middle text-capitalize">' . $solicitud->id . '</td>' .
                            '<td class="align-middle text-capitalize">' . $solicitud->usuario->nombre . '</td>' .
                            '<td class="align-middle">' . dateFormatHumans($solicitud->fecha_solicitud) . '</td>' .
                            '<td class="align-middle">' . $recuperable . '</td>' .
                            '<td class="align-middle">' . $solicitud->estado_solicitud->descripcion . '</td>' .
                            '<td class="align-middle">' .
                            '<div class="btn-group dropleft">' .
                            '<button type="button" class="btn btn-secondary dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"> Opciones </button>' .
                            '<div class="dropdown-menu">' . $editar . $eliminar . '</div>' .
                            '</div>' .
                            '</td>' .
                            '</tr>';
                    }

                    if (empty($output)) {
                        $output .= '<tr>' .
                            '<td colspan="7" class="text-center">' .
                            '<span class="text-danger">No existen datos para mostrar.</span>' .
                            '</td>' .
                            '</tr>';
                    }
                } else {
                    foreach ($solicitudes as $solicitud) {
                        $editar = "<a href='" . route('solicitud.eventualidad.edit', $solicitud->id) . "' class='dropdown-item'>Editar</a>";
                        $eliminar = "<a href='#' class='dropdown-item eliminar-solicitud' id='" . $solicitud->id . "'>Eliminar</a>";

                        if ($solicitud->usuariosEventualidad->count()) {
                            $colaboradores = $solicitud->usuariosEventualidad->pluck('usuario.nombre')->join(', ');
                        } else {
                            $colaboradores = '<span class="text-muted">Sin colaboradores</span>';
                        }

                        if ($solicitud->estado_solicitud->descripcion == 'Aprobado') {
                            $editar = "<a href='" . route('solicitud.eventualidad.show', $solicitud->id) . "' class='dropdown-item'>Detalle</a>";
                        }

                        $output .= '<tr id="' . $solicitud->id . '">' .
                            '<td class="align-middle">' . $solicitud->id . '</td>' .
                            '<td class="align-middle">' . $solicitud->usuario->nombre . '</td>' .
                            '<td class="align-middle">' . $colaboradores . '</td>' .
                            '<td class="align-middle">' . dateFormatHumans($solicitud->fecha_solicitud) . '</td>' .
                            '<td class="align-middle">' . $solicitud->estado_solicitud->descripcion . '</td>' .
                            '<td class="align-middle">' .
                            '<div class="btn-group dropleft">' .
                            '<button type="button" class="btn btn-secondary dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"> Opciones </button>' .
                            '<div class="dropdown-menu">' . $editar . $eliminar . '</div>' .
                            '</div>' .
                            '</td>' .
                            '</tr>';
                    }

                    if (empty($output)) {
                        $output .= '<tr>' .
                            '<td colspan="6" class="text-center">' .
                            '<span class="text-danger">No existen datos para mostrar.</span>' .
                            '</td>' .
                            '</tr>';
                    }
                }

                return Response($output);
            }
        }
    }
}