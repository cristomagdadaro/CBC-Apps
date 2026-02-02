<?php

namespace Database\Seeders;

use App\Enums\Subform;
use App\Models\EventSubform;
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
        Form::factory(1)->create()->each(function (Form $form) {
            // Create EventRequirements with per-requirement max_slots
            $requirements = [
                [
                    'form_type' => Subform::PREREGISTRATION->value,
                    'is_required' => true,
                    'max_slots' => 50,
                ],
                [
                    'form_type' => Subform::REGISTRATION->value,
                    'is_required' => true,
                    'max_slots' => 30,
                ],
                [
                    'form_type' => Subform::FEEDBACK->value,
                    'is_required' => false,
                    'max_slots' => 25,
                ],
            ];

            $createdRequirements = [];
            foreach ($requirements as $requirementData) {
                $requirement = EventSubform::firstOrCreate(
                    [
                        'event_id' => $form->event_id,
                        'form_type' => $requirementData['form_type'],
                    ],
                    [
                        'id' => (string) fake()->uuid(),
                        'is_required' => $requirementData['is_required'],
                        'max_slots' => $requirementData['max_slots'],
                        'config' => [
                            'open_from' => now(),
                            'open_to' => now()->addDays(7),
                        ],
                    ]
                );
                $createdRequirements[] = $requirement;
            }

            // Generate participants respecting per-requirement max_slots
            $totalParticipants = random_int(10, 20);
            Participant::factory()->count($totalParticipants)->create()->each(function (Participant $participant) use ($form, $createdRequirements) {
                $registration = Registration::factory()->create([
                    'participant_id' => $participant->id,
                    'event_subform_id' => $form->event_id,
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

                // Create responses for random requirements
                foreach ($createdRequirements as $requirement) {
                    // Randomly decide whether to create a response for this requirement
                    if (fake()->boolean(75)) { // 75% chance to create response
                        EventSubformResponse::factory()->create([
                            'form_parent_id' => $requirement->id,
                            'participant_id' => $registration->id,
                            'subform_type' => $requirement->form_type,
                            'response_data' => $responseData,
                        ]);
                    }
                }
            });
        });
    }

}
