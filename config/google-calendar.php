<?php

$resolvePath = static function (?string $path, string $default): string {
    $candidate = trim((string) $path);

    if ($candidate === '') {
        return $default;
    }

    if (preg_match('#^(?:[A-Za-z]:)?[\\/]#', $candidate) === 1) {
        return $candidate;
    }

    if (str_starts_with($candidate, 'app/') || str_starts_with($candidate, 'app\\')) {
        return storage_path(str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $candidate));
    }

    return base_path(str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $candidate));
};

return [

    'sync_enabled' => filter_var(env('GOOGLE_CALENDAR_SYNC_ENABLED', true), FILTER_VALIDATE_BOOLEAN),

    'default_auth_profile' => env('GOOGLE_CALENDAR_AUTH_PROFILE', 'service_account'),

    'auth_profiles' => [
        'service_account' => [
            'credentials_json' => $resolvePath(
                env('GOOGLE_CALENDAR_SERVICE_ACCOUNT_JSON'),
                storage_path('app/google-calendar/service-account-credentials.json')
            ),
        ],

        'oauth' => [
            'credentials_json' => $resolvePath(
                env('GOOGLE_CALENDAR_OAUTH_CREDENTIALS_JSON'),
                storage_path('app/google-calendar/oauth-credentials.json')
            ),
            'token_json' => $resolvePath(
                env('GOOGLE_CALENDAR_OAUTH_TOKEN_JSON'),
                storage_path('app/google-calendar/oauth-token.json')
            ),
        ],
    ],

    'calendar_id' => env('GOOGLE_CALENDAR_ID'),

    'user_to_impersonate' => env('GOOGLE_CALENDAR_IMPERSONATE'),

    'portal' => [
        'event_source_title' => env('GOOGLE_CALENDAR_EVENT_SOURCE_TITLE', env('APP_NAME', 'OneCBC Portal')),
        'timezone' => env('GOOGLE_CALENDAR_TIMEZONE', env('APP_TIMEZONE', 'Asia/Manila')),
        'window_months_before' => (int) env('GOOGLE_CALENDAR_WINDOW_MONTHS_BEFORE', 1),
        'window_months_after' => (int) env('GOOGLE_CALENDAR_WINDOW_MONTHS_AFTER', 3),
    ],
];