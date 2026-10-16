<?php

namespace Tests\Feature;

use App\Models\Adquisicion;
use App\Models\Articulo;
use App\Models\CatalogoDato;
use App\Models\Log;
use App\Models\OrdenRecepcion;
use App\Models\Proveedor;
use App\Models\Proyecto;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * Auditoria de adquisiciones (T7): el alta, la edicion y la baja del pedido,
 * la orden de recepcion, el alta administrativa y el articulo creado al vuelo
 * dejan una fila en `logs` con entidad/accion/entidad_id y antes/despues.
 */
class AuditoriaAdquisicionTest extends TestCase
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
        $user = User::factory()->create(['usuario' => 'usuario_aud_adq_test']);
        $user->assignRole(Role::findByName('Administrador'));

        $etapa = CatalogoDato::create(['descripcion' => 'Etapa aud adq', 'slug' => 'etapa-aud-adq', 'activo' => true]);
        $tipoEtapa = CatalogoDato::create(['descripcion' => 'Tipo etapa aud adq', 'slug' => 'tipo-etapa-aud-adq', 'activo' => true]);
        $catalogoProyecto = CatalogoDato::create(['descripcion' => 'Catalogo proyecto aud adq', 'slug' => 'catalogo-proyecto-aud-adq', 'activo' => true]);
        $estadoProyecto = CatalogoDato::create(['descripcion' => 'Estado proyecto aud adq', 'slug' => 'estado-proyecto-aud-adq', 'activo' => true]);
        $formaPago = CatalogoDato::create(['descripcion' => 'Forma pago aud adq', 'slug' => 'forma-pago-aud-adq', 'activo' => true]);
        $unidadMedida = CatalogoDato::create(['descripcion' => 'Unidad medida aud adq', 'slug' => 'unidad-medida-aud-adq', 'activo' => true]);
        // Categoria que usa registrarNuevoProducto() cuando el producto no es numerico.
        CatalogoDato::create(['descripcion' => 'Servicios', 'slug' => 'tipo.adquisiciones.servicios', 'activo' => true]);
        // Estado que lee storeOrdenRecepcion()/updateOrdenRecepcion() para el inventario.
        CatalogoDato::create(['descripcion' => 'Nuevo', 'slug' => 'estados.inventario.nuevo', 'activo' => true]);

        $proyecto = Proyecto::create([
            'catalogo_proyecto_id' => $catalogoProyecto->id,
            'nombre_proyecto' => 'Proyecto aud adq',
            'nombre_propietario' => 'Propietario aud adq',
            'telefono' => '0999999999',
            'correo' => 'aud-adq@example.com',
            'tipo_proyecto_id' => $catalogoProyecto->id,
            'portada' => 'portada.jpg',
            'estado_id' => $estadoProyecto->id,
        ]);

        $articulo = Articulo::create([
            'categoria_id' => $etapa->id,
            'codigo' => 'ART-AUD-ADQ',
            'descripcion' => 'Articulo aud adq',
            'valor_unitario' => 10,
            'iva' => 0,
            'activo' => true,
        ]);

        $proveedor = Proveedor::create([
            'categoria_proveedor_id' => $tipoEtapa->id,
            'documento' => '1799999999',
            'razon_social' => 'Proveedor aud adq',
        ]);

        $proveedor2 = Proveedor::create([
            'categoria_proveedor_id' => $tipoEtapa->id,
            'documento' => '1799999998',
            'razon_social' => 'Proveedor aud adq dos',
        ]);

        return compact('user', 'etapa', 'tipoEtapa', 'proyecto', 'articulo', 'proveedor', 'proveedor2', 'formaPago', 'unidadMedida');
    }

    private function urlStore(array $c): string
    {
        return '/proyectos/obra/1/adquisiciones/' . $c['proyecto']->id . '/tipo-adquisicion/'
            . $c['etapa']->id . '/' . $c['tipoEtapa']->id . '/guardar';
    }

    private function urlUpdate(array $c, int $pedidoId): string
    {
        return '/proyectos/obra/1/adquisiciones/' . $c['proyecto']->id . '/tipo-adquisicion/'
            . $c['etapa']->id . '/' . $c['tipoEtapa']->id . '/actualizar/' . $pedidoId;
    }

    private function urlDestroy(array $c, int $pedidoId): string
    {
        return '/proyectos/obra/1/adquisiciones/' . $c['proyecto']->id . '/tipo-adquisicion/'
            . $c['etapa']->id . '/' . $c['tipoEtapa']->id . '/eliminar-adquisicion/' . $pedidoId;
    }

    private function payloadAlta(array $c, array $mas = []): array
    {
        return array_merge([
            'fecha' => '2026-01-15',
            'proyecto_id' => $c['proyecto']->id,
            'tipo_adquisicion' => $c['etapa']->id,
            'tipo_etapa' => $c['tipoEtapa']->id,
            'productos' => [$c['articulo']->id],
            'cantidad' => ['2'],
            'necesidad' => ['necesidad auditoria alta'],
            'km' => [null],
            'precio' => ['10'],
            'forma_pago' => $c['formaPago']->id,
            'proveedor' => $c['proveedor']->id,
        ], $mas);
    }

    public function test_alta_deja_auditoria_con_entidad_id_y_total(): void
    {
        $c = $this->contexto();

        $this->actingAs($c['user'])->post($this->urlStore($c), $this->payloadAlta($c))
            ->assertRedirect();

        $adquisicion = Adquisicion::latest('id')->first();
        $this->assertNotNull($adquisicion);

        // El log historico sigue escribiendose (compatibilidad S5).
        $this->assertDatabaseHas('logs', ['message' => 'Adquisición creada']);

        $log = Log::where('entidad', 'adquisicion')->where('accion', 'crear')->latest('id')->first();

        $this->assertNotNull($log);
        $this->assertEquals($adquisicion->id, $log->entidad_id);
        $this->assertEquals(20.0, (float) $log->valores_despues['total']);
        $this->assertEquals($adquisicion->numero, $log->valores_despues['numero']);
        $this->assertStringContainsString($adquisicion->numero, $log->message);
    }

    public function test_edicion_deja_antes_y_despues_distintos(): void
    {
        $c = $this->contexto();

        $this->actingAs($c['user'])->post($this->urlStore($c), $this->payloadAlta($c))
            ->assertRedirect();

        $adquisicion = Adquisicion::latest('id')->first();
        $this->assertSame('En Proceso', $adquisicion->estado);

        $this->actingAs($c['user'])->put($this->urlUpdate($c, $adquisicion->id), [
            'tipo_etapa' => $c['tipoEtapa']->id,
            'productos' => [$c['articulo']->id],
            'cantidad' => ['2'],
            'necesidad' => ['necesidad auditoria edicion'],
            'km' => [null],
            'unidad_medida' => [$c['unidadMedida']->id],
            'precio' => ['10'],
            'proveedor' => $c['proveedor']->id,
            'forma_pago' => $c['formaPago']->id,
            'subproyecto' => 'SUB-T7',
            'orden_completa' => '1',
        ])->assertRedirect();

        $log = Log::where('entidad', 'adquisicion')->where('accion', 'actualizar')->latest('id')->first();

        $this->assertNotNull($log);
        $this->assertEquals($adquisicion->id, $log->entidad_id);
        $this->assertNotEquals($log->valores_antes, $log->valores_despues);
        $this->assertSame('En Proceso', $log->valores_antes['estado']);
        $this->assertSame('Finalizado', $log->valores_despues['estado']);
        $this->assertSame('SUB-T7', $log->valores_despues['subproyecto']);
        $this->assertStringContainsString($adquisicion->numero, $log->message);
    }

    public function test_baja_deja_la_fila_en_valores_antes_y_elimina_la_adquisicion(): void
    {
        Storage::fake('digitalocean');
        $c = $this->contexto();

        $this->actingAs($c['user'])->post($this->urlStore($c), $this->payloadAlta($c))
            ->assertRedirect();

        $adquisicion = Adquisicion::latest('id')->first();
        $numero = $adquisicion->numero;

        $this->actingAs($c['user'])->delete($this->urlDestroy($c, $adquisicion->id))
            ->assertOk()
            ->assertJson(['success' => true]);

        $this->assertDatabaseMissing('adquisiciones', ['id' => $adquisicion->id]);

        $log = Log::where('entidad', 'adquisicion')->where('accion', 'eliminar')->latest('id')->first();

        $this->assertNotNull($log);
        $this->assertEquals($adquisicion->id, $log->entidad_id);
        $this->assertSame($numero, $log->valores_antes['numero']);
        $this->assertArrayHasKey('detalle', $log->valores_antes);
        $this->assertCount(1, $log->valores_antes['detalle']);
        $this->assertStringContainsString($numero, $log->message);
    }

    public function test_actor_autenticado_queda_registrado(): void
    {
        $c = $this->contexto();

        $this->actingAs($c['user'])->post($this->urlStore($c), $this->payloadAlta($c))
            ->assertRedirect();

        $log = Log::where('entidad', 'adquisicion')->where('accion', 'crear')->latest('id')->first();

        $this->assertNotNull($log);
        $this->assertEquals($c['user']->id, $log->user_id);
        $this->assertEquals($c['user']->id, $log->context['user_id']);
    }

    public function test_orden_recepcion_deja_auditoria_con_proveedor_y_forma_de_pago(): void
    {
        $c = $this->contexto();

        $adquisicion = Adquisicion::create([
            'fecha' => '2026-01-15',
            'numero' => 'ADQ-AUD-ORDEN',
            'proyecto_id' => $c['proyecto']->id,
            'etapa_id' => $c['etapa']->id,
            'tipo_etapa_id' => $c['tipoEtapa']->id,
            'usuario_id' => $c['user']->id,
            'estado' => 'En Proceso',
            'tipo_adquisicion' => 'operativo',
        ]);
        \App\Models\AdquisicionDetalle::create([
            'adquisicion_id' => $adquisicion->id,
            'articulo_id' => $c['articulo']->id,
            'cantidad_solicitada' => 2,
            'necesidad' => 'necesidad orden recepcion',
            'valor' => 10,
        ]);

        $url = '/proyectos/obra/1/adquisiciones/' . $c['proyecto']->id . '/tipo-adquisicion/'
            . $c['etapa']->id . '/' . $c['tipoEtapa']->id . '/orden-recepcion/' . $adquisicion->id . '/guardar';

        $this->actingAs($c['user'])->post($url, [
            'pedido' => $adquisicion->id,
            'proveedor' => $c['proveedor']->id,
            'forma_pago' => $c['formaPago']->id,
            'tipo_etapa' => $c['tipoEtapa']->id,
            'cantidad_recibida' => ['2'],
            'inventario' => ['0'],
        ])->assertRedirect();

        $orden = OrdenRecepcion::where('adquisicion_id', $adquisicion->id)->first();
        $this->assertNotNull($orden);

        $log = Log::where('entidad', 'orden_recepcion')->where('accion', 'crear')->latest('id')->first();

        $this->assertNotNull($log);
        $this->assertEquals($orden->id, $log->entidad_id);
        $this->assertEquals($c['proveedor']->id, $log->valores_despues['proveedor']);
        $this->assertEquals($c['formaPago']->id, $log->valores_despues['forma_pago']);
        $this->assertArrayHasKey('completado', $log->valores_despues);
        $this->assertStringContainsString($adquisicion->numero, $log->message);
    }

    public function test_alta_administrativa_deja_auditoria_con_total(): void
    {
        $c = $this->contexto();

        $this->actingAs($c['user'])->post('/administrativo/adquisiciones/materiales/guardar', [
            'proyecto' => $c['proyecto']->id,
            'etapa' => $c['etapa']->id,
            'actividad' => $c['tipoEtapa']->id,
            'productos' => [$c['articulo']->id],
            'cantidad' => ['2'],
            'valor_unitario' => ['10'],
            'iva_producto' => ['0'],
            'unidad_medida' => [$c['unidadMedida']->id],
            'necesidad' => ['necesidad auditoria administrativa'],
            'inventario' => ['0'],
            'forma_pago' => $c['formaPago']->id,
            'proveedor' => $c['proveedor']->id,
        ])->assertRedirect();

        $adquisicion = Adquisicion::latest('id')->first();
        $this->assertNotNull($adquisicion);

        $log = Log::where('entidad', 'adquisicion')->where('accion', 'crear')->latest('id')->first();

        $this->assertNotNull($log);
        $this->assertEquals($adquisicion->id, $log->entidad_id);
        $this->assertEquals(20.0, (float) $log->valores_despues['total']);
        $this->assertStringContainsString(number_format(20, 2), $log->message);
    }

    public function test_articulo_creado_al_vuelo_deja_auditoria_con_su_id(): void
    {
        $c = $this->contexto();

        $this->actingAs($c['user'])->post(
            $this->urlStore($c),
            $this->payloadAlta($c, ['productos' => ['Tornillo especial T7']])
        )->assertRedirect();

        $articulo = Articulo::where('descripcion', 'Tornillo especial T7')->first();
        $this->assertNotNull($articulo);

        $log = Log::where('entidad', 'articulo')->where('accion', 'crear')->latest('id')->first();

        $this->assertNotNull($log);
        $this->assertEquals($articulo->id, $log->entidad_id);
        $this->assertStringContainsString('Tornillo especial T7', $log->message);
    }
}
