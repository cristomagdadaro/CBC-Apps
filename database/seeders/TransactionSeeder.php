<?php

namespace Database\Seeders;

use App\Enums\Inventory;
use App\Models\Item;
use App\Models\NewBarcode;
use App\Models\Personnel;
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
        Transaction::factory(500)->create();
    }
}
