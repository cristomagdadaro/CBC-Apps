<script>
import EventCertificates from '@/Pages/Forms/components/EventCertificates.vue';

export default {
    name: 'CertificateGenerator',
    components: {
        EventCertificates,
    },
    data() {
        return {
            eventId: '',
            selectedEventId: '',
            errorMessage: '',
            isLoading: false,
        };
    },
    methods: {
        async setEventId() {
            const cleaned = String(this.eventId || '').trim();
            if (!cleaned) {
                this.errorMessage = 'Please provide an event ID.';
                return;
            }

            this.isLoading = true;
            this.errorMessage = '';
            
            // Simulate validation delay for better UX
            await new Promise(resolve => setTimeout(resolve, 300));
            
            this.selectedEventId = cleaned;
            this.isLoading = false;
        },
        clearSelection() {
            this.selectedEventId = '';
            this.eventId = '';
            this.errorMessage = '';
        },
    },
};
</script>

<template>
    <AppLayout title="Certificate Generator">
        <template #header>
            <div class="flex items-center gap-3">
                <div class="p-2 bg-white/10 rounded-lg">
                    <LuAward class="w-6 h-6 text-white" />
                </div>
                <div>
                    <h2 class="text-xl font-bold text-white">Certificate Generator</h2>
                    <p class="text-sm text-white/80 mt-0.5">Bulk-generate and email certificates using PPTX templates</p>
                </div>
            </div>
        </template>

        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">
            <!-- Event Selection Card -->
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-800/50">
                    <div class="flex items-center gap-2">
                        <LuCalendar class="w-5 h-5 text-blue-600 dark:text-blue-400" />
                        <h3 class="font-semibold text-gray-900 dark:text-white">Event Selection</h3>
                    </div>
                </div>
                
                <div class="p-6">
                    <div v-if="!selectedEventId" class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Event ID
                            </label>
                            <div class="flex flex-col sm:flex-row gap-3">
                                <div class="relative flex-1 max-w-md">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <LuHash class="w-5 h-5 text-gray-400" />
                                    </div>
                                    <input
                                        v-model="eventId"
                                        type="text"
                                        class="block w-full pl-10 pr-3 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl text-gray-900 dark:text-white bg-white dark:bg-gray-700 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                                        placeholder="Enter event ID (e.g., 1234)"
                                        @keyup.enter="setEventId"
                                    />
                                </div>
                                <button
                                    type="button"
                                    class="inline-flex items-center justify-center gap-2 px-6 py-2.5 bg-blue-600 hover:bg-blue-700 dark:bg-blue-600 dark:hover:bg-blue-700 text-white font-medium rounded-xl shadow-sm hover:shadow-md transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed"
                                    :disabled="isLoading || !eventId.trim()"
                                    @click="setEventId"
                                >
                                    <LuLoader2 v-if="isLoading" class="w-4 h-4 animate-spin" />
                                    <LuArrowRight v-else class="w-4 h-4" />
                                    {{ isLoading ? 'Loading...' : 'Load Event' }}
                                </button>
                            </div>
                            <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">
                                Enter the event ID to load associated templates and response data
                            </p>
                        </div>

                        <!-- Error Message -->
                        <Transition
                            enter-active-class="transition duration-200 ease-out"
                            enter-from-class="opacity-0 -translate-y-2"
                            enter-to-class="opacity-0 translate-y-0"
                            leave-active-class="transition duration-150 ease-in"
                            leave-from-class="opacity-100 translate-y-0"
                            leave-to-class="opacity-0 -translate-y-2">
                            <div v-if="errorMessage" class="flex items-center gap-2 p-3 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-xl text-red-700 dark:text-red-300 text-sm">
                                <LuAlertCircle class="w-5 h-5 flex-shrink-0" />
                                {{ errorMessage }}
                            </div>
                        </Transition>
                    </div>

                    <!-- Selected Event State -->
                    <div v-else class="flex items-center justify-between p-4 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-xl">
                        <div class="flex items-center gap-3">
                            <div class="p-2 bg-blue-100 dark:bg-blue-800 rounded-lg">
                                <LuCheckCircle class="w-5 h-5 text-blue-600 dark:text-blue-400" />
                            </div>
                            <div>
                                <p class="text-sm text-gray-600 dark:text-gray-400">Selected Event</p>
                                <p class="text-lg font-bold text-gray-900 dark:text-white">#{{ selectedEventId }}</p>
                            </div>
                        </div>
                        <button
                            @click="clearSelection"
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 text-sm font-medium text-gray-600 dark:text-gray-400 hover:text-red-600 dark:hover:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition-colors">
                            <LuX class="w-4 h-4" />
                            Change
                        </button>
                    </div>
                </div>
            </div>

            <!-- Certificate Generator Component -->
            <Transition
                enter-active-class="transition duration-300 ease-out"
                enter-from-class="opacity-0 translate-y-4"
                enter-to-class="opacity-100 translate-y-0"
                leave-active-class="transition duration-200 ease-in"
                leave-from-class="opacity-100 translate-y-0"
                leave-to-class="opacity-0 translate-y-4">
                <event-certificates v-if="selectedEventId" :event-id="selectedEventId" />
            </Transition>
        </div>
    </AppLayout>
</template>