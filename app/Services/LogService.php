<?php

namespace App\Services;

use App\Models\Log;

class LogService
{
    public static function log($level, $message, $context = [])
    {
        Log::create([
            'level' => $level,
            'message' => $message,
            'context' => array_merge(['user_id' => auth()->id()], $context),
        ]);
    }

    /**
     * Registro de auditoria: guarda actor, IP, entidad y valores antes/despues
     * ademas del level/message/context que ya usa log().
     *
     * En CLI/cola (sin request) no hay IP: se guarda null sin reventar.
     */
    public static function auditar(string $accion, string $entidad, ?int $entidadId, string $mensaje, array $antes = [], array $despues = [], string $level = 'info'): void
    {
        $userId = auth()->id();

        $ip = null;
        try {
            if (app()->bound('request')) {
                $ip = request()->ip();
            }
        } catch (\Throwable $e) {
            $ip = null;
        }

        Log::create([
            'level' => $level,
            'message' => $mensaje,
            'context' => ['user_id' => $userId],
            'entidad' => $entidad,
            'entidad_id' => $entidadId,
            'accion' => $accion,
            'valores_antes' => $antes,
            'valores_despues' => $despues,
            'user_id' => $userId,
            'ip' => $ip,
        ]);
    }
}
