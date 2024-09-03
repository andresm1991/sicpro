<?php

namespace Database\Seeders;

use App\Models\CampoFormulario;
use Illuminate\Database\Seeder;

class FormularioTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        CampoFormulario::creare([
            'formulario_id' => 2,
            'lable' => 'RUC | CI',
            'tipo' => 'numeric',
            'requerido' => true,
            'orden' => 1,
            'instrucciones' => 'Ingrese RUC o CI',
        ]);

        CampoFormulario::creare([
            'formulario_id' => 2,
            'lable' => 'Nombre y Apellido',
            'tipo' => 'text',
            'requerido' => true,
            'orden' => 2,
            'instrucciones' => 'Ingrese nombre y apellido',
        ]);

        CampoFormulario::creare([
            'formulario_id' => 2,
            'lable' => 'Dirección',
            'tipo' => 'text',
            'requerido' => true,
            'orden' => 3,
            'instrucciones' => 'Ingrese una dirección',
        ]);

        CampoFormulario::creare([
            'formulario_id' => 2,
            'lable' => 'Teléfono 1',
            'tipo' => 'numeric',
            'requerido' => true,
            'orden' => 4,
            'instrucciones' => 'Ingrese teléfono',
        ]);

        CampoFormulario::creare([
            'formulario_id' => 2,
            'lable' => 'Teléfono 2',
            'tipo' => 'numeric',
            'requerido' => true,
            'orden' => 5,
            'instrucciones' => 'Ingrese teléfono',
        ]);
        CampoFormulario::creare([
            'formulario_id' => 2,
            'lable' => 'Teléfono 3',
            'tipo' => 'numeric',
            'requerido' => true,
            'orden' => 6,
            'instrucciones' => 'Ingrese teléfono',
        ]);

        CampoFormulario::creare([
            'formulario_id' => 2,
            'lable' => 'Email',
            'tipo' => 'email',
            'requerido' => true,
            'orden' => 7,
            'instrucciones' => 'Ingrese dirección de correo',
        ]);

        CampoFormulario::creare([
            'formulario_id' => 2,
            'lable' => 'Banco',
            'tipo' => 'numeric',
            'requerido' => true,
            'orden' => 8,
            'instrucciones' => 'Ingrese teléfono',
        ]);
    }
}
