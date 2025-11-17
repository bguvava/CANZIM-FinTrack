<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\InKindContribution>
 */
class InKindContributionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $categories = ['equipment', 'supplies', 'services', 'training', 'other'];
        $descriptions = [
            'equipment' => ['Medical equipment donation', 'Computer hardware', 'Office furniture'],
            'supplies' => ['Office supplies', 'Medical supplies', 'Stationery'],
            'services' => ['Consulting services', 'Training services', 'Technical support'],
            'training' => ['Staff training program', 'Capacity building workshop', 'Skills development'],
            'other' => ['Miscellaneous donation', 'Other contribution'],
        ];

        $category = fake()->randomElement($categories);

        return [
            'donor_id' => \App\Models\Donor::factory(),
            'project_id' => \App\Models\Project::factory(),
            'description' => fake()->randomElement($descriptions[$category]),
            'estimated_value' => fake()->randomFloat(2, 500, 25000),
            'contribution_date' => fake()->dateTimeBetween('-1 year', 'now'),
            'category' => $category,
            'created_by' => \App\Models\User::factory(),
        ];
    }
}
