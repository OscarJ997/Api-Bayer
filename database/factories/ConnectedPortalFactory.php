<?php

namespace Database\Factories;

use App\Enums\PortalCategory;
use App\Enums\PortalStatus;
use App\Models\ConnectedPortal;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ConnectedPortal>
 */
class ConnectedPortalFactory extends Factory
{
    protected $model = ConnectedPortal::class;

    public function definition(): array
    {
        return [
            'country_code' => 'CO',
            'name' => fake()->company().' Portal',
            'url' => fake()->url(),
            'description' => fake()->sentence(),
            'category' => PortalCategory::Gobierno,
            'status' => PortalStatus::Activo,
        ];
    }
}
