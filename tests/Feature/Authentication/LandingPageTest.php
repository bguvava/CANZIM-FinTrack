<?php

namespace Tests\Feature\Authentication;

use Tests\TestCase;

class LandingPageTest extends TestCase
{
    public function test_landing_page_loads_successfully(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
    }

    public function test_landing_page_contains_vue_mount_point(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200)
            ->assertSee('id="landing-page"', false);
    }

    public function test_landing_page_includes_csrf_token(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200)
            ->assertSee('csrf-token', false);
    }

    public function test_landing_page_includes_vite_assets(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200)
            ->assertSee('resources/css/app.css', false)
            ->assertSee('resources/js/app.js', false);
    }

    public function test_landing_page_has_correct_title(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200)
            ->assertSee('CANZIM FinTrack', false)
            ->assertSee('Financial Management Simplified', false);
    }

    public function test_landing_page_includes_font_awesome(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200)
            ->assertSee('font-awesome', false);
    }
}
