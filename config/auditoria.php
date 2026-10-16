<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Retencion de la tabla logs (auditoria)
    |--------------------------------------------------------------------------
    |
    | Cantidad de dias que se conservan los registros de la tabla `logs`.
    | El comando `logs:purgar` (programado a diario) elimina los registros
    | mas viejos que este valor. Se puede sobreescribir por ejecucion con
    | la opcion --dias= del comando.
    |
    */

    'retencion_dias' => (int) env('AUDITORIA_RETENCION_DIAS', 365),

];
