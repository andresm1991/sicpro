<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class RoleManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\PermissionTableSeeder::class);
        $this->seed(\Database\Seeders\RoleTableSeeder::class);
    }

    /**
     * Admin can view the roles list page.
     */
    public function test_admin_can_view_roles_list()
    {
        $adminRole = Role::findByName('Administrador');
        $user = User::factory()->create(['usuario' => 'admin_roles_test']);
        $user->assignRole($adminRole);

        $response = $this->actingAs($user)->get(route('sistema.roles.index'));

        $response->assertOk();
        $response->assertViewIs('sistema.roles.index');
        // Verify all 6 roles are present
        foreach (['Administrador', 'Gerencial', 'Operativo', 'Administrativo', 'Marketing', 'Sistema'] as $roleName) {
            $response->assertSeeText($roleName);
        }
    }

    /**
     * Role list shows correct permission count per role.
     */
    public function test_role_list_shows_correct_permission_count()
    {
        $adminRole = Role::findByName('Administrador');
        $user = User::factory()->create(['usuario' => 'admin_count_test']);
        $user->assignRole($adminRole);

        $response = $this->actingAs($user)->get(route('sistema.roles.index'));

        $response->assertOk();
        // Operativo has exactly 4 permissions per RoleTableSeeder
        $response->assertSee('4');
    }

    /**
     * Admin can view the edit role form.
     */
    public function test_admin_can_view_edit_role_form()
    {
        $adminRole = Role::findByName('Administrador');
        $user = User::factory()->create(['usuario' => 'admin_edit_test']);
        $user->assignRole($adminRole);

        $role = Role::findByName('Operativo');
        $response = $this->actingAs($user)->get(route('sistema.roles.edit', $role));

        $response->assertOk();
        $response->assertViewIs('sistema.roles.edit');
        $response->assertSee('Operativo');
    }

    /**
     * Edit form shows permissions grouped by module with checkboxes.
     */
    public function test_edit_form_shows_permissions_grouped_by_module()
    {
        $adminRole = Role::findByName('Administrador');
        $user = User::factory()->create(['usuario' => 'admin_group_test']);
        $user->assignRole($adminRole);

        $role = Role::findByName('Operativo');
        $response = $this->actingAs($user)->get(route('sistema.roles.edit', $role));

        $response->assertOk();
        // Should show module group names (parent permissions)
        $response->assertSeeText('Módulo de Proyectos');
        $response->assertSeeText('Módulo de Solicitudes');
        // Should show permission checkboxes (child permissions)
        $response->assertSee('proyectos.ver');
        $response->assertSee('solicitudes.ver');
    }

    /**
     * Edit form pre-checks permissions that the role already has.
     */
    public function test_edit_form_pre_checks_existing_permissions()
    {
        $adminRole = Role::findByName('Administrador');
        $user = User::factory()->create(['usuario' => 'admin_precheck_test']);
        $user->assignRole($adminRole);

        $role = Role::findByName('Operativo');

        $response = $this->actingAs($user)->get(route('sistema.roles.edit', $role));

        $response->assertOk();
        // Operativo has 'proyectos.ver' — checkbox should be checked
        $response->assertSee('checked');
    }

    /**
     * Admin can update role permissions and they persist correctly.
     */
    public function test_admin_can_update_role_permissions()
    {
        $adminRole = Role::findByName('Administrador');
        $user = User::factory()->create(['usuario' => 'admin_update_test']);
        $user->assignRole($adminRole);

        $role = Role::findByName('Operativo');
        $originalCount = $role->permissions()->count(); // 4

        // Get all permission IDs to assign
        $allPermissionIds = Permission::whereNotNull('parent_id')->pluck('id')->toArray();

        $response = $this->actingAs($user)->put(route('sistema.roles.update', $role), [
            'permissions' => $allPermissionIds,
        ]);

        $response->assertRedirect(route('sistema.roles.edit', $role));
        $response->assertSessionHas('success');

        // Verify permissions were synced
        $role->refresh();
        $this->assertGreaterThan($originalCount, $role->permissions()->count());
    }

    /**
     * Updating role permissions with empty selection removes all permissions.
     */
    public function test_updating_role_with_no_permissions_removes_all()
    {
        $adminRole = Role::findByName('Administrador');
        $user = User::factory()->create(['usuario' => 'admin_empty_test']);
        $user->assignRole($adminRole);

        $role = Role::findByName('Gerencial');
        $this->assertGreaterThan(0, $role->permissions()->count());

        $response = $this->actingAs($user)->put(route('sistema.roles.update', $role), [
            'permissions' => [],
        ]);

        $response->assertRedirect();

        $role->refresh();
        $this->assertEquals(0, $role->permissions()->count());
    }

    /**
     * Non-admin gets 403 on roles index.
     */
    public function test_non_admin_gets_403_on_roles_index()
    {
        $gerencialRole = Role::findByName('Gerencial');
        $user = User::factory()->create(['usuario' => 'gerencial_roles_test']);
        $user->assignRole($gerencialRole);

        $response = $this->actingAs($user)->get(route('sistema.roles.index'));

        $response->assertForbidden();
    }

    /**
     * Non-admin gets 403 on roles edit.
     */
    public function test_non_admin_gets_403_on_roles_edit()
    {
        $gerencialRole = Role::findByName('Gerencial');
        $user = User::factory()->create(['usuario' => 'gerencial_edit_test']);
        $user->assignRole($gerencialRole);

        $role = Role::findByName('Operativo');
        $response = $this->actingAs($user)->get(route('sistema.roles.edit', $role));

        $response->assertForbidden();
    }

    /**
     * Non-admin gets 403 on roles update.
     */
    public function test_non_admin_gets_403_on_roles_update()
    {
        $gerencialRole = Role::findByName('Gerencial');
        $user = User::factory()->create(['usuario' => 'gerencial_update_test']);
        $user->assignRole($gerencialRole);

        $role = Role::findByName('Operativo');
        $response = $this->actingAs($user)->put(route('sistema.roles.update', $role), [
            'permissions' => [],
        ]);

        $response->assertForbidden();
    }

    /**
     * Unauthenticated user gets redirected to login.
     */
    public function test_unauthenticated_redirected_to_login_on_roles()
    {
        $response = $this->get(route('sistema.roles.index'));
        $response->assertRedirect(route('login'));
    }
}
