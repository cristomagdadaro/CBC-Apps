<?php

use App\Enums\Subform;

return [
    // Preregistration form fields
    Subform::PREREGISTRATION->value => [
        'name' => 'required|string',
        'age' => 'required|integer',
        'address' => 'nullable|string',
    ],

    // Registration form fields
    Subform::REGISTRATION->value => [
        'organization' => 'string',
        'position' => 'string',
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
