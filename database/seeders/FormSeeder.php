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

                // Create responses for random requirements
                foreach ($createdRequirements as $requirement) {
                    // Randomly decide whether to create a response for this requirement
                    if (fake()->boolean(75)) { // 75% chance to create response
                        // Generate responseData dynamically based on form type
                        $responseData = $this->generateResponseData(
                            $requirement->form_type,
                            $participant,
                            $registration
                        );

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

    /**
     * Generate response data based on form type using config/subformtypes.php
     */
    private function generateResponseData(string $formType, Participant $participant, Registration $registration): array
    {
        $formFields = config("subformtypes.$formType", []);
        $responseData = [];

        foreach ($formFields as $field => $rules) {
            $responseData[$field] = $this->generateFieldValue($field, $rules, $participant, $registration);
        }

        return $responseData;
    }

    /**
     * Generate a fake value for a field based on its validation rules
     */
    private function generateFieldValue(string $fieldName, string $rules, Participant $participant, Registration $registration): mixed
    {
        $rules = strtolower($rules);

        // Try to get value from participant if it exists
        if (isset($participant->$fieldName)) {
            return $participant->$fieldName;
        }

        // Handle special fields from registration
        if ($fieldName === 'attendance_type') {
            return $registration->attendance_type;
        }

        // Handle boolean fields
        if (str_contains($rules, 'boolean') || str_contains($rules, 'accepted')) {
            return fake()->boolean(80); // 80% true for boolean fields
        }

        // Handle integer/rating fields (1-5)
        if (str_contains($rules, 'integer')) {
            if (str_contains($fieldName, 'rating') || 
                str_contains($fieldName, 'clarity') || 
                str_contains($fieldName, 'quality') ||
                str_contains($fieldName, 'knowledge_gain') ||
                str_contains($fieldName, 'time_allotment') ||
                str_contains($fieldName, 'attainment') ||
                str_contains($fieldName, 'relevance') ||
                str_contains($fieldName, 'support_staff') ||
                str_contains($fieldName, 'management')) {
                return fake()->numberBetween(1, 5);
            }
            return fake()->randomNumber();
        }

        // Handle email fields
        if (str_contains($rules, 'email')) {
            return fake()->email();
        }

        // Handle text/string fields
        if (str_contains($rules, 'string') || str_contains($rules, 'text')) {
            if (str_contains($fieldName, 'comment') || 
                str_contains($fieldName, 'remarks') ||
                str_contains($fieldName, 'suggestion') ||
                str_contains($fieldName, 'other_topics')) {
                return fake()->sentence();
            }
            if (str_contains($fieldName, 'name')) {
                return fake()->name();
            }
            if (str_contains($fieldName, 'phone')) {
                return fake()->phoneNumber();
            }
            if (str_contains($fieldName, 'organization')) {
                return fake()->company();
            }
            if (str_contains($fieldName, 'address') || str_contains($fieldName, 'city') || str_contains($fieldName, 'province') || str_contains($fieldName, 'country')) {
                return fake()->word();
            }
            return fake()->word();
        }

        return null;
    }

}
