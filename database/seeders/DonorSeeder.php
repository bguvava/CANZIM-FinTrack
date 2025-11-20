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
        $this->command->info('Creating donors...');

        $donors = [
            [
                'name' => 'Global Climate Fund',
                'contact_person' => 'Dr. Sarah Johnson',
                'email' => 'sarah.johnson@gcf.org',
                'phone' => '+1-555-0100',
                'address' => '123 Climate Avenue, New York, NY 10001, USA',
                'tax_id' => 'US-TAX-12345',
                'website' => 'https://www.globalclimatefund.org',
                'status' => 'active',
                'notes' => 'Major international climate funding organization',
            ],
            [
                'name' => 'European Green Initiative',
                'contact_person' => 'Hans Mueller',
                'email' => 'h.mueller@egi.eu',
                'phone' => '+49-30-12345678',
                'address' => 'Unter den Linden 1, 10117 Berlin, Germany',
                'tax_id' => 'DE-TAX-67890',
                'website' => 'https://www.eurogreeen.eu',
                'status' => 'active',
                'notes' => 'EU-based environmental initiative focusing on Africa',
            ],
            [
                'name' => 'African Development Bank',
                'contact_person' => 'Fatima Okonkwo',
                'email' => 'f.okonkwo@afdb.org',
                'phone' => '+225-20-26-10-20',
                'address' => 'Avenue Joseph Anoma 01, Abidjan, Côte d\'Ivoire',
                'tax_id' => 'CI-TAX-11223',
                'website' => 'https://www.afdb.org',
                'status' => 'active',
                'notes' => 'Pan-African development finance institution',
            ],
            [
                'name' => 'UN Climate Action Network',
                'contact_person' => 'Carlos Rodriguez',
                'email' => 'carlos.rodriguez@uncan.org',
                'phone' => '+41-22-917-1234',
                'address' => 'Palais des Nations, 1211 Geneva, Switzerland',
                'tax_id' => 'CH-TAX-99887',
                'website' => 'https://www.unclimateaction.org',
                'status' => 'active',
                'notes' => 'United Nations climate initiative',
            ],
            [
                'name' => 'USAID Zimbabwe',
                'contact_person' => 'Jennifer Williams',
                'email' => 'jwilliams@usaid.gov',
                'phone' => '+263-4-250-593',
                'address' => '1 Pascoe Avenue, Belgravia, Harare, Zimbabwe',
                'tax_id' => 'ZW-TAX-55443',
                'website' => 'https://www.usaid.gov/zimbabwe',
                'status' => 'active',
                'notes' => 'US government development agency',
            ],
            [
                'name' => 'Bill & Melinda Gates Foundation',
                'contact_person' => 'Michael Chen',
                'email' => 'mchen@gatesfoundation.org',
                'phone' => '+1-206-709-3100',
                'address' => '500 5th Avenue N, Seattle, WA 98109, USA',
                'tax_id' => 'US-TAX-33221',
                'website' => 'https://www.gatesfoundation.org',
                'status' => 'active',
                'notes' => 'Major philanthropic organization',
            ],
            [
                'name' => 'UK Aid Direct',
                'contact_person' => 'Emma Thompson',
                'email' => 'e.thompson@ukaid.gov.uk',
                'phone' => '+44-20-7023-0000',
                'address' => '22 Whitehall, London SW1A 2EG, United Kingdom',
                'tax_id' => 'UK-TAX-77665',
                'website' => 'https://www.gov.uk/government/organisations/department-for-international-development',
                'status' => 'active',
                'notes' => 'UK government international development',
            ],
            [
                'name' => 'Green Climate Initiative',
                'contact_person' => 'Raj Patel',
                'email' => 'rpatel@greenclimate.org',
                'phone' => '+91-11-2617-9999',
                'address' => 'New Delhi, India',
                'tax_id' => 'IN-TAX-88990',
                'website' => 'https://www.greenclimateinitiative.org',
                'status' => 'active',
                'notes' => 'Focus on renewable energy projects',
            ],
            [
                'name' => 'Previous Donor Foundation',
                'contact_person' => 'John Smith',
                'email' => 'jsmith@olddonor.org',
                'phone' => '+1-555-9999',
                'address' => 'Old Address, Inactive City',
                'tax_id' => null,
                'website' => null,
                'status' => 'inactive',
                'notes' => 'Former donor - no longer active',
            ],
        ];

        foreach ($donors as $donorData) {
            Donor::create($donorData);
        }

        $this->command->info('✓ Created '.count($donors).' donors');

        // Create additional test donors with factory
        $this->command->info('Creating additional test donors...');
        Donor::factory(15)->create();
        $this->command->info('✓ Created 15 additional test donors');
    }
}
