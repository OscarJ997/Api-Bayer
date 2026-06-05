<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('regulatory_insights', function (Blueprint $table) {
            $table->id();
            $table->boolean('relevante')->default(false);
            $table->string('pais')->nullable();
            $table->string('autoridad')->nullable();
            $table->date('fecha_publicacion')->nullable();
            $table->string('url_fuente')->nullable()->unique();
            $table->string('tipo_publicacion')->nullable();
            $table->json('sector')->nullable();
            $table->string('prioridad', 10)->nullable();
            $table->string('titulo_ejecutivo');
            $table->text('resumen_tecnico')->nullable();
            $table->text('analisis_impacto')->nullable();
            $table->json('impacto_para_bayer')->nullable();
            $table->json('resumen_puntos')->nullable();
            $table->json('obligaciones_o_acciones')->nullable();
            $table->json('fechas_clave')->nullable();
            $table->json('productos_o_categorias_mencionadas')->nullable();
            $table->json('entidades_mencionadas')->nullable();
            $table->json('palabras_clave_regulatorias')->nullable();
            $table->json('riesgos_identificados')->nullable();
            $table->text('recomendacion_preliminar')->nullable();
            $table->boolean('requiere_revision_humana')->default(false);
            $table->text('razon_revision_humana')->nullable();
            $table->json('evidencia_textual_relevante')->nullable();
            $table->string('estado', 20)->default('pendiente');
            $table->json('payload_original')->nullable();
            $table->string('n8n_execution_id')->nullable()->unique();
            $table->timestamps();

            $table->index('prioridad');
            $table->index(['prioridad', 'created_at']);
            $table->index('pais');
            $table->index('relevante');
            $table->index('requiere_revision_humana');
            $table->index('estado');
            $table->index('fecha_publicacion');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('regulatory_insights');
    }
};
