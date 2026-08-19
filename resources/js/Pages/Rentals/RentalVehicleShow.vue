<script>
import ApiMixin from '@/Modules/mixins/ApiMixin'
import RentalTripRouteVisualizer from '@/Pages/Rentals/components/RentalTripRouteVisualizer.vue'
import { getTripTypeMeta } from '@/Pages/Rentals/constants/tripWorkflows'

export default {
    name: 'RentalVehicleShow',
    components: {
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
                    color: 'bg-amber-50 dark:bg-amber-900/30 text-amber-800 dark:text-amber-300 border-amber-200 dark:border-amber-800',
                    icon: 'LuClock3',
                    label: 'Pending Approval',
                },
                approved: {
                    color: 'bg-emerald-50 dark:bg-emerald-900/30 text-emerald-800 dark:text-emerald-300 border-emerald-200 dark:border-emerald-800',
                    icon: 'LuCheckCircle2',
                    label: 'Approved',
                },
                in_progress: {
                    color: 'bg-blue-50 dark:bg-blue-900/30 text-blue-800 dark:text-blue-300 border-blue-200 dark:border-blue-800',
                    icon: 'LuClock',
                    label: 'In Progress',
                },
                rejected: {
                    color: 'bg-red-50 dark:bg-red-900/30 text-red-800 dark:text-red-300 border-red-200 dark:border-red-800',
                    icon: 'LuXCircle',
                    label: 'Rejected',
                },
                cancelled: {
                    color: 'bg-slate-50 dark:bg-slate-800/50 text-slate-800 dark:text-slate-300 border-slate-200 dark:border-slate-700',
                    icon: 'LuXCircle',
                    label: 'Cancelled',
                },
                completed: {
                    color: 'bg-slate-50 dark:bg-slate-800/50 text-slate-800 dark:text-slate-300 border-slate-200 dark:border-slate-700',
                    icon: 'LuCheckCircle2',
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
        guide-key="rental-vehicle-detail"
        :delay-ready="true"
        max-width="max-w-4xl"
    >
        <!-- Loading State -->
        <div v-if="loading" class="rounded-2xl border border-gray-100 dark:border-slate-800 bg-white/80 dark:bg-slate-900/80 backdrop-blur-lg p-8">
            <div class="flex flex-col items-center justify-center space-y-4 py-12">
                <div class="relative">
                    <div class="h-12 w-12 rounded-full border-4 border-gray-200 dark:border-slate-700"></div>
                    <div class="absolute inset-0 h-12 w-12 animate-spin rounded-full border-4 border-blue-600 dark:border-blue-500 border-t-transparent dark:border-t-transparent"></div>
                </div>
                <p class="text-sm font-medium text-gray-600 dark:text-slate-400">Loading rental details...</p>
            </div>
        </div>

        <!-- Error State -->
        <div v-else-if="error" class="rounded-2xl border border-red-200 dark:border-red-900/50 bg-red-50 dark:bg-red-900/20 backdrop-blur-lg p-8">
            <div class="flex flex-col items-center justify-center space-y-3 py-8 text-center">
                <div class="rounded-full bg-red-100 dark:bg-red-900/50 p-3">
                    <LuAlertCircle class="h-6 w-6 text-red-600 dark:text-red-400" />
                </div>
                <h3 class="text-lg font-semibold text-red-900 dark:text-red-200">Failed to Load</h3>
                <p class="max-w-sm text-sm text-red-700 dark:text-red-300">{{ error }}</p>
                <button 
                    @click="loadRental" 
                    class="mt-2 rounded-lg bg-red-600 dark:bg-red-700 px-4 py-2 text-sm font-medium text-white transition-colors hover:bg-red-700 dark:hover:bg-red-600 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2"
                >
                    Try Again
                </button>
            </div>
        </div>

        <!-- Empty State -->
        <div v-else-if="!rental" class="rounded-2xl border border-gray-100 dark:border-slate-800 bg-white/80 dark:bg-slate-900/80 backdrop-blur-lg p-8">
            <div class="flex flex-col items-center justify-center space-y-3 py-12 text-center">
                <div class="rounded-full bg-gray-100 dark:bg-slate-800 p-4">
                    <LuCar class="h-8 w-8 text-gray-400 dark:text-slate-500" />
                </div>
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Rental Not Found</h3>
                <p class="max-w-sm text-sm text-gray-500 dark:text-slate-400">The requested vehicle rental details could not be found or may have been removed.</p>
            </div>
        </div>

        <!-- Content State -->
        <div v-else data-guide="rental-details" class="space-y-6">
            <!-- Status Banner -->
            <div :class="['rounded-2xl border-2 p-6 backdrop-blur-lg', statusConfig.color]">
                <div class="flex items-center justify-between gap-5">
                    <!-- Vehicle Header -->
                    <div class="flex items-center space-x-4">
                        <div class="rounded-xl bg-blue-600 dark:bg-blue-500 p-3 text-white shadow-lg shadow-blue-600/20">
                            <LuCar class="h-6 w-6" />
                        </div>
                        <div class="leading-tight">
                            <h2 class="text-base sm:text-lg font-bold">{{ rental.vehicle_type || 'Vehicle Not Assigned Yet' }}</h2>
                            <p class="text-sm opacity-80">{{ tripTypeMeta.label }}</p>
                        </div>
                    </div>
                    <div class="flex items-center space-x-3">
                        <component :is="statusConfig.icon" class="h-6 w-6" />
                        <div class="leading-tight">
                            <p class="text-base sm:text-lg font-bold">{{ statusConfig.label }}</p>
                            <p class="text-xs font-semibold uppercase tracking-wider opacity-80">Current Status</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Details Card -->
            <div class="overflow-hidden rounded-2xl border border-gray-100 dark:border-slate-800 bg-white/80 dark:bg-slate-900/80 backdrop-blur-lg shadow-sm">
                <div class="p-6">
                    <!-- Date Range -->
                    <div class="mb-8 grid gap-4 md:grid-cols-2">
                        <div class="rounded-xl bg-slate-50 dark:bg-slate-800/50 p-4 border border-slate-100 dark:border-slate-800/50">
                            <div class="mb-2 flex items-center space-x-2 text-slate-500 dark:text-slate-400">
                                <LuCalendar class="h-4 w-4" />
                                <span class="text-xs font-semibold uppercase tracking-wider">Pickup</span>
                            </div>
                            <p class="text-sm font-semibold text-slate-900 dark:text-white">
                                {{ formatDateTime(rental.date_from, rental.time_from) }}
                            </p>
                        </div>
                        <div class="rounded-xl bg-slate-50 dark:bg-slate-800/50 p-4 border border-slate-100 dark:border-slate-800/50">
                            <div class="mb-2 flex items-center space-x-2 text-slate-500 dark:text-slate-400">
                                <LuClock class="h-4 w-4" />
                                <span class="text-xs font-semibold uppercase tracking-wider">Return</span>
                            </div>
                            <p class="text-sm font-semibold text-slate-900 dark:text-white">
                                {{ formatDateTime(rental.date_to, rental.time_to) }}
                            </p>
                        </div>
                    </div>

                    <!-- Public Details -->
                    <div class="grid gap-6 md:grid-cols-2">
                        <div class="space-y-4">
                            <h3 class="flex items-center space-x-2 text-sm font-bold text-slate-900 dark:text-white">
                                <LuUser class="h-4 w-4 text-blue-600 dark:text-blue-400" />
                                <span>Booking Snapshot</span>
                            </h3>
                            <div class="space-y-3">
                                <div class="flex items-start space-x-3">
                                    <div class="mt-0.5 h-2 w-2 rounded-full bg-blue-600 dark:bg-blue-400"></div>
                                    <div>
                                        <p class="text-xs text-slate-500 dark:text-slate-400">Booking Reference</p>
                                        <p class="text-sm font-medium text-slate-900 dark:text-white">{{ rental.id }}</p>
                                    </div>
                                </div>
                                <div class="flex items-start space-x-3">
                                    <div class="mt-0.5 h-2 w-2 rounded-full bg-blue-600 dark:bg-blue-400"></div>
                                    <div>
                                        <p class="text-xs text-slate-500 dark:text-slate-400">Trip Type</p>
                                        <p class="text-sm font-medium text-slate-900 dark:text-white">{{ tripTypeMeta.label }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="space-y-4">
                            <h3 class="flex items-center space-x-2 text-sm font-bold text-slate-900 dark:text-white">
                                <LuMapPin class="h-4 w-4 text-blue-600 dark:text-blue-400" />
                                <span>Public Details</span>
                            </h3>
                            <div class="space-y-3">
                                <div class="flex items-start space-x-3">
                                    <div class="mt-0.5 h-2 w-2 rounded-full bg-blue-600 dark:bg-blue-400"></div>
                                    <div>
                                        <p class="text-xs text-slate-500 dark:text-slate-400">Vehicle Type</p>
                                        <p class="text-sm font-medium text-slate-900 dark:text-white">{{ rental.vehicle_type || 'To be assigned' }}</p>
                                    </div>
                                </div>
                                <div class="flex items-start space-x-3">
                                    <div class="mt-0.5 h-2 w-2 rounded-full bg-blue-600 dark:bg-blue-400"></div>
                                    <div>
                                        <p class="text-xs text-slate-500 dark:text-slate-400">Current Status</p>
                                        <p class="text-sm font-medium text-slate-900 dark:text-white">{{ statusConfig.label }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-6 rounded-xl border border-blue-100 dark:border-blue-900/30 bg-blue-50/80 dark:bg-blue-900/10 p-4">
                        <div class="flex items-start space-x-3">
                            <LuAlertCircle class="mt-0.5 h-5 w-5 text-blue-600 dark:text-blue-400" />
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-wider text-blue-900 dark:text-blue-300">Privacy Notice</p>
                                <p class="mt-1 text-sm text-blue-900 dark:text-blue-300/80">
                                    This public page only shows non-sensitive booking details. Contact the rentals team if you need the full internal request record.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Help Text -->
            <div class="text-center">
                <p class="text-xs text-slate-500 dark:text-slate-400">
                    Need help with this booking? Contact support for assistance.
                </p>
            </div>
        </div>
    </GuestFormPage>
</template>
