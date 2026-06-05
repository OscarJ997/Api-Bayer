<?php

namespace App\Enums;

enum EstadoInsight: string
{
    case Pendiente = 'pendiente';
    case EnRevision = 'en_revision';
    case Revisado = 'revisado';
    case Descartado = 'descartado';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
