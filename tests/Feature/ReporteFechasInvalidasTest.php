<?php

namespace Tests\Feature;

use App\Models\User;
use App\Support\RangoFechas;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Rango de fechas invalido en los reportes.
 *
 * Todo el modulo hacia explode(' - ', $fechas) + Carbon::createFromFormat a
 * mano: una basura reventaba el reporte con un 500 y una fecha inexistente
 * como 13/45/2025 se corria en silencio a otro rango.
 */
class ReporteFechasInvalidasTest extends TestCase
{
    use RefreshDatabase;

    /**
     * URI literal: el nombre `reporte.caja` esta tomado por el GET
     * /reportes/flujo-de-efectivo de reporteria.php (colision preexistente),
     * asi que route() resuelve a la ruta equivocada.
     */
    private const PDF_CAJA = '/generar-pdf/reporte-caja/pdf';

    private function usuario(): User
    {
        return User::factory()->create(['usuario' => 'usuario_fechas_invalidas_test']);
    }

    public function test_el_pdf_de_caja_con_rango_invalido_responde_con_error_claro(): void
    {
        $response = $this->actingAs($this->usuario())
            ->post(self::PDF_CAJA, ['fechas' => 'basura - basura']);

        $response->assertStatus(302);
        $this->assertStringContainsString(
            RangoFechas::MENSAJE_INVALIDO,
            (string) session('error'),
            'El reporte tiene que explicar que el rango es invalido, no tirar un 500.'
        );
    }

    public function test_el_pdf_de_caja_sin_rango_responde_con_error_claro(): void
    {
        $response = $this->actingAs($this->usuario())->post(self::PDF_CAJA);

        $response->assertStatus(302);
        $this->assertNotEmpty(session('error'));
    }

    public function test_el_preview_de_caja_con_rango_invalido_responde_con_mensaje_claro(): void
    {
        $response = $this->actingAs($this->usuario())
            ->post('/reportes/visulizar-reporte-caja', ['fechas' => 'basura - basura']);

        $response->assertOk()->assertJson(['success' => false]);
        $this->assertStringContainsString(
            RangoFechas::MENSAJE_INVALIDO,
            (string) $response->json('mensaje')
        );
    }

    public function test_el_pdf_de_caja_con_rango_valido_sigue_generando_el_pdf(): void
    {
        $response = $this->actingAs($this->usuario())
            ->post(self::PDF_CAJA, ['fechas' => '03/12/2025 - 03/30/2025']);

        $response->assertOk();
        $this->assertStringContainsString('pdf', (string) $response->headers->get('content-type'));
        $this->assertNull(session('error'));
    }
}
