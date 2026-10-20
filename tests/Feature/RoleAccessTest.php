<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class RoleAccessTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Seed permissions and roles before each test
        $this->seed(\Database\Seeders\PermissionTableSeeder::class);
        $this->seed(\Database\Seeders\RoleTableSeeder::class);
    }

    /**
     * Test Gerencial role has broad access, including sistema administration.
     *
     * Decision del 2026-10-19: Gerencial administra usuarios y configuracion.
     */
    public function test_gerencial_has_broad_access_including_sistema()
    {
        $role = Role::findByName('Gerencial');
        $this->assertTrue($role->hasPermissionTo('proyectos.ver'));
        $this->assertTrue($role->hasPermissionTo('solicitudes.aprobar'));
        $this->assertTrue($role->hasPermissionTo('sistema.ver'));
        $this->assertTrue($role->hasPermissionTo('sistema.usuarios.gestionar'));
        $this->assertTrue($role->hasPermissionTo('sistema.configuracion.gestionar'));
        // Rubros no cuelga de sistema.ver: tiene su propio grupo y su propio permiso
        // (routes/modules/sistema.php:66), asi que sin esto Gerencial ve el menu
        // pero no puede entrar a la pantalla.
        $this->assertTrue($role->hasPermissionTo('sistema.rubros.ver'));
    }

    /**
     * Test Operativo role has limited permissions.
     */
    public function test_operativo_has_limited_permissions()
    {
        $role = Role::findByName('Operativo');
        $this->assertTrue($role->hasPermissionTo('proyectos.ver'));
        $this->assertTrue($role->hasPermissionTo('solicitudes.ver'));
        $this->assertTrue($role->hasPermissionTo('solicitudes.crear'));
        $this->assertTrue($role->hasPermissionTo('agenda.ver'));
        $this->assertFalse($role->hasPermissionTo('proyectos.editar'));
        $this->assertFalse($role->hasPermissionTo('solicitudes.eliminar'));
    }

    /**
     * Test Sistema role has NO UI permissions.
     */
    public function test_sistema_role_has_no_ui_permissions()
    {
        $role = Role::findByName('Sistema');
        $this->assertFalse($role->hasPermissionTo('proyectos.ver'));
        $this->assertFalse($role->hasPermissionTo('solicitudes.ver'));
        $this->assertFalse($role->hasPermissionTo('sistema.usuarios.gestionar'));
    }

    /**
     * Test Operativo (no sistema access) gets 403 on sistema routes.
     */
    public function test_operativo_gets_403_on_sistema_routes()
    {
        $operativoRole = Role::findByName('Operativo');
        $user = User::factory()->create(['usuario' => 'operativo_test']);
        $user->assignRole($operativoRole);

        $response = $this->actingAs($user)->get(route('sistema.index'));
        $response->assertForbidden();
    }

    /**
     * Test Gerencial can reach the user management screen.
     */
    public function test_gerencial_can_access_user_management()
    {
        $gerencialRole = Role::findByName('Gerencial');
        $user = User::factory()->create(['usuario' => 'gerencial_test']);
        $user->assignRole($gerencialRole);

        $response = $this->actingAs($user)->get(route('sistema.users.index'));
        $response->assertOk();
    }

    /**
     * Test Operativo gets 403 on user management routes.
     */
    public function test_operativo_gets_403_on_user_management()
    {
        $operativoRole = Role::findByName('Operativo');
        $user = User::factory()->create(['usuario' => 'operativo_test']);
        $user->assignRole($operativoRole);

        $response = $this->actingAs($user)->get(route('sistema.users.index'));
        $response->assertForbidden();
    }

    /**
     * Test any authenticated user can access their own profile.
     */
    public function test_any_user_can_access_own_profile()
    {
        $operativoRole = Role::findByName('Operativo');
        $user = User::factory()->create(['usuario' => 'operativo_test']);
        $user->assignRole($operativoRole);

        $response = $this->actingAs($user)->get(route('perfil.show'));
        $response->assertOk();
    }

    /**
     * Test Admin bypass: Admin can access sistema routes despite no explicit permissions.
     */
    public function test_admin_bypass_allows_sistema_access()
    {
        $adminRole = Role::findByName('Administrador');
        $user = User::factory()->create(['usuario' => 'admin_test']);
        $user->assignRole($adminRole);

        $response = $this->actingAs($user)->get(route('sistema.index'));
        $response->assertOk();
    }
}
