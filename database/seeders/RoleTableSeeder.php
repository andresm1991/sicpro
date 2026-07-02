<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        app()->make(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();

        $roleNames = [
            'Administrador',
            'Gerencial',
            'Operativo',
            'Administrativo',
            'Marketing',
            'Sistema',
        ];

        foreach ($roleNames as $name) {
            Role::firstOrCreate(
                ['name' => $name, 'guard_name' => 'web']
            );
        }

        // ============================================================
        // ADMINISTRADOR: No explicit permissions needed (Gate::before bypass)
        // ============================================================
        $admin = Role::findByName('Administrador');
        $admin->syncPermissions([]);

        // ============================================================
        // GERENCIAL: All modules EXCEPT sistema.*, reporteria.financiero.*
        // ============================================================
        $gerencial = Role::findByName('Gerencial');
        $gerencialPermissions = Permission::where(function ($q) {
            $q->where('name', 'like', 'gerencia%')
                ->orWhere('name', 'like', 'proyectos%')
                ->orWhere('name', 'like', 'solicitudes%')
                ->orWhere('name', 'like', 'administrativo%')
                ->orWhere('name', 'like', 'adquisiciones%')
                ->orWhere('name', 'like', 'caja%')
                ->orWhere('name', 'like', 'contratistas%')
                ->orWhere('name', 'like', 'mano_obra%')
                ->orWhere('name', 'like', 'prestamos%')
                ->orWhere('name', 'like', 'agenda%')
                ->orWhere('name', 'like', 'reporteria.ver')
                ->orWhere('name', 'like', 'reporteria.caja.ver')
                ->orWhere('name', 'like', 'marketing%')
                ->orWhere('name', 'like', 'proformas%')
                ->orWhere('name', 'like', 'clientes%')
                ->orWhere('name', 'like', 'jp_limpieza%');
        })->whereNotIn('name', [
            'reporteria.financiero.ver',
        ])->pluck('name')->toArray();
        $gerencial->syncPermissions($gerencialPermissions);

        // ============================================================
        // OPERATIVO: proyectos.ver, solicitudes.ver, solicitudes.crear, agenda.ver
        // ============================================================
        $operativo = Role::findByName('Operativo');
        $operativo->syncPermissions([
            'proyectos.ver',
            'solicitudes.ver',
            'solicitudes.crear',
            'agenda.ver',
        ]);

        // ============================================================
        // ADMINISTRATIVO: adquisiciones.*, caja.*, proformas.*, clientes.*
        // ============================================================
        $administrativo = Role::findByName('Administrativo');
        $administrativoPermissions = Permission::where(function ($q) {
            $q->where('name', 'like', 'adquisiciones%')
                ->orWhere('name', 'like', 'caja%')
                ->orWhere('name', 'like', 'contratistas%')
                ->orWhere('name', 'like', 'mano_obra%')
                ->orWhere('name', 'like', 'prestamos%')
                ->orWhere('name', 'like', 'administrativo%')
                ->orWhere('name', 'like', 'proformas%')
                ->orWhere('name', 'like', 'clientes%');
        })->pluck('name')->toArray();
        $administrativo->syncPermissions($administrativoPermissions);

        // ============================================================
        // MARKETING: marketing.*
        // ============================================================
        $marketing = Role::findByName('Marketing');
        $marketing->syncPermissions(
            Permission::where('name', 'like', 'marketing%')->pluck('name')->toArray()
        );

        // ============================================================
        // SISTEMA: NO UI permissions (for API/automated processes only)
        // ============================================================
        $sistema = Role::findByName('Sistema');
        $sistema->syncPermissions([]);

        app()->make(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
