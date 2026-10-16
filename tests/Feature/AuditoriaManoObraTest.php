<?php

namespace Tests\Feature;

use App\Models\Articulo;
use App\Models\CatalogoDato;
use App\Models\DetalleManoObra;
use App\Models\Log;
use App\Models\ManoObra;
use App\Models\Proveedor;
use App\Models\Proyecto;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Auditoria de mano de obra (T4): la planificacion (alta/edicion/baja) y el
 * registro de trabajadores dejan una fila en `logs` con entidad/accion y los
 * valores antes/despues. Las llamadas viejas a `LogService::log()` se
 * mantienen intactas; la auditoria es aditiva.
 */
class AuditoriaManoObraTest extends TestCase
{
    use RefreshDatabase;

    private CatalogoDato $etapa;

    private CatalogoDato $tipoEtapa;

    private Proyecto $proyecto;

    private User $actor;

    protected function setUp(): void
    {
        parent::setUp();

        $this->actor = User::factory()->create(['usuario' => 'auditor_mano_obra', 'correo' => 'auditor_mano_obra@test.com']);

        $this->etapa = CatalogoDato::create(['descripcion' => 'Etapa auditoria MO', 'slug' => 'etapa-auditoria-mo', 'activo' => true]);
        $this->tipoEtapa = CatalogoDato::create(['descripcion' => 'Tipo etapa auditoria MO', 'slug' => 'tipo-etapa-auditoria-mo', 'activo' => true]);

        $catalogoProyecto = CatalogoDato::create(['descripcion' => 'Catalogo proyecto auditoria MO', 'slug' => 'catalogo-proyecto-auditoria-mo', 'activo' => true]);
        $estado = CatalogoDato::create(['descripcion' => 'Estado proyecto auditoria MO', 'slug' => 'estado-proyecto-auditoria-mo', 'activo' => true]);

        $this->proyecto = Proyecto::create([
            'catalogo_proyecto_id' => $catalogoProyecto->id,
            'nombre_proyecto' => 'Proyecto auditoria mano obra',
            'nombre_propietario' => 'Propietario auditoria MO',
            'telefono' => '0999999999',
            'correo' => 'auditoria-mo@example.com',
            'tipo_proyecto_id' => $catalogoProyecto->id,
            'portada' => 'portada.jpg',
            'estado_id' => $estado->id,
        ]);
    }

    private function actor(): User
    {
        return $this->actor;
    }

    private function urlPlanificacion(string $accion, ?int $manoObraId = null): string
    {
        // Ojo: crear/actualizar cuelgan de tipo-adquisicion/{ta}/{te} (dos
        // params), pero guardar/eliminar cuelgan de
        // tipo-adquisicion/{ta}/mano-obra/{te} (ver routes/modules/proyectos.php).
        $base = "/proyectos/tipo/1/adquisiciones/{$this->proyecto->id}"
            . "/tipo-adquisicion/{$this->etapa->id}/{$this->tipoEtapa->id}";
        $baseManoObra = "/proyectos/tipo/1/adquisiciones/{$this->proyecto->id}"
            . "/tipo-adquisicion/{$this->etapa->id}/mano-obra/{$this->tipoEtapa->id}";

        return match ($accion) {
            'crear' => $base . '/crear-planificacion',
            'actualizar' => $base . "/actualizar-planificacion/{$manoObraId}",
            'guardar' => $baseManoObra . "/guardar/{$manoObraId}",
            'eliminar' => $baseManoObra . "/eliminar-mano-obra/{$manoObraId}",
        };
    }

    private function ajaxHeaders(): array
    {
        return ['X-Requested-With' => 'XMLHttpRequest'];
    }

    private function crearPlanificacion(array $extras = []): ManoObra
    {
        return ManoObra::create(array_merge([
            'semana' => 1,
            'fecha_inicio' => '2026-08-10',
            'fecha_fin' => '2026-08-15',
            'proyecto_id' => $this->proyecto->id,
            'etapa_id' => $this->etapa->id,
            'tipo_etapa_id' => $this->tipoEtapa->id,
            'usuario_id' => $this->actor()->id,
            'subproyecto' => 'SUB-AUD',
        ], $extras));
    }

