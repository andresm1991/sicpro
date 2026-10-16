<?php

namespace Tests\Feature;

use App\Models\Articulo;
use App\Models\CatalogoDato;
use App\Models\Contratista;
use App\Models\DetalleContratista;
use App\Models\Log;
use App\Models\PagoOrdenTrabajoContratista;
use App\Models\Proveedor;
use App\Models\Proyecto;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * Auditoria administrativa de contratistas (T10 y T11):
 * - T10 `pagoOrdenTrabajo`: el flag `pagado` (el que decide si el dinero
 *   entra al balance) y el estado de la orden quedan en antes/despues.
 * - T11 `actualizarContratista`: las lineas mutadas quedan en antes/despues
 *   y el cambio de `Articulo::valor_unitario` deja su propio registro.
 */
class AuditoriaAdministrativoContratistaTest extends TestCase
{
    use RefreshDatabase;

    private Proyecto $proyecto;

    private CatalogoDato $etapa;

    private CatalogoDato $tipoEtapa;

    private CatalogoDato $unidadMedida;

    private Articulo $articulo;

    private Proveedor $proveedor;

    private int $contextoUserId;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\PermissionTableSeeder::class);
        $this->seed(\Database\Seeders\RoleTableSeeder::class);
        $this->crearContexto();
    }

    private function usuario(): User
    {
        $user = User::factory()->create(['usuario' => 'usuario_admin_contratista_aud_test', 'correo' => 'admin-contratista-aud-test@example.com']);
        $user->assignRole(Role::findByName('Administrador'));

        return $user;
    }

    private function crearContexto(): void
    {
        $etapa = CatalogoDato::create(['descripcion' => 'Etapa admin contratista aud', 'slug' => 'etapa-admin-contratista-aud', 'activo' => true]);
        $tipoEtapa = CatalogoDato::create(['descripcion' => 'Tipo etapa admin contratista aud', 'slug' => 'tipo-etapa-admin-contratista-aud', 'activo' => true]);
        $this->etapa = $etapa;
        $this->tipoEtapa = $tipoEtapa;
        $this->unidadMedida = CatalogoDato::create(['descripcion' => 'Unidad medida admin aud', 'slug' => 'unidad-medida-admin-aud', 'activo' => true]);
        $catalogoProyecto = CatalogoDato::create(['descripcion' => 'Catalogo proyecto admin aud', 'slug' => 'catalogo-proyecto-admin-aud', 'activo' => true]);
        $estadoProyecto = CatalogoDato::create(['descripcion' => 'Estado proyecto admin aud', 'slug' => 'estado-proyecto-admin-aud', 'activo' => true]);
        $categoriaArticulo = CatalogoDato::create(['descripcion' => 'Categoria articulo admin aud', 'slug' => 'categoria-articulo-admin-aud', 'activo' => true]);
        $categoriaProveedor = CatalogoDato::create(['descripcion' => 'Categoria proveedor admin aud', 'slug' => 'categoria-proveedor-admin-aud', 'activo' => true]);

        $estadoContratista = new CatalogoDato(['descripcion' => 'Estado contratista admin aud', 'slug' => 'estado-contratista-admin-aud', 'activo' => true]);
        $estadoContratista->id = 38;
        $estadoContratista->save();

        $user = User::factory()->create(['usuario' => 'usuario_contexto_admin_aud_test', 'correo' => 'contexto-admin-aud-test@example.com']);

        $this->proyecto = Proyecto::create([
            'catalogo_proyecto_id' => $catalogoProyecto->id,
            'nombre_proyecto' => 'Proyecto admin contratista aud',
            'nombre_propietario' => 'Propietario aud',
            'telefono' => '0999999999',
            'correo' => 'admin-contratista-aud@example.com',
            'tipo_proyecto_id' => $catalogoProyecto->id,
            'portada' => 'portada.jpg',
            'estado_id' => $estadoProyecto->id,
        ]);

        $this->articulo = Articulo::create([
            'categoria_id' => $categoriaArticulo->id,
            'codigo' => 'ART-ADMIN-CONT-AUD',
            'descripcion' => 'Rubro admin contratista aud',
            'valor_unitario' => 100,
            'activo' => true,
        ]);

        $this->proveedor = Proveedor::create([
            'categoria_proveedor_id' => $categoriaProveedor->id,
            'documento' => '0999999999004',
            'razon_social' => 'Contratista Admin Auditoria SA',
            'direccion' => 'Direccion aud',
        ]);

        $this->contextoUserId = $user->id;
    }

    private function crearOrden(float $cantidad = 10, float $valorUnitario = 100, int $casas = 1, $costoIndirecto = 5): Contratista
    {
        $orden = Contratista::create([
            'fecha' => '2026-10-01',
            'plazo_semanas' => 4,
            'proveedor_id' => $this->proveedor->id,
            'articulo_id' => $this->articulo->id,
            'proyecto_id' => $this->proyecto->id,
            'etapa_id' => $this->etapa->id,
            'tipo_etapa_id' => $this->tipoEtapa->id,
            'usuario_id' => $this->contextoUserId,
            'estado_id' => 38,
            'numero_casas' => $casas,
        ]);

        DetalleContratista::create([
            'contratista_id' => $orden->id,
            'articulo_id' => $this->articulo->id,
            'cantidad' => $cantidad,
            'unidad_medida_id' => $this->unidadMedida->id,
            'valor_unitario' => $valorUnitario,
            'costo_indirecto' => $costoIndirecto,
        ]);

        return $orden;
    }

    private function crearPago(Contratista $orden, float $valor = 400): PagoOrdenTrabajoContratista
    {
        return PagoOrdenTrabajoContratista::create([
            'fecha' => '2026-10-10',
            'contratista_id' => $orden->id,
            'tipo_pago' => 'AVANCE',
            'forma_pago' => 'EFECTIVO',
            'valor' => $valor,
            'detalle' => 'Pago admin auditoria',
        ]);
    }

    private function payloadActualizar(array $overrides = []): array
    {
        return array_merge([
            'nro_proforma' => 'PROF-AUD-1',
            'productos' => [$this->articulo->id],
            'cantidad' => ['12'],
            'precio_unitario' => ['150'],
            'costo_indirecto' => ['8'],
            'unidad_medida' => [$this->unidadMedida->id],
        ], $overrides);
    }

    public function test_pago_deja_pagado_false_a_true_y_estado_de_la_orden(): void
    {
        $user = $this->usuario();
        $orden = $this->crearOrden(); // total 10 x 100 x 1 = 1000.00
        $pago = $this->crearPago($orden, 400);
        $this->assertFalse((bool) $pago->pagado);

        $response = $this->actingAs($user)->put(
            "/pago_orden_trabajo/{$pago->id}",
            [],
            ['X-Requested-With' => 'XMLHttpRequest']
        );

        $response->assertOk()->assertJson(['success' => true]);
        $this->assertTrue((bool) $pago->fresh()->pagado);

        $log = Log::where('entidad', 'pago_orden_trabajo_contratista')
            ->where('accion', 'pagar')
            ->latest('id')
            ->first();

        $this->assertNotNull($log);
        $this->assertEquals($pago->id, $log->entidad_id);
        $this->assertFalse((bool) $log->valores_antes['pagado']);
        $this->assertTrue((bool) $log->valores_despues['pagado']);
        $this->assertEquals(38, $log->valores_antes['estado_orden_id']);
        $this->assertArrayHasKey('estado_orden_id', $log->valores_despues);
        $this->assertEquals(400.00, (float) $log->valores_despues['valor']);
        $this->assertStringContainsString('400.00', $log->message);
        $this->assertEquals($user->id, $log->user_id);
    }

    public function test_edicion_de_orden_deja_los_valores_cambiados(): void
    {
        $user = $this->usuario();
        $orden = $this->crearOrden(10, 100, 1, 5); // monto 1000.00

        $response = $this->actingAs($user)->put(
            "/administrativo/contratistas/{$orden->id}",
            $this->payloadActualizar()
        );

        $response->assertRedirect();

        $log = Log::where('entidad', 'orden_trabajo_contratista')
            ->where('accion', 'actualizar')
            ->latest('id')
            ->first();

        $this->assertNotNull($log);
        $this->assertEquals($orden->id, $log->entidad_id);
        $this->assertEquals(1000.00, (float) $log->valores_antes['monto']);
        $this->assertEquals(1800.00, (float) $log->valores_despues['monto']);

        $lineaAntes = $log->valores_antes['lineas'][$this->articulo->id];
        $lineaDespues = $log->valores_despues['lineas'][$this->articulo->id];

        $this->assertEquals(10, (float) $lineaAntes['cantidad']);
        $this->assertEquals(100.00, (float) $lineaAntes['valor_unitario']);
        $this->assertEquals(12, (float) $lineaDespues['cantidad']);
        $this->assertEquals(150.00, (float) $lineaDespues['valor_unitario']);
        $this->assertEquals(8, $lineaDespues['costo_indirecto']);
        $this->assertStringContainsString('1,800.00', $log->message);
        $this->assertStringContainsString('Contratista Admin Auditoria SA', $log->message);
        $this->assertEquals($user->id, $log->user_id);
    }

    public function test_cambio_de_valor_unitario_deja_registro_de_articulo(): void
    {
        $user = $this->usuario();
        $orden = $this->crearOrden(); // articulo en 100

        $this->actingAs($user)->put(
            "/administrativo/contratistas/{$orden->id}",
            $this->payloadActualizar(['precio_unitario' => ['150']])
        );

        $this->assertEquals(150.00, (float) $this->articulo->fresh()->valor_unitario);

        $log = Log::where('entidad', 'articulo')
            ->where('accion', 'actualizar')
            ->latest('id')
            ->first();

        $this->assertNotNull($log);
        $this->assertEquals($this->articulo->id, $log->entidad_id);
        $this->assertEquals(100.00, (float) $log->valores_antes['valor_unitario']);
        $this->assertEquals(150.00, (float) $log->valores_despues['valor_unitario']);
        $this->assertStringContainsString('150.00', $log->message);
        $this->assertEquals($user->id, $log->user_id);
    }

    public function test_sin_cambio_de_valor_unitario_no_hay_registro_de_articulo(): void
    {
        $user = $this->usuario();
        $orden = $this->crearOrden(); // linea y articulo en 100

        $this->actingAs($user)->put(
            "/administrativo/contratistas/{$orden->id}",
            $this->payloadActualizar([
                'cantidad' => ['10'],
                'precio_unitario' => ['100'],
                'costo_indirecto' => ['5'],
            ])
        );

        // La orden si se audita (paso por el metodo), el catalogo no cambia.
        $this->assertNotNull(
            Log::where('entidad', 'orden_trabajo_contratista')->where('accion', 'actualizar')->latest('id')->first()
        );
        $this->assertNull(
            Log::where('entidad', 'articulo')->where('accion', 'actualizar')->latest('id')->first()
        );
    }

    public function test_actor_autenticado_queda_en_los_registros(): void
    {
        $user = $this->usuario();
        $orden = $this->crearOrden();
        $pago = $this->crearPago($orden);

        $this->actingAs($user)->put(
            "/pago_orden_trabajo/{$pago->id}",
            [],
            ['X-Requested-With' => 'XMLHttpRequest']
        );

        $this->actingAs($user)->put(
            "/administrativo/contratistas/{$orden->id}",
            $this->payloadActualizar()
        );

        $logs = Log::whereIn('entidad', ['pago_orden_trabajo_contratista', 'orden_trabajo_contratista', 'articulo'])
            ->orderBy('id')
            ->get();

        $this->assertNotEmpty($logs);
        foreach ($logs as $log) {
            $this->assertEquals($user->id, $log->user_id);
            $this->assertEquals($user->id, $log->context['user_id']);
        }
    }
}
