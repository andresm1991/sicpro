<?php

namespace Tests\Feature;

use App\Models\CatalogoDato;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Tipo de reporte inexistente en adquisiciones y solicitudes.
 *
 * Las dos ramas hacian CatalogoDato::find($tipo)->slug / ->descripcion sin
 * comprobar el resultado: un tipo que no existe (o que no se envio) tiraba
 * "Attempt to read property on null" y respondia 500.
 */
class ReporteTipoInvalidoTest extends TestCase
{
    use RefreshDatabase;

    private function usuario(): User
    {
        return User::factory()->create([
            'usuario' => 'usuario_tipo_invalido_test',
            'correo' => 'usuario_tipo_invalido_test@example.test',
        ]);
    }

    public function test_el_pdf_de_adquisiciones_con_tipo_inexistente_responde_con_error_claro(): void
    {
        $response = $this->actingAs($this->usuario())
            ->post(route('pdf.reporte.adquisiciones', 'pdf'), [
                'tipo_reporte' => 'operativo',
                'tipo' => 999999,
            ]);

        $response->assertStatus(302);
        $this->assertNotEmpty(
            session('error'),
            'El reporte tiene que explicar que el tipo no es valido, no tirar un 500.'
        );
    }

    public function test_el_pdf_de_adquisiciones_con_tipo_valido_sigue_generando_el_pdf(): void
    {
        $tipo = CatalogoDato::create([
            'descripcion' => 'Servicios',
            'detalle' => '',
            'slug' => 'servicios',
            'padre_id' => null,
            'activo' => true,
        ]);

        $response = $this->actingAs($this->usuario())
            ->post(route('pdf.reporte.adquisiciones', 'pdf'), [
                'tipo_reporte' => 'operativo',
                'tipo' => $tipo->id,
            ]);

        $response->assertOk();
        $this->assertStringContainsString('pdf', (string) $response->headers->get('content-type'));
        $this->assertNull(session('error'));
    }

    public function test_el_pdf_de_solicitudes_con_tipo_inexistente_responde_con_error_claro(): void
    {
        $response = $this->actingAs($this->usuario())
            ->post(route('pdf.reporte.solicitudes', 'pdf'), ['tipo_solicitud' => 999999]);

        $response->assertStatus(302);
        $this->assertNotEmpty(session('error'));
    }

    public function test_el_preview_de_adquisiciones_con_tipo_inexistente_responde_con_mensaje_claro(): void
    {
        $response = $this->actingAs($this->usuario())
            ->post('/reportes/visulizar-reporte-adquisiciones', [
                'tipo_reporte' => 'operativo',
                'tipo' => 999999,
            ]);

        $response->assertOk()->assertJson(['success' => false]);
        $this->assertStringNotContainsString(
            'Attempt to read property',
            (string) $response->json('mensaje'),
            'El usuario no tiene que ver el error crudo de PHP.'
        );
        $this->assertNotEmpty($response->json('mensaje'));
    }
}
