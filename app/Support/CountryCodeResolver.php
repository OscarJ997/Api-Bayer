<?php

namespace App\Support;

final class CountryCodeResolver
{
    /**
     * @var array<string, string>
     */
    private const NAME_TO_CODE = [
        'argentina' => 'AR',
        'bolivia' => 'BO',
        'brasil' => 'BR',
        'brazil' => 'BR',
        'chile' => 'CL',
        'colombia' => 'CO',
        'costa rica' => 'CR',
        'cuba' => 'CU',
        'rep. dominicana' => 'DO',
        'republica dominicana' => 'DO',
        'república dominicana' => 'DO',
        'ecuador' => 'EC',
        'el salvador' => 'SV',
        'guatemala' => 'GT',
        'honduras' => 'HN',
        'méxico' => 'MX',
        'mexico' => 'MX',
        'nicaragua' => 'NI',
        'panamá' => 'PA',
        'panama' => 'PA',
        'paraguay' => 'PY',
        'perú' => 'PE',
        'peru' => 'PE',
        'uruguay' => 'UY',
    ];

    public static function resolve(?string $countryCode, ?string $pais): ?string
    {
        if ($countryCode !== null && $countryCode !== '') {
            return strtoupper(trim($countryCode));
        }

        if ($pais === null || $pais === '') {
            return null;
        }

        $trimmed = trim($pais);

        // n8n a veces envía el código ISO en "pais" (ej. "CO" en lugar de "Colombia")
        if (strlen($trimmed) === 2 && ctype_alpha($trimmed)) {
            return strtoupper($trimmed);
        }

        $key = mb_strtolower($trimmed);

        return self::NAME_TO_CODE[$key] ?? null;
    }
}
