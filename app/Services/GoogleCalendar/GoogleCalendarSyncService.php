<?php

namespace App\Services\GoogleCalendar;

use Carbon\Carbon;
use Carbon\CarbonInterface;
use Google\Client as GoogleClient;
use Google\Service\Calendar as GoogleCalendar;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use RuntimeException;
use Spatie\GoogleCalendar\Event;

class GoogleCalendarSyncService
{
    public function isConfigured(): bool
    {
        return $this->configurationStatus()['configured'];
    }

    public function status(): array
    {
        $configuration = $this->configurationStatus();

        return [
            'configured' => $configuration['configured'],
            'sync_enabled' => (bool) config('google-calendar.sync_enabled'),
            'auth_profile' => config('google-calendar.default_auth_profile'),
            'timezone' => config('google-calendar.portal.timezone', config('app.timezone', 'Asia/Manila')),
            'configuration_issue' => $configuration['issue'],
            'configuration_message' => $configuration['message'],
            'oauth_connectable' => $this->canStartOauthFlow(),
            'oauth_connected' => $this->hasOauthToken(),
        ];
    }

    public function getOauthAuthorizationUrl(int|string|null $userId = null): string
    {
        if ((string) config('google-calendar.default_auth_profile') !== 'oauth') {
            throw new RuntimeException('Google Calendar OAuth connect is only available when the oauth auth profile is active.');
        }

        $credentialsPath = trim((string) config('google-calendar.auth_profiles.oauth.credentials_json'));
        $credentials = $credentialsPath !== '' && is_file($credentialsPath)
            ? $this->readJsonFile($credentialsPath)
            : null;

        if ($credentials === null || (!isset($credentials['web']) && !isset($credentials['installed']))) {
            throw new RuntimeException('Google Calendar OAuth credentials are missing or invalid.');
        }

        $state = Str::random(64);

        session([
            'google_calendar_oauth_state' => $state,
            'google_calendar_oauth_user_id' => $userId,
        ]);

        $client = $this->makeOauthClient();
        $client->setState($state);

        return $client->createAuthUrl();
    }

    public function handleOauthCallback(?string $state, ?string $code, int|string|null $userId = null): void
    {
        if ((string) config('google-calendar.default_auth_profile') !== 'oauth') {
            throw new RuntimeException('Google Calendar OAuth callback is only available when the oauth auth profile is active.');
        }

        $expectedState = session('google_calendar_oauth_state');
        $expectedUserId = session('google_calendar_oauth_user_id');

        session()->forget(['google_calendar_oauth_state', 'google_calendar_oauth_user_id']);

        if (!is_string($expectedState) || $expectedState === '' || !hash_equals($expectedState, (string) $state)) {
            throw new RuntimeException('Google Calendar OAuth state is invalid or has expired.');
        }

        if ($expectedUserId !== null && (string) $expectedUserId !== (string) $userId) {
            throw new RuntimeException('Google Calendar OAuth session does not match the current user.');
        }

        if (!is_string($code) || trim($code) === '') {
            throw new RuntimeException('Google Calendar OAuth authorization code is missing.');
        }

        $client = $this->makeOauthClient();
        $token = $client->fetchAccessTokenWithAuthCode($code);

        if (!is_array($token) || isset($token['error'])) {
            $message = is_array($token) ? ($token['error_description'] ?? $token['error'] ?? null) : null;
            throw new RuntimeException($message ?: 'Google Calendar OAuth token exchange failed.');
        }

        $existingToken = $this->readJsonFile($this->oauthTokenPath()) ?? [];

        if (empty($token['refresh_token']) && !empty($existingToken['refresh_token'])) {
            $token['refresh_token'] = $existingToken['refresh_token'];
        }

        $this->storeOauthToken($token);
    }

    public function disconnectOauth(): void
    {
        $tokenPath = $this->oauthTokenPath();

        if (is_file($tokenPath)) {
            @unlink($tokenPath);
        }
    }

    public function listEvents(?CarbonInterface $start = null, ?CarbonInterface $end = null): Collection
    {
        $this->guardConfiguration();

        [$rangeStart, $rangeEnd] = $this->resolveRange($start, $end);

        return Event::get($rangeStart, $rangeEnd, [
            'singleEvents' => true,
            'orderBy' => 'startTime',
        ])->map(fn (Event $event) => $this->transformGoogleEvent($event));
    }

