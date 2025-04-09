<?php

namespace Database\Seeders;

use App\Enums\Inventory;
use App\Models\Item;
use App\Models\NewBarcode;
use App\Models\Transaction;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TransactionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (!Transaction::count()){
            Transaction::factory()->create([
                'id' => '61b0cf37-1ee9-35d4-b6fd-565535a8ec70',
                'item_id' => Item::all()->random()->id,
                'barcode' => 'CBC-01-000001',
                'transac_type' => Inventory::INCOMING->value,
                'quantity' => '100',
                'unit_price' => '100',
                'unit' => 'kg',
                'total_cost' => '10000',
                'personnel_id' => null,
                'project_code' => 'RTF-022-360',
                'user_id' => '1',
                'expiration' => '2021-12-31',
                'remarks' => 'Remarks 1',
            ]);
            NewBarcode::create([
                'barcode' => 'CBC-01-000001',
                'room' => '01',
            ]);
        }
        Transaction::factory(500)->create();
    }
}
