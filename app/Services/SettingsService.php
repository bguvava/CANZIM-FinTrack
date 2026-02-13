<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\AuditTrail;
use App\Models\SystemSetting;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;

/**
 * Settings Service
 *
 * Handles system settings management, logo upload, and cache management
 */
class SettingsService
{
    /**
     * Get all settings grouped by category
     */
    public function getAllSettings(): array
    {
        return [
            'organization' => SystemSetting::getByGroup('organization'),
            'financial' => SystemSetting::getByGroup('financial'),
            'email' => SystemSetting::getByGroup('email'),
            'security' => SystemSetting::getByGroup('security'),
            'notifications' => SystemSetting::getByGroup('notifications'),
            'general' => SystemSetting::getByGroup('general'),
        ];
    }

    /**
     * Get settings by group
     */
    public function getByGroup(string $group): array
    {
        return SystemSetting::getByGroup($group);
    }

    /**
     * Update organization settings
     */
    public function updateOrganization(array $data): array
    {
        $oldValues = SystemSetting::getByGroup('organization');

        foreach ($data as $key => $value) {
            SystemSetting::set($key, $value, 'organization');
        }

        // Clear group cache to ensure fresh data
        Cache::forget('settings.group.organization');

        $newValues = SystemSetting::getByGroup('organization');

        // Clear all cache
        SystemSetting::clearCache();

        // Log audit trail
        AuditTrail::create([
            'user_id' => Auth::id(),
            'action' => 'updated',
            'auditable_type' => SystemSetting::class,
            'auditable_id' => null,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'description' => 'Updated organization settings',
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'request_url' => request()->fullUrl(),
            'request_method' => request()->method(),
        ]);

        return $newValues;
    }

    /**
     * Update financial settings
     */
    public function updateFinancial(array $data): array
    {
        $oldValues = SystemSetting::getByGroup('financial');

        foreach ($data as $key => $value) {
            SystemSetting::set($key, $value, 'financial');
        }

        // Clear group cache to ensure fresh data
        Cache::forget('settings.group.financial');

        $newValues = SystemSetting::getByGroup('financial');

        // Clear all cache
        SystemSetting::clearCache();

        AuditTrail::create([
            'user_id' => Auth::id(),
            'action' => 'updated',
            'auditable_type' => SystemSetting::class,
            'auditable_id' => null,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'description' => 'Updated financial settings',
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'request_url' => request()->fullUrl(),
            'request_method' => request()->method(),
        ]);

        return $newValues;
    }

    /**
     * Update email settings
     */
    public function updateEmail(array $data): array
    {
        $oldValues = SystemSetting::getByGroup('email');

        foreach ($data as $key => $value) {
            SystemSetting::set($key, $value, 'email');
        }

        // Clear group cache to ensure fresh data
        Cache::forget('settings.group.email');

        $newValues = SystemSetting::getByGroup('email');

        // Clear all cache
        SystemSetting::clearCache();

        AuditTrail::create([
            'user_id' => Auth::id(),
            'action' => 'updated',
            'auditable_type' => SystemSetting::class,
            'auditable_id' => null,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'description' => 'Updated email settings',
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'request_url' => request()->fullUrl(),
            'request_method' => request()->method(),
        ]);

        return $newValues;
    }

    /**
     * Update security settings
     */
    public function updateSecurity(array $data): array
    {
        $oldValues = SystemSetting::getByGroup('security');

        foreach ($data as $key => $value) {
            SystemSetting::set($key, $value, 'security');
        }

        // Clear group cache to ensure fresh data
        Cache::forget('settings.group.security');

        $newValues = SystemSetting::getByGroup('security');

        // Clear all cache
        SystemSetting::clearCache();

        AuditTrail::create([
            'user_id' => Auth::id(),
            'action' => 'updated',
            'auditable_type' => SystemSetting::class,
            'auditable_id' => null,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'description' => 'Updated security settings',
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'request_url' => request()->fullUrl(),
            'request_method' => request()->method(),
        ]);

        return $newValues;
    }

    /**
     * Update notification settings
     */
    public function updateNotifications(array $data): array
    {
        $oldValues = SystemSetting::getByGroup('notifications');

        foreach ($data as $key => $value) {
            $boolValue = filter_var($value, FILTER_VALIDATE_BOOLEAN);
            SystemSetting::set($key, $boolValue ? 'true' : 'false', 'notifications', 'boolean');
        }

        // Clear group cache to ensure fresh data
        Cache::forget('settings.group.notifications');

        $newValues = SystemSetting::getByGroup('notifications');

        // Clear all cache
        SystemSetting::clearCache();

        // Log audit trail
        AuditTrail::create([
            'user_id' => Auth::id(),
            'action' => 'updated',
            'auditable_type' => SystemSetting::class,
            'auditable_id' => null,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'description' => 'Updated notification settings',
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'request_url' => request()->fullUrl(),
            'request_method' => request()->method(),
        ]);

        return $newValues;
    }