    public function syncPortalEvent(array $payload): array
    {
        $this->guardConfiguration();

        $portalEventKey = $this->portalEventKey($payload);
        $existingEvent = $this->findByPortalEventKey($portalEventKey, $payload);

        $attributes = $this->buildGoogleAttributes($payload, $portalEventKey);

        $event = $existingEvent
            ? $existingEvent->update($attributes)
            : Event::create($attributes);

        return $this->transformGoogleEvent($event);
    }

    public function syncPortalEvents(array $events): array
    {
        $this->guardConfiguration();

        return collect($events)
            ->map(fn (array $event) => $this->syncPortalEvent($event))
            ->all();
    }

    private function guardConfiguration(): void
    {
        $configuration = $this->configurationStatus();

        if (!$configuration['configured']) {
            throw new RuntimeException($configuration['message']);
        }
    }

    private function configurationStatus(): array
    {
        if (!config('google-calendar.sync_enabled')) {
            return [
                'configured' => false,
                'issue' => 'sync_disabled',
                'message' => 'Google Calendar sync is disabled in configuration.',
            ];
        }

        $calendarId = trim((string) config('google-calendar.calendar_id'));

        if ($calendarId === '') {
            return [
                'configured' => false,
                'issue' => 'missing_calendar_id',
                'message' => 'Google Calendar ID is missing.',
            ];
        }

        $profile = trim((string) config('google-calendar.default_auth_profile', 'service_account'));

        if (!in_array($profile, ['service_account', 'oauth'], true)) {
            return [
                'configured' => false,
                'issue' => 'invalid_auth_profile',
                'message' => 'Google Calendar auth profile must be service_account or oauth.',
            ];
        }

        $credentialsPath = trim((string) config("google-calendar.auth_profiles.{$profile}.credentials_json"));

        if ($credentialsPath === '' || !is_file($credentialsPath)) {
            $missingCredentialsMessage = 'Google Calendar credentials file is missing.';

            if ($profile === 'service_account') {
                $oauthCredentialsPath = trim((string) config('google-calendar.auth_profiles.oauth.credentials_json'));
                $oauthCredentials = $oauthCredentialsPath !== '' && is_file($oauthCredentialsPath)
                    ? $this->readJsonFile($oauthCredentialsPath)
                    : null;

                if ($oauthCredentials !== null && (isset($oauthCredentials['web']) || isset($oauthCredentials['installed']))) {
                    $missingCredentialsMessage = 'Service-account credentials are missing. The available Google credential file is an OAuth client secret and cannot be used for the service_account profile.';
                }
            }

            return [
                'configured' => false,
                'issue' => 'missing_credentials_file',
                'message' => $missingCredentialsMessage,
            ];
        }

        if ($this->isPublicPath($credentialsPath)) {
            return [
                'configured' => false,
                'issue' => 'public_credentials_path',
                'message' => 'Google Calendar credentials must be stored outside the public directory.',
            ];
        }

        $credentials = $this->readJsonFile($credentialsPath);

        if ($credentials === null) {
            return [
                'configured' => false,
                'issue' => 'invalid_credentials_json',
                'message' => 'Google Calendar credentials JSON could not be read.',
            ];
        }

        if ($profile === 'service_account') {
            if (($credentials['type'] ?? null) !== 'service_account'
                || empty($credentials['client_email'])
                || empty($credentials['private_key'])) {
                return [
                    'configured' => false,
                    'issue' => 'invalid_service_account_credentials',
                    'message' => 'The selected service_account profile requires a Google service-account JSON key.',
                ];
            }

            return [
                'configured' => true,
                'issue' => null,
                'message' => 'Google Calendar is connected through a server-side credential flow.',
            ];
        }

        if (!isset($credentials['web']) && !isset($credentials['installed'])) {
            return [
                'configured' => false,
                'issue' => 'invalid_oauth_credentials',
                'message' => 'The selected oauth profile requires a Google OAuth client credentials JSON file.',
            ];
        }

        $tokenPath = trim((string) config('google-calendar.auth_profiles.oauth.token_json'));

        if ($tokenPath === '' || !is_file($tokenPath)) {
            return [
                'configured' => false,
                'issue' => 'missing_oauth_token',
                'message' => 'Google Calendar OAuth token is missing. Complete the OAuth consent flow first.',
            ];
        }

        if ($this->isPublicPath($tokenPath)) {
            return [
                'configured' => false,
                'issue' => 'public_oauth_token_path',
                'message' => 'Google Calendar OAuth tokens must be stored outside the public directory.',
            ];
        }

        $token = $this->readJsonFile($tokenPath);

        if ($token === null || (empty($token['access_token']) && empty($token['refresh_token']))) {
            return [
                'configured' => false,
                'issue' => 'invalid_oauth_token',
                'message' => 'Google Calendar OAuth token JSON is invalid.',
            ];
        }

        return [
            'configured' => true,
            'issue' => null,
            'message' => 'Google Calendar is connected through a server-side credential flow.',
        ];
    }

