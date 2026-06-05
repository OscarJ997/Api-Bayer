<?php

namespace App\Http\Requests\Concerns;

trait NormalizesRegulatoryInsightPayload
{
    /**
     * Campos del esquema n8n (formato actual y campos opcionales legacy).
     *
     * @var list<string>
     */
    public const SCHEMA_KEYS = [
        'relevante',
        'titulo_ejecutivo',
        'pais',
        'country_code',
        'autoridad',
        'prioridad',
        'fecha_publicacion',
        'url_fuente',
        'resumen_tecnico',
        'analisis_impacto',
        'nivel_confianza',
        'recomendacion_preliminar',
        'resumen_puntos',
        'tipo_publicacion',
        'sector',
        'impacto_para_bayer',
        'obligaciones_o_acciones',
        'fechas_clave',
        'productos_o_categorias_mencionadas',
        'entidades_mencionadas',
        'palabras_clave_regulatorias',
        'riesgos_identificados',
        'requiere_revision_humana',
        'razon_revision_humana',
        'evidencia_textual_relevante',
        'n8n_execution_id',
        'estado',
    ];

    /** @var array<int, array<string, mixed>>|null */
    private ?array $rawBatchItems = null;

    public function isBatch(): bool
    {
        $payload = $this->json()->all();

        return is_array($payload) && array_is_list($payload);
    }

