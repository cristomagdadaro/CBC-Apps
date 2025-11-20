<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class InventoryCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            ['name' => 'Office Supplies', 'description' => 'Various office supplies'],
            ['name' => 'IEC Materials' , 'description' => 'Information, Education, and Communication materials'],
            ['name' => 'Tokens', 'description' => 'Various tokens or promotional materials'],
            ['name' => 'ICT Equipment', 'description' => 'Different types of equipment'],
            ['name' => 'ICT Supplies', 'description' => 'Different types of supplies'],
            ['name' => 'Laboratory Consumables', 'description' => 'Disposable items used in laboratories such as tubes, pipette tips, etc.'],
            ['name' => 'Laboratory Equipment', 'description' => 'Various equipment used in laboratories like beakers, graduated cylinders, etc.'],
        ];

        foreach ($categories as $category) {
            Category::factory()->create($category);
        }
    }
}
