<script>
import { ref } from 'vue'

export default {
    name: 'GoogleCalendarTopic',
    setup() {
        const activeSubsection = ref('overview')

        const subsections = {
            overview: 'Overview',
            setup: 'One-Time Setup',
            usage: 'How to Sync',
            publicAccess: 'Public Viewing',
            troubleshooting: 'Troubleshooting',
        }

        return {
            activeSubsection,
            subsections,
        }
    },
}
</script>

<template>
    <div>
        <div class="mb-6 flex flex-wrap gap-2 border-b border-gray-200 dark:border-gray-700 pb-4">
            <button
                v-for="(label, id) in subsections"
                :key="id"
                @click="activeSubsection = id"
                :class="[
                    'px-3 py-2 text-sm font-medium rounded-md transition-colors',
                    activeSubsection === id
                        ? 'bg-blue-600 text-white'
                        : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200'
                ]"
            >
                {{ label }}
            </button>
        </div>

        <div v-if="activeSubsection === 'overview'" class="space-y-4">
            <p>
                The <strong>Google Calendar Integration</strong> allows OneCBC rental and schedule events to be pushed into a managed Google Calendar so they can be viewed outside the portal.
            </p>

            <div class="rounded-lg border border-blue-200 bg-blue-50 p-4 dark:border-blue-800 dark:bg-blue-950/40">
                <h4 class="font-bold text-blue-900 dark:text-blue-100">Target Google Calendar</h4>
                <p class="mt-2 text-sm text-blue-800 dark:text-blue-200">
                    Google account: <code>pin.dacbc@gmail.com</code>
                </p>
                <p class="mt-1 text-sm text-blue-800 dark:text-blue-200">
                    Calendar name: <code>OneCBC Sync Calendar</code>
                </p>
                <p class="mt-1 text-sm text-blue-800 dark:text-blue-200 break-all">
                    Calendar ID: <code>c1de7bfb8167a0d020d2826b715617d06b7d12c561dd42b4bb359e57f206876f@group.calendar.google.com</code>
                </p>
            </div>

            <h4 class="font-bold text-gray-900 dark:text-gray-100">What this integration does</h4>
            <ul class="list-disc list-inside space-y-2 text-gray-700 dark:text-gray-300">
                <li>Reads OneCBC portal events from the rental schedules page</li>
                <li>Syncs selected events into a Google Calendar through Laravel server-side APIs</li>
                <li>Keeps Google credentials and tokens on the server, not in the browser</li>
                <li>Lets administrators refresh, sync a single event, or sync all loaded events</li>
                <li>Allows Google-side public sharing so outside users can view or subscribe</li>
            </ul>

            <h4 class="font-bold text-gray-900 dark:text-gray-100">Current access model</h4>
            <ul class="list-disc list-inside space-y-2 text-gray-700 dark:text-gray-300">
                <li>The OneCBC sync page is for authenticated users with admin or administrative assistant access</li>
                <li>The current environment is configured for <code>oauth</code> authentication</li>
                <li>Public users do not sync through OneCBC directly; they subscribe from Google Calendar after the calendar is shared publicly</li>
            </ul>
        </div>

        <div v-if="activeSubsection === 'setup'" class="space-y-4">
            <h4 class="font-bold text-gray-900 dark:text-gray-100">Step 1: Prepare the Google Calendar</h4>
            <ol class="list-decimal list-inside space-y-2 text-gray-700 dark:text-gray-300">
                <li>Sign in to Google Calendar using <code>pin.dacbc@gmail.com</code></li>
                <li>Create or confirm the calendar named <strong>OneCBC Sync Calendar</strong></li>
                <li>Open <strong>Settings and sharing</strong> for that calendar</li>
                <li>Confirm the calendar ID matches <code>c1de7bfb8167a0d020d2826b715617d06b7d12c561dd42b4bb359e57f206876f@group.calendar.google.com</code></li>
            </ol>

            <h4 class="font-bold text-gray-900 dark:text-gray-100">Step 2: Configure OneCBC environment</h4>
            <div class="bg-gray-100 dark:bg-gray-800 p-4 rounded-lg text-xs overflow-x-auto">
                <pre class="text-gray-800 dark:text-gray-200"><code>GOOGLE_CALENDAR_SYNC_ENABLED=true
