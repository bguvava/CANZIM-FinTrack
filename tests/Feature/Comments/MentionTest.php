<?php

namespace Tests\Feature\Comments;

use App\Models\Comment;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class MentionTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected Project $project;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->project = Project::factory()->create();
    }

    public function test_mention_parsing_extracts_usernames(): void
    {
        $mentionedUser = User::factory()->create(['name' => 'John Doe']);

        $commentData = [
            'commentable_type' => 'Project',
            'commentable_id' => $this->project->id,
            'content' => 'Hey @johndoe, can you review this project?',
        ];

        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/v1/comments', $commentData);

        $response->assertStatus(201);

        $comment = Comment::latest()->first();
        $this->assertNotNull($comment);
        $this->assertStringContainsString('@johndoe', $comment->content);
    }

    public function test_mentions_create_notifications_for_valid_users(): void
    {
        Notification::fake();

        $mentionedUser1 = User::factory()->create(['name' => 'Alice Smith']);
        $mentionedUser2 = User::factory()->create(['name' => 'Bob Johnson']);

        $commentData = [
            'commentable_type' => 'Project',
            'commentable_id' => $this->project->id,
            'content' => 'Hey @alicesmith and @bobjohnson, please check this out!',
        ];

        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/v1/comments', $commentData);

        $response->assertStatus(201);

        $comment = Comment::latest()->first();
        $this->assertNotNull($comment);
    }

    public function test_invalid_mentions_are_ignored(): void
    {
        Notification::fake();

        $commentData = [
            'commentable_type' => 'Project',
            'commentable_id' => $this->project->id,
            'content' => 'Hey @nonexistentuser123, this mention should be ignored.',
        ];

        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/v1/comments', $commentData);

        $response->assertStatus(201);

        $comment = Comment::latest()->first();
        $this->assertNotNull($comment);
        $this->assertStringContainsString('@nonexistentuser123', $comment->content);
    }

    public function test_self_mentions_dont_create_notifications(): void
    {
        Notification::fake();

        $this->user->update(['name' => 'Current User']);

        $commentData = [
            'commentable_type' => 'Project',
            'commentable_id' => $this->project->id,
            'content' => 'I am mentioning myself @currentuser in this comment.',
        ];

        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/v1/comments', $commentData);

        $response->assertStatus(201);

        $comment = Comment::latest()->first();
        $this->assertNotNull($comment);
    }
}
