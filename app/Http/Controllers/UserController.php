<?php

namespace App\Http\Controllers;

use Throwable;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use App\Constants\MessagesConstant;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\UserStoreRequest;
use App\Http\Requests\UserUpdateRequest;
use App\Http\Requests\PerfilUserUpdateRequest;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $title_page = 'Sistema - Usuario';
        $back_route = route('sistema.index');
        $users = User::with('roles')->orderBy('nombre', 'asc')->paginate(15);

        $breadcrumbs = [
            ['name' => 'Inicio', 'url' => route('home')],
            ['name' => 'Sistema', 'url' => route('sistema.index')],
            ['name' => 'Usuarios', 'url' => ''] // Último breadcrumb no tiene URL, es el actual
        ];

        return view('user.index', compact('title_page', 'breadcrumbs', 'users'));
    }

    public function perfil()
    {
        $user = Auth::user();
        $title_page = 'Mi Perfil';
        return view('user.perfil', compact('user', 'title_page'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $title_page = 'Usuario - Nuevo';
        $user = new User();
        $roles = Role::all()->pluck('name', 'id');

        $breadcrumbs = [
            ['name' => 'Inicio', 'url' => route('home')],
            ['name' => 'Usuarios', 'url' => route('sistema.users.index')],
            ['name' => 'Nuevo', 'url' => ''] // Último breadcrumb no tiene URL, es el actual
        ];

        return view('user.create', compact('title_page', 'breadcrumbs', 'user', 'roles'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(UserStoreRequest $request)
    {
        DB::beginTransaction();
        try {
            $parametros = [
                'nombre' => $request->get('nombres'),
                'usuario' => $request->get('usuario'),
                'correo' => $request->get('email'),
                'telefono' => $request->get('telefono'),
                'clave' => bcrypt($request->get('password')),
                'activo' => $request->get('activo'),
            ];

            $user = User::create($parametros);
            if ($user->roles()->sync($request->get('perfil'))) {
                DB::commit();
                return redirect()->route('sistema.users.create')
                    ->with('success', 'Usuario creado con éxito.');
            }

            DB::rollBack();
            return redirect()->route('sistema.users.create')
                ->with('error', 'Ocurrió un error al intentar registrar el usuario.');
        } catch (Throwable $e) { //$e->getMessage();
            DB::rollBack();
            return redirect()->route('user.create')->with('error', MessagesConstant::CATCH_ERROR);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(User $user)
    {
        $title_page = 'Usuario - Editar';
        $back_route = route('sistema.users.index');
        $roles = Role::all()->pluck('name', 'id');

        $breadcrumbs = [
            ['name' => 'Inicio', 'url' => route('home')],
            ['name' => 'Usuarios', 'url' => route('sistema.users.index')],
            ['name' => 'Editar', 'url' => ''] // Último breadcrumb no tiene URL, es el actual
        ];

        return view('user.edit', compact('title_page', 'breadcrumbs', 'user', 'roles'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(UserUpdateRequest $request, User $user)
    {
        DB::beginTransaction();
        try {
            if ($user->id > 1) {
                $user->nombre = $request->get('nombres');
                $user->usuario = $request->get('usuario');
                $user->activo = $request->get('activo');
            }

            $user->correo = $request->get('email');
            $user->telefono = $request->get('telefono');

            if ($request->get('password')) {
                $user->clave = bcrypt($request->get('password'));
            }

            if ($user->save()) {
                $user->roles()->sync($request->get('perfil'));
                DB::commit();
                return redirect()->route('sistema.users.edit', $user->id)->with('success', 'Datos actualizados con éxito.');
            } else {
                DB::rollBack();
                return redirect()->route('sistema.users.edit', $user->id)->with('error', 'Ocurrió un error al intentar actializar la informacón.');
            }
        } catch (Throwable $e) {
            DB::rollBack();
            return redirect()->route('sistema.users.edit', $user->id)->with('error', MessagesConstant::CATCH_ERROR);
        }
    }

    public function updatePerfil(PerfilUserUpdateRequest $request, User $user)
    {
        DB::beginTransaction();
        try {
            if ($request->get('password')) {
                $user->clave = bcrypt($request->get('password'));
            }
            if ($request->get('correo')) {
                $user->correo = $request->get('correo');
            }
            if ($request->get('telefono')) {
                $user->telefono = $request->get('telefono');
            }
            if ($user->save()) {
                DB::commit();
                return redirect()->route('perfil.show')->with('success', 'Datos actualizados con éxito.');
            }
        } catch (Throwable $e) {
            DB::rollBack();
            return redirect()->route('perfil')->with('error', MessagesConstant::CATCH_ERROR);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        if ($id > 1) {
            $user = User::find($id)->delete();
            if ($user) {
                return response()->json(['success' => true, 'message' => 'Registro eliminado correctamente.']);
            } else {
                return response()->json(['success' => false, 'message' => 'Error al intentar eliminar el registro.']);
            }
        } else {
            return response()->json(['success' => false, 'message' => 'Acción no permitida, por seguridad el usuario Administrador no puede ser eliminado.']);
        }
    }

    public function buscar(Request $request)
    {
        if ($request->ajax()) {
            $buscar = $request->text;
            $output = "";

            $users = User::with('roles')->where(function ($query) use ($buscar) {
                $query->where('nombre', 'LIKE', '%' . $buscar . "%")
                    ->orWhere('correo', 'LIKE', '%' . $buscar . "%");
            })->orderBy('nombre', 'asc')
                ->get();

            if ($users) {
                foreach ($users as $user) {
                    $estado = $user->activo ? '<span class="badge badge-success">Activo</span>' : '<span class="badge badge-danger">Inactivo</span>';
                    $output .= '<tr id="' . $user->id . '">' .
                        '<td class="align-middle">' . $user->id . '</td>' .
                        '<td class="align-middle text-capitalize">' . $user->nombre . '</td>' .
                        '<td class="align-middle">' . $user->usuario . '</td>' .
                        '<td class="align-middle">' . $user->roles()->first()->name . '</td>' .
                        '<td class="align-middle">' . $estado . '</td>' .
                        '<td class="align-middle table-actions">' .
                        '<div class="action-buttons">' .
                        '<a href="' . route('sistema.users.edit', $user->id) . '"
                                                    class="btn btn-secondary btn-sm btn-space"><i
                                                        class="fa-light fa-pen-to-square"></i> Editar</a>' .
                        '<a href="javascript:void(0);" class="btn btn-danger btn-sm delete-user"
                                                    id="' . $user->id . '"><i class="fa-solid fa-trash-can"></i>
                                                    Eliminar</a>' .
                        '</div>' .
                        '</td>' .
                        '</tr>';
                }

                if (empty($output)) {
                    $output .= '<tr>' .
                        '<td colspan="6" class="text-center">' .
                        '<span class="text-danger">No existen datos para mostrar.</span>' .
                        '</td>' .
                        '</tr>';
                }
                return Response($output);
            }
        }
    }
}
