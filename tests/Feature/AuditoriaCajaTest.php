<?php

namespace Tests\Feature;

use App\Models\Log;
use App\Models\MovimientoCaja;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * Auditoria de caja (T3): el alta y la baja de movimientos de caja dejan
 * una fila en `logs` con entidad/accion/antes/despues y el usuario actor.
 */
class AuditoriaCajaTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\PermissionTableSeeder::class);
        $this->seed(\Database\Seeders\RoleTableSeeder::class);
    }

    private function usuario(): User
    {
        $user = User::factory()->create(['usuario' => 'usuario_caja_aud_test']);
        $user->assignRole(Role::findByName('Administrador'));

        return $user;
    }

    private function altaPayload(): array
    {
        return [
            'proveedor' => null,
            'detalle' => 'Venta de prueba auditoria',
            'tipo_movimiento' => 'ingreso',
            'monto' => '1500.50',
            'referencia' => 'REF-AUD-1',
        ];
    }

    public function test_alta_de_movimiento_deja_auditoria_con_monto_en_despues(): void
    {
        $user = $this->usuario();

        $response = $this->actingAs($user)->post(
            '/administrativo/caja/guardar',
            $this->altaPayload(),
            ['X-Requested-With' => 'XMLHttpRequest']
        );

        $response->assertOk()->assertJson(['success' => true]);

        $movimiento = MovimientoCaja::latest('id')->first();
        $this->assertNotNull($movimiento);

        $log = Log::where('entidad', 'movimiento_caja')
            ->where('accion', 'crear')
            ->latest('id')
            ->first();

        $this->assertNotNull($log);
        $this->assertEquals($movimiento->id, $log->entidad_id);
        $this->assertEquals(1500.50, (float) $log->valores_despues['monto']);
        $this->assertSame('ingreso', $log->valores_despues['tipo']);
        $this->assertStringContainsString('1,500.50', $log->message);
    }

    public function test_baja_de_movimiento_deja_auditoria_con_monto_en_antes(): void
    {
        $user = $this->usuario();

        $movimiento = MovimientoCaja::create([
            'fecha' => date('Y-m-d'),
            'tipo' => 'egreso',
            'monto' => 2500.75,
            'descripcion' => 'Egreso de prueba auditoria',
            'user_id' => $user->id,
        ]);

        $response = $this->actingAs($user)->delete(
            "/administrativo/caja/eliminar-movimiento/{$movimiento->id}",
            [],
            ['X-Requested-With' => 'XMLHttpRequest']
        );

        $response->assertOk()->assertJson(['success' => true]);

        // La fila de caja ya no existe: la auditoria es el unico rastro.
        $this->assertDatabaseMissing('movimientos_caja', ['id' => $movimiento->id]);

        $log = Log::where('entidad', 'movimiento_caja')
            ->where('accion', 'eliminar')
            ->latest('id')
            ->first();

        $this->assertNotNull($log);
        $this->assertEquals($movimiento->id, $log->entidad_id);
        $this->assertEquals(2500.75, (float) $log->valores_antes['monto']);
        $this->assertStringContainsString('2,500.75', $log->message);
    }

    public function test_usuario_autenticado_queda_como_actor(): void
    {
        $user = $this->usuario();

        $this->actingAs($user)->post(
            '/administrativo/caja/guardar',
            $this->altaPayload(),
            ['X-Requested-With' => 'XMLHttpRequest']
        );

        $movimiento = MovimientoCaja::create([
            'fecha' => date('Y-m-d'),
            'tipo' => 'egreso',
            'monto' => 100,
            'descripcion' => 'Egreso actor auditoria',
            'user_id' => $user->id,
        ]);

        $this->actingAs($user)->delete(
            "/administrativo/caja/eliminar-movimiento/{$movimiento->id}",
            [],
            ['X-Requested-With' => 'XMLHttpRequest']
        );

        $logs = Log::where('entidad', 'movimiento_caja')->orderBy('id')->get();

        $this->assertCount(2, $logs);
        foreach ($logs as $log) {
            $this->assertEquals($user->id, $log->user_id);
            $this->assertEquals($user->id, $log->context['user_id']);
        }
    }
}
