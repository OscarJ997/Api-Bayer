<?php

namespace App\Http\Requests\Concerns;

use App\Enums\EstadoInsight;
use App\Enums\NivelConfianza;
use App\Enums\Prioridad;
use Illuminate\Validation\Rule;

trait RegulatoryInsightRules
{
    /**
     * @return array<string, mixed>
     */
    protected function insightRules(): array
    {
        return [
            'relevante' => ['sometimes', 'boolean'],
            'pais' => ['nullable', 'string', 'max:100'],
            'country_code' => ['nullable', 'string', 'size:2', 'alpha'],
            'autoridad' => ['nullable', 'string', 'max:255'],
            'fecha_publicacion' => ['nullable', 'date'],
            'url_fuente' => ['nullable', 'string', 'max:2048', 'url'],
            'tipo_publicacion' => ['nullable', 'string', 'max:100'],
            'sector' => ['nullable', 'array'],
            'sector.*' => ['string', 'max:100'],
            'prioridad' => ['nullable', Rule::enum(Prioridad::class)],
            'titulo_ejecutivo' => ['nullable', 'string', 'max:500'],
            'resumen_tecnico' => ['nullable', 'string'],
            'analisis_impacto' => ['nullable', 'string'],
            'nivel_confianza' => ['nullable', Rule::enum(NivelConfianza::class)],
            'impacto_para_bayer' => ['nullable', 'array'],
            'impacto_para_bayer.tipo_impacto' => ['nullable', 'array'],
            'impacto_para_bayer.tipo_impacto.*' => ['string', 'max:100'],
            'impacto_para_bayer.descripcion' => ['nullable', 'string'],
            'impacto_para_bayer.nivel_confianza' => ['nullable', Rule::enum(NivelConfianza::class)],
            'impacto_para_bayer.justificacion_confianza' => ['nullable', 'string'],
            'resumen_puntos' => ['nullable', 'array'],
            'resumen_puntos.*' => ['string'],
            'obligaciones_o_acciones' => ['nullable', 'array'],
            'obligaciones_o_acciones.*.accion_requerida' => ['required', 'string'],
            'obligaciones_o_acciones.*.responsable_sugerido' => ['nullable', 'string', 'max:255'],
            'obligaciones_o_acciones.*.plazo' => ['nullable', 'string', 'max:255'],
            'obligaciones_o_acciones.*.estado' => ['nullable', 'string', 'max:255'],
            'fechas_clave' => ['nullable', 'array'],
            'fechas_clave.*.tipo_fecha' => ['required', 'string', 'max:100'],
            'fechas_clave.*.fecha' => ['nullable', 'date'],
            'fechas_clave.*.descripcion' => ['nullable', 'string'],
            'productos_o_categorias_mencionadas' => ['nullable', 'array'],
            'productos_o_categorias_mencionadas.*' => ['string'],
            'entidades_mencionadas' => ['nullable', 'array'],
            'entidades_mencionadas.*' => ['string'],
            'palabras_clave_regulatorias' => ['nullable', 'array'],
            'palabras_clave_regulatorias.*' => ['string'],
            'riesgos_identificados' => ['nullable', 'array'],
            'riesgos_identificados.*' => ['string'],
            'recomendacion_preliminar' => ['nullable', 'string'],
            'requiere_revision_humana' => ['sometimes', 'boolean'],
            'razon_revision_humana' => ['nullable', 'string'],
            'evidencia_textual_relevante' => ['nullable', 'array'],
            'evidencia_textual_relevante.*.fragmento' => ['required', 'string'],
            'evidencia_textual_relevante.*.por_que_es_relevante' => ['nullable', 'string'],
            'estado' => ['sometimes', Rule::enum(EstadoInsight::class)],
            'n8n_execution_id' => ['nullable', 'string', 'max:255'],
        ];
    }
}
