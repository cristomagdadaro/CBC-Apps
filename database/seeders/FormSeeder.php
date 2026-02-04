<?php

namespace Database\Seeders;

use App\Enums\Subform;
use App\Models\EventSubform;
use App\Models\EventSubformResponse;
use App\Models\Form;
use App\Models\Participant;
use App\Models\Registration;
use Illuminate\Database\Seeder;


class FormSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Form::factory(1)->create()->each(function (Form $form) {
            $requirements = [
                [
                    'form_type' => Subform::PREREGISTRATION->value,
                    'is_required' => true,
                    'max_slots' => fake()->numberBetween(0, 50),
                    'step_order' => 1,
                    'visibility_rules' => [],
                    'completion_rules' => [
                        'required_fields' => ['agreed_tc'],
                    ],
                ],
                [
                    'form_type' => Subform::REGISTRATION->value,
                    'is_required' => true,
                    'max_slots' => fake()->numberBetween(0, 50),
                    'step_order' => 2,
                    'visibility_rules' => [
                        'requires_steps' => [Subform::PREREGISTRATION->value],
                    ],
                    'completion_rules' => [
                        'required_fields' => ['agreed_tc'],
                    ],
                ],
                [
                    'form_type' => Subform::FEEDBACK->value,
                    'is_required' => false,
                    'max_slots' => fake()->numberBetween(0, 50),
                    'step_order' => 3,
                    'visibility_rules' => [
                        'requires_steps' => [Subform::REGISTRATION->value],
                    ],
                    'completion_rules' => [
                        'min_score' => 3,
                    ],
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
                        'is_required' => $requirementData['is_required'],
                        'max_slots' => $requirementData['max_slots'],
                        'open_from' => now(),
                        'open_to' => now()->addDays(7),
                        'step_order' => $requirementData['step_order'],
                        'is_enabled' => true,
                        'visibility_rules' => $requirementData['visibility_rules'] ?? [],
                        'completion_rules' => $requirementData['completion_rules'] ?? [],
                    ]
                );
                $createdRequirements[] = $requirement;
            }

            $totalParticipants = random_int(10, 20);
            Participant::factory()->count($totalParticipants)->create()->each(function (Participant $participant) use ($form, $createdRequirements) {
                $registration = Registration::factory()->create([
                    'participant_id' => $participant->id,
                    'event_subform_id' => $form->event_id,
                ]);

                foreach ($createdRequirements as $requirement) {
                    if (fake()->boolean(50)) {
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

        if (isset($participant->$fieldName)) {
            return $participant->$fieldName;
        }

        if ($fieldName === 'attendance_type') {
            return $registration->attendance_type;
        }

        if (str_contains($rules, 'boolean') || str_contains($rules, 'accepted')) {
            return fake()->boolean(80); // 80% true for boolean fields
        }

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

        if (str_contains($rules, 'email')) {
            return fake()->email();
        }

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
