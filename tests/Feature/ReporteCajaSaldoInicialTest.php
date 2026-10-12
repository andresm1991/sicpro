<?php

namespace Tests\Feature;

use App\Models\MovimientoCaja;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Tests\TestCase;

/**
 * El saldo inicial del reporte de caja es el acumulado hasta el dia anterior
 * al inicio del rango, por lo que su etiqueta debe referenciar ese dia
 * (inicio - 1) y no dos dias antes.
 */
class ReporteCajaSaldoInicialTest extends TestCase
{
    use RefreshDatabase;

    public function test_el_saldo_inicial_se_etiqueta_con_el_dia_anterior_al_inicio_del_rango(): void
    {
        $request = new Request();
        $request->merge(['fechas' => '01/15/2025 - 01/20/2025']);

        $resultado = MovimientoCaja::dataReporteCaja($request);

        $this->assertStringContainsString('14 de enero de 2025', $resultado['fecha_saldo_inicio']);
        $this->assertStringNotContainsString('13 de enero de 2025', $resultado['fecha_saldo_inicio']);
        $this->assertStringContainsString('14 de enero de 2025', $resultado['fecha_reporte']);
    }
}
