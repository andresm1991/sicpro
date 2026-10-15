<?php

namespace App\Support;

use App\Exceptions\RangoFechasInvalidoException;
use Carbon\Carbon;

/**
 * Parser unico del rango "m/d/Y - m/d/Y" que mandan los reportes.
 *
 * Antes cada reporte repetia explode(' - ') + Carbon::createFromFormat a mano:
 * con basura en el input el reporte reventaba con un 500 y una fecha
 * inexistente como 13/45/2025 se corria en silencio a otro rango
 * (Carbon::createFromFormat no valida el calendario, solo el formato).
 */
class RangoFechas
{
    public const FORMATO = 'm/d/Y';

    public const MENSAJE_INVALIDO = 'El rango de fechas enviado no es válido.';

    /**
     * Partes crudas ya validadas, tal como las manda el date-range picker.
     *
     * @return array{0:string,1:string}|null null cuando no vino rango
     */
    public static function partes($valor): ?array
    {
        if ($valor === null || $valor === '') {
            return null;
        }

        if (!is_string($valor)) {
            throw new RangoFechasInvalidoException(self::MENSAJE_INVALIDO);
        }

        $partes = array_map('trim', explode(' - ', $valor));

        if (count($partes) !== 2
            || !self::fechaValida($partes[0])
            || !self::fechaValida($partes[1])) {
            throw new RangoFechasInvalidoException(self::MENSAJE_INVALIDO);
        }

        return $partes;
    }

    /**
     * hasFormat solo mira la forma ("m/d/Y"), no el calendario: 02/30/2025 la
     * pasa y Carbon::createFromFormat la corre en silencio al 02/03. checkdate
     * cierra ese agujero.
     */
    private static function fechaValida(string $valor): bool
    {
        if (!Carbon::hasFormat($valor, self::FORMATO)) {
            return false;
        }

        [$mes, $dia, $anio] = array_map('intval', explode('/', $valor));

        return checkdate($mes, $dia, (int) $anio);
    }

    /**
     * Rango listo para comparar contra columnas date.
     *
     * @return array{0:string,1:string}|null null cuando no vino rango
     */
    public static function parse($valor): ?array
    {
        $partes = self::partes($valor);

        if ($partes === null) {
            return null;
        }

        return [
            Carbon::createFromFormat(self::FORMATO, $partes[0])->format('Y-m-d'),
            Carbon::createFromFormat(self::FORMATO, $partes[1])->format('Y-m-d'),
        ];
    }

    /**
     * Rango obligatorio: los reportes que no pueden funcionar sin fechas
     * (caja) lo usan para fallar con el mismo mensaje claro.
     *
     * @return array{0:string,1:string}
     */
    public static function requerido($valor): array
    {
        $rango = self::parse($valor);

        if ($rango === null) {
            throw new RangoFechasInvalidoException(self::MENSAJE_INVALIDO);
        }

        return $rango;
    }

    public static function valido($valor): bool
    {
        try {
            self::partes($valor);

            return true;
        } catch (RangoFechasInvalidoException $e) {
            return false;
        }
    }
}
