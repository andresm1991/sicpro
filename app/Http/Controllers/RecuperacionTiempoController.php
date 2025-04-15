<?php

namespace App\Http\Controllers;

use App\Constants\MessagesConstant;
use App\Http\Requests\ReposicionTiempoUpdateRequest;
use App\Models\CatalogoDato;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Solicitud;
use Illuminate\Http\Request;
use App\Models\ReposicionTiempo;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Services\PushNotificationService;

class RecuperacionTiempoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $title_page = 'Reposición';
        // Obtener las solicitudes agrupadas por usuario
        $solicitudeUsuarios = Solicitud::getSolicitudesConReposiciones();

        $breadcrumbs = [
            ['name' => 'Inicio', 'url' => route('home')],
            ['name' => 'Opciones', 'url' => route('solicitud.index')],
            ['name' => $title_page, 'url' => ''] // Último breadcrumb no tiene URL, es el actual
        ];

        return view('reposicion_tiempo.index', compact('title_page', 'breadcrumbs', 'solicitudeUsuarios'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $title_page = 'Nueva Solicitud';

        $breadcrumbs = [
            ['name' => 'Inicio', 'url' => route('home')],
            ['name' => 'Reposicones', 'url' => route('solicitud.reposicion.index')],
            ['name' => $title_page, 'url' => ''] // Último breadcrumb no tiene URL, es el actual
        ];


        $reposicion = new ReposicionTiempo();

        $users = User::getUsusarios()->pluck('nombre', 'id');


        return view('reposicion_tiempo.create', compact('title_page', 'breadcrumbs', 'reposicion', 'users'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            DB::beginTransaction();
            $user_id = $request->user;
            $fecha_reposicion =  Carbon::createFromFormat('d-m-Y', $request->fecha)->format('Y-m-d');
            $hora_desde = $request->hora_inicio;
            $hora_hasta = $request->hora_fin;
            $total_resposicion = calcularTiempoTotal($fecha_reposicion, $hora_desde, $fecha_reposicion, $hora_hasta);

            $solicitud_id = ReposicionTiempo::create([
                'usuario_id' => $user_id,
                'fecha' => $fecha_reposicion,
                'hora_desde' => $hora_desde,
                'hora_hasta' => $hora_hasta,
                'total' => $total_resposicion,
                'detalle' => $request->detalle,
                'estado_id' => $request->estado ?? CatalogoDato::getIdCatalogo('estados.solicitud.pendiente'),
            ])->id;

            DB::commit();
            PushNotificationService::sendNotification(Auth::user(), 'Reposición de tiempo', "Se registro reposición de tiempo para el colaborador " . Auth::user()->nombre, route('solicitud.reposicion.edit', $solicitud_id));
            return redirect()->back()->with('success', MessagesConstant::INSERT . ' Pendiente de aprobación');
        } catch (\Throwable $th) {
            DB::rollBack();
            return redirect()->back()->with('error', MessagesConstant::DEFAUL_ERROR);
        }
    }

    /**
     * Mostrar las solicitudes de reposiciones realizadas por el usuario
     * @param User $usuario
     */
    public function detalleSolicitudesReposicion(User $usuario)
    {
        $title_page = 'Solicitudes Reposición de tiempo';

        $breadcrumbs = [
            ['name' => 'Inicio', 'url' => route('home')],
            ['name' => 'Reposicones', 'url' => route('solicitud.reposicion.index')],
            ['name' => $title_page, 'url' => ''] // Último breadcrumb no tiene URL, es el actual
        ];


        $solicitudesReposicion = ReposicionTiempo::where('usuario_id', $usuario->id)->orderBy('fecha', 'desc')->paginate(15);


        return view('reposicion_tiempo.list_solicitudes', compact('title_page', 'breadcrumbs', 'solicitudesReposicion', 'usuario'));
    }

    /**
     * Editar la solicitud de reposicion de tiempo
     * @param ReposicionTiempo $solicitud
     */
    public function editSolicitud(ReposicionTiempo $solicitud)
    {
        $title_page = 'Editar Solicitud';

        $breadcrumbs = [
            ['name' => 'Inicio', 'url' => route('home')],
            ['name' => 'Reposicones', 'url' => route('solicitud.reposicion.detalle', $solicitud->usuario_id)],
            ['name' => $title_page, 'url' => ''] // Último breadcrumb no tiene URL, es el actual
        ];


        $reposicion = $solicitud;

        $users = User::getUsusarios()->pluck('nombre', 'id');


        return view('reposicion_tiempo.edit', compact('title_page', 'breadcrumbs', 'reposicion', 'users'));
    }

    /**
     * Actualizar la solicitud de reposicion de tiempo
     */
    public function update(ReposicionTiempoUpdateRequest $request, ReposicionTiempo $solicitud)
    {
        try {
            DB::beginTransaction();
            $solicitud->usuario_id = $request->user;
            $solicitud->fecha = Carbon::createFromFormat('d-m-Y', $request->fecha)->format('Y-m-d');
            $solicitud->hora_desde = $request->hora_inicio;
            $solicitud->hora_hasta = $request->hora_fin;
            $solicitud->total = calcularTiempoTotal($solicitud->fecha, $solicitud->hora_desde, $solicitud->fecha, $solicitud->hora_hasta);
            $solicitud->detalle = $request->detalle;
            $solicitud->estado_id = $request->estado ?? CatalogoDato::getIdCatalogo('estados.solicitud.pendiente');
            $solicitud->save();

            DB::commit();

            return redirect()->route('solicitud.reposicion.edit', $solicitud->id)->with('success', MessagesConstant::UPDATE);
        } catch (\Throwable $e) {
            DB::rollBack();
            return redirect()->back()->with('error', MessagesConstant::DEFAUL_ERROR);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request)
    {
        try {
            DB::beginTransaction();
            $solicitud = ReposicionTiempo::find($request->solicitud);
            if ($solicitud->estado->slug == 'estados.solicitud.aprobado' && !auth()->user()->hasRole(['Administrador', 'Gerencial'])) {
                return response()->json(['success' => false, 'message' => 'No se puede eliminar la solicitud aprobada.']);
            }
            $solicitud->delete();
            DB::commit();
            return response()->json(['success' => true, 'message' => MessagesConstant::DELETE]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => MessagesConstant::DEFAUL_ERROR]);
        }
    }

    public function getAjaxTiempoPermisosUsuario(Request $request)
    {
        $user_id = $request->user_id;
        $total_tiempo = Solicitud::getTotalTiempoUsuario($user_id);

        return response()->json(['tiempo_total' => $total_tiempo]);
    }

    public function buscar(Request $request)
    {
        if ($request->ajax()) {
            $buscar = $request->text;
            $output = "";

            // Construir la consulta
            $solicitudeUsuarios = Solicitud::getSolicitudesConReposiciones($buscar);

            if ($solicitudeUsuarios) {
                foreach ($solicitudeUsuarios as $index => $recuperacion) {
                    $output .= '<tr id="' . $index . '">' .
                        '<td class="align-middle">' . $index + 1 . '</td>' .
                        '<td class="align-middle text-capitalize">' . $recuperacion->usuario->nombre . '</td>' .
                        '<td class="align-middle">' . $recuperacion->tiempo_formateado . '</td>' .
                        '<td class="align-middle">' . $recuperacion->suma_reposiciones_formateada . '</td>' .
                        '</tr>';
                }

                if (empty($output)) {
                    $output .= '<tr>' .
                        '<td colspan="4" class="text-center">' .
                        '<span class="text-danger">No existen datos para mostrar.</span>' .
                        '</td>' .
                        '</tr>';
                }
                return Response($output);
            }
        }
    }

    public function buscarSolicitud(Request $request)
    {
        if ($request->ajax()) {
            $buscar = $request->text;
            $output = "";

            // Construir la consulta
            $solicitudesReposicion = ReposicionTiempo::where('usuario_id', $request->usuario) // Filtrar por usuario_id
                ->when(!empty($buscar), function ($query) use ($buscar) {
                    $query->where(function ($q) use ($buscar) {
                        $q->where('fecha', 'LIKE', "%{$buscar}%") // Filtrar por fecha
                            ->orWhereHas('estado', function ($q) use ($buscar) {
                                $q->where('descripcion', 'LIKE', "%{$buscar}%"); // Filtrar por estado
                            });
                    });
                })
                ->orderBy('fecha', 'desc')
                ->get();

            if ($solicitudesReposicion) {
                foreach ($solicitudesReposicion as $index => $recuperacion) {
                    $editar = "<a href='" . route('solicitud.reposicion.edit', $recuperacion->id) . "' class='dropdown-item'>Editar</a>";
                    $eliminar = "<a href='#' class='dropdown-item eliminar-solicitid-reposicion' id='" . $recuperacion->id . "'>Eliminar</a>";

                    $output .= '<tr id="' . $recuperacion->id . '">' .
                        '<td class="align-middle">' . ($index + 1) . '</td>' .
                        '<td class="align-middle">' . $recuperacion->fecha . '</td>' .
                        '<td class="align-middle">' . $recuperacion->hora_desde . '</td>' .
                        '<td class="align-middle">' . $recuperacion->hora_hasta . '</td>' .
                        '<td class="align-middle">' . $recuperacion->total . '</td>' .
                        '<td class="align-middle">' . $recuperacion->estado->descripcion . '</td>' .
                        '<td class="align-middle align-middle text-right text-truncate">' .
                        '<button type="button" class="btn btn-outline-dark" data-container="body" data-toggle="popover" data-placement="left" data-trigger="focus" data-content="' . $editar . $eliminar . ' ">
                            <i class="fas fa-caret-left font-weight-normal"></i> Opciones
                        </button>' .
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
                return Response($output);
            }
        }
    }
}
