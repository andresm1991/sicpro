<?php

namespace Tests\Feature;

use App\Models\CatalogoDato;
use App\Models\JPLimpieza\Adquisicion as AdquisicionJp;
use App\Models\JPLimpieza\Producto as ProductoJp;
use App\Models\JPLimpieza\Proyecto as ProyectoJp;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

/**
 * Numero de adquisicion unico en JP Limpieza.
 *
 * Mismo defecto que el modulo principal: el numero se calculaba al abrir el
 * formulario ("ultimo id + 1") y se guardaba tal cual al enviar. En
 * jp_limpieza.adquisiciones eso dejo 28 numeros repetidos entre el 18/07/2025 y
 * el 11/09/2025, con un caso de 41 filas con el mismo numero; la validacion
 * unique del 15/09/2025 fue el torniquete.
 *
 * La conexion mysql_jp_limpieza se aisla con DB_JPLIMPIEZA_DATABASE en
 * phpunit.xml. Sin eso apunta a la base real jp_limpieza y este test escribiria
 * en datos de produccion: el esquema JP comparte 9 nombres de tabla con el
 * principal, asi que tampoco se puede redirigir a sicpro_testing.
 */
class NumeroAdquisicionJPLimpiezaTest extends TestCase
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
        $user = User::factory()->create(['usuario' => 'usuario_numero_jp_test']);

        $tipo = CatalogoDato::create([
            'descripcion' => 'Tipo numero JP',
            'slug' => 'tipo-numero-jp-test',
            'activo' => true,
        ]);

        $proyecto = ProyectoJp::create([
            'nombre_proyecto' => 'Proyecto numero JP',
            'entidad' => 'Entidad numero JP',
            'metros_contratado' => 100,
            'precio_por_metro' => 10,
            'tiempo_contratado' => 1,
            'fecha_inicio' => '2026-01-01',
            'fecha_finalizacion' => '2026-12-31',
        ]);

        $producto = ProductoJp::create([
            'nombre' => 'Producto numero JP',
            'precio_unitario' => 10,
            'iva' => 0,
        ]);

        return compact('user', 'tipo', 'proyecto', 'producto');
    }

    private function alta(array $contexto, string $numeroEnviado): void
    {
        $url = sprintf(
            '/jp-limpieza/adquisiciones/%d/%s/guardar',
            $contexto['proyecto']->id,
            $contexto['tipo']->slug
        );

        $this->actingAs($contexto['user'])->post($url, [
            'fecha' => '2026-04-10',
            'numero' => $numeroEnviado,
            'proyecto' => $contexto['proyecto']->id,
            'tipo_adquisicion' => $contexto['tipo']->slug,
            'proveedor' => 1,
            'forma_pago' => 1,
            'factura' => 'F-001',
            'producto' => [$contexto['producto']->id],
            'cantidad' => ['1'],
            'precio' => ['10'],
            'iva' => ['0'],
            'unidad_medida' => ['1'],
            'necesidad' => ['necesidad jp'],
            'inventario' => ['0'],
        ])->assertRedirect();
    }

    public function test_el_numero_del_alta_jp_se_deriva_del_registro_y_no_del_valor_enviado(): void
    {
        $contexto = $this->contexto();

        $this->travelTo(Carbon::parse('2026-04-10 09:00:00'));

        $this->alta($contexto, 'NUMERO-INVENTADO-POR-EL-CLIENTE');

        $adquisicion = AdquisicionJp::orderByDesc('id')->first();

        $this->assertNotNull($adquisicion, 'El alta de JP debio guardarse.');
        $this->assertSame(
            '20260410-' . str_pad($adquisicion->id, 3, '0', STR_PAD_LEFT),
            $adquisicion->numero
        );
    }

    public function test_dos_altas_jp_con_el_mismo_numero_enviado_no_guardan_numeros_duplicados(): void
    {
        $contexto = $this->contexto();

        $this->travelTo(Carbon::parse('2026-04-10 09:00:00'));

        $this->alta($contexto, '20200101-777');
        $this->alta($contexto, '20200101-777');

        $adquisiciones = AdquisicionJp::orderBy('id')->get();
        $numeros = $adquisiciones->pluck('numero')->all();

        $this->assertCount(2, $numeros, 'La base de test de JP deberia tener solo estas dos altas: mas filas significa que la conexion JP no se esta revirtiendo entre tests.');
        $this->assertSame($numeros, array_values(array_unique($numeros)), 'Dos altas guardaron el mismo numero.');
        $this->assertNotContains('20200101-777', $numeros, 'Se guardo el numero que mando el cliente.');

        foreach ($adquisiciones as $adquisicion) {
            $this->assertSame(
                '20260410-' . str_pad($adquisicion->id, 3, '0', STR_PAD_LEFT),
                $adquisicion->numero
            );
            // El valor provisorio con el que se inserta la fila nunca debe quedar:
            // la asignacion del numero final tiene que ser atomica con el alta.
            $this->assertDoesNotMatchRegularExpression(
                '/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/',
                $adquisicion->numero
            );
        }
    }
}
