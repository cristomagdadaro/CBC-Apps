<script>
import CalendarModule from '@/Components/CalendarModule.vue'
import ApiMixin from '@/Modules/mixins/ApiMixin'

export default {
    name: 'GoogleCalendarModule',
    components: {
        CalendarModule,
    },
    mixins: [ApiMixin],
    props: {
        title: {
            type: String,
            default: 'Sync to your Google Calendar',
        },
        subtitle: {
            type: String,
            default: 'Sync Rentals and Bookings to Google Calendar. Securely sync portal schedules to a managed Google Calendar.',
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
                timezone: 'Asia/Manila',
                calendar_id: null,
                connected_account_email: null,
                configuration_issue: null,
                configuration_message: null,
            },
            loadingGoogleEvents: false,
            syncingVisible: false,
            syncingEventIds: [],
            disconnectingOauth: false,
        }
    },
    computed: {
        googleEventsByPortalKey() {
            return this.googleEvents.reduce((carry, event) => {
                if (event.portal_event_key) {
                    carry[event.portal_event_key] = event
                }

                return carry
            }, {})
        },
        syncedEventCount() {
            return this.events.filter((event) => this.isSynced(event)).length
        },
        nextPortalEvents() {
            return [...this.events]
                .sort((left, right) => {
                    const leftDate = String(left.date_from || left.start_at || '')
                    const rightDate = String(right.date_from || right.start_at || '')

                    return leftDate.localeCompare(rightDate)
                })
                .slice(0, 8)
        },
        syncStatusTone() {
            if (!this.googleMeta.sync_enabled) {
                return 'bg-amber-50 border-amber-200 text-amber-800'
            }

            if (!this.googleMeta.configured) {
                return 'bg-red-50 border-red-200 text-red-800'
            }

            return 'bg-emerald-50 border-emerald-200 text-emerald-800'
        },
        syncStatusLabel() {
            if (!this.googleMeta.sync_enabled) {
                return 'Google Calendar sync is disabled in configuration.'
            }

            if (!this.googleMeta.configured) {
                return this.googleMeta.configuration_message || 'Google Calendar is not configured yet.'
            }

            return 'Google Calendar is connected through a server-side credential flow.'
        },
        canStartOauthConnect() {
            return this.googleMeta.auth_profile === 'oauth' && Boolean(this.googleMeta.oauth_connectable)
        },
        canDisconnectOauth() {
            return this.googleMeta.auth_profile === 'oauth' && Boolean(this.googleMeta.oauth_connected)
        },
    },
    mounted() {
        this.handleOAuthNotice()
        this.loadGoogleEvents()
    },
    methods: {
        handleOAuthNotice() {
            if (typeof window === 'undefined') {
                return
            }

            const currentUrl = new URL(window.location.href)
            const notice = currentUrl.searchParams.get('google_calendar_notice')
            const message = currentUrl.searchParams.get('google_calendar_message')

            if (!notice) {
                return
            }

            const notices = {
                connected: {
                    type: 'success',
                    message: 'Google Calendar OAuth connection completed.',
                },
                oauth_failed: {
                    type: 'error',
                    message: message || 'Google Calendar OAuth connection failed.',
                },
            }

            const detail = notices[notice]

            if (detail) {
                window.dispatchEvent(new CustomEvent('cbc:notify', { detail }))
            }

            currentUrl.searchParams.delete('google_calendar_notice')
            currentUrl.searchParams.delete('google_calendar_message')
            window.history.replaceState({}, document.title, `${currentUrl.pathname}${currentUrl.search}${currentUrl.hash}`)
        },
        normalizePortalEvent(event) {
            const payload = {
                id: event.id,
                label: event.label || event.title || event.purpose || 'Untitled Event',
                subtitle: event.subtitle || event.requested_by || '',
                type: event.type || event.vehicle_type || event.venue_type || 'portal',
                status: event.status || '',
                date_from: event.date_from || event.start_at || event.started_at,
                date_to: event.date_to || event.end_at || event.end_use_at || event.date_from,
                time_from: event.time_from || null,
                time_to: event.time_to || null,
                description: event.description || event.subtitle || '',
                location: event.location || event.destination_location || '',
                portal_url: this.buildPortalUrl(event),
                checkoutPage: event.checkoutPage || null,
                checkoutPageId: event.checkoutPageId || null,
            }

            if (payload.time_from && payload.time_from.length === 8) {
                payload.time_from = payload.time_from.slice(0, 5)
            }

            if (payload.time_to && payload.time_to.length === 8) {
                payload.time_to = payload.time_to.slice(0, 5)
            }

            return payload
        },
        buildPortalUrl(event) {
            if (!event.checkoutPage || !event.checkoutPageId) {
                return null
            }

            try {
                return route(event.checkoutPage, event.checkoutPageId)
            } catch (error) {
                return null
            }
        },
        isSynced(event) {
            return Boolean(this.googleEventsByPortalKey[event.id])
        },
        googleEventFor(event) {
            return this.googleEventsByPortalKey[event.id] || null
        },
        async loadGoogleEvents() {
            this.loadingGoogleEvents = true

            try {
                const payload = await this.fetchGetApi('api.google-calendar.index')
                this.googleEvents = Array.isArray(payload?.data) ? payload.data : []
                this.googleMeta = payload?.meta || this.googleMeta
            } finally {
                this.loadingGoogleEvents = false
            }
        },
        startOauthConnect() {
            if (!this.canStartOauthConnect) {
                return
            }

            window.location.assign(route('rentals.calendar.oauth.redirect'))
        },
        async disconnectOauth() {
            if (!this.canDisconnectOauth || this.disconnectingOauth) {
                return
            }

            this.disconnectingOauth = true

            try {
                const payload = await this.fetchPostApi('api.google-calendar.disconnect')

                await this.loadGoogleEvents()

                window.dispatchEvent(
                    new CustomEvent('cbc:notify', {
                        detail: {
                            type: 'success',
                            message: payload?.message || 'Google Calendar OAuth token removed.',
                        },
                    }),
                )
            } finally {
                this.disconnectingOauth = false
            }
        },
        async syncEvent(event) {
            if (!this.googleMeta.configured) {
                window.dispatchEvent(
                    new CustomEvent('cbc:notify', {
                        detail: {
                            type: 'warning',
                            message: this.googleMeta.configuration_message || 'Google Calendar sync is not configured yet.',
                        },
                    }),
                )
                return
            }

            this.syncingEventIds.push(event.id)

            try {
                const payload = await this.fetchPostApi('api.google-calendar.sync', {
                    event: this.normalizePortalEvent(event),
                })
                const syncedEvent = payload?.data

                if (syncedEvent) {
                    this.googleEvents = [
                        ...this.googleEvents.filter(
                            (existing) => existing.portal_event_key !== syncedEvent.portal_event_key,
                        ),
                        syncedEvent,
                    ]
                }

                window.dispatchEvent(
                    new CustomEvent('cbc:notify', {
                        detail: {
                            type: 'success',
                            message: payload?.message || 'Event synced to Google Calendar.',
                        },
                    }),
                )
            } finally {
                this.syncingEventIds = this.syncingEventIds.filter((id) => id !== event.id)
            }
        },
        async syncVisibleEvents() {
            if (!this.googleMeta.configured || !this.events.length) {
                return
            }

            this.syncingVisible = true

            try {
                const payload = await this.fetchPostApi('api.google-calendar.sync-batch', {
                    events: this.events.map((event) => this.normalizePortalEvent(event)),
                })

                const syncedEvents = Array.isArray(payload?.data) ? payload.data : []

                this.googleEvents = [
                    ...this.googleEvents.filter(
                        (existing) => !syncedEvents.some((synced) => synced.portal_event_key === existing.portal_event_key),
                    ),
                    ...syncedEvents,
                ]

                window.dispatchEvent(
                    new CustomEvent('cbc:notify', {
                        detail: {
                            type: 'success',
                            message: payload?.message || 'Loaded events synced to Google Calendar.',
                        },
                    }),
                )
            } finally {
                this.syncingVisible = false
            }
        },
        openGoogleEvent(event) {
            const synced = this.googleEventFor(event)

            if (!synced?.html_link) {
                return
            }

            window.open(synced.html_link, '_blank', 'noopener')
        },
        openGoogleLink(url) {
            if (!url) {
                return
            }

            window.open(url, '_blank', 'noopener')
        },
    },
}
</script>

