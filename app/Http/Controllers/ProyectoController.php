<?php

namespace App\Http\Controllers;

use Exception;
use Throwable;
use App\Models\Proyecto;
use App\Models\CatalogoDato;
use App\Services\LogService;
use Illuminate\Http\Request;
use App\Models\ArchivoProyecto;
use Illuminate\Support\Facades\DB;
use App\Constants\MessagesConstant;
use Illuminate\Support\Facades\Storage;
use App\Http\Requests\ProyectoStoreRequest;

class ProyectoController extends Controller
{
    private $path_files;

    public function __construct()
    {
        $environment = env('APP_ENV');

        if ($environment === 'production') {
            $this->path_files = 'proyectos';
        } else {
            $this->path_files = 'pruebas/proyectos';
        }
    }
    public function index()
    {
        $title_page = 'Proyectos';
        $tipo_proyectos = CatalogoDato::getChildrenCatalogo('proyectos');
        return view('proyectos.index', compact('tipo_proyectos', 'title_page'));
    }

    /**
     * Mostrar proyectos segun su tipo
     * @param int tipo_proyecto
     * @return view
     */
    public function proyectos($tipo, $tipo_id)
    {
        $title_page = str_replace('-', ' ', $tipo);
        $back_route = route('proyecto.index');
        $proyectos = Proyecto::where('catalogo_proyecto_id', $tipo_id)->paginate(15);

        return view('proyectos.list_proyectos', compact('proyectos', 'tipo', 'tipo_id', 'title_page', 'back_route'));
    }

    /**
     * Mostrar la información de un proyecto
     * @param string tipo 
     * @param int tipo_id
     * @return  
     */
    public function opcionesProyecto($tipo, $tipo_id, Proyecto $proyecto)
    {
        $title_page = 'Opciones';
        $back_route = route('proyecto.list', ['tipo' => $tipo, 'tipo_id' => $tipo_id]);

        return view('proyectos.opciones_proyectos', compact('title_page', 'back_route', 'tipo', 'tipo_id', 'proyecto'));
    }


    public function show_informacion_general($tipo, $tipo_id, Proyecto $proyecto)
    {
        $title_page = 'Información General';
        $back_route = route('proyecto.view', ['tipo' => $tipo, 'tipo_id' => $tipo_id, 'proyecto' => $proyecto->id]);

        return view('proyectos.informacion_general', compact('title_page', 'back_route', 'tipo', 'tipo_id', 'proyecto'));
    }

    public function create($tipo, $tipo_id)
    {
        $proyecto = new Proyecto();
        $title_page = str_replace('-', ' ', $tipo);
        $back_route = route('proyecto.list', ['tipo' => $tipo, 'tipo_id' => $tipo_id]);
        $tipo_proyectos = CatalogoDato::getChildrenCatalogo('tipo.proyectos')->pluck('descripcion', 'id');


        return view('proyectos.create', compact('proyecto', 'tipo_proyectos', 'tipo', 'tipo_id', 'title_page', 'back_route'));
    }

