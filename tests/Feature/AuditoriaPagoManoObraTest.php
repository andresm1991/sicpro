<?php

namespace Tests\Feature;

use App\Models\Articulo;
use App\Models\CatalogoDato;
use App\Models\DetalleManoObra;
use App\Models\Log;
use App\Models\ManoObra;
use App\Models\PagoManoObra;
use App\Models\PagoPrestamo;
use App\Models\Prestamo;
use App\Models\Proveedor;
use App\Models\Proyecto;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Auditoria del pago de nomina (T4): `registrarPagoManoObra` deja una fila en
 * `logs` (accion `pagar`, entidad `pago_mano_obra`) con el monto de la nomina
 * y el periodo/semana pagados. El log de error previo se mantiene intacto y
 * el comportamiento del endpoint no cambia.
 */
class AuditoriaPagoManoObraTest extends TestCase
{
    use RefreshDatabase;

    private ManoObra $manoObra;

    private PagoPrestamo $pagoPrestamo;

    protected function setUp(): void
    {
        parent::setUp();

        $etapa = CatalogoDato::create(['descripcion' => 'Etapa pago nomina', 'slug' => 'etapa-pago-nomina', 'activo' => true]);
        $tipoEtapa = CatalogoDato::create(['descripcion' => 'Tipo etapa pago nomina', 'slug' => 'tipo-etapa-pago-nomina', 'activo' => true]);
        $catalogoProyecto = CatalogoDato::create(['descripcion' => 'Catalogo proyecto pago nomina', 'slug' => 'catalogo-proyecto-pago-nomina', 'activo' => true]);
        $estadoProyecto = CatalogoDato::create(['descripcion' => 'Estado proyecto pago nomina', 'slug' => 'estado-proyecto-pago-nomina', 'activo' => true]);

        $proyecto = Proyecto::create([
            'catalogo_proyecto_id' => $catalogoProyecto->id,
            'nombre_proyecto' => 'Proyecto pago nomina',
            'nombre_propietario' => 'Propietario pago nomina',
            'telefono' => '0999999999',
            'correo' => 'pago-nomina@example.com',
            'tipo_proyecto_id' => $catalogoProyecto->id,
            'portada' => 'portada.jpg',
            'estado_id' => $estadoProyecto->id,
        ]);

        $usuario = User::factory()->create(['usuario' => 'auditor_pago_nomina', 'correo' => 'auditor_pago_nomina@test.com']);

        $this->manoObra = ManoObra::create([
            'semana' => 3,
            'fecha_inicio' => '2026-08-10',
            'fecha_fin' => '2026-08-15',
            'proyecto_id' => $proyecto->id,
            'etapa_id' => $etapa->id,
            'tipo_etapa_id' => $tipoEtapa->id,
            'usuario_id' => $usuario->id,
        ]);

        $categoriaArticulo = CatalogoDato::create(['descripcion' => 'Categoria articulo pago nomina', 'slug' => 'categoria-articulo-pago-nomina', 'activo' => true]);
        $articulo = Articulo::create([
            'categoria_id' => $categoriaArticulo->id,
            'codigo' => 'ART-PAGO-NOM',
            'descripcion' => 'Oficial',
            'activo' => true,
        ]);

        $categoriaProveedor = CatalogoDato::create(['descripcion' => 'Categoria proveedor pago nomina', 'slug' => 'categoria-proveedor-pago-nomina', 'activo' => true]);
        $trabajador = Proveedor::create([
            'categoria_proveedor_id' => $categoriaProveedor->id,
            'documento' => '0999999999001',
            'razon_social' => 'Trabajador pago nomina',
            'direccion' => 'Direccion pago nomina',
        ]);

        // Nomina: (100 + 10 - 5) + (50 + 0 - 0) = 155.
        DetalleManoObra::create([
            'fecha' => '2026-08-10',
            'mano_obra_id' => $this->manoObra->id,
            'proveedor_id' => $trabajador->id,
            'articulo_id' => $articulo->id,
            'jornada' => 'COMPLETA',
            'valor' => 100,
            'adicional' => 10,
            'descuento' => 5,
        ]);
        DetalleManoObra::create([
            'fecha' => '2026-08-11',
            'mano_obra_id' => $this->manoObra->id,
            'proveedor_id' => $trabajador->id,
            'articulo_id' => $articulo->id,
            'jornada' => 'COMPLETA',
            'valor' => 50,
        ]);

        $estadoPrestamo = CatalogoDato::create(['descripcion' => 'Pagado', 'slug' => 'estados.prestamos.pagado', 'activo' => true]);
        $estadoPago = CatalogoDato::create(['descripcion' => 'Pagado', 'slug' => 'estados.pagos.prestamos.pagado', 'activo' => true]);
        $metodoPago = CatalogoDato::create(['descripcion' => 'Efectivo', 'slug' => 'metodos.pagos.efectivo', 'activo' => true]);

        $prestamo = Prestamo::create([
            'trabajador_id' => $trabajador->id,
            'fecha_solicitud' => '2026-08-01',
            'monto' => 200,
            'saldo' => 200,
            'interes' => 0,
            'plazo' => 4,
            'estado_id' => $estadoPrestamo->id,
            'motivo' => 'Prestamo pago nomina test',
        ]);

        $this->pagoPrestamo = PagoPrestamo::create([
            'prestamo_id' => $prestamo->id,
            'fecha_pago' => '2026-08-10',
            'monto_pagado' => 50,
            'monto_programado' => 50,
            'estado_id' => $estadoPago->id,
            'metodo_pago_id' => $metodoPago->id,
        ]);
    }

