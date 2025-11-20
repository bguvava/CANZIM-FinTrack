<?php

namespace Tests\Feature\Comments;

use App\Models\Comment;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CommentCrudTest extends TestCase
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

    public function test_user_can_view_comments_for_entity(): void
    {
        Comment::factory()
            ->count(3)
            ->forCommentable($this->project)
            ->create();

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson("/api/v1/comments?commentable_type=Project&commentable_id={$this->project->id}");

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
            ])
            ->assertJsonCount(3, 'data');
    }

    public function test_user_can_create_root_comment(): void
    {
        $commentData = [
            'commentable_type' => 'Project',
            'commentable_id' => $this->project->id,
            'content' => 'This is a test comment for the project.',
        ];

        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/v1/comments', $commentData);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'content',
                    'user',
                    'created_at',
                ],
            ])
            ->assertJson([
                'data' => [
                    'content' => 'This is a test comment for the project.',
                ],
            ]);

        $this->assertDatabaseHas('comments', [
            'user_id' => $this->user->id,
            'commentable_type' => 'Project',
            'commentable_id' => $this->project->id,
            'content' => 'This is a test comment for the project.',
            'parent_id' => null,
        ]);
    }

    public function test_user_can_create_reply_to_comment(): void
    {
        $parentComment = Comment::factory()
            ->forCommentable($this->project)
            ->create();

        $replyData = [
            'commentable_type' => 'Project',
            'commentable_id' => $this->project->id,
            'content' => 'This is a reply to the comment.',
            'parent_id' => $parentComment->id,
        ];

        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/v1/comments', $replyData);

        $response->assertStatus(201)
            ->assertJson([
                'data' => [
                    'content' => 'This is a reply to the comment.',
                    'parent_id' => $parentComment->id,
                ],
            ]);

        $this->assertDatabaseHas('comments', [
            'user_id' => $this->user->id,
            'parent_id' => $parentComment->id,
            'content' => 'This is a reply to the comment.',
        ]);
    }

    public function test_user_can_create_comment_with_attachments(): void
    {
        Storage::fake('public');

        $file1 = UploadedFile::fake()->create('document.pdf', 500, 'application/pdf');
        $file2 = UploadedFile::fake()->image('screenshot.png', 800, 600);

        $commentData = [
            'commentable_type' => 'Project',
            'commentable_id' => $this->project->id,
            'content' => 'Comment with attachments.',
            'attachments' => [$file1, $file2],
        ];

        $response = $this->actingAs($this->user, 'sanctum')
            ->post('/api/v1/comments', $commentData);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'content',
                    'attachments' => [
                        '*' => [
                            'id',
                            'file_name',
                            'file_size',
                            'mime_type',
                        ],
                    ],
                ],
            ]);

        $this->assertDatabaseHas('comments', [
            'content' => 'Comment with attachments.',
        ]);

        $this->assertDatabaseCount('comment_attachments', 2);
    }

    public function test_user_can_update_own_comment(): void
    {
        $comment = Comment::factory()
            ->byUser($this->user)
            ->forCommentable($this->project)
            ->create(['content' => 'Original content']);

        $updateData = [
            'content' => 'Updated content for the comment.',
        ];

        $response = $this->actingAs($this->user, 'sanctum')
            ->putJson("/api/v1/comments/{$comment->id}", $updateData);

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'id' => $comment->id,
                    'content' => 'Updated content for the comment.',
                ],
            ]);

        $this->assertDatabaseHas('comments', [
            'id' => $comment->id,
            'content' => 'Updated content for the comment.',
        ]);
    }

    public function test_user_cannot_update_others_comment(): void
    {
        $otherUser = User::factory()->create();
        $comment = Comment::factory()
            ->byUser($otherUser)
            ->forCommentable($this->project)
            ->create(['content' => 'Original content']);

        $updateData = [
            'content' => 'Trying to update someone else\'s comment.',
        ];

        $response = $this->actingAs($this->user, 'sanctum')
            ->putJson("/api/v1/comments/{$comment->id}", $updateData);

        $response->assertStatus(403);

        $this->assertDatabaseHas('comments', [
            'id' => $comment->id,
            'content' => 'Original content',
        ]);
    }

    public function test_user_can_delete_own_comment(): void
    {
        $comment = Comment::factory()
            ->byUser($this->user)
            ->forCommentable($this->project)
            ->create();

        $response = $this->actingAs($this->user, 'sanctum')
            ->deleteJson("/api/v1/comments/{$comment->id}");

        $response->assertStatus(204);

        $this->assertSoftDeleted('comments', [
            'id' => $comment->id,
        ]);
    }

    public function test_user_cannot_delete_others_comment(): void
    {
        $otherUser = User::factory()->create();
        $comment = Comment::factory()
            ->byUser($otherUser)
            ->forCommentable($this->project)
            ->create();

        $response = $this->actingAs($this->user, 'sanctum')
            ->deleteJson("/api/v1/comments/{$comment->id}");

        $response->assertStatus(403);

        $this->assertDatabaseHas('comments', [
            'id' => $comment->id,
            'deleted_at' => null,
        ]);
    }

    public function test_deleted_comment_preserves_thread_structure(): void
    {
        $parentComment = Comment::factory()
            ->byUser($this->user)
            ->forCommentable($this->project)
            ->create(['content' => 'Parent comment']);

        $reply = Comment::factory()
            ->byUser($this->user)
            ->reply($parentComment)
            ->create(['content' => 'Reply to parent']);

        $this->actingAs($this->user, 'sanctum')
            ->deleteJson("/api/v1/comments/{$parentComment->id}");

        $this->assertSoftDeleted('comments', [
            'id' => $parentComment->id,
        ]);

        $this->assertDatabaseHas('comments', [
            'id' => $reply->id,
            'parent_id' => $parentComment->id,
            'deleted_at' => null,
        ]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson("/api/v1/comments?commentable_type=Project&commentable_id={$this->project->id}");

        $response->assertStatus(200);
    }
}
