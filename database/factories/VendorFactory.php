<?php

namespace Database\Factories;

use App\Models\Vendor;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Vendor>
 */
class VendorFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        static $counter = 0;
        $counter++;
        $paymentTerms = ['Net 30', 'Net 60', 'Net 90', 'COD', 'Net 15', '2/10 Net 30'];

        return [
            'vendor_code' => 'VEN-'.date('Y').'-'.str_pad((string) $counter, 4, '0', STR_PAD_LEFT),
            'name' => fake()->company(),
            'contact_person' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'phone' => fake()->phoneNumber(),
            'address' => fake()->address(),
            'tax_id' => fake()->optional()->numerify('##########'),
            'payment_terms' => fake()->randomElement($paymentTerms),
            'is_active' => true,
        ];
    }

    /**
     * Indicate that the vendor is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }
}
