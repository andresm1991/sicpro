<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class SeederIdempotencyTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that running PermissionSeeder twice does not create duplicate permissions.
     */
    public function test_permission_seeder_is_idempotent()
    {
        $this->seed(\Database\Seeders\PermissionTableSeeder::class);
        $countAfterFirst = Permission::count();

        $this->seed(\Database\Seeders\PermissionTableSeeder::class);
        $countAfterSecond = Permission::count();

        $this->assertEquals($countAfterFirst, $countAfterSecond, 'Running PermissionSeeder twice should not create duplicates');
        $this->assertGreaterThanOrEqual(80, $countAfterFirst, 'Should have at least 80 permissions after first run');
    }

    /**
     * Test that running RoleSeeder twice produces identical role-permission state.
     */
    public function test_role_seeder_is_idempotent()
    {
        // First run: seed both
        $this->seed(\Database\Seeders\PermissionTableSeeder::class);
        $this->seed(\Database\Seeders\RoleTableSeeder::class);

        $roleCountAfterFirst = Role::count();
        $pivotCountAfterFirst = \DB::table('role_has_permissions')->count();

        // Second run: seed both again
        $this->seed(\Database\Seeders\PermissionTableSeeder::class);
        $this->seed(\Database\Seeders\RoleTableSeeder::class);

        $roleCountAfterSecond = Role::count();
        $pivotCountAfterSecond = \DB::table('role_has_permissions')->count();

        $this->assertEquals($roleCountAfterFirst, $roleCountAfterSecond, 'Role count should be identical after second run');
        $this->assertEquals($pivotCountAfterFirst, $pivotCountAfterSecond, 'Role-permission pivot count should be identical after second run');
    }

    /**
     * Test that DatabaseSeeder orchestrates seeders in correct order.
     */
    public function test_database_seeder_runs_in_correct_order()
    {
        $this->seed(\Database\Seeders\DatabaseSeeder::class);

        // Permissions should exist
        $this->assertGreaterThanOrEqual(80, Permission::count());

        // Roles should exist
        $this->assertEquals(6, Role::count());

        // Gerencial should have permissions assigned
        $gerencial = Role::findByName('Gerencial');
        $this->assertGreaterThan(0, $gerencial->permissions->count());
    }

    /**
     * Test that cache is cleared after seeding (forgetCachedPermissions).
     */
    public function test_cache_cleared_after_seeding()
    {
        $this->seed(\Database\Seeders\PermissionTableSeeder::class);

        // After seeding, permissions should be immediately queryable via Spatie
        $proyectosVer = Permission::findByName('proyectos.ver');
        $this->assertNotNull($proyectosVer);
    }
}
