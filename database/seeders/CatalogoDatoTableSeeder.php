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
        /*DB::statement('SET FOREIGN_KEY_CHECKS=0;');
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
        // Unidades de Medidas
        $padre = CatalogoDato::create([
            'descripcion' => 'Unidades de medidas',
            'detalle' => '',
            'slug' => 'unidades.medida',
            'padre_id' => null,
            'activo' => true,
        ]);
        CatalogoDato::create([
            'descripcion' => 'Unidad',
            'detalle' => '',
            'slug' => 'unidad.medida.unidad',
            'padre_id' => $padre->id,
            'activo' => true,
        ]);
        CatalogoDato::create([
            'descripcion' => 'Metros',
            'detalle' => '',
            'slug' => 'unidad.medida.metros',
            'padre_id' => $padre->id,
            'activo' => true,
        ]);

        // Estados
        $padre = CatalogoDato::create([
            'descripcion' => 'Estados Contratistas',
            'detalle' => '',
            'slug' => 'estados.contratistas',
            'padre_id' => null,
            'activo' => true,
        ]);
        CatalogoDato::create([
            'descripcion' => 'Completado',
            'detalle' => '',
            'slug' => 'estados.contratistas.completado',
            'padre_id' => $padre->id,
            'activo' => true,
        ]);
        CatalogoDato::create([
            'descripcion' => 'En Proceso',
            'detalle' => '',
            'slug' => 'estados.contratistas.proceso',
            'padre_id' => $padre->id,
            'activo' => true,
        ]);*/

        // Estados inventario
        /*
        $padre = CatalogoDato::create([
            'descripcion' => 'Estados Inventario',
            'detalle' => '',
            'slug' => 'estados.inventario',
            'padre_id' => null,
            'activo' => true,
        ]);
        CatalogoDato::create([
            'descripcion' => 'Nuevo',
            'detalle' => '',
            'slug' => 'estados.inventario.nuevo',
            'padre_id' => $padre->id,
            'activo' => true,
        ]);
        CatalogoDato::create([
            'descripcion' => 'Defectuoso',
            'detalle' => '',
            'slug' => 'estados.inventario.defectuoso',
            'padre_id' => $padre->id,
            'activo' => true,
        ]);
        CatalogoDato::create([
            'descripcion' => 'Dado de baja',
            'detalle' => '',
            'slug' => 'estados.inventario.baja',
            'padre_id' => $padre->id,
            'activo' => true,
        ]);
       
        $padre = CatalogoDato::create([
            'descripcion' => 'Menu Administrativo',
            'detalle' => '',
            'slug' => 'menu.administrativo',
            'padre_id' => null,
            'activo' => true,
        ]);
        CatalogoDato::create([
            'descripcion' => 'Adquisiciones',
            'detalle' => '',
            'slug' => 'menu.administrativo.adquisiciones',
            'padre_id' => $padre->id,
            'activo' => true,
        ]);
        
        CatalogoDato::create([
            'descripcion' => 'Pagado',
            'detalle' => '',
            'slug' => 'estados.contratistas.pagado',
            'padre_id' => 36,
            'activo' => true,
        ]);
        */

        /**Estados Prestamos */
        /*$padre = CatalogoDato::create([
            'descripcion' => 'Estados Prestamos',
            'detalle' => '',
            'slug' => 'estados.prestamos',
            'padre_id' => null,
            'activo' => true,
        ]);
        CatalogoDato::create([
            'descripcion' => 'Pendiente',
            'detalle' => '',
            'slug' => 'estados.prestamos.pendiente',
            'padre_id' => $padre->id,
            'activo' => true,
        ]);
        CatalogoDato::create([
            'descripcion' => 'Aprobado',
            'detalle' => '',
            'slug' => 'estados.prestamos.aprobado',
            'padre_id' => $padre->id,
            'activo' => true,
        ]);
        CatalogoDato::create([
            'descripcion' => 'Pagado',
            'detalle' => '',
            'slug' => 'estados.prestamos.pagado',
            'padre_id' => $padre->id,
            'activo' => true,
        ]);
        CatalogoDato::create([
            'descripcion' => 'Cancelado',
            'detalle' => '',
            'slug' => 'estados.prestamos.cancelado',
            'padre_id' => $padre->id,
            'activo' => true,
        ]);
        /**Estados Pagos Prestamos */
        /*$padre = CatalogoDato::create([
            'descripcion' => 'Estados Pagos Prestamos',
            'detalle' => '',
            'slug' => 'estados.pagos.prestamos',
            'padre_id' => null,
            'activo' => true,
        ]);
        CatalogoDato::create([
            'descripcion' => 'Pendiente',
            'detalle' => '',
            'slug' => 'estados.pagos.prestamos.pendiente',
            'padre_id' => $padre->id,
            'activo' => true,
        ]);
        CatalogoDato::create([
            'descripcion' => 'Pagado',
            'detalle' => '',
            'slug' => 'estados.pagos.prestamos.pagado',
            'padre_id' => $padre->id,
            'activo' => true,
        ]);
        CatalogoDato::create([
            'descripcion' => 'Rechazado',
            'detalle' => '',
            'slug' => 'estados.pagos.prestamos.rechazado',
            'padre_id' => $padre->id,
            'activo' => true,
        ]);
        CatalogoDato::create([
            'descripcion' => 'Postergado',
            'detalle' => '',
            'slug' => 'estados.pagos.prestamos.postergado',
            'padre_id' => $padre->id,
            'activo' => true,
        ]);
        /**Estados tipo de solicitud */
        /*$padre = CatalogoDato::create([
            'descripcion' => 'Estados tipo de solicitud',
            'detalle' => '',
            'slug' => 'estados.tipo.solicitud.prestamos',
            'padre_id' => null,
            'activo' => true,
        ]);
        CatalogoDato::create([
            'descripcion' => 'Pausar Descuento',
            'detalle' => '',
            'slug' => 'estados.tipo.solicitud.prestamos.pausar.descuento',
            'padre_id' => $padre->id,
            'activo' => true,
        ]);
        CatalogoDato::create([
            'descripcion' => 'Cambiar Monto',
            'detalle' => '',
            'slug' => 'estados.tipo.solicitud.prestamos.cambiar.monto',
            'padre_id' => $padre->id,
            'activo' => true,
        ]);
        /** Metodos de pago Prestamo*/
        /*$padre = CatalogoDato::create([
            'descripcion' => 'Metodo de Pagos',
            'detalle' => '',
            'slug' => 'metodos.pagos',
            'padre_id' => null,
            'activo' => true,
        ]);
        CatalogoDato::create([
            'descripcion' => 'Efectivo',
            'detalle' => '',
            'slug' => 'metodos.pagos.efectivo',
            'padre_id' => $padre->id,
            'activo' => true,
        ]);
        CatalogoDato::create([
            'descripcion' => 'Transferencia',
            'detalle' => '',
            'slug' => 'metodos.pagos.transferencia',
            'padre_id' => $padre->id,
            'activo' => true,
        ]);
        CatalogoDato::create([
            'descripcion' => 'Otros',
            'detalle' => '',
            'slug' => 'metodos.pagos.otro',
            'padre_id' => $padre->id,
            'activo' => true,
        ]);

        $padre = CatalogoDato::create([
            'descripcion' => 'Tipo Costos',
            'detalle' => '',
            'slug' => 'tipo.costos',
            'padre_id' => null,
            'activo' => true,
        ]);
        CatalogoDato::create([
            'descripcion' => 'Costos Directos',
            'detalle' => '',
            'slug' => 'tipo.costos.directos',
            'padre_id' => $padre->id,
            'activo' => true,
        ]);
        CatalogoDato::create([
            'descripcion' => 'Costos Indirectos',
            'detalle' => '',
            'slug' => 'tipo.costos.indirectos',
            'padre_id' => $padre->id,
            'activo' => true,
        ]);
        */

        $padre = CatalogoDato::create([
            'descripcion' => 'Etapas Construcción',
            'detalle' => '',
            'slug' => 'etapas.construccion',
            'padre_id' => null,
            'activo' => true,
        ]);

        CatalogoDato::create([
            'descripcion' => 'Estructural',
            'detalle' => '',
            'slug' => 'etapas.construccion.estructural',
            'padre_id' => $padre->id,
            'activo' => true,
        ]);

        CatalogoDato::create([
            'descripcion' => 'Mamposterias',
            'detalle' => '',
            'slug' => 'etapas.construccion.mamposteria',
            'padre_id' => $padre->id,
            'activo' => true,
        ]);

        CatalogoDato::create([
            'descripcion' => 'Enlucidos',
            'detalle' => '',
            'slug' => 'etapas.construccion.enlucidos',
            'padre_id' => $padre->id,
            'activo' => true,
        ]);

        CatalogoDato::create([
            'descripcion' => 'Acabados',
            'detalle' => '',
            'slug' => 'etapas.construccion.acabados',
            'padre_id' => $padre->id,
            'activo' => true,
        ]);

        $padre = CatalogoDato::create([
            'descripcion' => 'Tipo Solicitudes',
            'detalle' => '',
            'slug' => 'tipo.solicitudes',
            'padre_id' => null,
            'activo' => true,
        ]);

        CatalogoDato::create([
            'descripcion' => 'Ausencia',
            'detalle' => '',
            'slug' => 'tipo.solicitudes.ausencia',
            'padre_id' => $padre->id,
            'activo' => true,
        ]);

        CatalogoDato::create([
            'descripcion' => 'Reposición de Ausencia',
            'detalle' => '',
            'slug' => 'tipo.solicitudes.reposición.ausencia',
            'padre_id' => $padre->id,
            'activo' => true,
        ]);
        CatalogoDato::create([
            'descripcion' => 'Eventualidad',
            'detalle' => '',
            'slug' => 'tipo.solicitudes.eventualidad',
            'padre_id' => $padre->id,
            'activo' => true,
        ]);

        $padre = CatalogoDato::create([
            'descripcion' => 'Estados Solicitud',
            'detalle' => '',
            'slug' => 'estados.solicitud',
            'padre_id' => null,
            'activo' => true,
        ]);

        CatalogoDato::create([
            'descripcion' => 'Aprobado',
            'detalle' => '',
            'slug' => 'estados.solicitud.aprobado',
            'padre_id' => $padre->id,
            'activo' => true,
        ]);
        CatalogoDato::create([
            'descripcion' => 'Pendiente',
            'detalle' => '',
            'slug' => 'estados.solicitud.pendiente',
            'padre_id' => $padre->id,
            'activo' => true,
        ]);

        CatalogoDato::create([
            'descripcion' => 'Rechazado',
            'detalle' => '',
            'slug' => 'estados.solicitud.rechazado',
            'padre_id' => $padre->id,
            'activo' => true,
        ]);

        CatalogoDato::create([
            'descripcion' => 'Cancelado',
            'detalle' => '',
            'slug' => 'estados.solicitud.cancelado',
            'padre_id' => $padre->id,
            'activo' => true,
        ]);
    }
}
