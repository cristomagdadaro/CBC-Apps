<?php

namespace Database\Seeders;

use App\Models\LaboratoryEquipmentLog;
use App\Models\Personnel;
use App\Models\User;
use Illuminate\Database\Seeder;

class LaboratoryEquipmentLogSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (!User::query()->exists()) {
            User::factory()->create([
                'name' => 'Lab Admin',
                'email' => 'lab.admin@example.com',
            ]);
        }

        if (!Personnel::query()->exists()) {
            Personnel::factory()->count(10)->create();
        }

        LaboratoryEquipmentLog::factory()->count(10)->completed()->create();
        LaboratoryEquipmentLog::factory()->count(5)->overdue()->create();
        LaboratoryEquipmentLog::factory()->count(5)->active()->create();
        LaboratoryEquipmentLog::factory()->count(5)->create();
    }
}
