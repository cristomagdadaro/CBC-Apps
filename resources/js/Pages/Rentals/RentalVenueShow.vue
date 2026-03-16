<script setup>
import { onMounted, ref, computed } from 'vue'
import { 
    MapPin, 
    Calendar, 
    Clock, 
    Users, 
    User, 
    Phone, 
    FileText, 
    AlertCircle,
    CheckCircle2,
    XCircle,
    Clock3,
    Building2,
    PartyPopper,
    Loader2
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
            color: 'bg-amber-50 text-amber-800 border-amber-200', 
            icon: Clock3,
            label: 'Pending Approval',
            accent: 'amber'
        },
        approved: { 
            color: 'bg-emerald-50 text-emerald-800 border-emerald-200', 
            icon: CheckCircle2,
            label: 'Confirmed',
            accent: 'emerald'
        },
        rejected: { 
            color: 'bg-red-50 text-red-800 border-red-200', 
            icon: XCircle,
            label: 'Declined',
            accent: 'red'
        },
        completed: { 
            color: 'bg-slate-50 text-slate-800 border-slate-200', 
            icon: CheckCircle2,
            label: 'Event Completed',
            accent: 'slate'
        },
        cancelled: { 
            color: 'bg-gray-50 text-gray-800 border-gray-200', 
            icon: XCircle,
            label: 'Cancelled',
            accent: 'gray'
        }
    }
    const status = rental.value?.status?.toLowerCase()
    return configs[status] || { 
        color: 'bg-gray-50 text-gray-800 border-gray-200', 
        icon: AlertCircle,
        label: rental.value?.status || 'Unknown',
        accent: 'gray'
    }
})

const formatDateTime = (date, time) => {
    if (!date) return 'Not specified'
    const dateObj = new Date(date)
    const formatted = dateObj.toLocaleDateString('en-US', { 
        weekday: 'long', 
        year: 'numeric', 
        month: 'long', 
        day: 'numeric' 
    })
    return time ? `${formatted} at ${time}` : formatted
}

const formatDuration = computed(() => {
    if (!rental.value?.date_from || !rental.value?.date_to) return null
    const start = new Date(rental.value.date_from)
    const end = new Date(rental.value.date_to)
    const diffTime = Math.abs(end - start)
    const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24))
    return diffDays === 1 ? '1 day' : `${diffDays} days`
})

const loadRental = async () => {
    loading.value = true
    error.value = ''

    try {
        const response = await fetch(`/api/guest/rental/venues/${props.rental_id}`)

        if (!response.ok) {
            throw new Error('Unable to load venue rental details.')
        }

        const payload = await response.json()
        rental.value = payload?.data ?? null
    } catch (err) {
        error.value = err?.message || 'Failed to load venue rental details.'
    } finally {
        loading.value = false
    }
}

onMounted(() => {
    loadRental()
})
</script>