    /**
     * Upload and process organization logo
     */
    public function uploadLogo(UploadedFile $file): string
    {
        // Create ImageManager instance
        $manager = new ImageManager(new Driver);

        // Read and resize image
        $image = $manager->read($file);
        $image->scaleDown(300, 300);

        // Generate filename
        $filename = 'canzim_logo_'.time().'.png';
        $path = 'images/logo/'.$filename;

        // Ensure directory exists
        if (! file_exists(public_path('images/logo'))) {
            mkdir(public_path('images/logo'), 0755, true);
        }

        // Save image
        $image->toPng()->save(public_path($path));

        // Delete old logo if exists and it's not the default
        $oldLogo = SystemSetting::get('org_logo');
        if ($oldLogo && $oldLogo !== '/images/logo/canzim_logo.png' && file_exists(public_path($oldLogo))) {
            unlink(public_path($oldLogo));
        }

        // Update setting
        SystemSetting::set('org_logo', '/'.$path, 'organization');

        // Log audit trail
        AuditTrail::create([
            'user_id' => Auth::id(),
            'action' => 'updated',
            'auditable_type' => SystemSetting::class,
            'auditable_id' => null,
            'old_values' => ['org_logo' => $oldLogo],
            'new_values' => ['org_logo' => '/'.$path],
            'description' => 'Updated organization logo',
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'request_url' => request()->fullUrl(),
            'request_method' => request()->method(),
        ]);

        return '/'.$path;
    }

    /**
     * Clear all application caches
     */
    public function clearAllCaches(): array
    {
        $cleared = [];

        // Clear application cache
        Cache::flush();
        $cleared[] = 'application';

        // Clear settings cache
        SystemSetting::clearCache();
        $cleared[] = 'settings';

        // Log audit trail
        AuditTrail::create([
            'user_id' => Auth::id(),
            'action' => 'cache_cleared',
            'auditable_type' => 'System',
            'auditable_id' => null,
            'old_values' => null,
            'new_values' => ['caches_cleared' => $cleared],
            'description' => 'Cleared all system caches',
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'request_url' => request()->fullUrl(),
            'request_method' => request()->method(),
        ]);

        return $cleared;
    }

    /**
     * Get system health status
     */
    public function getSystemHealth(): array
    {
        return [
            'disk_usage' => $this->getDiskUsage(),
            'database_size' => $this->getDatabaseSize(),
            'cache_status' => $this->getCacheStatus(),
            'last_backup' => $this->getLastBackupDate(),
        ];
    }

    /**
     * Get disk usage information
     */
    private function getDiskUsage(): array
    {
        $total = @disk_total_space(base_path());
        $free = @disk_free_space(base_path());

        if ($total === false || $free === false) {
            return [
                'total' => 'N/A',
                'used' => 'N/A',
                'free' => 'N/A',
                'percentage' => 0,
            ];
        }

        $used = $total - $free;

        return [
            'total' => $this->formatBytes($total),
            'used' => $this->formatBytes($used),
            'free' => $this->formatBytes($free),
            'percentage' => round(($used / $total) * 100, 2),
        ];
    }

    /**
     * Get database size
     */
    private function getDatabaseSize(): string
    {
        $database = config('database.connections.mysql.database');

        $size = DB::selectOne(
            'SELECT SUM(data_length + index_length) as size
             FROM information_schema.tables
             WHERE table_schema = ?',
            [$database]
        )->size;

        // Cast to float before passing to formatBytes
        return $this->formatBytes($size !== null ? (float) $size : 0);
    }

    /**
     * Get cache status
     */
    private function getCacheStatus(): string
    {
        try {
            Cache::get('test_key');

            return 'Active';
        } catch (\Exception $e) {
            return 'Inactive';
        }
    }

    /**
     * Get last backup date
     */
    private function getLastBackupDate(): ?string
    {
        // This would check your backup directory for the latest backup
        // For now, returning null as placeholder
        return null;
    }

    /**
     * Format bytes to human readable
     */
    private function formatBytes(int|float $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        for ($i = 0; $bytes > 1024; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, $precision).' '.$units[$i];
    }
}
