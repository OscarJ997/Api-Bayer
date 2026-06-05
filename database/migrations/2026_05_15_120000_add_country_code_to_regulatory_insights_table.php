<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('regulatory_insights', function (Blueprint $table) {
            $table->string('country_code', 2)->nullable()->after('pais');
            $table->index('country_code');
            $table->index(['country_code', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::table('regulatory_insights', function (Blueprint $table) {
            $table->dropIndex(['country_code', 'created_at']);
            $table->dropIndex(['country_code']);
            $table->dropColumn('country_code');
        });
    }
};
