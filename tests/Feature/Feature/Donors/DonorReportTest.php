<?php

namespace Tests\Feature\Feature\Donors;

use Tests\TestCase;

class DonorReportTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function test_example(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
    }
}
