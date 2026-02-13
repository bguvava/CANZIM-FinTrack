<?php

declare(strict_types=1);

namespace Tests\Traits;

/**
 * Trait RequiresGdExtension
 *
 * Skip tests that require GD extension if it's not available.
 */
trait RequiresGdExtension
{
    /**
     * Skip test if GD extension is not loaded.
     */
    protected function skipIfGdNotAvailable(): void
    {
        if (! extension_loaded('gd') || ! function_exists('imagecreatetruecolor')) {
            $this->markTestSkipped('GD extension is not available. This test requires GD for image manipulation.');
        }
    }
}