    protected function prepareForValidation(): void
    {
        if ($this->isBatch()) {
            $this->rawBatchItems = $this->json()->all();
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function validationData(): array
    {
        if ($this->isBatch()) {
            $this->rawBatchItems ??= $this->json()->all();

            $normalized = [];

            foreach ($this->rawBatchItems as $item) {
                $normalized[] = $this->normalizeInsightItem(is_array($item) ? $item : []);
            }

            return $normalized;
        }

        return $this->normalizeInsightItem(parent::validationData());
    }

    /**
     * Body JSON completo recibido (objeto único).
     *
     * @return array<string, mixed>
     */
    public function fullPayload(): array
    {
        return $this->json()->all();
    }

    /**
     * Items crudos del array enviado por n8n.
     *
     * @return array<int, array<string, mixed>>
     */
    public function batchRawItems(): array
    {
        return $this->rawBatchItems ?? [];
    }

    /**
     * @return array<string, mixed>
     */
    public function insightAttributes(): array
    {
        return $this->finalizeAttributes($this->safe()->only(self::SCHEMA_KEYS));
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function batchInsightAttributes(): array
    {
        $attributesList = [];

        foreach ($this->validated() as $item) {
            if (! is_array($item)) {
                continue;
            }

            $attributesList[] = $this->finalizeAttributes(
                collect($item)->only(self::SCHEMA_KEYS)->all()
            );
        }

        return $attributesList;
    }

    /**
     * @param  array<string, mixed>  $item
     * @return array<string, mixed>
     */
    protected function normalizeInsightItem(array $item): array
    {
        $payload = collect($item)->only(self::SCHEMA_KEYS)->all();

        foreach ([
            'pais',
            'autoridad',
            'fecha_publicacion',
            'url_fuente',
            'tipo_publicacion',
            'prioridad',
            'titulo_ejecutivo',
            'resumen_tecnico',
            'analisis_impacto',
            'nivel_confianza',
            'recomendacion_preliminar',
            'razon_revision_humana',
            'country_code',
        ] as $field) {
            if (array_key_exists($field, $payload) && $payload[$field] === '') {
                $payload[$field] = null;
            }
        }

        if (! empty($payload['country_code'])) {
            $payload['country_code'] = strtoupper((string) $payload['country_code']);
        }

        if (empty($payload['nivel_confianza'])
            && isset($payload['impacto_para_bayer']['nivel_confianza'])
            && $payload['impacto_para_bayer']['nivel_confianza'] !== '') {
            $payload['nivel_confianza'] = $payload['impacto_para_bayer']['nivel_confianza'];
        }

        if (isset($payload['impacto_para_bayer']) && is_array($payload['impacto_para_bayer'])) {
            $impacto = $payload['impacto_para_bayer'];

            foreach (['descripcion', 'nivel_confianza', 'justificacion_confianza'] as $field) {
                if (array_key_exists($field, $impacto) && $impacto[$field] === '') {
                    $impacto[$field] = null;
                }
            }

            $payload['impacto_para_bayer'] = $impacto;
        }

        if (isset($payload['obligaciones_o_acciones']) && is_array($payload['obligaciones_o_acciones'])) {
            $payload['obligaciones_o_acciones'] = $this->normalizeObligaciones($payload['obligaciones_o_acciones']);
        }

        if (isset($payload['fechas_clave']) && is_array($payload['fechas_clave'])) {
            $payload['fechas_clave'] = $this->normalizeFechasClave($payload['fechas_clave']);
        }

        if (array_key_exists('evidencia_textual_relevante', $payload)) {
            $payload['evidencia_textual_relevante'] = $this->normalizeEvidenciaTextual($payload['evidencia_textual_relevante']);
        }

        return $payload;
    }

    /**
     * @param  array<string, mixed>  $attributes
     * @return array<string, mixed>
     */
    private function finalizeAttributes(array $attributes): array
    {
        if (empty($attributes['titulo_ejecutivo'])) {
            $attributes['titulo_ejecutivo'] = 'Sin título';
        }

        return $attributes;
    }

    /**
     * @param  list<mixed>  $items
     * @return list<array{accion_requerida: string, responsable_sugerido: ?string, plazo: ?string, estado: ?string}>
     */
    private function normalizeObligaciones(array $items): array
    {
        $normalized = [];

        foreach ($items as $item) {
            if (is_string($item)) {
                $normalized[] = [
                    'accion_requerida' => $item,
                    'responsable_sugerido' => null,
                    'plazo' => null,
                    'estado' => null,
                ];

                continue;
            }

            if (! is_array($item)) {
                continue;
            }

            foreach (['accion_requerida', 'responsable_sugerido', 'plazo', 'estado'] as $field) {
                if (array_key_exists($field, $item) && $item[$field] === '') {
                    $item[$field] = null;
                }
            }

            $normalized[] = $item;
        }

        return $normalized;
    }

    /**
     * @param  list<mixed>  $items
     * @return list<array{tipo_fecha: string, fecha: ?string, descripcion: ?string}>
     */
    private function normalizeFechasClave(array $items): array
    {
        $normalized = [];

        foreach ($items as $item) {
            if (is_string($item)) {
                $normalized[] = $this->fechaClaveFromLegacyString($item);

                continue;
            }

            if (! is_array($item)) {
                continue;
            }

            foreach (['tipo_fecha', 'fecha', 'descripcion'] as $field) {
                if (array_key_exists($field, $item) && $item[$field] === '') {
                    $item[$field] = null;
                }
            }

            $normalized[] = $item;
        }

        return $normalized;
    }

    /**
     * @return array{tipo_fecha: string, fecha: ?string, descripcion: ?string}
     */
    private function fechaClaveFromLegacyString(string $value): array
    {
        $timestamp = strtotime($value);

        if ($timestamp !== false) {
            return [
                'tipo_fecha' => 'Sin clasificar',
                'fecha' => date('Y-m-d', $timestamp),
                'descripcion' => null,
            ];
        }

        return [
            'tipo_fecha' => 'Sin clasificar',
            'fecha' => null,
            'descripcion' => $value,
        ];
    }

    /**
     * @return list<array{fragmento: string, por_que_es_relevante: ?string}>
     */
    private function normalizeEvidenciaTextual(mixed $value): array
    {
        if (is_string($value)) {
            $decoded = json_decode($value, true);

            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                $value = $decoded;
            } else {
                return [
                    [
                        'fragmento' => $value,
                        'por_que_es_relevante' => null,
                    ],
                ];
            }
        }

        if (! is_array($value)) {
            return [];
        }

        if (! array_is_list($value)) {
            $value = [$value];
        }

        $normalized = [];

        foreach ($value as $item) {
            if (is_string($item)) {
                $normalized[] = [
                    'fragmento' => $item,
                    'por_que_es_relevante' => null,
                ];

                continue;
            }

            if (! is_array($item)) {
                continue;
            }

            foreach (['fragmento', 'por_que_es_relevante'] as $field) {
                if (array_key_exists($field, $item) && $item[$field] === '') {
                    $item[$field] = null;
                }
            }

            $normalized[] = $item;
        }

        return $normalized;
    }
}
