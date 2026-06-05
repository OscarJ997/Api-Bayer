<?php

namespace Database\Factories;

use App\Enums\EstadoInsight;
use App\Enums\Prioridad;
use App\Models\RegulatoryInsight;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<RegulatoryInsight>
 */
class RegulatoryInsightFactory extends Factory
{
    protected $model = RegulatoryInsight::class;

    public function definition(): array
    {
        return [
            'relevante' => true,
            'pais' => 'Colombia',
            'country_code' => 'CO',
            'autoridad' => fake()->company(),
            'fecha_publicacion' => fake()->date(),
            'url_fuente' => fake()->unique()->url(),
            'tipo_publicacion' => 'Resolución',
            'sector' => ['Agroquímico'],
            'prioridad' => fake()->randomElement(Prioridad::cases()),
            'titulo_ejecutivo' => fake()->sentence(),
            'resumen_tecnico' => fake()->paragraph(),
            'impacto_para_bayer' => [
                'tipo_impacto' => ['Regulatorio'],
                'descripcion' => fake()->sentence(),
                'nivel_confianza' => 'Medio',
                'justificacion_confianza' => fake()->sentence(),
            ],
            'resumen_puntos' => [fake()->sentence()],
            'requiere_revision_humana' => false,
            'estado' => EstadoInsight::Pendiente,
        ];
    }
}
