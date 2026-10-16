<?php

namespace Tests\Feature;

use App\Models\Adquisicion;
use App\Models\Articulo;
use App\Models\CatalogoDato;
use App\Models\Proveedor;
use App\Models\Proyecto;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

/**
 * Numero de adquisicion unico.
 *
 * El numero se calculaba al renderizar el formulario de alta ("ultimo id + 1")
 * y viajaba al cliente dentro de un campo oculto. Dos formularios abiertos a la
 * vez, o uno enviado tarde, calculaban el mismo numero y la base lo aceptaba:
 * quedaron 36 numeros duplicados en produccion.
 *
 * La regla es que el numero guardado se deriva del propio registro (fecha de
 * registro + su id), nunca del valor que manda el cliente.
 */
class NumeroAdquisicionUnicoTest extends TestCase
{
    use RefreshDatabase;

    private function contexto(): array
    {
        $user = User::factory()->create(['usuario' => 'usuario_numero_adquisicion_test']);

        $etapa = CatalogoDato::create(['descripcion' => 'Etapa numero adq', 'slug' => 'etapa-numero-adq', 'activo' => true]);
        $tipoEtapa = CatalogoDato::create(['descripcion' => 'Tipo etapa numero adq', 'slug' => 'tipo-etapa-numero-adq', 'activo' => true]);
        $catalogoProyecto = CatalogoDato::create(['descripcion' => 'Catalogo proyecto numero adq', 'slug' => 'catalogo-proyecto-numero-adq', 'activo' => true]);
        $estadoProyecto = CatalogoDato::create(['descripcion' => 'Estado proyecto numero adq', 'slug' => 'estado-proyecto-numero-adq', 'activo' => true]);
        $formaPago = CatalogoDato::create(['descripcion' => 'Forma pago numero adq', 'slug' => 'forma-pago-numero-adq', 'activo' => true]);
        $unidadMedida = CatalogoDato::create(['descripcion' => 'Unidad medida numero adq', 'slug' => 'unidad-medida-numero-adq', 'activo' => true]);

        $proyecto = Proyecto::create([
            'catalogo_proyecto_id' => $catalogoProyecto->id,
            'nombre_proyecto' => 'Proyecto numero adq',
            'nombre_propietario' => 'Propietario numero adq',
            'telefono' => '0999999999',
            'correo' => 'numero-adq@example.com',
            'tipo_proyecto_id' => $catalogoProyecto->id,
            'portada' => 'portada.jpg',
            'estado_id' => $estadoProyecto->id,
        ]);

        $articulo = Articulo::create([
            'categoria_id' => $etapa->id,
            'codigo' => 'ART-NUM-ADQ',
            'descripcion' => 'Articulo numero adq',
            'valor_unitario' => 10,
            'iva' => 0,
            'activo' => true,
        ]);

        $proveedor = Proveedor::create([
            'categoria_proveedor_id' => $tipoEtapa->id,
            'documento' => '1799999999',
            'razon_social' => 'Proveedor numero adq',
        ]);

        $url = '/proyectos/obra/1/adquisiciones/' . $proyecto->id . '/tipo-adquisicion/'
            . $etapa->id . '/' . $tipoEtapa->id . '/guardar';

        return compact('user', 'etapa', 'tipoEtapa', 'proyecto', 'articulo', 'proveedor', 'formaPago', 'unidadMedida', 'url');
    }

    private function alta(array $contexto, string $numeroEnviado, string $necesidad)
    {
        return $this->actingAs($contexto['user'])->post($contexto['url'], [
            'fecha' => '2026-01-15',
            'numero_pedido' => $numeroEnviado,
            'proyecto_id' => $contexto['proyecto']->id,
            'tipo_adquisicion' => $contexto['etapa']->id,
            'tipo_etapa' => $contexto['tipoEtapa']->id,
            'productos' => [$contexto['articulo']->id],
            'cantidad' => ['1'],
            'necesidad' => [$necesidad],
            'km' => [null],
            'precio' => ['10'],
            'forma_pago' => $contexto['formaPago']->id,
            'proveedor' => $contexto['proveedor']->id,
        ]);
    }

