<?php

namespace Tests\Feature;

use App\Models\Adquisicion;
use App\Models\CatalogoDato;
use App\Models\Proyecto;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * El indice unico de adquisiciones.numero.
 *
 * Los numeros duplicados se pudieron crear dos veces (36 grupos en sicpro, 28 en
 * jp_limpieza) porque la columna no tenia indice unico: el codigo calculaba el
 * numero y la base lo aceptaba sin chistar. Ya reparado el historico, este indice
 * es la unica barrera que convierte un duplicado futuro en un error ruidoso en
 * vez de un duplicado mudo, que es exactamente lo que paso dos veces.
 */
class IndiceUnicoNumeroAdquisicionTest extends TestCase
{
    use RefreshDatabase;

    private function contexto(): array
    {
        $user = User::factory()->create(['usuario' => 'usuario_indice_numero_test']);

        $etapa = CatalogoDato::create(['descripcion' => 'Etapa indice', 'slug' => 'etapa-indice-numero', 'activo' => true]);
        $tipoEtapa = CatalogoDato::create(['descripcion' => 'Tipo etapa indice', 'slug' => 'tipo-etapa-indice-numero', 'activo' => true]);
        $catalogoProyecto = CatalogoDato::create(['descripcion' => 'Catalogo indice', 'slug' => 'catalogo-indice-numero', 'activo' => true]);
        $estadoProyecto = CatalogoDato::create(['descripcion' => 'Estado indice', 'slug' => 'estado-indice-numero', 'activo' => true]);

        $proyecto = Proyecto::create([
            'catalogo_proyecto_id' => $catalogoProyecto->id,
            'nombre_proyecto' => 'Proyecto indice numero',
            'nombre_propietario' => 'Propietario indice numero',
            'telefono' => '0999999999',
            'correo' => 'indice-numero@example.com',
            'tipo_proyecto_id' => $catalogoProyecto->id,
            'portada' => 'portada.jpg',
            'estado_id' => $estadoProyecto->id,
        ]);

        return compact('user', 'etapa', 'tipoEtapa', 'proyecto');
    }

    private function adquisicion(array $contexto, string $numero): void
    {
        Adquisicion::create([
            'fecha' => '2026-01-15',
            'numero' => $numero,
            'proyecto_id' => $contexto['proyecto']->id,
            'etapa_id' => $contexto['etapa']->id,
            'tipo_etapa_id' => $contexto['tipoEtapa']->id,
            'usuario_id' => $contexto['user']->id,
            'estado' => 'En Proceso',
            'tipo_adquisicion' => 'operativo',
        ]);
    }

    public function test_la_base_rechaza_dos_adquisiciones_con_el_mismo_numero(): void
    {
        $contexto = $this->contexto();

        $this->adquisicion($contexto, '20260115-999');

        $this->expectException(QueryException::class);

        $this->adquisicion($contexto, '20260115-999');
    }
}
