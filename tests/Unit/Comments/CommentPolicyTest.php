<?php

namespace Tests\Unit\Comments;

use App\Models\Comment;
use App\Models\Project;
use App\Models\User;
use App\Policies\CommentPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CommentPolicyTest extends TestCase
{
    use RefreshDatabase;

    protected CommentPolicy $policy;

    protected User $user;

    protected User $otherUser;

    protected Project $project;

    protected function setUp(): void
    {
        parent::setUp();

        $this->policy = new CommentPolicy;
        $this->user = User::factory()->create();
        $this->otherUser = User::factory()->create();
        $this->project = Project::factory()->create();
    }

    public function test_user_can_view_any_comments(): void
    {
        $result = $this->policy->viewAny($this->user);

        $this->assertTrue($result);
    }

    public function test_user_can_view_comment(): void
    {
        $comment = Comment::factory()
            ->forCommentable($this->project)
            ->create();

        $result = $this->policy->view($this->user, $comment);

        $this->assertTrue($result);
    }

    public function test_user_can_create_comment(): void
    {
        $result = $this->policy->create($this->user);

        $this->assertTrue($result);
    }

    public function test_user_can_update_own_comment(): void
    {
        $comment = Comment::factory()
            ->byUser($this->user)
            ->forCommentable($this->project)
            ->create();

        $result = $this->policy->update($this->user, $comment);

        $this->assertTrue($result);
    }

    public function test_user_cannot_update_others_comment(): void
    {
        $comment = Comment::factory()
            ->byUser($this->otherUser)
            ->forCommentable($this->project)
            ->create();

        $result = $this->policy->update($this->user, $comment);

        $this->assertFalse($result);
    }

    public function test_user_can_delete_own_comment(): void
    {
        $comment = Comment::factory()
            ->byUser($this->user)
            ->forCommentable($this->project)
            ->create();

        $result = $this->policy->delete($this->user, $comment);

        $this->assertTrue($result);
    }

    public function test_user_cannot_delete_others_comment(): void
    {
        $comment = Comment::factory()
            ->byUser($this->otherUser)
            ->forCommentable($this->project)
            ->create();

        $result = $this->policy->delete($this->user, $comment);

        $this->assertFalse($result);
    }
}
