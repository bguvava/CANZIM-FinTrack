<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class TestUsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all roles
        $programsManager = Role::where('slug', 'programs-manager')->first();
        $financeOfficer = Role::where('slug', 'finance-officer')->first();
        $projectOfficer = Role::where('slug', 'project-officer')->first();

        // Test user for Programs Manager role
        User::create([
            'name' => 'Test Programs Manager',
            'email' => 'programs-manager@test.com',
            'password' => Hash::make('password123'),
            'role_id' => $programsManager->id,
            'phone_number' => '+263771111111',
            'office_location' => 'Harare',
            'status' => 'active',
            'email_verified_at' => now(),
        ]);

        // Test user for Finance Officer role
        User::create([
            'name' => 'Test Finance Officer',
            'email' => 'finance-officer@test.com',
            'password' => Hash::make('password123'),
            'role_id' => $financeOfficer->id,
            'phone_number' => '+263772222222',
            'office_location' => 'Harare',
            'status' => 'active',
            'email_verified_at' => now(),
        ]);

        // Test user for Project Officer role
        User::create([
            'name' => 'Test Project Officer',
            'email' => 'project-officer@test.com',
            'password' => Hash::make('password123'),
            'role_id' => $projectOfficer->id,
            'phone_number' => '+263773333333',
            'office_location' => 'Harare',
            'status' => 'active',
            'email_verified_at' => now(),
        ]);
    }
}
