<script>
import axios from "axios";
import ApiMixin from "@/Modules/mixins/ApiMixin.js";
import DataFormatterMixin from "@/Modules/mixins/DataFormatterMixin";
import DtoResponse from "@/Modules/dto/DtoResponse";
import RequestFormPivot from "@/Modules/domain/RequestFormPivot";
import UseRequestApprovalBtn from "@/Pages/LabRequest/components/UseRequestApprovalBtn.vue";
import { extractRequestErrorMessage, normalizeRequestDisplayText } from "@/Pages/LabRequest/utils/requestErrorUtils";
import { X, Calendar, Clock, User, Building2, Briefcase, FileText, FlaskConical, Package, Microscope, AlertCircle, CheckCircle2, XCircle, ChevronRight, Info } from "lucide-vue-next";

export default {
    name: "UseRequestCard",
    props: {
        data: {
            type: Object,
            required: true,
        },
    },
    emits: ["deletedModel", "updated", "failedUpdate"],
    components: {
        UseRequestApprovalBtn,
        X,
        Calendar,
        Clock,
        User,
        Building2,
        Briefcase,
        FileText,
        FlaskConical,
        Package,
        Microscope,
        AlertCircle,
        CheckCircle2,
        XCircle,
        ChevronRight,
        Info,
    },
    mixins: [ApiMixin, DataFormatterMixin],
    data() {
        return {
            confirmDelete: false,
            updatedData: null,
            errors: null,
            showModal: false,
        };
    },
    computed: {
        RequestFormPivot() {
            return RequestFormPivot;
        },
        formsData() {
            if (this.updatedData && this.updatedData instanceof DtoResponse) {
                return this.updatedData.data;
            }
            return this.data;
        },
        statusConfig() {
            const configs = {
                approved: {
                    badge: "bg-emerald-100 text-emerald-700 border-emerald-200 dark:bg-emerald-500/10 dark:text-emerald-400 dark:border-emerald-500/20",
                    border: "bg-emerald-500",
                    ring: "ring-emerald-50 dark:ring-emerald-900/30",
                    icon: CheckCircle2,
                    label: "Approved",
                },
                released: {
                    badge: "bg-blue-100 text-blue-700 border-blue-200 dark:bg-blue-500/10 dark:text-blue-400 dark:border-blue-500/20",
                    border: "bg-blue-500",
                    ring: "ring-blue-50 dark:ring-blue-900/30",
                    icon: Package,
                    label: "Released",
                },
                returned: {
                    badge: "bg-slate-100 text-slate-700 border-slate-200 dark:bg-slate-500/10 dark:text-slate-400 dark:border-slate-500/20",
                    border: "bg-slate-500",
                    ring: "ring-slate-50 dark:ring-slate-800",
                    icon: CheckCircle2,
                    label: "Returned",
                },
                overdue: {
                    badge: "bg-orange-100 text-orange-700 border-orange-200 dark:bg-orange-500/10 dark:text-orange-400 dark:border-orange-500/20",
                    border: "bg-orange-500",
                    ring: "ring-orange-50 dark:ring-orange-900/30",
                    icon: AlertCircle,
                    label: "Overdue",
                },
                rejected: {
                    badge: "bg-rose-100 text-rose-700 border-rose-200 dark:bg-rose-500/10 dark:text-rose-400 dark:border-rose-500/20",
                    border: "bg-rose-500",
                    ring: "ring-rose-50 dark:ring-rose-900/30",
                    icon: XCircle,
                    label: "Rejected",
                },
                pending: {
                    badge: "bg-amber-100 text-amber-700 border-amber-200 dark:bg-amber-500/10 dark:text-amber-400 dark:border-amber-500/20",
                    border: "bg-amber-500",
                    ring: "ring-amber-50 dark:ring-amber-900/30",
                    icon: AlertCircle,
                    label: "Pending",
                },
            };
            return configs[this.formsData?.display_status] || configs.pending;
        },
        hasItems() {
            const rf = this.formsData?.requestForm;
            return rf?.laboratories_labels?.length || rf?.labs_to_use?.length || rf?.equipments_labels?.length || rf?.equipments_to_use?.length || rf?.consumables_to_use?.length || rf?.consumables_labels?.length;
        },
        requesterDisplayName() {
            return normalizeRequestDisplayText(this.formsData?.requester?.fullName || this.formsData?.requester?.name, "Unknown User");
        },
        requesterInitial() {
            return this.requesterDisplayName.charAt(0).toUpperCase() || "?";
        },
        lifecycleHint() {
            if (this.formsData?.is_overdue && this.formsData?.schedule_end_at) {
                return `Overdue since ${this.formatDate(this.formsData.schedule_end_at)}`;
            }
            if (this.formsData?.returned_at) {
                return `Returned ${this.formatDate(this.formsData.returned_at)}`;
            }
            if (this.formsData?.released_at) {
                return `Released ${this.formatDate(this.formsData.released_at)}`;
            }
            if (this.formsData?.approved_at) {
                return `Approved ${this.formatDate(this.formsData.approved_at)}`;
            }
            return null;
        },
    },
    methods: {
        openDetails() {
            this.showModal = true;
        },
        confirmAction() {
            this.confirmDelete = true;
        },
        async refreshData(updatedData) {
            this.closeModal();
            this.$emit("updated", updatedData);
        },
        async handleDelete() {
            this.toDelete = { event_id: this.formsData.event_id };
            const response = await this.submitDelete();
            if (response instanceof DtoResponse) {
                this.confirmDelete = false;
                this.$emit("deletedModel", response.data);
            }
        },
        closeModal() {
            this.showModal = false;
        },
        formatItems(labels, fallback) {
            const items = labels?.length ? labels : fallback;
            if (!items || !items.length) return "None specified";
            return Array.isArray(items) ? items.join(", ") : items;
        },
        displayText(value, fallback = "N/A") {
            return normalizeRequestDisplayText(value, fallback);
        },
    },
};
</script>

