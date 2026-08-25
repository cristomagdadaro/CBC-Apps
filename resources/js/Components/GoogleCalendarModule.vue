<script>
import CalendarModule from "@/Components/CalendarModule.vue";
import ApiMixin from "@/Modules/mixins/ApiMixin";
import { subscribeToRealtimeChannels } from "@/Modules/realtime/subscriptions";
import { CalendarDays, Link, Unlink, RefreshCcw, CloudUpload, Info, CalendarClock, CalendarSearch, ExternalLink, CheckCircle2, Clock, ShieldCheck, AlertTriangle, XCircle } from "lucide-vue-next";

export default {
    name: "GoogleCalendarModule",
    components: {
        CalendarModule,
        CalendarDays,
        Link,
        Unlink,
        RefreshCcw,
        CloudUpload,
        Info,
        CalendarClock,
        CalendarSearch,
        ExternalLink,
        CheckCircle2,
        Clock,
        ShieldCheck,
        AlertTriangle,
        XCircle,
    },
    mixins: [ApiMixin],
    props: {
        title: {
            type: String,
            default: "Sync to your Google Calendar",
        },
        subtitle: {
            type: String,
            default: "Sync Rentals and Bookings to Google Calendar. Securely sync portal schedules to a managed Google Calendar.",
        },
        events: {
            type: Array,
            default: () => [],
        },
        typeOptions: {
            type: Array,
            default: () => [],
        },
        statusOptions: {
            type: Array,
            default: () => [],
        },
        statusColors: {
            type: Object,
            default: () => ({}),
        },
        startDate: {
            type: [String, Date],
            default: null,
        },
    },
    data() {
        return {
            googleEvents: [],
            googleMeta: {
                configured: false,
                sync_enabled: false,
                auth_profile: null,
                timezone: "Asia/Manila",
                calendar_id: null,
                connected_account_email: null,
                configuration_issue: null,
                configuration_message: null,
            },
            loadingGoogleEvents: false,
            syncingVisible: false,
            syncingEventIds: [],
            disconnectingOauth: false,
            realtimeCleanup: null,
            realtimeRefreshTimer: null,
        };
    },
    computed: {
        googleEventsByPortalKey() {
            return this.googleEvents.reduce((carry, event) => {
                if (event.portal_event_key) {
                    carry[event.portal_event_key] = event;
                }

                return carry;
            }, {});
        },
        syncedEventCount() {
            return this.events.filter((event) => this.isSynced(event)).length;
        },
        nextPortalEvents() {
            return [...this.events]
                .sort((left, right) => {
                    const leftDate = String(left.date_from || left.start_at || "");
                    const rightDate = String(right.date_from || right.start_at || "");

                    return leftDate.localeCompare(rightDate);
                })
                .slice(0, 8);
        },
        syncStatusConfig() {
            if (!this.googleMeta.sync_enabled) {
                return {
                    tone: "bg-amber-50 border-amber-200 dark:bg-amber-500/10 dark:border-amber-500/30 text-amber-800 dark:text-amber-300",
                    iconTone: "text-amber-500",
                    icon: AlertTriangle,
                    title: "Sync Disabled",
                    label: "Google Calendar sync is disabled in configuration.",
                };
            }

            if (!this.googleMeta.configured) {
                return {
                    tone: "bg-rose-50 border-rose-200 dark:bg-rose-500/10 dark:border-rose-500/30 text-rose-800 dark:text-rose-300",
                    iconTone: "text-rose-500",
                    icon: XCircle,
                    title: "Not Configured",
                    label: this.googleMeta.configuration_message || "Google Calendar is not configured yet.",
                };
            }

            return {
                tone: "bg-emerald-50 border-emerald-200 dark:bg-emerald-500/10 dark:border-emerald-500/30 text-emerald-800 dark:text-emerald-300",
                iconTone: "text-emerald-500",
                icon: ShieldCheck,
                title: "Connected & Active",
                label: "Google Calendar is connected through a server-side credential flow.",
            };
        },
        canStartOauthConnect() {
            return this.googleMeta.auth_profile === "oauth" && Boolean(this.googleMeta.oauth_connectable);
        },
        canDisconnectOauth() {
            return this.googleMeta.auth_profile === "oauth" && Boolean(this.googleMeta.oauth_connected);
        },
    },
    mounted() {
        this.handleOAuthNotice();
        this.loadGoogleEvents();
        this.configureRealtime();
    },
    beforeUnmount() {
        if (this.realtimeRefreshTimer) {
            clearTimeout(this.realtimeRefreshTimer);
        }

        this.cleanupRealtime();
    },
    methods: {
        handleOAuthNotice() {
            if (typeof window === "undefined") {
                return;
            }

            const currentUrl = new URL(window.location.href);
            const notice = currentUrl.searchParams.get("google_calendar_notice");
            const message = currentUrl.searchParams.get("google_calendar_message");

            if (!notice) {
                return;
            }

            const notices = {
                connected: {
                    type: "success",
                    message: "Google Calendar OAuth connection completed.",
                },
                oauth_failed: {
                    type: "error",
                    message: message || "Google Calendar OAuth connection failed.",
                },
            };

            const detail = notices[notice];

            if (detail) {
                window.dispatchEvent(new CustomEvent("cbc:notify", { detail }));
            }

            currentUrl.searchParams.delete("google_calendar_notice");
            currentUrl.searchParams.delete("google_calendar_message");
            window.history.replaceState({}, document.title, `${currentUrl.pathname}${currentUrl.search}${currentUrl.hash}`);
        },
        normalizePortalEvent(event) {
            const payload = {
                id: event.id,
                label: event.label || event.title || event.purpose || "Untitled Event",
                subtitle: event.subtitle || event.requested_by || "",
                type: event.type || event.vehicle_type || event.venue_type || "portal",
                status: event.status || "",
                date_from: event.date_from || event.start_at || event.started_at,
                date_to: event.date_to || event.end_at || event.end_use_at || event.date_from,
                time_from: event.time_from || null,
                time_to: event.time_to || null,
                description: event.description || event.subtitle || "",
                location: event.location || event.destination_location || "",
                portal_url: this.buildPortalUrl(event),
                checkoutPage: event.checkoutPage || null,
                checkoutPageId: event.checkoutPageId || null,
            };

            if (payload.time_from && payload.time_from.length === 8) {
                payload.time_from = payload.time_from.slice(0, 5);
            }

            if (payload.time_to && payload.time_to.length === 8) {
                payload.time_to = payload.time_to.slice(0, 5);
            }

            return payload;
        },
        buildPortalUrl(event) {
            if (!event.checkoutPage || !event.checkoutPageId) {
                return null;
            }

            try {
                return route(event.checkoutPage, event.checkoutPageId);
            } catch (error) {
                return null;
            }
        },
        isSynced(event) {
            return Boolean(this.googleEventsByPortalKey[event.id]);
        },
        googleEventFor(event) {
            return this.googleEventsByPortalKey[event.id] || null;
        },
        async loadGoogleEvents() {
            this.loadingGoogleEvents = true;

            try {
                const payload = await this.fetchGetApi("api.google-calendar.index");
                this.googleEvents = Array.isArray(payload?.data) ? payload.data : [];
                this.googleMeta = payload?.meta || this.googleMeta;
            } finally {
                this.loadingGoogleEvents = false;
            }
        },
        startOauthConnect() {
            if (!this.canStartOauthConnect) {
                return;
            }

            window.location.assign(route("rentals.calendar.oauth.redirect"));
        },
        async disconnectOauth() {
            if (!this.canDisconnectOauth || this.disconnectingOauth) {
                return;
            }

            this.disconnectingOauth = true;

            try {
                const payload = await this.fetchPostApi("api.google-calendar.disconnect");

                await this.loadGoogleEvents();

                window.dispatchEvent(
                    new CustomEvent("cbc:notify", {
                        detail: {
                            type: "success",
                            message: payload?.message || "Google Calendar OAuth token removed.",
                        },
                    }),
                );
            } finally {
                this.disconnectingOauth = false;
            }
        },
        async syncEvent(event) {
            if (!this.googleMeta.configured) {
                window.dispatchEvent(
                    new CustomEvent("cbc:notify", {
                        detail: {
                            type: "warning",
                            message: this.googleMeta.configuration_message || "Google Calendar sync is not configured yet.",
                        },
                    }),
                );
                return;
            }

            this.syncingEventIds.push(event.id);

            try {
                const payload = await this.fetchPostApi("api.google-calendar.sync", {
                    event: this.normalizePortalEvent(event),
                });
                const syncedEvent = payload?.data;

                if (syncedEvent) {
                    this.googleEvents = [...this.googleEvents.filter((existing) => existing.portal_event_key !== syncedEvent.portal_event_key), syncedEvent];
                }

                window.dispatchEvent(
                    new CustomEvent("cbc:notify", {
                        detail: {
                            type: "success",
                            message: payload?.message || "Event synced to Google Calendar.",
                        },
                    }),
                );
            } finally {
                this.syncingEventIds = this.syncingEventIds.filter((id) => id !== event.id);
            }
        },
        async syncVisibleEvents() {
            if (!this.googleMeta.configured || !this.events.length) {
                return;
            }

            this.syncingVisible = true;

            try {
                const payload = await this.fetchPostApi("api.google-calendar.sync-batch", {
                    events: this.events.map((event) => this.normalizePortalEvent(event)),
                });

                const syncedEvents = Array.isArray(payload?.data) ? payload.data : [];

                this.googleEvents = [...this.googleEvents.filter((existing) => !syncedEvents.some((synced) => synced.portal_event_key === existing.portal_event_key)), ...syncedEvents];

                window.dispatchEvent(
                    new CustomEvent("cbc:notify", {
                        detail: {
                            type: "success",
                            message: payload?.message || "Loaded events synced to Google Calendar.",
                        },
                    }),
                );
            } finally {
                this.syncingVisible = false;
            }
        },
        openGoogleEvent(event) {
            const synced = this.googleEventFor(event);

            if (!synced?.html_link) {
                return;
            }

            window.open(synced.html_link, "_blank", "noopener");
        },
        openGoogleLink(url) {
            if (!url) {
                return;
            }

            window.open(url, "_blank", "noopener");
        },
        cleanupRealtime() {
            if (typeof this.realtimeCleanup === "function") {
                this.realtimeCleanup();
            }

            this.realtimeCleanup = null;
        },
        scheduleRealtimeRefresh() {
            if (this.realtimeRefreshTimer) {
                clearTimeout(this.realtimeRefreshTimer);
            }

            this.realtimeRefreshTimer = setTimeout(() => {
                this.loadGoogleEvents();
            }, 400);
        },
        configureRealtime() {
            this.cleanupRealtime();

            this.realtimeCleanup = subscribeToRealtimeChannels([
                {
                    type: "private",
                    channel: "rentals.calendar",
                    event: "rentals.calendar.sync-status",
                    feature: "calendar_sync",
                    handler: (payload) => {
                        this.scheduleRealtimeRefresh();

                        if (typeof window === "undefined" || !payload?.message || this.syncingVisible || this.syncingEventIds.length) {
                            return;
                        }

                        window.dispatchEvent(
                            new CustomEvent("cbc:notify", {
                                detail: {
                                    type: payload.status === "failed" ? "error" : "success",
                                    message: payload.message,
                                },
                            }),
                        );
                    },
                },
            ]);
        },
    },
};
</script>

