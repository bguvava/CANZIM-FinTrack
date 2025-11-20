<?php

namespace Database\Factories;

use App\Models\Budget;
use App\Models\Comment;
use App\Models\Expense;
use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Comment>
 */
class CommentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $commentableTypes = [Project::class, Budget::class, Expense::class];
        $commentableType = fake()->randomElement($commentableTypes);

        return [
            'user_id' => User::factory(),
            'commentable_type' => class_basename($commentableType),
            'commentable_id' => function () use ($commentableType) {
                return $commentableType::factory()->create()->id;
            },
            'content' => fake()->realText(fake()->numberBetween(50, 500)),
            'parent_id' => fake()->boolean(20) ? null : null,
            'created_at' => fake()->dateTimeBetween('-30 days', 'now'),
            'updated_at' => function (array $attributes) {
                return fake()->dateTimeBetween($attributes['created_at'], 'now');
            },
        ];
    }

    /**
     * Indicate that the comment is a reply to another comment.
     */
    public function reply(?Comment $parent = null): static
    {
        return $this->state(function (array $attributes) use ($parent) {
            $parentComment = $parent ?? Comment::factory()->create();

            return [
                'parent_id' => $parentComment->id,
                'commentable_type' => $parentComment->commentable_type,
                'commentable_id' => $parentComment->commentable_id,
            ];
        });
    }

    /**
     * Indicate that the comment belongs to a specific commentable.
     */
    public function forCommentable($commentable): static
    {
        return $this->state(function (array $attributes) use ($commentable) {
            return [
                'commentable_type' => class_basename(get_class($commentable)),
                'commentable_id' => $commentable->id,
            ];
        });
    }

    /**
     * Indicate that the comment was created by a specific user.
     */
    public function byUser(User $user): static
    {
        return $this->state(function (array $attributes) use ($user) {
            return [
                'user_id' => $user->id,
            ];
        });
    }
}
