<?php

namespace Tests\Feature;

use App\Models\User;
use App\Support\RangoFechas;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Revision de caja de JP Limpieza (trait GuardaRevisionCaja).
 *
 * Solo se cubre el camino de rechazo: este test no transacciona la conexion
 * mysql_jp_limpieza (no declara connectionsToTransact), asi que un rango valido
 * dejaria la fila escrita en la base de test. La conexion ya esta aislada por
 * DB_JPLIMPIEZA_DATABASE en phpunit.xml; para tests que si escriben en JP, ver
 * NumeroAdquisicionJPLimpiezaTest, que la transacciona y ademas verifica en
 * setUp que apunte a una base de testing. El camino feliz del trait esta
 * cubierto por GuardarRevisionCajaTest, que usa la conexion de testing.
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
