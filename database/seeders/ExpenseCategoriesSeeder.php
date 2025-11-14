<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ExpenseCategoriesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Travel',
                'slug' => 'travel',
                'description' => 'Travel expenses including transportation, accommodation, and per diem',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Staff Salaries',
                'slug' => 'staff-salaries',
                'description' => 'Employee salaries, wages, and related compensation',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Procurement/Supplies',
                'slug' => 'procurement-supplies',
                'description' => 'Office supplies, equipment, and procurement of goods',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Consultants/Contractors',
                'slug' => 'consultants-contractors',
                'description' => 'External consultants, contractors, and professional services',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Other',
                'slug' => 'other',
                'description' => 'Miscellaneous expenses not covered by other categories',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('expense_categories')->insert($categories);
    }
}
