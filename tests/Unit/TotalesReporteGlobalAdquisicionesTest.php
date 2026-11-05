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
 *
 * La base de cada categoria es la misma que usa el Reporte Balance de Gerencia
 * (y la misma que muestra la pantalla del reporte): los contratistas aportan
 * lo PAGADO y la mano de obra aporta el campo 'pagado' de sus filas.
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
                    'mano_obra' => [['periodo' => 'Del Lun de Enero al Dom de Enero 2025', 'pagado' => 500.0]],
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
        // 400 (pagos) + 500 (pagado) + 300,5 + 199,5 = 1400  y  1000  => 2400
        $totales = Adquisicion::totalesReporteGlobal($this->data(), 0);

        $this->assertEqualsWithDelta(2400.0, $totales['subtotal'], 0.0001);
    }

    public function test_el_contratista_aporta_por_pagos_y_no_por_lo_contratado(): void
    {
        $data = $this->data();
        // Un unico contratista: contratado 1000, pagado 400.
        $data['data'] = [
            'Etapa 1' => [
                'contratista' => [['total_contratado' => 1000.0, 'pagos' => 400.0, 'saldo' => 600.0]],
                'mano_obra' => [],
                'materiales_herramientas' => [],
                'servicios' => [],
            ],
        ];

        $totales = Adquisicion::totalesReporteGlobal($data, 0);

        $this->assertEqualsWithDelta(400.0, $totales['subtotal'], 0.0001);
    }

    public function test_la_mano_de_obra_aporta_por_el_campo_pagado(): void
    {
        $data = $this->data();
        // Las filas de mano de obra exponen 'pagado'; no tienen clave 'total'.
        $data['data'] = [
            'Etapa 1' => [
                'contratista' => [],
                'mano_obra' => [['periodo' => 'Del Lun de Enero al Dom de Enero 2025', 'pagado' => 500.0]],
                'materiales_herramientas' => [],
                'servicios' => [],
            ],
        ];

        $totales = Adquisicion::totalesReporteGlobal($data, 0);

        $this->assertEqualsWithDelta(500.0, $totales['subtotal'], 0.0001);
    }

    public function test_sin_porcentaje_no_hay_costos_indirectos_y_el_total_es_el_subtotal(): void
    {
        $totales = Adquisicion::totalesReporteGlobal($this->data(), 0);

        $this->assertEqualsWithDelta(0.0, $totales['costos_indirectos'], 0.0001);
        $this->assertEqualsWithDelta(2400.0, $totales['total_general'], 0.0001);
    }

    public function test_el_porcentaje_se_aplica_sobre_el_total_final(): void
    {
        // 2400 / (1 - 0,10) = 2666,6667
        $totales = Adquisicion::totalesReporteGlobal($this->data(), 10);

        $this->assertEqualsWithDelta(2666.6667, $totales['total_general'], 0.0001);
        $this->assertEqualsWithDelta(266.6667, $totales['costos_indirectos'], 0.0001);
    }

    public function test_un_porcentaje_de_100_no_rompe_el_calculo(): void
    {
        // La formula divide por (1 - p/100): con 100 daria division por cero.
        $totales = Adquisicion::totalesReporteGlobal($this->data(), 100);

        $this->assertIsFloat($totales['total_general']);
        $this->assertGreaterThan($totales['subtotal'], $totales['total_general']);
    }

    /**
     * El desglose es lo que la vista PDF y el preview del navegador sumaban por
     * su cuenta. Ahora sale de aca y los dos solo lo muestran.
     */
    public function test_devuelve_el_desglose_por_etapa_y_categoria(): void
    {
        $totales = Adquisicion::totalesReporteGlobal($this->data(), 0);
        $etapa1 = $totales['por_etapa']['Etapa 1'];

        $this->assertEqualsWithDelta(1000.0, $etapa1['contratistas'], 0.0001);
        $this->assertEqualsWithDelta(400.0, $etapa1['pagos'], 0.0001);
        $this->assertEqualsWithDelta(600.0, $etapa1['saldos'], 0.0001);
        $this->assertEqualsWithDelta(500.0, $etapa1['mano_obra'], 0.0001);
        $this->assertEqualsWithDelta(300.5, $etapa1['materiales'], 0.0001);
        $this->assertEqualsWithDelta(199.5, $etapa1['servicios'], 0.0001);
        // 400 + 500 + 300,5 + 199,5
        $this->assertEqualsWithDelta(1400.0, $etapa1['total'], 0.0001);

        $this->assertEqualsWithDelta(1000.0, $totales['por_etapa']['Etapa 2']['total'], 0.0001);
    }

    public function test_el_desglose_por_etapa_suma_lo_mismo_que_el_subtotal(): void
    {
        $totales = Adquisicion::totalesReporteGlobal($this->data(), 10);
        $sumaEtapas = array_sum(array_column($totales['por_etapa'], 'total'));

        $this->assertEqualsWithDelta($totales['subtotal'], $sumaEtapas, 0.0001);
    }

    public function test_el_total_de_productos_suma_materiales_y_servicios(): void
    {
        $data = [
            'data' => [
                'Etapa 1' => [
                    'materiales_herramientas' => [['total' => 10.0, 'cantidad_total' => 4]],
                    'servicios' => [['total' => 20.0, 'cantidad_total' => 2.5]],
                ],
            ],
        ];

        $totales = Adquisicion::totalesReporteGlobal($data, 0);

        $this->assertEqualsWithDelta(6.5, $totales['total_productos'], 0.0001);
    }

    public function test_sin_etapas_el_desglose_queda_vacio(): void
    {
        $totales = Adquisicion::totalesReporteGlobal(['data' => []], 0);

        $this->assertSame([], $totales['por_etapa']);
        $this->assertSame(0.0, $totales['total_productos']);
    }
}
