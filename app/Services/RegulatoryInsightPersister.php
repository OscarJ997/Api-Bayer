<?php

namespace App\Services;

use App\Enums\EstadoInsight;
use App\Models\RegulatoryInsight;
use App\Support\CountryCodeResolver;

class RegulatoryInsightPersister
{
    /**
     * @param  array<string, mixed>  $attributes
     * @param  array<string, mixed>  $payloadOriginal
     * @return array{insight: RegulatoryInsight, created: bool}
     */
    public function persist(array $attributes, array $payloadOriginal): array
    {
        $data = $this->mapPayload($attributes);
        $data['payload_original'] = $payloadOriginal;

        $uniqueKey = $this->resolveUniqueKey($data);

        if ($uniqueKey !== null) {
            $insight = RegulatoryInsight::query()->updateOrCreate($uniqueKey, $data);

            return [
                'insight' => $insight,
                'created' => $insight->wasRecentlyCreated,
            ];
        }

        return [
            'insight' => RegulatoryInsight::query()->create($data),
            'created' => true,
        ];
    }

    /**
     * @param  array<string, mixed>  $attributes
     * @param  array<string, mixed>  $payloadOriginal
     */
    public function update(
        RegulatoryInsight $insight,
        array $attributes,
        array $payloadOriginal,
    ): RegulatoryInsight {
        $data = $this->mapPayload($attributes);
        $data['payload_original'] = $payloadOriginal;
        $insight->update($data);

        return $insight->fresh();
    }

    /**
     * @param  array<int, array<string, mixed>>  $attributesList
     * @param  array<int, array<string, mixed>>  $rawItems
     * @return array{insights: list<RegulatoryInsight>, created: int, updated: int}
     */
    public function persistMany(array $attributesList, array $rawItems): array
    {
        $insights = [];
        $created = 0;
        $updated = 0;

        foreach ($attributesList as $index => $attributes) {
            $result = $this->persist(
                $attributes,
                $rawItems[$index] ?? $attributes
            );

            $insights[] = $result['insight'];

            if ($result['created']) {
                $created++;
            } else {
                $updated++;
            }
        }

        return compact('insights', 'created', 'updated');
    }

    /**
     * @param  array<string, mixed>  $attributes
     * @return array<string, mixed>
     */
    private function mapPayload(array $attributes): array
    {
        if (! array_key_exists('estado', $attributes)) {
            $attributes['estado'] = EstadoInsight::Pendiente->value;
        }

        $attributes['country_code'] = CountryCodeResolver::resolve(
            $attributes['country_code'] ?? null,
            $attributes['pais'] ?? null,
        );

        return $attributes;
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, string>|null
     */
    private function resolveUniqueKey(array $data): ?array
    {
        if (! empty($data['url_fuente'])) {
            return ['url_fuente' => $data['url_fuente']];
        }

        if (! empty($data['n8n_execution_id'])) {
            return ['n8n_execution_id' => $data['n8n_execution_id']];
        }

        return null;
    }
}
