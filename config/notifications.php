<?php

use App\Enums\Role;

return [
    'enabled' => env('NOTIFICATIONS_ENABLED', true),

    'queues' => [
        'default' => env('NOTIFICATIONS_QUEUE', 'notifications'),
        'mail' => env('MAIL_QUEUE', 'mail'),
        'certificates' => env('CERTIFICATES_QUEUE', 'certificates'),
    ],

    'domains' => [
        'forms' => [
            'responses' => [
                'enabled' => env('NOTIFICATIONS_FORMS_RESPONSES_ENABLED', true),
                'queue' => env('NOTIFICATIONS_FORMS_RESPONSES_QUEUE', env('NOTIFICATIONS_QUEUE', 'notifications')),
                'option_keys' => ['event_response_notification_email'],
                'roles' => [Role::ADMIN->value],
            ],
        ],
        'inventory' => [
            'checkout' => [
                'enabled' => env('NOTIFICATIONS_INVENTORY_CHECKOUT_ENABLED', true),
                'queue' => env('NOTIFICATIONS_INVENTORY_CHECKOUT_QUEUE', env('NOTIFICATIONS_QUEUE', 'notifications')),
                'option_keys' => ['inventory_checkout_notification_emails'],
                'fallback_option_keys' => ['event_response_notification_email'],
                'roles' => [
                    Role::ADMIN->value,
                    Role::LABORATORY_MANAGER->value,
                    Role::ICT_MANAGER->value,
                ],
            ],
        ],
        'laboratory' => [
            'logs' => [
                'enabled' => env('NOTIFICATIONS_LABORATORY_LOGS_ENABLED', true),
                'queue' => env('NOTIFICATIONS_LABORATORY_LOGS_QUEUE', env('NOTIFICATIONS_QUEUE', 'notifications')),
                'option_keys' => ['laboratory_log_notification_emails'],
                'roles' => [
                    Role::ADMIN->value,
                    Role::LABORATORY_MANAGER->value,
                ],
            ],
        ],
        'ict' => [
            'logs' => [
                'enabled' => env('NOTIFICATIONS_ICT_LOGS_ENABLED', true),
                'queue' => env('NOTIFICATIONS_ICT_LOGS_QUEUE', env('NOTIFICATIONS_QUEUE', 'notifications')),
                'option_keys' => ['ict_log_notification_emails'],
                'roles' => [
                    Role::ADMIN->value,
                    Role::ICT_MANAGER->value,
                ],
            ],
        ],
        'certificates' => [
            'delivery' => [
                'enabled' => env('NOTIFICATIONS_CERTIFICATES_DELIVERY_ENABLED', true),
                'queue' => env('NOTIFICATIONS_CERTIFICATES_DELIVERY_QUEUE', env('CERTIFICATES_QUEUE', 'certificates')),
                'option_keys' => [],
                'roles' => [],
            ],
            'summary' => [
                'enabled' => env('NOTIFICATIONS_CERTIFICATES_SUMMARY_ENABLED', true),
                'queue' => env('NOTIFICATIONS_CERTIFICATES_SUMMARY_QUEUE', env('CERTIFICATES_QUEUE', 'certificates')),
                'option_keys' => ['certificate_batch_summary_notification_emails'],
                'roles' => [
                    Role::ADMIN->value,
                    Role::ADMINISTRATIVE_ASSISTANT->value,
                ],
            ],
        ],
    ],
];
