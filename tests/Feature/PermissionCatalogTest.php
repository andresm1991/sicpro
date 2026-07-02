<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class PermissionCatalogTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that PermissionSeeder creates parent permissions with null parent_id.
     */
    public function test_parent_permissions_exist_with_null_parent_id()
    {
        $this->seed(\Database\Seeders\PermissionTableSeeder::class);

        $parentNames = [
            'gerencia', 'proyectos', 'solicitudes', 'administrativo',
            'agenda', 'reporteria', 'marketing', 'proformas', 'clientes',
            'jp_limpieza', 'sistema',
        ];

        foreach ($parentNames as $name) {
            $permission = Permission::where('name', $name)->first();
            $this->assertNotNull($permission, "Parent permission '{$name}' should exist");
            $this->assertNull($permission->parent_id, "Parent permission '{$name}' should have null parent_id");
        }
    }

    /**
     * Test that child permissions reference correct parent.
     */
    public function test_child_permissions_reference_correct_parent()
    {
        $this->seed(\Database\Seeders\PermissionTableSeeder::class);

        $parent = Permission::where('name', 'proyectos')->first();
        $this->assertNotNull($parent);

        $childNames = ['proyectos.ver', 'proyectos.crear', 'proyectos.editar', 'proyectos.eliminar', 'proyectos.aprobar'];

        foreach ($childNames as $name) {
            $child = Permission::where('name', $name)->first();
            $this->assertNotNull($child, "Child permission '{$name}' should exist");
            $this->assertEquals($parent->id, $child->parent_id, "Child '{$name}' should reference proyectos parent");
        }
    }

    /**
     * Test that all permissions have descripcion field populated.
     */
    public function test_all_permissions_have_descripcion()
    {
        $this->seed(\Database\Seeders\PermissionTableSeeder::class);

        $permissionsWithoutDesc = Permission::whereNull('descripcion')
            ->orWhere('descripcion', '')
            ->count();

        $this->assertEquals(0, $permissionsWithoutDesc, 'All permissions should have a descripcion');
    }

    /**
     * Test that total permission count is approximately 90+.
     */
    public function test_permission_catalog_has_comprehensive_coverage()
    {
        $this->seed(\Database\Seeders\PermissionTableSeeder::class);

        $count = Permission::count();
        $this->assertGreaterThanOrEqual(80, $count, "Expected at least 80 permissions, got {$count}");
    }
}
