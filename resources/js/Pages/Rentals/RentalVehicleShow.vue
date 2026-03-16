<script setup>
import { onMounted, ref, computed } from 'vue'
import { 
    Car, 
    Calendar, 
    Clock, 
    User, 
    Phone, 
    MapPin, 
    FileText, 
    AlertCircle,
    CheckCircle2,
    XCircle,
    Clock3,
    ArrowRight
} from 'lucide-vue-next'

const props = defineProps({
    rental_id: {
        type: String,
        required: true,
    },
})

const loading = ref(true)
const error = ref('')
const rental = ref(null)

const statusConfig = computed(() => {
    const configs = {
        pending: { 
            color: 'bg-amber-100 text-amber-800 border-amber-200', 
            icon: Clock3,
            label: 'Pending Approval'
        },
        approved: { 
            color: 'bg-emerald-100 text-emerald-800 border-emerald-200', 
            icon: CheckCircle2,
            label: 'Approved'
        },
        rejected: { 
            color: 'bg-red-100 text-red-800 border-red-200', 
            icon: XCircle,
            label: 'Rejected'
        },
        completed: { 
            color: 'bg-slate-100 text-slate-800 border-slate-200', 
            icon: CheckCircle2,
            label: 'Completed'
        },
        active: { 
            color: 'bg-blue-100 text-blue-800 border-blue-200', 
            icon: Car,
            label: 'Active'
        }
    }
    return configs[rental.value?.status?.toLowerCase()] || { 
        color: 'bg-gray-100 text-gray-800 border-gray-200', 
        icon: AlertCircle,
        label: rental.value?.status || 'Unknown'
    }
})

const formatDateTime = (date, time) => {
    if (!date) return 'Not specified'
    const dateObj = new Date(date)
    const formatted = dateObj.toLocaleDateString('en-US', { 
        weekday: 'short', 
        year: 'numeric', 
        month: 'short', 
        day: 'numeric' 
    })
    return time ? `${formatted} at ${time}` : formatted
}

const loadRental = async () => {
    loading.value = true
    error.value = ''

    try {
        const response = await fetch(`/api/guest/rental/vehicles/${props.rental_id}`)

        if (!response.ok) {
            throw new Error('Unable to load vehicle rental details.')
        }

        const payload = await response.json()
        rental.value = payload?.data ?? null
    } catch (err) {
        error.value = err?.message || 'Failed to load vehicle rental details.'
    } finally {
        loading.value = false
    }
}

onMounted(() => {
    loadRental()
})
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
                    <AlertCircle class="h-6 w-6 text-red-600" />
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
                    <Car class="h-8 w-8 text-gray-400" />
                </div>
                <h3 class="text-lg font-semibold text-gray-900">Rental Not Found</h3>
                <p class="max-w-sm text-sm text-gray-500">The requested vehicle rental details could not be found or may have been removed.</p>
            </div>
        </div>

        <!-- Content State -->
        <div v-else class="space-y-6">
            <!-- Status Banner -->
            <div :class="['rounded-2xl border-2 p-6', statusConfig.color]">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-3">
                        <component :is="statusConfig.icon" class="h-6 w-6" />
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wider opacity-80">Current Status</p>
                            <p class="text-lg font-bold">{{ statusConfig.label }}</p>
                        </div>
                    </div>
                    <div class="text-right">
                        <p class="text-xs opacity-80">Booking ID</p>
                        <p class="font-mono text-sm font-semibold">#{{ rental_id.slice(-8).toUpperCase() }}</p>
                    </div>
                </div>
            </div>

            <!-- Main Details Card -->
            <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm">
                <!-- Vehicle Header -->
                <div class="border-b border-gray-100 bg-gradient-to-r from-gray-50 to-white p-6">
                    <div class="flex items-start justify-between">
                        <div class="flex items-center space-x-4">
                            <div class="rounded-xl bg-blue-600 p-3 text-white shadow-lg shadow-blue-600/20">
                                <Car class="h-6 w-6" />
                            </div>
                            <div>
                                <h2 class="text-xl font-bold text-gray-900">{{ rental.vehicle_type || 'Vehicle Not Specified' }}</h2>
                                <p class="text-sm text-gray-500">Vehicle Rental Request</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="p-6">
                    <!-- Date Range -->
                    <div class="mb-8 grid gap-4 md:grid-cols-2">
                        <div class="rounded-xl bg-gray-50 p-4">
                            <div class="mb-2 flex items-center space-x-2 text-gray-500">
                                <Calendar class="h-4 w-4" />
                                <span class="text-xs font-semibold uppercase tracking-wider">Pickup</span>
                            </div>
                            <p class="text-sm font-semibold text-gray-900">
                                {{ formatDateTime(rental.date_from, rental.time_from) }}
                            </p>
                        </div>
                        <div class="rounded-xl bg-gray-50 p-4">
                            <div class="mb-2 flex items-center space-x-2 text-gray-500">
                                <Clock class="h-4 w-4" />
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
                                <User class="h-4 w-4 text-blue-600" />
                                <span>Requester Information</span>
                            </h3>
                            <div class="space-y-3">
                                <div class="flex items-start space-x-3">
                                    <div class="mt-0.5 h-2 w-2 rounded-full bg-blue-600"></div>
                                    <div>
                                        <p class="text-xs text-gray-500">Requested By</p>
                                        <p class="text-sm font-medium text-gray-900">{{ rental.requested_by || 'Not specified' }}</p>
                                    </div>
                                </div>
                                <div class="flex items-start space-x-3">
                                    <div class="mt-0.5 h-2 w-2 rounded-full bg-blue-600"></div>
                                    <div>
                                        <p class="text-xs text-gray-500">Contact Number</p>
                                        <p class="text-sm font-medium text-gray-900">{{ rental.contact_number || 'Not specified' }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Destination Info -->
                        <div class="space-y-4">
                            <h3 class="flex items-center space-x-2 text-sm font-bold text-gray-900">
                                <MapPin class="h-4 w-4 text-blue-600" />
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

                    <!-- Purpose -->
                    <div v-if="rental.purpose" class="mt-6 rounded-xl bg-blue-50 p-4">
                        <div class="flex items-start space-x-3">
                            <FileText class="mt-0.5 h-5 w-5 text-blue-600" />
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-wider text-blue-900">Purpose of Trip</p>
                                <p class="mt-1 text-sm text-blue-900">{{ rental.purpose }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Specific Location -->
                    <div v-if="rental.destination_location" class="mt-4 rounded-xl border border-gray-200 p-4">
                        <div class="flex items-start space-x-3">
                            <MapPin class="mt-0.5 h-5 w-5 text-gray-400" />
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-wider text-gray-500">Specific Location</p>
                                <p class="mt-1 text-sm text-gray-900">{{ rental.destination_location }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Notes -->
                    <div v-if="rental.notes" class="mt-4 rounded-xl border border-amber-200 bg-amber-50 p-4">
                        <div class="flex items-start space-x-3">
                            <AlertCircle class="mt-0.5 h-5 w-5 text-amber-600" />
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