    private function canStartOauthFlow(): bool
    {
        if ((string) config('google-calendar.default_auth_profile') !== 'oauth') {
            return false;
        }

        $credentialsPath = trim((string) config('google-calendar.auth_profiles.oauth.credentials_json'));

        if ($credentialsPath === '' || !is_file($credentialsPath) || $this->isPublicPath($credentialsPath)) {
            return false;
        }

        $credentials = $this->readJsonFile($credentialsPath);

        return is_array($credentials) && (isset($credentials['web']) || isset($credentials['installed']));
    }

    private function hasOauthToken(): bool
    {
        $tokenPath = $this->oauthTokenPath();
        $token = $tokenPath !== '' && is_file($tokenPath) ? $this->readJsonFile($tokenPath) : null;

        return is_array($token) && (!empty($token['access_token']) || !empty($token['refresh_token']));
    }

    private function resolveRange(?CarbonInterface $start = null, ?CarbonInterface $end = null): array
    {
        $timezone = config('google-calendar.portal.timezone', config('app.timezone', 'Asia/Manila'));
        $before = (int) config('google-calendar.portal.window_months_before', 1);
        $after = (int) config('google-calendar.portal.window_months_after', 3);

        $rangeStart = $start
            ? Carbon::instance($start->toDateTime())->timezone($timezone)
            : now($timezone)->subMonths($before)->startOfMonth();

        $rangeEnd = $end
            ? Carbon::instance($end->toDateTime())->timezone($timezone)
            : now($timezone)->addMonths($after)->endOfMonth();

        return [$rangeStart, $rangeEnd];
    }

    private function buildGoogleAttributes(array $payload, string $portalEventKey): array
    {
        $attributes = [
            'name' => Str::limit((string) $payload['label'], 255, ''),
            'description' => $this->buildDescription($payload),
            'extendedProperties.private.portal_event_key' => $portalEventKey,
            'extendedProperties.private.portal_event_type' => (string) ($payload['type'] ?? 'portal'),
            'extendedProperties.private.portal_event_status' => (string) ($payload['status'] ?? ''),
        ];

        $sourceUrl = $this->safePortalUrl($payload);

        if ($sourceUrl) {
            $attributes['source'] = [
                'title' => (string) config('google-calendar.portal.event_source_title', config('app.name', 'OneCBC Portal')),
                'url' => $sourceUrl,
            ];
        }

        if (!empty($payload['location'])) {
            $attributes['location'] = Str::limit((string) $payload['location'], 255, '');
        }

        if ($this->hasTimeWindow($payload)) {
            [$startAt, $endAt] = $this->resolveTimedRange($payload);
            $attributes['startDateTime'] = $startAt;
            $attributes['endDateTime'] = $endAt;
        } else {
            [$startDate, $endDate] = $this->resolveAllDayRange($payload);
            $attributes['startDate'] = $startDate;
            $attributes['endDate'] = $endDate;
        }

        return $attributes;
    }

    private function transformGoogleEvent(Event $event): array
    {
        $extendedProperties = (array) ($event->extendedProperties ?? []);
        $privateProperties = (array) Arr::get($extendedProperties, 'private', []);
        $isAllDay = $event->startDateTime === null;

        $startDate = $event->startDateTime ?? $event->startDate;
        $endDate = $event->endDateTime ?? $event->endDate;

        return [
            'id' => (string) $event->id,
            'label' => (string) $event->name,
            'subtitle' => (string) ($event->description ?? ''),
            'description' => (string) ($event->description ?? ''),
            'location' => (string) ($event->location ?? ''),
            'source' => 'google_calendar',
            'portal_event_key' => $privateProperties['portal_event_key'] ?? null,
            'portal_event_type' => $privateProperties['portal_event_type'] ?? null,
            'portal_event_status' => $privateProperties['portal_event_status'] ?? null,
            'html_link' => $event->htmlLink,
            'date_from' => $startDate ? Carbon::parse($startDate)->toDateString() : null,
            'date_to' => $endDate
                ? ($isAllDay
                    ? Carbon::parse($endDate)->subDay()->toDateString()
                    : Carbon::parse($endDate)->toDateString())
                : null,
            'start_at' => $event->startDateTime ? Carbon::parse($event->startDateTime)->toIso8601String() : null,
            'end_at' => $event->endDateTime ? Carbon::parse($event->endDateTime)->toIso8601String() : null,
            'is_all_day' => $isAllDay,
        ];
    }

