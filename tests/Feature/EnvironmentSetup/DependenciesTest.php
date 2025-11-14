<?php

declare(strict_types=1);

namespace Tests\Feature\EnvironmentSetup;

use Tests\TestCase;

/**
 * Dependencies Test
 *
 * Verifies that all required packages and dependencies are installed.
 *
 * Requirements: REQ-009, REQ-011, REQ-012, REQ-013, REQ-017, REQ-018, REQ-019
 */
class DependenciesTest extends TestCase
{
    /**
     * Test Laravel is installed
     */
    public function test_laravel_is_installed(): void
    {
        $laravelVersion = app()->version();

        $this->assertNotEmpty($laravelVersion);
        $this->assertStringContainsString('12.', $laravelVersion);
    }

    /**
     * Test Laravel Sanctum is installed
     */
    public function test_sanctum_is_installed(): void
    {
        $this->assertTrue(class_exists(\Laravel\Sanctum\Sanctum::class));
    }

    /**
     * Test DomPDF is installed
     */
    public function test_dompdf_is_installed(): void
    {
        $this->assertTrue(class_exists(\Barryvdh\DomPDF\ServiceProvider::class));
    }

    /**
     * Test Intervention Image is installed
     */
    public function test_intervention_image_is_installed(): void
    {
        // Intervention Image 3.x uses different namespace structure
        $this->assertTrue(class_exists(\Intervention\Image\ImageManager::class));
    }

    /**
     * Test required directories exist
     */
    public function test_required_directories_exist(): void
    {
        $directories = [
            'app',
            'bootstrap',
            'config',
            'database',
            'public',
            'resources',
            'routes',
            'storage',
            'tests',
            'docs',
            'public/images/logo',
        ];

        foreach ($directories as $directory) {
            $this->assertDirectoryExists(base_path($directory), "Directory {$directory} does not exist");
        }
    }

    /**
     * Test logo files exist
     */
    public function test_logo_files_exist(): void
    {
        $this->assertFileExists(public_path('images/logo/canzim_logo.png'), 'Primary logo file is missing');
        $this->assertFileExists(public_path('images/logo/canzim_white.png'), 'White logo file is missing');
    }

    /**
     * Test animation CSS file exists
     */
    public function test_animation_css_exists(): void
    {
        $this->assertFileExists(resource_path('css/animations.css'), 'Animation CSS file is missing');
    }

    /**
     * Test SweetAlert2 plugin file exists
     */
    public function test_sweetalert_plugin_exists(): void
    {
        $this->assertFileExists(resource_path('js/plugins/sweetalert.js'), 'SweetAlert2 plugin file is missing');
    }

    /**
     * Test API routes file exists
     */
    public function test_api_routes_file_exists(): void
    {
        $this->assertFileExists(base_path('routes/api.php'), 'API routes file is missing');
    }

    /**
     * Test CORS config file exists
     */
    public function test_cors_config_exists(): void
    {
        $this->assertFileExists(config_path('cors.php'), 'CORS config file is missing');
    }
}
