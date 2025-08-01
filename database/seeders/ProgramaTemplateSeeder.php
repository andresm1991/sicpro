<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\CategoriaPrograma;
use App\Models\ProgramaArquitectonico;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class ProgramaTemplateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Busca las categorías que ya deben existir gracias a CategoriaProgramaSeeder
        $categorias = CategoriaPrograma::all()->keyBy('nombre');

        // 1. Crear el programa arquitectónico que servirá de plantilla
        $template = ProgramaArquitectonico::create([
            'proforma_id' => null, // Esto lo define como una plantilla
            'nombre' => 'Plantilla Maestra',
            'estilo' => 'CONTEMPORANEA'
        ]);
    }
}