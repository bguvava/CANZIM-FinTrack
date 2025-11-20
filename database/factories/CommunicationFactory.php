<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Communication>
 */
class CommunicationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $types = ['email', 'phone_call', 'meeting', 'letter', 'other'];
        $subjects = [
            'Funding proposal discussion',
            'Project update meeting',
            'Budget review call',
            'Quarterly report submission',
            'Follow-up on donation',
            'Thank you communication',
            'Partnership agreement discussion',
        ];

        return [
            'communicable_id' => \App\Models\Donor::factory(),
            'communicable_type' => \App\Models\Donor::class,
            'type' => fake()->randomElement($types),
            'communication_date' => fake()->dateTimeBetween('-6 months', 'now'),
            'subject' => fake()->randomElement($subjects),
            'notes' => fake()->paragraph(),
            'attachment_path' => null,
            'next_action_date' => fake()->optional()->dateTimeBetween('now', '+3 months'),
            'next_action_notes' => fake()->optional()->sentence(),
            'created_by' => \App\Models\User::factory(),
        ];
    }

    /**
     * Indicate that the communication has an attachment.
     */
    public function withAttachment(): static
    {
        return $this->state(fn (array $attributes) => [
            'attachment_path' => 'communications/'.fake()->uuid().'.pdf',
        ]);
    }
}
