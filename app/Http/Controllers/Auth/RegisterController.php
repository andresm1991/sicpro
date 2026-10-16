<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use App\Models\User;
use App\Services\LogService;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class RegisterController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Register Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users as well as their
    | validation and creation. By default this controller uses a trait to
    | provide this functionality without requiring any additional code.
    |
    */

    use RegistersUsers;

    /**
     * Where to redirect users after registration.
     *
     * @var string
     */
    protected $redirectTo = RouteServiceProvider::HOME;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest');
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        return Validator::make($data, [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:usuarios,correo'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array  $data
     * @return \App\Models\User
     */
    protected function create(array $data)
    {
        // El formulario publico no pide usuario: se usa el correo (validado
        // como unico) para que la credencial funcione con el login por
        // `usuario` y la clave quede en `clave` (ver User::getAuthPassword).
        $user = User::create([
            'nombre' => $data['name'],
            'usuario' => $data['email'],
            'correo' => $data['email'],
            'clave' => Hash::make($data['password']),
            'activo' => true,
        ]);

        // Solo datos no sensibles: la clave y su hash nunca se guardan.
        LogService::auditar(
            'crear',
            'user',
            $user->id,
            'Se registró el usuario ' . $user->usuario . ' con el nombre ' . $user->nombre . '.',
            [],
            [
                'nombre' => $user->nombre,
                'usuario' => $user->usuario,
                'correo' => $user->correo,
                'activo' => $user->activo,
            ]
        );

        return $user;
    }
}
