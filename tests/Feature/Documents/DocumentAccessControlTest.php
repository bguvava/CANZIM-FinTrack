<?php

namespace Tests\Feature\Documents;

use App\Models\Document;
use App\Models\Project;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DocumentAccessControlTest extends TestCase
{
    use RefreshDatabase;

    private User $programsManager;

    private User $owner;

    private User $otherUser;

    private Project $project;

    protected function setUp(): void
    {
        parent::setUp();

        // Create roles
        $pmRole = Role::create(['name' => 'Programs Manager', 'slug' => 'programs-manager']);
        $poRole = Role::create(['name' => 'Project Officer', 'slug' => 'project-officer']);

        // Create users
        $this->programsManager = User::factory()->create(['role_id' => $pmRole->id]);
        $this->owner = User::factory()->create(['role_id' => $poRole->id]);
        $this->otherUser = User::factory()->create(['role_id' => $poRole->id]);

        // Create a project
        $this->project = Project::factory()->create(['created_by' => $this->owner->id]);
    }

    /** @test */
    public function test_user_can_view_document_they_have_access_to()
    {
        $document = Document::factory()->create([
            'documentable_type' => 'App\\Models\\Project',
            'documentable_id' => $this->project->id,
            'uploaded_by' => $this->owner->id,
        ]);

        $response = $this->actingAs($this->owner, 'sanctum')
            ->getJson("/api/v1/documents/{$document->id}");

        $response->assertStatus(200);
    }

    /** @test */
    public function test_user_can_view_project_document_even_if_not_project_creator()
    {
        $document = Document::factory()->create([
            'documentable_type' => 'App\\Models\\Project',
            'documentable_id' => $this->project->id,
            'uploaded_by' => $this->owner->id,
        ]);

        $response = $this->actingAs($this->otherUser, 'sanctum')
            ->getJson("/api/v1/documents/{$document->id}");

        $response->assertStatus(200);
    }

    /** @test */
    public function test_user_can_update_their_own_document()
    {
        $document = Document::factory()->create([
            'documentable_type' => 'App\\Models\\Project',
            'documentable_id' => $this->project->id,
            'uploaded_by' => $this->owner->id,
        ]);

        $response = $this->actingAs($this->owner, 'sanctum')
            ->putJson("/api/v1/documents/{$document->id}", [
                'title' => 'Updated Title',
            ]);

        $response->assertStatus(200);
    }

    /** @test */
    public function test_user_cannot_update_others_document()
    {
        $document = Document::factory()->create([
            'documentable_type' => 'App\\Models\\Project',
            'documentable_id' => $this->project->id,
            'uploaded_by' => $this->owner->id,
        ]);

        $response = $this->actingAs($this->otherUser, 'sanctum')
            ->putJson("/api/v1/documents/{$document->id}", [
                'title' => 'Updated Title',
            ]);

        $response->assertStatus(403);
    }

    /** @test */
    public function test_programs_manager_can_update_any_document()
    {
        $document = Document::factory()->create([
            'documentable_type' => 'App\\Models\\Project',
            'documentable_id' => $this->project->id,
            'uploaded_by' => $this->owner->id,
        ]);

        $response = $this->actingAs($this->programsManager, 'sanctum')
            ->putJson("/api/v1/documents/{$document->id}", [
                'title' => 'Programs Manager Update',
            ]);

        $response->assertStatus(200);
    }

    /** @test */
    public function test_user_can_delete_their_own_document()
    {
        $document = Document::factory()->create([
            'documentable_type' => 'App\\Models\\Project',
            'documentable_id' => $this->project->id,
            'uploaded_by' => $this->owner->id,
        ]);

        $response = $this->actingAs($this->owner, 'sanctum')
            ->deleteJson("/api/v1/documents/{$document->id}");

        $response->assertStatus(200);
    }

    /** @test */
    public function test_user_cannot_delete_others_document()
    {
        $document = Document::factory()->create([
            'documentable_type' => 'App\\Models\\Project',
            'documentable_id' => $this->project->id,
            'uploaded_by' => $this->owner->id,
        ]);

        $response = $this->actingAs($this->otherUser, 'sanctum')
            ->deleteJson("/api/v1/documents/{$document->id}");

        $response->assertStatus(403);
    }

    /** @test */
    public function test_programs_manager_can_delete_any_document()
    {
        $document = Document::factory()->create([
            'documentable_type' => 'App\\Models\\Project',
            'documentable_id' => $this->project->id,
            'uploaded_by' => $this->owner->id,
        ]);

        $response = $this->actingAs($this->programsManager, 'sanctum')
            ->deleteJson("/api/v1/documents/{$document->id}");

        $response->assertStatus(200);
    }

    /** @test */
    public function test_unauthenticated_user_cannot_access_documents()
    {
        $document = Document::factory()->create([
            'documentable_type' => 'App\\Models\\Project',
            'documentable_id' => $this->project->id,
            'uploaded_by' => $this->owner->id,
        ]);

        $response = $this->getJson("/api/v1/documents/{$document->id}");

        $response->assertStatus(401);
    }
}
