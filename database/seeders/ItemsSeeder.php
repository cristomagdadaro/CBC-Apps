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
                'category_id' => 1, // Office Supplies
                'supplier_id' => 1,
            ],
            [
                'name' => 'Binder Clips',
                'brand' => null,
                'description' => '1 inch',
                'category_id' => 1, // Office Supplies
                'supplier_id' => 1,
            ],
            [
                'name' => 'Binder Clips',
                'brand' => null,
                'description' => '1 1/4 inch',
                'category_id' => 1, // Office Supplies
                'supplier_id' => 1,
            ],
            [
                'name' => 'Bond Paper',
                'brand' => null,
                'description' => 'A3',
                'category_id' => 1, // Office Supplies
                'supplier_id' => 1,
            ],
            [
                'name' => 'Bond Paper',
                'brand' => null,
                'description' => 'A4',
                'category_id' => 1, // Office Supplies
                'supplier_id' => 1,
            ],
            [
                'name' => 'Bond Paper',
                'brand' => null,
                'description' => 'Legal',
                'category_id' => 1, // Office Supplies
                'supplier_id' => 1,
            ],
            [
                'name' => 'Brown Envelope',
                'brand' => null,
                'description' => 'A4',
                'category_id' => 1, // Office Supplies
                'supplier_id' => 1,
            ],
            [
                'name' => 'Brown Envelope',
                'brand' => null,
                'description' => 'Long',
                'category_id' => 1, // Office Supplies
                'supplier_id' => 1,
            ],
            [
                'name' => 'Brown Expanding Envelope',
                'brand' => null,
                'description' => null,
                'category_id' => 1, // Office Supplies
                'supplier_id' => 1,
            ],
            [
                'name' => 'Certificate Holder',
                'brand' => null,
                'description' => 'A4',
                'category_id' => 1, // Office Supplies
                'supplier_id' => 1,
            ],
            [
                'name' => 'Clear Book',
                'brand' => null,
                'description' => 'Blue',
                'category_id' => 1, // Office Supplies
                'supplier_id' => 1,
            ],
            [
                'name' => 'Clear Book',
                'brand' => null,
                'description' => 'Orange',
                'category_id' => 1, // Office Supplies
                'supplier_id' => 1,
            ],
            [
                'name' => 'Clear Book',
                'brand' => null,
                'description' => 'Pink',
                'category_id' => 1, // Office Supplies
                'supplier_id' => 1,
            ],
            [
                'name' => 'Clear Book',
                'brand' => null,
                'description' => 'Red',
                'category_id' => 1, // Office Supplies
                'supplier_id' => 1,
            ],
            [
                'name' => 'Clear Book',
                'brand' => null,
                'description' => 'Refill',
                'category_id' => 1, // Office Supplies
                'supplier_id' => 1,
            ],
            [
                'name' => 'Clear Book',
                'brand' => null,
                'description' => 'Yellow',
                'category_id' => 1, // Office Supplies
                'supplier_id' => 1,
            ],
            [
                'name' => 'Correction Tape',
                'brand' => null,
                'description' => null,
                'category_id' => 1, // Office Supplies
                'supplier_id' => 1,
            ],
            [
                'name' => 'Double Sided Tape',
                'brand' => null,
                'description' => null,
                'category_id' => 1, // Office Supplies
                'supplier_id' => 1,
            ],
            [
                'name' => 'Double Sided Tape Foam',
                'brand' => null,
                'description' => null,
                'category_id' => 1, // Office Supplies
                'supplier_id' => 1,
            ],
            [
                'name' => 'Duct Tape',
                'brand' => null,
                'description' => null,
                'category_id' => 1, // Office Supplies
                'supplier_id' => 1,
            ],
            [
                'name' => 'Eco Best Tissue Pulls',
                'brand' => null,
                'description' => null,
                'category_id' => 1, // Office Supplies (Best fit)
                'supplier_id' => 1,
            ],
            [
                'name' => 'Eco Hygine Tissue Rolls',
                'brand' => null,
                'description' => null,
                'category_id' => 1, // Office Supplies (Best fit)
                'supplier_id' => 1,
            ],
            [
                'name' => 'Ethyl Alcohol',
                'brand' => null,
                'description' => null,
                'category_id' => 4, // Alcohols
                'supplier_id' => 1,
            ],
            [
                'name' => 'Glassine Bags',
                'brand' => null,
                'description' => null,
                'category_id' => 1, // Office Supplies (Best fit)
                'supplier_id' => 1,
            ],
            [
                'name' => 'Glue Stick',
                'brand' => null,
                'description' => null,
                'category_id' => 1, // Office Supplies
                'supplier_id' => 1,
            ],
            [
                'name' => 'Heavy Duty Stapler',
                'brand' => null,
                'description' => null,
                'category_id' => 1, // Office Supplies
                'supplier_id' => 1,
            ],
            [
                'name' => 'INK',
                'brand' => null,
                'description' => 'Black',
                'category_id' => 1, // Office Supplies
                'supplier_id' => 1,
            ],
            [
                'name' => 'INK',
                'brand' => null,
                'description' => 'Cyan',
                'category_id' => 1, // Office Supplies
                'supplier_id' => 1,
            ],
            [
                'name' => 'INK',
                'brand' => null,
                'description' => 'Magenta',
                'category_id' => 1, // Office Supplies
                'supplier_id' => 1,
            ],
            [
                'name' => 'INK',
                'brand' => null,
                'description' => 'Yellow',
                'category_id' => 1, // Office Supplies
                'supplier_id' => 1,
            ],
            [
                'name' => 'Lab Gown',
                'brand' => null,
                'description' => null,
                'category_id' => 13, // Laboratory Consumables (Best fit for apparel)
                'supplier_id' => 1,
            ],
            [
                'name' => 'Laboratory Manual',
                'brand' => null,
                'description' => null,
                'category_id' => 1, // Office Supplies (Manuals/Books)
                'supplier_id' => 1,
            ],
            [
                'name' => 'Laminating Film',
                'brand' => null,
                'description' => null,
                'category_id' => 1, // Office Supplies
                'supplier_id' => 1,
            ],
            [
                'name' => 'Long Arm Stapler',
                'brand' => null,
                'description' => null,
                'category_id' => 1, // Office Supplies
                'supplier_id' => 1,
            ],
            [
                'name' => 'Lysol',
                'brand' => null,
                'description' => null,
                'category_id' => 17, // Antimicrobial Agents / Sterilization Products (Best fit)
                'supplier_id' => 1,
            ],
            [
                'name' => 'Packaging Tape',
                'brand' => null,
                'description' => null,
                'category_id' => 1, // Office Supplies
                'supplier_id' => 1,
            ],
            [
                'name' => 'Paper Fastener',
                'brand' => null,
                'description' => null,
                'category_id' => 1, // Office Supplies
                'supplier_id' => 1,
            ],
            [
                'name' => 'Plastic Envelope',
                'brand' => null,
                'description' => null,
                'category_id' => 1, // Office Supplies
                'supplier_id' => 1,
            ],
            [
                'name' => 'Plastic Expanding Envelope',
                'brand' => null,
                'description' => null,
                'category_id' => 1, // Office Supplies
                'supplier_id' => 1,
            ],
            [
                'name' => 'Plastic Ring Bind',
                'brand' => null,
                'description' => null,
                'category_id' => 1, // Office Supplies
                'supplier_id' => 1,
            ],
            [
                'name' => 'Regular Stapler',
                'brand' => null,
                'description' => null,
                'category_id' => 1, // Office Supplies
                'supplier_id' => 1,
            ],
            [
                'name' => 'Rubber Bands',
                'brand' => null,
                'description' => 'Big',
                'category_id' => 1, // Office Supplies
                'supplier_id' => 1,
            ],
            [
                'name' => 'Sanicare Tissue Pulls',
                'brand' => null,
                'description' => null,
                'category_id' => 1, // Office Supplies (Best fit)
                'supplier_id' => 1,
            ],
            [
                'name' => 'Scotch Tape',
                'brand' => null,
                'description' => null,
                'category_id' => 1, // Office Supplies
                'supplier_id' => 1,
            ],
            [
                'name' => 'Sharpie Markers',
                'brand' => null,
                'description' => null,
                'category_id' => 1, // Office Supplies
                'supplier_id' => 1,
            ],
            [
                'name' => 'Sign Pen',
                'brand' => null,
                'description' => 'Black',
                'category_id' => 1, // Office Supplies
                'supplier_id' => 1,
            ],
            [
                'name' => 'Sign Pen',
                'brand' => null,
                'description' => 'Blue',
                'category_id' => 1, // Office Supplies
                'supplier_id' => 1,
            ],
            [
                'name' => 'Sprayers',
                'brand' => null,
                'description' => null,
                'category_id' => 14, // Laboratory Equipment (Best fit for tools/apparatus)
                'supplier_id' => 1,
            ],
            [
                'name' => 'Stape Wire',
                'brand' => null,
                'description' => '23/13',
                'category_id' => 1, // Office Supplies
                'supplier_id' => 1,
            ],
            [
                'name' => 'Stape Wire',
                'brand' => null,
                'description' => 'No. 35',
                'category_id' => 1, // Office Supplies
                'supplier_id' => 1,
            ],
            [
                'name' => 'Tape Dispenser',
                'brand' => null,
                'description' => null,
                'category_id' => 1, // Office Supplies
                'supplier_id' => 1,
            ],
        ];

        foreach ($data as $item) {
            Item::factory()->create($item);
        }
    }
}
