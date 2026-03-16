<script setup>
import { ref, computed, watch } from 'vue'
import { 
    Building2, 
    Calendar, 
    Clock, 
    User, 
    Users,
    CheckCircle2, 
    XCircle, 
    AlertCircle,
    MapPin,
    ChevronRight,
    Loader2,
    PartyPopper
} from 'lucide-vue-next'

const props = defineProps({
    data: {
        type: Object,
        required: true,
    },
})

const emit = defineEmits(['updated', 'failedUpdate'])

const showModal = ref(false)
const formState = ref({ ...props.data })
const processing = ref(false)

// Permission check - adjust based on your auth system
const canApprove = computed(() => {
    // Replace with your actual permission check
    return true // Placeholder
})

const statusConfig = computed(() => {
    const configs = {
        approved: { 
            color: 'bg-emerald-100 text-emerald-700 border-emerald-200',
            bg: 'bg-emerald-50',
            icon: CheckCircle2,
            label: 'Confirmed'
        },
        rejected: { 
            color: 'bg-red-100 text-red-700 border-red-200',
            bg: 'bg-red-50',
            icon: XCircle,
            label: 'Declined'
        },
        pending: { 
            color: 'bg-amber-100 text-amber-700 border-amber-200',
            bg: 'bg-amber-50',
            icon: AlertCircle,
            label: 'Pending'
        }
    }
    return configs[formState.value.status] || configs.pending
})

const formatDate = (date) => {
    if (!date) return 'N/A'
    return new Date(date).toLocaleDateString('en-US', {
        month: 'short',
        day: 'numeric',
        year: 'numeric'
    })
}

const formatTime = (time) => {
    if (!time) return ''
    return time
}

const formatDuration = computed(() => {
    if (!formState.value.date_from || !formState.value.date_to) return null
    const start = new Date(formState.value.date_from)
    const end = new Date(formState.value.date_to)
    const diffTime = Math.abs(end - start)
    const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24))
    return diffDays === 1 ? '1 day' : `${diffDays} days`
})

const updateStatus = async (status) => {
    if (!canApprove.value || processing.value) return
    
    processing.value = true
    try {
        // Replace with your actual API call
        const response = await fetch(`/api/rental/venues/${formState.value.id}/status`, {
            method: 'PUT',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                status,
                notes: formState.value.notes
            })
        })
        
        if (!response.ok) throw new Error('Update failed')
        
        const data = await response.json()
        formState.value = { ...data.data }
        emit('updated', formState.value)
        showModal.value = false
    } catch (error) {
        emit('failedUpdate', error)
    } finally {
        processing.value = false
    }
}

watch(() => props.data, (newData) => {
    formState.value = { ...newData }
}, { deep: true })
</script>

