<script>
import { Head, Link } from "@inertiajs/vue3";
import ActionHeaderLayout from "@/Layouts/ActionHeaderLayout.vue";
import AppLayout from "@/Layouts/AppLayout.vue";
import QrBarCode from "@/Components/QrBarCode.vue";

export default {
    name: "ResearchSamplePassport",
    components: {
        ActionHeaderLayout,
        AppLayout,
        Head,
        Link,
        QrBarCode,
    },
    props: {
        sample: { type: Object, required: true },
    },
    computed: {
        monitoringRecords() {
            return Array.isArray(this.sample?.monitoring_records) ? this.sample.monitoring_records : [];
        },
        inventoryLogs() {
            return Array.isArray(this.sample?.inventory_logs) ? this.sample.inventory_logs : [];
        },
        metadataEntries() {
            const metadata = this.sample?.metadata;
            if (!metadata || typeof metadata !== "object") {
                return [];
            }
            return Object.entries(metadata);
        },
        passportLink() {
            return route("research.samples.show", this.sample.uid);
        },
        apiLink() {
            return route("api.research.samples.show", this.sample.uid);
        },
        statusColor() {
            const status = this.sample.current_status?.toLowerCase() || "";
            if (["active", "available", "in stock"].includes(status)) return "bg-emerald-50 text-emerald-700 border-emerald-200";
            if (["depleted", "consumed", "destroyed"].includes(status)) return "bg-red-50 text-red-700 border-red-200";
            if (["reserved", "pending"].includes(status)) return "bg-amber-50 text-amber-700 border-amber-200";
            return "bg-slate-50 text-slate-700 border-slate-200";
        },
        experimentPath() {
            const parts = [];
            if (this.sample.experiment?.study?.project?.title) parts.push(this.sample.experiment.study.project.title);
            if (this.sample.experiment?.study?.title) parts.push(this.sample.experiment.study.title);
            if (this.sample.experiment?.title) parts.push(this.sample.experiment.title);
            return parts;
        },
    },
    methods: {
        formatDate(value) {
            if (!value) return "-";
            const date = new Date(value);
            if (Number.isNaN(date.getTime())) return value;
            return date.toLocaleDateString("en-US", {
                year: "numeric",
                month: "short",
                day: "numeric",
            });
        },
        formatDateTime(value) {
            if (!value) return "-";
            const date = new Date(value);
            if (Number.isNaN(date.getTime())) return value;
            return date.toLocaleDateString("en-US", {
                year: "numeric",
                month: "short",
                day: "numeric",
                hour: "2-digit",
                minute: "2-digit",
            });
        },
        toDisplay(value) {
            if (value === null || value === undefined || value === "") return "-";
            if (typeof value === "boolean") return value ? "Yes" : "No";
            if (typeof value === "object") return JSON.stringify(value);
            return String(value);
        },
        copyToClipboard(text) {
            navigator.clipboard.writeText(text);
            // Could add toast notification here
        },
    },
};
</script>

