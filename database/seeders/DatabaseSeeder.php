<?php

namespace Database\Seeders;

use App\Models\RentalVehicle;
use App\Models\RentalVenue;
use Illuminate\Database\Seeder;
use App\Models\User;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
         //\App\Models\User::factory(10)->create();

        (User::factory()->create([
            'name' => 'DA-CBC Administrator',
            'email' => 'dacropbiotechcenter@gmail.com',
            'is_admin' => true,
        ]))->roles()->syncWithoutDetaching([1]);

        User::factory()->create([
            'name' => 'Cristo Rey C. Magdadaro',
            'email' => 'magdadaro.cristoreyc@gmail.com',
        ]);

        User::factory()->create([
            'name' => 'Ephraim Dioeve Yarcia',
            'email' => 'ephraimdioeveyarcia@gmail.com',
        ]);

        User::factory()->create([
            'name' => 'Ma. Johna C. Doque',
            'email' => 'unknown@gmail.com',
        ]);

        User::factory()->create([
            'name' => 'Al Jun Omandam',
            'email' => 'unknown2@gmail.com',
        ]);

        $this->call([
            RolesSeeder::class,
            OptionSeeder::class,
            FormSeeder::class,
            InventorySeeder::class,
            RequesterSeeder::class,
            UseRequestFormSeeder::class,
            RequestFormPIvotSeeder::class,
            LocCitiesSeeder::class,
            InventoryIcf2026Seeder::class,
            LaboratoryEquipmentLogSeeder::class,
        ]);

        RentalVehicle::factory()->count(20)->create();
        RentalVenue::factory()->count(20)->create();
    }
}
