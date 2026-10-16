<?php

namespace App\Http\Controllers\Sistema;

use App\Http\Controllers\Controller;
use App\Models\Log;
use Illuminate\Http\Request;

class AuditoriaController extends Controller
{
    /**
     * Visor de auditoria (solo lectura).
     *
     * Nota sobre App\Support\RangoFechas: NO se usa aqui a proposito.
     * RangoFechas solo entiende el formato combinado "m/d/Y - m/d/Y" del
     * date-range picker de los reportes (param `fechas`); este visor filtra
     * con dos inputs de fecha sueltos (`desde`/`hasta` en Y-m-d) sobre
     * whereDate, asi que forzar RangoFechas complicaria el formulario GET
     * sin aportar validacion util.
     */
    public function index(Request $request)
    {
        $title_page = 'Sistema - Auditoría';
        $breadcrumbs = [
            ['name' => 'Inicio', 'url' => route('home')],
            ['name' => 'Sistema', 'url' => route('sistema.index')],
            ['name' => 'Auditoría', 'url' => ''], // Último breadcrumb no tiene URL, es el actual
        ];

        $logs = Log::with('user')
            ->when($request->filled('entidad'), function ($query) use ($request) {
                $query->where('entidad', $request->input('entidad'));
            })
            ->when($request->filled('accion'), function ($query) use ($request) {
                $query->where('accion', $request->input('accion'));
            })
            ->when($request->filled('user_id'), function ($query) use ($request) {
                $query->where('user_id', $request->input('user_id'));
            })
            ->when($request->filled('desde'), function ($query) use ($request) {
                $query->whereDate('created_at', '>=', $request->input('desde'));
            })
            ->when($request->filled('hasta'), function ($query) use ($request) {
                $query->whereDate('created_at', '<=', $request->input('hasta'));
            })
            ->when($request->filled('q'), function ($query) use ($request) {
                $query->where('message', 'like', '%' . $request->input('q') . '%');
            })
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate(50)
            ->withQueryString();

        $entidades = Log::select('entidad')->whereNotNull('entidad')->distinct()->orderBy('entidad')->pluck('entidad');
        $acciones = Log::select('accion')->whereNotNull('accion')->distinct()->orderBy('accion')->pluck('accion');

        return view('sistema.auditoria.index', compact('title_page', 'breadcrumbs', 'logs', 'entidades', 'acciones'));
    }
}
