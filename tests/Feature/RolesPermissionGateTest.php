<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * Spec: odd/tasks/permiso-roles-y-summernote-agenda.md, S1-S3.
 *
 * S1: /sistema/roles is gated by sistema.roles.ver / sistema.roles.gestionar, not role:Administrador.
 * S2: the permission group exists and renders in the role edit form so it can be assigned.
 * S3: no role receives it implicitly through the 'sistema%' wildcard.
 */
class RolesPermissionGateTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\PermissionTableSeeder::class);
        $this->seed(\Database\Seeders\RoleTableSeeder::class);
    }

    private function makeUser(string $usuario): User
    {
        return User::factory()->create(['usuario' => $usuario]);
    }

    /** @test */
    public function sistema_roles_permissions_are_seeded_under_sistema()
    {
        $sistema = Permission::where('name', 'sistema')->firstOrFail();

        foreach (['sistema.roles.ver', 'sistema.roles.gestionar'] as $name) {
            $permission = Permission::where('name', $name)->firstOrFail();
            // La pantalla de roles solo renderiza dos niveles, así que el permiso
            // debe colgar directamente de 'sistema' para poder asignarse.
            $this->assertSame($sistema->id, $permission->parent_id, "{$name} debe colgar de sistema");
        }
    }

    /** @test */
    public function gerencial_does_not_receive_roles_permissions_through_the_sistema_wildcard()
    {
        $gerencial = Role::findByName('Gerencial');

        $this->assertTrue($gerencial->hasPermissionTo('sistema.usuarios.ver'));
        $this->assertFalse($gerencial->hasPermissionTo('sistema.roles.ver'));
        $this->assertFalse($gerencial->hasPermissionTo('sistema.roles.gestionar'));
    }

    /** @test */
    public function administrador_keeps_access_through_the_gate_bypass()
    {
        $user = $this->makeUser('admin_gate_test');
        $user->assignRole('Administrador');

        $this->actingAs($user)->get(route('sistema.roles.index'))->assertOk();
    }

    /** @test */
    public function a_role_granted_sistema_roles_ver_can_enter_the_module()
    {
        $user = $this->makeUser('ver_grants_index');
        // El módulo /sistema exige sistema.ver; el submódulo exige sistema.roles.ver.
        $user->givePermissionTo(['sistema.ver', 'sistema.roles.ver']);

        $this->actingAs($user)->get(route('sistema.roles.index'))->assertOk();
        $this->actingAs($user)
            ->get(route('sistema.roles.edit', Role::findByName('Operativo')))
            ->assertOk();
    }

    /** @test */
    public function sistema_roles_ver_alone_is_not_enough_to_enter_the_sistema_module()
    {
        $user = $this->makeUser('ver_without_sistema_ver');
        $user->givePermissionTo('sistema.roles.ver');

        $this->actingAs($user)->get(route('sistema.roles.index'))->assertForbidden();
    }

    /** @test */
    public function a_user_without_the_permission_is_denied()
    {
        $user = $this->makeUser('denied_index');

        $this->actingAs($user)->get(route('sistema.roles.index'))->assertForbidden();
    }

    /** @test */
    public function viewing_the_module_does_not_grant_update_rights()
    {
        $user = $this->makeUser('ver_cannot_update');
        $user->givePermissionTo(['sistema.ver', 'sistema.roles.ver']);
        $role = Role::findByName('Operativo');
        $before = $role->permissions()->pluck('name')->sort()->values()->all();

        $this->actingAs($user)
            ->put(route('sistema.roles.update', $role), ['permissions' => []])
            ->assertForbidden();

        $this->assertSame($before, $role->fresh()->permissions()->pluck('name')->sort()->values()->all());
    }

    /** @test */
    public function the_edit_form_renders_the_roles_permissions_for_assignment()
    {
        $user = $this->makeUser('admin_form_render');
        $user->assignRole('Administrador');

        $response = $this->actingAs($user)->get(route('sistema.roles.edit', Role::findByName('Operativo')));

        $response->assertOk();
        $response->assertSee('sistema.roles.ver');
        $response->assertSee('sistema.roles.gestionar');
        $response->assertSee('Ver roles y permisos');
        $response->assertSee('Gestionar roles y permisos');
    }

    /** @test */
    public function gestionar_allows_updating_role_permissions()
    {
        $user = $this->makeUser('gestionar_updates');
        $user->givePermissionTo(['sistema.ver', 'sistema.roles.ver', 'sistema.roles.gestionar']);
        $role = Role::findByName('Operativo');
        $permission = Permission::where('name', 'sistema.roles.ver')->firstOrFail();

        $this->actingAs($user)
            ->put(route('sistema.roles.update', $role), ['permissions' => [$permission->id]])
            ->assertRedirect(route('sistema.roles.edit', $role));

        $this->assertTrue($role->fresh()->hasPermissionTo('sistema.roles.ver'));
    }
}
