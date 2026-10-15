<?php

namespace App\Exceptions;

use InvalidArgumentException;

/**
 * El rango de fechas que manda el date-range picker no es valido. Es un error
 * de entrada del usuario, no una falla del servidor: los reportes lo traducen
 * a un mensaje claro en lugar de responder 500.
 */
class RangoFechasInvalidoException extends InvalidArgumentException
{
}
