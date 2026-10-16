<?php

namespace Tests\Feature;

use App\Http\Controllers\PrestamoController;
use App\Models\CatalogoDato;
use App\Models\PagoPrestamo;
use App\Models\Prestamo;
use App\Models\Proveedor;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Tests\TestCase;

/**
 * P4 (deriva de centavos) y P5 (suma sobre coleccion en memoria).
 *
 * Regla de reparto documentada: el residuo de redondeo se asigna a la
 * ULTIMA cuota, en los tres lugares que reparten saldo
 * (recalcularPagos, updatePrestamo/agregarPagosAdicionales y
 * updatePrestamo/agregarTodosPagos).
 */
class PrestamoRepartoSaldoTest extends TestCase
{
    use RefreshDatabase;

    private User $actor;

    private CatalogoDato $estadoPrestamoPendiente;

    private CatalogoDato $estadoPagoPendiente;

    private CatalogoDato $estadoPagoPagado;

    private CatalogoDato $metodoOtro;

    protected function setUp(): void
    {
        parent::setUp();

        $this->actor = User::factory()->create(['usuario' => 'reparto_saldo', 'correo' => 'reparto_saldo@test.com']);

        $this->estadoPrestamoPendiente = CatalogoDato::create(['descripcion' => 'Pendiente', 'slug' => 'estados.prestamos.pendiente', 'activo' => true]);
        CatalogoDato::create(['descripcion' => 'Pagado', 'slug' => 'estados.prestamos.pagado', 'activo' => true]);
        CatalogoDato::create(['descripcion' => 'Aprobado', 'slug' => 'estados.prestamos.aprobado', 'activo' => true]);
        $this->estadoPagoPendiente = CatalogoDato::create(['descripcion' => 'Pendiente', 'slug' => 'estados.pagos.prestamos.pendiente', 'activo' => true]);
        $this->estadoPagoPagado = CatalogoDato::create(['descripcion' => 'Pagado', 'slug' => 'estados.pagos.prestamos.pagado', 'activo' => true]);
        CatalogoDato::create(['descripcion' => 'Efectivo', 'slug' => 'metodos.pagos.efectivo', 'activo' => true]);
        $this->metodoOtro = CatalogoDato::create(['descripcion' => 'Otro', 'slug' => 'metodos.pagos.otro', 'activo' => true]);
    }

    private function trabajador(): Proveedor
    {
        $categoria = CatalogoDato::create(['descripcion' => 'Categoria test', 'slug' => 'categoria-test-' . uniqid(), 'activo' => true]);

        return Proveedor::create([
            'categoria_proveedor_id' => $categoria->id,
            'documento' => '0999999999001',
            'razon_social' => 'Trabajador reparto saldo test',
            'direccion' => 'Direccion test',
        ]);
    }

