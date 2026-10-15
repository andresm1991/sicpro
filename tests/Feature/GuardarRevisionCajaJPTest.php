<?php

namespace Tests\Feature;

use App\Models\User;
use App\Support\RangoFechas;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Revision de caja de JP Limpieza (trait GuardaRevisionCaja).
 *
 * Solo se cubre el camino de rechazo: el modelo JPLimpieza\RevisionCaja usa la
 * conexion mysql_jp_limpieza, que phpunit.xml no aisla, asi que un rango valido
 * escribiria en la base real. El camino feliz del trait esta cubierto por
 * GuardarRevisionCajaTest, que usa la conexion de testing.
 */
class GuardarRevisionCajaJPTest extends TestCase
{
    use RefreshDatabase;

    private const URL = '/jp-limpieza/reportes/guardar-revision-caja';

    public function test_rechaza_un_rango_invalido_con_mensaje_claro(): void
    {
        $usuario = User::factory()->create([
            'usuario' => 'usuario_revision_caja_jp_test',
            'correo' => 'usuario_revision_caja_jp_test@example.test',
        ]);

        $response = $this->actingAs($usuario)->post(self::URL, ['fechas' => 'basura - basura']);

        $response->assertOk()->assertJson(['success' => false]);
        $this->assertStringContainsString(
            RangoFechas::MENSAJE_INVALIDO,
            (string) $response->json('mensaje')
        );
    }
}
