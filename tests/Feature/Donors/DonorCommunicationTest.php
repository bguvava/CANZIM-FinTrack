<?php

namespace Tests\Feature\Donors;

use App\Models\Communication;
use App\Models\Donor;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class DonorCommunicationTest extends TestCase
{
    use RefreshDatabase;

    protected User $programsManager;

    protected Donor $donor;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');

        $programsManagerRole = Role::create(['name' => 'Programs Manager', 'slug' => 'programs-manager']);

        $this->programsManager = User::create([
            'name' => 'Programs Manager',
            'email' => 'pm@test.com',
            'password' => bcrypt('password123'),
            'phone_number' => '+263712345678',
            'office_location' => 'Harare Office',
            'role_id' => $programsManagerRole->id,
            'status' => 'active',
        ]);

        $this->donor = Donor::factory()->create();
    }

    /** @test */
    public function can_log_email_communication()
    {
        Sanctum::actingAs($this->programsManager);

        $communicationData = [
            'communicable_type' => 'App\Models\Donor',
            'communicable_id' => $this->donor->id,
            'type' => 'email',
            'subject' => 'Grant Application Follow-up',
            'notes' => 'Discussed next steps for grant application',
            'communication_date' => '2025-01-15 10:00:00',
        ];

        $response = $this->postJson('/api/v1/communications', $communicationData);

        $response->assertCreated()
            ->assertJson([
                'success' => true,
                'message' => 'Communication logged successfully',
            ]);

        $this->assertDatabaseHas('communications', [
            'communicable_type' => 'App\Models\Donor',
            'communicable_id' => $this->donor->id,
            'type' => 'email',
            'subject' => 'Grant Application Follow-up',
        ]);
    }

    /** @test */
    public function can_log_phone_call_communication()
    {
        Sanctum::actingAs($this->programsManager);

        $communicationData = [
            'communicable_type' => 'App\Models\Donor',
            'communicable_id' => $this->donor->id,
            'type' => 'phone_call',
            'subject' => 'Quarterly Update Call',
            'notes' => 'Provided project progress update',
            'communication_date' => now()->format('Y-m-d H:i:s'),
        ];

        $response = $this->postJson('/api/v1/communications', $communicationData);

        $response->assertCreated();

        $this->assertDatabaseHas('communications', [
            'type' => 'phone_call',
            'subject' => 'Quarterly Update Call',
        ]);
    }

    /** @test */
    public function can_log_meeting_communication()
    {
        Sanctum::actingAs($this->programsManager);

        $communicationData = [
            'communicable_type' => 'App\Models\Donor',
            'communicable_id' => $this->donor->id,
            'type' => 'meeting',
            'subject' => 'Annual Review Meeting',
            'notes' => 'Reviewed 2024 achievements and 2025 plans',
            'communication_date' => now()->format('Y-m-d H:i:s'),
            'next_action_date' => now()->addDays(30)->format('Y-m-d'),
            'next_action_notes' => 'Schedule follow-up meeting',
        ];

        $response = $this->postJson('/api/v1/communications', $communicationData);

        $response->assertCreated();

        $this->assertDatabaseHas('communications', [
            'type' => 'meeting',
            'next_action_notes' => 'Schedule follow-up meeting',
        ]);
    }

    /** @test */
    public function can_attach_file_to_communication()
    {
        Sanctum::actingAs($this->programsManager);

        $file = UploadedFile::fake()->create('meeting-notes.pdf', 1024);

        $communicationData = [
            'communicable_type' => 'App\Models\Donor',
            'communicable_id' => $this->donor->id,
            'type' => 'email',
            'subject' => 'Meeting Notes',
            'notes' => 'Attached meeting notes',
            'communication_date' => now()->format('Y-m-d H:i:s'),
            'attachment' => $file,
        ];

        $response = $this->postJson('/api/v1/communications', $communicationData);

        $response->assertCreated();

        $communication = Communication::first();
        $this->assertNotNull($communication->attachment_path);
        Storage::disk('public')->assertExists($communication->attachment_path);
    }

    /** @test */
    public function attachment_must_be_under_5mb()
    {
        Sanctum::actingAs($this->programsManager);

        $file = UploadedFile::fake()->create('large-file.pdf', 6000);

        $communicationData = [
            'communicable_type' => 'App\Models\Donor',
            'communicable_id' => $this->donor->id,
            'type' => 'email',
            'subject' => 'Test',
            'notes' => 'Test notes',
            'communication_date' => now()->format('Y-m-d H:i:s'),
            'attachment' => $file,
        ];

        $response = $this->postJson('/api/v1/communications', $communicationData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['attachment']);
    }

    /** @test */
    public function communication_type_must_be_valid()
    {
        Sanctum::actingAs($this->programsManager);

        $communicationData = [
            'communicable_type' => 'App\Models\Donor',
            'communicable_id' => $this->donor->id,
            'type' => 'invalid_type',
            'subject' => 'Test',
            'notes' => 'Test notes',
            'communication_date' => now()->format('Y-m-d H:i:s'),
        ];

        $response = $this->postJson('/api/v1/communications', $communicationData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['type']);
    }

    /** @test */
    public function subject_is_required()
    {
        Sanctum::actingAs($this->programsManager);

        $communicationData = [
            'communicable_type' => 'App\Models\Donor',
            'communicable_id' => $this->donor->id,
            'type' => 'email',
            'notes' => 'Test notes',
            'communication_date' => now()->format('Y-m-d H:i:s'),
        ];

        $response = $this->postJson('/api/v1/communications', $communicationData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['subject']);
    }

    /** @test */
    public function donor_can_have_multiple_communications()
    {
        Sanctum::actingAs($this->programsManager);

        // Create 3 communications
        for ($i = 0; $i < 3; $i++) {
            Communication::create([
                'communicable_type' => 'App\Models\Donor',
                'communicable_id' => $this->donor->id,
                'type' => 'email',
                'subject' => "Email {$i}",
                'notes' => "Notes {$i}",
                'communication_date' => now()->subDays($i),
                'created_by' => $this->programsManager->id,
            ]);
        }

        $this->donor->load('communications');

        $this->assertCount(3, $this->donor->communications);
    }
}
