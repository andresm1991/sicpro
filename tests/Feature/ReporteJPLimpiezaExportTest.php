<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
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

    /**
     * @dataProvider combinacionesRechazadas
     */
    public function test_una_combinacion_rechazada_no_consulta_la_base(string $formato, array $cuerpo): void
    {
        $this->actingAs($this->usuario());
        DB::enableQueryLog();
        DB::flushQueryLog();

        $response = $this->post(route('export.reporte.jp.limpieza', $formato), $cuerpo);

        $consultas = DB::getQueryLog();
        DB::disableQueryLog();

        $response->assertStatus(302);
        $this->assertNotEmpty(session('error'));
        $this->assertCount(0, $consultas, 'El rechazo tiene que resolverse antes de consultar la base.');
    }

    public static function combinacionesRechazadas(): array
    {
        return [
            'formato desconocido' => ['xml', ['tipo_reporte' => 'operativo']],
            'formato en mayusculas' => ['PDF', ['tipo_reporte' => 'operativo']],
            'excel de global' => ['excel', ['tipo_reporte' => 'global']],
            'excel de balance' => ['excel', ['tipo_reporte' => 'balance']],
            'tipo desconocido' => ['pdf', ['tipo_reporte' => 'no-existe']],
            'sin tipo de reporte' => ['pdf', []],
        ];
    }

    /**
     * @dataProvider tiposConSalidaPdf
     */
    public function test_los_reportes_con_salida_pdf_siguen_saliendo(string $tipo): void
    {
        $response = $this->actingAs($this->usuario())
            ->post(route('export.reporte.jp.limpieza', 'pdf'), ['tipo_reporte' => $tipo]);

        $response->assertStatus(200);
        $this->assertStringContainsString(
            'pdf',
            strtolower((string) $response->headers->get('content-type')),
            'El reporte se sigue sirviendo como PDF.'
        );
    }

    public static function tiposConSalidaPdf(): array
    {
        return [
            'operativo' => ['operativo'],
            'global' => ['global'],
            'balance' => ['balance'],
        ];
    }
}
