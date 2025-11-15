<?php

namespace Database\Seeders;

use App\Models\ExpenseCategory;
use Illuminate\Database\Seeder;

class ExpenseCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Travel & Transportation',
                'code' => 'TRAVEL',
                'description' => 'Expenses related to travel, transportation, accommodation, and per diems',
                'is_active' => true,
                'sort_order' => 1,
            ],
            [
                'name' => 'Salaries & Benefits',
                'code' => 'SALARY',
                'description' => 'Staff salaries, wages, and employee benefits',
                'is_active' => true,
                'sort_order' => 2,
            ],
            [
                'name' => 'Procurement & Supplies',
                'code' => 'PROCURE',
                'description' => 'Office supplies, equipment, and procurement expenses',
                'is_active' => true,
                'sort_order' => 3,
            ],
            [
                'name' => 'Consultants & Professional Fees',
                'code' => 'CONSULT',
                'description' => 'Consultant fees, professional services, and expert payments',
                'is_active' => true,
                'sort_order' => 4,
            ],
            [
                'name' => 'Training & Workshops',
                'code' => 'TRAINING',
                'description' => 'Training programs, workshops, seminars, and capacity building',
                'is_active' => true,
                'sort_order' => 5,
            ],
            [
                'name' => 'Communication & IT',
                'code' => 'COMMS',
                'description' => 'Communication costs, internet, phone, IT services',
                'is_active' => true,
                'sort_order' => 6,
            ],
            [
                'name' => 'Utilities & Rent',
                'code' => 'UTILITIES',
                'description' => 'Rent, electricity, water, and other utility expenses',
                'is_active' => true,
                'sort_order' => 7,
            ],
            [
                'name' => 'Other Expenses',
                'code' => 'OTHER',
                'description' => 'Miscellaneous expenses not covered in other categories',
                'is_active' => true,
                'sort_order' => 8,
            ],
        ];

        foreach ($categories as $category) {
            ExpenseCategory::create($category);
        }
    }
}
