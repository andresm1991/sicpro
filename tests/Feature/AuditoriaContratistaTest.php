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
 * Auditoria de contratistas (T6): el alta/edicion/baja de la orden de trabajo
 * y el alta/edicion/baja de sus pagos dejan una fila en `logs` con
 * entidad/accion/antes/despues y el usuario actor.
 */
class AuditoriaContratistaTest extends TestCase
{
    use RefreshDatabase;

    private Proyecto $proyecto;

    private CatalogoDato $etapa;

    private CatalogoDato $tipoEtapa;

    private CatalogoDato $unidadMedida;

    private Articulo $articulo;

    private Proveedor $proveedor;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\PermissionTableSeeder::class);
        $this->seed(\Database\Seeders\RoleTableSeeder::class);
        $this->crearContexto();
    }

    private function usuario(): User
    {
        $user = User::factory()->create(['usuario' => 'usuario_contratista_aud_test', 'correo' => 'contratista-aud-test@example.com']);
        $user->assignRole(Role::findByName('Administrador'));

        return $user;
    }

    private function crearContexto(): void
    {
        $this->etapa = CatalogoDato::create(['descripcion' => 'Etapa contratista aud', 'slug' => 'etapa-contratista-aud', 'activo' => true]);
        $this->tipoEtapa = CatalogoDato::create(['descripcion' => 'Tipo etapa contratista aud', 'slug' => 'tipo-etapa-contratista-aud', 'activo' => true]);
        $this->unidadMedida = CatalogoDato::create(['descripcion' => 'Unidad medida aud', 'slug' => 'unidad-medida-aud', 'activo' => true]);
        $catalogoProyecto = CatalogoDato::create(['descripcion' => 'Catalogo proyecto aud', 'slug' => 'catalogo-proyecto-aud', 'activo' => true]);
        $estadoProyecto = CatalogoDato::create(['descripcion' => 'Estado proyecto aud', 'slug' => 'estado-proyecto-aud', 'activo' => true]);
        $categoriaArticulo = CatalogoDato::create(['descripcion' => 'Categoria articulo aud', 'slug' => 'categoria-articulo-aud', 'activo' => true]);
        $categoriaProveedor = CatalogoDato::create(['descripcion' => 'Categoria proveedor aud', 'slug' => 'categoria-proveedor-aud', 'activo' => true]);

        // storeOrdenTrabajo usa estado_id = 38 fijo.
        $estadoContratista = new CatalogoDato(['descripcion' => 'Estado contratista aud', 'slug' => 'estado-contratista-aud', 'activo' => true]);
        $estadoContratista->id = 38;
        $estadoContratista->save();

        $user = User::factory()->create(['usuario' => 'usuario_contexto_aud_test', 'correo' => 'contexto-aud-test@example.com']);

        $this->proyecto = Proyecto::create([
            'catalogo_proyecto_id' => $catalogoProyecto->id,
            'nombre_proyecto' => 'Proyecto contratista aud',
            'nombre_propietario' => 'Propietario aud',
            'telefono' => '0999999999',
            'correo' => 'contratista-aud@example.com',
            'tipo_proyecto_id' => $catalogoProyecto->id,
            'portada' => 'portada.jpg',
            'estado_id' => $estadoProyecto->id,
        ]);

        $this->articulo = Articulo::create([
            'categoria_id' => $categoriaArticulo->id,
            'codigo' => 'ART-CONT-AUD',
            'descripcion' => 'Rubro contratista aud',
            'activo' => true,
        ]);

        $this->proveedor = Proveedor::create([
            'categoria_proveedor_id' => $categoriaProveedor->id,
            'documento' => '0999999999003',
            'razon_social' => 'Contratista Auditoria SA',
            'direccion' => 'Direccion aud',
        ]);

        $this->contextoUserId = $user->id;
    }

    private int $contextoUserId;

    private function crearOrden(float $cantidad = 10, float $valorUnitario = 100, int $casas = 1): Contratista
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
        ]);

        return $orden;
    }

    private function urlBase(): string
    {
        return "/proyectos/op/1/adquisiciones/{$this->proyecto->id}/tipo-adquisicion/{$this->etapa->id}/contratista/{$this->tipoEtapa->id}";
    }

    public function test_alta_de_orden_deja_auditoria_con_monto_en_despues(): void
    {
        $user = $this->usuario();

        $response = $this->actingAs($user)->post(
            $this->urlBase().'/guardar-orden-trabajo',
            [
                'proyecto_id' => $this->proyecto->id,
                'tipo_adquisicion' => $this->etapa->id,
                'tipo_etapa' => $this->tipoEtapa->id,
                'fecha' => '2026-10-16',
                'proveedor' => $this->proveedor->id,
                'categoria' => $this->articulo->id,
                'plazo_semanas' => 4,
                'numero_casas' => 2,
                'subproyecto' => 'Subproyecto aud',
                'productos' => [$this->articulo->id],
                'cantidad' => ['2'],
                'precio_unitario' => ['100.50'],
                'unidad_medida' => [$this->unidadMedida->id],
            ]
        );

        $response->assertRedirect();
        $response->assertSessionHas('success');

        // 2 x 100.50 x 2 casas = 402.00
        $orden = Contratista::latest('id')->first();
        $this->assertNotNull($orden);

        $log = Log::where('entidad', 'orden_trabajo_contratista')
            ->where('accion', 'crear')
            ->latest('id')
            ->first();

        $this->assertNotNull($log);
        $this->assertEquals($orden->id, $log->entidad_id);
        $this->assertEquals(402.00, (float) $log->valores_despues['monto']);
        $this->assertEquals($this->proveedor->id, $log->valores_despues['proveedor_id']);
        $this->assertSame('Contratista Auditoria SA', $log->valores_despues['contratista']);
        $this->assertStringContainsString('402.00', $log->message);
        $this->assertEquals($user->id, $log->user_id);
    }

    public function test_actualizar_orden_deja_antes_y_despues_del_monto(): void
    {
        $user = $this->usuario();
        $orden = $this->crearOrden(10, 100, 1); // monto 1000.00

        $response = $this->actingAs($user)->put(
            $this->urlBase()."/actualizar-orden-trabajo/{$orden->id}",
            [
                'contratista' => $orden->id,
                'tipo_etapa' => $this->tipoEtapa->id,
                'plazo_semanas' => 8,
                'numero_casas' => 1,
                'subproyecto' => 'Subproyecto aud edit',
                'productos' => [$this->articulo->id],
                'cantidad' => ['10'],
                'precio_unitario' => ['120'],
                'unidad_medida' => [$this->unidadMedida->id],
            ]
        );

        $response->assertRedirect();

        $log = Log::where('entidad', 'orden_trabajo_contratista')
            ->where('accion', 'actualizar')
            ->latest('id')
            ->first();

        $this->assertNotNull($log);
        $this->assertEquals($orden->id, $log->entidad_id);
        $this->assertEquals(1000.00, (float) $log->valores_antes['monto']);
        $this->assertEquals(1200.00, (float) $log->valores_despues['monto']);
        $this->assertEquals(4, $log->valores_antes['plazo_semanas']);
        $this->assertEquals(8, $log->valores_despues['plazo_semanas']);
        $this->assertEquals($user->id, $log->user_id);
    }

    public function test_alta_de_pago_deja_auditoria_con_valor_fecha_y_pagado(): void
    {
        $user = $this->usuario();
        $orden = $this->crearOrden(10, 100, 1); // total 1000.00

        $response = $this->actingAs($user)->post(
            $this->urlBase()."/guardar-pago-orden-trabajo/{$orden->id}",
            [
                'orden_trabajo' => $orden->id,
                'contratista' => $orden->id,
                'fecha' => '2026-10-16',
                'tipo' => 'AVANCE',
                'forma_pago' => 'EFECTIVO',
                'valor' => '400',
                'detalle' => 'Avance de prueba auditoria',
            ]
        );

        $response->assertRedirect();

        $pago = PagoOrdenTrabajoContratista::latest('id')->first();
        $this->assertNotNull($pago);

        $log = Log::where('entidad', 'pago_orden_trabajo_contratista')
            ->where('accion', 'crear')
            ->latest('id')
            ->first();

        $this->assertNotNull($log);
        $this->assertEquals($pago->id, $log->entidad_id);
        $this->assertEquals(400.00, (float) $log->valores_despues['valor']);
        $this->assertSame('2026-10-16', $log->valores_despues['fecha']);
        $this->assertArrayHasKey('pagado', $log->valores_despues);
        $this->assertFalse((bool) $log->valores_despues['pagado']);
        $this->assertStringContainsString('400.00', $log->message);
        $this->assertEquals($user->id, $log->user_id);
    }

    public function test_baja_de_pago_deja_valor_en_antes_y_la_fila_ya_no_existe(): void
    {
        $user = $this->usuario();
        $orden = $this->crearOrden();

        $pago = PagoOrdenTrabajoContratista::create([
            'fecha' => '2026-10-10',
            'contratista_id' => $orden->id,
            'tipo_pago' => 'AVANCE',
            'forma_pago' => 'TRANSFERENCIA',
            'valor' => 250.75,
            'detalle' => 'Pago a eliminar auditoria',
        ]);

        $response = $this->actingAs($user)->delete(
            $this->urlBase()."/eliminar-pago-orden-trabajo/{$pago->id}"
        );

        $response->assertOk()->assertJson(['success' => true]);

        // La fila del pago ya no existe: la auditoria es el unico rastro.
        $this->assertDatabaseMissing('pagos_orden_trabajo_contratista', ['id' => $pago->id]);

        $log = Log::where('entidad', 'pago_orden_trabajo_contratista')
            ->where('accion', 'eliminar')
            ->latest('id')
            ->first();

        $this->assertNotNull($log);
        $this->assertEquals($pago->id, $log->entidad_id);
        $this->assertEquals(250.75, (float) $log->valores_antes['valor']);
        $this->assertStringContainsString('250.75', $log->message);
        $this->assertEquals($user->id, $log->user_id);
    }

    public function test_baja_de_orden_deja_la_fila_completa_en_antes(): void
    {
        $user = $this->usuario();
        $orden = $this->crearOrden(10, 100, 1); // monto 1000.00

        $response = $this->actingAs($user)->delete("/orden-trabajo/eliminar/{$orden->id}");

        $response->assertOk()->assertJson(['success' => true]);

        $this->assertDatabaseMissing('contratistas', ['id' => $orden->id]);

        $log = Log::where('entidad', 'orden_trabajo_contratista')
            ->where('accion', 'eliminar')
            ->latest('id')
            ->first();

        $this->assertNotNull($log);
        $this->assertEquals($orden->id, $log->entidad_id);
        $this->assertEquals($orden->id, $log->valores_antes['id']);
        $this->assertEquals($this->proveedor->id, $log->valores_antes['proveedor_id']);
        $this->assertStringContainsString('1,000.00', $log->message);
        $this->assertEquals($user->id, $log->user_id);
    }

    public function test_usuario_autenticado_queda_como_actor(): void
    {
        $user = $this->usuario();
        $orden = $this->crearOrden();

        $pago = PagoOrdenTrabajoContratista::create([
            'fecha' => '2026-10-10',
            'contratista_id' => $orden->id,
            'tipo_pago' => 'AVANCE',
            'forma_pago' => 'EFECTIVO',
            'valor' => 100,
            'detalle' => 'Pago actor auditoria',
        ]);

        $this->actingAs($user)->delete(
            $this->urlBase()."/eliminar-pago-orden-trabajo/{$pago->id}"
        );

        $log = Log::where('entidad', 'pago_orden_trabajo_contratista')
            ->where('accion', 'eliminar')
            ->latest('id')
            ->first();

        $this->assertNotNull($log);
        $this->assertEquals($user->id, $log->user_id);
        $this->assertEquals($user->id, $log->context['user_id']);
    }
}
