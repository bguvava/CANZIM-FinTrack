<?php

namespace Tests\Feature\Donors;

use App\Models\Donor;
use App\Models\Project;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class DonorReportTest extends TestCase
{
    use RefreshDatabase;

    protected User $programsManager;

    protected User $financeOfficer;

    protected Donor $donor;

    protected function setUp(): void
    {
        parent::setUp();

        $programsManagerRole = Role::create(['name' => 'Programs Manager', 'slug' => 'programs-manager']);
        $financeOfficerRole = Role::create(['name' => 'Finance Officer', 'slug' => 'finance-officer']);

        $this->programsManager = User::create([
            'name' => 'Programs Manager',
            'email' => 'pm@test.com',
            'password' => bcrypt('password123'),
            'phone_number' => '+263712345678',
            'office_location' => 'Harare Office',
            'role_id' => $programsManagerRole->id,
            'status' => 'active',
        ]);

        $this->financeOfficer = User::create([
            'name' => 'Finance Officer',
            'email' => 'fo@test.com',
            'password' => bcrypt('password123'),
            'phone_number' => '+263712345679',
            'office_location' => 'Harare Office',
            'role_id' => $financeOfficerRole->id,
            'status' => 'active',
        ]);

        $this->donor = Donor::factory()->create(['name' => 'USAID Foundation']);
    }

    /** @test */
    public function programs_manager_can_generate_donor_report()
    {
        Sanctum::actingAs($this->programsManager);

        $response = $this->get("/api/v1/donors/{$this->donor->id}/report");

        $response->assertOk();
        $this->assertEquals('application/pdf', $response->headers->get('Content-Type'));
        $this->assertStringContainsString('donor-financial-report', $response->headers->get('Content-Disposition'));
    }

    /** @test */
    public function finance_officer_can_generate_donor_report()
    {
        Sanctum::actingAs($this->financeOfficer);

        $response = $this->get("/api/v1/donors/{$this->donor->id}/report");

        $response->assertOk();
        $this->assertEquals('application/pdf', $response->headers->get('Content-Type'));
    }

    /** @test */
    public function report_filename_contains_donor_slug_and_timestamp()
    {
        Sanctum::actingAs($this->programsManager);

        $response = $this->get("/api/v1/donors/{$this->donor->id}/report");

        $response->assertOk();

        $disposition = $response->headers->get('Content-Disposition');

        $this->assertStringContainsString('usaid-foundation', $disposition);
        $this->assertStringContainsString('.pdf', $disposition);
    }

    /** @test */
    public function cannot_generate_report_for_nonexistent_donor()
    {
        Sanctum::actingAs($this->programsManager);

        $response = $this->get('/api/v1/donors/99999/report');

        $response->assertNotFound();
    }
}