<template>
    <Head :title="`Sample Passport ${sample.uid}`" />

    <AppLayout :title="`Sample Passport ${sample.uid}`">
        <template #header>
            <ActionHeaderLayout
                :route-link="route('research.samples.inventory')"
                :subtitle="sample.accession_name || sample.uid"
                title="Research Sample Passport">
                <Link
                    :href="route('research.samples.inventory')"
                    class="rounded-lg border border-white/25 px-4 py-2 text-sm font-medium text-white hover:bg-white/10 transition-colors">
                    ← Inventory
                </Link>
            </ActionHeaderLayout>
        </template>

        <div class="max-w-7xl mx-auto p-6 space-y-6">
            <!-- Identity Card -->
            <section class="rounded-2xl border border-gray-200 bg-white shadow-sm overflow-hidden">
                <div class="border-b border-gray-100 bg-gray-50/50 px-6 py-4 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="h-8 w-1 bg-emerald-500 rounded-full"></div>
                        <h2 class="text-lg font-semibold text-gray-900">Sample Identity</h2>
                    </div>
                    <span
                        class="inline-flex items-center rounded-full border px-3 py-1 text-xs font-medium"
                        :class="statusColor">
                        {{ sample.current_status || "Unknown" }}
                    </span>
                </div>

                <div class="p-6">
                    <div class="grid gap-6 lg:grid-cols-3">
                        <!-- Main Info -->
                        <div class="lg:col-span-2 space-y-4">
                            <div>
                                <h1 class="text-2xl font-bold text-gray-900 tracking-tight">
                                    {{ sample.accession_name || "Unnamed Sample" }}
                                </h1>
                                <p class="mt-1 text-sm text-gray-500 font-mono">{{ sample.uid }}</p>
                            </div>

                            <div class="grid gap-4 sm:grid-cols-2">
                                <div class="space-y-3">
                                    <div class="group">
                                        <label class="text-xs font-medium text-gray-400 uppercase tracking-wider">Sample Type</label>
                                        <p class="mt-0.5 text-sm font-medium text-gray-900">
                                            {{ sample.sample_type || "-" }}
                                        </p>
                                    </div>
                                    <div class="group">
                                        <label class="text-xs font-medium text-gray-400 uppercase tracking-wider">Commodity</label>
                                        <p class="mt-0.5 text-sm font-medium text-gray-900">
                                            {{ sample.commodity || "-" }}
                                        </p>
                                    </div>
                                    <div class="group">
                                        <label class="text-xs font-medium text-gray-400 uppercase tracking-wider">Current Location</label>
                                        <p class="mt-0.5 text-sm font-medium text-gray-900">
                                            {{ sample.current_location || "-" }}
                                        </p>
                                    </div>
                                </div>
                                <div class="space-y-3">
                                    <div class="group">
                                        <label class="text-xs font-medium text-gray-400 uppercase tracking-wider">Storage Location</label>
                                        <p class="mt-0.5 text-sm font-medium text-gray-900">
                                            {{ sample.storage_location || "-" }}
                                        </p>
                                    </div>
                                    <div class="group">
                                        <label class="text-xs font-medium text-gray-400 uppercase tracking-wider">Legacy Reference</label>
                                        <p class="mt-0.5 text-sm font-medium text-gray-900">
                                            {{ sample.legacy_reference || "-" }}
                                        </p>
                                    </div>
                                    <div class="group">
                                        <label class="text-xs font-medium text-gray-400 uppercase tracking-wider">Priority</label>
                                        <p class="mt-0.5 text-sm font-medium text-gray-900">
                                            <span
                                                v-if="sample.is_priority"
                                                class="inline-flex items-center gap-1 text-amber-600">
                                                <svg
                                                    class="w-4 h-4"
                                                    fill="currentColor"
                                                    viewBox="0 0 20 20">
                                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                                </svg>
                                                Priority Sample
                                            </span>
                                            <span
                                                v-else
                                                class="text-gray-500">
                                                Standard
                                            </span>
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- QR / Barcode -->
                        <div class="flex flex-col items-center justify-center rounded-xl border border-gray-100 bg-gray-50/50 p-5 space-y-3">
                            <QrBarCode
                                mode="both"
                                :barcode-value="sample.uid"
                                :qr-value="passportLink"
                                :qr-caption="sample.uid"
                                :qr-size="96"
                                :barcode-height="40"
                                :font-size="10"
                                container-class="bg-white rounded-lg border border-gray-200 p-4 shadow-sm" />
                            <div class="flex flex-col items-center gap-2 w-full">
                                <button
                                    @click="copyToClipboard(sample.uid)"
                                    class="text-xs text-gray-500 hover:text-gray-900 transition-colors flex items-center gap-1"
                                    title="Copy UID">
                                    <svg
                                        class="w-3 h-3"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                    </svg>
                                    Copy UID
                                </button>
                                <a
                                    :href="apiLink"
                                    class="inline-flex items-center gap-1 text-xs text-blue-600 hover:text-blue-800 font-medium transition-colors"
                                    target="_blank"
                                    rel="noopener noreferrer">
                                    <svg
                                        class="w-3 h-3"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4" />
                                    </svg>
                                    API Payload
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Experiment Context -->
            <section class="rounded-2xl border border-gray-200 bg-white shadow-sm overflow-hidden">
                <div class="border-b border-gray-100 bg-gray-50/50 px-6 py-4 flex items-center gap-3">
                    <div class="h-8 w-1 bg-blue-500 rounded-full"></div>
                    <h3 class="text-lg font-semibold text-gray-900">Experiment Context</h3>
                </div>

                <div class="p-6">
                    <!-- Breadcrumb Path -->
                    <div
                        v-if="experimentPath.length"
                        class="mb-5 flex items-center gap-2 text-sm flex-wrap">
                        <template
                            v-for="(part, index) in experimentPath"
                            :key="index">
                            <span
                                v-if="index > 0"
                                class="text-gray-300">
                                /
                            </span>
                            <span
                                class="rounded-md bg-blue-50 px-2.5 py-1 text-xs font-medium text-blue-700"
                                :class="{
                                    'ring-1 ring-blue-200': index === experimentPath.length - 1,
                                }">
                                {{ part }}
                            </span>
                        </template>
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                        <div class="rounded-lg border border-gray-100 bg-gray-50/30 p-4">
                            <label class="text-xs font-medium text-gray-400 uppercase tracking-wider">Project</label>
                            <p class="mt-1 text-sm font-semibold text-gray-900">
                                {{ sample.experiment?.study?.project?.title || "-" }}
                            </p>
                        </div>
                        <div class="rounded-lg border border-gray-100 bg-gray-50/30 p-4">
                            <label class="text-xs font-medium text-gray-400 uppercase tracking-wider">Study</label>
                            <p class="mt-1 text-sm font-semibold text-gray-900">
                                {{ sample.experiment?.study?.title || "-" }}
                            </p>
                        </div>
                        <div class="rounded-lg border border-gray-100 bg-gray-50/30 p-4">
                            <label class="text-xs font-medium text-gray-400 uppercase tracking-wider">Experiment</label>
                            <p class="mt-1 text-sm font-semibold text-gray-900">
                                {{ sample.experiment?.title || "-" }}
                            </p>
                        </div>
                        <div class="rounded-lg border border-gray-100 bg-gray-50/30 p-4">
                            <label class="text-xs font-medium text-gray-400 uppercase tracking-wider">Generation</label>
                            <p class="mt-1 text-sm font-semibold text-gray-900">
                                {{ sample.generation || "-" }}
                            </p>
                        </div>
                        <div class="rounded-lg border border-gray-100 bg-gray-50/30 p-4">
                            <label class="text-xs font-medium text-gray-400 uppercase tracking-wider">PR Code</label>
                            <p class="mt-1 text-sm font-mono text-gray-900">
                                {{ sample.pr_code || "-" }}
                            </p>
                        </div>
                        <div class="rounded-lg border border-gray-100 bg-gray-50/30 p-4">
                            <label class="text-xs font-medium text-gray-400 uppercase tracking-wider">Line Label</label>
                            <p class="mt-1 text-sm font-semibold text-gray-900">
                                {{ sample.line_label || "-" }}
                            </p>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Lifecycle Details -->
            <section class="rounded-2xl border border-gray-200 bg-white shadow-sm overflow-hidden">
                <div class="border-b border-gray-100 bg-gray-50/50 px-6 py-4 flex items-center gap-3">
                    <div class="h-8 w-1 bg-purple-500 rounded-full"></div>
                    <h3 class="text-lg font-semibold text-gray-900">Lifecycle & Field Data</h3>
                </div>

                <div class="p-6">
                    <div class="grid gap-6 lg:grid-cols-2">
                        <!-- Dates -->
                        <div class="space-y-4">
                            <h4 class="text-sm font-semibold text-gray-900 flex items-center gap-2">
                                <svg
                                    class="w-4 h-4 text-gray-400"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                                Key Dates
                            </h4>
                            <div class="space-y-3">
                                <div class="flex items-center justify-between py-2 border-b border-gray-50">
                                    <span class="text-sm text-gray-500">Germination</span>
                                    <span class="text-sm font-medium text-gray-900">
                                        {{ formatDate(sample.germination_date) }}
                                    </span>
                                </div>
                                <div class="flex items-center justify-between py-2 border-b border-gray-50">
                                    <span class="text-sm text-gray-500">Sowing</span>
                                    <span class="text-sm font-medium text-gray-900">
                                        {{ formatDate(sample.sowing_date) }}
                                    </span>
                                </div>
                                <div class="flex items-center justify-between py-2 border-b border-gray-50">
                                    <span class="text-sm text-gray-500">Harvest</span>
                                    <span class="text-sm font-medium text-gray-900">
                                        {{ formatDate(sample.harvest_date) }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Field Data -->
                        <div class="space-y-4">
                            <h4 class="text-sm font-semibold text-gray-900 flex items-center gap-2">
                                <svg
                                    class="w-4 h-4 text-gray-400"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                </svg>
                                Field Information
                            </h4>
                            <div class="grid grid-cols-2 gap-3">
                                <div class="rounded-lg bg-gray-50 p-3">
                                    <label class="text-xs text-gray-400 uppercase">Plot</label>
                                    <p class="text-lg font-semibold text-gray-900">
                                        {{ sample.plot_number || "-" }}
                                    </p>
                                </div>
                                <div class="rounded-lg bg-gray-50 p-3">
                                    <label class="text-xs text-gray-400 uppercase">Field</label>
                                    <p class="text-lg font-semibold text-gray-900">
                                        {{ sample.field_number || "-" }}
                                    </p>
                                </div>
                                <div class="rounded-lg bg-gray-50 p-3">
                                    <label class="text-xs text-gray-400 uppercase">Replication</label>
                                    <p class="text-lg font-semibold text-gray-900">
                                        {{ sample.replication_number || "-" }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Audit Trail -->
                    <div class="mt-6 pt-6 border-t border-gray-100">
                        <div class="flex items-center gap-6 text-xs text-gray-500">
                            <div class="flex items-center gap-1.5">
                                <svg
                                    class="w-3.5 h-3.5"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                                Created by
                                <span class="font-medium text-gray-700">
                                    {{ sample.creator?.name || "-" }}
                                </span>
                            </div>
                            <div class="flex items-center gap-1.5">
                                <svg
                                    class="w-3.5 h-3.5"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                </svg>
                                Updated by
                                <span class="font-medium text-gray-700">
                                    {{ sample.updater?.name || "-" }}
                                </span>
                            </div>
                            <div class="ml-auto text-gray-400">
                                {{ formatDateTime(sample.created_at) }} →
                                {{ formatDateTime(sample.updated_at) }}
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Monitoring Records -->
            <section class="rounded-2xl border border-gray-200 bg-white shadow-sm overflow-hidden">
                <div class="border-b border-gray-100 bg-gray-50/50 px-6 py-4 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="h-8 w-1 bg-amber-500 rounded-full"></div>
                        <h3 class="text-lg font-semibold text-gray-900">Monitoring Records</h3>
                    </div>
                    <span class="text-xs text-gray-500 bg-gray-100 px-2 py-1 rounded-full">{{ monitoringRecords.length }} records</span>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-gray-100 bg-gray-50/30">
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Date</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Stage</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Recorder</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Notes</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            <tr v-if="!monitoringRecords.length">
                                <td
                                    class="px-6 py-8 text-center text-gray-400"
                                    colspan="4">
                                    <div class="flex flex-col items-center gap-2">
                                        <svg
                                            class="w-8 h-8 text-gray-300"
                                            fill="none"
                                            stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                        </svg>
                                        <span class="text-sm">No monitoring records found</span>
                                    </div>
                                </td>
                            </tr>
                            <tr
                                v-for="record in monitoringRecords"
                                :key="record.id"
                                class="hover:bg-gray-50/50 transition-colors">
                                <td class="px-6 py-3.5 text-gray-900 font-medium whitespace-nowrap">
                                    {{ formatDate(record.recorded_on) }}
                                </td>
                                <td class="px-6 py-3.5">
                                    <span class="inline-flex items-center rounded-md bg-amber-50 px-2 py-0.5 text-xs font-medium text-amber-700">
                                        {{ record.stage || "-" }}
                                    </span>
                                </td>
                                <td class="px-6 py-3.5 text-gray-600">
                                    {{ record.recorder?.name || "-" }}
                                </td>
                                <td
                                    class="px-6 py-3.5 text-gray-600 max-w-xs truncate"
                                    :title="record.notes">
                                    {{ record.notes || "-" }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>

            <!-- Inventory History -->
            <section class="rounded-2xl border border-gray-200 bg-white shadow-sm overflow-hidden">
                <div class="border-b border-gray-100 bg-gray-50/50 px-6 py-4 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="h-8 w-1 bg-rose-500 rounded-full"></div>
                        <h3 class="text-lg font-semibold text-gray-900">Inventory History</h3>
                    </div>
                    <span class="text-xs text-gray-500 bg-gray-100 px-2 py-1 rounded-full">{{ inventoryLogs.length }} entries</span>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-gray-100 bg-gray-50/30">
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Date</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Action</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">User</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Payload</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            <tr v-if="!inventoryLogs.length">
                                <td
                                    class="px-6 py-8 text-center text-gray-400"
                                    colspan="4">
                                    <div class="flex flex-col items-center gap-2">
                                        <svg
                                            class="w-8 h-8 text-gray-300"
                                            fill="none"
                                            stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                                        </svg>
                                        <span class="text-sm">No inventory activity yet</span>
                                    </div>
                                </td>
                            </tr>
                            <tr
                                v-for="log in inventoryLogs"
                                :key="log.id"
                                class="hover:bg-gray-50/50 transition-colors">
                                <td class="px-6 py-3.5 text-gray-900 whitespace-nowrap">
                                    {{ formatDateTime(log.created_at) }}
                                </td>
                                <td class="px-6 py-3.5">
                                    <span class="inline-flex items-center rounded-md bg-rose-50 px-2 py-0.5 text-xs font-medium text-rose-700 capitalize">
                                        {{ log.action || "-" }}
                                    </span>
                                </td>
                                <td class="px-6 py-3.5 text-gray-600">
                                    {{ log.actor?.name || "System" }}
                                </td>
                                <td class="px-6 py-3.5 text-gray-600">
                                    <code class="text-xs bg-gray-100 px-1.5 py-0.5 rounded text-gray-700 break-all">
                                        {{ log.qr_payload || log.barcode_value || "-" }}
                                    </code>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>

            <!-- Metadata -->
            <section
                v-if="metadataEntries.length"
                class="rounded-2xl border border-gray-200 bg-white shadow-sm overflow-hidden">
                <div class="border-b border-gray-100 bg-gray-50/50 px-6 py-4 flex items-center gap-3">
                    <div class="h-8 w-1 bg-slate-500 rounded-full"></div>
                    <h3 class="text-lg font-semibold text-gray-900">Metadata</h3>
                    <span class="text-xs text-gray-500 bg-gray-100 px-2 py-1 rounded-full">{{ metadataEntries.length }} fields</span>
                </div>

                <div class="p-6">
                    <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                        <div
                            v-for="entry in metadataEntries"
                            :key="entry[0]"
                            class="group rounded-xl border border-gray-200 bg-gray-50/30 p-4 hover:border-gray-300 hover:shadow-sm transition-all">
                            <label class="text-xs font-medium text-gray-400 uppercase tracking-wider">
                                {{ entry[0] }}
                            </label>
                            <p class="mt-1 text-sm font-medium text-gray-900 break-all">
                                {{ toDisplay(entry[1]) }}
                            </p>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </AppLayout>
</template>
