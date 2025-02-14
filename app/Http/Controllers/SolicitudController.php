<?php

namespace App\Http\Controllers;

use App\Constants\MessagesConstant;
use App\Http\Requests\SolicitudStoreRequest;
use App\Models\CatalogoDato;
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
        $title_page = 'Solicitudes';

        $breadcrumbs = [
            ['name' => 'Inicio', 'url' => route('home')],
            ['name' => $title_page, 'url' => ''] // Último breadcrumb no tiene URL, es el actual
        ];

        return view('solicitudes.opciones', compact('title_page', 'breadcrumbs'));
    }

    public function permisos()
    {
        $title_page = 'Pemirsos';
        $solicitudes = Solicitud::orderBy('fecha_solicitud', 'desc')->paginate(15);

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
        $users = User::where('activo', true)
            ->where('id', '>', 1)->pluck('nombre', 'id');
        $tipo_solicitudes = CatalogoDato::getChildrenCatalogo('tipo.solicitudes')->pluck('descripcion', 'id');
        $estados_solicitud = CatalogoDato::getChildrenCatalogo('estados.solicitud')->pluck('descripcion', 'id');

        return view('solicitudes.create', compact('title_page', 'breadcrumbs', 'solicitud', 'users', 'tipo_solicitudes', 'estados_solicitud'));
    }

    public function store(SolicitudStoreRequest $request)
    {
        $tipo = $request->tipo;
        try {
            DB::beginTransaction();
            $user = $request->user;
            $tipo_solicitud = $request->tipo_solicitud;
            $estado_solicitud = $request->estado_solicitud;
            $fecha_desde = Carbon::createFromFormat('d-m-Y', $request->fecha_desde)->format('Y-m-d');
            $hora_desde = $request->hora_inicio;
            $fecha_hasta = Carbon::createFromFormat('d-m-Y', $request->fecha_hasta)->format('Y-m-d');
            $hora_hasta = $request->hora_fin;
            $detalle = $request->detalle;
            $total_horas = calcularTiempoTotal($fecha_desde, $hora_desde, $fecha_hasta, $hora_hasta);
            $recuperable = $request->recuperable ? true : false;

            $resposicion = CatalogoDato::where('id', $tipo_solicitud)
                ->where('slug', 'tipo.solicitudes.reposición.ausencia')->exists();

            $store_solicitud = Solicitud::create([
                'usuario_id' => $user,
                'fecha_solicitud' => date('Y-m-d'),
                'fecha_desde' => $fecha_desde,
                'fecha_hasta' => $fecha_hasta,
                'hora_desde' => $hora_desde,
                'hora_hasta' => $hora_hasta,
                'total_tiempo' => $total_horas,
                'tipo_id' => $tipo_solicitud,
                'estado_id' => $estado_solicitud,
                'recuperable' => $recuperable,
                'detalle' => $detalle
            ]);

            DB::commit();

            try {
                PushNotificationService::sendNotification(User::find($user), "Solicitud", "Se genero una solicitud de {$store_solicitud->tipo_solicitud->descripcion} para el colaborador {$store_solicitud->usuario->nombre}", route('solicitud.permisos.edit', $store_solicitud->id));
            } catch (Throwable $e) {
            }
            return redirect()->route('solicitud.permisos.create')->with('success', MessagesConstant::INSERT);
        } catch (Throwable $e) {
            DB::rollBack();
            LogService::log('ERROR', 'Error al actualizar solicitud de permiso', ['message' => $e->getMessage()]);
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

        return view('solicitudes.edit', compact('title_page', 'breadcrumbs', 'solicitud', 'tipo_solicitudes', 'estados_solicitud'));
    }

    public function update(Request $request, Solicitud $solicitud)
    {
        try {
            DB::beginTransaction();
            $solicitud->tipo_id = $request->tipo_solicitud;
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

    public function buscar(Request $request)
    {
        if ($request->ajax()) {
            $buscar = $request->text;
            $output = "";

            // Construir la consulta
            $query = Solicitud::with(['usuario', 'tipo_solicitud', 'estado_solicitud']) // Cargar relaciones
                ->whereHas('usuario', function ($query) use ($buscar) {
                    // Filtrar por el nombre del usuario
                    $query->where('nombre', 'like', "%{$buscar}%");
                })
                ->orWhereHas('tipo_solicitud', function ($query) use ($buscar) {
                    // Filtrar por la descripción del catálogo de datos
                    $query->where('descripcion', 'like', "%{$buscar}%");
                })->orWhereHas('estado_solicitud', function ($query) use ($buscar) {
                    // Filtrar por la descripción del catálogo de datos
                    $query->where('descripcion', 'like', "%{$buscar}%");
                });

            if ($buscar === 'si') {
                $recuperable = $buscar == 'si';
                $query->orWhere('recuperable', $recuperable);
            }

            $solicitudes = $query->orderBy('fecha_solicitud', 'desc')->get();

            if ($solicitudes) {
                foreach ($solicitudes as $solicitud) {
                    $recuperable = $solicitud->recuperable ? 'SI' : 'NO';
                    $editar = "<a href='" . route('solicitud.permisos.edit', $solicitud->id) . "' class='dropdown-item'>Editar</a>";
                    $eliminar = "<a href='#' class='dropdown-item eliminar-solicitud' id='" . $solicitud->id . "'>Eliminar</a>";

                    if ($solicitud->estado_solicitud->descripcion == 'Aprobado') {
                        $editar = "<a href='" . route('solicitud.permisos.show', $solicitud->id) . "' class='dropdown-item'>Detalle</a>";
                    }

                    $output .= '<tr id="{{ $solicitud->id }}">' .
                        '<td class="align-middle text-capitalize">' . $solicitud->id . '</td>' .
                        '<td class="align-middle text-capitalize">' . $solicitud->usuario->nombre . '</td>' .
                        '<td class="align-middle">' . dateFormatHumans($solicitud->fecha_solicitud) . '</td>' .
                        '<td class="align-middle">' . $solicitud->tipo_solicitud->descripcion . '</td>' .
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
                return Response($output);
            }
        }
    }
}