GOOGLE_CALENDAR_AUTH_PROFILE=oauth
GOOGLE_CALENDAR_ID=c1de7bfb8167a0d020d2826b715617d06b7d12c561dd42b4bb359e57f206876f@group.calendar.google.com
GOOGLE_CALENDAR_IMPERSONATE=pin.dacbc@gmail.com
GOOGLE_CALENDAR_OAUTH_CREDENTIALS_JSON=app/google-calendar/oauth-credentials.json
GOOGLE_CALENDAR_OAUTH_TOKEN_JSON=app/google-calendar/oauth-token.json
GOOGLE_CALENDAR_TIMEZONE=Asia/Manila</code></pre>
            </div>
            <p class="text-sm text-gray-600 dark:text-gray-400">
                Note: <code>GOOGLE_CALENDAR_IMPERSONATE</code> is only used by service-account mode. In OAuth mode, the connected Google account must have access to the target calendar.
            </p>

            <h4 class="font-bold text-gray-900 dark:text-gray-100">Step 3: Configure Google OAuth client</h4>
            <ol class="list-decimal list-inside space-y-2 text-gray-700 dark:text-gray-300">
                <li>Enable the Google Calendar API in Google Cloud</li>
                <li>Create a <strong>Web application</strong> OAuth client</li>
                <li>Save the client JSON to <code>storage/app/google-calendar/oauth-credentials.json</code></li>
                <li>Add the callback URL for your current OneCBC host</li>
            </ol>

            <div class="bg-gray-100 dark:bg-gray-800 p-4 rounded-lg text-xs overflow-x-auto">
                <pre class="text-gray-800 dark:text-gray-200"><code>http://127.0.0.1:8000/apps/rentals/calendar/google/callback
