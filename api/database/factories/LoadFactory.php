<?php

namespace Database\Factories;

use App\Domain\Loads\LoadStatus;
use App\Models\Load;
use App\Models\Organization;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Load>
 */
class LoadFactory extends Factory
{
    protected $model = Load::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $rate = fake()->numberBetween(80000, 500000);

        return [
            'organization_id' => Organization::factory(),
            'reference' => 'L'.fake()->unique()->numerify('#####'),
            'status' => LoadStatus::Quoted->value,
            'commodity' => fake()->randomElement(['Palletized freight', 'Produce', 'Auto parts', 'Retail goods']),
            'weight_lbs' => fake()->numberBetween(1000, 44000),
            'pickup_date' => fake()->dateTimeBetween('now', '+1 week')->format('Y-m-d'),
            'delivery_date' => fake()->dateTimeBetween('+1 week', '+2 weeks')->format('Y-m-d'),
            'customer_rate_cents' => $rate,
            'carrier_cost_cents' => (int) ($rate * fake()->randomFloat(2, 0.7, 0.9)),
            'notes' => null,
        ];
    }
}
