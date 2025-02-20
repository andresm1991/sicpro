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
        })->with('usuarios', 'comentarios')->paginate(15);

        $inProgressTasks = Tarea::whereHas('estado', function ($query) {
            $query->where('slug', 'estados.tarea.encurso');
        })->with('usuarios', 'comentarios')->paginate(15);

        $completedTasks = Tarea::whereHas('estado', function ($query) {
            $query->where('slug', 'estados.tarea.finalizada');
        })->with('usuarios', 'comentarios')->paginate(15);


        return view('tareas.index', compact('title_page', 'breadcrumbs', 'todoTasks', 'inProgressTasks', 'completedTasks'));
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
