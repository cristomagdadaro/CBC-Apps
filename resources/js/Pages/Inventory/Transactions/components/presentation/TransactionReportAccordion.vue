<script>
import TransitionContainer from "@/Components/Transitions/TransitionContrainer.vue";

export default {
    name: "TransactionReportAccordion",
    components: { TransitionContainer },
    props: {
        reports: {
            type: Array,
            default: () => [],
        },
        title: {
            type: String,
            default: "Attached Reports",
        },
    },
    data() {
        return {
            openIndex: null,
        };
    },
    computed: {
        hasReports() {
            return Array.isArray(this.reports) && this.reports.length > 0;
        },
    },
    methods: {
        togglePanel(index) {
            this.openIndex = this.openIndex === index ? null : index;
        },
        formatDate(value) {
            if (!value) return "Not specified";
            const date = new Date(value);
            if (Number.isNaN(date.getTime())) return value;
            return date.toLocaleString("en-US", {
                month: "short",
                day: "numeric",
                year: "numeric",
                hour: "numeric",
                minute: "2-digit",
            });
        },
        startCase(value) {
            if (!value) return "";
            return value
                .toString()
                .split("_")
                .map((segment) => segment.charAt(0).toUpperCase() + segment.slice(1))
                .join(" ");
        },
        normalizeFieldValue(value) {
            if (value === null || typeof value === "undefined") return "—";
            if (Array.isArray(value)) return value.join(", ");
            if (typeof value === "object") return JSON.stringify(value);
            return value;
        },
        fieldEntries(report) {
            if (!report || !report.report_data) return [];
            return Object.entries(report.report_data);
        },
    },
};
</script>

<template>
    <section class="bg-white dark:bg-gray-900 shadow rounded-lg border border-gray-100">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 p-4 sm:p-5 border-b border-gray-100">
            <div>
                <h3 class="text-base font-semibold text-gray-800">{{ title }}</h3>
                <p class="text-xs text-gray-500">Reports linked to this transaction.</p>
            </div>
            <p class="text-sm text-gray-500">Records: <span class="font-semibold text-gray-700">{{ reports.length }}</span></p>
        </div>

        <div v-if="!hasReports" class="p-4 text-sm text-gray-500 bg-gray-50">
            No reports linked to this transaction yet.
        </div>

        <div v-else class="p-4 sm:p-5 space-y-3">
            <div
                v-for="(report, index) in reports"
                :key="report.id || index"
                class="border border-gray-200 rounded-lg"
            >
                <button
                    type="button"
                    class="w-full flex items-center justify-between gap-4 px-4 py-3 text-left"
                    @click="togglePanel(index)"
                >
                    <div class="flex flex-col">
                        <span class="text-sm font-semibold text-gray-800">
                            {{ report.report_type ? startCase(report.report_type) : 'Unnamed Template' }}
                        </span>
                        <span class="text-xs text-gray-500">
                            {{ formatDate(report.reported_at || report.created_at) }} · {{ report.user?.name || 'Unknown user' }}
                        </span>
                    </div>
                    <span class="text-gray-500 transition-transform" :class="{'rotate-180': openIndex === index}">
                        ^
                    </span>
                </button>
                <transition-container type="fade">
                    <div v-if="openIndex === index" class="border-t border-gray-100 px-4 py-3 text-sm text-gray-700 space-y-3">
                        <div v-if="report.notes" class="bg-gray-50 border border-gray-100 rounded px-3 py-2 text-xs text-gray-600">
                            <span class="font-semibold text-gray-700">Notes:</span>
                            <p class="mt-1 whitespace-pre-line">{{ report.notes }}</p>
                        </div>
                        <div>
                            <p class="text-xs font-semibold text-gray-500 mb-2">Template Fields</p>
                            <dl class="grid gap-2 text-xs">
                                <div v-for="([entryKey, entryValue], idx) in fieldEntries(report)" :key="entryKey + idx" class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-1 border border-gray-100 rounded px-3 py-2 bg-gray-50">
                                    <dt class="font-semibold text-gray-600">{{ startCase(entryKey) }}</dt>
                                    <dd class="text-gray-700">{{ normalizeFieldValue(entryValue) }}</dd>
                                </div>
                            </dl>
                        </div>
                    </div>
                </transition-container>
            </div>
        </div>
    </section>
</template>

<style scoped>
.rotate-180 {
    transform: rotate(180deg);
}
</style>