<template>
    <!-- Card -->
    <div 
        @click="showModal = true"
        :class="[
            'group relative overflow-hidden rounded-xl border transition-all duration-300 cursor-pointer',
            'bg-white hover:shadow-lg hover:shadow-indigo-100/50 hover:border-indigo-200',
            'border-gray-200'
        ]"
    >
        <!-- Status Stripe -->
        <div :class="['absolute left-0 top-0 h-full w-1', statusConfig.bg.replace('50', '500')]"></div>
        
        <div class="p-4 pl-5">
            <div class="flex items-start justify-between gap-3">
                <!-- Main Info -->
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2 mb-1">
                        <div :class="['rounded-lg p-1.5', statusConfig.bg]">
                            <PartyPopper class="h-4 w-4" :class="statusConfig.color.split(' ')[0].replace('bg-', 'text-').replace('100', '600')" />
                        </div>
                        <h3 class="font-semibold text-gray-900 truncate">{{ formState.event_name || 'Untitled Event' }}</h3>
                    </div>
                    
                    <p class="text-sm text-indigo-600 font-medium mb-2">{{ formState.venue_type }}</p>
                    
                    <div class="flex flex-wrap items-center gap-x-3 gap-y-1 text-xs text-gray-500">
                        <span class="flex items-center gap-1">
                            <Calendar class="h-3 w-3" />
                            {{ formatDate(formState.date_from) }} - {{ formatDate(formState.date_to) }}
                        </span>
                        <span v-if="formatDuration" class="text-indigo-500 font-medium">
                            ({{ formatDuration }})
                        </span>
                    </div>
                    
                    <div v-if="formState.expected_attendees" class="mt-2 flex items-center gap-1 text-xs text-gray-500">
                        <Users class="h-3 w-3" />
                        {{ formState.expected_attendees }} expected attendees
                    </div>
                </div>

                <!-- Status Badge -->
                <div class="flex flex-col items-end gap-1">
                    <span :class="[
                        'inline-flex items-center gap-1 rounded-full px-2.5 py-1 text-xs font-medium border',
                        statusConfig.color
                    ]">
                        <component :is="statusConfig.icon" class="h-3 w-3" />
                        {{ formState.status }}
                    </span>
                    <span class="text-[10px] text-gray-400">{{ formatDate(formState.updated_at) }}</span>
                </div>
            </div>

            <!-- Hover Hint -->
            <div class="mt-3 flex items-center text-xs text-gray-400 opacity-0 group-hover:opacity-100 transition-opacity">
                <span>Click to review</span>
                <ChevronRight class="h-3 w-3 ml-1" />
            </div>
        </div>
    </div>

    <!-- Modal -->
    <Teleport to="body">
        <Transition
            enter-active-class="transition duration-200 ease-out"
            enter-from-class="opacity-0 scale-95"
            enter-to-class="opacity-100 scale-100"
            leave-active-class="transition duration-150 ease-in"
            leave-from-class="opacity-100 scale-100"
            leave-to-class="opacity-0 scale-95"
        >
            <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center p-4">
                <!-- Backdrop -->
                <div 
                    class="absolute inset-0 bg-gray-900/50 backdrop-blur-sm" 
                    @click="showModal = false"
                ></div>

                <!-- Modal Content -->
                <div class="relative w-full max-w-lg overflow-hidden rounded-2xl bg-white shadow-2xl">
                    <!-- Header -->
                    <div class="relative overflow-hidden bg-gradient-to-br from-indigo-600 to-purple-700 px-6 py-5 text-white">
                        <div class="absolute -right-6 -top-6 opacity-10">
                            <Building2 class="h-32 w-32" />
                        </div>
                        
                        <div class="relative flex items-start justify-between">
                            <div>
                                <div class="mb-2 inline-flex items-center gap-2 rounded-full bg-white/20 px-3 py-1 text-xs font-medium backdrop-blur-sm">
                                    <Building2 class="h-3 w-3" />
                                    <span class="uppercase tracking-wider">Venue Request</span>
                                </div>
                                <h2 class="text-xl font-bold">{{ formState.event_name || 'Untitled Event' }}</h2>
                                <p class="mt-1 text-sm text-indigo-100">{{ formState.venue_type }}</p>
                            </div>
                            <button 
                                @click="showModal = false"
                                class="rounded-lg p-1 text-white/70 hover:bg-white/20 hover:text-white transition-colors"
                            >
                                <XCircle class="h-5 w-5" />
                            </button>
                        </div>
                    </div>

                    <!-- Body -->
                    <div class="px-6 py-5 space-y-5">
                        <!-- Status Banner -->
                        <div :class="['rounded-xl border-2 p-4', statusConfig.color]">
                            <div class="flex items-center gap-3">
                                <div class="rounded-full bg-white/80 p-2">
                                    <component :is="statusConfig.icon" class="h-5 w-5" />
                                </div>
                                <div>
                                    <p class="text-xs font-semibold uppercase tracking-wider opacity-70">Current Status</p>
                                    <p class="text-lg font-bold">{{ statusConfig.label }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- Organizer -->
                        <div class="flex items-start gap-4">
                            <div class="rounded-xl bg-indigo-50 p-2.5">
                                <User class="h-5 w-5 text-indigo-600" />
                            </div>
                            <div class="flex-1">
                                <p class="text-xs font-semibold uppercase tracking-wider text-gray-500">Organizer</p>
                                <p class="text-lg font-semibold text-gray-900">{{ formState.requested_by || 'N/A' }}</p>
                                <p v-if="formState.contact_number" class="text-sm text-gray-600 mt-1">
                                    {{ formState.contact_number }}
                                </p>
                            </div>
                        </div>

                        <!-- Schedule -->
                        <div class="rounded-xl border border-gray-200 bg-gray-50/50 p-4">
                            <div class="grid gap-4 sm:grid-cols-2">
                                <div>
                                    <div class="flex items-center gap-2 text-xs font-medium text-gray-500 mb-1">
                                        <Calendar class="h-3.5 w-3.5" />
                                        Event Dates
                                    </div>
                                    <p class="text-sm font-semibold text-gray-900">
                                        {{ formatDate(formState.date_from) }} - {{ formatDate(formState.date_to) }}
                                    </p>
                                    <p v-if="formatDuration" class="text-xs text-indigo-600 mt-1 font-medium">
                                        {{ formatDuration }} duration
                                    </p>
                                </div>
                                <div>
                                    <div class="flex items-center gap-2 text-xs font-medium text-gray-500 mb-1">
                                        <Clock class="h-3.5 w-3.5" />
                                        Daily Schedule
                                    </div>
                                    <p class="text-sm font-semibold text-gray-900">
                                        {{ formatTime(formState.time_from) || 'N/A' }} - {{ formatTime(formState.time_to) || 'N/A' }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Attendees -->
                        <div v-if="formState.expected_attendees" class="rounded-xl bg-indigo-50/50 p-4">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <div class="rounded-full bg-indigo-100 p-2">
                                        <Users class="h-5 w-5 text-indigo-600" />
                                    </div>
                                    <div>
                                        <p class="text-xs font-semibold uppercase tracking-wider text-indigo-600">Expected Attendance</p>
                                        <p class="text-2xl font-bold text-indigo-900">{{ formState.expected_attendees }}</p>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <p class="text-xs text-indigo-600">people</p>
                                </div>
                            </div>
                        </div>

                        <!-- Notes Input -->
                        <div v-if="canApprove">
                            <label class="block text-xs font-semibold text-gray-700 mb-2">
                                Reviewer Notes
                            </label>
                            <textarea
                                v-model="formState.notes"
                                rows="3"
                                class="w-full rounded-lg border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500"
                                placeholder="Add notes about this venue decision..."
                            ></textarea>
                        </div>
                        <div v-else-if="formState.notes" class="rounded-lg bg-gray-50 p-3">
                            <p class="text-xs font-semibold text-gray-500 mb-1">Reviewer Notes</p>
                            <p class="text-sm text-gray-700">{{ formState.notes }}</p>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div v-if="canApprove" class="flex items-center justify-end gap-3 border-t border-gray-200 bg-gray-50 px-6 py-4">
                        <button
                            @click="updateStatus('rejected')"
                            :disabled="formState.status === 'rejected' || processing"
                            class="inline-flex items-center gap-2 rounded-lg px-4 py-2 text-sm font-medium transition-all focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed"
                            :class="formState.status === 'rejected' 
                                ? 'bg-gray-200 text-gray-500' 
                                : 'bg-white border border-gray-300 text-gray-700 hover:bg-red-50 hover:text-red-700 hover:border-red-300'"
                        >
                            <XCircle v-if="!processing" class="h-4 w-4" />
                            <span v-if="formState.status === 'rejected'">Declined</span>
                            <span v-else>Decline</span>
                        </button>
                        
                        <button
                            @click="updateStatus('approved')"
                            :disabled="formState.status === 'approved' || processing"
                            class="inline-flex items-center gap-2 rounded-lg px-4 py-2 text-sm font-medium transition-all focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed"
                            :class="formState.status === 'approved'
                                ? 'bg-emerald-100 text-emerald-700'
                                : 'bg-emerald-600 text-white hover:bg-emerald-700 shadow-lg shadow-emerald-600/20'"
                        >
                            <Loader2 v-if="processing" class="h-4 w-4 animate-spin" />
                            <CheckCircle2 v-else class="h-4 w-4" />
                            <span v-if="formState.status === 'approved'">Confirmed</span>
                            <span v-else>Confirm Venue</span>
                        </button>
                    </div>
                    
                    <!-- View Only Footer -->
                    <div v-else class="flex items-center justify-center border-t border-gray-200 bg-gray-50 px-6 py-3">
                        <p class="text-xs text-gray-500">You don't have permission to modify this request</p>
                    </div>
                </div>
            </div>
        </Transition>
    </Teleport>
</template>