https://onecbc.philrice.gov.ph/apps/rentals/calendar/google/callback</code></pre>
            </div>

            <h4 class="font-bold text-gray-900 dark:text-gray-100">Step 4: Share the calendar with the connected account</h4>
            <p class="text-gray-700 dark:text-gray-300">
                This is the critical part for OAuth mode. The Google account that completes the OneCBC OAuth flow must already have access to the <strong>OneCBC Sync Calendar</strong>.
            </p>
            <ul class="list-disc list-inside space-y-2 text-gray-700 dark:text-gray-300">
                <li>If you connect as <code>pin.dacbc@gmail.com</code>, use that account directly during OAuth</li>
                <li>If you connect as another Google account, share the calendar with that account and give <strong>Make changes to events</strong> permission</li>
                <li>If this step is missing, Google returns <code>404 Not Found</code> during sync even if the token is valid</li>
            </ul>
        </div>

        <div v-if="activeSubsection === 'usage'" class="space-y-4">
            <h4 class="font-bold text-gray-900 dark:text-gray-100">Open the sync page</h4>
            <p class="text-gray-700 dark:text-gray-300">
                Go to <code>/apps/rentals/calendar</code>. This page loads rental schedules from OneCBC and the currently available events from Google Calendar.
            </p>

            <h4 class="font-bold text-gray-900 dark:text-gray-100">Connect Google Calendar</h4>
            <ol class="list-decimal list-inside space-y-2 text-gray-700 dark:text-gray-300">
                <li>Click <strong>Connect Google Calendar</strong> if the page shows that OAuth is not connected yet</li>
                <li>Sign in with the Google account that has access to the <strong>OneCBC Sync Calendar</strong></li>
                <li>Approve calendar access</li>
                <li>Return to OneCBC and wait for the success notice</li>
            </ol>

            <h4 class="font-bold text-gray-900 dark:text-gray-100">Sync events</h4>
            <ul class="list-disc list-inside space-y-2 text-gray-700 dark:text-gray-300">
                <li><strong>Refresh Google Events</strong> reloads the latest Google Calendar entries</li>
                <li><strong>Sync Loaded Events</strong> pushes all currently loaded portal events to Google Calendar</li>
                <li><strong>Sync to Google</strong> on a queue item syncs a single event</li>
                <li><strong>Open in Google</strong> opens the synced Google event in a new tab</li>
            </ul>

            <h4 class="font-bold text-gray-900 dark:text-gray-100">Recommended workflow</h4>
            <ol class="list-decimal list-inside space-y-2 text-gray-700 dark:text-gray-300">
                <li>Review the portal calendar first</li>
                <li>Confirm schedules, labels, requester names, dates, and locations are correct</li>
                <li>Sync only the events that should appear in the Google calendar</li>
                <li>Use <strong>Open in Google</strong> to verify the event was created in the target calendar</li>
            </ol>
        </div>

        <div v-if="activeSubsection === 'publicAccess'" class="space-y-4">
            <h4 class="font-bold text-gray-900 dark:text-gray-100">Allow public viewing</h4>
            <ol class="list-decimal list-inside space-y-2 text-gray-700 dark:text-gray-300">
                <li>Open Google Calendar using the owner of <strong>OneCBC Sync Calendar</strong></li>
                <li>Go to <strong>Settings and sharing</strong> for that calendar</li>
                <li>Enable either <strong>Make available to public</strong> or share it with your organization as needed</li>
                <li>Copy the public calendar link or the calendar ID for subscriber use</li>
            </ol>

            <h4 class="font-bold text-gray-900 dark:text-gray-100">How public users can subscribe</h4>
            <ul class="list-disc list-inside space-y-2 text-gray-700 dark:text-gray-300">
                <li>In Google Calendar, click <strong>Other calendars</strong> then <strong>Add by URL</strong> or <strong>From URL</strong> if you publish an ICS link</li>
                <li>They can also use <strong>Add calendar</strong> and provide the calendar ID if the calendar is shared publicly or directly with them</li>
                <li>Once added, updates from OneCBC syncs will appear in their personal Google Calendar view</li>
            </ul>

            <div class="rounded-lg border border-emerald-200 bg-emerald-50 p-4 dark:border-emerald-800 dark:bg-emerald-950/40">
                <p class="text-sm text-emerald-800 dark:text-emerald-200">
                    Public users do not need OneCBC accounts to subscribe, but the Google Calendar itself must be shared appropriately in Google Calendar settings.
                </p>
            </div>
        </div>

        <div v-if="activeSubsection === 'troubleshooting'" class="space-y-4">
            <h4 class="font-bold text-gray-900 dark:text-gray-100">Common issues</h4>

            <div class="space-y-3">
                <div class="rounded-lg border border-red-200 bg-red-50 p-4 dark:border-red-800 dark:bg-red-950/40">
                    <h5 class="font-semibold text-red-900 dark:text-red-100">404 Not Found from Google</h5>
                    <p class="mt-2 text-sm text-red-800 dark:text-red-200">
                        The connected Google account cannot access the configured calendar ID. Verify the calendar exists, the ID is correct, and the OAuth account has permission to that calendar.
                    </p>
                </div>

                <div class="rounded-lg border border-amber-200 bg-amber-50 p-4 dark:border-amber-800 dark:bg-amber-950/40">
                    <h5 class="font-semibold text-amber-900 dark:text-amber-100">OAuth connected but sync still fails</h5>
                    <p class="mt-2 text-sm text-amber-800 dark:text-amber-200">
                        OAuth only proves the token is valid. It does not grant access to every calendar automatically. Share the target calendar with the connected account.
                    </p>
                </div>

                <div class="rounded-lg border border-slate-200 bg-slate-50 p-4 dark:border-slate-700 dark:bg-slate-800/60">
                    <h5 class="font-semibold text-slate-900 dark:text-slate-100">Credentials or token not found</h5>
                    <p class="mt-2 text-sm text-slate-700 dark:text-slate-300">
                        Check that the JSON files are stored under <code>storage/app/google-calendar/</code> and that the environment paths point there correctly.
                    </p>
                </div>
            </div>

            <h4 class="font-bold text-gray-900 dark:text-gray-100">Quick checklist</h4>
            <ul class="list-disc list-inside space-y-2 text-gray-700 dark:text-gray-300">
                <li><code>GOOGLE_CALENDAR_SYNC_ENABLED=true</code></li>
                <li><code>GOOGLE_CALENDAR_AUTH_PROFILE=oauth</code></li>
                <li>The target Google calendar ID matches the real calendar settings</li>
                <li>The OAuth user has <strong>Make changes to events</strong> access</li>
                <li>The callback URL is registered in Google Cloud</li>
                <li>Config cache has been cleared after environment changes</li>
            </ul>
        </div>
    </div>
</template>