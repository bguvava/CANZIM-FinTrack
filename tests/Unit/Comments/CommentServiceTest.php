<?php

namespace Tests\Unit\Comments;

use App\Models\Comment;
use App\Models\Project;
use App\Models\User;
use App\Services\CommentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CommentServiceTest extends TestCase
{
    use RefreshDatabase;

    protected CommentService $commentService;

    protected User $user;

    protected Project $project;

    protected function setUp(): void
    {
        parent::setUp();

        $this->commentService = app(CommentService::class);
        $this->user = User::factory()->create();
        $this->project = Project::factory()->create();

        Storage::fake('public');
    }

    public function test_create_comment_stores_data(): void
    {
        $data = [
            'commentable_type' => 'Project',
            'commentable_id' => $this->project->id,
            'content' => 'This is a test comment.',
        ];

        $comment = $this->commentService->createComment($data, $this->user);

        $this->assertInstanceOf(Comment::class, $comment);
        $this->assertEquals('This is a test comment.', $comment->content);
        $this->assertEquals($this->user->id, $comment->user_id);
        $this->assertEquals('Project', $comment->commentable_type);
        $this->assertEquals($this->project->id, $comment->commentable_id);

        $this->assertDatabaseHas('comments', [
            'id' => $comment->id,
            'content' => 'This is a test comment.',
            'user_id' => $this->user->id,
        ]);
    }

    public function test_create_comment_parses_mentions(): void
    {
        $mentionedUser = User::factory()->create(['name' => 'Jane Doe']);

        $data = [
            'commentable_type' => 'Project',
            'commentable_id' => $this->project->id,
            'content' => 'Hey @janedoe, please review this.',
        ];

        $comment = $this->commentService->createComment($data, $this->user);

        $this->assertInstanceOf(Comment::class, $comment);
        $this->assertStringContainsString('@janedoe', $comment->content);
    }

    public function test_update_comment_updates_content(): void
    {
        $comment = Comment::factory()
            ->byUser($this->user)
            ->forCommentable($this->project)
            ->create(['content' => 'Original content']);

        $updateData = [
            'content' => 'Updated content',
        ];

        $updatedComment = $this->commentService->updateComment($comment, $updateData);

        $this->assertInstanceOf(Comment::class, $updatedComment);
        $this->assertEquals('Updated content', $updatedComment->content);

        $this->assertDatabaseHas('comments', [
            'id' => $comment->id,
            'content' => 'Updated content',
        ]);
    }

    public function test_delete_comment_soft_deletes(): void
    {
        $comment = Comment::factory()
            ->byUser($this->user)
            ->forCommentable($this->project)
            ->create();

        $commentId = $comment->id;

        $result = $this->commentService->deleteComment($comment);

        $this->assertTrue($result);

        $this->assertSoftDeleted('comments', [
            'id' => $commentId,
        ]);

        $deletedComment = Comment::withTrashed()->find($commentId);
        $this->assertNotNull($deletedComment->deleted_at);
    }
}
