<?php

namespace Database\Seeders;

use App\Models\CatalogoDato;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CatalogoDatoTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('catalogo_datos')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $menu = CatalogoDato::create([
            'descripcion' => 'Proveedores',
            'detalle' => '',
            'slug' => 'proveedor',
            'padre_id' => null,
            'activo' => true,
        ]);

        CatalogoDato::create([
            'descripcion' => 'Materiales y Herramientas',
            'detalle' => '{"icono":"images/icons/herramientas.png"}',
            'slug' => 'meteriales.herramientas',
            'padre_id' => $menu->id,
            'activo' => true,
        ]);

        CatalogoDato::create([
            'descripcion' => 'Servicios',
            'detalle' => '{"icono":"images/icons/servicios.png"}',
            'slug' => 'servicios',
            'padre_id' => $menu->id,
            'activo' => true,
        ]);

        CatalogoDato::create([
            'descripcion' => 'Contratistas',
            'detalle' => '{"icono":"images/icons/trabajadores.png"}',
            'slug' => 'contratista',
            'padre_id' => $menu->id,
            'activo' => true,
        ]);

        CatalogoDato::create([
            'descripcion' => 'Mano de Obra',
            'detalle' => '{"icono":"images/icons/mano_obra.png"}',
            'slug' => 'mano.obra',
            'padre_id' => $menu->id,
            'activo' => true,
        ]);
        CatalogoDato::create([
            'descripcion' => 'Profecionales',
            'detalle' => '{"icono":"images/icons/profesionales.png"}',
            'slug' => 'profecionales',
            'padre_id' => $menu->id,
            'activo' => true,
        ]);

        // Tipo de Cuentas Bancarias
        $padre = CatalogoDato::create([
            'descripcion' => 'Tipo de Cuentas',
            'detalle' => '',
            'slug' => 'tipo.cuentas',
            'padre_id' => null,
            'activo' => true,
        ]);

        CatalogoDato::create([
            'descripcion' => 'Ahorros',
            'detalle' => '',
            'slug' => 'cuenta.ahorros',
            'padre_id' => $padre->id,
            'activo' => true,
        ]);

        CatalogoDato::create([
            'descripcion' => 'Corriente',
            'detalle' => '',
            'slug' => 'cuenta.corriente',
            'padre_id' => $padre->id,
            'activo' => true,
        ]);

        // Proyectos
        $padre = CatalogoDato::create([
            'descripcion' => 'Proyectos',
            'detalle' => '',
            'slug' => 'proyectos',
            'padre_id' => null,
            'activo' => true,
        ]);

        CatalogoDato::create([
            'descripcion' => 'Proyectos Propios',
            'detalle' => '{"icono": "images/icons/arquitecto.png"}',
            'slug' => 'proyectos.propios',
            'padre_id' => $padre->id,
            'activo' => true,
        ]);

        CatalogoDato::create([
            'descripcion' => 'Proyectos Particulares',
            'detalle' => '{"icono": "images/icons/cierre.png"}',
            'slug' => 'proyectos.particulares',
            'padre_id' => $padre->id,
            'activo' => true,
        ]);

        // Tipo Proyectos
        $padre = CatalogoDato::create([
            'descripcion' => 'Tipo Proyectos',
            'detalle' => '',
            'slug' => 'tipo.proyectos',
            'padre_id' => null,
            'activo' => true,
        ]);

        CatalogoDato::create([
            'descripcion' => 'Contrucción',
            'detalle' => '',
            'slug' => 'contruccion',
            'padre_id' => $padre->id,
            'activo' => true,
        ]);

        CatalogoDato::create([
            'descripcion' => 'Diseño de Interiores',
            'detalle' => '',
            'slug' => 'diseño.interiores',
            'padre_id' => $padre->id,
            'activo' => true,
        ]);

        CatalogoDato::create([
            'descripcion' => 'Ampliación Remodelación',
            'detalle' => '',
            'slug' => 'apliacion.remodelacion',
            'padre_id' => $padre->id,
            'activo' => true,
        ]);

        // Aquisiciones
        $padre = CatalogoDato::create([
            'descripcion' => 'Adquisiciones',
            'detalle' => '',
            'slug' => 'adquisiciones',
            'padre_id' => null,
            'activo' => true,
        ]);

        CatalogoDato::create([
            'descripcion' => 'Bienes',
            'detalle' => '',
            'slug' => 'adquisiciones.bienes',
            'padre_id' => $padre->id,
            'activo' => true,
        ]);

        CatalogoDato::create([
            'descripcion' => 'Servicios',
            'detalle' => '',
            'slug' => 'adquisiciones.servicios',
            'padre_id' => $padre->id,
            'activo' => true,
        ]);
    }
}
