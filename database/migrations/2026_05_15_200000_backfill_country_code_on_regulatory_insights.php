<?php

use App\Models\RegulatoryInsight;
use App\Support\CountryCodeResolver;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        RegulatoryInsight::query()
            ->whereNull('country_code')
            ->each(function (RegulatoryInsight $insight): void {
                $code = CountryCodeResolver::resolve(
                    $insight->country_code,
                    $insight->pais,
                );

                if ($code !== null) {
                    $insight->update(['country_code' => $code]);
                }
            });
    }

    public function down(): void
    {
        //
    }
};
