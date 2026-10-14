<?php

namespace Tests\Feature;

use App\Models\CatalogoDato;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\View;
use Tests\TestCase;

/**
 * Watermark del reporte global de adquisiciones (SICPRO).
 *
 * La vista lee la empresa para el watermark, pero el controlador que la arma
 * (GenerarPdfController::reportAdquisiciones, rama global) no se la pasaba ni
 * existia el campo que la vista consultaba, asi que el watermark caia siempre
 * al literal 'SICPRO'.
 */
class WatermarkReporteGlobalTest extends TestCase
{
    use RefreshDatabase;

    private function usuario(): User
    {
        return User::factory()->create(['usuario' => 'usuario_watermark_test']);
    }

    private function empresa(): CatalogoDato
    {
        $padre = CatalogoDato::create([
            'descripcion' => 'Informacion General',
            'detalle' => '',
            'slug' => 'informacion.general',
            'padre_id' => null,
            'activo' => true,
        ]);

        return CatalogoDato::create([
            'descripcion' => 'Nombre de la Empresa',
            'detalle' => 'PrimeJP Construcciones',
            'slug' => 'informacion.general.nombre.empresa',
            'padre_id' => $padre->id,
            'activo' => true,
        ]);
    }

    private function datosDelReporte(): array
    {
        return ['data' => [], 'subproyecto' => null, 'proyecto' => null];
    }

    public function test_el_controlador_le_pasa_la_empresa_al_reporte_global(): void
    {
        $this->empresa();

        $datosDeLaVista = null;
        View::composer('pdf.reporte_adquisiciones_global', function ($view) use (&$datosDeLaVista) {
            $datosDeLaVista = $view->getData();
        });

        $this->actingAs($this->usuario())
            ->post(route('pdf.reporte.adquisiciones', 'pdf'), ['tipo_reporte' => 'global'])
            ->assertStatus(200);

        $this->assertIsArray($datosDeLaVista, 'La vista del reporte global tiene que haberse renderizado.');
        $this->assertArrayHasKey('empresa', $datosDeLaVista);
        $this->assertSame('PrimeJP Construcciones', $datosDeLaVista['empresa']->detalle);
    }

    public function test_el_watermark_muestra_el_nombre_de_la_empresa_recibida(): void
    {
        $html = view('pdf.reporte_adquisiciones_global', [
            'data' => $this->datosDelReporte(),
            'totales' => ['subtotal' => 0.0, 'costos_indirectos' => 0.0, 'total_general' => 0.0],
            'empresa' => $this->empresa(),
        ])->render();

        $this->assertStringContainsString('PrimeJP Construcciones', $html);
    }
}
