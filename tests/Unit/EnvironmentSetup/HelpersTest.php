<?php

declare(strict_types=1);

namespace Tests\Unit\EnvironmentSetup;

use PHPUnit\Framework\TestCase;

/**
 * Helpers Test
 *
 * Unit tests for helper functions and utilities.
 *
 * Requirements: REQ-001 to REQ-035
 */
class HelpersTest extends TestCase
{
    /**
     * Test PHP version is 8.2 or higher
     */
    public function test_php_version_is_8_2_or_higher(): void
    {
        $phpVersion = phpversion();

        $this->assertTrue(version_compare($phpVersion, '8.2.0', '>='), "PHP version {$phpVersion} is below 8.2.0");
    }

    /**
     * Test required PHP extensions are loaded
     */
    public function test_required_php_extensions_are_loaded(): void
    {
        $requiredExtensions = [
            'pdo',
            'pdo_mysql',
            'mbstring',
            'openssl',
            'json',
            'tokenizer',
            'xml',
            'ctype',
            'fileinfo',
        ];

        foreach ($requiredExtensions as $extension) {
            $this->assertTrue(extension_loaded($extension), "PHP extension {$extension} is not loaded");
        }

        // GD is optional for image processing - skip if not available
        if (! extension_loaded('gd')) {
            $this->markTestIncomplete('GD extension is not loaded. Image upload tests will be skipped.');
        }
    }

    /**
     * Test bcrypt rounds configuration
     */
    public function test_bcrypt_rounds_configuration(): void
    {
        $rounds = (int) env('BCRYPT_ROUNDS', 12);

        // In testing environment, bcrypt rounds is set to 4 for speed (phpunit.xml)
        // In production, it should be 12 (.env)
        $expectedRounds = (env('APP_ENV') === 'testing') ? 4 : 12;

        $this->assertEquals($expectedRounds, $rounds);
        $this->assertGreaterThanOrEqual(4, $rounds);
    }
}
