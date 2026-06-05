<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('regulatory_insights', function (Blueprint $table) {
            $table->string('nivel_confianza', 10)->nullable()->after('analisis_impacto');
        });
    }

    public function down(): void
    {
        Schema::table('regulatory_insights', function (Blueprint $table) {
            $table->dropColumn('nivel_confianza');
        });
    }
};
