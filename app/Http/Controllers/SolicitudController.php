<?php

namespace App\Http\Controllers;

use App\Constants\MessagesConstant;
use App\Http\Requests\SolicitudStoreRequest;
use App\Models\CatalogoDato;
use App\Models\Solicitud;
use App\Models\User;
use App\Services\PushNotificationService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Throwable;

class SolicitudController extends Controller
{
    public function index () {
        $title_page = 'Solicitudes';
        $solicitudes = Solicitud::orderBy('fecha_solicitud', 'asc')->paginate(15);

        $breadcrumbs = [
            ['name' => 'Inicio', 'url' => route('home')],
            ['name' => $title_page, 'url' => ''] // Último breadcrumb no tiene URL, es el actual
        ];

        return view('solicitudes.opciones', compact('title_page', 'breadcrumbs', 'solicitudes'));
    }

    public function solicitudes($tipo){
        if($tipo == 'solicitud'){
            $title_page = 'Solicitudes';
            $solicitudes = Solicitud::orderBy('fecha_solicitud', 'asc')->paginate(15);
    
            $breadcrumbs = [
                ['name' => 'Inicio', 'url' => route('home')],
                ['name' => 'Opciones', 'url' => route('solicitud.index')],
                ['name' => $title_page, 'url' => ''] // Último breadcrumb no tiene URL, es el actual
            ];
    
            return view('solicitudes.index', compact('title_page', 'breadcrumbs', 'solicitudes'));

        }elseif($tipo == 'reposicion'){

        }
        
    }

    public function create () {
        $title_page = 'Nueva Solicitud';
        
        $breadcrumbs = [
            ['name' => 'Inicio', 'url' => route('home')],
            ['name' => 'Solicitudes', 'url' => route('solicitud.index')],
            ['name' => $title_page, 'url' => ''] // Último breadcrumb no tiene URL, es el actual
        ];

        $solicitud = new Solicitud();
        $users = User::where('activo', true)
        ->where('id', '>', 1)->pluck('nombre', 'id');
        $tipo_solicitudes = CatalogoDato::getChildrenCatalogo('tipo.solicitudes')->pluck('descripcion', 'id');
        $estados_solicitud = CatalogoDato::getChildrenCatalogo('estados.solicitud')->pluck('descripcion', 'id');

        return view('solicitudes.create', compact('title_page', 'breadcrumbs', 'solicitud', 'users', 'tipo_solicitudes', 'estados_solicitud'));
    }

    public function store(SolicitudStoreRequest $request) {
        try{
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
            $recuperable = $request->recuperable ? true:false;

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

            PushNotificationService::sendNotification($user, "Solicitud", "Se genero una solicitud de {$store_solicitud->tipo_solicitud->descripcion} para el colaborador {$tipo_solicitud->usuario->nombre}", route('solicitud.edit', $store_solicitud->id));

            return redirect()->route('solicitud.create')->with('success', 'Solicitud creada con éxito.');
        }catch (Throwable $e) {
            DB::rollBack();
            return redirect()->route('solicitud.create')->with('success', MessagesConstant::CATCH_ERROR);
        }
    }

    public function buscar (Request $request) {
        if($request->ajax()){
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

            if($buscar === 'si'){
                $recuperable = $buscar == 'si';
                $query->orWhere('recuperable', $recuperable);
            }

            $solicitudes = $query->get();
            
            if ($solicitudes) {
                foreach ($solicitudes as $solicitud) {
                    $recuperable = $solicitud->recuperable ? 'SI' : 'NO';
                    $editar = "<a href='". route('solicitud.edit', $solicitud->id) ."' class='dropdown-item'>Editar</a>";
                    $eliminar = "<a href='#' class='dropdown-item eliminar-solicitud' id='". $solicitud->id ."'>Eliminar</a>";

                    $output .= '<tr id="{{ $solicitud->id }}">'.
                    '<td class="align-middle text-capitalize">'.$solicitud->id .'</td>'.
                    '<td class="align-middle text-capitalize">'. $solicitud->usuario->nombre .'</td>'.
                    '<td class="align-middle">'.dateFormatHumans($solicitud->fecha_solicitud) .'</td>'.
                    '<td class="align-middle">'. $solicitud->tipo_solicitud->descripcion .'</td>'.
                    '<td class="align-middle">'.$recuperable.'</td>'.
                    '<td class="align-middle">'.$solicitud->estado_solicitud->descripcion .'</td>'.
                    '<td class="align-middle align-middle text-right text-truncate">'.
                        '<button type="button" class="btn btn-outline-dark" data-container="body"
                            data-toggle="popover" data-placement="left" data-trigger="focus"
                            data-content ="'.$editar.$eliminar.'"><i class="fas fa-caret-left font-weight-normal"></i> Opciones</button>'.
                    '</td>'.
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
