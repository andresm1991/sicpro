<?php

namespace Tests\Unit;

use App\Models\JPLimpieza\Adquisicion;
use Tests\TestCase;

/**
 * El gran total del reporte operativo de adquisiciones de JP Limpieza debe
 * salir siempre de los campos numéricos de cada categoría.
 *
 * Sumar los campos formateados (total_general_formatted = number_format(...))
 * trunca el valor, porque PHP convierte "1,500.0000" a 1.5 antes de sumar y
 * ademas emite un E_WARNING que Laravel transforma en ErrorException.
 */
class GranTotalAdquisicionesJPTest extends TestCase
{
    public function test_suma_los_totales_numericos_de_materiales_y_servicios(): void
    {
        $reporte = [
            'Materiales y Herramientas' => [
                ['total_general' => 1500.0, 'total_general_formatted' => '1,500.0000'],
                ['total_general' => 1500.0, 'total_general_formatted' => '1,500.0000'],
            ],
            'Servicios' => [
                ['total_general' => 1250.5, 'total_general_formatted' => '1,250.5000'],
            ],
        ];

        $this->assertEqualsWithDelta(4250.5, Adquisicion::granTotalReporte($reporte), 0.0001);
    }

    public function test_incluye_el_total_contratado_de_contratistas(): void
    {
        $reporte = [
            'Materiales y Herramientas' => [
                ['total_general' => 100.0, 'total_general_formatted' => '100.0000'],
            ],
            'Contratistas' => [
                ['total_contratado' => 2000.0],
            ],
        ];

        $this->assertEqualsWithDelta(2100.0, Adquisicion::granTotalReporte($reporte), 0.0001);
    }

    public function test_incluye_el_total_a_recibir_de_mano_de_obra(): void
    {
        $reporte = [
            'Mano de Obra' => [
                ['total_recibir' => 900.0],
            ],
            'Servicios' => [
                ['total_general' => 50.0, 'total_general_formatted' => '50.0000'],
            ],
        ];

        $this->assertEqualsWithDelta(950.0, Adquisicion::granTotalReporte($reporte), 0.0001);
    }

    public function test_un_reporte_sin_datos_devuelve_gran_total_cero(): void
    {
        $this->assertEqualsWithDelta(0.0, Adquisicion::granTotalReporte([]), 0.0001);
    }
}
