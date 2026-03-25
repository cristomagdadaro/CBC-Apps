<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Database\Seeders\ResearchMonitoringSeeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
         //\App\Models\User::factory(10)->create();

        $admin = User::query()->firstOrCreate(
            ['email' => 'dacropbiotechcenter@gmail.com'],
            User::factory()->raw([
                'name' => 'DA-CBC Administrator',
                'is_admin' => true,
            ])
        );
        $admin->roles()->syncWithoutDetaching([1]);

        User::query()->firstOrCreate(
            ['email' => 'magdadaro.cristoreyc@gmail.com'],
            User::factory()->raw(['name' => 'Cristo Rey C. Magdadaro'])
        );

        User::query()->firstOrCreate(
            ['email' => 'ephraimdioeveyarcia@gmail.com'],
            User::factory()->raw(['name' => 'Ephraim Dioeve Yarcia'])
        );

        User::query()->firstOrCreate(
            ['email' => 'unknown@gmail.com'],
            User::factory()->raw(['name' => 'Ma. Johna C. Doque'])
        );

        User::query()->firstOrCreate(
            ['email' => 'unknown2@gmail.com'],
            User::factory()->raw(['name' => 'Al Jun Omandam'])
        );

        $this->call([
            RolesSeeder::class,
            OptionSeeder::class,
            FormSeeder::class,
            InventorySeeder::class,
            RequesterSeeder::class,
            UseRequestFormSeeder::class,
            RequestFormPivotSeeder::class,
            LocCitiesSeeder::class,
            InventoryIcf2026Seeder::class,
            LaboratoryEquipmentLogSeeder::class,
            RentalsSeeder::class,
            ResearchMonitoringSeeder::class,
        ]);
    }
}
