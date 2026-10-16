<?php

namespace Tests\Feature;

use App\Models\Log;
use App\Models\User;
use App\Services\LogService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AuditoriaLogTest extends TestCase
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
        $user = User::factory()->create(['usuario' => 'admin_aud', 'correo' => 'admin_aud@test.com']);
        $user->assignRole(Role::findByName('Administrador'));

        return $user;
    }

    private function operativo(): User
    {
        // Operativo no tiene ningun permiso sistema.* (ver RoleTableSeeder).
        // Ojo: Gerencial NO sirve para este caso porque el seeder actual le
        // otorga sistema.* (los tests viejos que afirman lo contrario ya
        // fallan en base por eso).
        $user = User::factory()->create(['usuario' => 'operativo_aud', 'correo' => 'operativo_aud@test.com']);
        $user->assignRole(Role::findByName('Operativo'));

        return $user;
    }

    public function test_auditar_persiste_columnas_de_auditoria(): void
    {
        $admin = $this->admin();

        // Peticion previa para que haya request con IP (127.0.0.1 en tests).
        $this->actingAs($admin)->get(route('sistema.index'));

        LogService::auditar(
            'crear',
            'caja',
            42,
            'Movimiento de caja registrado',
            ['saldo' => 100],
            ['saldo' => 150]
        );

        $log = Log::latest('id')->first();

        $this->assertSame('info', $log->level);
        $this->assertSame('Movimiento de caja registrado', $log->message);
        $this->assertSame('crear', $log->accion);
        $this->assertSame('caja', $log->entidad);
        $this->assertEquals(42, $log->entidad_id);
        $this->assertEquals($admin->id, $log->user_id);
        $this->assertSame('127.0.0.1', $log->ip);
        $this->assertSame(['saldo' => 100], $log->valores_antes);
        $this->assertSame(['saldo' => 150], $log->valores_despues);
        $this->assertSame(['user_id' => $admin->id], $log->context);
    }

    public function test_auditar_sin_request_no_revienta(): void
    {
        // Sin ninguna peticion HTTP previa (contexto tipo CLI/cola).
        $this->assertNull(LogService::auditar('eliminar', 'caja', 7, 'Baja sin request'));

        $this->assertDatabaseHas('logs', [
            'accion' => 'eliminar',
            'entidad' => 'caja',
            'entidad_id' => 7,
        ]);
    }

    public function test_log_mantiene_forma_vieja_sin_columnas_de_auditoria(): void
    {
        $admin = $this->admin();
        $this->actingAs($admin);

        // Firma y comportamiento originales: level, message, context con user_id.
        LogService::log('error', 'Algo fallo', ['extra' => 1]);

        $log = Log::latest('id')->first();

        $this->assertSame('error', $log->level);
        $this->assertSame('Algo fallo', $log->message);
        // assertEquals y no assertSame: MySQL puede devolver las claves del JSON en otro orden.
        $this->assertEquals(['user_id' => $admin->id, 'extra' => 1], $log->context);
        $this->assertNull($log->entidad);
        $this->assertNull($log->entidad_id);
        $this->assertNull($log->accion);
        $this->assertNull($log->valores_antes);
        $this->assertNull($log->valores_despues);
        $this->assertNull($log->user_id);
        $this->assertNull($log->ip);
    }

    public function test_usuario_sin_permiso_no_accede_al_visor(): void
    {
        $response = $this->actingAs($this->operativo())->get(route('sistema.auditoria.index'));

        $response->assertForbidden();
    }

    public function test_usuario_con_permiso_ve_el_registro(): void
    {
        $admin = $this->admin();
        $this->actingAs($admin);

        LogService::auditar('crear', 'prestamo', 5, 'Prestamo desembolsado unico-xyz', [], ['saldo' => 500]);

        $response = $this->actingAs($admin)->get(route('sistema.auditoria.index'));

        $response->assertOk();
        $response->assertSee('Prestamo desembolsado unico-xyz');
        $response->assertSee('prestamo');
    }

    public function test_visor_tolera_usuario_eliminado(): void
    {
        $admin = $this->admin();

        Log::create([
            'level' => 'info',
            'message' => 'Registro huerfano',
            'context' => ['user_id' => 999999],
            'entidad' => 'caja',
            'entidad_id' => 1,
            'accion' => 'crear',
            'user_id' => 999999,
        ]);

        $response = $this->actingAs($admin)->get(route('sistema.auditoria.index'));

        $response->assertOk();
        $response->assertSee('(usuario eliminado)');
    }

    public function test_visor_filtra_por_entidad_y_accion(): void
    {
        $admin = $this->admin();
        $this->actingAs($admin);

        LogService::auditar('crear', 'caja', 1, 'Alta de caja filtro-a');
        LogService::auditar('eliminar', 'prestamo', 2, 'Baja de prestamo filtro-b');

        $response = $this->actingAs($admin)->get(route('sistema.auditoria.index', ['entidad' => 'caja']));

        $response->assertOk();
        $response->assertSee('Alta de caja filtro-a');
        $response->assertDontSee('Baja de prestamo filtro-b');

        $response = $this->actingAs($admin)->get(route('sistema.auditoria.index', ['accion' => 'eliminar']));

        $response->assertOk();
        $response->assertSee('Baja de prestamo filtro-b');
        $response->assertDontSee('Alta de caja filtro-a');
    }

    public function test_visor_filtra_por_texto_y_fechas(): void
    {
        $admin = $this->admin();
        $this->actingAs($admin);

        $viejo = Log::create([
            'level' => 'info',
            'message' => 'Movimiento antiguo texto-raro',
            'context' => ['user_id' => $admin->id],
        ]);
        $viejo->created_at = now()->subDays(10);
        $viejo->save();

        LogService::auditar('crear', 'caja', 9, 'Movimiento reciente');

        $response = $this->actingAs($admin)->get(route('sistema.auditoria.index', ['q' => 'texto-raro']));

        $response->assertOk();
        $response->assertSee('texto-raro');
        $response->assertDontSee('Movimiento reciente');

        $response = $this->actingAs($admin)->get(route('sistema.auditoria.index', [
            'desde' => now()->subDays(2)->format('Y-m-d'),
        ]));

        $response->assertOk();
        $response->assertSee('Movimiento reciente');
        $response->assertDontSee('texto-raro');
    }

    public function test_purgar_elimina_solo_viejos_con_retencion_por_defecto(): void
    {
        $this->assertSame(365, config('auditoria.retencion_dias'));

        $viejo = Log::create(['level' => 'info', 'message' => 'viejo', 'context' => []]);
        $viejo->created_at = now()->subDays(400);
        $viejo->save();

        $nuevo = Log::create(['level' => 'info', 'message' => 'nuevo', 'context' => []]);

        $this->artisan('logs:purgar')
            ->expectsOutputToContain('1 registro(s)')
            ->assertExitCode(0);

        $this->assertDatabaseMissing('logs', ['id' => $viejo->id]);
        $this->assertDatabaseHas('logs', ['id' => $nuevo->id]);
    }

    public function test_purgar_respeta_dias(): void
    {
        $medio = Log::create(['level' => 'info', 'message' => 'medio', 'context' => []]);
        $medio->created_at = now()->subDays(10);
        $medio->save();

        $reciente = Log::create(['level' => 'info', 'message' => 'reciente', 'context' => []]);
        $reciente->created_at = now()->subDays(2);
        $reciente->save();

        $this->artisan('logs:purgar', ['--dias' => 5])->assertExitCode(0);

        $this->assertDatabaseMissing('logs', ['id' => $medio->id]);
        $this->assertDatabaseHas('logs', ['id' => $reciente->id]);
    }
}
