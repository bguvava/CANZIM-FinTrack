<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get Programs Manager role ID
        $programsManagerRoleId = DB::table('roles')
            ->where('slug', 'programs-manager')
            ->value('id');

        DB::table('users')->insert([
            'name' => 'CANZIM Administrator',
            'email' => 'admin@canzim.org.zw',
            'email_verified_at' => now(),
            'password' => Hash::make('canzim@2025'),
            'role_id' => $programsManagerRoleId,
            'office_location' => 'Head Office',
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
