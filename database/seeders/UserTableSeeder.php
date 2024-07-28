<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $user = User::where('usuario', 'admin')->first();
        if ($user) {
            $user->assignRole('Administrador');
        } else {
            $superAdmin = User::create([
                'nombre' => 'Administrador',
                'usuario' => 'admin',
                'correo' => '',
                'telefono' => '',
                'clave' => bcrypt('admin'),
                'activo' => true,
            ]);

            $superAdmin->assignRole('Administrador');
        }
    }
}