<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RolesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [
            [
                'name' => 'Programs Manager',
                'slug' => 'programs-manager',
                'description' => 'Highest authority - Full system access, strategic oversight, budget approval, and user management',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Finance Officer',
                'slug' => 'finance-officer',
                'description' => 'Middle authority - Financial operations, accounting, expense management, and financial reporting',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Project Officer',
                'slug' => 'project-officer',
                'description' => 'Base authority - Project implementation, expense submission, and activity coordination',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('roles')->insert($roles);
    }
}
