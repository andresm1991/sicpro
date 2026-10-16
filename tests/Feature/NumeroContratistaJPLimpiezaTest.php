<?php

namespace Tests\Feature;

use App\Models\CatalogoDato;
use App\Models\JPLimpieza\Contratista as ContratistaJp;
use App\Models\JPLimpieza\Producto as ProductoJp;
use App\Models\JPLimpieza\Proyecto as ProyectoJp;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

/**
 * Numero de contratista unico en JP Limpieza.
 *
 * `jp_limpieza.contratistas` esta vacia, asi que hoy no hay numeros duplicados,
 * pero el defecto estaba latente y arrancaba peor que en adquisiciones: la vista
 * previa usaba `Contratista::first()` en vez del ultimo registro, o sea que
 * siempre proponia el sufijo 002, y el numero se guardaba tal cual lo mandara el
 * cliente, sin validacion ni indice unico.
 *
 * Sigue el mismo patron de aislamiento que NumeroAdquisicionJPLimpiezaTest:
 * `DB_JPLIMPIEZA_DATABASE` en phpunit.xml mantiene la conexion lejos de la base
 * real jp_limpieza.
 */
class NumeroContratistaJPLimpiezaTest extends TestCase
{
    use RefreshDatabase;

    /** Las escrituras de JP tambien tienen que revertirse entre tests. */
    protected $connectionsToTransact = ['mysql', 'mysql_jp_limpieza'];

    protected function setUp(): void
    {
        parent::setUp();

        // Red de seguridad: escribir en la base real jp_limpieza seria un accidente
        // silencioso. El override de phpunit.xml puede quedar anulado por
        // DATABASE_JPLIMPIEZA_URL (config/database.php), asi que se verifica en vez
        // de confiar. Si salta, esta base no esta aislada.
        if (!str_ends_with((string) config('database.connections.mysql_jp_limpieza.database'), '_testing')) {
            $this->markTestSkipped(
                'mysql_jp_limpieza no apunta a una base de testing: correr este test escribiria en datos reales.'
            );
        }
    }

    private function contexto(): array
    {
        $user = User::factory()->create(['usuario' => 'usuario_numero_contratista_jp_test']);

        CatalogoDato::create([
            'descripcion' => 'En proceso',
            'slug' => 'estados.contratistas.proceso',
            'activo' => true,
        ]);

        $proyecto = ProyectoJp::create([
            'nombre_proyecto' => 'Proyecto numero contratista JP',
            'entidad' => 'Entidad numero contratista JP',
            'metros_contratado' => 100,
            'precio_por_metro' => 10,
            'tiempo_contratado' => 1,
            'fecha_inicio' => '2026-01-01',
            'fecha_finalizacion' => '2026-12-31',
        ]);

        $producto = ProductoJp::create([
            'nombre' => 'Producto numero contratista JP',
            'precio_unitario' => 10,
            'iva' => 0,
        ]);

        return compact('user', 'proyecto', 'producto');
    }

    private function alta(array $contexto, string $numeroEnviado): void
    {
        $url = sprintf('/jp-limpieza/contratistas/%d/guardar', $contexto['proyecto']->id);

        $this->actingAs($contexto['user'])->post($url, [
            'numero' => $numeroEnviado,
            'proyecto' => $contexto['proyecto']->id,
            'proveedor' => 1,
            'categoria' => 1,
            'fecha' => '2026-04-10',
            'plazo' => 30,
            'producto' => [$contexto['producto']->id],
            'cantidad' => ['1'],
            'precio' => ['10'],
            'iva' => ['0'],
            'unidad_medida' => ['1'],
        ])->assertRedirect();
    }

    public function test_el_numero_del_contratista_se_deriva_del_registro_y_no_del_valor_enviado(): void
    {
        $contexto = $this->contexto();

        $this->travelTo(Carbon::parse('2026-04-10 09:00:00'));

        $this->alta($contexto, 'NUMERO-INVENTADO-POR-EL-CLIENTE');

        $contratista = ContratistaJp::orderByDesc('id')->first();

        $this->assertNotNull($contratista, 'El alta del contratista debio guardarse.');
        $this->assertSame(
            '20260410-' . str_pad($contratista->id, 3, '0', STR_PAD_LEFT),
            $contratista->numero
        );
    }

    public function test_dos_altas_de_contratista_con_el_mismo_numero_enviado_no_duplican(): void
    {
        $contexto = $this->contexto();

        $this->travelTo(Carbon::parse('2026-04-10 09:00:00'));

        $this->alta($contexto, '20200101-777');
        $this->alta($contexto, '20200101-777');

        $contratistas = ContratistaJp::orderBy('id')->get();
        $numeros = $contratistas->pluck('numero')->all();

        $this->assertCount(2, $numeros, 'La base de test de JP deberia tener solo estas dos altas: mas filas significa que la conexion JP no se esta revirtiendo entre tests.');
        $this->assertSame($numeros, array_values(array_unique($numeros)), 'Dos altas guardaron el mismo numero.');
        $this->assertNotContains('20200101-777', $numeros, 'Se guardo el numero que mando el cliente.');

        foreach ($contratistas as $contratista) {
            $this->assertSame(
                '20260410-' . str_pad($contratista->id, 3, '0', STR_PAD_LEFT),
                $contratista->numero
            );
        }
    }
}
