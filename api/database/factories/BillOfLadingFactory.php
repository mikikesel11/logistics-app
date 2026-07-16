<?php

namespace Database\Factories;

use App\Models\BillOfLading;
use App\Models\Load;
use App\Models\Organization;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BillOfLading>
 */
class BillOfLadingFactory extends Factory
{
    protected $model = BillOfLading::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'organization_id' => Organization::factory(),
            'load_id' => Load::factory(),
            'bol_number' => 'BOL-'.fake()->unique()->numerify('######'),
            'customer_name' => fake()->company(),
            'carrier_name' => fake()->company().' Trucking',
            'ship_from' => ['city' => fake()->city(), 'state' => fake()->stateAbbr()],
            'ship_to' => ['city' => fake()->city(), 'state' => fake()->stateAbbr()],
            'freight' => [],
            'pdf_path' => null,
            'generated_at' => null,
        ];
    }
}
