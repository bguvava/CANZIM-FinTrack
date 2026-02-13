<?php

declare(strict_types=1);

namespace Tests\Feature\Comments;

use App\Models\Comment;
use App\Models\Project;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class CommentTest extends TestCase
{
    use RefreshDatabase;

    protected User $programsManager;

    protected User $projectOfficer;

    protected Role $programsManagerRole;

    protected Role $projectOfficerRole;

    protected Project $project;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');

        // Create roles
        $this->programsManagerRole = Role::create([
            'name' => 'Programs Manager',
            'slug' => 'programs-manager',
        ]);

        $this->projectOfficerRole = Role::create([
            'name' => 'Project Officer',
            'slug' => 'project-officer',
        ]);

        // Create test users
        $this->programsManager = User::create([
            'name' => 'Programs Manager',
            'email' => 'pm@test.com',
            'password' => bcrypt('password123'),
            'phone_number' => '+263712345678',
            'office_location' => 'Harare Office',
            'role_id' => $this->programsManagerRole->id,
            'status' => 'active',
        ]);

        $this->projectOfficer = User::create([
            'name' => 'Project Officer',
            'email' => 'po@test.com',
            'password' => bcrypt('password123'),
            'phone_number' => '+263712345679',
            'office_location' => 'Bulawayo Office',
            'role_id' => $this->projectOfficerRole->id,
            'status' => 'active',
        ]);

        // Create a test project
        $this->project = Project::create([
            'name' => 'Test Project',
            'code' => 'PRJ-001',
            'description' => 'Test project for comments',
            'start_date' => now(),
            'end_date' => now()->addMonths(6),
            'status' => 'active',
            'office_location' => 'Harare',
            'created_by' => $this->programsManager->id,
        ]);
    }

    /** @test */
    public function user_can_create_a_comment()
    {
        Sanctum::actingAs($this->programsManager);

        $response = $this->postJson('/api/v1/comments', [
            'commentable_type' => 'Project',
            'commentable_id' => $this->project->id,
            'content' => 'This is a test comment',
        ]);

        $response->assertCreated()
            ->assertJsonStructure([
                'message',
                'data' => [
                    'id',
                    'content',
                    'user',
                    'created_at',
                ],
            ]);

        $this->assertDatabaseHas('comments', [
            'commentable_type' => 'Project',
            'commentable_id' => $this->project->id,
            'user_id' => $this->programsManager->id,
            'content' => 'This is a test comment',
        ]);
    }

    /** @test */
    public function user_can_get_comments_for_an_entity()
    {
        Sanctum::actingAs($this->programsManager);

        // Create multiple comments
        Comment::create([
            'commentable_type' => 'Project',
            'commentable_id' => $this->project->id,
            'user_id' => $this->programsManager->id,
            'content' => 'First comment',
        ]);

        Comment::create([
            'commentable_type' => 'Project',
            'commentable_id' => $this->project->id,
            'user_id' => $this->projectOfficer->id,
            'content' => 'Second comment',
        ]);

        $response = $this->getJson('/api/v1/comments?commentable_type=Project&commentable_id='.$this->project->id);

        $response->assertOk()
            ->assertJsonStructure([
                'data',
            ]);
    }

    /** @test */
    public function user_can_update_their_own_comment()
    {
        Sanctum::actingAs($this->programsManager);

        $comment = Comment::create([
            'commentable_type' => 'Project',
            'commentable_id' => $this->project->id,
            'user_id' => $this->programsManager->id,
            'content' => 'Original content',
        ]);

        $response = $this->putJson("/api/v1/comments/{$comment->id}", [
            'content' => 'Updated content',
        ]);

        $response->assertOk()
            ->assertJson([
                'message' => 'Comment updated successfully',
            ]);

        $this->assertDatabaseHas('comments', [
            'id' => $comment->id,
            'content' => 'Updated content',
        ]);
    }

    /** @test */
    public function user_cannot_update_others_comment()
    {
        Sanctum::actingAs($this->projectOfficer);

        $comment = Comment::create([
            'commentable_type' => 'Project',
            'commentable_id' => $this->project->id,
            'user_id' => $this->programsManager->id,
            'content' => 'Original content',
        ]);

        $response = $this->putJson("/api/v1/comments/{$comment->id}", [
            'content' => 'Updated content',
        ]);

        $response->assertForbidden();

        $this->assertDatabaseHas('comments', [
            'id' => $comment->id,
            'content' => 'Original content',
        ]);
    }

    /** @test */
    public function user_can_delete_their_own_comment()
    {
        Sanctum::actingAs($this->programsManager);

        $comment = Comment::create([
            'commentable_type' => 'Project',
            'commentable_id' => $this->project->id,
            'user_id' => $this->programsManager->id,
            'content' => 'Comment to delete',
        ]);

        $response = $this->deleteJson("/api/v1/comments/{$comment->id}");

        $response->assertStatus(200)
            ->assertJson(['success' => true]);

        $this->assertSoftDeleted('comments', [
            'id' => $comment->id,
        ]);
    }

    /** @test */
    public function comment_content_is_required()
    {
        Sanctum::actingAs($this->programsManager);

        $response = $this->postJson('/api/v1/comments', [
            'commentable_type' => 'Project',
            'commentable_id' => $this->project->id,
            'content' => '',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['content']);
    }

    /** @test */
    public function unauthenticated_user_cannot_create_comment()
    {
        $response = $this->postJson('/api/v1/comments', [
            'commentable_type' => 'Project',
            'commentable_id' => $this->project->id,
            'content' => 'Test comment',
        ]);

        $response->assertUnauthorized();
    }
}
