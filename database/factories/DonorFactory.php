<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Donor>
 */
class DonorFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $organizations = [
            'Global Development Fund',
            'International Aid Foundation',
            'United Nations Development Program',
            'World Health Organization',
            'USAID',
            'Bill & Melinda Gates Foundation',
            'Africa Development Bank',
            'European Union Aid',
            'UK Aid Direct',
            'Canadian International Development Agency',
        ];

        return [
            'name' => fake()->randomElement($organizations),
            'contact_person' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'phone' => fake()->phoneNumber(),
            'address' => fake()->address(),
            'tax_id' => fake()->optional()->numerify('TAX-####-####'),
            'website' => fake()->optional()->url(),
            'status' => fake()->randomElement(['active', 'inactive']),
            'notes' => fake()->optional()->sentence(),
        ];
    }

    /**
     * Indicate that the donor is active.
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'active',
        ]);
    }

    /**
     * Indicate that the donor is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'inactive',
        ]);
    }
}
