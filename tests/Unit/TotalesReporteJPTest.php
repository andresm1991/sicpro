<?php

namespace Tests\Unit;

use App\Models\JPLimpieza\Adquisicion as JPLimpiezaAdquisicion;
use App\Models\JPLimpieza\Proyecto as JPLimpiezaProyecto;
use Tests\TestCase;

/**
 * Totales de los reportes de JP Limpieza.
 *
 * El preview (ajax) y el PDF sumaban el mismo resultado por caminos
 * distintos: el reporte por articulo con flatten(1)->sum('monto_total')
 * inline en cada lado y el balance con tres sum() inline en cada lado. Ahora
 * los dos caminos comparten estas funciones.
 */
class TotalesReporteJPTest extends TestCase
{
    public function test_el_gran_total_por_articulo_suma_los_montos_de_todas_las_categorias(): void
    {
        $reporte = [
            'Articulo A' => [
                ['monto_total' => 100.5],
                ['monto_total' => 49.5],
            ],
            'Articulo B' => [
                ['monto_total' => 200.0],
            ],
        ];

        $this->assertEqualsWithDelta(
            350.0,
            JPLimpiezaAdquisicion::granTotalReportePorArticulo($reporte),
            0.0001
        );
    }

    public function test_el_gran_total_por_articulo_es_cero_sin_datos(): void
    {
        $this->assertEqualsWithDelta(
            0.0,
            JPLimpiezaAdquisicion::granTotalReportePorArticulo([]),
            0.0001
        );
    }

    public function test_los_totales_del_balance_suman_ingresos_gastos_y_utilidad(): void
    {
        $reporte = collect([
            ['total_ingresos' => 1000.0, 'total_gastos' => 400.0, 'utilidad' => 600.0],
            ['total_ingresos' => 500.0, 'total_gastos' => 100.0, 'utilidad' => 400.0],
        ]);

        $totales = JPLimpiezaProyecto::totalesBalance($reporte);

        $this->assertEqualsWithDelta(1500.0, $totales['total_ingresos'], 0.0001);
        $this->assertEqualsWithDelta(500.0, $totales['total_gastos'], 0.0001);
        $this->assertEqualsWithDelta(1000.0, $totales['utilidad'], 0.0001);
    }

    public function test_los_totales_del_balance_son_cero_sin_datos(): void
    {
        $totales = JPLimpiezaProyecto::totalesBalance(collect());

        $this->assertEqualsWithDelta(0.0, $totales['total_ingresos'], 0.0001);
        $this->assertEqualsWithDelta(0.0, $totales['total_gastos'], 0.0001);
        $this->assertEqualsWithDelta(0.0, $totales['utilidad'], 0.0001);
    }
}
