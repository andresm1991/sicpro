<?php

namespace Tests\Feature;

use App\Models\User;
use App\Support\RangoFechas;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Revision de caja (ReporteriaController::guardarRevisionCaja).
 *
 * El metodo validaba 'fechas.*' sobre un string (regla que nunca se aplicaba)
 * y dejaba la validacion real en manos del cast 'date' del modelo: una fecha
 * inexistente como 02/30/2025 se guardaba en silencio corrida al 02/03, y el
 * error de una basura era el mensaje crudo de Carbon.
 */
class GuardarRevisionCajaTest extends TestCase
{
    use RefreshDatabase;

    private const URL = '/reportes/guardar-revision-caja';

    private function usuario(): User
    {
        return User::factory()->create(['usuario' => 'usuario_revision_caja_test']);
    }

    public function test_guarda_la_revision_con_fechas_validas_en_formato_de_fecha(): void
    {
        $response = $this->actingAs($this->usuario())->post(self::URL, [
            'fechas' => '03/12/2025 - 03/30/2025',
            'observaciones' => 'Revision de prueba',
        ]);

        $response->assertOk()->assertJson(['success' => true]);

        $this->assertDatabaseHas('revision_cajas', [
            'fecha_revision_inicio' => '2025-03-12',
            'fecha_revision_fin' => '2025-03-30',
            'observaciones' => 'Revision de prueba',
        ]);
    }

    public static function rangosInvalidos(): array
    {
        return [
            'sin separador' => ['basura'],
            'con separador' => ['basura - basura'],
            'dia fuera de rango' => ['13/45/2025 - 03/30/2025'],
            'dia inexistente' => ['02/30/2025 - 03/30/2025'],
        ];
    }

    /**
     * @dataProvider rangosInvalidos
     */
    public function test_rechaza_un_rango_invalido_con_mensaje_claro_y_sin_guardar_nada(string $fechas): void
    {
        $response = $this->actingAs($this->usuario())->post(self::URL, [
            'fechas' => $fechas,
            'observaciones' => 'Revision de prueba',
        ]);

        $response->assertOk()->assertJson(['success' => false]);
        $this->assertStringContainsString(
            RangoFechas::MENSAJE_INVALIDO,
            (string) $response->json('mensaje'),
            'El usuario tiene que ver un mensaje claro, no el error crudo del parser.'
        );
        $this->assertDatabaseCount('revision_cajas', 0);
    }

    public function test_exige_el_rango_de_fechas(): void
    {
        $response = $this->actingAs($this->usuario())->post(self::URL, [
            'observaciones' => 'Revision de prueba',
        ]);

        $response->assertOk()->assertJson(['success' => false]);
        $this->assertDatabaseCount('revision_cajas', 0);
    }
}
