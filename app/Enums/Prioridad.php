<?php

namespace App\Enums;

enum Prioridad: string
{
    case Alta = 'Alta';
    case Media = 'Media';
    case Baja = 'Baja';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