    private function actor(): User
    {
        return User::where('usuario', 'auditor_pago_nomina')->firstOrFail();
    }

    public function test_pago_nomina_deja_auditoria_con_monto_y_periodo(): void
    {
        $actor = $this->actor();

        $response = $this->actingAs($actor)->post(
            '/administrativo/mano-de-obra/registrar-pago',
            [
                'mano_obra' => $this->manoObra->id,
                'nroProforma' => 'PF-AUD-1',
                'estado' => 'pendiente',
                'pago_ids' => json_encode([$this->pagoPrestamo->id]),
            ]
        );

        $response->assertStatus(302);

        $this->assertDatabaseHas('pagos_mano_obra', [
            'mano_obra_id' => $this->manoObra->id,
            'pago_prestamo_id' => $this->pagoPrestamo->id,
        ]);

        $log = Log::where('entidad', 'pago_mano_obra')
            ->where('accion', 'pagar')
            ->latest('id')
            ->first();

        $this->assertNotNull($log);
        $this->assertEquals($this->manoObra->id, $log->entidad_id);
        $this->assertEquals($actor->id, $log->user_id);
        $this->assertEquals(155.0, (float) $log->valores_despues['monto']);
        $this->assertSame(3, (int) $log->valores_despues['semana']);
        $this->assertSame('2026-08-10 al 2026-08-15', $log->valores_despues['periodo']);
        $this->assertSame('PF-AUD-1', $log->valores_despues['nro_proforma']);
        $this->assertStringContainsString('semana 3', $log->message);
        $this->assertStringContainsString('155.00', $log->message);
    }

    public function test_pago_fallido_no_deja_auditoria(): void
    {
        $response = $this->actingAs($this->actor())->post(
            '/administrativo/mano-de-obra/registrar-pago',
            [
                'mano_obra' => 99999,
                'nroProforma' => 'PF-AUD-2',
                'estado' => 'pendiente',
                'pago_ids' => json_encode([$this->pagoPrestamo->id]),
            ]
        );

        $response->assertStatus(302);

        $this->assertSame(0, Log::where('entidad', 'pago_mano_obra')->where('accion', 'pagar')->count());
        $this->assertDatabaseMissing('pagos_mano_obra', ['mano_obra_id' => 99999]);
    }
}
