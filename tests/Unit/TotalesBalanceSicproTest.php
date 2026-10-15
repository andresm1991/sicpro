<?php

namespace Tests\Unit;

use App\Models\Proyecto;
use Tests\TestCase;

/**
 * Totales del pie del reporte de balance de SICPRO.
 *
 * El AJAX de gerencia y el PDF de Prime los calculaban por separado, y el PDF
 * pasaba solo tres de los cinco totales: la tabla del pie los usa todos, asi
 * que reventaba (Undefined variable) en cuanto el balance tenia filas.
 */
class TotalesBalanceSicproTest extends TestCase
{
    public function test_suma_los_cinco_totales_del_balance(): void
    {
        $balance = collect([
            [
                'total_ingresos' => 1000.0,
                'gastos_operativos' => 300.0,
                'gastos_administrativos' => 100.0,
                'total_gastos' => 400.0,
                'utilidad' => 600.0,
            ],
            [
                'total_ingresos' => 500.0,
                'gastos_operativos' => 150.0,
                'gastos_administrativos' => 50.0,
                'total_gastos' => 200.0,
                'utilidad' => 300.0,
            ],
        ]);

        $totales = Proyecto::totalesBalanceReporte($balance);

        $this->assertEqualsWithDelta(1500.0, $totales['total_ingresos'], 0.0001);
        $this->assertEqualsWithDelta(450.0, $totales['gastos_operativos'], 0.0001);
        $this->assertEqualsWithDelta(150.0, $totales['gastos_administrativos'], 0.0001);
        $this->assertEqualsWithDelta(600.0, $totales['total_gastos'], 0.0001);
        $this->assertEqualsWithDelta(900.0, $totales['utilidad'], 0.0001);
    }

    public function test_los_totales_son_cero_sin_filas(): void
    {
        $totales = Proyecto::totalesBalanceReporte(collect());

        $this->assertSame([
            'total_ingresos' => 0.0,
            'gastos_operativos' => 0.0,
            'gastos_administrativos' => 0.0,
            'total_gastos' => 0.0,
            'utilidad' => 0.0,
        ], $totales);
    }
}
