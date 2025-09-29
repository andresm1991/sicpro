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
                'observacion' => $observaciones,
                'telefono' => $telefono,
                'estado_id' => $request->input('estado_proyecto'),
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

    public function show(Proyecto $proyecto)
    {
        $breadcrumbs = [
            ['name' => 'Inicio', 'url' => route('home')],
            ['name' => 'Proyectos', 'url' => route('jp.limpieza.proyectos.index')],
            ['name' => $proyecto->nombre_proyecto, 'url' => ''],
        ];
        //$proyecto->load('solicitudes', 'cronograma', 'inventario', 'proveedores', 'contratistas', 'presupuesto', 'notificaciones', 'adquisiciones', 'reportes');
        return view('jp_limpieza.proyectos.opciones_proyecto', compact('breadcrumbs', 'proyecto'));
    }


    public function edit(Proyecto $proyecto)
    {
        $breadcrumbs = [
            ['name' => 'Inicio', 'url' => route('home')],
            ['name' => $proyecto->nombre_proyecto, 'url' => route('jp.limpieza.proyectos.show', $proyecto)],
            ['name' => 'Información general', 'url' => ''],
        ];

        return view('jp_limpieza.proyectos.edit', compact('breadcrumbs', 'proyecto'));
    }

    public function update(Request $request, Proyecto $proyecto)
    {
        try {
            DB::beginTransaction();
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

            $proyecto->nombre_proyecto = $nombre;
            $proyecto->entidad = $entidad;
            $proyecto->metros_contratado = $metros_contratado;
            $proyecto->precio_por_metro = str_replace(',', '', $precio_por_metro);
            $proyecto->tiempo_contratado = $tiempo_contratado;
            $proyecto->fecha_inicio = $fecha_inicio;
            $proyecto->fecha_finalizacion = $fecha_finalizacion;
            $proyecto->observacion = $observaciones;
            $proyecto->telefono = $telefono;
            $proyecto->estado_id = $request->input('estado_proyecto');

            // Eliminar archivos antiguos si se suben nuevos
            if ($file_portada) {
                if ($proyecto->archivo_portada) {
                    Storage::disk('digitalocean')->delete($proyecto->archivo_portada);
                }
                $proyecto->archivo_portada = Storage::disk('digitalocean')->put($path_files, $file_portada);
            }
            // Eliminar archivo de orden de compra si se sube uno nuevo o se elimina el existente
            if ($file_orden_compra || $request->input('archivo_orden_compra')) {
                if ($proyecto->archivo_orden_compra) {
                    Storage::disk('digitalocean')->delete($proyecto->archivo_orden_compra);
                }
                if ($file_orden_compra) {
                    $proyecto->archivo_orden_compra = Storage::disk('digitalocean')->put($path_files, $file_orden_compra);
                }
            }
            // Eliminar archivo de acta final si se sube uno nuevo o se elimina el existente
            if ($file_acta_final || $request->input('archivo_acta_final')) {
                if ($proyecto->archivo_acta_final) {
                    Storage::disk('digitalocean')->delete($proyecto->archivo_acta_final);
                }
                if ($file_acta_final) {
                    $proyecto->archivo_acta_final = Storage::disk('digitalocean')->put($path_files, $file_acta_final);
                }
            }

            $proyecto->save();

            DB::commit();

            return redirect()->route('jp.limpieza.proyectos.edit', $proyecto)->with('success', 'Proyecto actualizado exitosamente.');
        } catch (\Exception $th) {
            DB::rollBack();
            return redirect()->route('jp.limpieza.proyectos.edit', $proyecto)->with('error', 'Error al actualizar el proyecto: ' . $th->getMessage());
        }
    }
}
