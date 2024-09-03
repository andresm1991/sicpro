<?php

namespace Database\Seeders;

use App\Models\Banco;
use Illuminate\Database\Seeder;

class BancoTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Banco::create([
            'descripcion' => 'BANCO BOLIVARIANO',
            'activo' => true,
        ]);

        Banco::create([
            'descripcion' => 'BANCO CAPITAL SOCIEDAD ANONIMA',
            'activo' => true,
        ]);

        Banco::create([
            'descripcion' => 'BANCO CENTRAL DEL ECUADOR',
            'activo' => true,
        ]);

        Banco::create([
            'descripcion' => 'BANCO DE GUAYAQUIL',
            'activo' => true,
        ]);

        Banco::create([
            'descripcion' => 'PRODUBANCO',
            'activo' => true,
        ]);

        Banco::create([
            'descripcion' => 'BANCO DE LOJA',
            'activo' => true,
        ]);

        Banco::create([
            'descripcion' => 'BANCO DE MACHALA',
            'activo' => true,
        ]);

        Banco::create([
            'descripcion' => 'BANCO DEL AUSTRO',
            'activo' => true,
        ]);

        Banco::create([
            'descripcion' => 'BANCO DEL LITORAL',
            'activo' => true,
        ]);

        Banco::create([
            'descripcion' => 'BANCO DEL PACIFICO',
            'activo' => true,
        ]);

        Banco::create([
            'descripcion' => 'BANCO GENERAL RUMIÑAHUI',
            'activo' => true,
        ]);

        Banco::create([
            'descripcion' => 'BANCO INTERNACIONAL',
            'activo' => true,
        ]);

        Banco::create([
            'descripcion' => 'BANCO NACIONAL DE FOMENTO',
            'activo' => true,
        ]);

        Banco::create([
            'descripcion' => 'BANCO PICHINCHA',
            'activo' => true,
        ]);

        Banco::create([
            'descripcion' => 'BANCO PROCREDIT',
            'activo' => true,
        ]);

        Banco::create([
            'descripcion' => 'BANCO PROMERICA ',
            'activo' => true,
        ]);

        Banco::create([
            'descripcion' => 'CITIBANK',
            'activo' => true,
        ]);
    }
}