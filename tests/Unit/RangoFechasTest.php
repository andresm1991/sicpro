<?php

namespace Tests\Unit;

use App\Support\RangoFechas;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

/**
 * Parser unico del rango "m/d/Y - m/d/Y" que manda el date-range picker de los
 * reportes. Antes cada reporte hacia explode() + Carbon::createFromFormat a
 * mano, asi que cualquier basura en el input reventaba el reporte.
 */
class RangoFechasTest extends TestCase
{
    public function test_parsea_un_rango_valido_a_iso(): void
    {
        $this->assertSame(
            ['2025-03-12', '2025-03-30'],
            RangoFechas::parse('03/12/2025 - 03/30/2025')
        );
    }

    public function test_tolera_espacios_alrededor_del_rango(): void
    {
        $this->assertSame(
            ['2025-03-12', '2025-03-30'],
            RangoFechas::parse('  03/12/2025 - 03/30/2025  ')
        );
    }

    public function test_devuelve_null_cuando_no_vino_rango(): void
    {
        $this->assertNull(RangoFechas::parse(null));
        $this->assertNull(RangoFechas::parse(''));
    }

    public static function valoresInvalidos(): array
    {
        return [
            'texto sin separador' => ['basura'],
            'texto con separador' => ['basura - basura'],
            'formato iso' => ['2025-03-12'],
            'tres partes' => ['03/12/2025 - 03/30/2025 - 04/01/2025'],
            'mes y dia fuera de rango' => ['13/45/2025 - 03/30/2025'],
            'fecha en cero' => ['00/00/0000 - 00/00/0000'],
            'solo espacios' => ['   '],
            'separador sin fechas' => [' - '],
        ];
    }

    /**
     * @dataProvider valoresInvalidos
     */
    public function test_rechaza_un_rango_invalido_con_un_mensaje_claro(string $valor): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(RangoFechas::MENSAJE_INVALIDO);

        RangoFechas::parse($valor);
    }

    public function test_acepta_el_29_de_febrero_de_un_anio_bisiesto(): void
    {
        $this->assertSame(
            ['2024-02-29', '2024-03-30'],
            RangoFechas::parse('02/29/2024 - 03/30/2024')
        );
    }

    public function test_rechaza_un_valor_que_no_es_texto(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(RangoFechas::MENSAJE_INVALIDO);

        RangoFechas::parse(['03/12/2025 - 03/30/2025']);
    }

    public function test_requerido_exige_un_rango(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(RangoFechas::MENSAJE_INVALIDO);

        RangoFechas::requerido(null);
    }

    public function test_requerido_devuelve_el_rango_cuando_es_valido(): void
    {
        $this->assertSame(
            ['2025-03-12', '2025-03-30'],
            RangoFechas::requerido('03/12/2025 - 03/30/2025')
        );
    }

    public function test_valido_no_lanza_y_distingue_los_casos(): void
    {
        $this->assertTrue(RangoFechas::valido('03/12/2025 - 03/30/2025'));
        $this->assertTrue(RangoFechas::valido(null));
        $this->assertTrue(RangoFechas::valido(''));
        $this->assertFalse(RangoFechas::valido('basura'));
        $this->assertFalse(RangoFechas::valido('basura - basura'));
    }
}
