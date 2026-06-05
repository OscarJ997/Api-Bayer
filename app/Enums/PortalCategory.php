<?php

namespace App\Enums;

enum PortalCategory: string
{
    case Gobierno = 'gobierno';
    case BancoCentral = 'banco_central';
    case Estadisticas = 'estadisticas';
    case Noticias = 'noticias';
    case Comercio = 'comercio';
    case Otro = 'otro';
}
