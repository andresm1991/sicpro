<?php

namespace Tests\Feature;

use App\Models\CatalogoDato;
use App\Models\Log;
use App\Models\PagoPrestamo;
use App\Models\Prestamo;
use App\Models\Proveedor;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuditoriaPrestamoRecalculoTest extends TestCase
{
    use RefreshDatabase;

    private User $actor;

    protected function setUp(): void
    {
        parent::setUp();

        $this->actor = User::factory()->create(['usuario' => 'auditor_recalculo', 'correo' => 'auditor_recalculo@test.com']);

        CatalogoDato::create(['descripcion' => 'Pendiente', 'slug' => 'estados.prestamos.pendiente', 'activo' => true]);
        CatalogoDato::create(['descripcion' => 'Pagado', 'slug' => 'estados.prestamos.pagado', 'activo' => true]);
        CatalogoDato::create(['descripcion' => 'Aprobado', 'slug' => 'estados.prestamos.aprobado', 'activo' => true]);
        CatalogoDato::create(['descripcion' => 'Pendiente', 'slug' => 'estados.pagos.prestamos.pendiente', 'activo' => true]);
        CatalogoDato::create(['descripcion' => 'Pagado', 'slug' => 'estados.pagos.prestamos.pagado', 'activo' => true]);
        CatalogoDato::create(['descripcion' => 'Otro', 'slug' => 'metodos.pagos.otro', 'activo' => true]);
    }

    public function test_recalculo_deja_fila_de_auditoria(): void
    {
        $categoria = CatalogoDato::create(['descripcion' => 'Categoria test', 'slug' => 'categoria-test-' . uniqid(), 'activo' => true]);
        $trabajador = Proveedor::create([
            'categoria_proveedor_id' => $categoria->id,
            'documento' => '0999999999001',
            'razon_social' => 'Trabajador recalculo test',
            'direccion' => 'Direccion test',
        ]);

        $estadoPrestamo = CatalogoDato::where('slug', 'estados.prestamos.pendiente')->first();
        $estadoPago = CatalogoDato::where('slug', 'estados.pagos.prestamos.pendiente')->first();
        $metodoOtro = CatalogoDato::where('slug', 'metodos.pagos.otro')->first();

        $prestamo = Prestamo::create([
            'trabajador_id' => $trabajador->id,
            'fecha_solicitud' => now()->format('Y-m-d'),
            'monto' => 1000,
            'saldo' => 1000,
            'interes' => 0,
            'plazo' => 4,
            'estado_id' => $estadoPrestamo->id,
            'motivo' => 'Prestamo recalculo test',
        ]);

        foreach (range(1, 4) as $i) {
            PagoPrestamo::create([
                'prestamo_id' => $prestamo->id,
                'fecha_pago' => now()->addWeeks($i)->format('Y-m-d'),
                'monto_pagado' => 0,
                'monto_programado' => 250,
                'estado_id' => $estadoPago->id,
                'metodo_pago_id' => $metodoOtro->id,
            ]);
        }

        // Desbalancear el plan para que el recalculo lo reescriba.
        $prestamo->pago_prestamo()->orderBy('fecha_pago')->first()->update(['monto_programado' => 100]);

        $response = $this->actingAs($this->actor)->post(
            '/administrativo/prestamos/recalcular_pagos',
            ['prestamo' => $prestamo->id],
            ['X-Requested-With' => 'XMLHttpRequest']
        );

        $response->assertOk();
        $response->assertJson(['success' => true]);

        $log = Log::where('entidad', 'prestamo')
            ->where('entidad_id', $prestamo->id)
            ->where('accion', 'recalcular')
            ->latest('id')
            ->first();

        $this->assertNotNull($log);
        $this->assertEquals($this->actor->id, $log->user_id);
        $this->assertEquals(1000, $log->valores_antes['saldo']);
        $this->assertEquals(1000, $log->valores_despues['saldo']);
        $this->assertEquals(4, $log->valores_antes['cantidad_pagos']);
        $this->assertEquals(4, $log->valores_despues['cantidad_pagos']);
        $this->assertContains(100.0, array_values(array_map('floatval', $log->valores_antes['plan'])));
        $this->assertEquals([250.0, 250.0, 250.0, 250.0], array_values(array_map('floatval', $log->valores_despues['plan'])));
    }
}
