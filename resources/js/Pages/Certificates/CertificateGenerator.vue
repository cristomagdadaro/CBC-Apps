<script>
import EventCertificates from "@/Pages/Forms/components/EventCertificates.vue";

export default {
    name: "CertificateGenerator",
    components: {
        EventCertificates,
    },
    data() {
        return {
            eventId: "",
            selectedEventId: "",
            errorMessage: "",
            isLoading: false,
        };
    },
    methods: {
        async setEventId() {
            const cleaned = String(this.eventId || "").trim();
            if (!cleaned) {
                this.errorMessage = "Please provide an event ID.";
                return;
            }

            this.isLoading = true;
            this.errorMessage = "";

            // Simulate validation delay for better UX
            await new Promise((resolve) => setTimeout(resolve, 300));

            this.selectedEventId = cleaned;
            this.isLoading = false;
        },
        clearSelection() {
            this.selectedEventId = "";
            this.eventId = "";
            this.errorMessage = "";
        },
    },
};
</script>

<template>
    <AppLayout title="Certificate Generator">
        <template #header>
            <div class="flex items-center gap-4 py-2 sm:py-3">
                <div class="p-3 bg-indigo-100 dark:bg-indigo-500/20 rounded-xl border border-indigo-200 dark:border-indigo-500/30 shadow-sm shrink-0">
                    <LuAward class="w-6 h-6 text-indigo-600 dark:text-indigo-400" />
                </div>
                <div>
                    <h2 class="text-xl sm:text-2xl font-black text-slate-900 dark:text-white tracking-tight">Certificate Generator</h2>
                    <p class="text-sm font-medium text-slate-500 dark:text-slate-400 mt-0.5">Bulk-generate and email certificates using PPTX templates</p>
                </div>
            </div>
        </template>

        <div class="px-4 sm:px-6 lg:px-8 py-8 space-y-6">
            <!-- Event Selection Card -->
            <div class="bg-white/80 dark:bg-slate-900/80 backdrop-blur-xl rounded-2xl shadow-sm border border-slate-200/60 dark:border-slate-800 overflow-hidden transition-all duration-300">
                <div class="px-6 py-5 border-b border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/20">
                    <div class="flex items-center gap-2.5">
                        <LuCalendar class="w-5 h-5 text-indigo-600 dark:text-indigo-400" />
                        <h3 class="font-bold text-slate-900 dark:text-slate-100 tracking-wide uppercase text-sm">Event Selection</h3>
                    </div>
                </div>

                <div class="p-6">
                    <div
                        v-if="!selectedEventId"
                        class="space-y-4">
                        <div>
                            <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2.5">Event ID</label>
                            <div class="flex flex-col sm:flex-row gap-3">
                                <div class="relative flex-1 max-w-md">
                                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                                        <LuHash class="w-5 h-5 text-slate-400 dark:text-slate-500" />
                                    </div>
                                    <input
                                        v-model="eventId"
                                        type="text"
                                        class="block w-full pl-11 pr-4 py-3 border border-slate-300 dark:border-slate-700 rounded-xl text-slate-900 dark:text-white bg-slate-50 dark:bg-slate-900/50 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all font-medium shadow-sm"
                                        placeholder="Enter event ID (e.g., 1234)"
                                        @keyup.enter="setEventId" />
                                </div>
                                <button
                                    type="button"
                                    class="inline-flex items-center justify-center gap-2 px-7 py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl shadow-md shadow-indigo-600/20 hover:shadow-indigo-600/30 transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed"
                                    :disabled="isLoading || !eventId.trim()"
                                    @click="setEventId">
                                    <LuLoader2
                                        v-if="isLoading"
                                        class="w-4 h-4 animate-spin" />
                                    <LuArrowRight
                                        v-else
                                        class="w-4 h-4" />
                                    {{ isLoading ? "Loading..." : "Load Event" }}
                                </button>
                            </div>
                            <p class="mt-2.5 text-xs font-medium text-slate-500 dark:text-slate-400">Enter the event ID to load associated templates and response data</p>
                        </div>

                        <!-- Error Message -->
                        <Transition
                            enter-active-class="transition duration-200 ease-out"
                            enter-from-class="opacity-0 -translate-y-2"
                            enter-to-class="opacity-100 translate-y-0"
                            leave-active-class="transition duration-150 ease-in"
                            leave-from-class="opacity-100 translate-y-0"
                            leave-to-class="opacity-0 -translate-y-2">
                            <div
                                v-if="errorMessage"
                                class="flex items-center gap-2 p-3 bg-red-50 dark:bg-red-500/10 border border-red-200 dark:border-red-500/30 rounded-xl text-red-700 dark:text-red-400 text-sm font-semibold shadow-sm">
                                <LuAlertCircle class="w-5 h-5 flex-shrink-0" />
                                {{ errorMessage }}
                            </div>
                        </Transition>
                    </div>

                    <!-- Selected Event State -->
                    <div
                        v-else
                        class="flex items-center justify-between p-5 bg-indigo-50/80 dark:bg-indigo-500/10 border border-indigo-100 dark:border-indigo-500/30 rounded-2xl shadow-sm backdrop-blur-md">
                        <div class="flex items-center gap-4">
                            <div class="p-2.5 bg-indigo-100 dark:bg-indigo-500/20 rounded-xl shadow-inner border border-indigo-200 dark:border-indigo-500/30">
                                <LuCheckCircle class="w-6 h-6 text-indigo-600 dark:text-indigo-400" />
                            </div>
                            <div>
                                <p class="text-[0.65rem] font-bold uppercase tracking-widest text-indigo-600/70 dark:text-indigo-400/80 mb-0.5">Selected Event</p>
                                <p class="text-2xl font-black text-indigo-900 dark:text-indigo-300 tracking-tight">#{{ selectedEventId }}</p>
                            </div>
                        </div>
                        <button
                            @click="clearSelection"
                            class="inline-flex items-center gap-1.5 px-4 py-2 text-sm font-bold text-slate-500 dark:text-slate-400 hover:text-red-600 dark:hover:text-red-400 hover:bg-red-100 dark:hover:bg-red-500/20 rounded-xl transition-all duration-200">
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
                <event-certificates
                    v-if="selectedEventId"
                    :event-id="selectedEventId" />
            </Transition>
        </div>
    </AppLayout>
</template>
