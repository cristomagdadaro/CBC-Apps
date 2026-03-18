# Google Calendar Integration

## Security model

- Sync runs through Laravel only. Vue never receives Google credentials.
- Store Google credential JSON files under `storage/app/google-calendar/`, never under `public/`.
- Do not commit Google credential JSON files. `.gitignore` excludes them.
- Keep sync routes behind authenticated middleware. In this repo they are served from authenticated API routes only.

## Required environment variables

- `GOOGLE_CALENDAR_SYNC_ENABLED=true`
- `GOOGLE_CALENDAR_ID=your-calendar-id@group.calendar.google.com`
- `GOOGLE_CALENDAR_AUTH_PROFILE=service_account` or `oauth`
- `GOOGLE_CALENDAR_SERVICE_ACCOUNT_JSON=app/google-calendar/service-account-credentials.json`
- `GOOGLE_CALENDAR_OAUTH_CREDENTIALS_JSON=app/google-calendar/oauth-credentials.json`
- `GOOGLE_CALENDAR_OAUTH_TOKEN_JSON=app/google-calendar/oauth-token.json`
- `GOOGLE_CALENDAR_TIMEZONE=Asia/Manila`

## Service account setup

1. Create a Google Cloud project and enable the Google Calendar API.
2. Create a service account and download the JSON key.
3. Save the JSON key to `storage/app/google-calendar/service-account-credentials.json`.
4. Share the target Google Calendar with the service account email and grant `Make changes to events` access.
5. Set `GOOGLE_CALENDAR_ID` in the environment and clear cached config if needed.

## OAuth setup

1. Create a Google OAuth client for a web application and enable the Google Calendar API.
2. Save the client JSON to `storage/app/google-calendar/oauth-credentials.json`.
3. Set `GOOGLE_CALENDAR_AUTH_PROFILE=oauth`.
4. Add the app callback URL to the OAuth client redirect URIs: `/apps/rentals/calendar/google/callback` under the active `APP_URL` host.
5. Open the rentals Google Calendar page and use the `Connect Google Calendar` action to complete consent and generate `storage/app/google-calendar/oauth-token.json`.

## Available routes

- `GET /api/google-calendar` returns synced Google events for the configured window.
- `POST /api/google-calendar/sync` upserts one portal event into Google Calendar.
- `POST /api/google-calendar/sync-batch` upserts up to 50 portal events in one request.
- `POST /api/google-calendar/disconnect` removes the stored OAuth token.
- `GET /apps/rentals/calendar/google/connect` starts the protected OAuth flow.
- `GET /apps/rentals/calendar/google/callback` receives the OAuth callback and stores the token server-side.

## UI entry point

- Authenticated rentals page: `apps/rentals/calendar`
- Inertia page: `resources/js/Pages/Rentals/RentalsGoogleCalendar.vue`
- Reusable component: `resources/js/Components/GoogleCalendarModule.vue`
