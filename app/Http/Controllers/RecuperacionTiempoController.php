<?php

namespace App\Http\Controllers;

use App\Constants\MessagesConstant;
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

            ReposicionTiempo::create([
                'usuario_id' => $user_id,
                'fecha' => $fecha_reposicion,
                'hora_desde' => $hora_desde,
                'hora_hasta' => $hora_hasta,
                'total' => $total_resposicion,
            ]);

            DB::commit();
            PushNotificationService::sendNotification(Auth::user(), 'Reposición de tiempo', "Se registro reposición de tiempo para el colaborador " . Auth::user()->nombre);
            return redirect()->back()->with('success', MessagesConstant::INSERT);
        } catch (\Throwable $th) {
            DB::rollBack();
            return redirect()->back()->with('error', MessagesConstant::DEFAUL_ERROR);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
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
}