<?php

namespace Tests\Unit;

use App\Models\Adquisicion;
use Tests\TestCase;

/**
 * Totales monetarios del reporte global de adquisiciones.
 *
 * El servidor es la autoridad sobre el dinero: el subtotal sale de los datos
 * consultados y el porcentaje de costos indirectos se aplica sobre ese
 * subtotal, sin depender de ningun valor calculado en el navegador.
 */
class TotalesReporteGlobalAdquisicionesTest extends TestCase
{
    private function data(): array
    {
        return [
            'proyecto' => 'Proyecto de prueba',
            'subproyecto' => null,
            'data' => [
                'Etapa 1' => [
                    'contratista' => [['total_contratado' => 1000.0, 'pagos' => 400.0, 'saldo' => 600.0]],
                    'mano_obra' => [['total' => 500.0]],
                    'materiales_herramientas' => [['total' => 300.5]],
                    'servicios' => [['total' => 199.5]],
                ],
                'Etapa 2' => [
                    'contratista' => [],
                    'mano_obra' => [],
                    'materiales_herramientas' => [['total' => 1000.0]],
                    'servicios' => [],
                ],
            ],
        ];
    }

    public function test_el_subtotal_suma_las_cuatro_categorias_de_cada_etapa(): void
    {
        // 1000 + 500 + 300,5 + 199,5 = 2000  y  1000  => 3000
        $totales = Adquisicion::totalesReporteGlobal($this->data(), 0);

        $this->assertEqualsWithDelta(3000.0, $totales['subtotal'], 0.0001);
    }

    public function test_sin_porcentaje_no_hay_costos_indirectos_y_el_total_es_el_subtotal(): void
    {
        $totales = Adquisicion::totalesReporteGlobal($this->data(), 0);

        $this->assertEqualsWithDelta(0.0, $totales['costos_indirectos'], 0.0001);
        $this->assertEqualsWithDelta(3000.0, $totales['total_general'], 0.0001);
    }

    public function test_el_porcentaje_se_aplica_sobre_el_total_final(): void
    {
        // 3000 / (1 - 0,10) = 3333,3333
        $totales = Adquisicion::totalesReporteGlobal($this->data(), 10);

        $this->assertEqualsWithDelta(3333.3333, $totales['total_general'], 0.0001);
        $this->assertEqualsWithDelta(333.3333, $totales['costos_indirectos'], 0.0001);
    }

    public function test_un_porcentaje_de_100_no_rompe_el_calculo(): void
    {
        // La formula divide por (1 - p/100): con 100 daria division por cero.
        $totales = Adquisicion::totalesReporteGlobal($this->data(), 100);

        $this->assertIsFloat($totales['total_general']);
        $this->assertGreaterThan($totales['subtotal'], $totales['total_general']);
    }
}
