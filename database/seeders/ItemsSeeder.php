<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Item;
use App\Models\Supplier;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ItemsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            [
                'name' => 'Adhesive Tape',
                'brand' => null,
                'description' => null,
                'category_id' => Category::where('name', 'Office Supplies')->first()->id, // Office Supplies
                'supplier_id' => Supplier::where('name', 'Unknown')->first()->id,
            ],
            [
                'name' => 'Binder Clips',
                'brand' => null,
                'description' => '1 inch',
                'category_id' => Category::where('name', 'Office Supplies')->first()->id, // Office Supplies
                'supplier_id' => Supplier::where('name', 'Unknown')->first()->id,
            ],
            [
                'name' => 'Binder Clips',
                'brand' => null,
                'description' => '1 1/4 inch',
                'category_id' => Category::where('name', 'Office Supplies')->first()->id, // Office Supplies
                'supplier_id' => Supplier::where('name', 'Unknown')->first()->id,
            ],
            [
                'name' => 'Bond Paper',
                'brand' => null,
                'description' => 'A3',
                'category_id' => Category::where('name', 'Office Supplies')->first()->id, // Office Supplies
                'supplier_id' => Supplier::where('name', 'Unknown')->first()->id,
            ],
            [
                'name' => 'Bond Paper',
                'brand' => null,
                'description' => 'A4',
                'category_id' => Category::where('name', 'Office Supplies')->first()->id, // Office Supplies
                'supplier_id' => Supplier::where('name', 'Unknown')->first()->id,
            ],
            [
                'name' => 'Bond Paper',
                'brand' => null,
                'description' => 'Legal',
                'category_id' => Category::where('name', 'Office Supplies')->first()->id, // Office Supplies
                'supplier_id' => Supplier::where('name', 'Unknown')->first()->id,
            ],
            [
                'name' => 'Brown Envelope',
                'brand' => null,
                'description' => 'A4',
                'category_id' => Category::where('name', 'Office Supplies')->first()->id, // Office Supplies
                'supplier_id' => Supplier::where('name', 'Unknown')->first()->id,
            ],
            [
                'name' => 'Brown Envelope',
                'brand' => null,
                'description' => 'Long',
                'category_id' => Category::where('name', 'Office Supplies')->first()->id, // Office Supplies
                'supplier_id' => Supplier::where('name', 'Unknown')->first()->id,
            ],
            [
                'name' => 'Brown Expanding Envelope',
                'brand' => null,
                'description' => null,
                'category_id' => Category::where('name', 'Office Supplies')->first()->id, // Office Supplies
                'supplier_id' => Supplier::where('name', 'Unknown')->first()->id,
            ],
            [
                'name' => 'Certificate Holder',
                'brand' => null,
                'description' => 'A4',
                'category_id' => Category::where('name', 'Office Supplies')->first()->id, // Office Supplies
                'supplier_id' => Supplier::where('name', 'Unknown')->first()->id,
            ],
            [
                'name' => 'Clear Book',
                'brand' => null,
                'description' => 'Blue',
                'category_id' => Category::where('name', 'Office Supplies')->first()->id, // Office Supplies
                'supplier_id' => Supplier::where('name', 'Unknown')->first()->id,
            ],
            [
                'name' => 'Clear Book',
                'brand' => null,
                'description' => 'Orange',
                'category_id' => Category::where('name', 'Office Supplies')->first()->id, // Office Supplies
                'supplier_id' => Supplier::where('name', 'Unknown')->first()->id,
            ],
            [
                'name' => 'Clear Book',
                'brand' => null,
                'description' => 'Pink',
                'category_id' => Category::where('name', 'Office Supplies')->first()->id, // Office Supplies
                'supplier_id' => Supplier::where('name', 'Unknown')->first()->id,
            ],
            [
                'name' => 'Clear Book',
                'brand' => null,
                'description' => 'Red',
                'category_id' => Category::where('name', 'Office Supplies')->first()->id, // Office Supplies
                'supplier_id' => Supplier::where('name', 'Unknown')->first()->id,
            ],
            [
                'name' => 'Clear Book',
                'brand' => null,
                'description' => 'Refill',
                'category_id' => Category::where('name', 'Office Supplies')->first()->id, // Office Supplies
                'supplier_id' => Supplier::where('name', 'Unknown')->first()->id,
            ],
            [
                'name' => 'Clear Book',
                'brand' => null,
                'description' => 'Yellow',
                'category_id' => Category::where('name', 'Office Supplies')->first()->id, // Office Supplies
                'supplier_id' => Supplier::where('name', 'Unknown')->first()->id,
            ],
            [
                'name' => 'Correction Tape',
                'brand' => null,
                'description' => null,
                'category_id' => Category::where('name', 'Office Supplies')->first()->id, // Office Supplies
                'supplier_id' => Supplier::where('name', 'Unknown')->first()->id,
            ],
            [
                'name' => 'Double Sided Tape',
                'brand' => null,
                'description' => null,
                'category_id' => Category::where('name', 'Office Supplies')->first()->id, // Office Supplies
                'supplier_id' => Supplier::where('name', 'Unknown')->first()->id,
            ],
            [
                'name' => 'Double Sided Tape Foam',
                'brand' => null,
                'description' => null,
                'category_id' => Category::where('name', 'Office Supplies')->first()->id, // Office Supplies
                'supplier_id' => Supplier::where('name', 'Unknown')->first()->id,
            ],
            [
                'name' => 'Duct Tape',
                'brand' => null,
                'description' => null,
                'category_id' => Category::where('name', 'Office Supplies')->first()->id, // Office Supplies
                'supplier_id' => Supplier::where('name', 'Unknown')->first()->id,
            ],
            [
                'name' => 'Eco Best Tissue Pulls',
                'brand' => null,
                'description' => null,
                'category_id' => Category::where('name', 'Office Supplies')->first()->id, // Office Supplies (Best fit)
                'supplier_id' => Supplier::where('name', 'Unknown')->first()->id,
            ],
            [
                'name' => 'Eco Hygine Tissue Rolls',
                'brand' => null,
                'description' => null,
                'category_id' => Category::where('name', 'Office Supplies')->first()->id, // Office Supplies (Best fit)
                'supplier_id' => Supplier::where('name', 'Unknown')->first()->id,
            ],
            [
                'name' => 'Ethyl Alcohol',
                'brand' => null,
                'description' => null,
                'category_id' => Category::where('name', 'Office Supplies')->first()->id, // Alcohols
                'supplier_id' => Supplier::where('name', 'Unknown')->first()->id,
            ],
            [
                'name' => 'Glassine Bags',
                'brand' => null,
                'description' => null,
                'category_id' => Category::where('name', 'Office Supplies')->first()->id, // Office Supplies (Best fit)
                'supplier_id' => Supplier::where('name', 'Unknown')->first()->id,
            ],
            [
                'name' => 'Glue Stick',
                'brand' => null,
                'description' => null,
                'category_id' => Category::where('name', 'Office Supplies')->first()->id, // Office Supplies
                'supplier_id' => Supplier::where('name', 'Unknown')->first()->id,
            ],
            [
                'name' => 'Heavy Duty Stapler',
                'brand' => null,
                'description' => null,
                'category_id' => Category::where('name', 'Office Supplies')->first()->id, // Office Supplies
                'supplier_id' => Supplier::where('name', 'Unknown')->first()->id,
            ],
            [
                'name' => 'INK',
                'brand' => null,
                'description' => 'Black',
                'category_id' => Category::where('name', 'Office Supplies')->first()->id, // Office Supplies
                'supplier_id' => Supplier::where('name', 'Unknown')->first()->id,
            ],
            [
                'name' => 'INK',
                'brand' => null,
                'description' => 'Cyan',
                'category_id' => Category::where('name', 'Office Supplies')->first()->id, // Office Supplies
                'supplier_id' => Supplier::where('name', 'Unknown')->first()->id,
            ],
            [
                'name' => 'INK',
                'brand' => null,
                'description' => 'Magenta',
                'category_id' => Category::where('name', 'Office Supplies')->first()->id, // Office Supplies
                'supplier_id' => Supplier::where('name', 'Unknown')->first()->id,
            ],
            [
                'name' => 'INK',
                'brand' => null,
                'description' => 'Yellow',
                'category_id' => Category::where('name', 'Office Supplies')->first()->id, // Office Supplies
                'supplier_id' => Supplier::where('name', 'Unknown')->first()->id,
            ],
            [
                'name' => 'Lab Gown',
                'brand' => null,
                'description' => null,
                'category_id' => Category::where('name', 'Office Supplies')->first()->id, // Laboratory Consumables (Best fit for apparel)
                'supplier_id' => Supplier::where('name', 'Unknown')->first()->id,
            ],
            [
                'name' => 'Laboratory Manual',
                'brand' => null,
                'description' => null,
                'category_id' => Category::where('name', 'Office Supplies')->first()->id, // Office Supplies (Manuals/Books)
                'supplier_id' => Supplier::where('name', 'Unknown')->first()->id,
            ],
            [
                'name' => 'Laminating Film',
                'brand' => null,
                'description' => null,
                'category_id' => Category::where('name', 'Office Supplies')->first()->id, // Office Supplies
                'supplier_id' => Supplier::where('name', 'Unknown')->first()->id,
            ],
            [
                'name' => 'Long Arm Stapler',
                'brand' => null,
                'description' => null,
                'category_id' => Category::where('name', 'Office Supplies')->first()->id, // Office Supplies
                'supplier_id' => Supplier::where('name', 'Unknown')->first()->id,
            ],
            [
                'name' => 'Lysol',
                'brand' => null,
                'description' => null,
                'category_id' => Category::where('name', 'Office Supplies')->first()->id, // Antimicrobial Agents / Sterilization Products (Best fit)
                'supplier_id' => Supplier::where('name', 'Unknown')->first()->id,
            ],
            [
                'name' => 'Packaging Tape',
                'brand' => null,
                'description' => null,
                'category_id' => Category::where('name', 'Office Supplies')->first()->id, // Office Supplies
                'supplier_id' => Supplier::where('name', 'Unknown')->first()->id,
            ],
            [
                'name' => 'Paper Fastener',
                'brand' => null,
                'description' => null,
                'category_id' => Category::where('name', 'Office Supplies')->first()->id, // Office Supplies
                'supplier_id' => Supplier::where('name', 'Unknown')->first()->id,
            ],
            [
                'name' => 'Plastic Envelope',
                'brand' => null,
                'description' => null,
                'category_id' => Category::where('name', 'Office Supplies')->first()->id, // Office Supplies
                'supplier_id' => Supplier::where('name', 'Unknown')->first()->id,
            ],
            [
                'name' => 'Plastic Expanding Envelope',
                'brand' => null,
                'description' => null,
                'category_id' => Category::where('name', 'Office Supplies')->first()->id, // Office Supplies
                'supplier_id' => Supplier::where('name', 'Unknown')->first()->id,
            ],
            [
                'name' => 'Plastic Ring Bind',
                'brand' => null,
                'description' => null,
                'category_id' => Category::where('name', 'Office Supplies')->first()->id, // Office Supplies
                'supplier_id' => Supplier::where('name', 'Unknown')->first()->id,
            ],
            [
                'name' => 'Regular Stapler',
                'brand' => null,
                'description' => null,
                'category_id' => Category::where('name', 'Office Supplies')->first()->id, // Office Supplies
                'supplier_id' => Supplier::where('name', 'Unknown')->first()->id,
            ],
            [
                'name' => 'Rubber Bands',
                'brand' => null,
                'description' => 'Big',
                'category_id' => Category::where('name', 'Office Supplies')->first()->id, // Office Supplies
                'supplier_id' => Supplier::where('name', 'Unknown')->first()->id,
            ],
            [
                'name' => 'Sanicare Tissue Pulls',
                'brand' => null,
                'description' => null,
                'category_id' => Category::where('name', 'Office Supplies')->first()->id, // Office Supplies (Best fit)
                'supplier_id' => Supplier::where('name', 'Unknown')->first()->id,
            ],
            [
                'name' => 'Scotch Tape',
                'brand' => null,
                'description' => null,
                'category_id' => Category::where('name', 'Office Supplies')->first()->id, // Office Supplies
                'supplier_id' => Supplier::where('name', 'Unknown')->first()->id,
            ],
            [
                'name' => 'Sharpie Markers',
                'brand' => null,
                'description' => null,
                'category_id' => Category::where('name', 'Office Supplies')->first()->id, // Office Supplies
                'supplier_id' => Supplier::where('name', 'Unknown')->first()->id,
            ],
            [
                'name' => 'Sign Pen',
                'brand' => null,
                'description' => 'Black',
                'category_id' => Category::where('name', 'Office Supplies')->first()->id, // Office Supplies
                'supplier_id' => Supplier::where('name', 'Unknown')->first()->id,
            ],
            [
                'name' => 'Sign Pen',
                'brand' => null,
                'description' => 'Blue',
                'category_id' => Category::where('name', 'Office Supplies')->first()->id, // Office Supplies
                'supplier_id' => Supplier::where('name', 'Unknown')->first()->id,
            ],
            [
                'name' => 'Sprayers',
                'brand' => null,
                'description' => null,
                'category_id' => Category::where('name', 'Office Supplies')->first()->id, // Laboratory Equipment (Best fit for tools/apparatus)
                'supplier_id' => Supplier::where('name', 'Unknown')->first()->id,
            ],
            [
                'name' => 'Stape Wire',
                'brand' => null,
                'description' => '23/13',
                'category_id' => Category::where('name', 'Office Supplies')->first()->id, // Office Supplies
                'supplier_id' => Supplier::where('name', 'Unknown')->first()->id,
            ],
            [
                'name' => 'Stape Wire',
                'brand' => null,
                'description' => 'No. 35',
                'category_id' => Category::where('name', 'Office Supplies')->first()->id, // Office Supplies
                'supplier_id' => Supplier::where('name', 'Unknown')->first()->id,
            ],
            [
                'name' => 'Tape Dispenser',
                'brand' => null,
                'description' => null,
                'category_id' => Category::where('name', 'Office Supplies')->first()->id, // Office Supplies
                'supplier_id' => Supplier::where('name', 'Unknown')->first()->id,
            ],
        ];

        foreach ($data as $item) {
            Item::factory()->create($item);
        }
    }
}
