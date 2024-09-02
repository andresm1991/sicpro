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
            'context' => $context,
        ]);
    }
}
