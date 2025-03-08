<?php

namespace App\Http\Controllers;

use App\Models\Tarea;
use App\Models\CatalogoDato;
use App\Models\UsuarioTarea;
use Illuminate\Http\Request;
use App\Models\ComentarioTarea;
use Illuminate\Support\Facades\DB;
use App\Constants\MessagesConstant;
use Illuminate\Support\Facades\Auth;
use App\Services\PushNotificationService;

class TareaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $title_page = 'Agenda';
        $breadcrumbs = [
            ['name' => 'Inicio', 'url' => route('home')],
            ['name' => 'Agenda', 'url' => '']
        ];


        $todoTasks = $this->getTasksByState('estados.tarea.porhacer');
        $inProgressTasks = $this->getTasksByState('estados.tarea.encurso');
        $completedTasks = $this->getTasksByState('estados.tarea.finalizado');

        $estados = CatalogoDato::getChildrenCatalogo('estados.tarea')->pluck('descripcion', 'id');

        return view('tareas.index', compact('title_page', 'breadcrumbs', 'todoTasks', 'inProgressTasks', 'completedTasks', 'estados'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        if ($request->ajax()) {
            try {
                DB::beginTransaction();
                $usuarios = $request->input('list_usuarios', []);

                $tarea = Tarea::create([
                    'usuario_id' => auth()->user()->id,
                    'titulo' => $request->titulo,
                    'descripcion' => $request->descripcion,
                    'estado_id' => CatalogoDato::getIdCatalogo('estados.tarea.porhacer'),
                ]);

                foreach ($usuarios as $usuario) {
                    UsuarioTarea::create([
                        'usuario_id' => $usuario,
                        'tarea_id' => $tarea->id,
                    ]);
                }
                DB::commit();

                PushNotificationService::sendNotification(auth()->user(), 'Tarea creada', "El usuario " . auth()->user()->nombre . " creo una nueva tarea en agenda", route('tarea.index'));

                return response()->json(['success' => true, 'message' => MessagesConstant::INSERT]);
            } catch (\Throwable $e) {
                DB::rollBack();
                return response()->json(['success' => false, 'message' => MessagesConstant::CATCH_ERROR, 'error' => $e->getMessage()]);
            }
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function storeComentario(Request $request)
    {

        if ($request->ajax()) {
            try {
                DB::beginTransaction();
                if ($request->comentario != null) {
                    ComentarioTarea::create([
                        'tarea_id' => $request->tarea_id,
                        'usuario_id' => auth()->user()->id,
                        'comentario' => $request->comentario,
                    ]);
                }

                $usuarioIds = $request->input('list_usuarios', []);
                $usuarios_tarea_actuales = UsuarioTarea::where('tarea_id', $request->tarea_id)->pluck('usuario_id')->toArray();

                $usuarios_a_eliminar = array_diff($usuarios_tarea_actuales, $usuarioIds);
                $usuarios_a_agregar = array_diff($usuarioIds, $usuarios_tarea_actuales);

                foreach ($usuarios_a_eliminar as $usuario) {
                    UsuarioTarea::where('tarea_id', $request->tarea_id)->where('usuario_id', $usuario)->delete();
                }

                foreach ($usuarios_a_agregar as $usuario) {
                    UsuarioTarea::create([
                        'usuario_id' => $usuario,
                        'tarea_id' => $request->tarea_id,
                    ]);
                }

                DB::commit();

                PushNotificationService::sendNotification(auth()->user(), 'Comentario creado', "El usuario " . auth()->user()->nombre . " comento en una tarea en agenda", route('tarea.index'));

                return response()->json(['success' => true, 'message' => MessagesConstant::INSERT]);
            } catch (\Throwable $e) {
                DB::rollBack();
                return response()->json(['success' => false, 'message' => MessagesConstant::CATCH_ERROR, 'error' => $e->getMessage()]);
            }
        }
    }

    /** 
     * Get comentarios by tarea
     */

    public function getComentariosTarea(Request $request)
    {
        if ($request->ajax()) {
            $comentarios = ComentarioTarea::where('tarea_id', $request->tarea_id)->with('usuario')->orderBy('updated_at', 'desc')->get();
            $colaboradores = UsuarioTarea::where('tarea_id', $request->tarea_id)->with('usuario')->orderBy('updated_at', 'desc')->get();

            foreach ($comentarios as $comentario) {
                $comentario->created_at_formateado = $comentario->created_at_formateado;
                $comentario->updated_at_formateado = $comentario->updated_at_formateado;
            }
            return response()->json(['success' => true, 'comentarios' => $comentarios, 'colaboradores' => $colaboradores]);
        }
    }

    /**
     * PUT - Actualizar comentario de tarea
     */

    public function updateComentario(Request $request)
    {
        if ($request->ajax()) {
            try {
                DB::beginTransaction();
                $comentario = ComentarioTarea::find($request->comentarioId);
                $comentario->comentario = $request->comentario;
                $comentario->save();
                DB::commit();
                return response()->json(['success' => true, 'message' => MessagesConstant::UPDATE]);
            } catch (\Throwable $e) {
                DB::rollBack();
                return response()->json(['success' => false, 'message' => MessagesConstant::DEFAUL_ERROR]);
            }
        }
    }
    /**
     * PUT - Actualizar estado tarea
     */

    public function updateEstadoTarea(Request $request, Tarea $tarea)
    {
        if ($request->ajax()) {
            try {
                DB::beginTransaction();
                $tarea->estado_id = $request->estado;
                $tarea->save();
                DB::commit();
                return response()->json(['success' => true, 'message' => MessagesConstant::UPDATE]);
            } catch (\Throwable $e) {
                DB::rollBack();
                return response()->json(['success' => false, 'message' => MessagesConstant::DEFAUL_ERROR]);
            }
        }
    }

    /**
     * DELETE - Eliminar comentarios de tarea
     */

    public function deleteComentario(Request $request)
    {
        if ($request->ajax()) {
            try {
                DB::beginTransaction();
                $comentario = ComentarioTarea::find($request->comentario);
                $comentario->delete();
                DB::commit();
                return response()->json(['success' => true, 'message' => MessagesConstant::DELETE]);
            } catch (\Throwable $e) {
                DB::rollBack();
                return response()->json(['success' => false, 'message' => MessagesConstant::DEFAUL_ERROR]);
            }
        }
    }

    /**
     * DELETE - Eliminar tarea
     */

    public function deleteTarea(Request $request)
    {
        if ($request->ajax()) {
            try {
                DB::beginTransaction();
                $tarea = Tarea::find($request->tarea);
                $tarea->delete();
                DB::commit();
                return response()->json(['success' => true, 'message' => MessagesConstant::DELETE]);
            } catch (\Throwable $e) {
                DB::rollBack();
                return response()->json(['success' => false, 'message' => MessagesConstant::DEFAUL_ERROR]);
            }
        }
    }

    private function getTasksByState($stateSlug)
    {
        $hasRole = auth()->user()->hasRole('Administrador');
        $userId = Auth::user()->id;

        $tasks = Tarea::whereHas('estado', function ($query) use ($stateSlug) {
            $query->where('slug', $stateSlug);
        });

        if (!$hasRole) {
            $tasks = $tasks->where(function ($query) use ($userId) {
                $query->where('usuario_id', $userId)
                    ->orWhereHas('usuario_tareas', function ($query) use ($userId) {
                        $query->where('usuario_id', $userId);
                    });
            });
        }

        return $tasks->with('usuario_tareas', 'comentarios')->get();
    }
}