<template>
    <div class="space-y-6">
        <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                <div class="space-y-2">
                    <h2 class="text-2xl font-bold text-slate-900">{{ title }}</h2>
                    <p class="max-w-3xl text-sm leading-6 text-slate-600">
                        {{ subtitle }} The browser only talks to internal Laravel endpoints. Calendar credentials stay on the server, and sync is limited to authenticated users.
                    </p>
                </div>

                <div class="flex flex-wrap gap-3">
                    <button
                        v-if="canStartOauthConnect && !googleMeta.configured"
                        type="button"
                        class="rounded-xl bg-blue-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-blue-700"
                        @click="startOauthConnect"
                    >
                        Connect Google Calendar
                    </button>
                    <button
                        v-if="canDisconnectOauth"
                        type="button"
                        class="rounded-xl border border-red-200 px-4 py-2 text-sm font-semibold text-red-700 transition hover:bg-red-50 disabled:cursor-not-allowed disabled:opacity-60"
                        :disabled="disconnectingOauth"
                        @click="disconnectOauth"
                    >
                        {{ disconnectingOauth ? 'Disconnecting...' : 'Disconnect Google Calendar' }}
                    </button>
                    <button
                        type="button"
                        class="rounded-xl border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-50"
                        :disabled="loadingGoogleEvents"
                        @click="loadGoogleEvents"
                    >
                        {{ loadingGoogleEvents ? 'Refreshing...' : 'Refresh Google Events' }}
                    </button>
                    <button
                        type="button"
                        class="rounded-xl bg-emerald-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-emerald-700 disabled:cursor-not-allowed disabled:opacity-60"
                        :disabled="syncingVisible || !events.length || !googleMeta.configured"
                        @click="syncVisibleEvents"
                    >
                        {{ syncingVisible ? 'Syncing...' : 'Sync Loaded Events' }}
                    </button>
                </div>
            </div>

            <div :class="['mt-5 rounded-2xl border px-4 py-3 text-sm', syncStatusTone]">
                <p class="font-semibold">{{ syncStatusLabel }}</p>
                <p class="mt-1 opacity-80">
                    Auth profile: <b>{{ googleMeta.auth_profile || 'service_account' }}</b>
                    <span class="mx-2">•</span>
                    Connected account: <b>{{ googleMeta.connected_account_email || 'Unknown' }}</b>
                    <span class="mx-2">•</span>
                    Target calendar: <b>{{ googleMeta.calendar_id }}</b>
                    <span class="mx-2">•</span>
                    Timezone: <b>{{ googleMeta.timezone || 'Asia/Manila' }}</b>
                    <span class="mx-2">•</span>
                    Synced: <b>{{ syncedEventCount }} / {{ events.length }}</b> portal events
                </p>
                <p v-if="canStartOauthConnect && !googleMeta.configured" class="mt-2 opacity-80">
                    Complete a one-time Google OAuth consent flow to create the server-side token file used for syncing.
                </p>
            </div>
        </section>

        <div class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_360px]">
            <CalendarModule
                :title="'Portal Calendar'"
                :subtitle="'Review bookings and schedules before syncing them to Google Calendar.'"
                :events="events"
                :type-options="typeOptions"
                :status-options="statusOptions"
                :status-colors="statusColors"
                :start-date="startDate"
            />

            <aside class="space-y-4">
                <section class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
                    <div class="flex items-center justify-between">
                        <h3 class="text-sm font-semibold uppercase tracking-[0.18em] text-slate-500">Sync Queue</h3>
                        <span class="rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-700">
                            {{ nextPortalEvents.length }} upcoming
                        </span>
                    </div>

                    <div class="mt-4 space-y-3">
                        <article
                            v-for="event in nextPortalEvents"
                            :key="event.id"
                            class="rounded-2xl border border-slate-200 p-4"
                        >
                            <div class="flex items-start justify-between gap-3">
                                <div class="min-w-0">
                                    <p class="truncate text-sm font-semibold text-slate-900">{{ event.label }}</p>
                                    <p class="mt-1 text-xs leading-5 text-slate-500">
                                        {{ event.date_from }}
                                        <span v-if="event.date_to && event.date_to !== event.date_from">to {{ event.date_to }}</span>
                                    </p>
                                    <p v-if="event.subtitle" class="mt-1 text-xs leading-5 text-slate-500">
                                        {{ event.subtitle }}
                                    </p>
                                </div>
                                <span
                                    :class="[
                                        'rounded-full px-2 py-1 text-[11px] font-semibold uppercase tracking-wide',
                                        isSynced(event)
                                            ? 'bg-emerald-100 text-emerald-700'
                                            : 'bg-slate-100 text-slate-600',
                                    ]"
                                >
                                    {{ isSynced(event) ? 'Synced' : 'Pending' }}
                                </span>
                            </div>

                            <div class="mt-3 flex flex-wrap gap-2">
                                <button
                                    type="button"
                                    class="rounded-lg bg-slate-900 px-3 py-2 text-xs font-semibold text-white transition hover:bg-slate-700 disabled:opacity-60"
                                    :disabled="syncingEventIds.includes(event.id) || !googleMeta.configured"
                                    @click="syncEvent(event)"
                                >
                                    {{ syncingEventIds.includes(event.id) ? 'Syncing...' : (isSynced(event) ? 'Update Sync' : 'Sync to Google') }}
                                </button>
                                <button
                                    v-if="isSynced(event)"
                                    type="button"
                                    class="rounded-lg border border-slate-200 px-3 py-2 text-xs font-semibold text-slate-700 transition hover:bg-slate-50"
                                    @click="openGoogleEvent(event)"
                                >
                                    Open in Google
                                </button>
                            </div>
                        </article>

                        <p v-if="!nextPortalEvents.length" class="text-sm text-slate-500">
                            No portal events are currently available for sync.
                        </p>
                    </div>
                </section>

                <section class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
                    <div class="flex items-center justify-between">
                        <h3 class="text-sm font-semibold uppercase tracking-[0.18em] text-slate-500">Google Events</h3>
                        <span class="rounded-full bg-blue-100 px-2.5 py-1 text-xs font-semibold text-blue-700">
                            {{ googleEvents.length }} loaded
                        </span>
                    </div>

                    <div class="mt-4 space-y-3">
                        <article
                            v-for="event in googleEvents.slice(0, 8)"
                            :key="event.id"
                            class="rounded-2xl border border-slate-200 p-4"
                        >
                            <p class="truncate text-sm font-semibold text-slate-900">{{ event.label }}</p>
                            <p class="mt-1 text-xs leading-5 text-slate-500">
                                {{ event.date_from }}
                                <span v-if="event.date_to && event.date_to !== event.date_from">to {{ event.date_to }}</span>
                            </p>
                            <p v-if="event.portal_event_key" class="mt-1 text-xs text-slate-500">
                                Linked portal event: {{ event.portal_event_key }}
                            </p>
                            <button
                                v-if="event.html_link"
                                type="button"
                                class="mt-3 rounded-lg border border-slate-200 px-3 py-2 text-xs font-semibold text-slate-700 transition hover:bg-slate-50"
                                @click="openGoogleLink(event.html_link)"
                            >
                                Open Event
                            </button>
                        </article>

                        <p v-if="!googleEvents.length && !loadingGoogleEvents" class="text-sm text-slate-500">
                            No Google Calendar events were returned for the active range.
                        </p>
                    </div>
                </section>
            </aside>
        </div>
    </div>
</template>