    private function crearTrabajadorYArticulo(): array
    {
        $categoriaArticulo = CatalogoDato::create(['descripcion' => 'Categoria articulo aud MO', 'slug' => 'categoria-articulo-aud-mo-' . uniqid(), 'activo' => true]);
        $articulo = Articulo::create([
            'categoria_id' => $categoriaArticulo->id,
            'codigo' => 'ART-AUD-MO',
            'descripcion' => 'Oficial',
            'activo' => true,
        ]);

        $categoriaProveedor = CatalogoDato::create(['descripcion' => 'Categoria proveedor aud MO', 'slug' => 'categoria-proveedor-aud-mo-' . uniqid(), 'activo' => true]);
        $proveedor = Proveedor::create([
            'categoria_proveedor_id' => $categoriaProveedor->id,
            'documento' => '0999999999001',
            'razon_social' => 'Trabajador auditoria MO',
            'direccion' => 'Direccion auditoria MO',
        ]);

        return [$proveedor, $articulo];
    }

    public function test_alta_planificacion_deja_auditoria_con_periodo(): void
    {
        $actor = $this->actor();
        $inicio = Carbon::parse('next Monday');
        $fin = $inicio->copy()->addDays(5);

        $response = $this->actingAs($actor)->post(
            $this->urlPlanificacion('crear'),
            [
                'proyecto_id' => $this->proyecto->id,
                'tipo_adquisicion' => $this->etapa->id,
                'tipo_etapa' => $this->tipoEtapa->id,
                'fecha_inicio' => $inicio->format('d-m-Y'),
                'fecha_fin' => $fin->format('d-m-Y'),
                'subproyecto' => 'SUB-AUD',
            ],
            $this->ajaxHeaders()
        );

        $response->assertOk()->assertJson(['success' => true]);

        $planificacion = ManoObra::latest('id')->first();
        $this->assertNotNull($planificacion);

        $log = Log::where('entidad', 'mano_obra')
            ->where('accion', 'crear')
            ->latest('id')
            ->first();

        $this->assertNotNull($log);
        $this->assertEquals($planificacion->id, $log->entidad_id);
        $this->assertEquals($actor->id, $log->user_id);
        $this->assertSame($inicio->format('Y-m-d'), $log->valores_despues['fecha_inicio']);
        $this->assertSame($fin->format('Y-m-d'), $log->valores_despues['fecha_fin']);
        $this->assertSame('SUB-AUD', $log->valores_despues['subproyecto']);
        $this->assertStringContainsString($inicio->format('Y-m-d'), $log->message);
        $this->assertStringContainsString($fin->format('Y-m-d'), $log->message);
    }

    public function test_edicion_planificacion_deja_antes_y_despues_distintos(): void
    {
        $actor = $this->actor();
        $planificacion = $this->crearPlanificacion([
            'fecha_fin' => '2026-08-15',
            'subproyecto' => 'SUB-VIEJO',
        ]);

        $response = $this->actingAs($actor)->put(
            $this->urlPlanificacion('actualizar', $planificacion->id),
            [
                'id' => $planificacion->id,
                'proyecto_id' => $this->proyecto->id,
                'fecha_fin' => '20-08-2026',
                'subproyecto' => 'SUB-NUEVO',
            ],
            $this->ajaxHeaders()
        );

        $response->assertOk()->assertJson(['success' => true]);

        $log = Log::where('entidad', 'mano_obra')
            ->where('accion', 'actualizar')
            ->latest('id')
            ->first();

        $this->assertNotNull($log);
        $this->assertEquals($planificacion->id, $log->entidad_id);
        $this->assertEquals($actor->id, $log->user_id);
        $this->assertSame('2026-08-15', $log->valores_antes['fecha_fin']);
        $this->assertSame('2026-08-20', $log->valores_despues['fecha_fin']);
        $this->assertSame('SUB-VIEJO', $log->valores_antes['subproyecto']);
        $this->assertSame('SUB-NUEVO', $log->valores_despues['subproyecto']);
        $this->assertNotSame($log->valores_antes['fecha_fin'], $log->valores_despues['fecha_fin']);
        $this->assertStringContainsString('2026-08-20', $log->message);
    }

