<script>
import { computed, ref, watchEffect } from "vue";
import TopicLayout from "../Components/TopicLayout.vue";

export default {
    name: "GoogleCalendarTopic",
    components: {
        TopicLayout,
    },
    props: {
        showDeveloperSections: {
            type: Boolean,
            default: true,
        },
    },
    setup(props) {
        const activeSubsection = ref("overview");
        const developerSubsectionIds = ["setup", "troubleshooting"];

        const subsections = {
            overview: "Overview",
            setup: "One-Time Setup",
            usage: "How to Sync",
            publicAccess: "Public Viewing",
            troubleshooting: "Troubleshooting",
        };

        const visibleSubsections = computed(() => {
            if (props.showDeveloperSections) {
                return subsections;
            }

            return Object.fromEntries(Object.entries(subsections).filter(([key]) => !developerSubsectionIds.includes(key)));
        });

        watchEffect(() => {
            if (!visibleSubsections.value[activeSubsection.value]) {
                activeSubsection.value = "overview";
            }
        });

        return {
            activeSubsection,
            visibleSubsections,
        };
    },
};
</script>

<template>
    <TopicLayout
        title="Google Calendar Integration"
        description="Allows OneCBC rental and schedule events to be pushed into a managed Google Calendar so they can be viewed outside the portal."
        icon="LuCalendarDays"
        maxWidth="max-w-5xl"
        minHeight="min-h-[400px]">
        <template #header-tabs>
            <!-- Subsection Navigation -->
            <div class="flex flex-wrap gap-2 border-b border-slate-100 bg-slate-50/50 px-5 py-4 shadow-inner dark:border-slate-800/60 dark:bg-slate-800/20">
                <button
                    v-for="(label, id) in visibleSubsections"
                    :key="id"
                    @click="activeSubsection = id"
                    class="whitespace-nowrap rounded-xl px-4 py-2 text-sm font-semibold transition-all"
                    :class="activeSubsection === id ? 'bg-indigo-600 text-white shadow-sm ring-1 ring-indigo-700' : 'text-slate-500 hover:bg-slate-200/50 hover:text-slate-800 dark:text-slate-400 dark:hover:bg-slate-700/50 dark:hover:text-slate-200'">
                    {{ label }}
                </button>
            </div>
        </template>

        <!-- Overview Section -->
        <div
            v-if="activeSubsection === 'overview'"
            class="space-y-6">
            <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                <!-- Target Calendar -->
                <div class="rounded-xl border border-sky-100 bg-sky-50/50 p-5 shadow-sm dark:border-sky-500/20 dark:bg-sky-500/5">
                    <h3 class="mb-4 flex items-center gap-1.5 text-[0.65rem] font-semibold uppercase tracking-widest text-sky-600 dark:text-sky-400">
                        <LuTarget class="h-3.5 w-3.5" />
                        Target Google Calendar
                    </h3>
                    <ul class="space-y-3.5 text-xs font-medium text-slate-700 dark:text-slate-300">
                        <li>
                            <span class="mb-1 block text-[0.6rem] font-bold uppercase tracking-widest text-slate-500 dark:text-slate-400">Google Account</span>
                            <code class="rounded-md border border-slate-200 bg-white px-1.5 py-0.5 font-mono text-[0.65rem] font-semibold text-sky-600 shadow-sm dark:border-slate-700 dark:bg-slate-800 dark:text-sky-400">pin.dacbc@gmail.com</code>
                        </li>
                        <li>
                            <span class="mb-1 block text-[0.6rem] font-bold uppercase tracking-widest text-slate-500 dark:text-slate-400">Calendar Name</span>
                            <code class="rounded-md border border-slate-200 bg-white px-1.5 py-0.5 font-mono text-[0.65rem] font-semibold text-sky-600 shadow-sm dark:border-slate-700 dark:bg-slate-800 dark:text-sky-400">OneCBC Sync Calendar</code>
                        </li>
                        <li>
                            <span class="mb-1 block text-[0.6rem] font-bold uppercase tracking-widest text-slate-500 dark:text-slate-400">Calendar ID</span>
                            <code class="break-all rounded-md border border-slate-200 bg-white px-1.5 py-0.5 font-mono text-[0.65rem] font-semibold text-sky-600 shadow-sm dark:border-slate-700 dark:bg-slate-800 dark:text-sky-400">c1de7bfb8167a0d020d2826b715617d06b7d12c561dd42b4bb359e57f206876f@group.calendar.google.com</code>
                        </li>
                    </ul>
                </div>

                <div class="space-y-6">
                    <!-- Integration Purpose -->
                    <div class="rounded-xl border border-emerald-100 bg-emerald-50/50 p-5 shadow-sm dark:border-emerald-500/20 dark:bg-emerald-500/5">
                        <h3 class="mb-3.5 flex items-center gap-1.5 text-[0.65rem] font-semibold uppercase tracking-widest text-emerald-600 dark:text-emerald-400">
                            <LuListChecks class="h-3.5 w-3.5" />
                            What this integration does
                        </h3>
                        <ul class="ml-1 space-y-2.5 text-xs font-medium text-slate-700 dark:text-slate-300">
                            <li class="flex items-start gap-2.5">
                                <LuCheckCircle2 class="mt-0.5 h-3.5 w-3.5 shrink-0 text-emerald-500" />
                                <span>Reads OneCBC portal events from the rental schedules page.</span>
                            </li>
                            <li class="flex items-start gap-2.5">
                                <LuCheckCircle2 class="mt-0.5 h-3.5 w-3.5 shrink-0 text-emerald-500" />
                                <span>Syncs selected events into a Google Calendar through Laravel APIs.</span>
                            </li>
                            <li class="flex items-start gap-2.5">
                                <LuCheckCircle2 class="mt-0.5 h-3.5 w-3.5 shrink-0 text-emerald-500" />
                                <span>Keeps Google credentials and tokens on the server, not the browser.</span>
                            </li>
                            <li class="flex items-start gap-2.5">
                                <LuCheckCircle2 class="mt-0.5 h-3.5 w-3.5 shrink-0 text-emerald-500" />
                                <span>Lets administrators refresh, sync a single event, or sync all events.</span>
                            </li>
                            <li class="flex items-start gap-2.5">
                                <LuCheckCircle2 class="mt-0.5 h-3.5 w-3.5 shrink-0 text-emerald-500" />
                                <span>Allows Google-side public sharing so outside users can subscribe.</span>
                            </li>
                        </ul>
                    </div>

                    <!-- Access Model -->
                    <div class="rounded-xl border border-purple-100 bg-purple-50/50 p-5 shadow-sm dark:border-purple-500/20 dark:bg-purple-500/5">
                        <h3 class="mb-3.5 flex items-center gap-1.5 text-[0.65rem] font-semibold uppercase tracking-widest text-purple-600 dark:text-purple-400">
                            <LuShieldCheck class="h-3.5 w-3.5" />
                            Current Access Model
                        </h3>
                        <ul class="ml-1 space-y-2.5 text-xs font-medium text-slate-700 dark:text-slate-300">
                            <li class="flex items-start gap-2.5">
                                <div class="mt-1 h-1.5 w-1.5 shrink-0 rounded-full bg-purple-400"></div>
                                <span>The OneCBC sync page is for authenticated users with admin access.</span>
                            </li>
                            <li class="flex items-start gap-2.5">
                                <div class="mt-1 h-1.5 w-1.5 shrink-0 rounded-full bg-purple-400"></div>
                                <span>
                                    The current environment is configured for
                                    <code class="rounded border border-slate-200 bg-white px-1 py-0.5 font-mono text-[0.6rem] dark:border-slate-700 dark:bg-slate-800">oauth</code>
                                    authentication.
                                </span>
                            </li>
                            <li class="flex items-start gap-2.5">
                                <div class="mt-1 h-1.5 w-1.5 shrink-0 rounded-full bg-purple-400"></div>
                                <span>Public users do not sync through OneCBC directly; they subscribe from Google Calendar after it is shared publicly.</span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <!-- Setup Section (Developers) -->
        <div
            v-if="showDeveloperSections && activeSubsection === 'setup'"
            class="space-y-6">
            <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                <!-- Step 1 & 4 (Process) -->
                <div class="space-y-6">
                    <div class="rounded-xl border border-slate-200/60 bg-slate-50/50 p-5 shadow-sm dark:border-slate-700/60 dark:bg-slate-800/30">
                        <h4 class="mb-3 flex items-center gap-1.5 text-[0.65rem] font-semibold uppercase tracking-widest text-slate-600 dark:text-slate-300">
                            <LuCalendarDays class="h-3.5 w-3.5" />
                            Step 1: Prepare Google Calendar
                        </h4>
                        <ol class="ml-1 list-inside list-decimal space-y-2.5 text-xs font-medium text-slate-600 dark:text-slate-300">
                            <li>
                                Sign in to Google Calendar using
                                <code class="rounded border border-slate-200 bg-white px-1.5 py-0.5 font-mono text-[0.6rem] dark:border-slate-700 dark:bg-slate-800">pin.dacbc@gmail.com</code>
                                .
                            </li>
                            <li>
                                Create or confirm the calendar named
                                <span class="font-semibold text-slate-800 dark:text-slate-200">OneCBC Sync Calendar</span>
                                .
                            </li>
                            <li>
                                Open
                                <span class="font-semibold text-slate-800 dark:text-slate-200">Settings and sharing</span>
                                for that calendar.
                            </li>
                            <li>Confirm the calendar ID matches the configured ID.</li>
                        </ol>
                    </div>

                    <div class="rounded-xl border border-amber-100 bg-amber-50/50 p-5 shadow-sm dark:border-amber-500/20 dark:bg-amber-500/5">
                        <h4 class="mb-3 flex items-center gap-1.5 text-[0.65rem] font-semibold uppercase tracking-widest text-amber-600 dark:text-amber-400">
                            <LuKey class="h-3.5 w-3.5" />
                            Step 4: Share the Calendar
                        </h4>
                        <p class="mb-3 text-xs font-medium leading-relaxed text-slate-600 dark:text-slate-300">
                            This is critical for OAuth mode. The Google account that completes the OneCBC OAuth flow
                            <span class="font-bold">must</span>
                            already have access to the
                            <span class="font-semibold text-slate-800 dark:text-slate-200">OneCBC Sync Calendar</span>
                            .
                        </p>
                        <ul class="ml-1 space-y-2.5 text-xs font-medium text-slate-600 dark:text-slate-300">
                            <li class="flex items-start gap-2.5">
                                <div class="mt-1 h-1.5 w-1.5 shrink-0 rounded-full bg-amber-400"></div>
                                <span>
                                    If you connect as
                                    <code class="font-mono text-[0.6rem]">pin.dacbc@gmail.com</code>
                                    , use it directly.
                                </span>
                            </li>
                            <li class="flex items-start gap-2.5">
                                <div class="mt-1 h-1.5 w-1.5 shrink-0 rounded-full bg-amber-400"></div>
                                <span>
                                    If connecting as another account, share the calendar with that account and grant
                                    <span class="font-semibold text-slate-800 dark:text-slate-200">Make changes to events</span>
                                    permission.
                                </span>
                            </li>
                            <li class="flex items-start gap-2.5">
                                <div class="mt-1 h-1.5 w-1.5 shrink-0 rounded-full bg-amber-400"></div>
                                <span class="font-semibold text-rose-600 dark:text-rose-400">If missing, Google returns 404 Not Found during sync.</span>
                            </li>
                        </ul>
                    </div>
                </div>

                <!-- Step 2 & 3 (Code/Config) -->
                <div class="space-y-6">
                    <div class="space-y-2">
                        <h4 class="ml-1 text-[0.65rem] font-semibold uppercase tracking-widest text-slate-500 dark:text-slate-400">Step 2: Configure Environment</h4>
                        <div class="overflow-hidden rounded-xl border border-slate-200 bg-[#1e1e1e] shadow-inner dark:border-[#2d2d2d]">
                            <div class="flex items-center border-b border-[#1e1e1e] bg-[#2d2d2d] px-4 py-2.5">
                                <div class="flex gap-1.5">
                                    <div class="h-2.5 w-2.5 rounded-full bg-[#ff5f56]"></div>
                                    <div class="h-2.5 w-2.5 rounded-full bg-[#ffbd2e]"></div>
                                    <div class="h-2.5 w-2.5 rounded-full bg-[#27c93f]"></div>
                                </div>
                                <div class="ml-4 font-mono text-[0.65rem] font-medium tracking-wide text-[#858585]">.env</div>
                            </div>
                            <pre class="vscode-scrollbar overflow-x-auto p-4 font-mono text-[0.7rem] leading-loose text-[#d4d4d4]"><code class="!bg-transparent !p-0 !border-0 !shadow-none !text-inherit"><span class="text-[#9cdcfe]">GOOGLE_CALENDAR_SYNC_ENABLED</span>=<span class="text-[#569cd6]">true</span>
