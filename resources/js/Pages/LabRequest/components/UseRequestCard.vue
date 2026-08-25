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
        class="group relative bg-white/80 dark:bg-slate-900/80 backdrop-blur-xl rounded-2xl border border-slate-200/60 dark:border-slate-800 shadow-sm hover:shadow-xl transition-all duration-300 ease-out overflow-hidden max-w-full min-w-[20rem] sm:min-w-[30rem] cursor-pointer"
        role="button"
        tabindex="0"
        @click="openDetails"
        @keydown.enter.prevent="openDetails"
        @keydown.space.prevent="openDetails">
        <!-- Status Indicator Strip -->
        <div
            class="absolute left-0 top-0 bottom-0 w-1.5 transition-colors duration-300 group-hover:w-2"
            :class="statusConfig.border"></div>

        <div class="p-5 pl-6 flex flex-col sm:flex-row sm:items-start justify-between gap-4">
            <!-- Left: User Info -->
            <div class="flex items-start gap-3.5 flex-1 min-w-0">
                <div class="w-11 h-11 rounded-xl bg-gradient-to-br from-indigo-500 to-blue-600 flex items-center justify-center text-white font-bold text-lg shadow-sm shrink-0 border border-slate-200 dark:border-slate-700">
                    {{ requesterInitial }}
                </div>

                <div class="flex flex-col items-start gap-1 w-full">
                    <h3 class="font-bold text-slate-900 dark:text-white truncate w-full text-base tracking-tight">
                        {{ requesterDisplayName }}
                    </h3>

                    <div class="flex flex-col gap-0.5 mt-0.5 w-full">
                        <div class="text-[0.7rem] font-semibold text-slate-500 dark:text-slate-400 flex items-center gap-1.5 w-full">
                            <Briefcase class="w-3.5 h-3.5 shrink-0" />
                            <span class="truncate">
                                {{ displayText(formsData.requester?.position) }}
                            </span>
                        </div>
                        <div class="text-[0.7rem] font-semibold text-slate-500 dark:text-slate-400 flex items-center gap-1.5 w-full">
                            <Building2 class="w-3.5 h-3.5 shrink-0" />
                            <span class="truncate">
                                {{ displayText(formsData.requester?.affiliation) }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right: Actions & Status -->
            <div class="flex flex-row sm:flex-col items-center sm:items-end justify-between sm:justify-start gap-2.5 shrink-0 mt-2 sm:mt-0 w-full sm:w-auto">
                <span
                    class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold uppercase tracking-widest border shadow-sm"
                    :class="statusConfig.badge">
                    <component
                        :is="statusConfig.icon"
                        class="w-3.5 h-3.5" />
                    {{ statusConfig.label }}
                </span>

                <div class="flex flex-col items-end gap-0.5 text-right">
                    <span class="text-[0.65rem] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-widest flex items-center gap-1">
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
            <div class="absolute right-5 bottom-4 hidden sm:flex items-center text-[0.65rem] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-widest opacity-0 group-hover:opacity-100 transition-all duration-300 transform group-hover:translate-x-0 translate-x-2">
                Review
                <ChevronRight class="w-3.5 h-3.5 ml-0.5" />
            </div>
        </div>
    </div>

    <!-- Details Modal -->
    <Modal
        :show="showModal"
        :closeable="true"
        @close="closeModal"
        max-width="2xl">
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl overflow-hidden shadow-2xl">
            <!-- Header -->
            <div class="px-6 py-5 border-b border-slate-100 dark:border-slate-800 bg-slate-50 dark:bg-slate-900/80 flex items-start justify-between backdrop-blur-md sticky top-0 z-20">
                <div class="flex items-center gap-3.5">
                    <div class="w-12 h-12 rounded-xl bg-indigo-100 dark:bg-indigo-500/20 flex items-center justify-center border border-indigo-200 dark:border-indigo-500/30 shadow-sm shrink-0">
                        <FileText class="w-6 h-6 text-indigo-600 dark:text-indigo-400" />
                    </div>
                    <div>
                        <h3 class="text-xl font-black text-slate-900 dark:text-white tracking-tight leading-none">Request Details</h3>
                        <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mt-1.5">
                            {{ formsData.id }}
                        </p>
                    </div>
                </div>
                <button
                    @click="closeModal"
                    class="p-2 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 rounded-xl transition-colors text-slate-500 dark:text-slate-400">
                    <X class="w-5 h-5" />
                </button>
            </div>

            <!-- Content Area -->
            <div class="p-6 space-y-8 max-h-[75vh] overflow-y-auto custom-scrollbar bg-slate-50/50 dark:bg-slate-900/50">
                <!-- Status Timeline -->
                <div class="space-y-4">
                    <h4 class="text-[0.65rem] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest flex items-center gap-2">
                        <Clock class="w-4 h-4 text-indigo-500 dark:text-indigo-400" />
                        Request Timeline
                    </h4>

                    <div class="bg-white dark:bg-slate-800 rounded-2xl p-6 shadow-sm border border-slate-200 dark:border-slate-700 relative overflow-hidden">
                        <!-- Connecting Line -->
                        <div
                            class="absolute left-9 top-8 bottom-8 w-0.5 bg-slate-200 dark:bg-slate-700"
                            aria-hidden="true"></div>

                        <div class="space-y-6">
                            <!-- Submitted -->
                            <div class="flex items-start gap-4 relative z-10">
                                <div class="w-7 h-7 rounded-full bg-emerald-100 dark:bg-emerald-500/20 flex items-center justify-center shrink-0 border-2 border-white dark:border-slate-800 ring-2 ring-emerald-50 dark:ring-emerald-900/30">
                                    <CheckCircle2 class="w-4 h-4 text-emerald-600 dark:text-emerald-400" />
                                </div>
                                <div>
                                    <p class="font-bold text-slate-900 dark:text-white text-sm">Submitted</p>
                                    <p class="text-xs font-medium text-slate-500 dark:text-slate-400 mt-0.5">
                                        {{ formatDate(formsData.created_at) }}
                                    </p>
                                </div>
                            </div>

                            <!-- Approved -->
                            <div
                                v-if="formsData.approved_at"
                                class="flex items-start gap-4 relative z-10">
                                <div class="w-7 h-7 rounded-full bg-emerald-100 dark:bg-emerald-500/20 flex items-center justify-center shrink-0 border-2 border-white dark:border-slate-800 ring-2 ring-emerald-50 dark:ring-emerald-900/30">
                                    <CheckCircle2 class="w-4 h-4 text-emerald-600 dark:text-emerald-400" />
                                </div>
                                <div>
                                    <p class="font-bold text-slate-900 dark:text-white text-sm">
                                        Approved
                                        <span
                                            v-if="formsData.approved_by"
                                            class="text-slate-400 font-medium ml-1">
                                            by {{ formsData.approved_by }}
                                        </span>
                                    </p>
                                    <p class="text-xs font-medium text-slate-500 dark:text-slate-400 mt-0.5">
                                        {{ formatDate(formsData.approved_at) }}
                                    </p>
                                </div>
                            </div>

                            <!-- Released -->
                            <div
                                v-if="formsData.released_at"
                                class="flex items-start gap-4 relative z-10">
                                <div class="w-7 h-7 rounded-full bg-blue-100 dark:bg-blue-500/20 flex items-center justify-center shrink-0 border-2 border-white dark:border-slate-800 ring-2 ring-blue-50 dark:ring-blue-900/30">
                                    <Package class="w-4 h-4 text-blue-600 dark:text-blue-400" />
                                </div>
                                <div>
                                    <p class="font-bold text-slate-900 dark:text-white text-sm">
                                        Released
                                        <span
                                            v-if="formsData.released_by"
                                            class="text-slate-400 font-medium ml-1">
                                            by {{ formsData.released_by }}
                                        </span>
                                    </p>
                                    <p class="text-xs font-medium text-slate-500 dark:text-slate-400 mt-0.5">
                                        {{ formatDate(formsData.released_at) }}
                                    </p>
                                </div>
                            </div>

                            <!-- Returned -->
                            <div
                                v-if="formsData.returned_at"
                                class="flex items-start gap-4 relative z-10">
                                <div class="w-7 h-7 rounded-full bg-slate-200 dark:bg-slate-700 flex items-center justify-center shrink-0 border-2 border-white dark:border-slate-800 ring-2 ring-slate-50 dark:ring-slate-800">
                                    <CheckCircle2 class="w-4 h-4 text-slate-600 dark:text-slate-400" />
                                </div>
                                <div>
                                    <p class="font-bold text-slate-900 dark:text-white text-sm">
                                        Returned
                                        <span
                                            v-if="formsData.returned_by"
                                            class="text-slate-400 font-medium ml-1">
                                            by {{ formsData.returned_by }}
                                        </span>
                                    </p>
                                    <p class="text-xs font-medium text-slate-500 dark:text-slate-400 mt-0.5">
                                        {{ formatDate(formsData.returned_at) }}
                                    </p>
                                </div>
                            </div>

                            <!-- Rejected -->
                            <div
                                v-if="formsData.request_status === 'rejected'"
                                class="flex items-start gap-4 relative z-10">
                                <div class="w-7 h-7 rounded-full bg-rose-100 dark:bg-rose-500/20 flex items-center justify-center shrink-0 border-2 border-white dark:border-slate-800 ring-2 ring-rose-50 dark:ring-rose-900/30">
                                    <XCircle class="w-4 h-4 text-rose-600 dark:text-rose-400" />
                                </div>
                                <div>
                                    <p class="font-bold text-slate-900 dark:text-white text-sm">Rejected</p>
                                    <p class="text-xs font-medium text-slate-500 dark:text-slate-400 mt-0.5">
                                        {{ formatDate(formsData.updated_at) }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Requester & Schedule Info -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-4">
                        <h4 class="text-[0.65rem] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest flex items-center gap-2">
                            <User class="w-4 h-4 text-indigo-500 dark:text-indigo-400" />
                            Requester
                        </h4>
                        <div class="bg-white dark:bg-slate-800 rounded-2xl p-5 shadow-sm border border-slate-200 dark:border-slate-700 space-y-3">
                            <div>
                                <p class="text-base font-black text-slate-900 dark:text-white tracking-tight">
                                    {{ requesterDisplayName }}
                                </p>
                                <p class="text-xs font-bold text-slate-500 dark:text-slate-400 mt-1 uppercase tracking-wider">
                                    {{ displayText(formsData.requester?.position) }}
                                </p>
                            </div>
                            <div class="pt-3 border-t border-slate-100 dark:border-slate-700/50">
                                <p class="text-sm font-medium text-slate-600 dark:text-slate-300 leading-relaxed">
                                    {{ displayText(formsData.requester?.affiliation) }}
                                </p>
                                <p
                                    v-if="formsData.requester?.philrice_id"
                                    class="text-xs font-bold text-indigo-600 dark:text-indigo-400 mt-1.5 uppercase tracking-wide">
                                    ID: {{ formsData.requester.philrice_id }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="space-y-4">
                        <h4 class="text-[0.65rem] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest flex items-center gap-2">
                            <Clock class="w-4 h-4 text-indigo-500 dark:text-indigo-400" />
                            Schedule
                        </h4>
                        <div class="bg-white dark:bg-slate-800 rounded-2xl p-5 shadow-sm border border-slate-200 dark:border-slate-700 space-y-4">
                            <div>
                                <p class="text-[0.65rem] font-bold uppercase tracking-widest text-slate-400 mb-1">From</p>
                                <p class="text-sm font-bold text-slate-800 dark:text-slate-200">
                                    {{ formatDate(formsData.requestForm?.date_of_use) }}
                                    <span class="text-slate-400 mx-1">·</span>
                                    <span class="text-indigo-600 dark:text-indigo-400">
                                        {{ formatTime(formsData.requestForm?.time_of_use) }}
                                    </span>
                                </p>
                            </div>
                            <div
                                v-if="formsData.requestForm?.date_of_use_end"
                                class="pt-4 border-t border-slate-100 dark:border-slate-700/50">
                                <p class="text-[0.65rem] font-bold uppercase tracking-widest text-slate-400 mb-1">To</p>
                                <p class="text-sm font-bold text-slate-800 dark:text-slate-200">
                                    {{ formatDate(formsData.requestForm?.date_of_use_end) }}
                                    <span class="text-slate-400 mx-1">·</span>
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
                    <h4 class="text-[0.65rem] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest flex items-center gap-2">
                        <Info class="w-4 h-4 text-indigo-500 dark:text-indigo-400" />
                        Other Information
                    </h4>
                    <div class="bg-white dark:bg-slate-800 rounded-2xl p-5 shadow-sm border border-slate-200 dark:border-slate-700 space-y-4">
                        <div v-if="formsData.requestForm?.project_title">
                            <span class="text-[0.65rem] font-bold uppercase tracking-widest text-slate-400 block mb-1">Project Title</span>
                            <p class="text-sm font-medium text-slate-800 dark:text-slate-200">
                                {{ displayText(formsData.requestForm?.project_title) }}
                            </p>
                        </div>
                        <div v-if="formsData.requestForm?.request_purpose">
                            <span class="text-[0.65rem] font-bold uppercase tracking-widest text-slate-400 block mb-1">Purpose</span>
                            <p class="text-sm font-medium text-slate-800 dark:text-slate-200">
                                {{ displayText(formsData.requestForm?.request_purpose) }}
                            </p>
                        </div>
                        <div v-if="formsData.requestForm?.request_details">
                            <span class="text-[0.65rem] font-bold uppercase tracking-widest text-slate-400 block mb-1">Details</span>
                            <p class="text-sm font-medium text-slate-800 dark:text-slate-200 whitespace-pre-wrap">
                                {{ displayText(formsData.requestForm.request_details) }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Requested Items Grid -->
                <div
                    v-if="hasItems"
                    class="space-y-4">
                    <h4 class="text-[0.65rem] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest flex items-center gap-2">
                        <Package class="w-4 h-4 text-indigo-500 dark:text-indigo-400" />
                        Requested Items
                    </h4>

                    <div class="grid gap-4">
                        <!-- Laboratories -->
                        <div
                            v-if="formsData.requestForm?.laboratories_labels?.length || formsData.requestForm?.labs_to_use?.length"
                            class="flex items-start gap-4 p-4 bg-purple-50 dark:bg-purple-500/10 rounded-2xl border border-purple-100 dark:border-purple-500/30">
                            <div class="p-2 bg-purple-100 dark:bg-purple-500/20 rounded-xl">
                                <Microscope class="w-5 h-5 text-purple-600 dark:text-purple-400" />
                            </div>
                            <div class="flex-1 min-w-0">
                                <span class="text-xs font-bold uppercase tracking-widest text-purple-800 dark:text-purple-300">Laboratories</span>
                                <p class="text-sm font-medium text-purple-900 dark:text-purple-100 mt-1 leading-relaxed">
                                    {{ formatItems(formsData.requestForm.laboratories_labels, formsData.requestForm.labs_to_use) }}
                                </p>
                            </div>
                        </div>

                        <!-- Equipment -->
                        <div
                            v-if="formsData.requestForm?.equipments_labels?.length || formsData.requestForm?.equipments_to_use?.length"
                            class="flex items-start gap-4 p-4 bg-blue-50 dark:bg-blue-500/10 rounded-2xl border border-blue-100 dark:border-blue-500/30">
                            <div class="p-2 bg-blue-100 dark:bg-blue-500/20 rounded-xl">
                                <FlaskConical class="w-5 h-5 text-blue-600 dark:text-blue-400" />
                            </div>
                            <div class="flex-1 min-w-0">
                                <span class="text-xs font-bold uppercase tracking-widest text-blue-800 dark:text-blue-300">Equipment</span>
                                <p class="text-sm font-medium text-blue-900 dark:text-blue-100 mt-1 leading-relaxed">
                                    {{ formatItems(formsData.requestForm.equipments_labels, formsData.requestForm.equipments_to_use) }}
                                </p>
                            </div>
                        </div>

                        <!-- Consumables -->
                        <div
                            v-if="formsData.requestForm?.consumables_to_use?.length || formsData.requestForm?.consumables_labels?.length"
                            class="flex items-start gap-4 p-4 bg-emerald-50 dark:bg-emerald-500/10 rounded-2xl border border-emerald-100 dark:border-emerald-500/30">
                            <div class="p-2 bg-emerald-100 dark:bg-emerald-500/20 rounded-xl">
                                <Package class="w-5 h-5 text-emerald-600 dark:text-emerald-400" />
                            </div>
                            <div class="flex-1 min-w-0">
                                <span class="text-xs font-bold uppercase tracking-widest text-emerald-800 dark:text-emerald-300">Consumables</span>
                                <p class="text-sm font-medium text-emerald-900 dark:text-emerald-100 mt-1 leading-relaxed">
                                    {{ formatItems(formsData.requestForm.consumables_labels, formsData.requestForm.consumables_to_use) }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <div class="px-6 py-5 bg-white dark:bg-slate-900 border-t border-slate-200 dark:border-slate-800 flex items-center justify-end gap-3 sticky bottom-0">
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