<template>
    <Head title="Venue Rental Details" />

    <GuestFormPage
        title="Venue Rental Details"
        subtitle="View your event booking details and status."
        :delay-ready="true"
    >
        <!-- Loading State -->
        <div v-if="loading" class="rounded-2xl border border-gray-200 bg-white p-8 shadow-sm">
            <div class="flex flex-col items-center justify-center space-y-4 py-12">
                <Loader2 class="h-10 w-10 animate-spin text-indigo-600" />
                <div class="text-center">
                    <p class="text-sm font-medium text-gray-900">Loading venue details...</p>
                    <p class="text-xs text-gray-500 mt-1">Please wait a moment</p>
                </div>
            </div>
        </div>

        <!-- Error State -->
        <div v-else-if="error" class="rounded-2xl border border-red-200 bg-red-50/50 p-8 backdrop-blur-sm">
            <div class="flex flex-col items-center justify-center space-y-4 py-8 text-center">
                <div class="rounded-full bg-red-100 p-4 ring-4 ring-red-50">
                    <AlertCircle class="h-8 w-8 text-red-600" />
                </div>
                <div>
                    <h3 class="text-lg font-semibold text-red-900">Unable to Load Details</h3>
                    <p class="mt-1 max-w-sm text-sm text-red-700">{{ error }}</p>
                </div>
                <button 
                    @click="loadRental" 
                    class="mt-2 inline-flex items-center gap-2 rounded-lg bg-red-600 px-5 py-2.5 text-sm font-medium text-white transition-all hover:bg-red-700 hover:shadow-lg hover:shadow-red-600/20 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 active:scale-95"
                >
                    Try Again
                </button>
            </div>
        </div>

        <!-- Empty State -->
        <div v-else-if="!rental" class="rounded-2xl border border-gray-200 bg-white p-8 shadow-sm">
            <div class="flex flex-col items-center justify-center space-y-4 py-12 text-center">
                <div class="rounded-full bg-gray-100 p-4">
                    <Building2 class="h-8 w-8 text-gray-400" />
                </div>
                <div>
                    <h3 class="text-lg font-semibold text-gray-900">Booking Not Found</h3>
                    <p class="mt-1 max-w-sm text-sm text-gray-500">
                        The venue rental you're looking for doesn't exist or may have been removed from our system.
                    </p>
                </div>
            </div>
        </div>

        <!-- Content State -->
        <div v-else class="space-y-6">
            <!-- Status Banner -->
            <div :class="['relative overflow-hidden rounded-2xl border-2 p-6', statusConfig.color]">
                <div class="absolute -right-6 -top-6 opacity-10">
                    <component :is="statusConfig.icon" class="h-32 w-32" />
                </div>
                <div class="relative flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div class="flex items-center gap-4">
                        <div :class="['rounded-xl bg-white/80 p-3 shadow-sm backdrop-blur-sm']">
                            <component :is="statusConfig.icon" class="h-6 w-6" />
                        </div>
                        <div>
                            <p class="text-xs font-bold uppercase tracking-wider opacity-70">Booking Status</p>
                            <p class="text-xl font-bold">{{ statusConfig.label }}</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-3 rounded-lg bg-white/60 px-4 py-2 backdrop-blur-sm">
                        <span class="text-xs font-medium opacity-70">Reference</span>
                        <span class="font-mono text-sm font-bold tracking-wider">#{{ rental_id.slice(-8).toUpperCase() }}</span>
                    </div>
                </div>
            </div>

            <!-- Event Card -->
            <div class="overflow-hidden rounded-2xl bg-white shadow-sm">
                <!-- Event Header -->
                <div class="relative border-b border-gray-100 bg-gradient-to-br from-AB to-AB p-6 text-white">
                    <div class="relative">
                        <div class="mb-3 inline-flex items-center gap-2 rounded-full bg-white/20 px-3 py-1 text-xs font-medium backdrop-blur-sm">
                            <Building2 class="h-3.5 w-3.5" />
                            <span class="uppercase tracking-wider">{{ rental.venue_type || 'Venue' }}</span>
                        </div>
                        <h2 class="text-2xl font-bold leading-tight">
                            {{ rental.event_name || 'Untitled Event' }}
                        </h2>
                        <p v-if="formatDuration" class="mt-2 flex items-center gap-2 text-indigo-100">
                            <Clock class="h-4 w-4" />
                            <span class="text-sm">{{ formatDuration }} duration</span>
                        </p>
                    </div>
                </div>

                <div class="p-6">
                    <!-- Date & Time Section -->
                    <div class="mb-8">
                        <h3 class="mb-4 flex items-center gap-2 text-sm font-bold text-gray-900">
                            <Calendar class="h-4 w-4 text-indigo-600" />
                            Event Schedule
                        </h3>
                        <div class="grid gap-4 sm:grid-cols-2">
                            <div class="group relative overflow-hidden rounded-xl border border-gray-200 bg-gradient-to-br from-gray-50 to-white p-5 transition-all hover:border-indigo-300 hover:shadow-md">
                                <div class="absolute -right-4 -top-4 rounded-full bg-indigo-100 p-3 opacity-0 transition-opacity group-hover:opacity-100">
                                    <Calendar class="h-6 w-6 text-indigo-600" />
                                </div>
                                <div class="relative">
                                    <p class="mb-1 text-xs font-semibold uppercase tracking-wider text-gray-500">Start Date & Time</p>
                                    <p class="text-sm font-bold text-gray-900">
                                        {{ formatDateTime(rental.date_from, rental.time_from) }}
                                    </p>
                                </div>
                            </div>

                            <div class="group relative overflow-hidden rounded-xl border border-gray-200 bg-gradient-to-br from-gray-50 to-white p-5 transition-all hover:border-indigo-300 hover:shadow-md">
                                <div class="absolute -right-4 -top-4 rounded-full bg-indigo-100 p-3 opacity-0 transition-opacity group-hover:opacity-100">
                                    <Clock class="h-6 w-6 text-indigo-600" />
                                </div>
                                <div class="relative">
                                    <p class="mb-1 text-xs font-semibold uppercase tracking-wider text-gray-500">End Date & Time</p>
                                    <p class="text-sm font-bold text-gray-900">
                                        {{ formatDateTime(rental.date_to, rental.time_to) }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Details Grid -->
                    <div class="grid gap-8 lg:grid-cols-2">
                        <!-- Attendance Info -->
                        <div class="space-y-4">
                            <h3 class="flex items-center gap-2 text-sm font-bold text-gray-900">
                                <Users class="h-4 w-4 text-indigo-600" />
                                Attendance
                            </h3>
                            <div class="rounded-xl bg-indigo-50/50 p-4">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="text-xs font-medium text-indigo-600 uppercase tracking-wider">Expected Attendees</p>
                                        <p class="mt-1 text-2xl font-bold text-indigo-900">
                                            {{ rental.expected_attendees?.toLocaleString() || 'Not specified' }}
                                        </p>
                                    </div>
                                    <div class="rounded-full bg-indigo-100 p-3">
                                        <Users class="h-6 w-6 text-indigo-600" />
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Contact Info -->
                        <div class="space-y-4">
                            <h3 class="flex items-center gap-2 text-sm font-bold text-gray-900">
                                <User class="h-4 w-4 text-indigo-600" />
                                Organizer Details
                            </h3>
                            <div class="space-y-3">
                                <div class="flex items-start gap-3 rounded-lg border border-gray-100 bg-gray-50/50 p-3">
                                    <User class="mt-0.5 h-4 w-4 text-gray-400" />
                                    <div>
                                        <p class="text-xs text-gray-500">Requested By</p>
                                        <p class="font-medium text-gray-900">{{ rental.requested_by || 'Not specified' }}</p>
                                    </div>
                                </div>
                                <div class="flex items-start gap-3 rounded-lg border border-gray-100 bg-gray-50/50 p-3">
                                    <Phone class="mt-0.5 h-4 w-4 text-gray-400" />
                                    <div>
                                        <p class="text-xs text-gray-500">Contact Number</p>
                                        <p class="font-medium text-gray-900">{{ rental.contact_number || 'Not specified' }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Location Section -->
                    <div class="mt-8">
                        <h3 class="mb-4 flex items-center gap-2 text-sm font-bold text-gray-900">
                            <MapPin class="h-4 w-4 text-indigo-600" />
                            Location Details
                        </h3>
                        <div class="overflow-hidden rounded-xl border border-gray-200 bg-gray-50">
                            <div class="border-b border-gray-200 bg-white p-4">
                                <div class="flex items-start gap-3">
                                    <div class="mt-1 flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-indigo-100">
                                        <MapPin class="h-4 w-4 text-indigo-600" />
                                    </div>
                                    <div class="flex-1">
                                        <p class="text-xs font-semibold uppercase tracking-wider text-gray-500">Venue Address</p>
                                        <p class="mt-1 text-base font-semibold text-gray-900">
                                            {{ rental.destination_location || 'Location not specified' }}
                                        </p>
                                        <div class="mt-2 flex flex-wrap gap-2">
                                            <span v-if="rental.destination_city" class="inline-flex items-center rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-medium text-gray-800">
                                                {{ rental.destination_city }}
                                            </span>
                                            <span v-if="rental.destination_province" class="inline-flex items-center rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-medium text-gray-800">
                                                {{ rental.destination_province }}
                                            </span>
                                            <span v-if="rental.destination_region" class="inline-flex items-center rounded-full bg-indigo-100 px-2.5 py-0.5 text-xs font-medium text-indigo-800">
                                                {{ rental.destination_region }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Notes Section -->
                    <div v-if="rental.notes" class="mt-6">
                        <div class="rounded-xl border-l-4 border-amber-400 bg-amber-50 p-5">
                            <div class="flex items-start gap-3">
                                <AlertCircle class="mt-0.5 h-5 w-5 shrink-0 text-amber-600" />
                                <div>
                                    <p class="text-xs font-bold uppercase tracking-wider text-amber-800">Special Instructions</p>
                                    <p class="mt-1 text-sm leading-relaxed text-amber-900">
                                        {{ rental.notes }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer Info -->
            <div class="rounded-xl border border-gray-200 bg-gray-50/50 p-4 text-center">
                <p class="text-xs text-gray-500">
                    Questions about your booking? Contact our events team for assistance.
                </p>
            </div>
        </div>
    </GuestFormPage>
</template>