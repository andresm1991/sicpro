<?php

namespace Tests\Feature;

use App\Models\Adquisicion;
use Tests\TestCase;

/**
 * Pie y totales por etapa del PDF del reporte global de adquisiciones.
 *
 * Lo que ve el usuario debe salir de los datos consultados (no de valores del
 * navegador) y con la misma base que el Reporte Balance de Gerencia: los
 * contratistas aportan lo PAGADO y la mano de obra aporta su campo 'pagado'.
 *
 * Reproduce el mismo conjunto de variables que pasa GenerarPdfController,
 * para que la vista no dependa de nada que el controlador no entregue.
 */
class ReporteGlobalAdquisicionesPieTest extends TestCase
{
    private function data(): array
    {
        return [
            'proyecto' => 'Proyecto de prueba',
            'subproyecto' => null,
            'data' => [
                'Etapa 1' => [
                    'contratista' => [[
                        'proveedor' => 'Proveedor',
                        'categoria' => 'Categoria',
                        'cantidad' => 1,
                        'total_contratado' => 1000.0,
                        'pagos' => 0.0,
                        'saldo' => 1000.0,
                    ]],
                    'mano_obra' => [],
                    'materiales_herramientas' => [[
                        'articulo' => 'Articulo',
                        'unidad_medida' => 'u',
                        'cantidad_total' => 1,
                        'total' => 2000.0,
                    ]],
                    'servicios' => [],
                ],
            ],
        ];
    }

    public function test_el_pie_muestra_los_totales_calculados_por_el_servidor(): void
    {
        $data = $this->data();
        $totales = Adquisicion::totalesReporteGlobal($data, 10);

        $html = view('pdf.reporte_adquisiciones_global', compact('data', 'totales'))->render();

        // El contratista no aporta (pagos en 0): 2000 / (1 - 0,10) = 2222,2222
        $this->assertStringContainsString('SubTotal del proyecto: $2,000.0000', $html);
        $this->assertStringContainsString('Costos Indirectos: $222.2222', $html);
        $this->assertStringContainsString('$2,222.2222', $html);
    }

    public function test_sin_porcentaje_el_pie_solo_muestra_el_subtotal(): void
    {
        $data = $this->data();
        $totales = Adquisicion::totalesReporteGlobal($data, 0);

        $html = view('pdf.reporte_adquisiciones_global', compact('data', 'totales'))->render();

        $this->assertStringContainsString('SubTotal del proyecto: $2,000.0000', $html);
        $this->assertStringNotContainsString('Costos Indirectos', $html);
        $this->assertStringNotContainsString('Total general', $html);
    }

    public function test_las_etapas_del_pdf_suman_los_pagos_y_la_mano_de_obra_pagada(): void
    {
        $data = $this->data();
        // Contratista: contratado 1000, pagado 400. Mano de obra: 500 pagados.
        $data['data']['Etapa 1']['contratista'][0]['pagos'] = 400.0;
        $data['data']['Etapa 1']['contratista'][0]['saldo'] = 600.0;
        $data['data']['Etapa 1']['mano_obra'] = [[
            'id' => 1,
            'periodo' => 'Del Lun de Enero al Dom de Enero 2025',
            'pagado' => 500.0,
        ]];

        $totales = Adquisicion::totalesReporteGlobal($data, 0);
        $html = view('pdf.reporte_adquisiciones_global', compact('data', 'totales'))->render();

        // 400 + 500 + 2000 = 2900; con lo contratado (1000) serian 3500.
        $this->assertStringContainsString('Total Etapa Etapa 1: $2,900.0000', $html);
        $this->assertStringNotContainsString('Total Etapa Etapa 1: $3,500.0000', $html);

        // La tabla de mano de obra muestra el periodo y lo pagado, no 0.
        $this->assertStringContainsString('Del Lun de Enero al Dom de Enero 2025', $html);
        $this->assertStringContainsString('Total Mano de Obra:</strong></td>', $html);
        $this->assertStringContainsString('$500.0000', $html);
    }
}
