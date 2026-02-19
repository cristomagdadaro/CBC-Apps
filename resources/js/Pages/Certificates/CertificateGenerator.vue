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
        };
    },
    methods: {
        setEventId() {
            const cleaned = String(this.eventId || '').trim();
            if (!cleaned) {
                this.errorMessage = 'Please provide an event ID.';
                return;
            }

            this.selectedEventId = cleaned;
            this.errorMessage = '';
        },
    },
};
</script>

<template>
    <AppLayout title="Certificate Generator">
        <template #header>
            <div class="py-3">
                <h2 class="text-lg font-semibold text-white">Certificate Generator</h2>
                <p class="text-xs text-white/90 mt-1">Bulk-generate and email certificates using PPTX template and XLSX data.</p>
            </div>
        </template>

        <div class="default-container pt-6 space-y-5">
            <div class="bg-white dark:bg-gray-800 shadow sm:rounded-lg p-4">
                <label class="text-sm font-medium text-gray-700 dark:text-gray-200">Event ID</label>
                <div class="mt-2 flex flex-wrap gap-2 items-center">
                    <input
                        v-model="eventId"
                        type="text"
                        class="border border-gray-300 rounded px-3 py-2 w-full md:w-64"
                        placeholder="e.g. 1234"
                    />
                    <button
                        type="button"
                        class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded"
                        @click="setEventId"
                    >
                        Use Event
                    </button>
                </div>
                <p v-if="errorMessage" class="text-red-600 text-sm mt-2">{{ errorMessage }}</p>
            </div>

            <event-certificates v-if="selectedEventId" :event-id="selectedEventId" />
        </div>
    </AppLayout>
</template>
