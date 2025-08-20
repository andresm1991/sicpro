<?php

namespace App\Enums;

enum PushNotificationsEnum: string
{
    case OPERATIVO = 'operativo';
    case ADMINISTRATIVO = 'administrativo';
    case TAREAS = 'tareas';
    case EVENTUALIDAD = 'eventualidades';

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
            self::EVENTUALIDAD->value => 'eventualidades',
        ];
    }

    public function label(): string
    {
        return match ($this) {
            self::OPERATIVO => 'operativo',
            self::ADMINISTRATIVO => 'administrativo',
            self::TAREAS => 'tareas',
            self::EVENTUALIDAD->value => 'eventualidades',
        };
    }
}