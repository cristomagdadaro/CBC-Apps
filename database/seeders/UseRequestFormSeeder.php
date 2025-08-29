<?php

namespace Database\Seeders;

use App\Models\UseRequestForm;
use Illuminate\Database\Seeder;

class UseRequestFormSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        UseRequestForm::factory()->count(50)->create();
    }
}
