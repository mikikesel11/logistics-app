<?php

namespace Database\Factories;

use App\Models\Customer;
use App\Models\Organization;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Customer>
 */
class CustomerFactory extends Factory
{
    protected $model = Customer::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'organization_id' => Organization::factory(),
            'name' => fake()->company(),
            'email' => fake()->companyEmail(),
            'phone' => fake()->phoneNumber(),
            'billing_terms' => fake()->randomElement(['Net 15', 'Net 30', 'Net 45', 'Due on receipt']),
            'credit_limit' => fake()->randomFloat(2, 5000, 100000),
            'notes' => null,
        ];
    }
}
