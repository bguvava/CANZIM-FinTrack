<?php

namespace Database\Factories;

use App\Models\Comment;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\CommentAttachment>
 */
class CommentAttachmentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $extensions = ['pdf', 'jpg', 'png'];
        $extension = fake()->randomElement($extensions);

        $mimeTypes = [
            'pdf' => 'application/pdf',
            'jpg' => 'image/jpeg',
            'png' => 'image/png',
        ];

        $uuid = Str::uuid();
        $year = fake()->year();
        $month = str_pad(fake()->numberBetween(1, 12), 2, '0', STR_PAD_LEFT);

        return [
            'comment_id' => Comment::factory(),
            'file_name' => fake()->words(fake()->numberBetween(2, 4), true).'.'.$extension,
            'file_path' => "attachments/{$year}/{$month}/{$uuid}.{$extension}",
            'file_size' => fake()->numberBetween(100000, 2000000),
            'mime_type' => $mimeTypes[$extension],
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    /**
     * Indicate that the attachment is a PDF file.
     */
    public function pdf(): static
    {
        return $this->state(function (array $attributes) {
            $uuid = Str::uuid();
            $year = date('Y');
            $month = date('m');

            return [
                'file_name' => fake()->words(3, true).'.pdf',
                'file_path' => "attachments/{$year}/{$month}/{$uuid}.pdf",
                'file_size' => fake()->numberBetween(100000, 2000000),
                'mime_type' => 'application/pdf',
            ];
        });
    }

    /**
     * Indicate that the attachment is a JPEG image.
     */
    public function jpeg(): static
    {
        return $this->state(function (array $attributes) {
            $uuid = Str::uuid();
            $year = date('Y');
            $month = date('m');

            return [
                'file_name' => fake()->words(2, true).'.jpg',
                'file_path' => "attachments/{$year}/{$month}/{$uuid}.jpg",
                'file_size' => fake()->numberBetween(200000, 1500000),
                'mime_type' => 'image/jpeg',
            ];
        });
    }

    /**
     * Indicate that the attachment is a PNG image.
     */
    public function png(): static
    {
        return $this->state(function (array $attributes) {
            $uuid = Str::uuid();
            $year = date('Y');
            $month = date('m');

            return [
                'file_name' => fake()->words(2, true).'.png',
                'file_path' => "attachments/{$year}/{$month}/{$uuid}.png",
                'file_size' => fake()->numberBetween(150000, 1800000),
                'mime_type' => 'image/png',
            ];
        });
    }

    /**
     * Indicate that the attachment belongs to a specific comment.
     */
    public function forComment(Comment $comment): static
    {
        return $this->state(function (array $attributes) use ($comment) {
            return [
                'comment_id' => $comment->id,
            ];
        });
    }
}
