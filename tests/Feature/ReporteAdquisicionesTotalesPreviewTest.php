<?php

namespace Tests\Feature;

use App\Models\CatalogoDato;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Contrato de los totales del preview del reporte de adquisiciones.
 *
 * El preview ya no suma en el navegador: recibe la clave `totales` del servidor,
 * calculada con Adquisicion::totalesReporteAdquisiciones(), la misma funcion que
 * arma el PDF y el Excel. Si esa clave desaparece del JSON, el JS muestra ceros
 * en el pie sin que nadie se entere: por eso el contrato se fija aca.
 *
 * La matematica de la funcion (importes formateados, claves ausentes, filas
 * vacias) esta cubierta en tests/Unit/TotalesReporteAdquisicionesTest.php.
 */
class ReporteAdquisicionesTotalesPreviewTest extends TestCase
{
    use RefreshDatabase;

    private function usuario(): User
    {
        return User::factory()->create([
            'usuario' => 'usuario_totales_preview_test',
            'correo' => 'usuario_totales_preview_test@example.test',
        ]);
    }

    private function tipo(string $slug): CatalogoDato
    {
        return CatalogoDato::create([
            'descripcion' => ucfirst($slug),
            'detalle' => '',
            'slug' => $slug,
            'padre_id' => null,
            'activo' => true,
        ]);
    }

    public function test_el_preview_de_materiales_devuelve_los_totales_del_servidor(): void
    {
        $response = $this->actingAs($this->usuario())
            ->post('/reportes/visulizar-reporte-adquisiciones', [
                'tipo_reporte' => 'operativo',
                'tipo' => $this->tipo('meteriales.herramientas')->id,
            ]);

        $response->assertOk();

        // Los cuatro ceros llegan como numeros JSON: 0.0 se serializa como 0.
        $this->assertEquals([
            'total_general' => 0.0,
            'total_pagado' => 0.0,
            'total_saldos' => 0.0,
            'total_cantidades' => 0.0,
        ], $response->json('totales'));
    }

    public function test_el_preview_de_contratistas_devuelve_los_totales_del_servidor(): void
    {
        $response = $this->actingAs($this->usuario())
            ->post('/reportes/visulizar-reporte-adquisiciones', [
                'tipo_reporte' => 'operativo',
                'tipo' => $this->tipo('contratista')->id,
            ]);

        $response->assertOk();

        $this->assertEquals([
            'total_general' => 0.0,
            'total_pagado' => 0.0,
            'total_saldos' => 0.0,
            'total_cantidades' => 0.0,
        ], $response->json('totales'));
    }

    /**
     * La rama global trae ademas el desglose por etapa y el pie con costos
     * indirectos: es lo que la vista PDF y el navegador sumaban por su cuenta.
     */
    public function test_el_preview_global_devuelve_el_desglose_y_el_pie(): void
    {
        $response = $this->actingAs($this->usuario())
            ->post('/reportes/visulizar-reporte-adquisiciones', [
                'tipo_reporte' => 'global',
                'costos_indirectos' => '10',
            ]);

        $response->assertOk();

        // Sin proyecto el filtro devuelve data vacia: el desglose es vacio.
        $this->assertEquals([
            'por_etapa' => [],
            'total_productos' => 0.0,
            'subtotal' => 0.0,
            'costos_indirectos' => 0.0,
            'total_general' => 0.0,
            'porcentaje' => 10.0,
        ], $response->json('totales'));
    }
}
