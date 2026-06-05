<?php

namespace App\Models;

use App\Enums\EstadoInsight;
use App\Enums\NivelConfianza;
use App\Enums\Prioridad;
use Database\Factories\RegulatoryInsightFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'relevante',
    'pais',
    'country_code',
    'autoridad',
    'fecha_publicacion',
    'url_fuente',
    'tipo_publicacion',
    'sector',
    'prioridad',
    'titulo_ejecutivo',
    'resumen_tecnico',
    'analisis_impacto',
    'nivel_confianza',
    'impacto_para_bayer',
    'resumen_puntos',
    'obligaciones_o_acciones',
    'fechas_clave',
    'productos_o_categorias_mencionadas',
    'entidades_mencionadas',
    'palabras_clave_regulatorias',
    'riesgos_identificados',
    'recomendacion_preliminar',
    'requiere_revision_humana',
    'razon_revision_humana',
    'evidencia_textual_relevante',
    'estado',
    'payload_original',
    'n8n_execution_id',
])]
class RegulatoryInsight extends Model
{
    /** @use HasFactory<RegulatoryInsightFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'relevante' => 'boolean',
            'fecha_publicacion' => 'date',
            'sector' => 'array',
            'prioridad' => Prioridad::class,
            'nivel_confianza' => NivelConfianza::class,
            'impacto_para_bayer' => 'array',
            'resumen_puntos' => 'array',
            'obligaciones_o_acciones' => 'array',
            'fechas_clave' => 'array',
            'productos_o_categorias_mencionadas' => 'array',
            'entidades_mencionadas' => 'array',
            'palabras_clave_regulatorias' => 'array',
            'riesgos_identificados' => 'array',
            'requiere_revision_humana' => 'boolean',
            'evidencia_textual_relevante' => 'array',
            'estado' => EstadoInsight::class,
            'payload_original' => 'array',
        ];
    }

    /**
     * @param  Builder<self>  $query
     * @param  string|array<int, string>|Prioridad|array<int, Prioridad>  $prioridad
     */
    public function scopePrioridad(Builder $query, string|array|Prioridad $prioridad): void
    {
        $values = is_array($prioridad)
            ? array_map(fn ($p) => $p instanceof Prioridad ? $p->value : $p, $prioridad)
            : [($prioridad instanceof Prioridad) ? $prioridad->value : $prioridad];

        $query->whereIn('prioridad', $values);
    }

    /**
     * @param  Builder<self>  $query
     */
    public function scopeFilter(Builder $query, array $filters): void
    {
        if (isset($filters['prioridad'])) {
            $prioridades = is_array($filters['prioridad'])
                ? $filters['prioridad']
                : explode(',', (string) $filters['prioridad']);

            $query->prioridad(array_map('trim', $prioridades));
        }

        if (isset($filters['pais'])) {
            $query->where('pais', $filters['pais']);
        }

        if (isset($filters['country_code'])) {
            $query->where('country_code', strtoupper((string) $filters['country_code']));
        }

        if (isset($filters['relevante'])) {
            $query->where('relevante', filter_var($filters['relevante'], FILTER_VALIDATE_BOOLEAN));
        }

        if (isset($filters['requiere_revision_humana'])) {
            $query->where(
                'requiere_revision_humana',
                filter_var($filters['requiere_revision_humana'], FILTER_VALIDATE_BOOLEAN)
            );
        }

        if (isset($filters['estado'])) {
            $query->where('estado', $filters['estado']);
        }

        if (isset($filters['autoridad'])) {
            $query->where('autoridad', 'like', '%'.$filters['autoridad'].'%');
        }

        if (isset($filters['search'])) {
            $search = $filters['search'];
            $query->where(function (Builder $q) use ($search): void {
                $q->where('titulo_ejecutivo', 'like', "%{$search}%")
                    ->orWhere('resumen_tecnico', 'like', "%{$search}%");
            });
        }
    }
}
