<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ExpenseCategory>
 */
class ExpenseCategoryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        static $usedCodes = [];

        $categories = [
            ['name' => 'Travel & Transportation', 'code' => 'TRAVEL'],
            ['name' => 'Salaries & Benefits', 'code' => 'SALARY'],
            ['name' => 'Procurement & Supplies', 'code' => 'PROCURE'],
            ['name' => 'Consultants & Professional Fees', 'code' => 'CONSULT'],
            ['name' => 'Training & Workshops', 'code' => 'TRAINING'],
            ['name' => 'Communication & IT', 'code' => 'COMMS'],
            ['name' => 'Utilities & Rent', 'code' => 'UTILITIES'],
            ['name' => 'Other Expenses', 'code' => 'OTHER'],
        ];

        // Filter out already used categories
        $availableCategories = array_filter($categories, function ($cat) use ($usedCodes) {
            return ! in_array($cat['code'], $usedCodes);
        });

        // If all categories used, reset
        if (empty($availableCategories)) {
            $usedCodes = [];
            $availableCategories = $categories;
        }

        $category = fake()->randomElement($availableCategories);
        $usedCodes[] = $category['code'];

        return [
            'name' => $category['name'],
            'code' => $category['code'],
            'description' => fake()->sentence(),
            'is_active' => true,
            'sort_order' => fake()->numberBetween(1, 100),
        ];
    }

    /**
     * Indicate that the category is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }
}
