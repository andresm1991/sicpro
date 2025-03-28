<?php

namespace App\Models;

use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use HasRoles, HasApiTokens, HasFactory, Notifiable;

    protected $table = 'usuarios';
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'nombre',
        'usuario',
        'correo',
        'clave',
        'activo',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'clave',
    ];

    // Relación: Un usuario tiene muchas solicitudes
    public function solicitudes()
    {
        return $this->hasMany(Solicitud::class, 'usuario_id');
    }

    public function tareas()
    {
        return $this->belongsToMany(Tarea::class, 'usuario_tareas', 'usuario_id', 'tarea_id');
    }

    public function usuario_tareas()
    {
        return $this->hasMany(UsuarioTarea::class, 'usuario_id');
    }

    public function getAuthPassword()
    {
        return $this->clave;
    }


    public static function getUsusarios()
    {
        $user_id = auth()->user()->id;
        if ($user_id > 1) {
            $users =  User::where('activo', true)
                ->where('id', $user_id)->get();
        } else {
            $users = User::where('activo', true)->orderBy('nombre', 'asc')->get();
        }

        return $users;
    }
}