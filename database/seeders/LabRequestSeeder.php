<?php

namespace Database\Seeders;

use App\Models\LabRequest;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class LabRequestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        LabRequest::factory()->count(50)->create();
    }
}
