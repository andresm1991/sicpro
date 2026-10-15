<?php

namespace Tests\Feature;

use App\Models\CatalogoDato;
use App\Models\Proyecto;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * PDF de balance Prime (GenerarPdfController::exportarReporteBalancePrime).
 *
 * La vista del pie (gerencia.reportes.tabla_balance) usa los cinco totales,
 * pero este camino le pasaba solo tres: con el balance no vacio el PDF moria
 * con "Undefined variable $totalGatosDirectosGeneral".
 */
class ExportarBalancePrimeTest extends TestCase
{
    use RefreshDatabase;

    private const URL = '/gerencia/reporte/exportar/balance_proyecto';

    private function proyecto(): Proyecto
    {
        $catalogo = CatalogoDato::create([
            'descripcion' => 'Catalogo de prueba',
            'detalle' => '',
            'slug' => 'catalogo.prueba.balance',
            'padre_id' => null,
            'activo' => true,
        ]);

        return Proyecto::create([
            'catalogo_proyecto_id' => $catalogo->id,
            'nombre_proyecto' => 'Proyecto Balance Prime',
            'nombre_propietario' => 'Propietario de prueba',
            'telefono' => '0991000000',
            'correo' => 'balance.prime@example.test',
            'tipo_proyecto_id' => $catalogo->id,
            'estado_id' => $catalogo->id,
            'portada' => '',
        ]);
    }

    public function test_el_pdf_de_balance_prime_se_genera_con_filas_de_balance(): void
    {
        $proyecto = $this->proyecto();
        $usuario = User::factory()->create([
            'usuario' => 'usuario_balance_prime_test',
            'correo' => 'usuario_balance_prime_test@example.test',
        ]);

        $response = $this->actingAs($usuario)->post(self::URL, [
            'tipo_reporte' => 'balance_proyecto',
            'proyecto' => $proyecto->id,
        ]);

        $response->assertOk();
        $this->assertStringContainsString('pdf', (string) $response->headers->get('content-type'));
    }
}
