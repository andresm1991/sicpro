<?php

namespace Tests\Feature;

use App\Models\Adquisicion;
use App\Models\AdquisicionDetalle;
use App\Models\Articulo;
use App\Models\CatalogoDato;
use App\Models\Log;
use App\Models\MovimientoCaja;
use App\Models\OrdenRecepcion;
use App\Models\Proveedor;
use App\Models\Proyecto;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * Auditoria administrativa de adquisiciones (T13):
 * - `actualizarAdquisicion` (operativa y administrativa): las lineas de
 *   detalle cambiadas quedan en antes/despues, el cambio de
 *   `Articulo::valor_unitario` deja su propio registro y la compra de
 *   contado deja su `movimiento_caja`.
 * - `createRecepcionAdministrativo`: deja su `orden_recepcion` con
 *   proveedor, completado y efecto sobre el stock.
 * - `destroyPedido`: deja la fila y sus lineas en valores_antes.
 * - `agergarProductoAdquisicion` solo devuelve `$request->all()`: no muta
 *   nada y no se audita.
 */
class AuditoriaAdministrativoAdquisicionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\PermissionTableSeeder::class);
        $this->seed(\Database\Seeders\RoleTableSeeder::class);
    }

    private function contexto(): array
    {
        $user = User::factory()->create(['usuario' => 'usuario_aud_admin_adq_test']);
        $user->assignRole(Role::findByName('Administrador'));

        $etapa = CatalogoDato::create(['descripcion' => 'Etapa aud admin adq', 'slug' => 'etapa-aud-admin-adq', 'activo' => true]);
        $tipoEtapa = CatalogoDato::create(['descripcion' => 'Tipo etapa aud admin adq', 'slug' => 'tipo-etapa-aud-admin-adq', 'activo' => true]);
        $catalogoProyecto = CatalogoDato::create(['descripcion' => 'Catalogo proyecto aud admin adq', 'slug' => 'catalogo-proyecto-aud-admin-adq', 'activo' => true]);
        $estadoProyecto = CatalogoDato::create(['descripcion' => 'Estado proyecto aud admin adq', 'slug' => 'estado-proyecto-aud-admin-adq', 'activo' => true]);
        $formaPago = CatalogoDato::create(['descripcion' => 'Credito aud admin adq', 'slug' => 'forma-pago-credito-aud-admin-adq', 'activo' => true]);
        $formaPagoContado = CatalogoDato::create(['descripcion' => 'Contado', 'slug' => 'forma.pago.contado', 'activo' => true]);
        $unidadMedida = CatalogoDato::create(['descripcion' => 'Unidad medida aud admin adq', 'slug' => 'unidad-medida-aud-admin-adq', 'activo' => true]);

        $proyecto = Proyecto::create([
            'catalogo_proyecto_id' => $catalogoProyecto->id,
            'nombre_proyecto' => 'Proyecto aud admin adq',
            'nombre_propietario' => 'Propietario aud admin adq',
            'telefono' => '0999999999',
            'correo' => 'aud-admin-adq@example.com',
            'tipo_proyecto_id' => $catalogoProyecto->id,
            'portada' => 'portada.jpg',
            'estado_id' => $estadoProyecto->id,
        ]);

        $articulo = Articulo::create([
            'categoria_id' => $etapa->id,
            'codigo' => 'ART-AUD-ADMIN-ADQ',
            'descripcion' => 'Articulo aud admin adq',
            'valor_unitario' => 10,
            'iva' => 12,
            'activo' => true,
        ]);

        $proveedor = Proveedor::create([
            'categoria_proveedor_id' => $tipoEtapa->id,
            'documento' => '1799999997',
            'razon_social' => 'Proveedor aud admin adq',
        ]);

        return compact('user', 'etapa', 'tipoEtapa', 'proyecto', 'articulo', 'proveedor', 'formaPago', 'formaPagoContado', 'unidadMedida');
    }

    private function crearPedido(array $c, string $numero, float $cantidad = 2, float $valor = 10, float $iva = 12): Adquisicion
    {
        $adquisicion = Adquisicion::create([
            'fecha' => '2026-01-15',
            'numero' => $numero,
            'proyecto_id' => $c['proyecto']->id,
            'etapa_id' => $c['etapa']->id,
            'tipo_etapa_id' => $c['tipoEtapa']->id,
            'usuario_id' => $c['user']->id,
            'estado' => 'En Proceso',
            'tipo_adquisicion' => 'operativo',
        ]);
        AdquisicionDetalle::create([
            'adquisicion_id' => $adquisicion->id,
            'articulo_id' => $c['articulo']->id,
            'cantidad_solicitada' => $cantidad,
            'unidad_medida_id' => $c['unidadMedida']->id,
            'valor' => $valor,
            'iva' => $iva,
            'necesidad' => 'necesidad auditoria admin',
        ]);

        return $adquisicion;
    }

    private function payloadOperativo(array $c, array $mas = []): array
    {
        return array_merge([
            'productos' => [$c['articulo']->id],
            'cantidad' => ['5'],
            'valor' => ['$20.00'],
            'iva_producto' => ['12'],
            'unidad_medida' => [$c['unidadMedida']->id],
            'indirecto' => ['0'],
            'kilometraje' => [null],
            'necesidad' => ['necesidad auditoria admin'],
        ], $mas);
    }

    public function test_actualizacion_operativa_deja_lineas_cambiadas_y_entidad_id(): void
    {
        $c = $this->contexto();
        $adquisicion = $this->crearPedido($c, 'ADQ-ADMIN-AUD-1');

        $this->actingAs($c['user'])->put(
            "/administrativo/adquisicion/operativo/{$adquisicion->id}",
            $this->payloadOperativo($c)
        )->assertRedirect();

        // El log historico sigue escribiendose (compatibilidad S5).
        $this->assertDatabaseHas('logs', ['message' => 'Actualizacion la informacion de la adquisicion #' . $adquisicion->id]);

        $log = Log::where('entidad', 'adquisicion')->where('accion', 'actualizar')->latest('id')->first();

        $this->assertNotNull($log);
        $this->assertEquals($adquisicion->id, $log->entidad_id);

        $lineaAntes = $log->valores_antes['lineas'][$c['articulo']->id];
        $lineaDespues = $log->valores_despues['lineas'][$c['articulo']->id];

        $this->assertEquals(2, (float) $lineaAntes['cantidad_solicitada']);
        $this->assertEquals(10.0, (float) $lineaAntes['valor']);
        $this->assertEquals(5, (float) $lineaDespues['cantidad_solicitada']);
        $this->assertEquals(20.0, (float) $lineaDespues['valor']);
        $this->assertStringContainsString('ADQ-ADMIN-AUD-1', $log->message);
        $this->assertEquals($c['user']->id, $log->user_id);
    }

    public function test_actualizacion_administrativa_cambia_valor_de_articulo_con_su_registro(): void
    {
        $c = $this->contexto();
        $adquisicion = $this->crearPedido($c, 'ADQ-ADMIN-AUD-2', 2, 10, 0);
        $this->assertEquals(10.0, (float) $c['articulo']->fresh()->valor_unitario);

        $this->actingAs($c['user'])->put(
            "/administrativo/adquisicion/administrativo/{$adquisicion->id}",
            [
                'proyecto' => $c['proyecto']->id,
                'etapa' => $c['etapa']->id,
                'actividad' => $c['tipoEtapa']->id,
                'productos' => [$c['articulo']->id],
                'cantidad' => ['3'],
                'necesidad' => ['necesidad auditoria admin'],
                'unidad_medida' => [$c['unidadMedida']->id],
                'iva_producto' => ['0'],
                'valor' => ['25'],
                'proveedor' => $c['proveedor']->id,
                'forma_pago' => $c['formaPago']->id,
            ]
        )->assertRedirect();

        $this->assertEquals(25.0, (float) $c['articulo']->fresh()->valor_unitario);

        $logAdquisicion = Log::where('entidad', 'adquisicion')->where('accion', 'actualizar')->latest('id')->first();
        $this->assertNotNull($logAdquisicion);
        $this->assertEquals($adquisicion->id, $logAdquisicion->entidad_id);
        $this->assertArrayHasKey($c['articulo']->id, $logAdquisicion->valores_despues['lineas']);

        $log = Log::where('entidad', 'articulo')->where('accion', 'actualizar')->latest('id')->first();

        $this->assertNotNull($log);
        $this->assertEquals($c['articulo']->id, $log->entidad_id);
        $this->assertEquals(10.0, (float) $log->valores_antes['valor_unitario']);
        $this->assertEquals(25.0, (float) $log->valores_despues['valor_unitario']);
        $this->assertStringContainsString('25.00', $log->message);
        $this->assertEquals($c['user']->id, $log->user_id);
    }

    public function test_compra_de_contado_deja_registro_de_movimiento_caja(): void
    {
        $c = $this->contexto();
        $adquisicion = $this->crearPedido($c, 'ADQ-ADMIN-AUD-3', 2, 10, 12);
        OrdenRecepcion::create([
            'fecha' => '2026-01-16',
            'adquisicion_id' => $adquisicion->id,
            'proveedor_id' => $c['proveedor']->id,
            'forma_pago_id' => $c['formaPagoContado']->id,
            'completado' => false,
            'editar' => true,
        ]);

        $this->actingAs($c['user'])->put(
            "/administrativo/adquisicion/operativo/{$adquisicion->id}",
            $this->payloadOperativo($c, [
                'cantidad' => ['2'],
                'valor' => ['10'],
                'orden_completa' => '1',
            ])
        )->assertRedirect();

        $this->assertSame('Completado', $adquisicion->fresh()->estado);

        // 2 x 10 + 12% IVA = 22.40
        $movimiento = MovimientoCaja::where('origen_id', $adquisicion->id)->first();
        $this->assertNotNull($movimiento);
        $this->assertEquals(22.40, (float) $movimiento->monto);

        $log = Log::where('entidad', 'movimiento_caja')->where('accion', 'crear')->latest('id')->first();

        $this->assertNotNull($log);
        $this->assertEquals($movimiento->id, $log->entidad_id);
        $this->assertEquals(22.40, (float) $log->valores_despues['monto']);
        $this->assertSame('egreso', $log->valores_despues['tipo']);
        $this->assertStringContainsString('ADQ-ADMIN-AUD-3', $log->message);
        $this->assertStringContainsString('22.40', $log->message);
        $this->assertEquals($c['user']->id, $log->user_id);
    }

    public function test_recepcion_administrativa_deja_registro_con_proveedor_y_stock(): void
    {
        $c = $this->contexto();
        $adquisicion = $this->crearPedido($c, 'ADQ-ADMIN-AUD-4');

        $this->actingAs($c['user'])->put(
            "/administrativo/recepcion/administrativo/{$adquisicion->id}",
            [
                'proveedor' => $c['proveedor']->id,
                'cantidad_recibida' => ['4'],
                'unidad_medida' => [$c['unidadMedida']->id],
                'valor' => ['15'],
                'forma_pago' => $c['formaPago']->id,
                'inventario' => ['0'],
                'numero_factura' => 'FAC-001',
                'nro_proforma' => 'PROF-001',
            ]
        )->assertRedirect();

        // El log historico sigue escribiendose (compatibilidad S5).
        $this->assertDatabaseHas('logs', ['message' => 'Orden recepción creada']);

        $log = Log::where('entidad', 'orden_recepcion')->where('accion', 'crear')->latest('id')->first();

        $this->assertNotNull($log);
        $this->assertEquals($c['proveedor']->id, $log->valores_despues['proveedor']);
        $this->assertEquals($c['formaPago']->id, $log->valores_despues['forma_pago']);
        $this->assertFalse((bool) $log->valores_despues['completado']);

        $linea = $log->valores_despues['lineas'][$c['articulo']->id];
        $this->assertEquals(4, (float) $linea['cantidad_recibida']);
        $this->assertEquals(15.0, (float) $linea['valor']);

        $this->assertStringContainsString('ADQ-ADMIN-AUD-4', $log->message);
        $this->assertStringContainsString('Proveedor aud admin adq', $log->message);
        $this->assertEquals($c['user']->id, $log->user_id);
    }

    public function test_baja_deja_fila_y_lineas_en_antes_y_elimina_la_adquisicion(): void
    {
        $c = $this->contexto();
        $adquisicion = $this->crearPedido($c, 'ADQ-ADMIN-AUD-5');

        $this->actingAs($c['user'])->delete(
            "/administrativo/adquisiciones/eliminar/{$adquisicion->id}",
            [],
            ['X-Requested-With' => 'XMLHttpRequest']
        )->assertOk()->assertJson(['success' => true]);

        $this->assertDatabaseMissing('adquisiciones', ['id' => $adquisicion->id]);

        $log = Log::where('entidad', 'adquisicion')->where('accion', 'eliminar')->latest('id')->first();

        $this->assertNotNull($log);
        $this->assertEquals($adquisicion->id, $log->entidad_id);
        $this->assertSame('ADQ-ADMIN-AUD-5', $log->valores_antes['numero']);
        $this->assertArrayHasKey('detalle', $log->valores_antes);
        $this->assertCount(1, $log->valores_antes['detalle']);
        $this->assertEquals($c['articulo']->id, $log->valores_antes['detalle'][0]['articulo_id']);
        $this->assertStringContainsString('ADQ-ADMIN-AUD-5', $log->message);
        $this->assertEquals($c['user']->id, $log->user_id);
    }

    public function test_actor_autenticado_queda_en_los_registros(): void
    {
        $c = $this->contexto();
        $adquisicion = $this->crearPedido($c, 'ADQ-ADMIN-AUD-6');

        $this->actingAs($c['user'])->put(
            "/administrativo/adquisicion/operativo/{$adquisicion->id}",
            $this->payloadOperativo($c)
        )->assertRedirect();

        $this->actingAs($c['user'])->put(
            "/administrativo/recepcion/administrativo/{$adquisicion->id}",
            [
                'proveedor' => $c['proveedor']->id,
                'cantidad_recibida' => ['5'],
                'unidad_medida' => [$c['unidadMedida']->id],
                'valor' => ['20'],
                'forma_pago' => $c['formaPago']->id,
                'inventario' => ['0'],
            ]
        )->assertRedirect();

        $logs = Log::whereIn('entidad', ['adquisicion', 'articulo', 'orden_recepcion'])
            ->orderBy('id')
            ->get();

        $this->assertNotEmpty($logs);
        foreach ($logs as $log) {
            $this->assertEquals($c['user']->id, $log->user_id);
            $this->assertEquals($c['user']->id, $log->context['user_id']);
        }
    }

    public function test_agregar_producto_no_muta_ni_audita(): void
    {
        // La ruta PUT `adquisicion/agregar-producto/{adquisicion}` queda opacada
        // por la ruta generica `adquisicion/{tipo}/{adquisicion}` (definida antes
        // en routes/modules/administrativo.php), asi que se ejercita el metodo
        // directo: solo devuelve `$request->all()` sin tocar la base.
        $c = $this->contexto();
        $adquisicion = $this->crearPedido($c, 'ADQ-ADMIN-AUD-7');
        $logsAntes = Log::count();

        $request = \Illuminate\Http\Request::create(
            "/administrativo/adquisicion/agregar-producto/{$adquisicion->id}",
            'PUT',
            ['producto' => 'nuevo', 'cantidad' => '1'],
            [],
            [],
            ['HTTP_X-Requested-With' => 'XMLHttpRequest']
        );
        $request->setLaravelSession(app('session.store'));
        $this->actingAs($c['user']);

        $resultado = (new \App\Http\Controllers\AdministrativoController)->agergarProductoAdquisicion($request, $adquisicion);

        $this->assertSame(['producto' => 'nuevo', 'cantidad' => '1'], $resultado);
        $this->assertSame($logsAntes, Log::count());
        $this->assertCount(1, $adquisicion->fresh()->adquisiciones_detalle);
    }
}