<template>
    <div class="space-y-6">
        <!-- Main Header & Controls -->
        <section class="rounded-2xl border border-slate-200/60 bg-white/80 p-6 shadow-sm backdrop-blur-xl md:p-8 dark:border-slate-800 dark:bg-slate-900/80">
            <div class="flex flex-col gap-6 lg:flex-row lg:items-start lg:justify-between">
                <!-- Title Area -->
                <div class="flex-1 space-y-3">
                    <div class="mb-1 flex items-center gap-3.5">
                        <div class="shrink-0 rounded-xl border border-indigo-100 bg-indigo-50 p-2.5 shadow-sm dark:border-indigo-500/20 dark:bg-indigo-500/10">
                            <CalendarDays class="h-6 w-6 text-indigo-600 dark:text-indigo-400" />
                        </div>
                        <h2 class="text-2xl font-black tracking-tight text-slate-900 dark:text-white">
                            {{ title }}
                        </h2>
                    </div>
                    <p class="text-sm font-medium leading-relaxed text-slate-500 dark:text-slate-400">{{ subtitle }} The browser only talks to internal Laravel endpoints. Calendar credentials stay on the server, and sync is limited to authenticated users.</p>
                </div>

                <!-- Action Buttons -->
                <div class="flex shrink-0 flex-wrap items-center gap-3 pt-2 lg:pt-0">
                    <button
                        v-if="canStartOauthConnect && !googleMeta.configured"
                        type="button"
                        class="inline-flex items-center gap-2 rounded-xl bg-indigo-600 px-5 py-2.5 text-sm font-bold text-white shadow-sm transition-all hover:bg-indigo-700 active:scale-95"
                        @click="startOauthConnect">
                        <Link class="h-4 w-4" />
                        Connect Google Calendar
                    </button>

                    <button
                        v-if="canDisconnectOauth"
                        type="button"
                        class="inline-flex items-center gap-2 rounded-xl border border-rose-200 bg-rose-50 px-5 py-2.5 text-sm font-bold text-rose-700 shadow-sm transition-all hover:bg-rose-100 active:scale-95 disabled:pointer-events-none disabled:opacity-60 dark:border-rose-500/30 dark:bg-rose-500/10 dark:text-rose-400 dark:hover:bg-rose-500/20"
                        :disabled="disconnectingOauth"
                        @click="disconnectOauth">
                        <Unlink class="h-4 w-4" />
                        {{ disconnectingOauth ? "Disconnecting..." : "Disconnect Calendar" }}
                    </button>

                    <button
                        type="button"
                        class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-5 py-2.5 text-sm font-bold text-slate-700 shadow-sm transition-all hover:bg-slate-50 active:scale-95 disabled:pointer-events-none disabled:opacity-60 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300 dark:hover:bg-slate-700/50"
                        :disabled="loadingGoogleEvents"
                        @click="loadGoogleEvents">
                        <RefreshCcw
                            class="h-4 w-4"
                            :class="{ 'animate-spin': loadingGoogleEvents }" />
                        {{ loadingGoogleEvents ? "Refreshing..." : "Refresh Events" }}
                    </button>

                    <button
                        type="button"
                        class="inline-flex items-center gap-2 rounded-xl bg-emerald-600 px-5 py-2.5 text-sm font-bold text-white shadow-sm transition-all hover:bg-emerald-700 active:scale-95 disabled:pointer-events-none disabled:opacity-60"
                        :disabled="syncingVisible || !events.length || !googleMeta.configured"
                        @click="syncVisibleEvents">
                        <CloudUpload
                            class="h-4 w-4"
                            :class="{ 'animate-bounce': syncingVisible }" />
                        {{ syncingVisible ? "Syncing..." : "Sync Loaded Events" }}
                    </button>
                </div>
            </div>

            <!-- Status Banner -->
            <div
                class="mt-8 rounded-xl border p-5 transition-colors duration-300"
                :class="syncStatusConfig.tone">
                <div class="flex flex-col justify-between gap-4 md:flex-row md:items-start">
                    <div class="flex w-full shrink-0 flex-wrap justify-between gap-5 whitespace-nowrap text-xs font-medium opacity-90">
                        <div class="flex w-fit items-start gap-3 whitespace-nowrap">
                            <component
                                :is="syncStatusConfig.icon"
                                class="mt-0.5 h-5 w-5 shrink-0"
                                :class="syncStatusConfig.iconTone" />
                            <div>
                                <p class="mb-1 text-sm font-bold tracking-tight">
                                    {{ syncStatusConfig.title }}
                                </p>
                                <p class="text-xs font-medium leading-relaxed opacity-90">
                                    {{ syncStatusConfig.label }}
                                </p>

                                <p
                                    v-if="canStartOauthConnect && !googleMeta.configured"
                                    class="mt-2 flex items-center gap-1.5 text-[0.65rem] font-bold uppercase tracking-widest opacity-80">
                                    <Info class="h-3.5 w-3.5" />
                                    Complete OAuth consent flow to create the server-side token.
                                </p>
                            </div>
                        </div>
                        <div class="flex flex-col">
                            <span class="mb-0.5 text-[0.6rem] font-bold uppercase tracking-widest opacity-70">Auth Profile</span>
                            <span class="max-w-[150px] truncate">
                                {{ googleMeta.auth_profile || "service_account" }}
                            </span>
                        </div>
                        <div class="flex flex-col">
                            <span class="mb-0.5 text-[0.6rem] font-bold uppercase tracking-widest opacity-70">Timezone</span>
                            <span class="max-w-[150px] truncate">
                                {{ googleMeta.timezone || "Asia/Manila" }}
                            </span>
                        </div>
                        <div class="col-span-2 flex flex-col md:col-span-1 lg:col-span-2">
                            <span class="mb-0.5 text-[0.6rem] font-bold uppercase tracking-widest opacity-70">Connected Account</span>
                            <span class="max-w-[300px] truncate">
                                {{ googleMeta.connected_account_email || "Unknown" }}
                            </span>
                        </div>
                        <div class="col-span-2 flex flex-col md:col-span-1 lg:col-span-2">
                            <span class="mb-0.5 text-[0.6rem] font-bold uppercase tracking-widest opacity-70">Target Calendar</span>
                            <span class="max-w-[300px] truncate">{{ googleMeta.calendar_id }}</span>
                        </div>
                        <div class="col-span-2 mt-1 flex flex-col md:col-span-1 lg:col-span-2">
                            <span class="mb-0.5 text-[0.6rem] font-bold uppercase tracking-widest opacity-70">Sync Status</span>
                            <span class="font-bold">
                                {{ syncedEventCount }} / {{ events.length }}
                                <span class="font-medium opacity-80">portal events synced</span>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Main Content Grid -->
        <div class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_380px]">
            <!-- Left Column: Portal Calendar -->
            <CalendarModule
                :title="'Portal Calendar'"
                :subtitle="'Review bookings and schedules before syncing them to Google Calendar.'"
                :events="events"
                :type-options="typeOptions"
                :status-options="statusOptions"
                :status-colors="statusColors"
                :start-date="startDate"
                class="h-fit rounded-2xl border border-slate-200/60 bg-white/80 shadow-sm backdrop-blur-xl dark:border-slate-800 dark:bg-slate-900/80" />

            <!-- Right Column: Queues -->
            <aside class="space-y-6">
                <!-- Sync Queue -->
                <section class="rounded-2xl border border-slate-200/60 bg-white/80 p-5 shadow-sm backdrop-blur-xl dark:border-slate-800 dark:bg-slate-900/80">
                    <div class="mb-5 flex items-center justify-between border-b border-slate-100 pb-3 dark:border-slate-800/60">
                        <div class="flex items-center gap-2">
                            <CalendarClock class="h-4 w-4 text-slate-400 dark:text-slate-500" />
                            <h3 class="text-[0.65rem] font-semibold uppercase tracking-widest text-slate-500 dark:text-slate-400">Sync Queue</h3>
                        </div>
                        <span class="rounded-md border border-indigo-100 bg-indigo-50 px-2 py-0.5 text-[0.6rem] font-bold uppercase tracking-widest text-indigo-600 shadow-sm dark:border-indigo-500/20 dark:bg-indigo-500/10 dark:text-indigo-400">{{ nextPortalEvents.length }} upcoming</span>
                    </div>

                    <div class="space-y-3">
                        <article
                            v-for="event in nextPortalEvents"
                            :key="event.id"
                            class="rounded-xl border border-slate-200/60 bg-slate-50/50 p-4 transition-colors hover:border-slate-300 dark:border-slate-700/60 dark:bg-slate-800/30 dark:hover:border-slate-600">
                            <div class="mb-3 flex items-start justify-between gap-3">
                                <div class="min-w-0 flex-1">
                                    <p class="truncate text-sm font-bold text-slate-900 dark:text-white">
                                        {{ event.label }}
                                    </p>

                                    <div class="mt-1.5 flex items-center gap-1.5">
                                        <Clock class="h-3 w-3 shrink-0 text-slate-400" />
                                        <p class="text-[0.65rem] font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                                            {{ event.date_from }}
                                            <span
                                                v-if="event.date_to && event.date_to !== event.date_from"
                                                class="mx-0.5 opacity-70">
                                                to
                                            </span>
                                            <span v-if="event.date_to && event.date_to !== event.date_from">
                                                {{ event.date_to }}
                                            </span>
                                        </p>
                                    </div>

                                    <p
                                        v-if="event.subtitle"
                                        class="mt-1.5 truncate text-xs font-medium text-slate-500 dark:text-slate-400">
                                        {{ event.subtitle }}
                                    </p>
                                </div>
                                <span :class="['shrink-0 rounded-md border px-2 py-0.5 text-[0.6rem] font-bold uppercase tracking-widest shadow-sm', isSynced(event) ? 'border-emerald-200 bg-emerald-50 text-emerald-600 dark:border-emerald-500/30 dark:bg-emerald-500/10 dark:text-emerald-400' : 'border-slate-200 bg-white text-slate-500 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-400']">
                                    {{ isSynced(event) ? "Synced" : "Pending" }}
                                </span>
                            </div>

                            <div class="flex flex-wrap gap-2 border-t border-slate-200/60 pt-3 dark:border-slate-700/60">
                                <button
                                    type="button"
                                    class="inline-flex flex-1 items-center justify-center gap-1.5 rounded-lg bg-slate-900 px-3 py-1.5 text-[0.65rem] font-bold uppercase tracking-widest text-white transition hover:bg-slate-800 disabled:pointer-events-none disabled:opacity-60 dark:bg-slate-700 dark:hover:bg-slate-600"
                                    :disabled="syncingEventIds.includes(event.id) || !googleMeta.configured"
                                    @click="syncEvent(event)">
                                    <CloudUpload
                                        class="h-3 w-3"
                                        :class="{
                                            'animate-bounce': syncingEventIds.includes(event.id),
                                        }" />
                                    {{ syncingEventIds.includes(event.id) ? "Syncing..." : isSynced(event) ? "Update Sync" : "Sync to Google" }}
                                </button>

                                <button
                                    v-if="isSynced(event)"
                                    type="button"
                                    class="inline-flex items-center justify-center gap-1.5 rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-[0.65rem] font-bold uppercase tracking-widest text-slate-700 transition hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300 dark:hover:bg-slate-700/50"
                                    @click="openGoogleEvent(event)">
                                    <ExternalLink class="h-3 w-3" />
                                    Open
                                </button>
                            </div>
                        </article>

                        <div
                            v-if="!nextPortalEvents.length"
                            class="flex flex-col items-center justify-center rounded-xl border border-dashed border-slate-200 bg-slate-50/50 py-8 text-center dark:border-slate-700 dark:bg-slate-800/30">
                            <CalendarClock class="mb-2 h-8 w-8 text-slate-300 dark:text-slate-600" />
                            <p class="text-xs font-semibold text-slate-500 dark:text-slate-400">No portal events available.</p>
                        </div>
                    </div>
                </section>

                <!-- Google Events -->
                <section class="rounded-2xl border border-slate-200/60 bg-white/80 p-5 shadow-sm backdrop-blur-xl dark:border-slate-800 dark:bg-slate-900/80">
                    <div class="mb-5 flex items-center justify-between border-b border-slate-100 pb-3 dark:border-slate-800/60">
                        <div class="flex items-center gap-2">
                            <CalendarSearch class="h-4 w-4 text-slate-400 dark:text-slate-500" />
                            <h3 class="text-[0.65rem] font-semibold uppercase tracking-widest text-slate-500 dark:text-slate-400">Google Events</h3>
                        </div>
                        <span class="rounded-md border border-sky-100 bg-sky-50 px-2 py-0.5 text-[0.6rem] font-bold uppercase tracking-widest text-sky-600 shadow-sm dark:border-sky-500/20 dark:bg-sky-500/10 dark:text-sky-400">{{ googleEvents.length }} loaded</span>
                    </div>

                    <div class="space-y-3">
                        <article
                            v-for="event in googleEvents.slice(0, 8)"
                            :key="event.id"
                            class="rounded-xl border border-slate-200/60 bg-white p-4 shadow-sm dark:border-slate-700/60 dark:bg-slate-800/50">
                            <p class="truncate text-sm font-bold text-slate-900 dark:text-white">
                                {{ event.label }}
                            </p>

                            <div class="mt-2 flex items-center gap-1.5">
                                <Clock class="h-3 w-3 shrink-0 text-slate-400" />
                                <p class="text-[0.65rem] font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                                    {{ event.date_from }}
                                    <span
                                        v-if="event.date_to && event.date_to !== event.date_from"
                                        class="mx-0.5 opacity-70">
                                        to
                                    </span>
                                    <span v-if="event.date_to && event.date_to !== event.date_from">
                                        {{ event.date_to }}
                                    </span>
                                </p>
                            </div>

                            <p
                                v-if="event.portal_event_key"
                                class="mt-2 truncate rounded border border-slate-100 bg-slate-50 px-2 py-1 font-mono text-[0.6rem] text-slate-400 dark:border-slate-700/50 dark:bg-slate-900 dark:text-slate-500">
                                <span class="mr-1 font-semibold text-slate-500 dark:text-slate-400">KEY:</span>
                                {{ event.portal_event_key }}
                            </p>

                            <button
                                v-if="event.html_link"
                                type="button"
                                class="mt-3 inline-flex w-full items-center justify-center gap-1.5 rounded-lg border border-slate-200 bg-slate-50 px-3 py-1.5 text-[0.65rem] font-bold uppercase tracking-widest text-slate-700 transition hover:bg-white active:scale-95 dark:border-slate-700 dark:bg-slate-800/80 dark:text-slate-300 dark:hover:bg-slate-700"
                                @click="openGoogleLink(event.html_link)">
                                <ExternalLink class="h-3 w-3" />
                                View in Google Calendar
                            </button>
                        </article>

                        <div
                            v-if="!googleEvents.length && !loadingGoogleEvents"
                            class="flex flex-col items-center justify-center rounded-xl border border-dashed border-slate-200 bg-slate-50/50 py-8 text-center dark:border-slate-700 dark:bg-slate-800/30">
                            <CalendarSearch class="mb-2 h-8 w-8 text-slate-300 dark:text-slate-600" />
                            <p class="text-xs font-semibold text-slate-500 dark:text-slate-400">No events found in active range.</p>
                        </div>
                    </div>
                </section>
            </aside>
        </div>
    </div>
</template>
