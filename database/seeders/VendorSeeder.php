<?php

namespace Database\Seeders;

use App\Models\Vendor;
use Illuminate\Database\Seeder;

class VendorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create 15 active vendors for testing
        Vendor::factory()->count(15)->create();

        // Create 3 inactive vendors
        Vendor::factory()->inactive()->count(3)->create();
    }
}
