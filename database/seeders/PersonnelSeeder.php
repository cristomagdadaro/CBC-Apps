<?php

namespace Database\Seeders;

use App\Models\Personnel;
use Illuminate\Database\Seeder;

class PersonnelSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //Personnel::factory()->count(10)->create();
        $personnels = [
            [
                'fname' => 'John',
                'mname' => 'Michael',
                'lname' => 'Smith',
                'suffix' => 'Jr',
                'position' => 'Laboratory Technician',
                'phone' => '+1 (123) 456-7890',
                'address' => '123 Lab St, Science City, USA',
                'email' => 'john.smith@example.com'
            ],
            [
                'fname' => 'Emily',
                'mname' => 'Grace',
                'lname' => 'Johnson',
                'suffix' => 'Sr',
                'position' => 'Research Scientist',
                'phone' => '+1 (234) 567-8901',
                'address' => '456 Research Ave, Techville, USA',
                'email' => 'emily.johnson@example.com'
            ],
            [
                'fname' => 'David',
                'mname' => 'Patrick',
                'lname' => 'Williams',
                'suffix' => 'III',
                'position' => 'Lab Manager',
                'phone' => '+1 (345) 678-9012',
                'address' => '789 Lab Blvd, Innovation City, USA',
                'email' => 'david.williams@example.com'
            ],
            [
                'fname' => 'Jessica',
                'mname' => 'Marie',
                'lname' => 'Brown',
                'suffix' => 'IV',
                'position' => 'Quality Control Specialist',
                'phone' => '+1 (456) 789-0123',
                'address' => '901 Quality St, Assurance Town, USA',
                'email' => 'jessica.brown@example.com'
            ],
            [
                'fname' => 'Michael',
                'mname' => null,
                'lname' => 'Jones',
                'suffix' => null,
                'position' => 'Chemist',
                'phone' => '+1 (567) 890-1234',
                'address' => '234 Chem St, Analytical City, USA',
                'email' => 'michael.jones@example.com'
            ]
        ];

        foreach ($personnels as $personnel) {
            Personnel::factory()->create($personnel);
        }

        Personnel::factory()->count(10)->create();
    }
}
