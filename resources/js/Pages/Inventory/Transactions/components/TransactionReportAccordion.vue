<script>
export default {
    name: "TransactionReportAccordion",
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
        metadataRows(report) {
            if (!report) return [];
            const rows = [
                { label: "Transaction", value: report.transaction_id },
                { label: "Linked Barcode", value: report.transaction?.barcode },
                { label: "Item", value: report.item?.name ?? report.item?.fullName },
                { label: "Created By", value: report.user?.name },
            ];
            return rows.filter((row) => row.value);
        },
        reportLink(report) {
            if (!report?.transaction_id || typeof route !== "function") return null;
            return route("suppEquipReports.index", { search: report.transaction_id });
        },
    },
};
</script>

<template>
    <section class="bg-white dark:bg-gray-900 border border-gray-200 rounded-lg">
        <header class="flex items-center justify-between px-3 py-2 border-b border-gray-100 text-xs uppercase tracking-wide text-gray-500">
            <span>{{ title }}</span>
            <div class="flex items-center gap-2">
                <span class="text-[10px] text-gray-400">{{ reports.length }} linked</span>
                <button
                    class="inline-flex items-center justify-center h-8 w-8 rounded-full border border-gray-300 text-gray-600 hover:border-AB hover:text-AB transition"
                    type="button"
                    @click="togglePanel(openIndex === null ? 0 : null)"
                    :title="hasReports ? 'Toggle first report' : 'No reports'"
                >
                        <caret-down class="h-4 w-4" :class="{'rotate-180': openIndex !== null}" />
                </button>
            </div>
        </header>

        <div v-if="!hasReports" class="p-3 text-xs text-gray-500">
            No reports linked to this transaction yet.
        </div>
        <div v-else class="px-3 py-2 space-y-2">
            <div
                v-for="(report, index) in reports"
                :key="report.id || index"
                class="border border-gray-100 rounded-md"
            >
                <button
                    type="button"
                    class="w-full flex items-center justify-between gap-2 px-3 py-2 text-left text-xs"
                    @click="togglePanel(index)"
                >
                    <div class="flex flex-col">
                        <span class="font-semibold text-gray-700">
                            {{ report.report_type ? startCase(report.report_type) : 'Unnamed Template' }}
                        </span>
                        <span class="text-[10px] text-gray-500">
                            {{ formatDate(report.reported_at || report.created_at) }} · {{ report.user?.name || 'Unknown user' }}
                        </span>
                    </div>
                    <caret-down class="h-4 w-4 text-gray-400 transition-transform" :class="{'rotate-180': openIndex === index}" />
                </button>
                <transition-container type="fade">
                    <div v-if="openIndex === index" class="border-t border-gray-100 px-3 py-2 text-xs text-gray-600 space-y-2">
                        <div class="grid gap-2 sm:grid-cols-2">
                            <div
                                v-for="meta in metadataRows(report)"
                                :key="meta.label + meta.value"
                                class="border border-gray-100 rounded px-2 py-1 bg-gray-50"
                            >
                                <p class="uppercase text-[9px] text-gray-400 tracking-wide">{{ meta.label }}</p>
                                <p class="font-semibold text-gray-700 text-[11px]">{{ meta.value }}</p>
                            </div>
                            <a
                                v-if="reportLink(report)"
                                :href="reportLink(report)"
                                target="_blank"
                                class="border border-AB text-AB rounded px-2 py-1 text-center text-[11px] font-semibold hover:bg-AB hover:text-white transition-colors"
                            >
                                Open in Reports
                            </a>
                        </div>
                        <div v-if="report.notes" class="bg-gray-50 border border-gray-100 rounded px-2 py-1 text-[11px] text-gray-600">
                            <span class="font-semibold text-gray-700">Notes:</span>
                            <p class="mt-1 whitespace-pre-line">{{ report.notes }}</p>
                        </div>
                        <div>
                            <p class="text-[10px] font-semibold text-gray-500 mb-1">Template Fields</p>
                            <dl class="grid gap-1 text-[11px]">
                                <div v-for="([entryKey, entryValue], idx) in fieldEntries(report)" :key="entryKey + idx" class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-1 border border-gray-100 rounded px-2 py-1 bg-gray-50">
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
