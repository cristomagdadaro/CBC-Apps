<script>
import {
    LuAlertCircle,
    LuCalendar,
    LuCar,
    LuCheckCircle2,
    LuClock,
    LuClock3,
    LuFileText,
    LuMapPin,
    LuPhone,
    LuUser,
    LuXCircle,
} from '@/Components/Icons'
import ApiMixin from '@/Modules/mixins/ApiMixin'
import RentalTripRouteVisualizer from '@/Pages/Rentals/components/RentalTripRouteVisualizer.vue'
import { getTripTypeMeta } from '@/Pages/Rentals/constants/tripWorkflows'

export default {
    name: 'RentalVehicleShow',
    components: {
        LuAlertCircle,
        LuCalendar,
        LuCar,
        LuCheckCircle2,
        LuClock,
        LuClock3,
        LuFileText,
        LuMapPin,
        LuPhone,
        LuUser,
        LuXCircle,
        RentalTripRouteVisualizer,
    },
    mixins: [ApiMixin],
    props: {
        rental_id: {
            type: String,
            required: true,
        },
    },
    data() {
        return {
            loading: true,
            error: '',
            rental: null,
        }
    },
    computed: {
        canViewContactNumber() {
            return Boolean(this.$page.props.auth?.user && this.rental?.contact_number)
        },
        tripTypeMeta() {
            return getTripTypeMeta(this.rental?.trip_type)
        },
        destinationStops() {
            const stops = this.rental?.destination_stops
            return Array.isArray(stops) ? stops : []
        },
        statusConfig() {
            const configs = {
                pending: {
                    color: 'bg-amber-100 text-amber-800 border-amber-200',
                    icon: LuClock3,
                    label: 'Pending Approval',
                },
                approved: {
                    color: 'bg-emerald-100 text-emerald-800 border-emerald-200',
                    icon: LuCheckCircle2,
                    label: 'Approved',
                },
                in_progress: {
                    color: 'bg-blue-100 text-blue-800 border-blue-200',
                    icon: LuClock,
                    label: 'In Progress',
                },
                rejected: {
                    color: 'bg-red-100 text-red-800 border-red-200',
                    icon: LuXCircle,
                    label: 'Rejected',
                },
                cancelled: {
                    color: 'bg-gray-100 text-gray-800 border-gray-200',
                    icon: LuXCircle,
                    label: 'Cancelled',
                },
                completed: {
                    color: 'bg-slate-100 text-slate-800 border-slate-200',
                    icon: LuCheckCircle2,
                    label: 'Completed',
                },
            }

            return configs[this.rental?.status?.toLowerCase()] || {
                color: 'bg-gray-100 text-gray-800 border-gray-200',
                icon: LuAlertCircle,
                label: this.rental?.status || 'Unknown',
            }
        },
    },
    mounted() {
        this.loadRental()
    },
    methods: {
        formatDateTime(date, time) {
            if (!date) return 'Not specified'

            const dateObj = new Date(date)
            const formatted = dateObj.toLocaleDateString('en-US', {
                weekday: 'short',
                year: 'numeric',
                month: 'short',
                day: 'numeric',
            })

            return time ? `${formatted} at ${time}` : formatted
        },
        async loadRental() {
            this.loading = true
            this.error = ''

            try {
                const payload = await this.fetchGetApi('api.guest.rental.vehicles.show', {
                    routeParams: this.rental_id,
                })
                this.rental = payload?.data ?? null
            } catch (err) {
                this.error = err?.message || 'Failed to load vehicle rental details.'
            } finally {
                this.loading = false
            }
        },
    },
}
</script>

