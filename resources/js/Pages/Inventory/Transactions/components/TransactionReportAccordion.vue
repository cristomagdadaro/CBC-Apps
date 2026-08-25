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
    <section class="shadow-xs overflow-hidden rounded-2xl border border-slate-200 bg-white text-slate-900 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-100">
        <header class="flex items-center justify-between border-b border-slate-200 bg-slate-50 px-4 py-3 dark:border-slate-800 dark:bg-slate-800/80">
            <div class="flex items-center gap-2">
                <FileText class="h-4 w-4 text-slate-500 dark:text-slate-400" />
                <span class="text-xs font-bold uppercase tracking-wider text-slate-800 sm:text-sm dark:text-slate-200">
                    {{ title }}
                </span>
            </div>
            <div class="flex items-center gap-2">
                <span class="text-xs font-semibold text-slate-500 dark:text-slate-400">{{ reports.length }} linked</span>
                <button
                    class="inline-flex h-7 w-7 items-center justify-center rounded-lg border border-slate-300 bg-white text-slate-600 transition hover:bg-slate-100 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300 dark:hover:bg-slate-700"
                    type="button"
                    @click="togglePanel(openIndex === null ? 0 : null)"
                    :title="hasReports ? 'Toggle first report' : 'No reports'">
                    <ChevronDown
                        class="h-4 w-4 transition-transform duration-200"
                        :class="{ 'rotate-180': openIndex !== null }" />
                </button>
            </div>
        </header>

        <div
            v-if="!hasReports"
            class="p-4 text-center text-xs text-slate-500 dark:text-slate-400">
            No reports linked to this transaction yet.
        </div>
        <div
            v-else
            class="space-y-2 p-3">
            <div
                v-for="(report, index) in reports"
                :key="report.id || index"
                class="overflow-hidden rounded-xl border border-slate-200 bg-slate-50/50 dark:border-slate-800 dark:bg-slate-800/40">
                <button
                    type="button"
                    class="flex w-full items-center justify-between gap-2 px-3.5 py-2.5 text-left text-xs transition-colors hover:bg-slate-100/60 dark:hover:bg-slate-800/60"
                    @click="togglePanel(index)">
                    <div class="flex flex-col">
                        <span class="font-bold text-slate-900 dark:text-slate-100">
                            {{ report.report_type ? startCase(report.report_type) : "Unnamed Template" }}
                        </span>
                        <span class="text-[0.7rem] text-slate-500 dark:text-slate-400">
                            {{ formatDate(report.reported_at || report.created_at) }} ·
                            {{ report.user?.name || "Unknown user" }}
                        </span>
                    </div>
                    <ChevronDown
                        class="h-4 w-4 text-slate-400 transition-transform duration-200"
                        :class="{ 'rotate-180': openIndex === index }" />
                </button>
                <transition-container type="fade">
                    <div
                        v-if="openIndex === index"
                        class="space-y-3 border-t border-slate-200 bg-white px-3.5 py-3 text-xs text-slate-600 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-300">
                        <div class="grid gap-2 sm:grid-cols-2">
                            <div
                                v-for="meta in metadataRows(report)"
                                :key="meta.label + meta.value"
                                class="rounded-xl border border-slate-200 bg-slate-50 p-2 dark:border-slate-800 dark:bg-slate-800/60">
                                <p class="text-[0.65rem] font-semibold uppercase tracking-wider text-slate-400">
                                    {{ meta.label }}
                                </p>
                                <p class="mt-0.5 text-xs font-bold text-slate-800 dark:text-slate-200">
                                    {{ meta.value }}
                                </p>
                            </div>
                            <a
                                v-if="reportLink(report)"
                                :href="reportLink(report)"
                                target="_blank"
                                class="flex items-center justify-center rounded-xl border border-lime-600 p-2 text-center text-xs font-bold text-lime-600 transition-colors hover:bg-lime-600 hover:text-white dark:text-lime-400">
                                Open in Reports
                            </a>
                        </div>
                        <div
                            v-if="report.notes"
                            class="rounded-xl border border-amber-200 bg-amber-50 p-2.5 text-xs text-amber-900 dark:border-amber-900/60 dark:bg-amber-950/40 dark:text-amber-200">
                            <span class="font-bold">Notes:</span>
                            <p class="mt-1 whitespace-pre-line text-slate-700 dark:text-slate-300">
                                {{ report.notes }}
                            </p>
                        </div>
                        <div>
                            <p class="mb-1.5 text-[0.7rem] font-bold uppercase tracking-wider text-slate-400">Template Fields</p>
                            <dl class="grid gap-1.5 text-xs">
                                <div
                                    v-for="([entryKey, entryValue], idx) in fieldEntries(report)"
                                    :key="entryKey + idx"
                                    class="flex flex-col gap-1 rounded-xl border border-slate-200 bg-slate-50 p-2 sm:flex-row sm:items-center sm:justify-between dark:border-slate-800 dark:bg-slate-800/40">
                                    <dt class="font-bold text-slate-700 dark:text-slate-300">
                                        {{ startCase(entryKey) }}
                                    </dt>
                                    <dd class="font-medium text-slate-800 dark:text-slate-200">
                                        {{ normalizeFieldValue(entryValue) }}
                                    </dd>
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
