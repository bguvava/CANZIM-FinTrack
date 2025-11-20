<?php

namespace Tests\Feature\Comments;

use App\Models\Comment;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ThreadingTest extends TestCase
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

    public function test_comments_can_have_replies(): void
    {
        $parentComment = Comment::factory()
            ->forCommentable($this->project)
            ->create();

        $replyData = [
            'commentable_type' => 'Project',
            'commentable_id' => $this->project->id,
            'content' => 'This is a reply.',
            'parent_id' => $parentComment->id,
        ];

        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/v1/comments', $replyData);

        $response->assertStatus(201)
            ->assertJson([
                'data' => [
                    'parent_id' => $parentComment->id,
                ],
            ]);

        $reply = Comment::where('parent_id', $parentComment->id)->first();
        $this->assertNotNull($reply);
        $this->assertEquals($parentComment->id, $reply->parent_id);
    }

    public function test_replies_are_nested_correctly(): void
    {
        $rootComment = Comment::factory()
            ->forCommentable($this->project)
            ->create(['content' => 'Root comment']);

        $firstLevelReply = Comment::factory()
            ->reply($rootComment)
            ->create(['content' => 'First level reply']);

        $secondLevelReply = Comment::factory()
            ->reply($firstLevelReply)
            ->create(['content' => 'Second level reply']);

        $this->assertEquals($rootComment->id, $firstLevelReply->parent_id);
        $this->assertEquals($firstLevelReply->id, $secondLevelReply->parent_id);

        $rootComment->load('replies.replies');
        $this->assertCount(1, $rootComment->replies);
        $this->assertCount(1, $rootComment->replies->first()->replies);
    }

    public function test_deleted_parent_shows_replies(): void
    {
        $parentComment = Comment::factory()
            ->byUser($this->user)
            ->forCommentable($this->project)
            ->create(['content' => 'Parent comment']);

        $reply1 = Comment::factory()
            ->byUser($this->user)
            ->reply($parentComment)
            ->create(['content' => 'Reply 1']);

        $reply2 = Comment::factory()
            ->byUser($this->user)
            ->reply($parentComment)
            ->create(['content' => 'Reply 2']);

        $this->actingAs($this->user, 'sanctum')
            ->deleteJson("/api/v1/comments/{$parentComment->id}");

        $this->assertSoftDeleted('comments', ['id' => $parentComment->id]);

        $this->assertDatabaseHas('comments', [
            'id' => $reply1->id,
            'parent_id' => $parentComment->id,
            'deleted_at' => null,
        ]);

        $this->assertDatabaseHas('comments', [
            'id' => $reply2->id,
            'parent_id' => $parentComment->id,
            'deleted_at' => null,
        ]);
    }

    public function test_root_comments_are_fetched_with_replies(): void
    {
        $rootComment1 = Comment::factory()
            ->forCommentable($this->project)
            ->create(['content' => 'Root 1']);

        $rootComment2 = Comment::factory()
            ->forCommentable($this->project)
            ->create(['content' => 'Root 2']);

        Comment::factory()
            ->count(2)
            ->reply($rootComment1)
            ->create();

        Comment::factory()
            ->count(3)
            ->reply($rootComment2)
            ->create();

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson("/api/v1/comments?commentable_type=Project&commentable_id={$this->project->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'content',
                        'replies',
                    ],
                ],
            ]);

        $data = $response->json('data');
        $this->assertCount(2, $data);

        $root1Data = collect($data)->firstWhere('content', 'Root 1');
        $root2Data = collect($data)->firstWhere('content', 'Root 2');

        $this->assertNotNull($root1Data);
        $this->assertNotNull($root2Data);
        $this->assertCount(2, $root1Data['replies'] ?? []);
        $this->assertCount(3, $root2Data['replies'] ?? []);
    }
}
