<?php

namespace App\Http\Controllers;

use App\Constants\MessagesConstant;
use App\Models\CatalogoDato;
use App\Models\ComentarioTarea;
use App\Models\Tarea;
use App\Services\PushNotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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

        $todoTasks = Tarea::whereHas('estado', function ($query) {
            $query->where('slug', 'estados.tarea.porhacer');
        })->with('usuarios', 'comentarios')->get();

        $inProgressTasks = Tarea::whereHas('estado', function ($query) {
            $query->where('slug', 'estados.tarea.encurso');
        })->with('usuarios', 'comentarios')->get();

        $completedTasks = Tarea::whereHas('estado', function ($query) {
            $query->where('slug', 'estados.tarea.finalizado');
        })->with('usuarios', 'comentarios')->get();

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
                Tarea::create([
                    'usuario_id' => auth()->user()->id,
                    'titulo' => $request->titulo,
                    'descripcion' => $request->descripcion,
                    'estado_id' => CatalogoDato::getIdCatalogo('estados.tarea.porhacer'),
                ]);

                DB::commit();

                PushNotificationService::sendNotification(auth()->user(), 'Tarea creada', "El usuario " . auth()->user()->nombre . " creo una nueva tarea en agenda", route('tarea.index'));

                return response()->json(['success' => true, 'message' => MessagesConstant::INSERT]);
            } catch (\Throwable $e) {
                DB::rollBack();
                return response()->json(['success' => false, 'message' => MessagesConstant::CATCH_ERROR]);
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
                ComentarioTarea::create([
                    'tarea_id' => $request->tarea_id,
                    'usuario_id' => auth()->user()->id,
                    'comentario' => $request->comentario,
                ]);

                DB::commit();

                PushNotificationService::sendNotification(auth()->user(), 'Comentario creado', "El usuario " . auth()->user()->nombre . " comento en una tarea en agenda", route('tarea.index'));

                return response()->json(['success' => true, 'message' => MessagesConstant::INSERT]);
            } catch (\Throwable $e) {
                DB::rollBack();
                return response()->json(['success' => false, 'message' => MessagesConstant::CATCH_ERROR]);
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
            foreach ($comentarios as $comentario) {
                $comentario->created_at_formateado = $comentario->created_at_formateado;
                $comentario->updated_at_formateado = $comentario->updated_at_formateado;
            }
            return response()->json(['success' => true, 'comentarios' => $comentarios]);
        }
    }

    /**
     * PUT - Actualizar comentarios de tarea
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
}