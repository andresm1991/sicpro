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

        // Tipo de Aquisiciones
        $padre = CatalogoDato::create([
            'descripcion' => 'Tipo de Adquisiciones',
            'detalle' => '',
            'slug' => 'tipo.adquisiciones',
            'padre_id' => null,
            'activo' => true,
        ]);

        CatalogoDato::create([
            'descripcion' => 'Bienes',
            'detalle' => '',
            'slug' => 'tipo.adquisiciones.bienes',
            'padre_id' => $padre->id,
            'activo' => true,
        ]);

        CatalogoDato::create([
            'descripcion' => 'Servicios',
            'detalle' => '',
            'slug' => 'tipo.adquisiciones.servicios',
            'padre_id' => $padre->id,
            'activo' => true,
        ]);

        // Adquisiciones Menu
        $padre = CatalogoDato::create([
            'descripcion' => 'Menu Adquisiciones',
            'detalle' => '',
            'slug' => 'menu.adquisiciones',
            'padre_id' => null,
            'activo' => true,
        ]);

        CatalogoDato::create([
            'descripcion' => 'Diseño y aprobación de planos',
            'detalle' => '{"icono": "images/icons/diseno_planos.png"}',
            'slug' => 'menu.adquisciones.diseño.aprobacion.planos',
            'padre_id' => $padre->id,
            'activo' => true,
        ]);

        CatalogoDato::create([
            'descripcion' => 'Estructural',
            'detalle' => '{"icono": "images/icons/estructural.png"}',
            'slug' => 'menu.adquisciones.estructural',
            'padre_id' => $padre->id,
            'activo' => true,
        ]);
        CatalogoDato::create([
            'descripcion' => 'Obra gris',
            'detalle' => '{"icono": "images/icons/pared-de-ladrillo.png"}',
            'slug' => 'menu.adquisciones.obra.gris',
            'padre_id' => $padre->id,
            'activo' => true,
        ]);
        CatalogoDato::create([
            'descripcion' => 'Acabados',
            'detalle' => '{"icono": "images/icons/renovacion.png"}',
            'slug' => 'menu.adquisciones.acabados',
            'padre_id' => $padre->id,
            'activo' => true,
        ]);

        // Formas de pagos
        $padre = CatalogoDato::create([
            'descripcion' => 'Formas de pagos',
            'detalle' => '',
            'slug' => 'formas.pagos',
            'padre_id' => null,
            'activo' => true,
        ]);
        CatalogoDato::create([
            'descripcion' => 'Contado',
            'detalle' => '',
            'slug' => 'forma.pago.contado',
            'padre_id' => $padre->id,
            'activo' => true,
        ]);
        CatalogoDato::create([
            'descripcion' => 'Credito',
            'detalle' => '',
            'slug' => 'forma.pago.credito',
            'padre_id' => $padre->id,
            'activo' => true,
        ]);

        // Informacion general
        $padre = CatalogoDato::create([
            'descripcion' => 'Información General',
            'detalle' => '',
            'slug' => 'informacion.general',
            'padre_id' => null,
            'activo' => true,
        ]);
        CatalogoDato::create([
            'descripcion' => 'Nombre de la Empresa',
            'detalle' => 'PrimeJP Construcciones',
            'slug' => 'informacion.general.nombre.empresa',
            'padre_id' => $padre->id,
            'activo' => true,
        ]);
        CatalogoDato::create([
            'descripcion' => 'Dirección',
            'detalle' => 'Av. 6 de noviembre S/N y Augusto Gachet',
            'slug' => 'informacion.general.direccion',
            'padre_id' => $padre->id,
            'activo' => true,
        ]);
        CatalogoDato::create([
            'descripcion' => 'Teléfono',
            'detalle' => '099999999',
            'slug' => 'informacion.general.telefono',
            'padre_id' => $padre->id,
            'activo' => true,
        ]);
        CatalogoDato::create([
            'descripcion' => 'Correo',
            'detalle' => 'mail@example.com',
            'slug' => 'informacion.general.correo',
            'padre_id' => $padre->id,
            'activo' => true,
        ]);
    }
}
