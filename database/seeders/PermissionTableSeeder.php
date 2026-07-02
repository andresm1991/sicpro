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

        // ============================================================
        // GERENCIA
        // ============================================================
        $gerencia = Permission::firstOrCreate(
            ['name' => 'gerencia', 'guard_name' => 'web'],
            ['descripcion' => 'Módulo de Gerencia', 'parent_id' => null]
        );
        Permission::firstOrCreate(['name' => 'gerencia.ver', 'guard_name' => 'web'], ['descripcion' => 'Ver módulo de Gerencia', 'parent_id' => $gerencia->id]);
        Permission::firstOrCreate(['name' => 'gerencia.crear', 'guard_name' => 'web'], ['descripcion' => 'Crear datos en Gerencia', 'parent_id' => $gerencia->id]);
        Permission::firstOrCreate(['name' => 'gerencia.editar', 'guard_name' => 'web'], ['descripcion' => 'Editar datos en Gerencia', 'parent_id' => $gerencia->id]);
        Permission::firstOrCreate(['name' => 'gerencia.eliminar', 'guard_name' => 'web'], ['descripcion' => 'Eliminar datos en Gerencia', 'parent_id' => $gerencia->id]);

        // ============================================================
        // PROYECTOS
        // ============================================================
        $proyectos = Permission::firstOrCreate(
            ['name' => 'proyectos', 'guard_name' => 'web'],
            ['descripcion' => 'Módulo de Proyectos', 'parent_id' => null]
        );
        Permission::firstOrCreate(['name' => 'proyectos.ver', 'guard_name' => 'web'], ['descripcion' => 'Ver proyectos', 'parent_id' => $proyectos->id]);
        Permission::firstOrCreate(['name' => 'proyectos.crear', 'guard_name' => 'web'], ['descripcion' => 'Crear proyectos', 'parent_id' => $proyectos->id]);
        Permission::firstOrCreate(['name' => 'proyectos.editar', 'guard_name' => 'web'], ['descripcion' => 'Editar proyectos', 'parent_id' => $proyectos->id]);
        Permission::firstOrCreate(['name' => 'proyectos.eliminar', 'guard_name' => 'web'], ['descripcion' => 'Eliminar proyectos', 'parent_id' => $proyectos->id]);
        Permission::firstOrCreate(['name' => 'proyectos.aprobar', 'guard_name' => 'web'], ['descripcion' => 'Aprobar proyectos', 'parent_id' => $proyectos->id]);

        // ============================================================
        // SOLICITUDES
        // ============================================================
        $solicitudes = Permission::firstOrCreate(
            ['name' => 'solicitudes', 'guard_name' => 'web'],
            ['descripcion' => 'Módulo de Solicitudes', 'parent_id' => null]
        );
        Permission::firstOrCreate(['name' => 'solicitudes.ver', 'guard_name' => 'web'], ['descripcion' => 'Ver solicitudes', 'parent_id' => $solicitudes->id]);
        Permission::firstOrCreate(['name' => 'solicitudes.crear', 'guard_name' => 'web'], ['descripcion' => 'Crear solicitudes', 'parent_id' => $solicitudes->id]);
        Permission::firstOrCreate(['name' => 'solicitudes.editar', 'guard_name' => 'web'], ['descripcion' => 'Editar solicitudes', 'parent_id' => $solicitudes->id]);
        Permission::firstOrCreate(['name' => 'solicitudes.eliminar', 'guard_name' => 'web'], ['descripcion' => 'Eliminar solicitudes', 'parent_id' => $solicitudes->id]);
        Permission::firstOrCreate(['name' => 'solicitudes.aprobar', 'guard_name' => 'web'], ['descripcion' => 'Aprobar solicitudes', 'parent_id' => $solicitudes->id]);

        // ============================================================
        // ADMINISTRATIVO (parent)
        // ============================================================
        $administrativo = Permission::firstOrCreate(
            ['name' => 'administrativo', 'guard_name' => 'web'],
            ['descripcion' => 'Módulo Administrativo', 'parent_id' => null]
        );
        Permission::firstOrCreate(['name' => 'administrativo.ver', 'guard_name' => 'web'], ['descripcion' => 'Ver módulo Administrativo', 'parent_id' => $administrativo->id]);

        // ADQUISICIONES (sub-module)
        $adquisiciones = Permission::firstOrCreate(
            ['name' => 'adquisiciones', 'guard_name' => 'web'],
            ['descripcion' => 'Sub-módulo Adquisiciones', 'parent_id' => $administrativo->id]
        );
        Permission::firstOrCreate(['name' => 'adquisiciones.ver', 'guard_name' => 'web'], ['descripcion' => 'Ver adquisiciones', 'parent_id' => $adquisiciones->id]);
        Permission::firstOrCreate(['name' => 'adquisiciones.crear', 'guard_name' => 'web'], ['descripcion' => 'Crear adquisiciones', 'parent_id' => $adquisiciones->id]);
        Permission::firstOrCreate(['name' => 'adquisiciones.editar', 'guard_name' => 'web'], ['descripcion' => 'Editar adquisiciones', 'parent_id' => $adquisiciones->id]);
        Permission::firstOrCreate(['name' => 'adquisiciones.eliminar', 'guard_name' => 'web'], ['descripcion' => 'Eliminar adquisiciones', 'parent_id' => $adquisiciones->id]);

        // CAJA (sub-module)
        $caja = Permission::firstOrCreate(
            ['name' => 'caja', 'guard_name' => 'web'],
            ['descripcion' => 'Sub-módulo Caja', 'parent_id' => $administrativo->id]
        );
        Permission::firstOrCreate(['name' => 'caja.ver', 'guard_name' => 'web'], ['descripcion' => 'Ver caja', 'parent_id' => $caja->id]);
        Permission::firstOrCreate(['name' => 'caja.crear', 'guard_name' => 'web'], ['descripcion' => 'Crear registros en caja', 'parent_id' => $caja->id]);
        Permission::firstOrCreate(['name' => 'caja.editar', 'guard_name' => 'web'], ['descripcion' => 'Editar registros de caja', 'parent_id' => $caja->id]);
        Permission::firstOrCreate(['name' => 'caja.eliminar', 'guard_name' => 'web'], ['descripcion' => 'Eliminar registros de caja', 'parent_id' => $caja->id]);

        // CONTRATISTAS (sub-module)
        $contratistas = Permission::firstOrCreate(
            ['name' => 'contratistas', 'guard_name' => 'web'],
            ['descripcion' => 'Sub-módulo Contratistas', 'parent_id' => $administrativo->id]
        );
        Permission::firstOrCreate(['name' => 'contratistas.ver', 'guard_name' => 'web'], ['descripcion' => 'Ver contratistas', 'parent_id' => $contratistas->id]);
        Permission::firstOrCreate(['name' => 'contratistas.crear', 'guard_name' => 'web'], ['descripcion' => 'Crear contratistas', 'parent_id' => $contratistas->id]);
        Permission::firstOrCreate(['name' => 'contratistas.editar', 'guard_name' => 'web'], ['descripcion' => 'Editar contratistas', 'parent_id' => $contratistas->id]);
        Permission::firstOrCreate(['name' => 'contratistas.eliminar', 'guard_name' => 'web'], ['descripcion' => 'Eliminar contratistas', 'parent_id' => $contratistas->id]);

        // MANO_OBRA (sub-module)
        $manoObra = Permission::firstOrCreate(
            ['name' => 'mano_obra', 'guard_name' => 'web'],
            ['descripcion' => 'Sub-módulo Mano de Obra', 'parent_id' => $administrativo->id]
        );
        Permission::firstOrCreate(['name' => 'mano_obra.ver', 'guard_name' => 'web'], ['descripcion' => 'Ver mano de obra', 'parent_id' => $manoObra->id]);
        Permission::firstOrCreate(['name' => 'mano_obra.crear', 'guard_name' => 'web'], ['descripcion' => 'Crear registros de mano de obra', 'parent_id' => $manoObra->id]);
        Permission::firstOrCreate(['name' => 'mano_obra.editar', 'guard_name' => 'web'], ['descripcion' => 'Editar mano de obra', 'parent_id' => $manoObra->id]);
        Permission::firstOrCreate(['name' => 'mano_obra.eliminar', 'guard_name' => 'web'], ['descripcion' => 'Eliminar mano de obra', 'parent_id' => $manoObra->id]);

        // PRESTAMOS (sub-module)
        $prestamos = Permission::firstOrCreate(
            ['name' => 'prestamos', 'guard_name' => 'web'],
            ['descripcion' => 'Sub-módulo Préstamos', 'parent_id' => $administrativo->id]
        );
        Permission::firstOrCreate(['name' => 'prestamos.ver', 'guard_name' => 'web'], ['descripcion' => 'Ver préstamos', 'parent_id' => $prestamos->id]);
        Permission::firstOrCreate(['name' => 'prestamos.crear', 'guard_name' => 'web'], ['descripcion' => 'Crear préstamos', 'parent_id' => $prestamos->id]);
        Permission::firstOrCreate(['name' => 'prestamos.editar', 'guard_name' => 'web'], ['descripcion' => 'Editar préstamos', 'parent_id' => $prestamos->id]);
        Permission::firstOrCreate(['name' => 'prestamos.eliminar', 'guard_name' => 'web'], ['descripcion' => 'Eliminar préstamos', 'parent_id' => $prestamos->id]);

        // ============================================================
        // AGENDA
        // ============================================================
        $agenda = Permission::firstOrCreate(
            ['name' => 'agenda', 'guard_name' => 'web'],
            ['descripcion' => 'Módulo de Agenda', 'parent_id' => null]
        );
        Permission::firstOrCreate(['name' => 'agenda.ver', 'guard_name' => 'web'], ['descripcion' => 'Ver agenda', 'parent_id' => $agenda->id]);
        Permission::firstOrCreate(['name' => 'agenda.crear', 'guard_name' => 'web'], ['descripcion' => 'Crear eventos en agenda', 'parent_id' => $agenda->id]);
        Permission::firstOrCreate(['name' => 'agenda.editar', 'guard_name' => 'web'], ['descripcion' => 'Editar eventos de agenda', 'parent_id' => $agenda->id]);
        Permission::firstOrCreate(['name' => 'agenda.eliminar', 'guard_name' => 'web'], ['descripcion' => 'Eliminar eventos de agenda', 'parent_id' => $agenda->id]);
        Permission::firstOrCreate(['name' => 'agenda.exportar', 'guard_name' => 'web'], ['descripcion' => 'Exportar agenda', 'parent_id' => $agenda->id]);

        // ============================================================
        // REPORTERIA
        // ============================================================
        $reporteria = Permission::firstOrCreate(
            ['name' => 'reporteria', 'guard_name' => 'web'],
            ['descripcion' => 'Módulo de Reportería', 'parent_id' => null]
        );
        Permission::firstOrCreate(['name' => 'reporteria.ver', 'guard_name' => 'web'], ['descripcion' => 'Ver reportería', 'parent_id' => $reporteria->id]);
        Permission::firstOrCreate(['name' => 'reporteria.financiero.ver', 'guard_name' => 'web'], ['descripcion' => 'Ver reportes financieros', 'parent_id' => $reporteria->id]);
        Permission::firstOrCreate(['name' => 'reporteria.caja.ver', 'guard_name' => 'web'], ['descripcion' => 'Ver reportes de caja', 'parent_id' => $reporteria->id]);

        // ============================================================
        // MARKETING
        // ============================================================
        $marketing = Permission::firstOrCreate(
            ['name' => 'marketing', 'guard_name' => 'web'],
            ['descripcion' => 'Módulo de Marketing', 'parent_id' => null]
        );
        Permission::firstOrCreate(['name' => 'marketing.ver', 'guard_name' => 'web'], ['descripcion' => 'Ver marketing', 'parent_id' => $marketing->id]);
        Permission::firstOrCreate(['name' => 'marketing.crear', 'guard_name' => 'web'], ['descripcion' => 'Crear contenido de marketing', 'parent_id' => $marketing->id]);
        Permission::firstOrCreate(['name' => 'marketing.editar', 'guard_name' => 'web'], ['descripcion' => 'Editar marketing', 'parent_id' => $marketing->id]);
        Permission::firstOrCreate(['name' => 'marketing.eliminar', 'guard_name' => 'web'], ['descripcion' => 'Eliminar marketing', 'parent_id' => $marketing->id]);

        // ============================================================
        // PROFORMAS
        // ============================================================
        $proformas = Permission::firstOrCreate(
            ['name' => 'proformas', 'guard_name' => 'web'],
            ['descripcion' => 'Módulo de Proformas', 'parent_id' => null]
        );
        Permission::firstOrCreate(['name' => 'proformas.ver', 'guard_name' => 'web'], ['descripcion' => 'Ver proformas', 'parent_id' => $proformas->id]);
        Permission::firstOrCreate(['name' => 'proformas.crear', 'guard_name' => 'web'], ['descripcion' => 'Crear proformas', 'parent_id' => $proformas->id]);
        Permission::firstOrCreate(['name' => 'proformas.editar', 'guard_name' => 'web'], ['descripcion' => 'Editar proformas', 'parent_id' => $proformas->id]);
        Permission::firstOrCreate(['name' => 'proformas.eliminar', 'guard_name' => 'web'], ['descripcion' => 'Eliminar proformas', 'parent_id' => $proformas->id]);

        // ============================================================
        // CLIENTES
        // ============================================================
        $clientes = Permission::firstOrCreate(
            ['name' => 'clientes', 'guard_name' => 'web'],
            ['descripcion' => 'Módulo de Clientes', 'parent_id' => null]
        );
        Permission::firstOrCreate(['name' => 'clientes.ver', 'guard_name' => 'web'], ['descripcion' => 'Ver clientes', 'parent_id' => $clientes->id]);
        Permission::firstOrCreate(['name' => 'clientes.crear', 'guard_name' => 'web'], ['descripcion' => 'Crear clientes', 'parent_id' => $clientes->id]);
        Permission::firstOrCreate(['name' => 'clientes.editar', 'guard_name' => 'web'], ['descripcion' => 'Editar clientes', 'parent_id' => $clientes->id]);
        Permission::firstOrCreate(['name' => 'clientes.eliminar', 'guard_name' => 'web'], ['descripcion' => 'Eliminar clientes', 'parent_id' => $clientes->id]);

        // ============================================================
        // JP LIMPIEZA
        // ============================================================
        $jpLimpieza = Permission::firstOrCreate(
            ['name' => 'jp_limpieza', 'guard_name' => 'web'],
            ['descripcion' => 'Módulo JP Limpieza', 'parent_id' => null]
        );
        Permission::firstOrCreate(['name' => 'jp_limpieza.ver', 'guard_name' => 'web'], ['descripcion' => 'Ver JP Limpieza', 'parent_id' => $jpLimpieza->id]);

        // JP Limpieza - Adquisiciones
        $jpAdquisiciones = Permission::firstOrCreate(
            ['name' => 'jp_limpieza.adquisiciones', 'guard_name' => 'web'],
            ['descripcion' => 'JP Limpieza - Adquisiciones', 'parent_id' => $jpLimpieza->id]
        );
        Permission::firstOrCreate(['name' => 'jp_limpieza.adquisiciones.ver', 'guard_name' => 'web'], ['descripcion' => 'Ver adquisiciones JP', 'parent_id' => $jpAdquisiciones->id]);
        Permission::firstOrCreate(['name' => 'jp_limpieza.adquisiciones.crear', 'guard_name' => 'web'], ['descripcion' => 'Crear adquisiciones JP', 'parent_id' => $jpAdquisiciones->id]);
        Permission::firstOrCreate(['name' => 'jp_limpieza.adquisiciones.editar', 'guard_name' => 'web'], ['descripcion' => 'Editar adquisiciones JP', 'parent_id' => $jpAdquisiciones->id]);
        Permission::firstOrCreate(['name' => 'jp_limpieza.adquisiciones.eliminar', 'guard_name' => 'web'], ['descripcion' => 'Eliminar adquisiciones JP', 'parent_id' => $jpAdquisiciones->id]);

        // JP Limpieza - Caja
        $jpCaja = Permission::firstOrCreate(
            ['name' => 'jp_limpieza.caja', 'guard_name' => 'web'],
            ['descripcion' => 'JP Limpieza - Caja', 'parent_id' => $jpLimpieza->id]
        );
        Permission::firstOrCreate(['name' => 'jp_limpieza.caja.ver', 'guard_name' => 'web'], ['descripcion' => 'Ver caja JP', 'parent_id' => $jpCaja->id]);
        Permission::firstOrCreate(['name' => 'jp_limpieza.caja.crear', 'guard_name' => 'web'], ['descripcion' => 'Crear registros caja JP', 'parent_id' => $jpCaja->id]);
        Permission::firstOrCreate(['name' => 'jp_limpieza.caja.editar', 'guard_name' => 'web'], ['descripcion' => 'Editar caja JP', 'parent_id' => $jpCaja->id]);
        Permission::firstOrCreate(['name' => 'jp_limpieza.caja.eliminar', 'guard_name' => 'web'], ['descripcion' => 'Eliminar caja JP', 'parent_id' => $jpCaja->id]);

        // JP Limpieza - Contratistas
        $jpContratistas = Permission::firstOrCreate(
            ['name' => 'jp_limpieza.contratistas', 'guard_name' => 'web'],
            ['descripcion' => 'JP Limpieza - Contratistas', 'parent_id' => $jpLimpieza->id]
        );
        Permission::firstOrCreate(['name' => 'jp_limpieza.contratistas.ver', 'guard_name' => 'web'], ['descripcion' => 'Ver contratistas JP', 'parent_id' => $jpContratistas->id]);
        Permission::firstOrCreate(['name' => 'jp_limpieza.contratistas.crear', 'guard_name' => 'web'], ['descripcion' => 'Crear contratistas JP', 'parent_id' => $jpContratistas->id]);
        Permission::firstOrCreate(['name' => 'jp_limpieza.contratistas.editar', 'guard_name' => 'web'], ['descripcion' => 'Editar contratistas JP', 'parent_id' => $jpContratistas->id]);
        Permission::firstOrCreate(['name' => 'jp_limpieza.contratistas.eliminar', 'guard_name' => 'web'], ['descripcion' => 'Eliminar contratistas JP', 'parent_id' => $jpContratistas->id]);

        // JP Limpieza - Reportes
        $jpReportes = Permission::firstOrCreate(
            ['name' => 'jp_limpieza.reportes', 'guard_name' => 'web'],
            ['descripcion' => 'JP Limpieza - Reportes', 'parent_id' => $jpLimpieza->id]
        );
        Permission::firstOrCreate(['name' => 'jp_limpieza.reportes.ver', 'guard_name' => 'web'], ['descripcion' => 'Ver reportes JP', 'parent_id' => $jpReportes->id]);

        // ============================================================
        // SISTEMA
        // ============================================================
        $sistema = Permission::firstOrCreate(
            ['name' => 'sistema', 'guard_name' => 'web'],
            ['descripcion' => 'Módulo de Sistema', 'parent_id' => null]
        );
        Permission::firstOrCreate(['name' => 'sistema.ver', 'guard_name' => 'web'], ['descripcion' => 'Ver módulo de Sistema', 'parent_id' => $sistema->id]);

        // Sistema - Usuarios
        $sistemaUsuarios = Permission::firstOrCreate(
            ['name' => 'sistema.usuarios', 'guard_name' => 'web'],
            ['descripcion' => 'Sistema - Gestión de Usuarios', 'parent_id' => $sistema->id]
        );
        Permission::firstOrCreate(['name' => 'sistema.usuarios.ver', 'guard_name' => 'web'], ['descripcion' => 'Ver usuarios', 'parent_id' => $sistemaUsuarios->id]);
        Permission::firstOrCreate(['name' => 'sistema.usuarios.crear', 'guard_name' => 'web'], ['descripcion' => 'Crear usuarios', 'parent_id' => $sistemaUsuarios->id]);
        Permission::firstOrCreate(['name' => 'sistema.usuarios.editar', 'guard_name' => 'web'], ['descripcion' => 'Editar usuarios', 'parent_id' => $sistemaUsuarios->id]);
        Permission::firstOrCreate(['name' => 'sistema.usuarios.eliminar', 'guard_name' => 'web'], ['descripcion' => 'Eliminar usuarios', 'parent_id' => $sistemaUsuarios->id]);
        Permission::firstOrCreate(['name' => 'sistema.usuarios.gestionar', 'guard_name' => 'web'], ['descripcion' => 'Gestionar usuarios del sistema', 'parent_id' => $sistemaUsuarios->id]);

        // Sistema - Configuración
        $sistemaConfig = Permission::firstOrCreate(
            ['name' => 'sistema.configuracion', 'guard_name' => 'web'],
            ['descripcion' => 'Sistema - Configuración', 'parent_id' => $sistema->id]
        );
        Permission::firstOrCreate(['name' => 'sistema.configuracion.ver', 'guard_name' => 'web'], ['descripcion' => 'Ver configuración', 'parent_id' => $sistemaConfig->id]);
        Permission::firstOrCreate(['name' => 'sistema.configuracion.gestionar', 'guard_name' => 'web'], ['descripcion' => 'Gestionar configuración del sistema', 'parent_id' => $sistemaConfig->id]);

        // Sistema - Proveedores
        $sistemaProveedores = Permission::firstOrCreate(
            ['name' => 'sistema.proveedores', 'guard_name' => 'web'],
            ['descripcion' => 'Sistema - Proveedores', 'parent_id' => $sistema->id]
        );
        Permission::firstOrCreate(['name' => 'sistema.proveedores.ver', 'guard_name' => 'web'], ['descripcion' => 'Ver proveedores', 'parent_id' => $sistemaProveedores->id]);
        Permission::firstOrCreate(['name' => 'sistema.proveedores.crear', 'guard_name' => 'web'], ['descripcion' => 'Crear proveedores', 'parent_id' => $sistemaProveedores->id]);
        Permission::firstOrCreate(['name' => 'sistema.proveedores.editar', 'guard_name' => 'web'], ['descripcion' => 'Editar proveedores', 'parent_id' => $sistemaProveedores->id]);
        Permission::firstOrCreate(['name' => 'sistema.proveedores.eliminar', 'guard_name' => 'web'], ['descripcion' => 'Eliminar proveedores', 'parent_id' => $sistemaProveedores->id]);

        // Sistema - Productos
        $sistemaProductos = Permission::firstOrCreate(
            ['name' => 'sistema.productos', 'guard_name' => 'web'],
            ['descripcion' => 'Sistema - Productos', 'parent_id' => $sistema->id]
        );
        Permission::firstOrCreate(['name' => 'sistema.productos.ver', 'guard_name' => 'web'], ['descripcion' => 'Ver productos', 'parent_id' => $sistemaProductos->id]);
        Permission::firstOrCreate(['name' => 'sistema.productos.crear', 'guard_name' => 'web'], ['descripcion' => 'Crear productos', 'parent_id' => $sistemaProductos->id]);
        Permission::firstOrCreate(['name' => 'sistema.productos.editar', 'guard_name' => 'web'], ['descripcion' => 'Editar productos', 'parent_id' => $sistemaProductos->id]);
        Permission::firstOrCreate(['name' => 'sistema.productos.eliminar', 'guard_name' => 'web'], ['descripcion' => 'Eliminar productos', 'parent_id' => $sistemaProductos->id]);

        // Sistema - Inventario
        $sistemaInventario = Permission::firstOrCreate(
            ['name' => 'sistema.inventario', 'guard_name' => 'web'],
            ['descripcion' => 'Sistema - Inventario', 'parent_id' => $sistema->id]
        );
        Permission::firstOrCreate(['name' => 'sistema.inventario.ver', 'guard_name' => 'web'], ['descripcion' => 'Ver inventario', 'parent_id' => $sistemaInventario->id]);
        Permission::firstOrCreate(['name' => 'sistema.inventario.crear', 'guard_name' => 'web'], ['descripcion' => 'Crear registros de inventario', 'parent_id' => $sistemaInventario->id]);
        Permission::firstOrCreate(['name' => 'sistema.inventario.editar', 'guard_name' => 'web'], ['descripcion' => 'Editar inventario', 'parent_id' => $sistemaInventario->id]);
        Permission::firstOrCreate(['name' => 'sistema.inventario.eliminar', 'guard_name' => 'web'], ['descripcion' => 'Eliminar inventario', 'parent_id' => $sistemaInventario->id]);

        // Sistema - Rubros
        $sistemaRubros = Permission::firstOrCreate(
            ['name' => 'sistema.rubros', 'guard_name' => 'web'],
            ['descripcion' => 'Sistema - Rubros', 'parent_id' => $sistema->id]
        );
        Permission::firstOrCreate(['name' => 'sistema.rubros.ver', 'guard_name' => 'web'], ['descripcion' => 'Ver rubros', 'parent_id' => $sistemaRubros->id]);

        // Sistema - Clientes
        $sistemaClientes = Permission::firstOrCreate(
            ['name' => 'sistema.clientes', 'guard_name' => 'web'],
            ['descripcion' => 'Sistema - Clientes', 'parent_id' => $sistema->id]
        );
        Permission::firstOrCreate(['name' => 'sistema.clientes.ver', 'guard_name' => 'web'], ['descripcion' => 'Ver clientes del sistema', 'parent_id' => $sistemaClientes->id]);
        Permission::firstOrCreate(['name' => 'sistema.clientes.crear', 'guard_name' => 'web'], ['descripcion' => 'Crear clientes del sistema', 'parent_id' => $sistemaClientes->id]);
        Permission::firstOrCreate(['name' => 'sistema.clientes.editar', 'guard_name' => 'web'], ['descripcion' => 'Editar clientes del sistema', 'parent_id' => $sistemaClientes->id]);
        Permission::firstOrCreate(['name' => 'sistema.clientes.eliminar', 'guard_name' => 'web'], ['descripcion' => 'Eliminar clientes del sistema', 'parent_id' => $sistemaClientes->id]);

        app()->make(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
