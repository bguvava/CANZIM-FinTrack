<?php

namespace Database\Seeders;

use App\Models\Donor;
use Illuminate\Database\Seeder;

class DonorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $donors = [
            [
                'name' => 'Global Climate Fund',
                'contact_person' => 'Dr. Sarah Johnson',
                'email' => 'sarah.johnson@gcf.org',
                'phone' => '+1-555-0100',
                'address' => '123 Climate Avenue, New York, USA',
                'funding_total' => 5000000.00,
            ],
            [
                'name' => 'European Green Initiative',
                'contact_person' => 'Hans Mueller',
                'email' => 'h.mueller@egi.eu',
                'phone' => '+49-30-12345678',
                'address' => 'Unter den Linden 1, Berlin, Germany',
                'funding_total' => 3500000.00,
            ],
            [
                'name' => 'African Development Bank',
                'contact_person' => 'Fatima Okonkwo',
                'email' => 'f.okonkwo@afdb.org',
                'phone' => '+225-20-26-10-20',
                'address' => 'Avenue Joseph Anoma, Abidjan, Côte d\'Ivoire',
                'funding_total' => 2800000.00,
            ],
            [
                'name' => 'UN Climate Action Network',
                'contact_person' => 'Carlos Rodriguez',
                'email' => 'carlos.rodriguez@uncan.org',
                'phone' => '+41-22-917-1234',
                'address' => 'Palais des Nations, Geneva, Switzerland',
                'funding_total' => 4200000.00,
            ],
        ];

        foreach ($donors as $donorData) {
            Donor::create($donorData);
        }
    }
}
