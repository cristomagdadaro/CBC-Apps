<?php

namespace Database\Seeders;

use App\Models\Form;
use App\Models\Participant;
use App\Models\Registration;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class FormSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Form::factory()->count(25)->create()->each(function (Form $form) {
            $participantCount = random_int($form->max_slots/2, $form->max_slots); // Generate between 1 and 10 participants per form

            Participant::factory()->count($participantCount)->create()->each(function (Participant $participant) use ($form) {
                Registration::factory()->create([
                    'participant_id' => $participant->id,
                    'event_id' => $form->event_id,
                ]);
            });
        });
    }

}