    public function test_baja_planificacion_deja_fila_borrada_en_antes(): void
    {
        $actor = $this->actor();
        $planificacion = $this->crearPlanificacion();
        [$proveedor, $articulo] = $this->crearTrabajadorYArticulo();

        DetalleManoObra::create([
            'fecha' => '2026-08-10',
            'mano_obra_id' => $planificacion->id,
            'proveedor_id' => $proveedor->id,
            'articulo_id' => $articulo->id,
            'jornada' => 'COMPLETA',
            'valor' => 25.5,
        ]);

        $response = $this->actingAs($actor)->delete(
            $this->urlPlanificacion('eliminar', $planificacion->id),
            [],
            $this->ajaxHeaders()
        );

        $response->assertOk()->assertJson(['success' => true]);

        // La planificacion ya no existe: la auditoria es el unico rastro.
        $this->assertDatabaseMissing('mano_obra', ['id' => $planificacion->id]);

        $log = Log::where('entidad', 'mano_obra')
            ->where('accion', 'eliminar')
            ->latest('id')
            ->first();

        $this->assertNotNull($log);
        $this->assertEquals($planificacion->id, $log->entidad_id);
        $this->assertEquals($actor->id, $log->user_id);
        $this->assertSame('2026-08-10', $log->valores_antes['fecha_inicio']);
        $this->assertSame('2026-08-15', $log->valores_antes['fecha_fin']);
        $this->assertSame('SUB-AUD', $log->valores_antes['subproyecto']);
        $this->assertStringContainsString('semana 1', $log->message);
    }

    public function test_registro_y_edicion_de_trabajadores_dejan_auditoria_con_valores(): void
    {
        $actor = $this->actor();
        $planificacion = $this->crearPlanificacion();
        [$proveedor, $articulo] = $this->crearTrabajadorYArticulo();

        $payload = function (float $valor) use ($planificacion, $proveedor, $articulo): array {
            return [
                'mano_obra' => $planificacion->id,
                'fecha' => '2026-08-10',
                'personal' => [$proveedor->id],
                'categoria' => [$articulo->id],
                'jornada' => ['COMPLETA'],
                'valor' => [$valor],
                'adicional' => [2],
                'descuento' => [1],
                'detalle_adicional' => [null],
                'detalle_descuento' => [null],
                'observaciones' => [null],
            ];
        };

        // Alta: sin personal previo.
        $response = $this->actingAs($actor)->post(
            $this->urlPlanificacion('guardar', $planificacion->id),
            $payload(25.5)
        );
        $response->assertStatus(302);

        $alta = Log::where('entidad', 'mano_obra_trabajador')
            ->where('accion', 'crear')
            ->latest('id')
            ->first();

        $this->assertNotNull($alta);
        $this->assertEquals($planificacion->id, $alta->entidad_id);
        $this->assertEquals($actor->id, $alta->user_id);
        $this->assertSame([], $alta->valores_antes['detalles']);
        $this->assertEquals(25.5, (float) $alta->valores_despues['detalles'][0]['valor']);
        $this->assertEquals(2, (float) $alta->valores_despues['detalles'][0]['adicional']);
        $this->assertEquals(1, (float) $alta->valores_despues['detalles'][0]['descuento']);
        $this->assertStringContainsString('2026-08-10', $alta->message);

        // Edicion: el mismo dia ya tiene personal, cambia el valor.
        $response = $this->actingAs($actor)->post(
            $this->urlPlanificacion('guardar', $planificacion->id),
            $payload(30.0)
        );
        $response->assertStatus(302);

        $edicion = Log::where('entidad', 'mano_obra_trabajador')
            ->where('accion', 'actualizar')
            ->latest('id')
            ->first();

        $this->assertNotNull($edicion);
        $this->assertEquals($planificacion->id, $edicion->entidad_id);
        $this->assertEquals(25.5, (float) $edicion->valores_antes['detalles'][0]['valor']);
        $this->assertEquals(30.0, (float) $edicion->valores_despues['detalles'][0]['valor']);
    }
}
