<?php

namespace Tests\Feature;

use App\Models\CatalogoDato;
use App\Models\Log;
use App\Models\Prestamo;
use App\Models\Proveedor;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuditoriaPrestamoTest extends TestCase
{
    use RefreshDatabase;

    private User $actor;

    private CatalogoDato $estadoPendiente;

    private CatalogoDato $estadoAprobado;

    protected function setUp(): void
    {
        parent::setUp();

        $this->actor = User::factory()->create(['usuario' => 'auditor_prestamo', 'correo' => 'auditor_prestamo@test.com']);

        $this->estadoPendiente = CatalogoDato::create(['descripcion' => 'Pendiente', 'slug' => 'estados.prestamos.pendiente', 'activo' => true]);
        $this->estadoAprobado = CatalogoDato::create(['descripcion' => 'Aprobado', 'slug' => 'estados.prestamos.aprobado', 'activo' => true]);
        CatalogoDato::create(['descripcion' => 'Pagado', 'slug' => 'estados.prestamos.pagado', 'activo' => true]);
        CatalogoDato::create(['descripcion' => 'Pendiente', 'slug' => 'estados.pagos.prestamos.pendiente', 'activo' => true]);
        CatalogoDato::create(['descripcion' => 'Pagado', 'slug' => 'estados.pagos.prestamos.pagado', 'activo' => true]);
        CatalogoDato::create(['descripcion' => 'Otro', 'slug' => 'metodos.pagos.otro', 'activo' => true]);
        CatalogoDato::create(['descripcion' => 'Efectivo', 'slug' => 'metodos.pagos.efectivo', 'activo' => true]);
    }

    private function trabajador(): Proveedor
    {
        $categoria = CatalogoDato::create(['descripcion' => 'Categoria test', 'slug' => 'categoria-test-' . uniqid(), 'activo' => true]);

        return Proveedor::create([
            'categoria_proveedor_id' => $categoria->id,
            'documento' => '0999999999001',
            'razon_social' => 'Trabajador auditoria test',
            'direccion' => 'Direccion test',
        ]);
    }

    private function ajaxHeaders(): array
    {
        return ['X-Requested-With' => 'XMLHttpRequest'];
    }

    public function test_alta_deja_fila_de_auditoria(): void
    {
        $trabajador = $this->trabajador();

        $response = $this->actingAs($this->actor)->post(
            '/administrativo/prestamos/nuevo',
            [
                'proveedor' => $trabajador->id,
                'fecha_solicitud' => now()->format('Y-m-d'),
                'monto' => '1000',
                'interes' => 10,
                'plazo' => 4,
                'estado' => $this->estadoPendiente->id,
                'motivo' => 'Emergencia medica test',
            ],
            $this->ajaxHeaders()
        );

        $response->assertOk();
        $response->assertJson(['success' => true]);

        $prestamo = Prestamo::latest('id')->first();
        $this->assertNotNull($prestamo);

        $log = Log::where('entidad', 'prestamo')
            ->where('entidad_id', $prestamo->id)
            ->where('accion', 'crear')
            ->latest('id')
            ->first();

        $this->assertNotNull($log);
        $this->assertEquals($this->actor->id, $log->user_id);
        $this->assertEquals(1000, $log->valores_despues['monto']);
        $this->assertEquals(10, $log->valores_despues['interes']);
        $this->assertEquals(4, $log->valores_despues['plazo']);
        $this->assertSame('Pendiente', $log->valores_despues['estado']);
        $this->assertSame('Emergencia medica test', $log->valores_despues['motivo']);
    }

    public function test_pago_deja_saldo_antes_y_despues(): void
    {
        $trabajador = $this->trabajador();

        // Alta aprobada para que se genere el cronograma de pagos.
        $this->actingAs($this->actor)->post(
            '/administrativo/prestamos/nuevo',
            [
                'proveedor' => $trabajador->id,
                'fecha_solicitud' => now()->format('Y-m-d'),
                'monto' => '1000',
                'interes' => 0,
                'plazo' => 4,
                'estado' => $this->estadoAprobado->id,
                'motivo' => 'Prestamo para pago test',
            ],
            $this->ajaxHeaders()
        );

        $prestamo = Prestamo::latest('id')->first();
        $this->assertNotNull($prestamo);
        $this->assertEquals(1000, (float) $prestamo->saldo);

        $pago = $prestamo->pago_prestamo()->orderBy('fecha_pago')->first();
        $this->assertNotNull($pago);

        $formaPago = CatalogoDato::where('slug', 'metodos.pagos.efectivo')->first();

        $response = $this->actingAs($this->actor)->put(
            '/administrativo/prestamos/pago/' . $pago->id,
            ['monto_pagado' => '100', 'forma_pago' => $formaPago->id],
            $this->ajaxHeaders()
        );

        $response->assertOk();
        $response->assertJson(['success' => true]);

        $this->assertEquals(900, (float) $prestamo->fresh()->saldo);

        $log = Log::where('entidad', 'prestamo')
            ->where('entidad_id', $prestamo->id)
            ->where('accion', 'pagar')
            ->latest('id')
            ->first();

        $this->assertNotNull($log);
        $this->assertEquals($this->actor->id, $log->user_id);
        $this->assertEquals(1000, $log->valores_antes['saldo']);
        $this->assertEquals(900, $log->valores_despues['saldo']);
        $this->assertArrayHasKey('estado', $log->valores_antes);
        $this->assertArrayHasKey('estado', $log->valores_despues);
        $this->assertStringContainsString('100', $log->message);
        $this->assertStringContainsString('900', $log->message);
    }

    public function test_edicion_deja_fila_de_auditoria_con_actor(): void
    {
        $trabajador = $this->trabajador();

        $prestamo = Prestamo::create([
            'trabajador_id' => $trabajador->id,
            'fecha_solicitud' => now()->format('Y-m-d'),
            'monto' => 1000,
            'saldo' => 1000,
            'interes' => 5,
            'plazo' => 4,
            'estado_id' => $this->estadoPendiente->id,
            'motivo' => 'Motivo original',
        ]);

        $response = $this->actingAs($this->actor)->put(
            '/administrativo/prestamos/actualizar/' . $prestamo->id,
            [
                'interes' => 8,
                'motivo' => 'Motivo editado',
                'plazo' => 4,
            ],
            $this->ajaxHeaders()
        );

        $response->assertOk();
        $response->assertJson(['success' => true]);

        $log = Log::where('entidad', 'prestamo')
            ->where('entidad_id', $prestamo->id)
            ->where('accion', 'actualizar')
            ->latest('id')
            ->first();

        $this->assertNotNull($log);
        $this->assertEquals($this->actor->id, $log->user_id);
        $this->assertSame('Motivo original', $log->valores_antes['motivo']);
        $this->assertSame('Motivo editado', $log->valores_despues['motivo']);
        $this->assertEquals(8, $log->valores_despues['interes']);
    }
}
