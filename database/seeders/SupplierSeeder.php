<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Supplier;

class SupplierSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //Supplier::factory(10)->create();

        $suppliers = [
            [
                'name' => 'Chemical Solutions Inc.',
                'phone' => '+1 (123) 456-7890',
                'email' => 'info@chemicalsolutions.com',
                'address' => '1234 Lab St, Cityville, USA',
                'description' => 'Supplier of a wide range of chemical solutions for laboratory and industrial use.'
            ],
            [
                'name' => 'Lab Equipment Co.',
                'phone' => '+1 (234) 567-8901',
                'email' => 'sales@labequipmentco.com',
                'address' => '5678 Science Ave, Research City, USA',
                'description' => 'Specializes in providing laboratory equipment and consumables to research institutions and industrial labs.'
            ],
            [
                'name' => 'Biochemical Supplies Ltd.',
                'phone' => '+1 (345) 678-9012',
                'email' => 'orders@biochemicalsupplies.com',
                'address' => '9012 Bio Way, Biotech Park, USA',
                'description' => 'Supplier of biochemicals, enzymes, and reagents for molecular biology and life science research.'
            ],
            [
                'name' => 'Lab Safety Solutions',
                'phone' => '+1 (456) 789-0123',
                'email' => 'info@labsafetysolutions.com',
                'address' => '3456 Safety Blvd, Labville, USA',
                'description' => 'Provides safety equipment, protective gear, and hygiene solutions for laboratories and research facilities.'
            ],
            [
                'name' => 'Genetic Technologies Inc.',
                'phone' => '+1 (567) 890-1234',
                'email' => 'sales@genetictech.com',
                'address' => '6789 DNA St, Genetown, USA',
                'description' => 'Supplier of molecular biology kits, DNA/RNA extraction products, and genetic analysis tools.'
            ],
            [
                'name' => 'Analytical Instruments Corp.',
                'phone' => '+1 (678) 901-2345',
                'email' => 'support@analyticalinstruments.com',
                'address' => '7890 Lab Ave, Analytica City, USA',
                'description' => 'Offers a range of analytical instruments, chromatography systems, and lab automation solutions.'
            ],
            [
                'name' => 'Microbiology Supplies Ltd.',
                'phone' => '+1 (789) 012-3456',
                'email' => 'support@microtech.com',
                'address' => '8901 Micro St, Microville, USA',
                'description' => 'Supplier of microbiology products, culture media, and microbial identification systems.'
            ],
        ];

        foreach ($suppliers as $supplier) {
            Supplier::factory()->create($supplier);
        }
    }
}
