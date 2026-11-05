<?php

namespace Tests\Unit;

use App\Models\Adquisicion;
use Tests\TestCase;

/**
 * Totales del pie del reporte de adquisiciones de SICPRO.
 *
 * Los calculaba dos veces: una en PHP para el PDF y el Excel, y otra en el
 * navegador para el preview. Esta funcion es la unica fuente: los dos caminos
 * tienen que leer el mismo numero.
 *
 * Los importes que devuelven `Adquisicion::dataReporteAdquisiciones`,
 * `Contratista::filtroContratista` y `ManoObra::filtroManoObra` llegan ya
 * formateados ('$ 1,234.0000'), asi que la funcion tiene que parsearlos sin
 * perder precision.
 */
class TotalesReporteAdquisicionesTest extends TestCase
{
    public function test_suma_general_pagado_y_saldos_de_la_rama_contratistas(): void
    {
        $reporte = collect([
            ['total' => '$ 1,234.0000', 'total_pagado' => '$ 234.5000', 'saldo' => '$ 999.5000'],
            ['total' => '$ 2,000.0000', 'total_pagado' => '$ 0.0000', 'saldo' => '$ 2,000.0000'],
        ]);

        $totales = Adquisicion::totalesReporteAdquisiciones($reporte);

        $this->assertEqualsWithDelta(3234.0, $totales['total_general'], 0.0001);
        $this->assertEqualsWithDelta(234.5, $totales['total_pagado'], 0.0001);
        $this->assertEqualsWithDelta(2999.5, $totales['total_saldos'], 0.0001);
    }

    public function test_las_claves_ausentes_cuentan_como_cero(): void
    {
        $reporte = collect([
            ['total' => '$ 500.0000', 'cantidad' => 3],
            ['total' => '$ 250.0000', 'cantidad' => 2],
        ]);

        $totales = Adquisicion::totalesReporteAdquisiciones($reporte);

        $this->assertEqualsWithDelta(750.0, $totales['total_general'], 0.0001);
        $this->assertEqualsWithDelta(0.0, $totales['total_pagado'], 0.0001);
        $this->assertEqualsWithDelta(0.0, $totales['total_saldos'], 0.0001);
        $this->assertEqualsWithDelta(5.0, $totales['total_cantidades'], 0.0001);
    }

    public function test_acepta_valores_numericos_ademas_de_importes_formateados(): void
    {
        $reporte = collect([
            ['total' => 100.25, 'total_pagado' => 0, 'saldo' => 100.25],
            ['total' => '$ 0.0000', 'total_pagado' => '$ 0.0000', 'saldo' => '$ 0.0000'],
        ]);

        $totales = Adquisicion::totalesReporteAdquisiciones($reporte);

        $this->assertEqualsWithDelta(100.25, $totales['total_general'], 0.0001);
        $this->assertEqualsWithDelta(100.25, $totales['total_saldos'], 0.0001);
    }

    public function test_los_totales_son_cero_sin_filas(): void
    {
        $totales = Adquisicion::totalesReporteAdquisiciones(collect());

        $this->assertSame([
            'total_general' => 0.0,
            'total_pagado' => 0.0,
            'total_saldos' => 0.0,
            'total_cantidades' => 0.0,
        ], $totales);
    }
}