    private function findByPortalEventKey(string $portalEventKey, array $payload): ?Event
    {
        [$rangeStart, $rangeEnd] = $this->resolveRange(
            Carbon::parse($payload['date_from']),
            Carbon::parse($payload['date_to'] ?? $payload['date_from'])
        );

        return Event::get($rangeStart, $rangeEnd, [
            'singleEvents' => true,
            'privateExtendedProperty' => "portal_event_key={$portalEventKey}",
        ])->first();
    }

    private function buildDescription(array $payload): string
    {
        $lines = array_filter([
            $payload['description'] ?? null,
            !empty($payload['subtitle']) ? 'Portal Context: ' . $payload['subtitle'] : null,
            !empty($payload['status']) ? 'Status: ' . $payload['status'] : null,
            !empty($payload['type']) ? 'Type: ' . $payload['type'] : null,
            !empty($payload['location']) ? 'Location: ' . $payload['location'] : null,
        ]);

        return Str::limit(implode("\n", $lines), 2000, '');
    }

    private function portalEventKey(array $payload): string
    {
        return Str::limit((string) $payload['id'], 191, '');
    }

    private function safePortalUrl(array $payload): ?string
    {
        $portalUrl = $payload['portal_url'] ?? null;

        if (!is_string($portalUrl) || trim($portalUrl) === '') {
            return null;
        }

        $parsedUrl = parse_url($portalUrl);
        $appUrl = parse_url((string) config('app.url'));

        if (!is_array($parsedUrl) || !is_array($appUrl)) {
            return null;
        }

        if (($parsedUrl['host'] ?? null) !== ($appUrl['host'] ?? null)) {
            return null;
        }

        return $portalUrl;
    }

    private function hasTimeWindow(array $payload): bool
    {
        return !empty($payload['time_from']) && !empty($payload['time_to']);
    }

    private function resolveTimedRange(array $payload): array
    {
        $timezone = config('google-calendar.portal.timezone', config('app.timezone', 'Asia/Manila'));
        $startAt = Carbon::parse($payload['date_from'] . ' ' . $payload['time_from'], $timezone);
        $endDate = $payload['date_to'] ?? $payload['date_from'];
        $endAt = Carbon::parse($endDate . ' ' . $payload['time_to'], $timezone);

        if ($endAt->lessThanOrEqualTo($startAt)) {
            $endAt = $startAt->copy()->addHour();
        }

        return [$startAt, $endAt];
    }

    private function resolveAllDayRange(array $payload): array
    {
        $timezone = config('google-calendar.portal.timezone', config('app.timezone', 'Asia/Manila'));
        $startDate = Carbon::parse($payload['date_from'], $timezone)->startOfDay();
        $endDate = Carbon::parse($payload['date_to'] ?? $payload['date_from'], $timezone)
            ->addDay()
            ->startOfDay();

        return [$startDate, $endDate];
    }

    private function readJsonFile(string $path): ?array
    {
        $contents = @file_get_contents($path);

        if (!is_string($contents) || trim($contents) === '') {
            return null;
        }

        $decoded = json_decode($contents, true);

        return is_array($decoded) ? $decoded : null;
    }

    private function isPublicPath(string $path): bool
    {
        $normalizedPath = $this->normalizePath($path);
        $normalizedPublicPath = $this->normalizePath(public_path());

        return $normalizedPath === $normalizedPublicPath
            || str_starts_with($normalizedPath, $normalizedPublicPath . DIRECTORY_SEPARATOR);
    }

    private function normalizePath(string $path): string
    {
        return strtolower(rtrim(str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $path), DIRECTORY_SEPARATOR));
    }

    private function makeOauthClient(): GoogleClient
    {
        $client = new GoogleClient();
        $client->setAuthConfig($this->oauthCredentialsPath());
        $client->setRedirectUri(route('rentals.calendar.oauth.callback'));
        $client->setAccessType('offline');
        $client->setIncludeGrantedScopes(true);
        $client->setPrompt('consent');
        $client->setScopes([GoogleCalendar::CALENDAR]);

        return $client;
    }

    private function oauthCredentialsPath(): string
    {
        return trim((string) config('google-calendar.auth_profiles.oauth.credentials_json'));
    }

    private function oauthTokenPath(): string
    {
        return trim((string) config('google-calendar.auth_profiles.oauth.token_json'));
    }

    private function storeOauthToken(array $token): void
    {
        $tokenPath = $this->oauthTokenPath();
        $directory = dirname($tokenPath);

        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        file_put_contents($tokenPath, json_encode($token, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    }
}