<template>
    <Head title="Vehicle Rental Details" />

    <GuestFormPage
        title="Vehicle Rental Details"
        subtitle="View booking details and current status."
        :delay-ready="true"
    >
        <!-- Loading State -->
        <div v-if="loading" class="rounded-2xl border border-gray-200 bg-white p-8">
            <div class="flex flex-col items-center justify-center space-y-4 py-12">
                <div class="relative">
                    <div class="h-12 w-12 rounded-full border-4 border-gray-200"></div>
                    <div class="absolute inset-0 h-12 w-12 animate-spin rounded-full border-4 border-blue-600 border-t-transparent"></div>
                </div>
                <p class="text-sm font-medium text-gray-600">Loading rental details...</p>
            </div>
        </div>

        <!-- Error State -->
        <div v-else-if="error" class="rounded-2xl border border-red-200 bg-red-50 p-8">
            <div class="flex flex-col items-center justify-center space-y-3 py-8 text-center">
                <div class="rounded-full bg-red-100 p-3">
                    <LuAlertCircle class="h-6 w-6 text-red-600" />
                </div>
                <h3 class="text-lg font-semibold text-red-900">Failed to Load</h3>
                <p class="max-w-sm text-sm text-red-700">{{ error }}</p>
                <button 
                    @click="loadRental" 
                    class="mt-2 rounded-lg bg-red-600 px-4 py-2 text-sm font-medium text-white transition-colors hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2"
                >
                    Try Again
                </button>
            </div>
        </div>

        <!-- Empty State -->
        <div v-else-if="!rental" class="rounded-2xl border border-gray-200 bg-white p-8">
            <div class="flex flex-col items-center justify-center space-y-3 py-12 text-center">
                <div class="rounded-full bg-gray-100 p-4">
                    <LuCar class="h-8 w-8 text-gray-400" />
                </div>
                <h3 class="text-lg font-semibold text-gray-900">Rental Not Found</h3>
                <p class="max-w-sm text-sm text-gray-500">The requested vehicle rental details could not be found or may have been removed.</p>
            </div>
        </div>

        <!-- Content State -->
        <div v-else class="space-y-6 w-fit mx-auto">
            <!-- Status Banner -->
            <div :class="['rounded-2xl border-2 p-6', statusConfig.color]">
                <div class="flex items-center justify-between gap-5">
                    <!-- Vehicle Header -->
                    <div class="flex items-center space-x-4">
                        <div class="rounded-xl bg-blue-600 p-3 text-white shadow-lg shadow-blue-600/20">
                            <LuCar class="h-6 w-6" />
                        </div>
                        <div class="leading-tight">
                            <h2 class="text-lg font-bold text-gray-900">{{ rental.vehicle_type || 'Vehicle Not Assigned Yet' }}</h2>
                            <p class="text-sm text-gray-500">{{ tripTypeMeta.label }}</p>
                        </div>
                    </div>
                    <div class="flex items-center space-x-3">
                        <component :is="statusConfig.icon" class="h-6 w-6" />
                        <div class="leading-tight">
                            <p class="text-lg font-bold">{{ statusConfig.label }}</p>
                            <p class="text-xs font-semibold uppercase tracking-wider opacity-80">Current Status</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Details Card -->
            <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm">
                <div class="p-6">
                    <!-- Date Range -->
                    <div class="mb-8 grid gap-4 md:grid-cols-2">
                        <div class="rounded-xl bg-gray-50 p-4">
                            <div class="mb-2 flex items-center space-x-2 text-gray-500">
                                <LuCalendar class="h-4 w-4" />
                                <span class="text-xs font-semibold uppercase tracking-wider">Pickup</span>
                            </div>
                            <p class="text-sm font-semibold text-gray-900">
                                {{ formatDateTime(rental.date_from, rental.time_from) }}
                            </p>
                        </div>
                        <div class="rounded-xl bg-gray-50 p-4">
                            <div class="mb-2 flex items-center space-x-2 text-gray-500">
                                <LuClock class="h-4 w-4" />
                                <span class="text-xs font-semibold uppercase tracking-wider">Return</span>
                            </div>
                            <p class="text-sm font-semibold text-gray-900">
                                {{ formatDateTime(rental.date_to, rental.time_to) }}
                            </p>
                        </div>
                    </div>

                    <!-- Info Grid -->
                    <div class="grid gap-6 md:grid-cols-2">
                        <!-- Requester Info -->
                        <div class="space-y-4">
                            <h3 class="flex items-center space-x-2 text-sm font-bold text-gray-900">
                                <LuUser class="h-4 w-4 text-blue-600" />
                                <span>Requester Information</span>
                            </h3>
                            <div class="space-y-3">
                                <div class="flex items-start space-x-3">
                                    <div class="mt-0.5 h-2 w-2 rounded-full bg-blue-600"></div>
                                    <div>
                                        <p class="text-xs text-gray-500">Requested By/HOP</p>
                                        <p class="text-sm font-medium text-gray-900">{{ rental.requested_by || 'Not specified' }}</p>
                                    </div>
                                </div>
                                <div v-if="canViewContactNumber" class="flex items-start space-x-3">
                                    <div class="mt-0.5 h-2 w-2 rounded-full bg-blue-600"></div>
                                    <div>
                                        <p class="text-xs text-gray-500">Contact Number</p>
                                        <p class="text-sm font-medium text-gray-900">
                                            <span class="inline-flex items-center gap-2">
                                                <LuPhone class="h-3.5 w-3.5 text-gray-400" />
                                                {{ rental.contact_number || 'Not specified' }}
                                            </span>
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Destination Info -->
                        <div class="space-y-4">
                            <h3 class="flex items-center space-x-2 text-sm font-bold text-gray-900">
                                <LuMapPin class="h-4 w-4 text-blue-600" />
                                <span>Destination Details</span>
                            </h3>
                            <div class="space-y-3">
                                <div class="flex items-start space-x-3">
                                    <div class="mt-0.5 h-2 w-2 rounded-full bg-blue-600"></div>
                                    <div>
                                        <p class="text-xs text-gray-500">Region</p>
                                        <p class="text-sm font-medium text-gray-900">{{ rental.destination_region || 'Not specified' }}</p>
                                    </div>
                                </div>
                                <div class="flex items-start space-x-3">
                                    <div class="mt-0.5 h-2 w-2 rounded-full bg-blue-600"></div>
                                    <div>
                                        <p class="text-xs text-gray-500">Province / City</p>
                                        <p class="text-sm font-medium text-gray-900">
                                            {{ [rental.destination_province, rental.destination_city].filter(Boolean).join(', ') || 'Not specified' }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-6">
                        <RentalTripRouteVisualizer
                            :trip-type="rental.trip_type || 'dedicated_trip'"
                            :destination-location="rental.destination_location || ''"
                            :destination-stops="destinationStops"
                            :is-shared-ride="Boolean(rental.is_shared_ride)"
                            :shared-ride-reference="rental.shared_ride_reference || ''"
                        />
                    </div>

                    <!-- Purpose -->
                    <div v-if="rental.purpose" class="mt-6 rounded-xl bg-blue-50 p-4">
                        <div class="flex items-start space-x-3">
                            <LuFileText class="mt-0.5 h-5 w-5 text-blue-600" />
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-wider text-blue-900">Purpose of Trip</p>
                                <p class="mt-1 text-sm text-blue-900">{{ rental.purpose }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Specific Location -->
                    <div v-if="rental.destination_location" class="mt-4 rounded-xl border border-gray-200 p-4">
                        <div class="flex items-start space-x-3">
                            <LuMapPin class="mt-0.5 h-5 w-5 text-gray-400" />
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-wider text-gray-500">Specific Location</p>
                                <p class="mt-1 text-sm text-gray-900">{{ rental.destination_location }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Notes -->
                    <div v-if="rental.notes" class="mt-4 rounded-xl border border-amber-200 bg-amber-50 p-4">
                        <div class="flex items-start space-x-3">
                            <LuAlertCircle class="mt-0.5 h-5 w-5 text-amber-600" />
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-wider text-amber-900">Additional Notes</p>
                                <p class="mt-1 text-sm text-amber-900">{{ rental.notes }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Help Text -->
            <div class="text-center">
                <p class="text-xs text-gray-500">
                    Need help with this booking? Contact support for assistance.
                </p>
            </div>
        </div>
    </GuestFormPage>
</template>