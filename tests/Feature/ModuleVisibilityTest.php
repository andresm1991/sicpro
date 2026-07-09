<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ModuleVisibilityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\PermissionTableSeeder::class);
        $this->seed(\Database\Seeders\RoleTableSeeder::class);
    }

    /**
     * Test that Admin and Gerencial can see the Gerencia module on home.
     */
    public function test_admin_and_gerencial_can_see_gerencia_module()
    {
        $adminRole = Role::findByName('Administrador');
        $admin = User::factory()->create(['usuario' => 'admin_vis', 'correo' => 'admin_vis@test.com']);
        $admin->assignRole($adminRole);

        $this->assertTrue($admin->can('gerencia.ver'));

        $gerencialRole = Role::findByName('Gerencial');
        $gerencial = User::factory()->create(['usuario' => 'gerencial_vis', 'correo' => 'gerencial_vis@test.com']);
        $gerencial->assignRole($gerencialRole);

        $this->assertTrue($gerencial->can('gerencia.ver'));
    }

    /**
     * Test that Operativo cannot see the Gerencia module.
     */
    public function test_operativo_cannot_see_gerencia_module()
    {
        $operativoRole = Role::findByName('Operativo');
        $operativo = User::factory()->create(['usuario' => 'operativo_vis', 'correo' => 'operativo_vis@test.com']);
        $operativo->assignRole($operativoRole);

        $this->assertFalse($operativo->can('gerencia.ver'));
    }

    /**
     * Test that Admin and Gerencial can see Marketing module.
     */
    public function test_admin_and_gerencial_can_see_marketing_module()
    {
        $adminRole = Role::findByName('Administrador');
        $admin = User::factory()->create(['usuario' => 'admin_mkt', 'correo' => 'admin_mkt@test.com']);
        $admin->assignRole($adminRole);

        $this->assertTrue($admin->can('marketing.ver'));

        $gerencialRole = Role::findByName('Gerencial');
        $gerencial = User::factory()->create(['usuario' => 'gerencial_mkt', 'correo' => 'gerencial_mkt@test.com']);
        $gerencial->assignRole($gerencialRole);

        $this->assertTrue($gerencial->can('marketing.ver'));
    }

    /**
     * Test that Operativo cannot see Marketing module.
     */
    public function test_operativo_cannot_see_marketing_module()
    {
        $operativoRole = Role::findByName('Operativo');
        $operativo = User::factory()->create(['usuario' => 'operativo_mkt', 'correo' => 'operativo_mkt@test.com']);
        $operativo->assignRole($operativoRole);

        $this->assertFalse($operativo->can('marketing.ver'));
    }

    /**
     * Test that Admin and Gerencial can see Administrativo module.
     */
    public function test_admin_and_gerencial_can_see_administrativo_module()
    {
        $adminRole = Role::findByName('Administrador');
        $admin = User::factory()->create(['usuario' => 'admin_adm', 'correo' => 'admin_adm@test.com']);
        $admin->assignRole($adminRole);

        $this->assertTrue($admin->can('administrativo.ver'));

        $gerencialRole = Role::findByName('Gerencial');
        $gerencial = User::factory()->create(['usuario' => 'gerencial_adm', 'correo' => 'gerencial_adm@test.com']);
        $gerencial->assignRole($gerencialRole);

        $this->assertTrue($gerencial->can('administrativo.ver'));
    }

    /**
     * Test that Operativo cannot see Administrativo module.
     */
    public function test_operativo_cannot_see_administrativo_module()
    {
        $operativoRole = Role::findByName('Operativo');
        $operativo = User::factory()->create(['usuario' => 'operativo_adm', 'correo' => 'operativo_adm@test.com']);
        $operativo->assignRole($operativoRole);

        $this->assertFalse($operativo->can('administrativo.ver'));
    }

    /**
     * Test that Admin and Gerencial can see Proformas module.
     */
    public function test_admin_and_gerencial_can_see_proformas_module()
    {
        $adminRole = Role::findByName('Administrador');
        $admin = User::factory()->create(['usuario' => 'admin_pro', 'correo' => 'admin_pro@test.com']);
        $admin->assignRole($adminRole);

        $this->assertTrue($admin->can('proformas.ver'));

        $gerencialRole = Role::findByName('Gerencial');
        $gerencial = User::factory()->create(['usuario' => 'gerencial_pro', 'correo' => 'gerencial_pro@test.com']);
        $gerencial->assignRole($gerencialRole);

        $this->assertTrue($gerencial->can('proformas.ver'));
    }

    /**
     * Test that Operativo cannot see Proformas module.
     */
    public function test_operativo_cannot_see_proformas_module()
    {
        $operativoRole = Role::findByName('Operativo');
        $operativo = User::factory()->create(['usuario' => 'operativo_pro', 'correo' => 'operativo_pro@test.com']);
        $operativo->assignRole($operativoRole);

        $this->assertFalse($operativo->can('proformas.ver'));
    }

    /**
     * Test that Admin can see Sistema module (via Gate::before bypass).
     */
    public function test_admin_can_see_sistema_module_via_bypass()
    {
        $adminRole = Role::findByName('Administrador');
        $admin = User::factory()->create(['usuario' => 'admin_sys', 'correo' => 'admin_sys@test.com']);
        $admin->assignRole($adminRole);

        // Admin bypasses all permission checks via Gate::before
        $this->assertTrue($admin->can('sistema.ver'));
    }

    /**
     * Test that Gerencial cannot see Sistema module.
     */
    public function test_gerencial_cannot_see_sistema_module()
    {
        $gerencialRole = Role::findByName('Gerencial');
        $gerencial = User::factory()->create(['usuario' => 'gerencial_sys', 'correo' => 'gerencial_sys@test.com']);
        $gerencial->assignRole($gerencialRole);

        $this->assertFalse($gerencial->can('sistema.ver'));
    }

    /**
     * Test that Admin and Gerencial can edit solicitudes.
     */
    public function test_admin_and_gerencial_can_edit_solicitudes()
    {
        $adminRole = Role::findByName('Administrador');
        $admin = User::factory()->create(['usuario' => 'admin_sol', 'correo' => 'admin_sol@test.com']);
        $admin->assignRole($adminRole);

        $this->assertTrue($admin->can('solicitudes.editar'));

        $gerencialRole = Role::findByName('Gerencial');
        $gerencial = User::factory()->create(['usuario' => 'gerencial_sol', 'correo' => 'gerencial_sol@test.com']);
        $gerencial->assignRole($gerencialRole);

        $this->assertTrue($gerencial->can('solicitudes.editar'));
    }

    /**
     * Test that Operativo cannot edit solicitudes.
     */
    public function test_operativo_cannot_edit_solicitudes()
    {
        $operativoRole = Role::findByName('Operativo');
        $operativo = User::factory()->create(['usuario' => 'operativo_sol', 'correo' => 'operativo_sol@test.com']);
        $operativo->assignRole($operativoRole);

        $this->assertFalse($operativo->can('solicitudes.editar'));
    }

    /**
     * Test that Admin and Gerencial can manage agenda (filter/export).
     */
    public function test_admin_and_gerencial_can_manage_agenda()
    {
        $adminRole = Role::findByName('Administrador');
        $admin = User::factory()->create(['usuario' => 'admin_agd', 'correo' => 'admin_agd@test.com']);
        $admin->assignRole($adminRole);

        $this->assertTrue($admin->can('agenda.editar'));

        $gerencialRole = Role::findByName('Gerencial');
        $gerencial = User::factory()->create(['usuario' => 'gerencial_agd', 'correo' => 'gerencial_agd@test.com']);
        $gerencial->assignRole($gerencialRole);

        $this->assertTrue($gerencial->can('agenda.editar'));
    }

    /**
     * Test that Operativo can see agenda but not manage it.
     */
    public function test_operativo_can_see_agenda_but_not_manage()
    {
        $operativoRole = Role::findByName('Operativo');
        $operativo = User::factory()->create(['usuario' => 'operativo_agd', 'correo' => 'operativo_agd@test.com']);
        $operativo->assignRole($operativoRole);

        $this->assertTrue($operativo->can('agenda.ver'));
        $this->assertFalse($operativo->can('agenda.editar'));
    }

    /**
     * Test that Admin and Gerencial can delete from caja.
     */
    public function test_admin_and_gerencial_can_delete_caja()
    {
        $adminRole = Role::findByName('Administrador');
        $admin = User::factory()->create(['usuario' => 'admin_caja', 'correo' => 'admin_caja@test.com']);
        $admin->assignRole($adminRole);

        $this->assertTrue($admin->can('caja.eliminar'));

        $gerencialRole = Role::findByName('Gerencial');
        $gerencial = User::factory()->create(['usuario' => 'gerencial_caja', 'correo' => 'gerencial_caja@test.com']);
        $gerencial->assignRole($gerencialRole);

        $this->assertTrue($gerencial->can('caja.eliminar'));
    }

    /**
     * Test that Operativo cannot delete from caja.
     */
    public function test_operativo_cannot_delete_caja()
    {
        $operativoRole = Role::findByName('Operativo');
        $operativo = User::factory()->create(['usuario' => 'operativo_caja', 'correo' => 'operativo_caja@test.com']);
        $operativo->assignRole($operativoRole);

        $this->assertFalse($operativo->can('caja.eliminar'));
    }

    /**
     * Test that Admin and Gerencial can delete adquisiciones.
     */
    public function test_admin_and_gerencial_can_delete_adquisiciones()
    {
        $adminRole = Role::findByName('Administrador');
        $admin = User::factory()->create(['usuario' => 'admin_adq', 'correo' => 'admin_adq@test.com']);
        $admin->assignRole($adminRole);

        $this->assertTrue($admin->can('adquisiciones.eliminar'));

        $gerencialRole = Role::findByName('Gerencial');
        $gerencial = User::factory()->create(['usuario' => 'gerencial_adq', 'correo' => 'gerencial_adq@test.com']);
        $gerencial->assignRole($gerencialRole);

        $this->assertTrue($gerencial->can('adquisiciones.eliminar'));
    }

    /**
     * Test that Operativo cannot delete adquisiciones.
     */
    public function test_operativo_cannot_delete_adquisiciones()
    {
        $operativoRole = Role::findByName('Operativo');
        $operativo = User::factory()->create(['usuario' => 'operativo_adq', 'correo' => 'operativo_adq@test.com']);
        $operativo->assignRole($operativoRole);

        $this->assertFalse($operativo->can('adquisiciones.eliminar'));
    }

    // ============================================================
    // JP Limpieza Module Tests (Phase 3)
    // ============================================================

    /**
     * Test that Admin and Gerencial can delete from JP Limpieza caja.
     */
    public function test_admin_and_gerencial_can_delete_jp_limpieza_caja()
    {
        $adminRole = Role::findByName('Administrador');
        $admin = User::factory()->create(['usuario' => 'admin_jp_caja', 'correo' => 'admin_jp_caja@test.com']);
        $admin->assignRole($adminRole);

        $this->assertTrue($admin->can('jp_limpieza.caja.eliminar'));

        $gerencialRole = Role::findByName('Gerencial');
        $gerencial = User::factory()->create(['usuario' => 'gerencial_jp_caja', 'correo' => 'gerencial_jp_caja@test.com']);
        $gerencial->assignRole($gerencialRole);

        $this->assertTrue($gerencial->can('jp_limpieza.caja.eliminar'));
    }

    /**
     * Test that Operativo cannot delete from JP Limpieza caja.
     */
    public function test_operativo_cannot_delete_jp_limpieza_caja()
    {
        $operativoRole = Role::findByName('Operativo');
        $operativo = User::factory()->create(['usuario' => 'operativo_jp_caja', 'correo' => 'operativo_jp_caja@test.com']);
        $operativo->assignRole($operativoRole);

        $this->assertFalse($operativo->can('jp_limpieza.caja.eliminar'));
    }

    /**
     * Test that Admin and Gerencial can edit JP Limpieza adquisiciones.
     */
    public function test_admin_and_gerencial_can_edit_jp_limpieza_adquisiciones()
    {
        $adminRole = Role::findByName('Administrador');
        $admin = User::factory()->create(['usuario' => 'admin_jp_adq', 'correo' => 'admin_jp_adq@test.com']);
        $admin->assignRole($adminRole);

        $this->assertTrue($admin->can('jp_limpieza.adquisiciones.editar'));

        $gerencialRole = Role::findByName('Gerencial');
        $gerencial = User::factory()->create(['usuario' => 'gerencial_jp_adq', 'correo' => 'gerencial_jp_adq@test.com']);
        $gerencial->assignRole($gerencialRole);

        $this->assertTrue($gerencial->can('jp_limpieza.adquisiciones.editar'));
    }

    /**
     * Test that Operativo cannot edit JP Limpieza adquisiciones.
     */
    public function test_operativo_cannot_edit_jp_limpieza_adquisiciones()
    {
        $operativoRole = Role::findByName('Operativo');
        $operativo = User::factory()->create(['usuario' => 'operativo_jp_adq', 'correo' => 'operativo_jp_adq@test.com']);
        $operativo->assignRole($operativoRole);

        $this->assertFalse($operativo->can('jp_limpieza.adquisiciones.editar'));
    }

    /**
     * Test that Admin and Gerencial can edit JP Limpieza contratistas.
     */
    public function test_admin_and_gerencial_can_edit_jp_limpieza_contratistas()
    {
        $adminRole = Role::findByName('Administrador');
        $admin = User::factory()->create(['usuario' => 'admin_jp_ctr', 'correo' => 'admin_jp_ctr@test.com']);
        $admin->assignRole($adminRole);

        $this->assertTrue($admin->can('jp_limpieza.contratistas.editar'));

        $gerencialRole = Role::findByName('Gerencial');
        $gerencial = User::factory()->create(['usuario' => 'gerencial_jp_ctr', 'correo' => 'gerencial_jp_ctr@test.com']);
        $gerencial->assignRole($gerencialRole);

        $this->assertTrue($gerencial->can('jp_limpieza.contratistas.editar'));
    }

    /**
     * Test that Operativo cannot edit JP Limpieza contratistas.
     */
    public function test_operativo_cannot_edit_jp_limpieza_contratistas()
    {
        $operativoRole = Role::findByName('Operativo');
        $operativo = User::factory()->create(['usuario' => 'operativo_jp_ctr', 'correo' => 'operativo_jp_ctr@test.com']);
        $operativo->assignRole($operativoRole);

        $this->assertFalse($operativo->can('jp_limpieza.contratistas.editar'));
    }

    /**
     * Test that Admin and Gerencial can view JP Limpieza reportes.
     */
    public function test_admin_and_gerencial_can_view_jp_limpieza_reportes()
    {
        $adminRole = Role::findByName('Administrador');
        $admin = User::factory()->create(['usuario' => 'admin_jp_rep', 'correo' => 'admin_jp_rep@test.com']);
        $admin->assignRole($adminRole);

        $this->assertTrue($admin->can('jp_limpieza.reportes.ver'));

        $gerencialRole = Role::findByName('Gerencial');
        $gerencial = User::factory()->create(['usuario' => 'gerencial_jp_rep', 'correo' => 'gerencial_jp_rep@test.com']);
        $gerencial->assignRole($gerencialRole);

        $this->assertTrue($gerencial->can('jp_limpieza.reportes.ver'));
    }

    /**
     * Test that Operativo cannot view JP Limpieza reportes.
     */
    public function test_operativo_cannot_view_jp_limpieza_reportes()
    {
        $operativoRole = Role::findByName('Operativo');
        $operativo = User::factory()->create(['usuario' => 'operativo_jp_rep', 'correo' => 'operativo_jp_rep@test.com']);
        $operativo->assignRole($operativoRole);

        $this->assertFalse($operativo->can('jp_limpieza.reportes.ver'));
    }

    // ============================================================
    // Reporteria Module Tests (Phase 3)
    // ============================================================

    /**
     * Test that Admin and Gerencial can view reporteria caja reports.
     */
    public function test_admin_and_gerencial_can_view_reporteria_caja()
    {
        $adminRole = Role::findByName('Administrador');
        $admin = User::factory()->create(['usuario' => 'admin_rep_caja', 'correo' => 'admin_rep_caja@test.com']);
        $admin->assignRole($adminRole);

        $this->assertTrue($admin->can('reporteria.caja.ver'));

        $gerencialRole = Role::findByName('Gerencial');
        $gerencial = User::factory()->create(['usuario' => 'gerencial_rep_caja', 'correo' => 'gerencial_rep_caja@test.com']);
        $gerencial->assignRole($gerencialRole);

        $this->assertTrue($gerencial->can('reporteria.caja.ver'));
    }

    /**
     * Test that Operativo cannot view reporteria caja reports.
     */
    public function test_operativo_cannot_view_reporteria_caja()
    {
        $operativoRole = Role::findByName('Operativo');
        $operativo = User::factory()->create(['usuario' => 'operativo_rep_caja', 'correo' => 'operativo_rep_caja@test.com']);
        $operativo->assignRole($operativoRole);

        $this->assertFalse($operativo->can('reporteria.caja.ver'));
    }
}
