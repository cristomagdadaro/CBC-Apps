<?php

namespace Database\Seeders;

use App\Models\RequestFormPivot;
use Illuminate\Database\Seeder;

class RequestFormPIvotSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        RequestFormPivot::factory()->count(50)->create();
    }
}
