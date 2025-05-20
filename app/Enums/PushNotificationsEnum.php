<?php

namespace App\Enums;

enum PushNotificationsEnum: string
{
    case OPERATIVO = 'operativo';
    case ADMINISTRATIVO = 'administrativo';
    case TAREAS = 'tareas';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public static function options(): array
    {
        return [
            self::OPERATIVO->value => 'operativo',
            self::ADMINISTRATIVO->value => 'administrativo',
            self::TAREAS->value => 'tareas',
        ];
    }

    public function label(): string
    {
        return match ($this) {
            self::OPERATIVO => 'operativo',
            self::ADMINISTRATIVO => 'administrativo',
            self::TAREAS => 'tareas',
        };
    }
}