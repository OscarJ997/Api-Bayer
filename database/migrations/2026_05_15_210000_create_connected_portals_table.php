<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('connected_portals', function (Blueprint $table) {
            $table->id();
            $table->string('country_code', 2);
            $table->string('name');
            $table->string('url', 2048)->nullable();
            $table->text('description')->nullable();
            $table->string('category', 30)->nullable();
            $table->string('status', 20)->default('activo');
            $table->timestamps();

            $table->index('country_code');
            $table->index(['country_code', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('connected_portals');
    }
};
