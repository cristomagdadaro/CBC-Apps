<?php

return [
    'enabled' => env('REALTIME_ENABLED', true),

    'features' => [
        'inventory' => env('REALTIME_INVENTORY_ENABLED', true),
        'laboratory' => env('REALTIME_LABORATORY_ENABLED', true),
        'rentals' => env('REALTIME_RENTALS_ENABLED', true),
        'certificates' => env('REALTIME_CERTIFICATES_ENABLED', true),
        'forms' => env('REALTIME_FORMS_ENABLED', true),
        'research' => env('REALTIME_RESEARCH_ENABLED', true),
        'calendar_sync' => env('REALTIME_CALENDAR_SYNC_ENABLED', true),
    ],
];
