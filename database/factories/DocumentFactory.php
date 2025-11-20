<?php

namespace Database\Factories;

use App\Models\Document;
use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Document>
 */
class DocumentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $fileTypes = ['pdf', 'docx', 'xlsx', 'jpg', 'png'];
        $fileType = fake()->randomElement($fileTypes);
        $fileName = Str::slug(fake()->words(3, true)) . '.' . $fileType;

        $categories = [
            'budget-documents',
            'expense-receipts',
            'project-reports',
            'donor-agreements',
            'other',
        ];

        return [
            'documentable_type' => Project::class,
            'documentable_id' => Project::factory(),
            'title' => fake()->sentence(4),
            'description' => fake()->paragraph(),
            'category' => fake()->randomElement($categories),
            'file_name' => $fileName,
            'file_path' => 'documents/' . now()->format('Y/m') . '/' . Str::uuid() . '.' . $fileType,
            'file_type' => $fileType,
            'file_size' => fake()->numberBetween(1024, 5242880), // 1KB to 5MB
            'version_number' => 1,
            'current_version_id' => null,
            'uploaded_by' => User::factory(),
        ];
    }

    /**
     * Indicate that the document is for a specific entity.
     */
    public function forEntity(string $type, int $id): static
    {
        return $this->state(fn(array $attributes) => [
            'documentable_type' => $type,
            'documentable_id' => $id,
        ]);
    }

    /**
     * Indicate that the document is a PDF.
     */
    public function pdf(): static
    {
        return $this->state(function (array $attributes) {
            $fileName = Str::slug(fake()->words(3, true)) . '.pdf';

            return [
                'file_name' => $fileName,
                'file_path' => 'documents/' . now()->format('Y/m') . '/' . Str::uuid() . '.pdf',
                'file_type' => 'pdf',
            ];
        });
    }

    /**
     * Indicate that the document is in a specific category.
     */
    public function inCategory(string $category): static
    {
        return $this->state(fn(array $attributes) => [
            'category' => $category,
        ]);
    }

    /**
     * Indicate that the document is uploaded by a specific user.
     */
    public function uploadedBy(User $user): static
    {
        return $this->state(fn(array $attributes) => [
            'uploaded_by' => $user->id,
        ]);
    }

    /**
     * Indicate that the document has a specific version number.
     */
    public function version(int $number): static
    {
        return $this->state(fn(array $attributes) => [
            'version_number' => $number,
        ]);
    }
}
