<?php

namespace Database\Seeders;

use App\Models\Form;
use App\Models\Participant;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Symfony\Contracts\EventDispatcher\Event;
use App\Models\EventSubformResponse;

class EventSubformResponseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        EventSubformResponse::factory()->count(100)->create();
    }

}
