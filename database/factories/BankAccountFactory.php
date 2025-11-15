<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\BankAccount>
 */
class BankAccountFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $banks = ['Standard Bank', 'FNB', 'Nedbank', 'Absa', 'Capitec'];
        $branches = ['Main Branch', 'City Branch', 'Central Branch', 'Commercial Branch'];
        $currencies = ['ZAR', 'USD', 'EUR'];

        return [
            'account_name' => fake()->company().' Operating Account',
            'account_number' => fake()->numerify('##########'),
            'bank_name' => fake()->randomElement($banks),
            'branch' => fake()->randomElement($branches),
            'currency' => fake()->randomElement($currencies),
            'current_balance' => fake()->randomFloat(2, 10000, 500000),
            'is_active' => true,
        ];
    }

    /**
     * Indicate that the account is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    /**
     * Indicate that the account has low balance.
     */
    public function lowBalance(): static
    {
        return $this->state(fn (array $attributes) => [
            'current_balance' => fake()->randomFloat(2, 100, 5000),
        ]);
    }
}
