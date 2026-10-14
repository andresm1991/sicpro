<?php

namespace Tests\Feature;

use App\Models\CatalogoDato;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\View;
use Tests\TestCase;

/**
 * Exportacion de los reportes de JP Limpieza (ruta export.reporte.jp.limpieza).
 *
 * Los reportes global y balance solo tienen salida en PDF. El excel ya esta
 * bloqueado en el JS de la pantalla, pero la ruta no tenia rama para ese caso
 * y respondia 200 con el cuerpo vacio.
 */
class ReporteJPLimpiezaExportTest extends TestCase
{
    use RefreshDatabase;

    private function usuario(): User
    {
        return User::factory()->create(['usuario' => 'usuario_export_jp_test']);
    }

    /**
     * @dataProvider tiposSinSalidaExcel
     */
    public function test_un_tipo_sin_salida_excel_responde_con_error_claro(string $tipo): void
    {
        $response = $this->actingAs($this->usuario())
            ->post(route('export.reporte.jp.limpieza', 'excel'), ['tipo_reporte' => $tipo]);

        $response->assertStatus(302);
        $this->assertNotEmpty(
            session('error'),
            'La ruta tiene que informar por que no exporta, no devolver un cuerpo vacio.'
        );
    }

    public static function tiposSinSalidaExcel(): array
    {
        return [
            'global' => ['global'],
            'balance' => ['balance'],
        ];
    }

    public function test_el_excel_del_reporte_operativo_sigue_descargando_el_archivo(): void
    {
        $response = $this->actingAs($this->usuario())
            ->post(route('export.reporte.jp.limpieza', 'excel'), ['tipo_reporte' => 'operativo']);

        $response->assertStatus(200);
        $this->assertStringContainsString(
            '.xlsx',
            (string) $response->headers->get('content-disposition'),
            'El excel del reporte operativo se sigue descargando como archivo.'
        );
        $this->assertNull(session('error'));
    }
}
