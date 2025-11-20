<?php

namespace Tests\Feature\Comments;

use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AttachmentTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected Project $project;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->project = Project::factory()->create();

        Storage::fake('public');
    }

    public function test_can_upload_pdf_attachment(): void
    {
        $pdfFile = UploadedFile::fake()->create('document.pdf', 500, 'application/pdf');

        $commentData = [
            'commentable_type' => 'Project',
            'commentable_id' => $this->project->id,
            'content' => 'Comment with PDF attachment.',
            'attachments' => [$pdfFile],
        ];

        $response = $this->actingAs($this->user, 'sanctum')
            ->post('/api/v1/comments', $commentData);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'data' => [
                    'attachments' => [
                        '*' => [
                            'id',
                            'file_name',
                            'mime_type',
                            'file_size',
                        ],
                    ],
                ],
            ]);

        $attachmentData = $response->json('data.attachments.0');
        $this->assertEquals('application/pdf', $attachmentData['mime_type']);
        $this->assertStringEndsWith('.pdf', $attachmentData['file_name']);
    }

    public function test_can_upload_image_attachment(): void
    {
        $imageFile = UploadedFile::fake()->image('photo.jpg', 800, 600);

        $commentData = [
            'commentable_type' => 'Project',
            'commentable_id' => $this->project->id,
            'content' => 'Comment with image attachment.',
            'attachments' => [$imageFile],
        ];

        $response = $this->actingAs($this->user, 'sanctum')
            ->post('/api/v1/comments', $commentData);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'data' => [
                    'attachments' => [
                        '*' => [
                            'id',
                            'file_name',
                            'mime_type',
                        ],
                    ],
                ],
            ]);

        $attachmentData = $response->json('data.attachments.0');
        $this->assertStringContainsString('image/', $attachmentData['mime_type']);
    }

    public function test_attachment_validation_rejects_invalid_types(): void
    {
        $invalidFile = UploadedFile::fake()->create('script.exe', 100, 'application/x-msdownload');

        $commentData = [
            'commentable_type' => 'Project',
            'commentable_id' => $this->project->id,
            'content' => 'Comment with invalid attachment.',
            'attachments' => [$invalidFile],
        ];

        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/v1/comments', $commentData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('attachments.0');
    }

    public function test_attachment_validation_rejects_large_files(): void
    {
        $largeFile = UploadedFile::fake()->create('large.pdf', 6000, 'application/pdf');

        $commentData = [
            'commentable_type' => 'Project',
            'commentable_id' => $this->project->id,
            'content' => 'Comment with large attachment.',
            'attachments' => [$largeFile],
        ];

        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/v1/comments', $commentData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('attachments.0');
    }

    public function test_maximum_three_attachments_allowed(): void
    {
        $file1 = UploadedFile::fake()->create('doc1.pdf', 100, 'application/pdf');
        $file2 = UploadedFile::fake()->create('doc2.pdf', 100, 'application/pdf');
        $file3 = UploadedFile::fake()->create('doc3.pdf', 100, 'application/pdf');
        $file4 = UploadedFile::fake()->create('doc4.pdf', 100, 'application/pdf');

        $commentData = [
            'commentable_type' => 'Project',
            'commentable_id' => $this->project->id,
            'content' => 'Comment with too many attachments.',
            'attachments' => [$file1, $file2, $file3, $file4],
        ];

        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/v1/comments', $commentData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('attachments');
    }

    public function test_can_download_attachment(): void
    {
        $pdfFile = UploadedFile::fake()->create('document.pdf', 500, 'application/pdf');

        $commentData = [
            'commentable_type' => 'Project',
            'commentable_id' => $this->project->id,
            'content' => 'Comment with downloadable attachment.',
            'attachments' => [$pdfFile],
        ];

        $createResponse = $this->actingAs($this->user, 'sanctum')
            ->post('/api/v1/comments', $commentData);

        $createResponse->assertStatus(201);

        $attachmentId = $createResponse->json('data.attachments.0.id');
        $this->assertNotNull($attachmentId);

        $downloadResponse = $this->actingAs($this->user, 'sanctum')
            ->getJson("/api/v1/comments/attachments/{$attachmentId}/download");

        $downloadResponse->assertStatus(200);
    }
}
