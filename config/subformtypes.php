<?php

use App\Enums\Subform;

return [
    Subform::PREREGISTRATION->value => [
        'name' => 'required|string',
        'age' => 'required|integer',
        'address' => 'nullable|string',
    ],

    Subform::REGISTRATION->value => [
        'organization' => 'string',
        'position' => 'string',
    ],

    Subform::FEEDBACK->value => [
        'clarity_objective' => 'required|integer|in:1,2,3,4,5',
        'time_allotment' => 'required|integer|in:1,2,3,4,5',
        'attainment_objective' => 'required|integer|in:1,2,3,4,5',
        'relevance_usefulness' => 'required|integer|in:1,2,3,4,5',
        'overall_quality_content' => 'required|integer|in:1,2,3,4,5',
        'overall_quality_resource_persons' => 'required|integer|in:1,2,3,4,5',
        'time_management_organization' => 'required|integer|in:1,2,3,4,5',
        'support_staff' => 'required|integer',
        'overall_quality_activity_admin' => 'required|integer|in:1,2,3,4,5',
        'knowledge_gain' => 'required|integer|in:1,2,3,4,5',
        'comments_event_coordination' => 'required|string',
        'other_topics' => 'required|string',
    ],

    Subform::POSTTEST->value => [
        'score' => 'integer',
        'remarks' => 'string',
    ],

    Subform::POSTTEST->value => [
        'score' => 'integer',
        'remarks' => 'string',
    ],
];
