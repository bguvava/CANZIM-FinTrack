<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Idempotent seeder for production essentials.
 * Safe to run multiple times — uses updateOrCreate/firstOrCreate.
 */
class ProductionSeeder extends Seeder
{
    public function run(): void
    {
        // Roles
        $rolesData = [
            [
                'name' => 'Programs Manager',
                'slug' => 'programs-manager',
                'description' => 'Highest authority - Full system access, strategic oversight, budget approval, and user management',
            ],
            [
                'name' => 'Finance Officer',
                'slug' => 'finance-officer',
                'description' => 'Middle authority - Financial operations, accounting, expense management, and financial reporting',
            ],
            [
                'name' => 'Project Officer',
                'slug' => 'project-officer',
                'description' => 'Base authority - Project implementation, expense submission, and activity coordination',
            ],
        ];

        foreach ($rolesData as $roleData) {
            Role::firstOrCreate(['slug' => $roleData['slug']], $roleData);
        }

        $programsManagerRole = Role::where('slug', 'programs-manager')->first();

        // Primary admin user
        User::updateOrCreate(
            ['email' => 'admin@canzim.org.zw'],
            [
                'name' => 'CANZIM Administrator',
                'password' => Hash::make('canzim@2025'),
                'role_id' => $programsManagerRole->id,
                'office_location' => 'Head Office',
                'status' => 'active',
                'email_verified_at' => now(),
            ]
        );

        // Programs Manager test user (full access)
        User::updateOrCreate(
            ['email' => 'programs-manager@test.com'],
            [
                'name' => 'Test Programs Manager',
                'password' => Hash::make('password123'),
                'role_id' => $programsManagerRole->id,
                'phone_number' => '+263771111111',
                'office_location' => 'Harare',
                'status' => 'active',
                'email_verified_at' => now(),
            ]
        );
    }
}
