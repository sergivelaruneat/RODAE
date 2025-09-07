<?php

namespace App\Enums;

enum Sport: string
{
    case CYCLING = 'cycling';
    case SWIMMING = 'swimming';
    case JOGGING = 'jogging';
    case RUNNING = 'running';
    case TRAIL_RUNNING = 'trail_running';
    case ATHLETICS = 'athletics';
    case POWERLIFTING = 'powerlifting';
    case BODYBUILDING = 'bodybuilding';
    case CROSSFIT = 'crossfit';
    case CLIMBING = 'climbing';
    case CALISTHENICS = 'calisthenics';

    public function label(): string
    {
        return match($this) {
            self::CYCLING => 'Ciclismo',
            self::SWIMMING => 'Natación',
            self::JOGGING => 'Jogging',
            self::RUNNING => 'Correr',
            self::TRAIL_RUNNING => 'Trail Running',
            self::ATHLETICS => 'Atletismo',
            self::POWERLIFTING => 'Powerlifting',
            self::BODYBUILDING => 'Musculación',
            self::CROSSFIT => 'CrossFit',
            self::CLIMBING => 'Escalada',
            self::CALISTHENICS => 'Calistenia',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}