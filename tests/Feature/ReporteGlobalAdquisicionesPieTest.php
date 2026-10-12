<?php

namespace Tests\Feature;

use App\Models\Adquisicion;
use Tests\TestCase;

/**
 * Pie del reporte global de adquisiciones: lo que ve el usuario debe salir
 * de los totales calculados por el servidor, no de valores del navegador.
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

        $this->assertStringContainsString('SubTotal del proyecto: $3,000.0000', $html);
        $this->assertStringContainsString('Costos Indirectos: $333.3333', $html);
        $this->assertStringContainsString('$3,333.3333', $html);
    }

    public function test_sin_porcentaje_el_pie_solo_muestra_el_subtotal(): void
    {
        $data = $this->data();
        $totales = Adquisicion::totalesReporteGlobal($data, 0);

        $html = view('pdf.reporte_adquisiciones_global', compact('data', 'totales'))->render();

        $this->assertStringContainsString('SubTotal del proyecto: $3,000.0000', $html);
        $this->assertStringNotContainsString('Costos Indirectos', $html);
        $this->assertStringNotContainsString('Total general', $html);
    }
}
