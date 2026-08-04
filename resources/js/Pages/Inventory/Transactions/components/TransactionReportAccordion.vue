<script>
import { ChevronDown, FileText } from "lucide-vue-next";

export default {
    name: "TransactionReportAccordion",
    components: { ChevronDown, FileText },
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
    <section class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl overflow-hidden shadow-xs text-slate-900 dark:text-slate-100">
        <header class="flex items-center justify-between px-4 py-3 border-b border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-800/80">
            <div class="flex items-center gap-2">
                <FileText class="w-4 h-4 text-slate-500 dark:text-slate-400" />
                <span class="text-xs sm:text-sm font-bold uppercase tracking-wider text-slate-800 dark:text-slate-200">{{ title }}</span>
            </div>
            <div class="flex items-center gap-2">
                <span class="text-xs font-semibold text-slate-500 dark:text-slate-400">{{ reports.length }} linked</span>
                <button
                    class="inline-flex items-center justify-center h-7 w-7 rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 transition"
                    type="button"
                    @click="togglePanel(openIndex === null ? 0 : null)"
                    :title="hasReports ? 'Toggle first report' : 'No reports'"
                >
                    <ChevronDown class="h-4 w-4 transition-transform duration-200" :class="{'rotate-180': openIndex !== null}" />
                </button>
            </div>
        </header>

        <div v-if="!hasReports" class="p-4 text-xs text-slate-500 dark:text-slate-400 text-center">
            No reports linked to this transaction yet.
        </div>
        <div v-else class="p-3 space-y-2">
            <div
                v-for="(report, index) in reports"
                :key="report.id || index"
                class="border border-slate-200 dark:border-slate-800 rounded-xl overflow-hidden bg-slate-50/50 dark:bg-slate-800/40"
            >
                <button
                    type="button"
                    class="w-full flex items-center justify-between gap-2 px-3.5 py-2.5 text-left text-xs hover:bg-slate-100/60 dark:hover:bg-slate-800/60 transition-colors"
                    @click="togglePanel(index)"
                >
                    <div class="flex flex-col">
                        <span class="font-bold text-slate-900 dark:text-slate-100">
                            {{ report.report_type ? startCase(report.report_type) : 'Unnamed Template' }}
                        </span>
                        <span class="text-[0.7rem] text-slate-500 dark:text-slate-400">
                            {{ formatDate(report.reported_at || report.created_at) }} · {{ report.user?.name || 'Unknown user' }}
                        </span>
                    </div>
                    <ChevronDown class="h-4 w-4 text-slate-400 transition-transform duration-200" :class="{'rotate-180': openIndex === index}" />
                </button>
                <transition-container type="fade">
                    <div v-if="openIndex === index" class="border-t border-slate-200 dark:border-slate-800 px-3.5 py-3 text-xs text-slate-600 dark:text-slate-300 space-y-3 bg-white dark:bg-slate-900">
                        <div class="grid gap-2 sm:grid-cols-2">
                            <div
                                v-for="meta in metadataRows(report)"
                                :key="meta.label + meta.value"
                                class="border border-slate-200 dark:border-slate-800 rounded-xl p-2 bg-slate-50 dark:bg-slate-800/60"
                            >
                                <p class="uppercase text-[0.65rem] font-semibold text-slate-400 tracking-wider">{{ meta.label }}</p>
                                <p class="font-bold text-slate-800 dark:text-slate-200 text-xs mt-0.5">{{ meta.value }}</p>
                            </div>
                            <a
                                v-if="reportLink(report)"
                                :href="reportLink(report)"
                                target="_blank"
                                class="border border-lime-600 text-lime-600 dark:text-lime-400 rounded-xl p-2 text-center text-xs font-bold hover:bg-lime-600 hover:text-white transition-colors flex items-center justify-center"
                            >
                                Open in Reports
                            </a>
                        </div>
                        <div v-if="report.notes" class="bg-amber-50 dark:bg-amber-950/40 border border-amber-200 dark:border-amber-900/60 rounded-xl p-2.5 text-xs text-amber-900 dark:text-amber-200">
                            <span class="font-bold">Notes:</span>
                            <p class="mt-1 whitespace-pre-line text-slate-700 dark:text-slate-300">{{ report.notes }}</p>
                        </div>
                        <div>
                            <p class="text-[0.7rem] font-bold uppercase tracking-wider text-slate-400 mb-1.5">Template Fields</p>
                            <dl class="grid gap-1.5 text-xs">
                                <div v-for="([entryKey, entryValue], idx) in fieldEntries(report)" :key="entryKey + idx" class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-1 border border-slate-200 dark:border-slate-800 rounded-xl p-2 bg-slate-50 dark:bg-slate-800/40">
                                    <dt class="font-bold text-slate-700 dark:text-slate-300">{{ startCase(entryKey) }}</dt>
                                    <dd class="text-slate-800 dark:text-slate-200 font-medium">{{ normalizeFieldValue(entryValue) }}</dd>
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