    public function store(ProyectoStoreRequest $request, $tipo, $tipo_id)
    {
        try {
            DB::beginTransaction();
            $nombre_proyecto = $request->nombre_proyecto;
            $fecha_inicio = dateFormat('d-m-Y', 'Y-m-d', $request->fecha_inicio);
            $fecha_fin =  dateFormat('d-m-Y', 'Y-m-d', $request->fecha_fin);
            $path_portada = Storage::disk('digitalocean')->put($this->path_files . '/' .  str_replace(' ', '_', $nombre_proyecto), $request->file('portada'));

            $param = [
                'catalogo_proyecto_id' => $tipo_id,
                'nombre_proyecto' => $nombre_proyecto,
                'nombre_propietario' => $request->propietario,
                'ubicacion' => $request->ubicacion_proyecto,
                'direccion' => $request->direccion,
                'telefono' => $request->telefono,
                'correo' => $request->correo,
                'tipo_proyecto_id' => $request->tipo_proyecto,
                'area_lote' => $request->area_lote,
                'area_construccion' => $request->area_construccion,
                'numero_unidades' => $request->numero_unidades,
                'area_lote_unidad' => $request->area_lote_unidad,
                'area_construccion_unidad' => $request->area_construccion_unidad,
                'presupuesto_total' => $request->presupuesto,
                'presupuesto_unidad' => $request->presupuesto_unidad,
                'fecha_inicio' => $fecha_inicio,
                'fecha_finalizacion' => $fecha_fin,
                'observacion' => $request->observaciones,
                'portada' => $path_portada,
            ];

            if ($proyecto = Proyecto::create($param)) {
                // Verificar si hay archivos en el request
                if ($request->file('archivos_proyecto')) {
                    $result_upload_files = $this->subirArchivos($request->file('archivos_proyecto'), $nombre_proyecto);
                    if (count($result_upload_files) > 0) {
                        foreach ($result_upload_files as $archivo) {
                            ArchivoProyecto::create([
                                'proyecto_id' => $proyecto->id,
                                'nombre' => $archivo['nombre_archivo'],
                                'ruta_archivo' => $archivo['path'],
                                'tipo_archivo' => $archivo['categoria'],
                                'size' => $archivo['size'],
                            ]);
                        }
                    }
                }

                DB::commit();
                LogService::log('info', 'Proyecto creado con éxito', ['user_id' => auth()->id(), 'action' => 'create']);
                return redirect()->route('proyecto.create', ['tipo' => $tipo, 'tipo_id' => $tipo_id])->with('success', 'La información ingresada se ha guardado con éxito.');
            } else {
                throw new Exception('Error al intentar guardar la información.');
            }
        } catch (Throwable $e) {
            DB::rollBack();
            // Verifica si el archivo existe antes de intentar eliminarlo
            if (Storage::disk('digitalocean')->exists($path_portada)) {
                // Elimina el archivo
                Storage::disk('digitalocean')->delete($path_portada);
            }
            // Si existen varios archivos se eliminan
            if ($request->file('archivos_proyecto')) {
                $this->eliminarArchivos($request->file('archivos_proyecto'), $nombre_proyecto);
            }
            LogService::log('error', 'Error al crear proyecto', ['user_id' => auth()->id(), 'action' => 'create', 'message' => $e->getMessage()]);
            return redirect()->route('proyecto.create', ['tipo' => $tipo, 'tipo_id' => $tipo_id])->with('error', MessagesConstant::CATCH_ERROR);
        }
    }

    public function edit($tipo, $tipo_id, Proyecto $proyecto)
    {
        $title_page = str_replace('-', ' ', $tipo) . ' - Editar';
        $back_route = route('proyecto.view', ['tipo' => $tipo, 'tipo_id' => $tipo_id, 'proyecto' => $proyecto->id]);
        $tipo_proyectos = CatalogoDato::getChildrenCatalogo('tipo.proyectos')->pluck('descripcion', 'id');

        return view('proyectos.edit', compact('proyecto', 'tipo_proyectos', 'tipo', 'tipo_id', 'title_page', 'back_route'));
    }
    public function update(Request $request, $tipo, $tipo_id, Proyecto $proyecto)
    {
        try {
            $old_data = Proyecto::find($proyecto->id);
            DB::beginTransaction();
            $nombre_proyecto = $request->nombre_proyecto;
            $fecha_inicio = dateFormat('d-m-Y', 'Y-m-d', $request->fecha_inicio);
            $fecha_fin =  dateFormat('d-m-Y', 'Y-m-d', $request->fecha_fin);
            $path = $this->path_files . '/' .  str_replace(' ', '_', $nombre_proyecto);
            $archivos_db = $proyecto->archivos_proyecto->pluck('ruta_archivo', 'id')->toArray();
            $archivos_actuales = isset($request->archivos_actuales) ? $request->archivos_actuales : [];
            $proyecto->nombre_proyecto = $nombre_proyecto;
            $proyecto->nombre_propietario = $request->propietario;
            $proyecto->ubicacion = $request->ubicacion_proyecto;
            $proyecto->direccion = $request->direccion;
            $proyecto->telefono = $request->telefono;
            $proyecto->correo = $request->correo;
            $proyecto->tipo_proyecto_id = $request->tipo_proyecto;
            $proyecto->area_lote = $request->area_lote;
            $proyecto->area_construccion = $request->area_construccion;
            $proyecto->numero_unidades = $request->numero_unidades;
            $proyecto->area_lote_unidad = $request->area_lote_unidad;
            $proyecto->area_construccion_unidad = $request->area_construccion_unidad;
            $proyecto->presupuesto_total = $request->presupuesto;
            $proyecto->presupuesto_unidad = $request->presupuesto_unidad;
            $proyecto->fecha_inicio = $fecha_inicio;
            $proyecto->fecha_finalizacion = $fecha_fin;
            $proyecto->observacion = $request->observaciones;

            $archivos_combinados = [];

            if (!empty($archivos_actuales)) {
                foreach ($archivos_actuales['id'] as $key => $id) {
                    $archivos_combinados[$id] = $archivos_actuales['path'][$key];
                }
            }

            $archivos_diff = array_diff($archivos_db, $archivos_combinados);

            // Si se cambio la portada
            if ($request->file('portada')) {
                // Se sube la nueva portada
                $path_portada = Storage::disk('digitalocean')->put($path, $request->file('portada'));
                // se elimina la portada anterior
                if (Storage::disk('digitalocean')->exists($proyecto->portada)) {
                    // Elimina el archivo
                    Storage::disk('digitalocean')->delete($proyecto->portada);
                }

                $proyecto->portada = $path_portada;
            }

            if ($proyecto->save()) {
                /// Borrar los archvos actuales 
                foreach ($archivos_diff as $key => $borrar) {
                    ArchivoProyecto::find($key)->delete();
                    if (Storage::disk('digitalocean')->exists($borrar)) {
                        // Elimina el archivo
                        Storage::disk('digitalocean')->delete($borrar);
                    }
                }
                if ($request->file('archivos_proyecto')) {
                    $result_upload_files = $this->subirArchivos($request->file('archivos_proyecto'), $nombre_proyecto);
                    if (count($result_upload_files) > 0) {
                        foreach ($result_upload_files as $archivo) {
                            ArchivoProyecto::create([
                                'proyecto_id' => $proyecto->id,
                                'nombre' => $archivo['nombre_archivo'],
                                'ruta_archivo' => $archivo['path'],
                                'tipo_archivo' => $archivo['categoria'],
                                'size' => $archivo['size'],
                            ]);
                        }
                    }
                }

                DB::commit();
                LogService::log('info', 'Proyecto actualizado', ['user_id' => auth()->id(), 'action' => 'update', 'old_data' => $old_data, 'new_data' => $proyecto]);
                return redirect()->route('proyecto.edit', ['tipo' => $tipo, 'tipo_id' => $tipo_id, 'proyecto' => $proyecto->id])->with('success', 'Información se ha actualizado con éxito.');
            } else {
                throw 'Error al intentar actualizar la información.';
            }
        } catch (Throwable $e) {
            DB::rollBack();
            // Verifica si el archivo existe antes de intentar eliminarlo
            if (isset($path_portada) && Storage::disk('digitalocean')->exists($path_portada)) {
                // Elimina el archivo
                Storage::disk('digitalocean')->delete($path_portada);
            }
            // Si existen varios archivos se eliminan
            if ($request->file('archivos_proyecto')) {
                $this->eliminarArchivos($request->file('archivos_proyecto'), $nombre_proyecto);
            }

            LogService::log('error', 'Error al actualizar Proyecto', ['user_id' => auth()->id(), 'action' => 'update', 'message' => $e->getMessage()]);
            return redirect()->route('proyecto.edit', ['tipo' => $tipo, 'tipo_id' => $tipo_id, 'proyecto' => $proyecto->id])->with('error', MessagesConstant::CATCH_ERROR);
        }
    }