<span class="text-[#9cdcfe]">GOOGLE_CALENDAR_AUTH_PROFILE</span>=<span class="text-[#ce9178]">oauth</span>
<span class="text-[#9cdcfe]">GOOGLE_CALENDAR_ID</span>=<span class="text-[#ce9178]">c1de7bfb81...</span>
<span class="text-[#9cdcfe]">GOOGLE_CALENDAR_IMPERSONATE</span>=<span class="text-[#ce9178]">pin.dacbc@gmail.com</span>
<span class="text-[#9cdcfe]">GOOGLE_CALENDAR_OAUTH_CREDENTIALS_JSON</span>=<span class="text-[#ce9178]">app/google-calendar/oauth-credentials.json</span>
<span class="text-[#9cdcfe]">GOOGLE_CALENDAR_OAUTH_TOKEN_JSON</span>=<span class="text-[#ce9178]">app/google-calendar/oauth-token.json</span>
<span class="text-[#9cdcfe]">GOOGLE_CALENDAR_TIMEZONE</span>=<span class="text-[#ce9178]">Asia/Manila</span></code></pre>
                        </div>
                        <p class="mt-2 px-1 text-[0.65rem] font-medium text-slate-500 dark:text-slate-400">
                            <span class="font-semibold">Note:</span>
                            <code class="font-mono text-[0.6rem]">IMPERSONATE</code>
                            is only used by service-account mode.
                        </p>
                    </div>

                    <div class="space-y-2">
                        <h4 class="ml-1 text-[0.65rem] font-semibold uppercase tracking-widest text-slate-500 dark:text-slate-400">Step 3: Configure Google OAuth Client</h4>
                        <ol class="mb-3 ml-1 list-inside list-decimal space-y-1.5 text-xs font-medium text-slate-600 dark:text-slate-300">
                            <li>Enable the Google Calendar API in Google Cloud.</li>
                            <li>
                                Create a
                                <span class="font-semibold text-slate-800 dark:text-slate-200">Web application</span>
                                OAuth client.
                            </li>
                            <li>
                                Save the client JSON to
                                <code class="rounded border border-slate-200 bg-slate-100 px-1 py-0.5 font-mono text-[0.6rem] dark:border-slate-700 dark:bg-slate-800">storage/app/google-calendar/oauth-credentials.json</code>
                                .
                            </li>
                            <li>Add the callback URLs for your current OneCBC host:</li>
                        </ol>
                        <div class="overflow-hidden rounded-xl border border-slate-200 bg-[#1e1e1e] shadow-inner dark:border-[#2d2d2d]">
                            <div class="flex items-center border-b border-[#1e1e1e] bg-[#2d2d2d] px-4 py-2.5">
                                <div class="flex gap-1.5">
                                    <div class="h-2.5 w-2.5 rounded-full bg-[#ff5f56]"></div>
                                    <div class="h-2.5 w-2.5 rounded-full bg-[#ffbd2e]"></div>
                                    <div class="h-2.5 w-2.5 rounded-full bg-[#27c93f]"></div>
                                </div>
                                <div class="ml-4 font-mono text-[0.65rem] font-medium tracking-wide text-[#858585]">Callback URIs</div>
                            </div>
                            <pre class="vscode-scrollbar overflow-x-auto p-4 font-mono text-[0.7rem] leading-loose text-[#d4d4d4]"><code class="!bg-transparent !p-0 !border-0 !shadow-none !text-inherit"><span class="text-[#ce9178]">http://127.0.0.1:8000/apps/rentals/calendar/google/callback</span>
