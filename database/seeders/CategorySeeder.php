<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            ['name' => 'Electronics', 'description' => 'Electronic gadgets'],
            ['name' => 'Clothing', 'description' => 'Clothing items'],
            ['name' => 'Furniture', 'description' => 'Furniture items'],
            ['name' => 'Appliances', 'description' => 'Home appliances'],
            ['name' => 'Books', 'description' => 'Books and reading materials'],
        ];

        foreach ($categories as $category) {
            Category::create($category);
        }
    }
}