    private function prestamoConPagosPendientes(float $saldo, float $monto, int $cuotas): Prestamo
    {
        $prestamo = Prestamo::create([
            'trabajador_id' => $this->trabajador()->id,
            'fecha_solicitud' => now()->format('Y-m-d'),
            'monto' => $monto,
            'saldo' => $saldo,
            'interes' => 0,
            'plazo' => $cuotas,
            'estado_id' => $this->estadoPrestamoPendiente->id,
            'motivo' => 'Prestamo reparto test',
        ]);

        foreach (range(1, $cuotas) as $i) {
            PagoPrestamo::create([
                'prestamo_id' => $prestamo->id,
                'fecha_pago' => now()->addWeeks($i)->format('Y-m-d'),
                'monto_pagado' => 0,
                'monto_programado' => 1,
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

    private function montosProgramados(Prestamo $prestamo): array
    {
        return $prestamo->pago_prestamo()->orderBy('fecha_pago', 'asc')
            ->pluck('monto_programado')
            ->map(fn ($v) => round((float) $v, 2))
            ->all();
    }

    /**
     * P4 (a): 1000 en 3 cuotas no divide exacto; el plan debe sumar
     * exactamente el saldo, con el residuo en la ultima cuota
     * (333.33, 333.33, 333.34).
     */
    public function test_recalcular_pagos_reparte_saldo_exactamente_con_residuo_en_ultima_cuota(): void
    {
        $prestamo = $this->prestamoConPagosPendientes(1000, 1000, 3);

        $response = $this->actingAs($this->actor)->post(
            '/administrativo/prestamos/recalcular_pagos',
            ['prestamo' => $prestamo->id],
            $this->ajaxHeaders()
        );

        $response->assertOk();
        $response->assertJson(['success' => true]);

        $montos = $this->montosProgramados($prestamo);

        $this->assertEquals([333.33, 333.33, 333.34], $montos);
        $this->assertEquals(1000.0, round(array_sum($montos), 2));
    }

    /**
     * P4 (b): el recalculo deja el plan sumando el saldo aunque haya
     * cuotas ya pagadas (no las toca) y el residuo sea negativo
     * (1000 en 6 -> 5 x 166.67 + 166.65).
     */
    public function test_recalculo_deja_plan_sumando_saldo_sin_tocar_cuotas_pagadas(): void
    {
        $prestamo = $this->prestamoConPagosPendientes(1000, 1100, 7);

        $primera = $prestamo->pago_prestamo()->orderBy('fecha_pago', 'asc')->first();
        $primera->monto_pagado = 100;
        $primera->monto_programado = 250;
        $primera->estado_id = $this->estadoPagoPagado->id;
        $primera->save();

        $response = $this->actingAs($this->actor)->post(
            '/administrativo/prestamos/recalcular_pagos',
            ['prestamo' => $prestamo->id],
            $this->ajaxHeaders()
        );

        $response->assertOk();
        $response->assertJson(['success' => true]);

        $montos = $this->montosProgramados($prestamo);

        // La cuota pagada no se toca.
        $this->assertEquals(250.0, $montos[0]);

        $pendientes = array_slice($montos, 1);
        $this->assertEquals([166.67, 166.67, 166.67, 166.67, 166.67, 166.65], $pendientes);
        $this->assertEquals(1000.0, round(array_sum($pendientes), 2));
    }

    /**
     * PRESERVE: con division exacta el reparto no cambia (todas las cuotas
     * iguales, sin residuo); el nuevo codigo solo mueve el residuo cuando existe.
     */
    public function test_recalcular_pagos_con_division_exacta_deja_cuotas_iguales(): void
    {
        $prestamo = $this->prestamoConPagosPendientes(1000, 1000, 4);

        $response = $this->actingAs($this->actor)->post(
            '/administrativo/prestamos/recalcular_pagos',
            ['prestamo' => $prestamo->id],
            $this->ajaxHeaders()
        );

        $response->assertOk();
        $response->assertJson(['success' => true]);

        $montos = $this->montosProgramados($prestamo);

        $this->assertEquals([250.0, 250.0, 250.0, 250.0], $montos);
        $this->assertEquals(1000.0, round(array_sum($montos), 2));
    }

    /**
     * P5: updatePrestamo debe calcular el saldo con la suma de la base
     * ($prestamo->pago_prestamo()->sum(...)), no con la coleccion cargada
     * en memoria, que puede estar vieja despues de crear pagos en la
     * misma operacion.
     *
     * No es inyectable por HTTP (cada request arranca con modelo fresco),
     * asi que se ejercita por el modelo: se precarga la relacion (cache
     * vieja y vacia), se crea un pago directo en base y se invoca al
     * controlador con esa instancia.
     */
    public function test_update_prestamo_usa_suma_de_base_y_no_coleccion_vieja(): void
    {
        $this->actingAs($this->actor);

        $prestamo = $this->prestamoConPagosPendientes(1000, 1000, 4);

        // Deja la coleccion en memoria vieja y vacia...
        $prestamo->load('pago_prestamo');
        $prestamo->pago_prestamo()->delete();

        // ...y crea un pago directo en base (como haria la misma operacion).
        PagoPrestamo::create([
            'prestamo_id' => $prestamo->id,
            'fecha_pago' => now()->addWeek()->format('Y-m-d'),
            'monto_pagado' => 200,
            'monto_programado' => 250,
            'estado_id' => $this->estadoPagoPagado->id,
            'metodo_pago_id' => $this->metodoOtro->id,
        ]);

        $request = Request::create(
            '/administrativo/prestamos/actualizar/' . $prestamo->id,
            'PUT',
            [
                'monto' => '1200',
                'plazo' => 4,
                'estado' => $this->estadoPrestamoPendiente->id,
            ]
        );
        $request->headers->set('X-Requested-With', 'XMLHttpRequest');

        $response = (new PrestamoController)->updatePrestamo($request, $prestamo);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertTrue($response->getData(true)['success']);

        // 1200 - 200 (suma real en base), no 1200 - 0 (coleccion vieja).
        $this->assertEquals(1000.0, round((float) $prestamo->fresh()->saldo, 2));
    }
}
