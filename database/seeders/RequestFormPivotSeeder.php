<?php

namespace Database\Seeders;

use App\Models\RequestFormPivot;
use Illuminate\Database\Seeder;

class RequestFormPivotSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        RequestFormPivot::factory()->count(50)->create();
    }
}
