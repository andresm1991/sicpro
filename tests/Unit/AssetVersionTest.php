<?php

namespace Tests\Unit;

use Illuminate\Support\Facades\File;
use Tests\TestCase;

/**
 * Version de los assets en el query string.
 *
 * Las vistas usaban `?v=` + config('app.version'), un literal que hay que
 * acordarse de subir a mano: si no cambia, el navegador sirve el JS viejo desde
 * su cache y el usuario sigue viendo el calculo anterior de un reporte.
 */
class AssetVersionTest extends TestCase
{
    private const ARCHIVO = 'js/__asset_version_test.js';

    protected function tearDown(): void
    {
        $ruta = public_path(self::ARCHIVO);
        if (is_file($ruta)) {
            unlink($ruta);
        }

        parent::tearDown();
    }

    public function test_la_url_lleva_la_fecha_de_modificacion_del_archivo(): void
    {
        $ruta = public_path(self::ARCHIVO);
        File::ensureDirectoryExists(dirname($ruta));
        file_put_contents($ruta, '// version de prueba');
        touch($ruta, 1700000000);

        $this->assertSame(
            asset(self::ARCHIVO) . '?v=1700000000',
            asset_version(self::ARCHIVO)
        );
    }

    public function test_un_archivo_inexistente_usa_la_version_de_configuracion(): void
    {
        $this->assertSame(
            asset('js/no-existe-jamas.js') . '?v=' . config('app.version'),
            asset_version('js/no-existe-jamas.js')
        );
    }

    public function test_ninguna_vista_versiona_con_el_literal_de_configuracion(): void
    {
        $culpables = [];

        foreach (File::allFiles(resource_path('views')) as $archivo) {
            if (str_contains($archivo->getContents(), "config('app.version")) {
                $culpables[] = str_replace(resource_path('views') . DIRECTORY_SEPARATOR, '', $archivo->getPathname());
            }
        }

        $this->assertSame([], $culpables, 'Toda vista tiene que versionar por asset_version() para que el navegador no sirva el asset viejo.');
    }

    public function test_la_version_sale_del_archivo_publico_y_no_del_recurso(): void
    {
        $publico = public_path(self::ARCHIVO);
        $recurso = resource_path(self::ARCHIVO);
        File::ensureDirectoryExists(dirname($publico));
        File::ensureDirectoryExists(dirname($recurso));
        file_put_contents($publico, '// publico');
        file_put_contents($recurso, '// recurso');
        touch($publico, 1700000123);
        touch($recurso, 1600000000);

        try {
            // Lo que se sirve es la copia de public/, asi que esa es la version.
            $this->assertSame(
                asset(self::ARCHIVO) . '?v=1700000123',
                asset_version(self::ARCHIVO)
            );
        } finally {
            @unlink($publico);
            @unlink($recurso);
        }
    }

    public function test_las_vistas_no_tienen_restos_del_versionado_viejo(): void
    {
        $culpables = [];

        foreach (File::allFiles(resource_path('views')) as $archivo) {
            $contenido = $archivo->getContents();
            $nombre = $archivo->getRelativePathname();

            if (preg_match('/asset\([^)]*\?v=/', $contenido)) {
                $culpables[] = $nombre . ' (asset con ?v= dentro)';
            }
            if (preg_match('/\?v=.*\?v=/', $contenido)) {
                $culpables[] = $nombre . ' (doble ?v=)';
            }
            if (preg_match('/asset_version\([^)\n]*$/m', $contenido)) {
                $culpables[] = $nombre . ' (asset_version sin cerrar)';
            }
        }

        $this->assertSame([], $culpables, 'El reemplazo masivo de los 37 tags no debe dejar restos.');
    }
}
