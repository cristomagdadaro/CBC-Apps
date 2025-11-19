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
                'name' => 'Unknown',
                'phone' => '00000000',
                'email' => 'unknown@unknown.com',
                'address' => 'unknown',
                'description' => 'Use this supplier when the supplier is unknown',
            ],
        ];

        foreach ($suppliers as $supplier) {
            Supplier::factory()->create($supplier);
        }
    }
}
