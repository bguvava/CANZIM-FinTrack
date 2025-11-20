<?php

namespace Tests\Feature\Documents;

use App\Models\Document;
use App\Models\DocumentVersion;
use App\Models\Project;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DocumentVersioningTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private Project $project;

    protected function setUp(): void
    {
        parent::setUp();

        // Create role
        $role = Role::create(['name' => 'Project Officer', 'slug' => 'project-officer']);

        // Create a user
        $this->user = User::factory()->create(['role_id' => $role->id]);

        // Create a project
        $this->project = Project::factory()->create(['created_by' => $this->user->id]);

        // Fake storage
        Storage::fake('public');
    }

    /** @test */
    public function test_can_replace_document_with_new_version()
    {
        // Create initial document
        $initialFile = UploadedFile::fake()->create('document-v1.pdf', 1024);

        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/v1/documents', [
                'file' => $initialFile,
                'title' => 'Project Plan',
                'category' => 'project-reports',
                'documentable_type' => 'App\\Models\\Project',
                'documentable_id' => $this->project->id,
            ]);

        $documentId = $response->json('data.id');
        $this->assertEquals(1, $response->json('data.version_number'));

        // Replace with new version
        $newFile = UploadedFile::fake()->create('document-v2.pdf', 1500);

        $replaceResponse = $this->actingAs($this->user, 'sanctum')
            ->postJson("/api/v1/documents/{$documentId}/replace", [
                'file' => $newFile,
            ]);

        $replaceResponse->assertStatus(200)
            ->assertJson([
                'message' => 'Document replaced successfully',
                'data' => [
                    'id' => $documentId,
                    'title' => 'Project Plan',
                    'version_number' => 2,
                ],
            ]);

        // Assert old version archived
        $this->assertDatabaseHas('document_versions', [
            'document_id' => $documentId,
            'version_number' => 1,
            'replaced_by' => $this->user->id,
        ]);

        // Assert current document updated
        $this->assertDatabaseHas('documents', [
            'id' => $documentId,
            'version_number' => 2,
        ]);
    }

    /** @test */
    public function test_can_view_document_version_history()
    {
        $document = Document::factory()->create([
            'version_number' => 3,
            'documentable_type' => 'App\\Models\\Project',
            'documentable_id' => $this->project->id,
            'uploaded_by' => $this->user->id,
        ]);

        // Create version history
        DocumentVersion::factory()->create([
            'document_id' => $document->id,
            'version_number' => 1,
            'replaced_by' => $this->user->id,
        ]);

        DocumentVersion::factory()->create([
            'document_id' => $document->id,
            'version_number' => 2,
            'replaced_by' => $this->user->id,
        ]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson("/api/v1/documents/{$document->id}/versions");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'version_number',
                        'file_name',
                        'file_size',
                        'replaced_by',
                        'replaced_at',
                    ],
                ],
            ]);

        $this->assertEquals(2, count($response->json('data')));
    }

    /** @test */
    public function test_version_number_increments_correctly()
    {
        $document = Document::factory()->create([
            'version_number' => 1,
            'documentable_type' => 'App\\Models\\Project',
            'documentable_id' => $this->project->id,
            'uploaded_by' => $this->user->id,
        ]);

        // Replace 3 times
        for ($i = 2; $i <= 4; $i++) {
            $file = UploadedFile::fake()->create("document-v{$i}.pdf", 1000);

            $this->actingAs($this->user, 'sanctum')
                ->postJson("/api/v1/documents/{$document->id}/replace", [
                    'file' => $file,
                ]);

            $document->refresh();
            $this->assertEquals($i, $document->version_number);
        }

        // Assert 3 versions in history (versions 1, 2, 3)
        $this->assertEquals(3, DocumentVersion::where('document_id', $document->id)->count());
    }

    /** @test */
    public function test_old_versions_retain_original_file_info()
    {
        $initialFile = UploadedFile::fake()->create('original.pdf', 1024);

        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/v1/documents', [
                'file' => $initialFile,
                'title' => 'Document',
                'category' => 'other',
                'documentable_type' => 'App\\Models\\Project',
                'documentable_id' => $this->project->id,
            ]);

        $documentId = $response->json('data.id');
        $originalFileName = $response->json('data.file_name');
        $originalFileSize = $response->json('data.file_size');

        // Replace with different file
        $newFile = UploadedFile::fake()->create('updated.pdf', 2048);

        $this->actingAs($this->user, 'sanctum')
            ->postJson("/api/v1/documents/{$documentId}/replace", [
                'file' => $newFile,
            ]);

        // Check version history preserves original file info
        $version = DocumentVersion::where('document_id', $documentId)
            ->where('version_number', 1)
            ->first();

        $this->assertNotNull($version);
        $this->assertEquals('original.pdf', $version->file_name);
        $this->assertEquals($originalFileSize, $version->file_size);
    }
}
