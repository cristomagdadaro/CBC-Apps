<?php

namespace Database\Seeders;

use App\Enums\Inventory;
use App\Models\Category;
use App\Models\Item;
use App\Models\NewBarcode;
use App\Models\Personnel;
use App\Models\Supplier;
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
        $items = [
            [
                'name' => 'Double Adhesive Tape',
                'brand' => 'None',
                'description' => 'Tape, Double Adhesive Tape with Foam Joy/Alternative',
                'category_id' => Category::where('name', 'Office Supplies')->first()->id,
                'supplier_id' => Supplier::inRandomOrder()->first()->id,

                'transac_type' => 'incoming',
                'quantity' => 10,
                'unit_price'=> 45,
                'unit' => 'roll',
                'total_cost' => 450,
                'expiration' => null,
                'remarks' => 'PO#2024-08-0296',
            ],
            [
                'name' => 'Duck Tape',
                'brand' => 'Generic',
                'description' => 'Tape, Duct Tape (Cloth-backed)',
                'category_id' => Category::where('name', 'Office Supplies')->first()->id,
                'supplier_id' => Supplier::inRandomOrder()->first()->id,

                'transac_type' => 'incoming',
                'quantity' => 15,
                'unit_price'=> 60,
                'unit' => 'roll',
                'total_cost' => 900,
                'expiration' => null,
                'remarks' => 'PO#2024-08-0296',
            ],[
                'name' => 'Sign Pen Black',
                'brand' => 'Generic',
                'description' => 'Sign Pen, Black, Liquid/Gel Ink, 0.5 mm needle tip, Deli/Flex Office,/Alternative',
                'category_id' => Category::where('name', 'Office Supplies')->first()->id,
                'supplier_id' => Supplier::inRandomOrder()->first()->id,

                'transac_type' => 'incoming',
                'quantity' => 5,
                'unit_price'=> 20,
                'unit' => 'piece',
                'total_cost' => 100,
                'expiration' => null,
                'remarks' => 'PO#2024-08-0296',
            ],[
                'name' => 'Sign Pen Blue',
                'brand' => 'Generic',
                'description' => 'Sign Pen, Blue, Liquid/Gel Ink, 0.5 mm needle tip, Deli/Flex Office,/Alternative',
                'category_id' => Category::where('name', 'Office Supplies')->first()->id,
                'supplier_id' => Supplier::inRandomOrder()->first()->id,

                'transac_type' => 'incoming',
                'quantity' => 5,
                'unit_price'=> 20,
                'unit' => 'piece',
                'total_cost' => 100,
                'expiration' => null,
                'remarks' => 'PO#2024-08-0296',
            ],[
                'name' => 'Double Adhesive Tape',
                'brand' => 'Generic',
                'description' => 'Double Adhesive Tape without foam. Double sided tape without foam, 24mmx10m',
                'category_id' => Category::where('name', 'Office Supplies')->first()->id,
                'supplier_id' => Supplier::inRandomOrder()->first()->id,

                'transac_type' => 'incoming',
                'quantity' => 20,
                'unit_price'=> 28.5,
                'unit' => 'piece',
                'total_cost' => 570,
                'expiration' => null,
                'remarks' => 'PO#2024-08-0298',
            ],
        ];

        foreach ($items as $item) {

            $temp = Transaction::factory()->create([
                'item_id' => Item::factory()->create([
                        'name' => $item['name'],
                        'brand' => $item['brand'],
                        'description' => $item['description'],
                        'category_id' => $item['category_id'],
                        'supplier_id' => $item['supplier_id'],
                    ])->id,
                'transac_type' => $item['transac_type'],
                'quantity' => $item['quantity'],
                'unit_price' => $item['unit_price'],
                'unit' => $item['unit'],
                'total_cost' => $item['total_cost'],
                'expiration' => $item['expiration'],
                'remarks' => $item['remarks'],
            ]);
        }
    }
}
