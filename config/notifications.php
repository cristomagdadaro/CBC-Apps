<?php

use App\Enums\Role;

return [
    'enabled' => env('NOTIFICATIONS_ENABLED', true),

    'grouped' => [
        'to' => [
            'address' => env('NOTIFICATIONS_GROUPED_TO_ADDRESS', env('MAIL_FROM_ADDRESS')),
            'name' => env('NOTIFICATIONS_GROUPED_TO_NAME', env('MAIL_FROM_NAME')),
        ],
        'chunk_size' => env('NOTIFICATIONS_GROUPED_CHUNK_SIZE'),
    ],

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
                'delivery_mode' => env('NOTIFICATIONS_FORMS_RESPONSES_DELIVERY_MODE', 'grouped'),
                'option_keys' => ['event_response_notification_email'],
                'roles' => [Role::ADMIN->value],
            ],
        ],
        'inventory' => [
            'checkout' => [
                'enabled' => env('NOTIFICATIONS_INVENTORY_CHECKOUT_ENABLED', true),
                'queue' => env('NOTIFICATIONS_INVENTORY_CHECKOUT_QUEUE', env('NOTIFICATIONS_QUEUE', 'notifications')),
                'delivery_mode' => env('NOTIFICATIONS_INVENTORY_CHECKOUT_DELIVERY_MODE', 'grouped'),
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
                'delivery_mode' => env('NOTIFICATIONS_LABORATORY_LOGS_DELIVERY_MODE', 'grouped'),
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
                'delivery_mode' => env('NOTIFICATIONS_ICT_LOGS_DELIVERY_MODE', 'grouped'),
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
                'delivery_mode' => env('NOTIFICATIONS_CERTIFICATES_DELIVERY_DELIVERY_MODE', 'individual'),
                'option_keys' => [],
                'roles' => [],
            ],
            'summary' => [
                'enabled' => env('NOTIFICATIONS_CERTIFICATES_SUMMARY_ENABLED', true),
                'queue' => env('NOTIFICATIONS_CERTIFICATES_SUMMARY_QUEUE', env('CERTIFICATES_QUEUE', 'certificates')),
                'delivery_mode' => env('NOTIFICATIONS_CERTIFICATES_SUMMARY_DELIVERY_MODE', 'grouped'),
                'option_keys' => ['certificate_batch_summary_notification_emails'],
                'roles' => [
                    Role::ADMIN->value,
                    Role::ADMINISTRATIVE_ASSISTANT->value,
                ],
            ],
        ],
    ],
];
