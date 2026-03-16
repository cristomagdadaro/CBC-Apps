<script setup>
import { ref, computed, watch } from 'vue'
import { 
    Car, 
    Calendar, 
    Clock, 
    User, 
    CheckCircle2, 
    XCircle, 
    AlertCircle,
    FileText,
    Users,
    ChevronRight,
    Loader2
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
            label: 'Approved'
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

const updateStatus = async (status) => {
    if (!canApprove.value || processing.value) return
    
    processing.value = true
    try {
        // Replace with your actual API call
        const response = await fetch(`/api/rental/vehicles/${formState.value.id}/status`, {
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
            'bg-white hover:shadow-lg hover:shadow-gray-200/50 hover:border-gray-300',
            'border-gray-200'
        ]"
    >
        <!-- Status Stripe -->
        <div :class="['absolute left-0 top-0 h-full w-1', statusConfig.bg.replace('bg-', 'bg-').replace('50', '500')]"></div>
        
        <div class="p-4 pl-5">
            <div class="flex items-start justify-between gap-3">
                <!-- Main Info -->
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2 mb-1">
                        <div :class="['rounded-lg p-1.5', statusConfig.bg]">
                            <Car class="h-4 w-4" :class="statusConfig.color.split(' ')[0].replace('bg-', 'text-').replace('100', '600')" />
                        </div>
                        <h3 class="font-semibold text-gray-900 truncate">{{ formState.requested_by || 'Unknown' }}</h3>
                    </div>
                    
                    <p class="text-sm text-gray-600 mb-2">{{ formState.vehicle_type }}</p>
                    
                    <div class="flex flex-wrap items-center gap-x-3 gap-y-1 text-xs text-gray-500">
                        <span class="flex items-center gap-1">
                            <Calendar class="h-3 w-3" />
                            {{ formatDate(formState.date_from) }} - {{ formatDate(formState.date_to) }}
                        </span>
                        <span class="flex items-center gap-1">
                            <Clock class="h-3 w-3" />
                            {{ formatTime(formState.time_from) }} - {{ formatTime(formState.time_to) }}
                        </span>
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
                    <div :class="['relative overflow-hidden px-6 py-5', statusConfig.bg]">
                        <div class="absolute -right-6 -top-6 opacity-10">
                            <Car class="h-24 w-24" />
                        </div>
                        
                        <div class="relative flex items-start justify-between">
                            <div>
                                <div class="flex items-center gap-2 mb-1">
                                    <span :class="[
                                        'inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-xs font-semibold border',
                                        statusConfig.color
                                    ]">
                                        <component :is="statusConfig.icon" class="h-3 w-3" />
                                        {{ formState.status }}
                                    </span>
                                    <span class="text-xs text-gray-500">Updated {{ formatDate(formState.updated_at) }}</span>
                                </div>
                                <h2 class="text-xl font-bold text-gray-900">Vehicle Rental Request</h2>
                            </div>
                            <button 
                                @click="showModal = false"
                                class="rounded-lg p-1 text-gray-400 hover:bg-gray-200/50 hover:text-gray-600 transition-colors"
                            >
                                <XCircle class="h-5 w-5" />
                            </button>
                        </div>
                    </div>

                    <!-- Body -->
                    <div class="px-6 py-5 space-y-5">
                        <!-- Requester Info -->
                        <div class="flex items-start gap-4">
                            <div class="rounded-xl bg-blue-50 p-2.5">
                                <User class="h-5 w-5 text-blue-600" />
                            </div>
                            <div class="flex-1">
                                <p class="text-xs font-semibold uppercase tracking-wider text-gray-500">Requested By</p>
                                <p class="text-lg font-semibold text-gray-900">{{ formState.requested_by || 'N/A' }}</p>
                                <p class="text-sm text-gray-600">{{ formState.vehicle_type }}</p>
                            </div>
                        </div>

                        <!-- Schedule -->
                        <div class="rounded-xl border border-gray-200 bg-gray-50/50 p-4">
                            <div class="grid gap-4 sm:grid-cols-2">
                                <div>
                                    <div class="flex items-center gap-2 text-xs font-medium text-gray-500 mb-1">
                                        <Calendar class="h-3.5 w-3.5" />
                                        Date Range
                                    </div>
                                    <p class="text-sm font-semibold text-gray-900">
                                        {{ formatDate(formState.date_from) }} - {{ formatDate(formState.date_to) }}
                                    </p>
                                </div>
                                <div>
                                    <div class="flex items-center gap-2 text-xs font-medium text-gray-500 mb-1">
                                        <Clock class="h-3.5 w-3.5" />
                                        Time
                                    </div>
                                    <p class="text-sm font-semibold text-gray-900">
                                        {{ formatTime(formState.time_from) || 'N/A' }} - {{ formatTime(formState.time_to) || 'N/A' }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Details Grid -->
                        <div class="grid gap-4 sm:grid-cols-2">
                            <div v-if="formState.purpose">
                                <div class="flex items-center gap-2 text-xs font-semibold text-gray-500 mb-1">
                                    <FileText class="h-3.5 w-3.5" />
                                    Purpose
                                </div>
                                <p class="text-sm text-gray-900">{{ formState.purpose }}</p>
                            </div>
                            <div v-if="formState.contact_number">
                                <div class="flex items-center gap-2 text-xs font-semibold text-gray-500 mb-1">
                                    <User class="h-3.5 w-3.5" />
                                    Contact
                                </div>
                                <p class="text-sm text-gray-900">{{ formState.contact_number }}</p>
                            </div>
                        </div>

                        <!-- Members -->
                        <div v-if="formState.members_of_party?.length">
                            <div class="flex items-center gap-2 text-xs font-semibold text-gray-500 mb-2">
                                <Users class="h-3.5 w-3.5" />
                                Members of Party ({{ formState.members_of_party.length }})
                            </div>
                            <div class="flex flex-wrap gap-2">
                                <span 
                                    v-for="(member, index) in formState.members_of_party" 
                                    :key="index"
                                    class="inline-flex items-center rounded-full bg-gray-100 px-3 py-1 text-xs font-medium text-gray-700"
                                >
                                    {{ member }}
                                </span>
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
                                class="w-full rounded-lg border-gray-300 text-sm focus:border-blue-500 focus:ring-blue-500"
                                placeholder="Add notes about this decision..."
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
                            <span v-if="formState.status === 'approved'">Approved</span>
                            <span v-else>Approve</span>
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