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
                <div class="shrink-0 rounded-xl border border-indigo-200 bg-indigo-100 p-3 shadow-sm dark:border-indigo-500/30 dark:bg-indigo-500/20">
                    <LuAward class="h-6 w-6 text-indigo-600 dark:text-indigo-400" />
                </div>
                <div>
                    <h2 class="text-xl font-black tracking-tight text-slate-900 sm:text-2xl dark:text-white">Certificate Generator</h2>
                    <p class="mt-0.5 text-sm font-medium text-slate-500 dark:text-slate-400">Bulk-generate and email certificates using PPTX templates</p>
                </div>
            </div>
        </template>

        <div class="space-y-6 px-4 py-8 sm:px-6 lg:px-8">
            <!-- Event Selection Card -->
            <div class="overflow-hidden rounded-2xl border border-slate-200/60 bg-white/80 shadow-sm backdrop-blur-xl transition-all duration-300 dark:border-slate-800 dark:bg-slate-900/80">
                <div class="border-b border-slate-100 bg-slate-50/50 px-6 py-5 dark:border-slate-800 dark:bg-slate-800/20">
                    <div class="flex items-center gap-2.5">
                        <LuCalendar class="h-5 w-5 text-indigo-600 dark:text-indigo-400" />
                        <h3 class="text-sm font-bold uppercase tracking-wide text-slate-900 dark:text-slate-100">Event Selection</h3>
                    </div>
                </div>

                <div class="p-6">
                    <div
                        v-if="!selectedEventId"
                        class="space-y-4">
                        <div>
                            <label class="mb-2.5 block text-sm font-bold text-slate-700 dark:text-slate-300">Event ID</label>
                            <div class="flex flex-col gap-3 sm:flex-row">
                                <div class="relative max-w-md flex-1">
                                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5">
                                        <LuHash class="h-5 w-5 text-slate-400 dark:text-slate-500" />
                                    </div>
                                    <input
                                        v-model="eventId"
                                        type="text"
                                        class="block w-full rounded-xl border border-slate-300 bg-slate-50 py-3 pl-11 pr-4 font-medium text-slate-900 placeholder-slate-400 shadow-sm transition-all focus:border-transparent focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:border-slate-700 dark:bg-slate-900/50 dark:text-white"
                                        placeholder="Enter event ID (e.g., 1234)"
                                        @keyup.enter="setEventId" />
                                </div>
                                <button
                                    type="button"
                                    class="inline-flex items-center justify-center gap-2 rounded-xl bg-indigo-600 px-7 py-3 font-bold text-white shadow-md shadow-indigo-600/20 transition-all duration-200 hover:bg-indigo-700 hover:shadow-indigo-600/30 disabled:cursor-not-allowed disabled:opacity-50"
                                    :disabled="isLoading || !eventId.trim()"
                                    @click="setEventId">
                                    <LuLoader2
                                        v-if="isLoading"
                                        class="h-4 w-4 animate-spin" />
                                    <LuArrowRight
                                        v-else
                                        class="h-4 w-4" />
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
                                class="flex items-center gap-2 rounded-xl border border-red-200 bg-red-50 p-3 text-sm font-semibold text-red-700 shadow-sm dark:border-red-500/30 dark:bg-red-500/10 dark:text-red-400">
                                <LuAlertCircle class="h-5 w-5 flex-shrink-0" />
                                {{ errorMessage }}
                            </div>
                        </Transition>
                    </div>

                    <!-- Selected Event State -->
                    <div
                        v-else
                        class="flex items-center justify-between rounded-2xl border border-indigo-100 bg-indigo-50/80 p-5 shadow-sm backdrop-blur-md dark:border-indigo-500/30 dark:bg-indigo-500/10">
                        <div class="flex items-center gap-4">
                            <div class="rounded-xl border border-indigo-200 bg-indigo-100 p-2.5 shadow-inner dark:border-indigo-500/30 dark:bg-indigo-500/20">
                                <LuCheckCircle class="h-6 w-6 text-indigo-600 dark:text-indigo-400" />
                            </div>
                            <div>
                                <p class="mb-0.5 text-[0.65rem] font-bold uppercase tracking-widest text-indigo-600/70 dark:text-indigo-400/80">Selected Event</p>
                                <p class="text-2xl font-black tracking-tight text-indigo-900 dark:text-indigo-300">#{{ selectedEventId }}</p>
                            </div>
                        </div>
                        <button
                            @click="clearSelection"
                            class="inline-flex items-center gap-1.5 rounded-xl px-4 py-2 text-sm font-bold text-slate-500 transition-all duration-200 hover:bg-red-100 hover:text-red-600 dark:text-slate-400 dark:hover:bg-red-500/20 dark:hover:text-red-400">
                            <LuX class="h-4 w-4" />
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
