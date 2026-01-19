<?php

namespace Database\Seeders;

use App\Enums\Subform;
use App\Models\EventSubformResponse;
use App\Models\Form;
use App\Models\Participant;
use App\Models\Registration;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;

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
                $registration = Registration::factory()->create([
                    'participant_id' => $participant->id,
                    'event_id' => $form->event_id,
                ]);

                $responseData = Arr::only($participant->toArray(), [
                    'name',
                    'email',
                    'phone',
                    'sex',
                    'age',
                    'organization',
                    'designation',
                    'is_ip',
                    'is_pwd',
                    'city_address',
                    'province_address',
                    'country_address',
                    'agreed_tc',
                ]);

                $responseData['attendance_type'] = $registration->attendance_type;

                EventSubformResponse::create([
                    'form_parent_id' => $form->event_id,
                    'participant_id' => $registration->id,
                    'subform_type' => Subform::PREREGISTRATION->value,
                    'response_data' => $responseData,
                ]);
            });
        });
    }

}