<span class="text-[#ce9178]">https://onecbc.philrice.gov.ph/apps/rentals/calendar/google/callback</span></code></pre>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Usage Section -->
        <div
            v-if="activeSubsection === 'usage'"
            class="space-y-6">
            <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                <div class="rounded-xl border border-indigo-100 bg-indigo-50/50 p-5 shadow-sm dark:border-indigo-500/20 dark:bg-indigo-500/10">
                    <h4 class="mb-3 flex items-center gap-1.5 text-[0.65rem] font-semibold uppercase tracking-widest text-indigo-600 dark:text-indigo-400">
                        <LuLinkIcon class="h-3.5 w-3.5" />
                        1. Connect Google Calendar
                    </h4>
                    <p class="mb-3 text-xs font-medium leading-relaxed text-slate-600 dark:text-slate-400">
                        Go to
                        <code class="rounded border border-slate-200 bg-white px-1 py-0.5 font-mono text-[0.6rem] shadow-sm dark:border-slate-700 dark:bg-slate-800">/apps/rentals/calendar</code>
                        . This page loads rental schedules from OneCBC and available events from Google.
                    </p>
                    <ol class="ml-1 list-inside list-decimal space-y-2 text-xs font-medium text-slate-700 dark:text-slate-300">
                        <li>
                            Click
                            <span class="font-semibold text-slate-800 dark:text-slate-200">Connect Google Calendar</span>
                            if not connected.
                        </li>
                        <li>Sign in with an authorized Google account.</li>
                        <li>Approve calendar access.</li>
                        <li>Return to OneCBC and wait for success notice.</li>
                    </ol>
                </div>

                <div class="rounded-xl border border-emerald-100 bg-emerald-50/50 p-5 shadow-sm dark:border-emerald-500/20 dark:bg-emerald-500/10">
                    <h4 class="mb-3 flex items-center gap-1.5 text-[0.65rem] font-semibold uppercase tracking-widest text-emerald-600 dark:text-emerald-400">
                        <LuSettings2 class="h-3.5 w-3.5" />
                        2. Sync Actions
                    </h4>
                    <ul class="ml-1 space-y-2.5 text-xs font-medium text-slate-700 dark:text-slate-300">
                        <li class="flex items-start gap-2.5">
                            <div class="mt-1 h-1.5 w-1.5 shrink-0 rounded-full bg-emerald-400"></div>
                            <span>
                                <span class="font-semibold text-slate-800 dark:text-slate-200">Refresh Google Events:</span>
                                Reloads the latest Google entries.
                            </span>
                        </li>
                        <li class="flex items-start gap-2.5">
                            <div class="mt-1 h-1.5 w-1.5 shrink-0 rounded-full bg-emerald-400"></div>
                            <span>
                                <span class="font-semibold text-slate-800 dark:text-slate-200">Sync Loaded Events:</span>
                                Pushes all currently loaded portal events.
                            </span>
                        </li>
                        <li class="flex items-start gap-2.5">
                            <div class="mt-1 h-1.5 w-1.5 shrink-0 rounded-full bg-emerald-400"></div>
                            <span>
                                <span class="font-semibold text-slate-800 dark:text-slate-200">Sync to Google:</span>
                                Syncs a single specific event from the queue.
                            </span>
                        </li>
                        <li class="flex items-start gap-2.5">
                            <div class="mt-1 h-1.5 w-1.5 shrink-0 rounded-full bg-emerald-400"></div>
                            <span>
                                <span class="font-semibold text-slate-800 dark:text-slate-200">Open in Google:</span>
                                Opens the synced Google event in a new tab.
                            </span>
                        </li>
                    </ul>
                </div>

                <div class="rounded-xl border border-slate-200/60 bg-slate-50/50 p-5 shadow-sm md:col-span-2 dark:border-slate-700/60 dark:bg-slate-800/30">
                    <h4 class="mb-3 flex items-center gap-1.5 text-[0.65rem] font-semibold uppercase tracking-widest text-slate-500 dark:text-slate-400">
                        <LuCheckSquare class="h-3.5 w-3.5" />
                        Recommended Workflow
                    </h4>
                    <div class="flex flex-col justify-between gap-4 sm:flex-row">
                        <div class="flex-1 rounded-lg border border-slate-100 bg-white p-3 dark:border-slate-700 dark:bg-slate-800/80">
                            <span class="mb-1 block text-xl font-black text-slate-300 dark:text-slate-600">1</span>
                            <p class="text-xs font-medium text-slate-600 dark:text-slate-300">Review the portal calendar first.</p>
                        </div>
                        <div class="flex-1 rounded-lg border border-slate-100 bg-white p-3 dark:border-slate-700 dark:bg-slate-800/80">
                            <span class="mb-1 block text-xl font-black text-slate-300 dark:text-slate-600">2</span>
                            <p class="text-xs font-medium text-slate-600 dark:text-slate-300">Confirm schedules, labels, requester names, dates, and locations.</p>
                        </div>
                        <div class="flex-1 rounded-lg border border-slate-100 bg-white p-3 dark:border-slate-700 dark:bg-slate-800/80">
                            <span class="mb-1 block text-xl font-black text-slate-300 dark:text-slate-600">3</span>
                            <p class="text-xs font-medium text-slate-600 dark:text-slate-300">Sync only the events that should appear in the Google calendar.</p>
                        </div>
                        <div class="flex-1 rounded-lg border border-slate-100 bg-white p-3 dark:border-slate-700 dark:bg-slate-800/80">
                            <span class="mb-1 block text-xl font-black text-slate-300 dark:text-slate-600">4</span>
                            <p class="text-xs font-medium text-slate-600 dark:text-slate-300">
                                Use
                                <span class="font-semibold">Open in Google</span>
                                to verify creation.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Public Access Section -->
        <div
            v-if="activeSubsection === 'publicAccess'"
            class="space-y-6">
            <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                <div class="rounded-xl border border-slate-200/60 bg-slate-50/50 p-5 shadow-sm dark:border-slate-700/60 dark:bg-slate-800/30">
                    <h4 class="mb-3.5 flex items-center gap-1.5 text-[0.65rem] font-semibold uppercase tracking-widest text-slate-500 dark:text-slate-400">
                        <LuGlobe class="h-3.5 w-3.5" />
                        Allow Public Viewing
                    </h4>
                    <ol class="ml-1 list-inside list-decimal space-y-2.5 text-xs font-medium text-slate-600 dark:text-slate-300">
                        <li>
                            Open Google Calendar using the owner of
                            <span class="font-semibold text-slate-800 dark:text-slate-200">OneCBC Sync Calendar</span>
                            .
                        </li>
                        <li>
                            Go to
                            <span class="font-semibold text-slate-800 dark:text-slate-200">Settings and sharing</span>
                            for that calendar.
                        </li>
                        <li>
                            Enable
                            <span class="font-semibold text-slate-800 dark:text-slate-200">Make available to public</span>
                            or share with org.
                        </li>
                        <li>Copy the public calendar link or the calendar ID for subscriber use.</li>
                    </ol>
                </div>

                <div class="rounded-xl border border-slate-200/60 bg-slate-50/50 p-5 shadow-sm dark:border-slate-700/60 dark:bg-slate-800/30">
                    <h4 class="mb-3.5 flex items-center gap-1.5 text-[0.65rem] font-semibold uppercase tracking-widest text-slate-500 dark:text-slate-400">
                        <LuCalendarDays class="h-3.5 w-3.5" />
                        How Users Subscribe
                    </h4>
                    <ul class="ml-1 space-y-2.5 text-xs font-medium text-slate-600 dark:text-slate-300">
                        <li class="flex items-start gap-2.5">
                            <div class="mt-1 h-1.5 w-1.5 shrink-0 rounded-full bg-slate-400"></div>
                            <span>
                                In Google Calendar, click
                                <span class="font-semibold text-slate-800 dark:text-slate-200">Other calendars</span>
                                then
                                <span class="font-semibold text-slate-800 dark:text-slate-200">From URL</span>
                                (ICS link).
                            </span>
                        </li>
                        <li class="flex items-start gap-2.5">
                            <div class="mt-1 h-1.5 w-1.5 shrink-0 rounded-full bg-slate-400"></div>
                            <span>
                                Use
                                <span class="font-semibold text-slate-800 dark:text-slate-200">Add calendar</span>
                                and provide the ID if shared publicly/directly.
                            </span>
                        </li>
                        <li class="flex items-start gap-2.5">
                            <div class="mt-1 h-1.5 w-1.5 shrink-0 rounded-full bg-slate-400"></div>
                            <span>Once added, updates from OneCBC syncs will appear in their personal view.</span>
                        </li>
                    </ul>
                </div>
            </div>

            <div class="flex items-start gap-3 rounded-xl border border-sky-100 bg-sky-50/50 p-5 shadow-sm dark:border-sky-500/20 dark:bg-sky-500/10">
                <LuInfo class="mt-0.5 h-5 w-5 shrink-0 text-sky-500" />
                <p class="text-xs font-medium leading-relaxed text-sky-800 dark:text-sky-300">Public users do not need OneCBC accounts to subscribe, but the Google Calendar itself must be shared appropriately in Google Calendar settings.</p>
            </div>
        </div>

        <!-- Troubleshooting Section (Developers) -->
        <div
            v-if="showDeveloperSections && activeSubsection === 'troubleshooting'"
            class="space-y-6">
            <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                <div class="space-y-4">
                    <h4 class="ml-1 flex items-center gap-1.5 text-[0.65rem] font-semibold uppercase tracking-widest text-slate-500 dark:text-slate-400">
                        <LuServerCrash class="h-3.5 w-3.5" />
                        Common Issues
                    </h4>

                    <div class="rounded-xl border border-rose-100 bg-rose-50/50 p-4 shadow-sm dark:border-rose-500/20 dark:bg-rose-500/5">
                        <h5 class="mb-1.5 text-xs font-semibold text-rose-800 dark:text-rose-300">404 Not Found from Google</h5>
                        <p class="text-[0.65rem] font-medium leading-relaxed text-rose-700/80 dark:text-rose-300/80">The connected Google account cannot access the configured calendar ID. Verify the calendar exists, the ID is correct, and the OAuth account has permission to that calendar.</p>
                    </div>

                    <div class="rounded-xl border border-amber-100 bg-amber-50/50 p-4 shadow-sm dark:border-amber-500/20 dark:bg-amber-500/5">
                        <h5 class="mb-1.5 text-xs font-semibold text-amber-800 dark:text-amber-300">OAuth connected but sync fails</h5>
                        <p class="text-[0.65rem] font-medium leading-relaxed text-amber-700/80 dark:text-amber-300/80">OAuth only proves the token is valid. It does not grant access to every calendar automatically. Share the target calendar with the connected account.</p>
                    </div>

                    <div class="rounded-xl border border-slate-200/60 bg-slate-50/50 p-4 shadow-sm dark:border-slate-700/60 dark:bg-slate-800/30">
                        <h5 class="mb-1.5 text-xs font-semibold text-slate-800 dark:text-slate-200">Credentials or token not found</h5>
                        <p class="text-[0.65rem] font-medium leading-relaxed text-slate-600 dark:text-slate-400">
                            Check that the JSON files are stored under
                            <code class="rounded border border-slate-200 bg-white px-1 py-0.5 font-mono dark:border-slate-700 dark:bg-slate-800">storage/app/google-calendar/</code>
                            and that the environment paths point there correctly.
                        </p>
                    </div>
                </div>

                <div class="h-fit rounded-xl border border-slate-200/60 bg-slate-50/50 p-5 shadow-sm dark:border-slate-700/60 dark:bg-slate-800/30">
                    <h4 class="mb-4 flex items-center gap-1.5 text-[0.65rem] font-semibold uppercase tracking-widest text-slate-500 dark:text-slate-400">
                        <LuCheckSquare class="h-3.5 w-3.5" />
                        Quick Checklist
                    </h4>
                    <ul class="space-y-3 text-xs font-medium text-slate-600 dark:text-slate-300">
                        <li class="flex items-center gap-2.5">
                            <LuCheckCircle2 class="h-4 w-4 shrink-0 text-emerald-500" />
                            <code class="rounded border border-slate-200 bg-white px-1.5 py-0.5 font-mono text-[0.65rem] text-slate-700 shadow-sm dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300">SYNC_ENABLED=true</code>
                        </li>
                        <li class="flex items-center gap-2.5">
                            <LuCheckCircle2 class="h-4 w-4 shrink-0 text-emerald-500" />
                            <code class="rounded border border-slate-200 bg-white px-1.5 py-0.5 font-mono text-[0.65rem] text-slate-700 shadow-sm dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300">AUTH_PROFILE=oauth</code>
                        </li>
                        <li class="flex items-center gap-2.5">
                            <LuCheckCircle2 class="h-4 w-4 shrink-0 text-emerald-500" />
                            <span>Target ID matches real settings.</span>
                        </li>
                        <li class="flex items-center gap-2.5">
                            <LuCheckCircle2 class="h-4 w-4 shrink-0 text-emerald-500" />
                            <span>
                                OAuth user has
                                <span class="font-semibold text-slate-800 dark:text-slate-200">Make changes</span>
                                access.
                            </span>
                        </li>
                        <li class="flex items-center gap-2.5">
                            <LuCheckCircle2 class="h-4 w-4 shrink-0 text-emerald-500" />
                            <span>Callback URL registered in Google Cloud.</span>
                        </li>
                        <li class="flex items-center gap-2.5">
                            <LuCheckCircle2 class="h-4 w-4 shrink-0 text-emerald-500" />
                            <span>Config cache cleared after env changes.</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </TopicLayout>
</template>

<style scoped>
/* VS Code Scrollbar Replication */
.vscode-scrollbar::-webkit-scrollbar {
    height: 10px;
    width: 10px;
}
.vscode-scrollbar::-webkit-scrollbar-track {
    background: transparent;
}
.vscode-scrollbar::-webkit-scrollbar-thumb {
    background: #424242;
    border: 2px solid #1e1e1e;
    border-radius: 6px;
}
.vscode-scrollbar::-webkit-scrollbar-thumb:hover {
    background: #4f4f4f;
}

/* Force strip global backgrounds applied to code tags */
pre code {
    background-color: transparent !important;
    padding: 0 !important;
    border: none !important;
    box-shadow: none !important;
    color: inherit !important;
}
</style>