    private function subirArchivos($archivos_proyecto, $proyecto)
    {
        $result_archivos = [];
        foreach ($archivos_proyecto as $categoria => $archivos) {
            foreach ($archivos as $archivo) {
                // subir archivo al Space DigitalOcean
                $path_files = Storage::disk('digitalocean')->put($this->path_files . '/' . str_replace(' ', '_', $proyecto), $archivo);
                $fileName = $archivo->getClientOriginalName();
                $categoria_archivo = strtolower(substr($categoria, 9));
                $file_size = $archivo->getSize();

                $upload_file = ['nombre_archivo' => $fileName, 'path' => $path_files, 'categoria' => $categoria_archivo, 'size' => $file_size];
                $result_archivos[] = $upload_file;
            }
        }

        return $result_archivos;
    }
    public function downloadFiles(Request $request)
    {
        $fileName = $request->archivo;
        // Verificar si el archivo existe en el Space
        if (!Storage::disk('digitalocean')->exists($fileName)) {
            return back()->with('error', 'Archivo no encontrado o no disponible.');
        }
        // Obtener el contenido del archivo
        $fileContent = Storage::disk('digitalocean')->get($fileName);
        // Obtener el tipo MIME del archivo para una respuesta adecuada
        $mimeType = Storage::disk('digitalocean')->mimeType($fileName);

        // Devolver una respuesta de archivo para descargar
        return response($fileContent, 200)
            ->header('Content-Type', $mimeType)
            ->header('Content-Disposition', 'attachment; filename="' . basename($fileName) . '"');
    }

    private function eliminarArchivos($archivos_proyecto, $proyecto)
    {
        foreach ($archivos_proyecto as $archivos) {
            foreach ($archivos as $archivo) {
                $path_archivos = $this->path_files . '/' . str_replace(' ', '_', $proyecto) . '/' . $archivo;
                if (Storage::disk('digitalocean')->exists($path_archivos)) {
                    Storage::disk('digitalocean')->delete($path_archivos);
                }
            }
        }
    }
}
