<?php

namespace Database\Seeders;

use App\Models\Budget;
use App\Models\Comment;
use App\Models\CommentAttachment;
use App\Models\Expense;
use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Seeder;

class CommentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::all();
        $projects = Project::all();
        $budgets = Budget::all();
        $expenses = Expense::all();

        if ($users->isEmpty()) {
            $this->command->warn('No users found. Please seed users first.');

            return;
        }

        $commentables = collect([
            ...$projects->map(fn ($p) => ['type' => Project::class, 'id' => $p->id]),
            ...$budgets->map(fn ($b) => ['type' => Budget::class, 'id' => $b->id]),
            ...$expenses->map(fn ($e) => ['type' => Expense::class, 'id' => $e->id]),
        ]);

        if ($commentables->isEmpty()) {
            $this->command->warn('No commentable entities found. Please seed projects, budgets, or expenses first.');

            return;
        }

        // Create 30 root comments
        $rootComments = collect();
        for ($i = 0; $i < 30; $i++) {
            $commentable = $commentables->random();

            $comment = Comment::factory()
                ->byUser($users->random())
                ->create([
                    'commentable_type' => $commentable['type'],
                    'commentable_id' => $commentable['id'],
                    'parent_id' => null,
                ]);

            // Attach 1-2 attachments to 40% of comments
            if (fake()->boolean(40)) {
                $attachmentCount = fake()->numberBetween(1, 2);
                CommentAttachment::factory()
                    ->count($attachmentCount)
                    ->forComment($comment)
                    ->create();
            }

            $rootComments->push($comment);
        }

        // Create 2-3 replies for 10 random root comments (1 level deep)
        $selectedRootComments = $rootComments->random(10);
        $firstLevelReplies = collect();

        foreach ($selectedRootComments as $rootComment) {
            $replyCount = fake()->numberBetween(2, 3);

            for ($i = 0; $i < $replyCount; $i++) {
                $reply = Comment::factory()
                    ->byUser($users->random())
                    ->reply($rootComment)
                    ->create();

                // Attach attachments to 40% of replies
                if (fake()->boolean(40)) {
                    $attachmentCount = fake()->numberBetween(1, 2);
                    CommentAttachment::factory()
                        ->count($attachmentCount)
                        ->forComment($reply)
                        ->create();
                }

                $firstLevelReplies->push($reply);
            }
        }

        // Create 1-2 nested replies for 3 random first-level replies (2 levels deep - max depth)
        $selectedFirstLevelReplies = $firstLevelReplies->random(min(3, $firstLevelReplies->count()));

        foreach ($selectedFirstLevelReplies as $firstLevelReply) {
            $nestedReplyCount = fake()->numberBetween(1, 2);

            for ($i = 0; $i < $nestedReplyCount; $i++) {
                $nestedReply = Comment::factory()
                    ->byUser($users->random())
                    ->reply($firstLevelReply)
                    ->create();

                // Attach attachments to 40% of nested replies
                if (fake()->boolean(40)) {
                    $attachmentCount = fake()->numberBetween(1, 2);
                    CommentAttachment::factory()
                        ->count($attachmentCount)
                        ->forComment($nestedReply)
                        ->create();
                }
            }
        }

        $this->command->info('Comments seeded successfully!');
    }
}
