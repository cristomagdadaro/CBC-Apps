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
        'city_address' => 'nullable|string',
        'province_address' => 'nullable|string',
        'country_address' => 'nullable|string',
        'attendance_type' => 'required|string|in:Online,In-person',
        'agreed_tc' => 'accepted',
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
        'city_address' => 'nullable|string',
        'province_address' => 'nullable|string',
        'country_address' => 'nullable|string',
        'attendance_type' => 'required|string|in:Online,In-person',
        'agreed_tc' => 'accepted',
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
        'city_address' => 'nullable|string',
        'province_address' => 'nullable|string',
        'country_address' => 'nullable|string',
        'attendance_type' => 'required|string|in:Online,In-person',
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
];
