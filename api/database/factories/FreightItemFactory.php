<?php

namespace Database\Factories;

use App\Models\FreightItem;
use App\Models\Load;
use App\Models\Organization;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<FreightItem>
 */
class FreightItemFactory extends Factory
{
    protected $model = FreightItem::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'organization_id' => Organization::factory(),
            'load_id' => Load::factory(),
            'description' => fake()->words(3, true),
            'pieces' => fake()->numberBetween(1, 26),
            'weight_lbs' => fake()->numberBetween(100, 4000),
            'freight_class' => fake()->randomElement(['50', '70', '92.5', '175']),
        ];
    }
}
