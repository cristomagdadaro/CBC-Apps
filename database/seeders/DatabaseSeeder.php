<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
         //\App\Models\User::factory(10)->create();

        \App\Models\User::factory()->create([
            'name' => 'DA-CBC Administrator',
            'email' => 'dacropbiotechcenter@gmail.com',
        ]);

        \App\Models\User::factory()->create([
            'name' => 'Cristo Rey C. Magdadaro',
            'email' => 'magdadaro.cristoreyc@gmail.com',
        ]);

        \App\Models\User::factory()->create([
            'name' => 'Ephraim Dioeve Yarcia',
            'email' => 'ephraimdioeveyarcia@gmail.com',
        ]);

        \App\Models\User::factory()->create([
            'name' => 'Ma. Johna C. Doque',
            'email' => 'unknown@gmail.com',
        ]);

        \App\Models\User::factory()->create([
            'name' => 'Al Jun Omandam',
            'email' => 'unknown2@gmail.com',
        ]);

        $this->call([
            //FormSeeder::class,
            InventorySeeder::class,
            RequesterSeeder::class,
            UseRequestFormSeeder::class,
            RequestFormPIvotSeeder::class,
        ]);
    }
}
