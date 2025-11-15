<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Role;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Role>
 */
class RoleFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<\Illuminate\Database\Eloquent\Model>
     */
    protected $model = Role::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->unique()->randomElement([
                'Programs Manager',
                'Finance Officer',
                'Project Officer',
            ]),
            'slug' => fn (array $attributes) => \Illuminate\Support\Str::slug($attributes['name']),
            'description' => $this->faker->sentence(),
        ];
    }

    /**
     * Indicate that the role is Programs Manager.
     */
    public function programsManager(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'Programs Manager',
            'slug' => 'programs-manager',
        ]);
    }

    /**
     * Indicate that the role is Finance Officer.
     */
    public function financeOfficer(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'Finance Officer',
            'slug' => 'finance-officer',
        ]);
    }

    /**
     * Indicate that the role is Project Officer.
     */
    public function projectOfficer(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'Project Officer',
            'slug' => 'project-officer',
        ]);
    }
}
