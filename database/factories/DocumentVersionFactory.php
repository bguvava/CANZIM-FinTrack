<?php

namespace Database\Factories;

use App\Models\Document;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\DocumentVersion>
 */
class DocumentVersionFactory extends Factory
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

        return [
            'document_id' => Document::factory(),
            'version_number' => 1,
            'file_name' => $fileName,
            'file_path' => 'documents/archive/' . now()->format('Y/m') . '/' . Str::uuid() . '.' . $fileType,
            'file_type' => $fileType,
            'file_size' => fake()->numberBetween(1024, 5242880), // 1KB to 5MB
            'replaced_by' => User::factory(),
            'replaced_at' => now(),
        ];
    }

    /**
     * Indicate that the version is for a specific document.
     */
    public function forDocument(Document $document): static
    {
        return $this->state(fn(array $attributes) => [
            'document_id' => $document->id,
        ]);
    }

    /**
     * Indicate that the version has a specific version number.
     */
    public function version(int $number): static
    {
        return $this->state(fn(array $attributes) => [
            'version_number' => $number,
        ]);
    }

    /**
     * Indicate that the version was replaced by a specific user.
     */
    public function replacedBy(User $user): static
    {
        return $this->state(fn(array $attributes) => [
            'replaced_by' => $user->id,
        ]);
    }

    /**
     * Indicate that the version has not been replaced yet.
     */
    public function current(): static
    {
        return $this->state(fn(array $attributes) => [
            'replaced_by' => null,
            'replaced_at' => null,
        ]);
    }
}
