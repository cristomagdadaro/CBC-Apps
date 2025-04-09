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
        /*\App\Models\Inventory\Item::factory()
            ->count(20)
            ->create();*/

        $data = [
            [
                'name' => 'ACETIC ACID, GLACIAL',
                'brand' => 'MERCK',
                'description' => '2.5L',
                'category_id' => Category::all()->random()->id,
                'supplier_id' => Supplier::all()->random()->id,
            ],
            [
                'name' => 'CHLOROFORM',
                'brand' => 'JT baker',
                'description' => '4L',
                'category_id' => Category::all()->random()->id,
                'supplier_id' => Supplier::all()->random()->id,
            ],
            [
                'name' => 'CHLOROFORM',
                'brand' => 'ACI',
                'description' => '4L',
                'category_id' => Category::all()->random()->id,
                'supplier_id' => Supplier::all()->random()->id,
            ],
            [
                'name' => 'CHLOROFORM',
                'brand' => 'SCHARLAU',
                'description' => '2.5L',
                'category_id' => Category::all()->random()->id,
                'supplier_id' => Supplier::all()->random()->id,
            ],
            [
                'name' => 'ETHANOL',
                'brand' => 'cby',
                'description' => '0',
                'category_id' => Category::all()->random()->id,
                'supplier_id' => Supplier::all()->random()->id,
            ],
            [
                'name' => 'ETHANOL ABSOLUTE',
                'brand' => 'MERCK',
                'description' => '4L',
                'category_id' => Category::all()->random()->id,
                'supplier_id' => Supplier::all()->random()->id,
            ],
            [
                'name' => 'ETHANOL ABSOLUTE',
                'brand' => 'SCHARLAU',
                'description' => '4L',
                'category_id' => Category::all()->random()->id,
                'supplier_id' => Supplier::all()->random()->id,
            ],
            [
                'name' => 'ETHYL ACETATE',
                'brand' => 'SCHARLAU',
                'description' => '4L',
                'category_id' => Category::all()->random()->id,
                'supplier_id' => Supplier::all()->random()->id,
            ],
            [
                'name' => 'FORMAMIDE',
                'brand' => 'Loba Chemie',
                'description' => '500 ml',
                'category_id' => Category::all()->random()->id,
                'supplier_id' => Supplier::all()->random()->id,
            ],
            [
                'name' => 'FORMAMIDE',
                'brand' => 'Univar',
                'description' => '500 ml',
                'category_id' => Category::all()->random()->id,
                'supplier_id' => Supplier::all()->random()->id,
            ],
            [
                'name' => 'FORMAMIDE',
                'brand' => 'Sigma',
                'description' => '500 ml',
                'category_id' => Category::all()->random()->id,
                'supplier_id' => Supplier::all()->random()->id,
            ],
            [
                'name' => 'FORMAMIDE',
                'brand' => 'MERCK',
                'description' => '1L',
                'category_id' => Category::all()->random()->id,
                'supplier_id' => Supplier::all()->random()->id,
            ],
            [
                'name' => 'Hydrochloric Acid',
                'brand' => 'Emsure',
                'description' => '2.5L',
                'category_id' => Category::all()->random()->id,
                'supplier_id' => Supplier::all()->random()->id,
            ],
            [
                'name' => 'Hydrochloric Acid',
                'brand' => 'Labscan',
                'description' => '2.5L',
                'category_id' => Category::all()->random()->id,
                'supplier_id' => Supplier::all()->random()->id,
            ],
            [
                'name' => 'METHANOL',
                'brand' => 'JT baker',
                'description' => '4L',
                'category_id' => Category::all()->random()->id,
                'supplier_id' => Supplier::all()->random()->id,
            ],
            [
                'name' => 'METHANOL',
                'brand' => 'ACI',
                'description' => '4L',
                'category_id' => Category::all()->random()->id,
                'supplier_id' => Supplier::all()->random()->id,
            ],
            [
                'name' => '2-PROPANOL',
                'brand' => 'JTBAKER',
                'description' => '4L',
                'category_id' => Category::all()->random()->id,
                'supplier_id' => Supplier::all()->random()->id,
            ],
            [
                'name' => '2-PROPANOL',
                'brand' => 'ACL LABSCAN',
                'description' => '4L',
                'category_id' => Category::all()->random()->id,
                'supplier_id' => Supplier::all()->random()->id,
            ],
            [
                'name' => 'SODIUM HYPOCHLORITE',
                'brand' => 'cby',
                'description' => '16',
                'category_id' => Category::all()->random()->id,
                'supplier_id' => Supplier::all()->random()->id,
            ],
        ];

        foreach ($data as $item) {
            Item::factory()->create($item);
        }
    }
}
