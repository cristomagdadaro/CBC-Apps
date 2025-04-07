<?php

namespace Database\Seeders;

use App\Models\LabRequestForm;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class LabRequestFormSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        LabRequestForm::factory()->count(50)->create();
    }
}
