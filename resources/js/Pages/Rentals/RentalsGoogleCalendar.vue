<script>
import GoogleCalendarModule from '@/Components/GoogleCalendarModule.vue'
import { subscribeToRealtimeChannels } from '@/Modules/realtime/subscriptions'
import RentalsHeaderAction from '@/Pages/Rentals/components/RentalsHeaderAction.vue'

export default {
    name: 'RentalsGoogleCalendar',
    components: {
        GoogleCalendarModule,
        RentalsHeaderAction,
    },
    data() {
        return {
            loading: true,
            error: '',
            vehicleRentals: [],
            venueRentals: [],
            realtimeCleanup: null,
            realtimeRefreshTimer: null,
            statusColors: {
                pending: '#F59E0B',
                approved: '#10B981',
                in_progress: '#3B82F6',
                rejected: '#EF4444',
                cancelled: '#6B7280',
                completed: '#334155',
            },
            statusOptions: [
                { key: 'pending', label: 'Pending' },
                { key: 'approved', label: 'Approved' },
                { key: 'in_progress', label: 'In Progress' },
                { key: 'rejected', label: 'Rejected' },
                { key: 'cancelled', label: 'Cancelled' },
                { key: 'completed', label: 'Completed' },
            ],
            typeOptions: [
                { key: 'vehicle', label: 'Vehicles', color: '#2563EB' },
                { key: 'venue', label: 'Venues', color: '#059669' },
            ],
        }
    },
    computed: {
        allEvents() {
            const vehicles = this.vehicleRentals.map((rental) => ({
                id: `vehicle-${rental.id}`,
                type: 'vehicle',
                status: rental.status,
                date_from: rental.date_from,
                date_to: rental.date_to,
                label: `${rental.requested_by || 'Unknown requester'} (${rental.vehicle_type || 'Vehicle pending'})`,
                subtitle: [rental.destination_location, rental.purpose].filter(Boolean).join(' - '),
                location: rental.destination_location || '',
                checkoutPage: 'rental.vehicle.show',
                checkoutPageId: rental.id,
                checkoutPageTarget: '_blank',
            }))

            const venues = this.venueRentals.map((rental) => ({
                id: `venue-${rental.id}`,
                type: 'venue',
                status: rental.status,
                date_from: rental.date_from,
                date_to: rental.date_to,
                label: rental.event_name || 'Untitled Event',
                subtitle: rental.requested_by || '',
                location: rental.destination_location || '',
                checkoutPage: 'rental.venue.show',
                checkoutPageId: rental.id,
                checkoutPageTarget: '_blank',
            }))

            return [...vehicles, ...venues]
        },
    },
    mounted() {
        this.loadBookings()
        this.configureRealtime()
    },
    beforeUnmount() {
        if (this.realtimeRefreshTimer) {
            clearTimeout(this.realtimeRefreshTimer)
        }

        this.cleanupRealtime()
    },
    methods: {
        cleanupRealtime() {
            if (typeof this.realtimeCleanup === 'function') {
                this.realtimeCleanup()
            }

            this.realtimeCleanup = null
        },
        configureRealtime() {
            this.cleanupRealtime()

            this.realtimeCleanup = subscribeToRealtimeChannels([
                {
                    type: 'private',
                    channel: 'rentals.calendar',
                    event: 'rentals.calendar.changed',
                    handler: () => this.scheduleRealtimeRefresh(),
                },
            ])
        },
        scheduleRealtimeRefresh() {
            if (this.realtimeRefreshTimer) {
                clearTimeout(this.realtimeRefreshTimer)
            }

            this.realtimeRefreshTimer = setTimeout(() => {
                this.loadBookings()
            }, 400)
        },
        async loadBookings() {
            this.loading = true
            this.error = ''

            try {
                const [vehicleRes, venueRes] = await Promise.all([
                    fetch('/api/guest/rental/vehicles?statuses=pending,approved,in_progress,rejected,cancelled,completed', {
                        credentials: 'same-origin',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                    }),
                    fetch('/api/guest/rental/venues?statuses=pending,approved,in_progress,rejected,cancelled,completed', {
                        credentials: 'same-origin',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                    }),
                ])

                if (!vehicleRes.ok || !venueRes.ok) {
                    throw new Error('Unable to load rental schedules for Google Calendar sync.')
                }

                const vehicleData = await vehicleRes.json()
                const venueData = await venueRes.json()

                this.vehicleRentals = Array.isArray(vehicleData?.data) ? vehicleData.data : []
                this.venueRentals = Array.isArray(venueData?.data) ? venueData.data : []
            } catch (error) {
                this.error = error?.message || 'Failed to load rental schedules.'
            } finally {
                this.loading = false
            }
        },
    },
}
</script>

<template>
    <Head title="Google Calendar Sync" />

    <AppLayout>
        <template #header>
            <RentalsHeaderAction />
        </template>

        <div class="default-container py-5 space-y-4">
            <div v-if="loading" class="rounded-3xl border border-slate-200 bg-white p-8 text-sm text-slate-500">
                Loading rental schedules...
            </div>

            <div v-else-if="error" class="rounded-3xl border border-red-200 bg-red-50 p-8 text-sm text-red-700">
                {{ error }}
            </div>

            <GoogleCalendarModule
                v-else
                :events="allEvents"
                :type-options="typeOptions"
                :status-options="statusOptions"
                :status-colors="statusColors"
            />
        </div>
    </AppLayout>
</template>
