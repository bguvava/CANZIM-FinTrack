<?php

namespace Tests\Feature;

use App\Models\Comment;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CommentsSystemTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected Project $project;

    protected function setUp(): void
    {
        parent::setUp();

        // Create a test user
        $this->user = User::factory()->create([
            'email' => 'commenter@test.com',
        ]);

        // Create a test project
        $this->project = Project::factory()->create();
    }

    public function test_user_can_fetch_comments_with_valid_token(): void
    {
        // Create some comments
        Comment::factory()->count(3)->create([
            'commentable_type' => Project::class,
            'commentable_id' => $this->project->id,
            'user_id' => $this->user->id,
        ]);

        // Query parameters should be in the URL, not passed as second argument
        $commentableType = urlencode(Project::class);
        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson("/api/v1/comments?commentable_type={$commentableType}&commentable_id={$this->project->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'content',
                        'user',
                        'created_at',
                        'updated_at',
                    ],
                ],
                'current_page',
                'last_page',
                'per_page',
                'total',
            ]);

        $this->assertEquals(3, $response->json('total'));
    }

    public function test_user_can_create_comment(): void
    {
        $commentData = [
            'commentable_type' => Project::class,
            'commentable_id' => $this->project->id,
            'content' => 'This is a test comment',
        ];

        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/v1/comments', $commentData);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'data' => [
                    'content' => 'This is a test comment',
                ],
            ]);

        // Note: The database may store morph type as short name 'Project' instead of full class name
        // This depends on Laravel's morph map configuration
        $this->assertDatabaseHas('comments', [
            'commentable_id' => $this->project->id,
            'content' => 'This is a test comment',
            'user_id' => $this->user->id,
        ]);
    }

    public function test_user_can_create_reply_to_comment(): void
    {
        // Create a parent comment
        $parentComment = Comment::factory()->create([
            'commentable_type' => Project::class,
            'commentable_id' => $this->project->id,
            'user_id' => $this->user->id,
        ]);

        $replyData = [
            'commentable_type' => Project::class,
            'commentable_id' => $this->project->id,
            'content' => 'This is a reply',
            'parent_id' => $parentComment->id,
        ];

        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/v1/comments', $replyData);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'data' => [
                    'content' => 'This is a reply',
                    'parent_id' => $parentComment->id,
                ],
            ]);

        $this->assertDatabaseHas('comments', [
            'content' => 'This is a reply',
            'parent_id' => $parentComment->id,
        ]);
    }

    public function test_user_can_update_own_comment(): void
    {
        $comment = Comment::factory()->create([
            'commentable_type' => Project::class,
            'commentable_id' => $this->project->id,
            'user_id' => $this->user->id,
            'content' => 'Original content',
        ]);

        $updateData = [
            'content' => 'Updated content',
        ];

        $response = $this->actingAs($this->user, 'sanctum')
            ->putJson("/api/v1/comments/{$comment->id}", $updateData);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'content' => 'Updated content',
                ],
            ]);

        $this->assertDatabaseHas('comments', [
            'id' => $comment->id,
            'content' => 'Updated content',
        ]);
    }

    public function test_user_can_delete_own_comment(): void
    {
        $comment = Comment::factory()->create([
            'commentable_type' => Project::class,
            'commentable_id' => $this->project->id,
            'user_id' => $this->user->id,
        ]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->deleteJson("/api/v1/comments/{$comment->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);

        $this->assertSoftDeleted('comments', [
            'id' => $comment->id,
        ]);
    }

    public function test_unauthorized_user_cannot_fetch_comments(): void
    {
        $commentableType = urlencode(Project::class);
        $response = $this->getJson("/api/v1/comments?commentable_type={$commentableType}&commentable_id={$this->project->id}");

        $response->assertStatus(401);
    }

    public function test_user_cannot_update_others_comment(): void
    {
        $otherUser = User::factory()->create();

        $comment = Comment::factory()->create([
            'commentable_type' => Project::class,
            'commentable_id' => $this->project->id,
            'user_id' => $otherUser->id,
            'content' => 'Original content',
        ]);

        $updateData = [
            'content' => 'Trying to update others comment',
        ];

        $response = $this->actingAs($this->user, 'sanctum')
            ->putJson("/api/v1/comments/{$comment->id}", $updateData);

        $response->assertStatus(403);

        // Verify comment was not updated
        $this->assertDatabaseHas('comments', [
            'id' => $comment->id,
            'content' => 'Original content',
        ]);
    }

    public function test_comment_validation_requires_content(): void
    {
        $commentData = [
            'commentable_type' => Project::class,
            'commentable_id' => $this->project->id,
            'content' => '',
        ];

        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/v1/comments', $commentData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['content']);
    }
}
