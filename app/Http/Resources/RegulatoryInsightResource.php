<?php

namespace App\Http\Resources;

use App\Enums\EstadoInsight;
use App\Models\RegulatoryInsight;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin RegulatoryInsight */
class RegulatoryInsightResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'relevante' => $this->relevante,
            'pais' => $this->pais,
            'country_code' => $this->country_code,
            'autoridad' => $this->autoridad,
            'fecha_publicacion' => $this->fecha_publicacion?->toDateString(),
            'url_fuente' => $this->url_fuente,
            'tipo_publicacion' => $this->tipo_publicacion,
            'sector' => $this->sector ?? [],
            'prioridad' => $this->prioridad?->value,
            'titulo_ejecutivo' => $this->titulo_ejecutivo,
            'resumen_tecnico' => $this->resumen_tecnico,
            'analisis_impacto' => $this->analisis_impacto,
            'nivel_confianza' => $this->nivel_confianza?->value,
            'impacto_para_bayer' => $this->impacto_para_bayer,
            'resumen_puntos' => $this->resumen_puntos ?? [],
            'obligaciones_o_acciones' => $this->obligaciones_o_acciones ?? [],
            'fechas_clave' => $this->fechas_clave ?? [],
            'productos_o_categorias_mencionadas' => $this->productos_o_categorias_mencionadas ?? [],
            'entidades_mencionadas' => $this->entidades_mencionadas ?? [],
            'palabras_clave_regulatorias' => $this->palabras_clave_regulatorias ?? [],
            'riesgos_identificados' => $this->riesgos_identificados ?? [],
            'recomendacion_preliminar' => $this->recomendacion_preliminar,
            'requiere_revision_humana' => $this->requiere_revision_humana,
            'razon_revision_humana' => $this->razon_revision_humana,
            'evidencia_textual_relevante' => $this->evidencia_textual_relevante ?? [],
            'estado' => $this->estado?->value,
            'n8n_execution_id' => $this->n8n_execution_id,
            'payload_original' => $this->when(
                $request->boolean('include_payload'),
                $this->payload_original
            ),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
            // Aliases compatibles con latamdatahub (Base44 / ProcessedDocument)
            'title' => $this->titulo_ejecutivo,
            'summary' => $this->resumen_tecnico,
            'key_findings' => $this->resumen_puntos ?? [],
            'created_date' => $this->created_at?->toIso8601String(),
            'status' => $this->frontendStatus(),
        ];
    }

    private function frontendStatus(): string
    {
        return match ($this->estado) {
            EstadoInsight::Revisado => 'final',
            EstadoInsight::Descartado => 'error',
            default => 'draft',
        };
    }
}
