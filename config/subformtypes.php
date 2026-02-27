<?php

use App\Enums\Subform;

return [
    // Preregistration form fields
    Subform::PREREGISTRATION->value => [
        'name' => 'required|string',
        'email' => 'required|email',
        'phone' => 'required|string',
        'sex' => 'nullable|string|in:Male,Female,Prefer not to say',
        'age' => 'required|integer',
        'organization' => 'required|string',
        'designation' => 'required|string',
        'is_ip' => 'boolean',
        'is_pwd' => 'boolean',
        'city_address' => 'nullable|string|exists:loc_cities,city',
        'province_address' => 'nullable|string|exists:loc_cities,province',
        'region_address' => 'nullable|string|exists:loc_cities,region',
        'country_address' => 'nullable|string',
        'attendance_type' => 'nullable|string|in:Online,In-person',
        'agreed_tc' => 'accepted',
        'agreed_updates' => 'boolean|nullable',
    ],

    // Registration form fields
    Subform::REGISTRATION->value => [
        'name' => 'required|string',
        'email' => 'required|email',
        'phone' => 'required|string',
        'sex' => 'nullable|string|in:Male,Female,Prefer not to say',
        'age' => 'required|integer',
        'organization' => 'required|string',
        'designation' => 'required|string',
        'is_ip' => 'boolean',
        'is_pwd' => 'boolean',
        'city_address' => 'nullable|string|exists:loc_cities,city',
        'province_address' => 'nullable|string|exists:loc_cities,province',
        'region_address' => 'nullable|string|exists:loc_cities,region',
        'country_address' => 'nullable|string',
        'attendance_type' => 'nullable|string|in:Online,In-person',
        'agreed_tc' => 'accepted',
        'agreed_updates' => 'boolean|nullable',
    ],

    // Preregistration + Quiz Bee form fields
    Subform::PREREGISTRATION_BIOTECH->value => [
        'name' => 'required|string',
        'email' => 'required|email',
        'phone' => 'required|string',
        'sex' => 'nullable|string|in:Male,Female,Prefer not to say',
        'age' => 'required|integer',
        'organization' => 'required|string',
        'designation' => 'required|string',
        'is_ip' => 'boolean',
        'is_pwd' => 'boolean',
        'city_address' => 'nullable|string|exists:loc_cities,city',
        'province_address' => 'nullable|string|exists:loc_cities,province',
        'region_address' => 'nullable|string|exists:loc_cities,region',
        'country_address' => 'nullable|string',
        'attendance_type' => 'nullable|string|in:Online,In-person',
        'join_quiz_bee' => 'boolean',
        'agreed_tc' => 'accepted',
    ],

    // Feedback form fields (expanded to include participant info & agreement)
    Subform::FEEDBACK->value => [
        'clarity_objective' => 'required|integer|in:1,2,3,4,5',
        'time_allotment' => 'required|integer|in:1,2,3,4,5',
        'attainment_objective' => 'required|integer|in:1,2,3,4,5',
        'relevance_usefulness' => 'required|integer|in:1,2,3,4,5',
        'overall_quality_content' => 'required|integer|in:1,2,3,4,5',
        'overall_quality_resource_persons' => 'required|integer|in:1,2,3,4,5',
        'time_management_organization' => 'required|integer|in:1,2,3,4,5',
        'support_staff' => 'required|integer|in:1,2,3,4,5',
        'overall_quality_activity_admin' => 'required|integer|in:1,2,3,4,5',
        'knowledge_gain' => 'required|integer|in:1,2,3,4,5',
        'comments_event_coordination' => 'nullable|string',
        'other_topics' => 'nullable|string',
        'agreed_tc' => 'accepted',
        'agreed_updates' => 'boolean|nullable',
    ],

    // Post-test form fields
    Subform::POSTTEST->value => [
        'score' => 'integer',
        'remarks' => 'string',
    ],

    // Pre-test form fields
    Subform::PRETEST->value => [
        'score' => 'integer',
        'remarks' => 'string',
    ],

    Subform::PREREGISTRATION_QUIZBEE->value => [
        'organization' => 'required|string',
        'city_address' => 'nullable|string|exists:loc_cities,city',
        'province_address' => 'nullable|string|exists:loc_cities,province',
        'region_address' => 'nullable|string|exists:loc_cities,region',
        'team_name' => 'required|string',
        'participant_1_name' => 'required|string',
        'participant_1_sex' => 'required|string|in:Male,Female,Prefer not to say',
        'participant_1_gradelevel' => 'required|string|in:Grade 11,Grade 12',
        'participant_2_name' => 'required|string',
        'participant_2_sex' => 'required|string|in:Male,Female,Prefer not to say',
        'participant_2_gradelevel' => 'required|string|in:Grade 11,Grade 12',
        'proof_of_enrollment' => 'required|file|mimes:pdf|max:2048',
        'coach_name' => 'required|string',
        'coach_email' => 'required|email',
        'coach_phone' => 'required|string',
        'agreed_tc' => 'accepted',
        'agreed_updates' => 'boolean|nullable',
    ]
];
