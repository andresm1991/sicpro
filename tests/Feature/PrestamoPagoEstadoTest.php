<?php

namespace Tests\Feature;

use App\Models\CatalogoDato;
use App\Models\PagoPrestamo;
use App\Models\Prestamo;
use App\Models\Proveedor;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PrestamoPagoEstadoTest extends TestCase
{
    use RefreshDatabase;

    private User $actor;

    private CatalogoDato $estadoPrestamoPendiente;

    private CatalogoDato $estadoPrestamoPagado;

    private CatalogoDato $estadoPagoPendiente;

    private CatalogoDato $formaEfectivo;

    private CatalogoDato $metodoOtro;

    protected function setUp(): void
    {
        parent::setUp();

        $this->actor = User::factory()->create(['usuario' => 'pagos_estado', 'correo' => 'pagos_estado@test.com']);

        $this->estadoPrestamoPendiente = CatalogoDato::create(['descripcion' => 'Pendiente', 'slug' => 'estados.prestamos.pendiente', 'activo' => true]);
        $this->estadoPrestamoPagado = CatalogoDato::create(['descripcion' => 'Pagado', 'slug' => 'estados.prestamos.pagado', 'activo' => true]);
        CatalogoDato::create(['descripcion' => 'Aprobado', 'slug' => 'estados.prestamos.aprobado', 'activo' => true]);
        $this->estadoPagoPendiente = CatalogoDato::create(['descripcion' => 'Pendiente', 'slug' => 'estados.pagos.prestamos.pendiente', 'activo' => true]);
        CatalogoDato::create(['descripcion' => 'Pagado', 'slug' => 'estados.pagos.prestamos.pagado', 'activo' => true]);
        $this->formaEfectivo = CatalogoDato::create(['descripcion' => 'Efectivo', 'slug' => 'metodos.pagos.efectivo', 'activo' => true]);
        $this->metodoOtro = CatalogoDato::create(['descripcion' => 'Otro', 'slug' => 'metodos.pagos.otro', 'activo' => true]);
    }

    private function trabajador(): Proveedor
    {
        $categoria = CatalogoDato::create(['descripcion' => 'Categoria test', 'slug' => 'categoria-test-' . uniqid(), 'activo' => true]);

        return Proveedor::create([
            'categoria_proveedor_id' => $categoria->id,
            'documento' => '0999999999001',
            'razon_social' => 'Trabajador pagos estado test',
            'direccion' => 'Direccion test',
        ]);
    }

    private function prestamoConSaldo(float $saldo, int $cuotas = 4): Prestamo
    {
        $prestamo = Prestamo::create([
            'trabajador_id' => $this->trabajador()->id,
            'fecha_solicitud' => now()->format('Y-m-d'),
            'monto' => 1000,
            'saldo' => $saldo,
            'interes' => 0,
            'plazo' => $cuotas,
            'estado_id' => $this->estadoPrestamoPendiente->id,
            'motivo' => 'Prestamo estado test',
        ]);

        foreach (range(1, $cuotas) as $i) {
            PagoPrestamo::create([
                'prestamo_id' => $prestamo->id,
                'fecha_pago' => now()->addWeeks($i)->format('Y-m-d'),
                'monto_pagado' => 0,
                'monto_programado' => 250,
                'estado_id' => $this->estadoPagoPendiente->id,
                'metodo_pago_id' => $this->metodoOtro->id,
            ]);
        }

        return $prestamo;
    }

    private function ajaxHeaders(): array
    {
        return ['X-Requested-With' => 'XMLHttpRequest'];
    }

    private function pagar(PagoPrestamo $pago, string $monto)
    {
        return $this->actingAs($this->actor)->put(
            '/administrativo/prestamos/pago/' . $pago->id,
            ['monto_pagado' => $monto, 'forma_pago' => $this->formaEfectivo->id],
            $this->ajaxHeaders()
        );
    }

    public function test_pago_parcial_no_marca_pagado(): void
    {
        $prestamo = $this->prestamoConSaldo(1000);
        $pago = $prestamo->pago_prestamo()->orderBy('fecha_pago')->first();

        $response = $this->pagar($pago, '600');

        $response->assertOk();
        $response->assertJson(['success' => true]);

        $prestamo->refresh();
        $this->assertEquals(400, (float) $prestamo->saldo);
        $this->assertEquals($this->estadoPrestamoPendiente->id, $prestamo->estado_id);
        $this->assertSame('Pendiente', $prestamo->estado->descripcion);
    }

    public function test_ultimo_pago_marca_pagado(): void
    {
        $prestamo = $this->prestamoConSaldo(400, 1);
        $pago = $prestamo->pago_prestamo()->orderBy('fecha_pago')->first();

        $response = $this->pagar($pago, '400');

        $response->assertOk();
        $response->assertJson(['success' => true]);

        $prestamo->refresh();
        $this->assertEquals(0, (float) $prestamo->saldo);
        $this->assertEquals($this->estadoPrestamoPagado->id, $prestamo->estado_id);
        $this->assertSame('Pagado', $prestamo->estado->descripcion);
    }

    /**
     * Pin de regresion de la doble resta: con la condicion vieja
     * ($prestamo->saldo - $monto_pagado <= 0 sobre el saldo ya reducido)
     * el primer pago de 600 sobre saldo 1000 marcaba pagado con saldo 400.
     */
    public function test_flujo_parcial_y_final_deja_estado_coherente(): void
    {
        $prestamo = $this->prestamoConSaldo(1000);
        $pagos = $prestamo->pago_prestamo()->orderBy('fecha_pago')->get();

        $this->pagar($pagos[0], '600')->assertJson(['success' => true]);

        $prestamo->refresh();
        $this->assertEquals(400, (float) $prestamo->saldo);
        $this->assertSame('Pendiente', $prestamo->estado->descripcion);

        $this->pagar($pagos[1], '400')->assertJson(['success' => true]);

        $prestamo->refresh();
        $this->assertEquals(0, (float) $prestamo->saldo);
        $this->assertSame('Pagado', $prestamo->estado->descripcion);
    }
}
