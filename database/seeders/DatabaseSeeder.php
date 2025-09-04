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
             'name' => 'Test User',
             'email' => 'test@example.com',
         ]);

         $this->call([
             FormSeeder::class,
             InventoryCategorySeeder::class,
             SupplierSeeder::class,
             ItemsSeeder::class,
             PersonnelSeeder::class,
             TransactionSeeder::class,
             RequesterSeeder::class,
             UseRequestFormSeeder::class,
             RequestFormPIvotSeeder::class,
         ]);
    }
}
