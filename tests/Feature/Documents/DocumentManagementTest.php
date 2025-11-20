<?php

namespace Tests\Feature\Documents;

use App\Models\Document;
use App\Models\Project;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DocumentManagementTest extends TestCase
{
    use RefreshDatabase;

    private User $programsManager;

    private User $financeOfficer;

    private User $projectOfficer;

    private Project $project;

    protected function setUp(): void
    {
        parent::setUp();

        // Create roles
        $pmRole = Role::create(['name' => 'Programs Manager', 'slug' => 'programs-manager']);
        $foRole = Role::create(['name' => 'Finance Officer', 'slug' => 'finance-officer']);
        $poRole = Role::create(['name' => 'Project Officer', 'slug' => 'project-officer']);

        // Create users with different roles
        $this->programsManager = User::factory()->create(['role_id' => $pmRole->id]);
        $this->financeOfficer = User::factory()->create(['role_id' => $foRole->id]);
        $this->projectOfficer = User::factory()->create(['role_id' => $poRole->id]);

        // Create a project
        $this->project = Project::factory()->create([
            'created_by' => $this->programsManager->id,
        ]);

        // Fake storage
        Storage::fake('public');
    }

    /** @test */
    public function test_can_upload_pdf_document()
    {
        $file = UploadedFile::fake()->create('document.pdf', 1024, 'application/pdf');

        $response = $this->actingAs($this->programsManager, 'sanctum')
            ->postJson('/api/v1/documents', [
                'file' => $file,
                'title' => 'Test PDF Document',
                'description' => 'This is a test PDF document',
                'category' => 'budget-documents',
                'documentable_type' => 'App\\Models\\Project',
                'documentable_id' => $this->project->id,
            ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'message',
                'data' => [
                    'id',
                    'title',
                    'description',
                    'category',
                    'file_name',
                    'file_type',
                    'file_size',
                    'formatted_file_size',
                    'version_number',
                    'uploader' => ['id', 'name', 'email'],
                ],
            ])
            ->assertJson([
                'data' => [
                    'title' => 'Test PDF Document',
                    'category' => 'budget-documents',
                    'version_number' => 1,
                ],
            ]);

        // Assert file was stored
        $document = Document::first();
        Storage::disk('public')->assertExists($document->file_path);

        // Assert database record created
        $this->assertDatabaseHas('documents', [
            'title' => 'Test PDF Document',
            'category' => 'budget-documents',
            'documentable_type' => 'App\\Models\\Project',
            'documentable_id' => $this->project->id,
            'uploaded_by' => $this->programsManager->id,
        ]);
    }

    /** @test */
    public function test_can_upload_word_document()
    {
        $file = UploadedFile::fake()->create('document.docx', 500, 'application/vnd.openxmlformats-officedocument.wordprocessingml.document');

        $response = $this->actingAs($this->programsManager, 'sanctum')
            ->postJson('/api/v1/documents', [
                'file' => $file,
                'title' => 'Test Word Document',
                'description' => 'This is a test Word document',
                'category' => 'project-reports',
                'documentable_type' => 'App\\Models\\Project',
                'documentable_id' => $this->project->id,
            ]);

        $response->assertStatus(201);

        $this->assertDatabaseHas('documents', [
            'title' => 'Test Word Document',
            'category' => 'project-reports',
        ]);
    }

    /** @test */
    public function test_can_upload_excel_document()
    {
        $file = UploadedFile::fake()->create('spreadsheet.xlsx', 800, 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');

        $response = $this->actingAs($this->financeOfficer, 'sanctum')
            ->postJson('/api/v1/documents', [
                'file' => $file,
                'title' => 'Budget Spreadsheet',
                'description' => null,
                'category' => 'budget-documents',
                'documentable_type' => 'App\\Models\\Project',
                'documentable_id' => $this->project->id,
            ]);

        $response->assertStatus(201);
    }

    /** @test */
    public function test_can_upload_image_document()
    {
        $file = UploadedFile::fake()->image('receipt.jpg')->size(300);

        $response = $this->actingAs($this->projectOfficer, 'sanctum')
            ->postJson('/api/v1/documents', [
                'file' => $file,
                'title' => 'Receipt Image',
                'description' => 'Expense receipt',
                'category' => 'expense-receipts',
                'documentable_type' => 'App\\Models\\Project',
                'documentable_id' => $this->project->id,
            ]);

        $response->assertStatus(201);
    }

    /** @test */
    public function test_document_validation_rejects_invalid_file_types()
    {
        $file = UploadedFile::fake()->create('virus.exe', 100);

        $response = $this->actingAs($this->programsManager, 'sanctum')
            ->postJson('/api/v1/documents', [
                'file' => $file,
                'title' => 'Invalid File',
                'category' => 'other',
                'documentable_type' => 'App\\Models\\Project',
                'documentable_id' => $this->project->id,
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('file');
    }

    /** @test */
    public function test_document_validation_rejects_large_files()
    {
        $file = UploadedFile::fake()->create('large.pdf', 6000); // 6MB > 5MB limit

        $response = $this->actingAs($this->programsManager, 'sanctum')
            ->postJson('/api/v1/documents', [
                'file' => $file,
                'title' => 'Large File',
                'category' => 'other',
                'documentable_type' => 'App\\Models\\Project',
                'documentable_id' => $this->project->id,
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('file');
    }

    /** @test */
    public function test_document_validation_requires_title()
    {
        $file = UploadedFile::fake()->create('document.pdf', 100);

        $response = $this->actingAs($this->programsManager, 'sanctum')
            ->postJson('/api/v1/documents', [
                'file' => $file,
                'category' => 'other',
                'documentable_type' => 'App\\Models\\Project',
                'documentable_id' => $this->project->id,
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('title');
    }

    /** @test */
    public function test_document_validation_requires_valid_category()
    {
        $file = UploadedFile::fake()->create('document.pdf', 100);

        $response = $this->actingAs($this->programsManager, 'sanctum')
            ->postJson('/api/v1/documents', [
                'file' => $file,
                'title' => 'Document',
                'category' => 'invalid-category',
                'documentable_type' => 'App\\Models\\Project',
                'documentable_id' => $this->project->id,
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('category');
    }

    /** @test */
    public function test_can_list_documents()
    {
        // Create multiple documents
        Document::factory()->count(3)->create([
            'documentable_type' => 'App\\Models\\Project',
            'documentable_id' => $this->project->id,
            'uploaded_by' => $this->programsManager->id,
        ]);

        $response = $this->actingAs($this->programsManager, 'sanctum')
            ->getJson('/api/v1/documents?documentable_type=App\\Models\\Project&documentable_id=' . $this->project->id);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'title',
                        'description',
                        'category',
                        'file_name',
                        'file_size',
                        'formatted_file_size',
                        'uploader',
                        'created_at',
                    ],
                ],
                'meta' => [
                    'current_page',
                    'last_page',
                    'per_page',
                    'total',
                ],
            ]);

        $this->assertEquals(3, count($response->json('data')));
    }

    /** @test */
    public function test_can_search_documents()
    {
        Document::factory()->create([
            'title' => 'Budget Report 2024',
            'documentable_type' => 'App\\Models\\Project',
            'documentable_id' => $this->project->id,
            'uploaded_by' => $this->programsManager->id,
        ]);

        Document::factory()->create([
            'title' => 'Expense Receipt',
            'documentable_type' => 'App\\Models\\Project',
            'documentable_id' => $this->project->id,
            'uploaded_by' => $this->programsManager->id,
        ]);

        $response = $this->actingAs($this->programsManager, 'sanctum')
            ->getJson('/api/v1/documents?search=Budget');

        $response->assertStatus(200);
        $data = $response->json('data');
        $this->assertEquals(1, count($data));
        $this->assertEquals('Budget Report 2024', $data[0]['title']);
    }

    /** @test */
    public function test_can_filter_documents_by_category()
    {
        Document::factory()->create([
            'category' => 'budget-documents',
            'documentable_type' => 'App\\Models\\Project',
            'documentable_id' => $this->project->id,
            'uploaded_by' => $this->programsManager->id,
        ]);

        Document::factory()->create([
            'category' => 'expense-receipts',
            'documentable_type' => 'App\\Models\\Project',
            'documentable_id' => $this->project->id,
            'uploaded_by' => $this->programsManager->id,
        ]);

        $response = $this->actingAs($this->programsManager, 'sanctum')
            ->getJson('/api/v1/documents?category=budget-documents');

        $response->assertStatus(200);
        $data = $response->json('data');
        $this->assertEquals(1, count($data));
        $this->assertEquals('budget-documents', $data[0]['category']);
    }

    /** @test */
    public function test_can_view_single_document()
    {
        $document = Document::factory()->create([
            'documentable_type' => 'App\\Models\\Project',
            'documentable_id' => $this->project->id,
            'uploaded_by' => $this->programsManager->id,
        ]);

        $response = $this->actingAs($this->programsManager, 'sanctum')
            ->getJson("/api/v1/documents/{$document->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'title',
                    'description',
                    'category',
                    'uploader',
                    'versions_count',
                ],
            ])
            ->assertJson([
                'data' => [
                    'id' => $document->id,
                    'title' => $document->title,
                ],
            ]);
    }

    /** @test */
    public function test_can_update_document_metadata()
    {
        $document = Document::factory()->create([
            'title' => 'Original Title',
            'description' => 'Original Description',
            'category' => 'other',
            'documentable_type' => 'App\\Models\\Project',
            'documentable_id' => $this->project->id,
            'uploaded_by' => $this->programsManager->id,
        ]);

        $response = $this->actingAs($this->programsManager, 'sanctum')
            ->putJson("/api/v1/documents/{$document->id}", [
                'title' => 'Updated Title',
                'description' => 'Updated Description',
                'category' => 'budget-documents',
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'title' => 'Updated Title',
                    'description' => 'Updated Description',
                    'category' => 'budget-documents',
                ],
            ]);

        $this->assertDatabaseHas('documents', [
            'id' => $document->id,
            'title' => 'Updated Title',
            'description' => 'Updated Description',
            'category' => 'budget-documents',
        ]);
    }

    /** @test */
    public function test_can_delete_document()
    {
        $document = Document::factory()->create([
            'documentable_type' => 'App\\Models\\Project',
            'documentable_id' => $this->project->id,
            'uploaded_by' => $this->programsManager->id,
        ]);

        $response = $this->actingAs($this->programsManager, 'sanctum')
            ->deleteJson("/api/v1/documents/{$document->id}");

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Document deleted successfully',
            ]);

        $this->assertSoftDeleted('documents', [
            'id' => $document->id,
        ]);
    }

    /** @test */
    public function test_unauthorized_user_cannot_delete_document()
    {
        $document = Document::factory()->create([
            'documentable_type' => 'App\\Models\\Project',
            'documentable_id' => $this->project->id,
            'uploaded_by' => $this->programsManager->id,
        ]);

        // Use the existing projectOfficer who doesn't own the document
        $response = $this->actingAs($this->projectOfficer, 'sanctum')
            ->deleteJson("/api/v1/documents/{$document->id}");

        $response->assertStatus(403);
    }

    /** @test */
    public function test_can_download_document()
    {
        Storage::fake('public');

        $document = Document::factory()->create([
            'file_path' => 'documents/2024/11/test.pdf',
            'file_name' => 'test.pdf',
            'file_type' => 'application/pdf',
            'documentable_type' => 'App\\Models\\Project',
            'documentable_id' => $this->project->id,
            'uploaded_by' => $this->programsManager->id,
        ]);

        // Create fake file
        Storage::disk('public')->put($document->file_path, 'PDF content');

        $response = $this->actingAs($this->programsManager, 'sanctum')
            ->get("/api/v1/documents/{$document->id}/download");

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/pdf');
    }

    /** @test */
    public function test_can_get_document_categories()
    {
        $response = $this->actingAs($this->programsManager, 'sanctum')
            ->getJson('/api/v1/documents/categories');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'name',
                        'slug',
                        'description',
                        'is_active',
                        'sort_order',
                    ],
                ],
            ]);

        // Assert default categories exist
        $data = $response->json('data');
        $this->assertGreaterThanOrEqual(5, count($data));
    }
}
