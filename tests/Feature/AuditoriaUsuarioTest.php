<?php

namespace Tests\Feature;

use App\Models\Log;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * Auditoria de usuarios y permisos (T8): el alta, la edicion (roles y clave),
 * el cambio de clave propio, la baja y el syncPermissions dejan una fila en
 * `logs` con entidad/accion/antes/despues y el usuario actor. La clave y su
 * hash nunca se guardan.
 */
class AuditoriaUsuarioTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\PermissionTableSeeder::class);
        $this->seed(\Database\Seeders\RoleTableSeeder::class);
    }

    private function admin(): User
    {
        $user = User::factory()->create(['usuario' => 'admin_aud_user_test', 'correo' => 'admin_aud_user_test@example.com']);
        $user->assignRole(Role::findByName('Administrador'));

        return $user;
    }

    private function altaPayload(Role $perfil): array
    {
        return [
            'nombres' => 'Juan Perez',
            'usuario' => 'jperez_aud_test',
            'password' => 'secreta123',
            'confirm_password' => 'secreta123',
            'perfil' => $perfil->id,
            'activo' => '1',
            'email' => 'jperez_aud@example.com',
            'telefono' => '0999999999',
        ];
    }

    public function test_alta_de_usuario_deja_auditoria_con_sus_roles(): void
    {
        $actor = $this->admin();
        $perfil = Role::findByName('Operativo');

        $response = $this->actingAs($actor)->post(route('sistema.users.store'), $this->altaPayload($perfil));

        $response->assertRedirect();
        $nuevo = User::where('usuario', 'jperez_aud_test')->first();
        $this->assertNotNull($nuevo);

        $log = Log::where('entidad', 'user')->where('accion', 'crear')->latest('id')->first();

        $this->assertNotNull($log);
        $this->assertEquals($nuevo->id, $log->entidad_id);
        $this->assertEquals($actor->id, $log->user_id);
        $this->assertEquals($actor->id, $log->context['user_id']);
        $this->assertContains('Operativo', $log->valores_despues['roles']);
        $this->assertSame('jperez_aud_test', $log->valores_despues['usuario']);
        $this->assertStringContainsString('jperez_aud_test', $log->message);
    }

    public function test_cambio_de_roles_deja_antes_y_despues_con_los_nombres(): void
    {
        $actor = $this->admin();
        $objetivo = User::factory()->create(['usuario' => 'objetivo_roles_test', 'correo' => 'objetivo_roles_test@example.com']);
        $objetivo->assignRole(Role::findByName('Operativo'));

        $gerencial = Role::findByName('Gerencial');

        $response = $this->actingAs($actor)->put(route('sistema.users.update', $objetivo), [
            'nombres' => $objetivo->nombre,
            'usuario' => 'objetivo_roles_test',
            'perfil' => $gerencial->id,
            'activo' => '1',
            'email' => $objetivo->correo,
            'telefono' => '0987654321',
        ]);

        $response->assertRedirect();

        $log = Log::where('entidad', 'user')->where('accion', 'actualizar')->latest('id')->first();

        $this->assertNotNull($log);
        $this->assertEquals($objetivo->id, $log->entidad_id);
        $this->assertEquals($actor->id, $log->user_id);
        $this->assertContains('Operativo', $log->valores_antes['roles']);
        $this->assertContains('Gerencial', $log->valores_despues['roles']);
        $this->assertStringContainsString('objetivo_roles_test', $log->message);
    }

    public function test_cambio_de_clave_deja_registro_sin_clave_ni_hash(): void
    {
        $actor = $this->admin();
        $objetivo = User::factory()->create(['usuario' => 'objetivo_clave_test', 'correo' => 'objetivo_clave_test@example.com']);
        $objetivo->assignRole(Role::findByName('Operativo'));
        $hashAnterior = $objetivo->clave;
        $nuevaClave = 'nueva-clave-456';

        $response = $this->actingAs($actor)->put(route('sistema.users.update', $objetivo), [
            'nombres' => $objetivo->nombre,
            'usuario' => 'objetivo_clave_test',
            'perfil' => Role::findByName('Operativo')->id,
            'activo' => '1',
            'password' => $nuevaClave,
            'confirm_password' => $nuevaClave,
        ]);

        $response->assertRedirect();
        $this->assertTrue(Hash::check($nuevaClave, $objetivo->fresh()->clave));

        $log = Log::where('entidad', 'user')->where('accion', 'actualizar')->latest('id')->first();

        $this->assertNotNull($log);
        $this->assertTrue($log->valores_despues['clave_cambiada'] ?? false);
        $serializado = json_encode([
            $log->valores_antes,
            $log->valores_despues,
            $log->message,
        ]);
        $this->assertStringNotContainsString($nuevaClave, $serializado);
        $this->assertStringNotContainsString($hashAnterior, $serializado);
        $this->assertStringNotContainsString($objetivo->fresh()->clave, $serializado);
        $this->assertArrayNotHasKey('clave', $log->valores_despues ?? []);
        $this->assertArrayNotHasKey('password', $log->valores_despues ?? []);
    }

    public function test_cambio_de_clave_propio_deja_registro_sin_clave_ni_hash(): void
    {
        $actor = $this->admin();
        $nuevaClave = 'propia-clave-789';

        $response = $this->actingAs($actor)->put(route('perfil.update', $actor), [
            'password' => $nuevaClave,
            'password_confirmation' => $nuevaClave,
        ]);

        $response->assertRedirect();
        $this->assertTrue(Hash::check($nuevaClave, $actor->fresh()->clave));

        $log = Log::where('entidad', 'user')
            ->where('accion', 'cambiar_clave')
            ->latest('id')
            ->first();

        $this->assertNotNull($log);
        $this->assertEquals($actor->id, $log->entidad_id);
        $this->assertEquals($actor->id, $log->user_id);
        $serializado = json_encode([
            $log->valores_antes,
            $log->valores_despues,
            $log->message,
        ]);
        $this->assertStringNotContainsString($nuevaClave, $serializado);
        $this->assertStringNotContainsString($actor->fresh()->clave, $serializado);
    }

    public function test_baja_de_usuario_deja_auditoria_sin_hash_antes_del_delete(): void
    {
        $actor = $this->admin();
        $objetivo = User::factory()->create(['usuario' => 'objetivo_baja_test', 'correo' => 'objetivo_baja_test@example.com']);
        $objetivo->assignRole(Role::findByName('Operativo'));
        $hash = $objetivo->clave;
        $objetivoId = $objetivo->id;
        $this->assertGreaterThan(1, $objetivoId);

        $response = $this->actingAs($actor)->delete("/sistema/usuarios/eliminar/{$objetivoId}");

        $response->assertOk()->assertJson(['success' => true]);
        $this->assertDatabaseMissing('usuarios', ['id' => $objetivoId]);

        $log = Log::where('entidad', 'user')->where('accion', 'eliminar')->latest('id')->first();

        $this->assertNotNull($log);
        $this->assertEquals($objetivoId, $log->entidad_id);
        $this->assertEquals($actor->id, $log->user_id);
        $this->assertSame('objetivo_baja_test', $log->valores_antes['usuario']);
        $this->assertContains('Operativo', $log->valores_antes['roles']);
        $this->assertArrayNotHasKey('clave', $log->valores_antes);
        $this->assertStringNotContainsString($hash, json_encode([$log->valores_antes, $log->message]));
    }

    public function test_sync_permissions_deja_permisos_antes_y_despues(): void
    {
        $actor = $this->admin();
        $rol = Role::findByName('Operativo');
        $permisosAntes = $rol->permissions()->pluck('name')->toArray();
        $this->assertNotEmpty($permisosAntes);

        $nuevosIds = Permission::whereIn('name', ['proyectos.ver', 'agenda.ver'])->pluck('id')->toArray();
        $this->assertCount(2, $nuevosIds);

        $response = $this->actingAs($actor)->put(route('sistema.roles.update', $rol), [
            'permissions' => $nuevosIds,
        ]);

        $response->assertRedirect();

        $log = Log::where('entidad', 'rol')->where('accion', 'actualizar')->latest('id')->first();

        $this->assertNotNull($log);
        $this->assertEquals($rol->id, $log->entidad_id);
        $this->assertEquals($actor->id, $log->user_id);
        $this->assertEqualsCanonicalizing($permisosAntes, $log->valores_antes['permisos']);
        $this->assertEqualsCanonicalizing(['proyectos.ver', 'agenda.ver'], $log->valores_despues['permisos']);
        $this->assertStringContainsString('Operativo', $log->message);
    }

    public function test_usuario_autenticado_queda_como_actor(): void
    {
        $actor = $this->admin();
        $perfil = Role::findByName('Operativo');

        $this->actingAs($actor)->post(route('sistema.users.store'), $this->altaPayload($perfil));

        $rol = Role::findByName('Gerencial');
        $nuevosIds = Permission::where('name', 'proyectos.ver')->pluck('id')->toArray();
        $this->actingAs($actor)->put(route('sistema.roles.update', $rol), ['permissions' => $nuevosIds]);

        $logs = Log::whereIn('entidad', ['user', 'rol'])->orderBy('id')->get();

        $this->assertCount(2, $logs);
        foreach ($logs as $log) {
            $this->assertEquals($actor->id, $log->user_id);
            $this->assertEquals($actor->id, $log->context['user_id']);
        }
    }
}