    public function test_dos_altas_con_el_mismo_numero_enviado_no_guardan_numeros_duplicados(): void
    {
        $contexto = $this->contexto();
        $numeroCompartido = '20200101-777';

        $this->travelTo(Carbon::parse('2026-01-15 10:00:00'));

        $this->alta($contexto, $numeroCompartido, 'necesidad primera alta')->assertRedirect();
        $this->alta($contexto, $numeroCompartido, 'necesidad segunda alta')->assertRedirect();

        $adquisiciones = Adquisicion::orderBy('id')->get();
        $numeros = $adquisiciones->pluck('numero')->all();

        $this->assertCount(2, $numeros, 'Las dos altas debieron guardarse.');
        $this->assertSame($numeros, array_values(array_unique($numeros)), 'Dos altas guardaron el mismo numero.');
        $this->assertNotContains($numeroCompartido, $numeros, 'Se guardo el numero que mando el cliente.');

        foreach ($adquisiciones as $adquisicion) {
            $this->assertSame(
                '20260115-' . str_pad($adquisicion->id, 3, '0', STR_PAD_LEFT),
                $adquisicion->numero
            );
        }
    }

    public function test_el_numero_guardado_se_deriva_del_registro_y_no_del_valor_enviado(): void
    {
        $contexto = $this->contexto();

        $this->travelTo(Carbon::parse('2026-03-09 23:59:00'));

        $this->alta($contexto, 'NUMERO-INVENTADO-POR-EL-CLIENTE', 'necesidad numero derivado')->assertRedirect();

        $adquisicion = Adquisicion::orderBy('id', 'desc')->first();

        $this->assertSame(
            '20260309-' . str_pad($adquisicion->id, 3, '0', STR_PAD_LEFT),
            $adquisicion->numero
        );
    }

    public function test_el_alta_administrativa_tambien_deriva_el_numero_del_registro(): void
    {
        $contexto = $this->contexto();

        $this->travelTo(Carbon::parse('2026-02-20 08:00:00'));

        $this->actingAs($contexto['user'])->post('/administrativo/adquisiciones/materiales/guardar', [
            'numero_orden' => '20200101-777',
            'proyecto' => $contexto['proyecto']->id,
            'etapa' => $contexto['etapa']->id,
            'actividad' => $contexto['tipoEtapa']->id,
            'productos' => [$contexto['articulo']->id],
            'cantidad' => ['1'],
            'valor_unitario' => ['10'],
            'iva_producto' => ['0'],
            'unidad_medida' => [$contexto['unidadMedida']->id],
            'necesidad' => ['necesidad alta administrativa'],
            'inventario' => ['0'],
            'forma_pago' => $contexto['formaPago']->id,
            'proveedor' => $contexto['proveedor']->id,
        ])->assertRedirect();

        $adquisicion = Adquisicion::orderBy('id', 'desc')->first();

        $this->assertNotSame('20200101-777', $adquisicion->numero, 'Se guardo el numero que mando el cliente.');
        $this->assertSame(
            '20260220-' . str_pad($adquisicion->id, 3, '0', STR_PAD_LEFT),
            $adquisicion->numero
        );
    }

    public function test_el_numero_sigue_siendo_unico_con_ids_de_cuatro_cifras(): void
    {
        $adquisicion = (new Adquisicion())->forceFill(['id' => 1234])->setCreatedAt('2026-05-04 12:00:00');

        $this->assertSame('20260504-1234', numeroAdquisicion($adquisicion));
    }

    public function test_la_vista_previa_del_numero_funciona_con_la_tabla_vacia(): void
    {
        $this->travelTo(Carbon::parse('2026-01-15 10:00:00'));

        $this->assertSame('20260115-001', generarNumeroOrden());
    }
}