<template>
    <!-- Main Card -->
    <div
        v-if="formsData"
        class="group relative min-w-[20rem] max-w-full cursor-pointer overflow-hidden rounded-2xl border border-slate-200/60 bg-white/80 shadow-sm backdrop-blur-xl transition-all duration-300 ease-out hover:shadow-xl sm:min-w-[30rem] dark:border-slate-800 dark:bg-slate-900/80"
        role="button"
        tabindex="0"
        @click="openDetails"
        @keydown.enter.prevent="openDetails"
        @keydown.space.prevent="openDetails">
        <!-- Status Indicator Strip -->
        <div
            class="absolute bottom-0 left-0 top-0 w-1.5 transition-colors duration-300 group-hover:w-2"
            :class="statusConfig.border"></div>

        <div class="flex flex-col justify-between gap-4 p-5 pl-6 sm:flex-row sm:items-start">
            <!-- Left: User Info -->
            <div class="flex min-w-0 flex-1 items-start gap-3.5">
                <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl border border-slate-200 bg-gradient-to-br from-indigo-500 to-blue-600 text-lg font-bold text-white shadow-sm dark:border-slate-700">
                    {{ requesterInitial }}
                </div>

                <div class="flex w-full flex-col items-start gap-1">
                    <h3 class="w-full truncate text-base font-bold tracking-tight text-slate-900 dark:text-white">
                        {{ requesterDisplayName }}
                    </h3>

                    <div class="mt-0.5 flex w-full flex-col gap-0.5">
                        <div class="flex w-full items-center gap-1.5 text-[0.7rem] font-semibold text-slate-500 dark:text-slate-400">
                            <Briefcase class="h-3.5 w-3.5 shrink-0" />
                            <span class="truncate">
                                {{ displayText(formsData.requester?.position) }}
                            </span>
                        </div>
                        <div class="flex w-full items-center gap-1.5 text-[0.7rem] font-semibold text-slate-500 dark:text-slate-400">
                            <Building2 class="h-3.5 w-3.5 shrink-0" />
                            <span class="truncate">
                                {{ displayText(formsData.requester?.affiliation) }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right: Actions & Status -->
            <div class="mt-2 flex w-full shrink-0 flex-row items-center justify-between gap-2.5 sm:mt-0 sm:w-auto sm:flex-col sm:items-end sm:justify-start">
                <span
                    class="inline-flex items-center gap-1.5 rounded-full border px-3 py-1 text-xs font-bold uppercase tracking-widest shadow-sm"
                    :class="statusConfig.badge">
                    <component
                        :is="statusConfig.icon"
                        class="h-3.5 w-3.5" />
                    {{ statusConfig.label }}
                </span>

                <div class="flex flex-col items-end gap-0.5 text-right">
                    <span class="flex items-center gap-1 text-[0.65rem] font-bold uppercase tracking-widest text-slate-400 dark:text-slate-500">
                        {{ formatDate(formsData.updated_at) }}
                    </span>
                    <span
                        v-if="lifecycleHint"
                        class="text-[0.65rem] font-bold"
                        :class="formsData?.is_overdue ? 'text-orange-500 dark:text-orange-400' : 'text-slate-500 dark:text-slate-400'">
                        {{ lifecycleHint }}
                    </span>
                </div>
            </div>

            <!-- Hover Prompt (Desktop only) -->
            <div class="absolute bottom-4 right-5 hidden translate-x-2 transform items-center text-[0.65rem] font-bold uppercase tracking-widest text-slate-400 opacity-0 transition-all duration-300 group-hover:translate-x-0 group-hover:opacity-100 sm:flex dark:text-slate-500">
                Review
                <ChevronRight class="ml-0.5 h-3.5 w-3.5" />
            </div>
        </div>
    </div>

    <!-- Details Modal -->
    <Modal
        :show="showModal"
        :closeable="true"
        @close="closeModal"
        max-width="2xl">
        <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-2xl dark:border-slate-700 dark:bg-slate-900">
            <!-- Header -->
            <div class="sticky top-0 z-20 flex items-start justify-between border-b border-slate-100 bg-slate-50 px-6 py-5 backdrop-blur-md dark:border-slate-800 dark:bg-slate-900/80">
                <div class="flex items-center gap-3.5">
                    <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl border border-indigo-200 bg-indigo-100 shadow-sm dark:border-indigo-500/30 dark:bg-indigo-500/20">
                        <FileText class="h-6 w-6 text-indigo-600 dark:text-indigo-400" />
                    </div>
                    <div>
                        <h3 class="text-xl font-black leading-none tracking-tight text-slate-900 dark:text-white">Request Details</h3>
                        <p class="mt-1.5 text-xs font-bold uppercase tracking-widest text-slate-400">
                            {{ formsData.id }}
                        </p>
                    </div>
                </div>
                <button
                    @click="closeModal"
                    class="rounded-xl bg-slate-100 p-2 text-slate-500 transition-colors hover:bg-slate-200 dark:bg-slate-800 dark:text-slate-400 dark:hover:bg-slate-700">
                    <X class="h-5 w-5" />
                </button>
            </div>

            <!-- Content Area -->
            <div class="custom-scrollbar max-h-[75vh] space-y-8 overflow-y-auto bg-slate-50/50 p-6 dark:bg-slate-900/50">
                <!-- Status Timeline -->
                <div class="space-y-4">
                    <h4 class="flex items-center gap-2 text-[0.65rem] font-bold uppercase tracking-widest text-slate-500 dark:text-slate-400">
                        <Clock class="h-4 w-4 text-indigo-500 dark:text-indigo-400" />
                        Request Timeline
                    </h4>

                    <div class="relative overflow-hidden rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-700 dark:bg-slate-800">
                        <!-- Connecting Line -->
                        <div
                            class="absolute bottom-8 left-9 top-8 w-0.5 bg-slate-200 dark:bg-slate-700"
                            aria-hidden="true"></div>

                        <div class="space-y-6">
                            <!-- Submitted -->
                            <div class="relative z-10 flex items-start gap-4">
                                <div class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full border-2 border-white bg-emerald-100 ring-2 ring-emerald-50 dark:border-slate-800 dark:bg-emerald-500/20 dark:ring-emerald-900/30">
                                    <CheckCircle2 class="h-4 w-4 text-emerald-600 dark:text-emerald-400" />
                                </div>
                                <div>
                                    <p class="text-sm font-bold text-slate-900 dark:text-white">Submitted</p>
                                    <p class="mt-0.5 text-xs font-medium text-slate-500 dark:text-slate-400">
                                        {{ formatDate(formsData.created_at) }}
                                    </p>
                                </div>
                            </div>

                            <!-- Approved -->
                            <div
                                v-if="formsData.approved_at"
                                class="relative z-10 flex items-start gap-4">
                                <div class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full border-2 border-white bg-emerald-100 ring-2 ring-emerald-50 dark:border-slate-800 dark:bg-emerald-500/20 dark:ring-emerald-900/30">
                                    <CheckCircle2 class="h-4 w-4 text-emerald-600 dark:text-emerald-400" />
                                </div>
                                <div>
                                    <p class="text-sm font-bold text-slate-900 dark:text-white">
                                        Approved
                                        <span
                                            v-if="formsData.approved_by"
                                            class="ml-1 font-medium text-slate-400">
                                            by {{ formsData.approved_by }}
                                        </span>
                                    </p>
                                    <p class="mt-0.5 text-xs font-medium text-slate-500 dark:text-slate-400">
                                        {{ formatDate(formsData.approved_at) }}
                                    </p>
                                </div>
                            </div>

                            <!-- Released -->
                            <div
                                v-if="formsData.released_at"
                                class="relative z-10 flex items-start gap-4">
                                <div class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full border-2 border-white bg-blue-100 ring-2 ring-blue-50 dark:border-slate-800 dark:bg-blue-500/20 dark:ring-blue-900/30">
                                    <Package class="h-4 w-4 text-blue-600 dark:text-blue-400" />
                                </div>
                                <div>
                                    <p class="text-sm font-bold text-slate-900 dark:text-white">
                                        Released
                                        <span
                                            v-if="formsData.released_by"
                                            class="ml-1 font-medium text-slate-400">
                                            by {{ formsData.released_by }}
                                        </span>
                                    </p>
                                    <p class="mt-0.5 text-xs font-medium text-slate-500 dark:text-slate-400">
                                        {{ formatDate(formsData.released_at) }}
                                    </p>
                                </div>
                            </div>

                            <!-- Returned -->
                            <div
                                v-if="formsData.returned_at"
                                class="relative z-10 flex items-start gap-4">
                                <div class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full border-2 border-white bg-slate-200 ring-2 ring-slate-50 dark:border-slate-800 dark:bg-slate-700 dark:ring-slate-800">
                                    <CheckCircle2 class="h-4 w-4 text-slate-600 dark:text-slate-400" />
                                </div>
                                <div>
                                    <p class="text-sm font-bold text-slate-900 dark:text-white">
                                        Returned
                                        <span
                                            v-if="formsData.returned_by"
                                            class="ml-1 font-medium text-slate-400">
                                            by {{ formsData.returned_by }}
                                        </span>
                                    </p>
                                    <p class="mt-0.5 text-xs font-medium text-slate-500 dark:text-slate-400">
                                        {{ formatDate(formsData.returned_at) }}
                                    </p>
                                </div>
                            </div>

                            <!-- Rejected -->
                            <div
                                v-if="formsData.request_status === 'rejected'"
                                class="relative z-10 flex items-start gap-4">
                                <div class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full border-2 border-white bg-rose-100 ring-2 ring-rose-50 dark:border-slate-800 dark:bg-rose-500/20 dark:ring-rose-900/30">
                                    <XCircle class="h-4 w-4 text-rose-600 dark:text-rose-400" />
                                </div>
                                <div>
                                    <p class="text-sm font-bold text-slate-900 dark:text-white">Rejected</p>
                                    <p class="mt-0.5 text-xs font-medium text-slate-500 dark:text-slate-400">
                                        {{ formatDate(formsData.updated_at) }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Requester & Schedule Info -->
                <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                    <div class="space-y-4">
                        <h4 class="flex items-center gap-2 text-[0.65rem] font-bold uppercase tracking-widest text-slate-500 dark:text-slate-400">
                            <User class="h-4 w-4 text-indigo-500 dark:text-indigo-400" />
                            Requester
                        </h4>
                        <div class="space-y-3 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-700 dark:bg-slate-800">
                            <div>
                                <p class="text-base font-black tracking-tight text-slate-900 dark:text-white">
                                    {{ requesterDisplayName }}
                                </p>
                                <p class="mt-1 text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                                    {{ displayText(formsData.requester?.position) }}
                                </p>
                            </div>
                            <div class="border-t border-slate-100 pt-3 dark:border-slate-700/50">
                                <p class="text-sm font-medium leading-relaxed text-slate-600 dark:text-slate-300">
                                    {{ displayText(formsData.requester?.affiliation) }}
                                </p>
                                <p
                                    v-if="formsData.requester?.philrice_id"
                                    class="mt-1.5 text-xs font-bold uppercase tracking-wide text-indigo-600 dark:text-indigo-400">
                                    ID: {{ formsData.requester.philrice_id }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="space-y-4">
                        <h4 class="flex items-center gap-2 text-[0.65rem] font-bold uppercase tracking-widest text-slate-500 dark:text-slate-400">
                            <Clock class="h-4 w-4 text-indigo-500 dark:text-indigo-400" />
                            Schedule
                        </h4>
                        <div class="space-y-4 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-700 dark:bg-slate-800">
                            <div>
                                <p class="mb-1 text-[0.65rem] font-bold uppercase tracking-widest text-slate-400">From</p>
                                <p class="text-sm font-bold text-slate-800 dark:text-slate-200">
                                    {{ formatDate(formsData.requestForm?.date_of_use) }}
                                    <span class="mx-1 text-slate-400">·</span>
                                    <span class="text-indigo-600 dark:text-indigo-400">
                                        {{ formatTime(formsData.requestForm?.time_of_use) }}
                                    </span>
                                </p>
                            </div>
                            <div
                                v-if="formsData.requestForm?.date_of_use_end"
                                class="border-t border-slate-100 pt-4 dark:border-slate-700/50">
                                <p class="mb-1 text-[0.65rem] font-bold uppercase tracking-widest text-slate-400">To</p>
                                <p class="text-sm font-bold text-slate-800 dark:text-slate-200">
                                    {{ formatDate(formsData.requestForm?.date_of_use_end) }}
                                    <span class="mx-1 text-slate-400">·</span>
                                    <span class="text-indigo-600 dark:text-indigo-400">
                                        {{ formatTime(formsData.requestForm?.time_of_use_end) }}
                                    </span>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Project Info -->
                <div class="space-y-4">
                    <h4 class="flex items-center gap-2 text-[0.65rem] font-bold uppercase tracking-widest text-slate-500 dark:text-slate-400">
                        <Info class="h-4 w-4 text-indigo-500 dark:text-indigo-400" />
                        Other Information
                    </h4>
                    <div class="space-y-4 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-700 dark:bg-slate-800">
                        <div v-if="formsData.requestForm?.project_title">
                            <span class="mb-1 block text-[0.65rem] font-bold uppercase tracking-widest text-slate-400">Project Title</span>
                            <p class="text-sm font-medium text-slate-800 dark:text-slate-200">
                                {{ displayText(formsData.requestForm?.project_title) }}
                            </p>
                        </div>
                        <div v-if="formsData.requestForm?.request_purpose">
                            <span class="mb-1 block text-[0.65rem] font-bold uppercase tracking-widest text-slate-400">Purpose</span>
                            <p class="text-sm font-medium text-slate-800 dark:text-slate-200">
                                {{ displayText(formsData.requestForm?.request_purpose) }}
                            </p>
                        </div>
                        <div v-if="formsData.requestForm?.request_details">
                            <span class="mb-1 block text-[0.65rem] font-bold uppercase tracking-widest text-slate-400">Details</span>
                            <p class="whitespace-pre-wrap text-sm font-medium text-slate-800 dark:text-slate-200">
                                {{ displayText(formsData.requestForm.request_details) }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Requested Items Grid -->
                <div
                    v-if="hasItems"
                    class="space-y-4">
                    <h4 class="flex items-center gap-2 text-[0.65rem] font-bold uppercase tracking-widest text-slate-500 dark:text-slate-400">
                        <Package class="h-4 w-4 text-indigo-500 dark:text-indigo-400" />
                        Requested Items
                    </h4>

                    <div class="grid gap-4">
                        <!-- Laboratories -->
                        <div
                            v-if="formsData.requestForm?.laboratories_labels?.length || formsData.requestForm?.labs_to_use?.length"
                            class="flex items-start gap-4 rounded-2xl border border-purple-100 bg-purple-50 p-4 dark:border-purple-500/30 dark:bg-purple-500/10">
                            <div class="rounded-xl bg-purple-100 p-2 dark:bg-purple-500/20">
                                <Microscope class="h-5 w-5 text-purple-600 dark:text-purple-400" />
                            </div>
                            <div class="min-w-0 flex-1">
                                <span class="text-xs font-bold uppercase tracking-widest text-purple-800 dark:text-purple-300">Laboratories</span>
                                <p class="mt-1 text-sm font-medium leading-relaxed text-purple-900 dark:text-purple-100">
                                    {{ formatItems(formsData.requestForm.laboratories_labels, formsData.requestForm.labs_to_use) }}
                                </p>
                            </div>
                        </div>

                        <!-- Equipment -->
                        <div
                            v-if="formsData.requestForm?.equipments_labels?.length || formsData.requestForm?.equipments_to_use?.length"
                            class="flex items-start gap-4 rounded-2xl border border-blue-100 bg-blue-50 p-4 dark:border-blue-500/30 dark:bg-blue-500/10">
                            <div class="rounded-xl bg-blue-100 p-2 dark:bg-blue-500/20">
                                <FlaskConical class="h-5 w-5 text-blue-600 dark:text-blue-400" />
                            </div>
                            <div class="min-w-0 flex-1">
                                <span class="text-xs font-bold uppercase tracking-widest text-blue-800 dark:text-blue-300">Equipment</span>
                                <p class="mt-1 text-sm font-medium leading-relaxed text-blue-900 dark:text-blue-100">
                                    {{ formatItems(formsData.requestForm.equipments_labels, formsData.requestForm.equipments_to_use) }}
                                </p>
                            </div>
                        </div>

                        <!-- Consumables -->
                        <div
                            v-if="formsData.requestForm?.consumables_to_use?.length || formsData.requestForm?.consumables_labels?.length"
                            class="flex items-start gap-4 rounded-2xl border border-emerald-100 bg-emerald-50 p-4 dark:border-emerald-500/30 dark:bg-emerald-500/10">
                            <div class="rounded-xl bg-emerald-100 p-2 dark:bg-emerald-500/20">
                                <Package class="h-5 w-5 text-emerald-600 dark:text-emerald-400" />
                            </div>
                            <div class="min-w-0 flex-1">
                                <span class="text-xs font-bold uppercase tracking-widest text-emerald-800 dark:text-emerald-300">Consumables</span>
                                <p class="mt-1 text-sm font-medium leading-relaxed text-emerald-900 dark:text-emerald-100">
                                    {{ formatItems(formsData.requestForm.consumables_labels, formsData.requestForm.consumables_to_use) }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <div class="sticky bottom-0 flex items-center justify-end gap-3 border-t border-slate-200 bg-white px-6 py-5 dark:border-slate-800 dark:bg-slate-900">
                <UseRequestApprovalBtn
                    :data="formsData"
                    @updated="refreshData" />
            </div>
        </div>
    </Modal>
</template>

<style scoped>
/* Smooth transitions for modal */
:deep(.modal-enter-active),
:deep(.modal-leave-active) {
    transition: opacity 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

:deep(.modal-enter-from),
:deep(.modal-leave-to) {
    opacity: 0;
}

/* Custom scrollbar for modal content */
.custom-scrollbar::-webkit-scrollbar {
    width: 6px;
}
.custom-scrollbar::-webkit-scrollbar-track {
    background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
    background-color: #cbd5e1;
    border-radius: 9999px;
}
:deep(.dark) .custom-scrollbar::-webkit-scrollbar-thumb {
    background-color: #334155;
}
</style>
