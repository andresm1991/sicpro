<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class PermissionTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        app()->make(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();


        $parent = Permission::create([
            'name' => 'gerencia',
            'descripcion' => 'Gerencia',
            'parent_id' => null,
        ]);

        Permission::create([
            'name' => 'gerencia.index',
            'descripcion' => 'Visualisar modulo de Gerencia',
            'parent_id' => $parent->id,
        ]);

        Permission::create([
            'name' => 'gerencia.create',
            'descripcion' => 'Crear datos en modulo de Gerencia',
            'parent_id' => $parent->id,
        ]);
    }
}