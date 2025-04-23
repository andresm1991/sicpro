<?php

namespace App\Http\Controllers\JPLimpieza;

use App\Http\Controllers\Controller;
use App\Http\Requests\JPLimpieza\ProyectoStoreRequest;
use App\Models\JPLimpieza\Proyecto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ProyectoController extends Controller
{
    private $path_files;

    public function __construct()
    {
        $environment = env('APP_ENV');

        if ($environment === 'production') {
            $this->path_files = 'jp_limpieza/proyectos';
        } else {
            $this->path_files = 'pruebas/jp_limpieza/proyectos';
        }
    }

    function index(Request $request)
    {
        $breadcrumbs = [
            ['name' => 'Inicio', 'url' => route('home')],
            ['name' => 'Limpieza y mantenimiento', 'url' => route('jp.limpieza.index')],
            ['name' => 'Proyectos', 'url' => ''],
        ];

        $proyectos = Proyecto::orderBy('created_at', 'desc')->paginate(15);
        return view('jp_limpieza.proyectos.index', compact('breadcrumbs', 'proyectos'));
    }

    public function create()
    {
        $breadcrumbs = [
            ['name' => 'Inicio', 'url' => route('home')],
            ['name' => 'Limpieza y mantenimiento', 'url' => route('jp.limpieza.index')],
            ['name' => 'Proyectos', 'url' => route('jp.limpieza.proyectos.index')],
            ['name' => 'Nuevo proyecto', 'url' => ''],
        ];

        $proyecto = new Proyecto();

        return view('jp_limpieza.proyectos.create', compact('breadcrumbs', 'proyecto'));
    }

    public function store(ProyectoStoreRequest $request)
    {
        $nombre = $request->input('nombre_proyecto');
        $entidad = $request->input('entidad');
        $metros_contratado = $request->input('metraje_contratado');
        $precio_por_metro = $request->input('precio_metro');
        $tiempo_contratado = $request->input('tiempo_contratado');
        $fecha_inicio = $request->input('fecha_inicio');
        $fecha_finalizacion = $request->input('fecha_fin');
        $observaciones = $request->input('observaciones');
        $telefono = $request->input('telefono');

        $path_files = $this->path_files . '/' . str_replace(' ', '_', $nombre);
        $file_portada = $request->file('portada');
        $file_orden_compra = $request->file('file_orden_compra');
        $file_acta_final = $request->file('file_acta_final');

        try {
            DB::beginTransaction();
            Proyecto::create([
                'nombre_proyecto' => $nombre,
                'entidad' => $entidad,
                'metros_contratado' => $metros_contratado,
                'precio_por_metro' => str_replace(',', '', $precio_por_metro),
                'tiempo_contratado' => $tiempo_contratado,
                'fecha_inicio' => $fecha_inicio,
                'fecha_finalizacion' => $fecha_finalizacion,
                'observaciones' => $observaciones,
                'telefono' => $telefono,
                'estado' => 'nuevo',
                'archivo_portada' => $file_portada ? Storage::disk('digitalocean')->put($path_files, $file_portada) : null,
                'archivo_orden_compra' => $file_orden_compra ? Storage::disk('digitalocean')->put($path_files, $file_orden_compra) : null,
                'archivo_acta_final' => $file_acta_final ? Storage::disk('digitalocean')->put($path_files, $file_acta_final) : null,
            ]);

            DB::commit();

            return redirect()->route('jp.limpieza.proyectos.index')->with('success', 'Proyecto creado exitosamente.');
        } catch (\Exception $th) {
            DB::rollBack();
            if ($file_portada) {
                Storage::disk('digitalocean')->delete($this->path_files . '/' . str_replace(' ', '_', $nombre) . '/' . $file_portada->getClientOriginalName());
            }
            if ($file_orden_compra) {
                Storage::disk('digitalocean')->delete($this->path_files . '/' . str_replace(' ', '_', $nombre) . '/' . $file_orden_compra->getClientOriginalName());
            }
            if ($file_acta_final) {
                Storage::disk('digitalocean')->delete($this->path_files . '/' . str_replace(' ', '_', $nombre) . '/' . $file_acta_final->getClientOriginalName());
            }

            return redirect()->route('jp.limpieza.proyectos.create')->with('error', 'Error al crear el proyecto: ' . $th->getMessage());
        }
    }
    public function show($id)
    {
        $proyecto = Proyecto::findOrFail($id);
        return view('jp_limpieza.proyectos.show', compact('proyecto'));
    }
}
