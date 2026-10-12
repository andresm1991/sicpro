<?php

namespace Tests\Feature;

use App\Models\Adquisicion;
use App\Models\CatalogoDato;
use App\Models\Proyecto;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Tests\TestCase;

/**
 * El filtro "Costo" del reporte de adquisiciones debe seleccionar por
 * adquisiciones.tipo_costo_id (catalogo tipo.costos), no por etapa_id.
 */
class FiltroCostoReporteAdquisicionesTest extends TestCase
{
    use RefreshDatabase;

    private function crearContextoBase(): array
    {
        $user = User::factory()->create(['usuario' => 'usuario_filtro_costo_test']);

        $etapa = CatalogoDato::create(['descripcion' => 'Etapa filtro costo', 'slug' => 'etapa-filtro-costo', 'activo' => true]);
        $tipoEtapa = CatalogoDato::create(['descripcion' => 'Tipo etapa filtro costo', 'slug' => 'tipo-etapa-filtro-costo', 'activo' => true]);
        $catalogoProyecto = CatalogoDato::create(['descripcion' => 'Catalogo proyecto filtro costo', 'slug' => 'catalogo-proyecto-filtro-costo', 'activo' => true]);
        $estadoProyecto = CatalogoDato::create(['descripcion' => 'Estado proyecto filtro costo', 'slug' => 'estado-proyecto-filtro-costo', 'activo' => true]);

        $costoDirecto = CatalogoDato::create(['descripcion' => 'Costos Directos', 'slug' => 'tipo.costos.directos', 'activo' => true]);
        $costoIndirecto = CatalogoDato::create(['descripcion' => 'Costos Indirectos', 'slug' => 'tipo.costos.indirectos', 'activo' => true]);

        $proyecto = Proyecto::create([
            'catalogo_proyecto_id' => $catalogoProyecto->id,
            'nombre_proyecto' => 'Proyecto filtro costo',
            'nombre_propietario' => 'Propietario filtro costo',
            'telefono' => '0999999999',
            'correo' => 'filtro-costo@example.com',
            'tipo_proyecto_id' => $catalogoProyecto->id,
            'portada' => 'portada.jpg',
            'estado_id' => $estadoProyecto->id,
        ]);

        $adquisicionDirecta = Adquisicion::create([
            'fecha' => '2025-01-10',
            'numero' => 'ADQ-DIRECTA',
            'proyecto_id' => $proyecto->id,
            'etapa_id' => $etapa->id,
            'tipo_etapa_id' => $tipoEtapa->id,
            'usuario_id' => $user->id,
            'estado' => 'Completado',
            'tipo_adquisicion' => 'operativo',
            'tipo_costo_id' => $costoDirecto->id,
        ]);

        $adquisicionIndirecta = Adquisicion::create([
            'fecha' => '2025-01-11',
            'numero' => 'ADQ-INDIRECTA',
            'proyecto_id' => $proyecto->id,
            'etapa_id' => $etapa->id,
            'tipo_etapa_id' => $tipoEtapa->id,
            'usuario_id' => $user->id,
            'estado' => 'Completado',
            'tipo_adquisicion' => 'operativo',
            'tipo_costo_id' => $costoIndirecto->id,
        ]);

        return compact('costoDirecto', 'adquisicionDirecta', 'adquisicionIndirecta');
    }

    public function test_el_filtro_costo_selecciona_por_tipo_costo_id(): void
    {
        ['costoDirecto' => $costoDirecto, 'adquisicionDirecta' => $directa, 'adquisicionIndirecta' => $indirecta] = $this->crearContextoBase();

        $request = new Request();
        $request->merge(['costo' => (string) $costoDirecto->id]);

        $ids = Adquisicion::dataReporteAdquisiciones($request)
            ->map(fn ($item) => $item['adquisicion']->id)
            ->all();

        $this->assertSame([$directa->id], $ids);
        $this->assertNotContains($indirecta->id, $ids);
    }

    public function test_sin_filtro_costo_aparecen_todas_las_adquisiciones(): void
    {
        ['adquisicionDirecta' => $directa, 'adquisicionIndirecta' => $indirecta] = $this->crearContextoBase();

        $ids = Adquisicion::dataReporteAdquisiciones(new Request())
            ->map(fn ($item) => $item['adquisicion']->id)
            ->all();

        $this->assertEqualsCanonicalizing([$directa->id, $indirecta->id], $ids);
    }
}
