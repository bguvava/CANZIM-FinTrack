<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SystemSettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $settings = [
            [
                'key' => 'org_name',
                'value' => 'Climate Action Network Zimbabwe',
                'type' => 'string',
                'description' => 'Organization name displayed throughout the system',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'org_short_name',
                'value' => 'CANZIM',
                'type' => 'string',
                'description' => 'Organization short name/acronym',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'org_logo',
                'value' => '/images/logo/canzim_logo.png',
                'type' => 'string',
                'description' => 'Organization logo path',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'currency',
                'value' => 'USD',
                'type' => 'string',
                'description' => 'System default currency',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'timezone',
                'value' => 'Africa/Harare',
                'type' => 'string',
                'description' => 'System timezone',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'session_timeout',
                'value' => '5',
                'type' => 'integer',
                'description' => 'Session timeout in minutes',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'date_format',
                'value' => 'd/m/Y',
                'type' => 'string',
                'description' => 'System date format (PHP format)',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'datetime_format',
                'value' => 'd/m/Y H:i',
                'type' => 'string',
                'description' => 'System datetime format (PHP format)',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'max_file_size_documents',
                'value' => '5120',
                'type' => 'integer',
                'description' => 'Maximum file size for documents in KB (5MB)',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'max_file_size_receipts',
                'value' => '5120',
                'type' => 'integer',
                'description' => 'Maximum file size for receipts in KB (5MB)',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'max_file_size_attachments',
                'value' => '2048',
                'type' => 'integer',
                'description' => 'Maximum file size for comment attachments in KB (2MB)',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('system_settings')->insert($settings);
    }
}
