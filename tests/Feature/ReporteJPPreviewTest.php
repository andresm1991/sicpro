<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Preview del reporte de adquisiciones de JP Limpieza.
 *
 * Red de seguridad del cableado de los totales: el preview y el PDF ahora
 * comparten las mismas funciones (granTotalReportePorArticulo / totalesBalance),
 * asi que un error al engancharlas tiene que verse como success:false.
 */
class ReporteJPPreviewTest extends TestCase
{
    use RefreshDatabase;

    private const URL = '/jp-limpieza/reportes/visualizar-reporte-adquisiciones';

    public static function tiposDeReporte(): array
    {
        return [
            'operativo' => ['operativo'],
            'global' => ['global'],
            'balance' => ['balance'],
        ];
    }

    /**
     * @dataProvider tiposDeReporte
     */
    public function test_el_preview_de_adquisiciones_responde_ok(string $tipo): void
    {
        $usuario = User::factory()->create([
            'usuario' => 'usuario_preview_jp_test',
            'correo' => 'usuario_preview_jp_test@example.test',
        ]);

        $response = $this->actingAs($usuario)
            ->withHeaders(['X-Requested-With' => 'XMLHttpRequest'])
            ->post(self::URL, ['tipo_reporte' => $tipo]);

        $response->assertOk()->assertJson(['success' => true]);
        $this->assertArrayHasKey('result', $response->json());
    }
}
