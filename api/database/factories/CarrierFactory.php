<?php

namespace Database\Factories;

use App\Models\Carrier;
use App\Models\Organization;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Carrier>
 */
class CarrierFactory extends Factory
{
    protected $model = Carrier::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'organization_id' => Organization::factory(),
            'name' => fake()->company().' Trucking',
            'mc_number' => 'MC'.fake()->unique()->numerify('######'),
            'dot_number' => fake()->numerify('#######'),
            'email' => fake()->companyEmail(),
            'phone' => fake()->phoneNumber(),
            'insurance_expires_at' => fake()->dateTimeBetween('+1 month', '+1 year')->format('Y-m-d'),
            'notes' => null,
        ];
    }
}
