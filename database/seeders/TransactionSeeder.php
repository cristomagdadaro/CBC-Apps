<?php

namespace Database\Seeders;

use App\Enums\Inventory;
use App\Models\Category;
use App\Models\Item;
use App\Models\NewBarcode;
use App\Models\Personnel;
use App\Models\Supplier;
use App\Models\Transaction;
use Database\Factories\TransactionFactory;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TransactionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //Transaction::factory(100)->create();
        $this->customSeeder();
    }

    private function customSeeder(): void
    {
        $data = [
            ['Adhesive Tape', null, 27, 'piece'],
            ['Binder Clips', '1"', 3, 'box'],
            ['Binder Clips', '1 1/4"', 4, 'box'],
            ['Bond Paper', 'A3', 20, 'ream'],
            ['Bond Paper', 'A4', 100, 'ream'],
            ['Bond Paper', 'Legal', 150, 'ream'],
            ['Brown Envelope', 'A4', 700, 'piece'],
            ['Brown Envelope', 'Long', 735, 'piece'],
            ['Brown Expanding Envelope', null, 151, 'piece'],
            ['Certificate Holder', 'A4', 59, 'piece'],
            ['Clear Book', 'Blue', 8, 'piece'],
            ['Clear Book', 'Orange', 8, 'piece'],
            ['Clear Book', 'Pink', 8, 'piece'],
            ['Clear Book', 'Red', 8, 'piece'],
            ['Clear Book', 'Refill', 10, 'piece'],
            ['Clear Book', 'Yellow', 7, 'piece'],
            ['Correction Tape', null, 66, 'piece'],
            ['Double Sided Tape', null, 12, 'piece'],
            ['Double Sided Tape Foam', null, 7, 'piece'],
            ['Duct Tape', null, 15, 'piece'],
            ['Eco Best Tissue Pulls', null, 30, 'pack'],
            ['Eco Hygine Tissue Rolls', null, 288, 'rolls'],
            ['Ethyl Alcohol', null, 24, 'bottle'],
            ['Glassine Bags', null, 51, 'bundle'],
            ['Glue Stick', null, 60, 'piece'],
            ['Heavy Duty Stapler', null, 1, 'piece'],
            ['INK', 'Black', 25, 'bottle'],
            ['INK', 'Cyan', 35, 'bottle'],
            ['INK', 'Magenta', 39, 'bottle'],
            ['INK', 'Yellow', 33, 'bottle'],
            ['Lab Gown', null, 35, 'piece'],
            ['Laboratory Manual', null, 155, 'piece'],
            ['Laminating Film', null, 50, 'ream'],
            ['Long Arm Stapler', null, 3, 'piece'],
            ['Lysol', null, 9, 'bottle'],
            ['Packaging Tape', null, 38, 'piece'],
            ['Paper Fastener', null, 10, 'box'],
            ['Plastic Envelope', null, 244, 'piece'],
            ['Plastic Expanding Envelope', null, 85, 'piece'],
            ['Plastic Ring Bind', null, 54, 'piece'],
            ['Regular Stapler', null, 3, 'piece'],
            ['Rubber Bands', 'Big', 1, 'box'],
            ['Sanicare Tissue Pulls', null, 40, 'pack'],
            ['Scotch Tape', null, 22, 'piece'],
            ['Sharpie Markers', null, 25, 'set'],
            ['Sign Pen', 'Black', 15, 'box'],
            ['Sign Pen', 'Blue', 15, 'box'],
            ['Sprayers', null, 4, 'piece'],
            ['Stape Wire', '23/13', 21, 'box'],
            ['Stape Wire', 'No. 35', 87, 'box'],
            ['Tape Dispenser', null, 2, 'piece'],
        ];

        foreach ($data as [$name, $description, $quantity, $unit]) {

            $item = Item::where('name', $name)
                ->where('description', $description)
                ->first();

            if (!$item) {
                info("ITEM NOT FOUND: {$name} - {$description}");
                continue;
            }

            Transaction::create([
                'user_id' => 1,    // admin user
                'personnel_id' => Personnel::inRandomOrder()->first()->id,
                'barcode' => TransactionFactory::generateBarcode(Inventory::BIOINFOROOM->value),
                'item_id' => $item->id,
                'transac_type' => Inventory::INCOMING->value,
                'quantity' => $quantity,
                'unit_price' => 0,    // unknown price
                'unit' => $unit,
                'total_cost' => 0,
                'expiration' => null,
                'remarks' => 'Initial Stock',
            ]);
        }
    }

}
