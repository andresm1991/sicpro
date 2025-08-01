<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class CategoriaProgramaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('categorias_programa')->insert([
            ['nombre' => 'AREAS INTIMAS', 'orden' => 1, 'es_exterior' => false],
            ['nombre' => 'AREAS COMPARTIDAS', 'orden' => 2, 'es_exterior' => false],
            ['nombre' => 'AREAS SOCIALES', 'orden' => 3, 'es_exterior' => false],
            ['nombre' => 'AREAS EXTERIORES', 'orden' => 4, 'es_exterior' => true],
        ]);
    }
}