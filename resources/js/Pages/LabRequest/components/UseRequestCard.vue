<script>
import axios from "axios";
import ApiMixin from "@/Modules/mixins/ApiMixin.js";
import DataFormatterMixin from "@/Modules/mixins/DataFormatterMixin";
import DtoResponse from "@/Modules/dto/DtoResponse";
import RequestFormPivot from "@/Modules/domain/RequestFormPivot";
import UseRequestApprovalBtn from "@/Pages/LabRequest/components/UseRequestApprovalBtn.vue";
import { extractRequestErrorMessage, normalizeRequestDisplayText } from "@/Pages/LabRequest/utils/requestErrorUtils";
import {
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
    XCircle
} from "lucide-vue-next";
import InfoIcon from "@/Components/Icons/InfoIcon.vue";

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
        XCircle
    },
    mixins: [ApiMixin, DataFormatterMixin],
    data() {
        return {
            confirmDelete: false,
            updatedData: null,
            errors: null,
            showModal: false,
        }
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
                    color: 'text-emerald-600',
                    bgColor: 'bg-emerald-50',
                    borderColor: 'border-emerald-200',
                    icon: CheckCircle2,
                    label: 'Approved'
                },
                released: {
                    color: 'text-blue-700',
                    bgColor: 'bg-blue-50',
                    borderColor: 'border-blue-200',
                    icon: Package,
                    label: 'Released'
                },
                returned: {
                    color: 'text-slate-700',
                    bgColor: 'bg-slate-100',
                    borderColor: 'border-slate-200',
                    icon: CheckCircle2,
                    label: 'Returned'
                },
                overdue: {
                    color: 'text-orange-700',
                    bgColor: 'bg-orange-50',
                    borderColor: 'border-orange-200',
                    icon: AlertCircle,
                    label: 'Overdue'
                },
                rejected: {
                    color: 'text-rose-600',
                    bgColor: 'bg-rose-50',
                    borderColor: 'border-rose-200',
                    icon: XCircle,
                    label: 'Rejected'
                },
                pending: {
                    color: 'text-amber-600',
                    bgColor: 'bg-amber-50',
                    borderColor: 'border-amber-200',
                    icon: AlertCircle,
                    label: 'Pending'
                }
            };
            return configs[this.formsData?.display_status] || configs.pending;
        },
        hasItems() {
            const rf = this.formsData?.requestForm;
            return (
                (rf?.laboratories_labels?.length || rf?.labs_to_use?.length) ||
                (rf?.equipments_labels?.length || rf?.equipments_to_use?.length) ||
                (rf?.consumables_to_use?.length || rf?.consumables_labels?.length)
            );
        },
        requesterDisplayName() {
            return normalizeRequestDisplayText(this.formsData?.requester?.fullName || this.formsData?.requester?.name, 'Unknown User');
        },
        requesterInitial() {
            return this.requesterDisplayName.charAt(0).toUpperCase() || '?';
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
        }
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
            if (!items || !items.length) return 'None specified';
            return Array.isArray(items) ? items.join(', ') : items;
        },
        displayText(value, fallback = 'N/A') {
            return normalizeRequestDisplayText(value, fallback);
        }
    },
}
</script>

<template>
    <div v-if="formsData"
        class="group relative bg-white rounded-xl border border-gray-200 shadow-sm hover:shadow-lg transition-all duration-300 ease-out overflow-hidden max-w-full min-w-[30rem]">

        <!-- Status Indicator Strip -->
        <div class="absolute left-0 top-0 bottom-0 w-1" :class="statusConfig.bgColor.replace('50', '500')"></div>

        <!-- Card Content -->
        <div class="p-5 pl-6">
            <div
                class="flex items-start justify-between gap-4 cursor-pointer"
                role="button"
                tabindex="0"
                @click="openDetails"
                @keydown.enter.prevent="openDetails"
                @keydown.space.prevent="openDetails"
            >

                <!-- Left: User Info -->
                <div class="flex flex-col items-end gap-1.5">
                    <div class="flex items-start gap-2">
                        <div
                            class="w-10 h-10 rounded-full bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center text-white font-semibold text-sm">
                            {{ requesterInitial }}
                        </div>
                        <div class="flex flex-col items-start gap-1">
                            <h3 class="font-semibold text-gray-900 truncate">
                                {{ requesterDisplayName }}
                            </h3>
                            <div class="text-xs text-gray-500 flex items-center gap-1">
                                <Briefcase class="w-3.5 h-3.5" />
                                <span class="truncate">{{ displayText(formsData.requester?.position) }}</span>
                            </div>
                            <div class="text-xs text-gray-500 flex items-center gap-1">
                                <Building2 class="w-3.5 h-3.5" />
                                <span class="truncate">{{ displayText(formsData.requester?.affiliation) }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right: Actions & Status -->
                <div class="flex flex-col items-end gap-1.5">
                    <!-- Status Badge -->
                    <div class="flex flex-col items-end gap-1">
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-sm font-medium border"
                            :class="[statusConfig.bgColor, statusConfig.color, statusConfig.borderColor]">
                            <component :is="statusConfig.icon" class="w-4 h-4" />
                            {{ statusConfig.label }}
                        </span>
                        <span class="text-xs text-gray-400 flex items-center gap-1">
                            <Clock class="w-3 h-3" />
                            {{ formatDate(formsData.updated_at) }}
                        </span>
                        <span v-if="lifecycleHint" class="text-xs text-right" :class="formsData?.is_overdue ? 'text-orange-600' : 'text-gray-500'">
                            {{ lifecycleHint }}
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Details Modal -->
    <Modal :show="showModal" :closeable="true" @close="closeModal" max-width="2xl">
        <div class="bg-white">
            <!-- Header -->
            <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between bg-gray-50/50">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-blue-100 flex items-center justify-center">
                        <FileText class="w-5 h-5 text-blue-600" />
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900">Request Details</h3>
                        <p class="text-xs text-gray-500">{{ formsData.id }}</p>
                    </div>
                </div>
                <button @click="closeModal" class="p-2 hover:bg-gray-100 rounded-lg transition-colors">
                    <span class="sr-only">Close request details</span>
                    <X class="w-5 h-5 text-gray-500" />
                </button>
            </div>

            <!-- Content -->
            <div class="p-6 space-y-6 max-h-[70vh] overflow-y-auto">
                <!-- Status Timeline -->
                <div class="space-y-3">
                    <h4 class="text-sm font-semibold text-gray-900 uppercase tracking-wider flex items-center gap-2">
                        <Clock class="w-4 h-4" /> Request Timeline
                    </h4>
                    <div class="bg-gray-50 rounded-xl p-5 space-y-5 border border-gray-100 relative">
                        <!-- Connecting Line Background -->
                        <div class="absolute left-8 top-8 bottom-8 w-0.5 bg-gray-200" aria-hidden="true"></div>

                        <!-- Submitted -->
                        <div class="flex items-start gap-4 relative z-10">
                            <div class="w-6 h-6 rounded-full bg-emerald-100 flex items-center justify-center shrink-0 border-2 border-white ring-2 ring-emerald-50">
                                <CheckCircle2 class="w-4 h-4 text-emerald-600" />
                            </div>
                            <div>
                                <p class="font-medium text-gray-900 text-sm">Submitted</p>
                                <p class="text-xs text-gray-500">{{ formatDate(formsData.created_at) }}</p>
                            </div>
                        </div>

                        <!-- Approved -->
                        <div v-if="formsData.approved_at" class="flex items-start gap-4 relative z-10">
                            <div class="w-6 h-6 rounded-full bg-emerald-100 flex items-center justify-center shrink-0 border-2 border-white ring-2 ring-emerald-50">
                                <CheckCircle2 class="w-4 h-4 text-emerald-600" />
                            </div>
                            <div>
                                <p class="font-medium text-gray-900 text-sm">Approved <span v-if="formsData.approved_by" class="text-gray-500 font-normal ml-1">by {{ formsData.approved_by }}</span></p>
                                <p class="text-xs text-gray-500">{{ formatDate(formsData.approved_at) }}</p>
                            </div>
                        </div>

                        <!-- Released -->
                        <div v-if="formsData.released_at" class="flex items-start gap-4 relative z-10">
                            <div class="w-6 h-6 rounded-full bg-blue-100 flex items-center justify-center shrink-0 border-2 border-white ring-2 ring-blue-50">
                                <Package class="w-4 h-4 text-blue-600" />
                            </div>
                            <div>
                                <p class="font-medium text-gray-900 text-sm">Released <span v-if="formsData.released_by" class="text-gray-500 font-normal ml-1">by {{ formsData.released_by }}</span></p>
                                <p class="text-xs text-gray-500">{{ formatDate(formsData.released_at) }}</p>
                            </div>
                        </div>

                        <!-- Returned -->
                        <div v-if="formsData.returned_at" class="flex items-start gap-4 relative z-10">
                            <div class="w-6 h-6 rounded-full bg-slate-200 flex items-center justify-center shrink-0 border-2 border-white ring-2 ring-slate-50">
                                <CheckCircle2 class="w-4 h-4 text-slate-600" />
                            </div>
                            <div>
                                <p class="font-medium text-gray-900 text-sm">Returned <span v-if="formsData.returned_by" class="text-gray-500 font-normal ml-1">by {{ formsData.returned_by }}</span></p>
                                <p class="text-xs text-gray-500">{{ formatDate(formsData.returned_at) }}</p>
                            </div>
                        </div>

                        <!-- Rejected -->
                        <div v-if="formsData.request_status === 'rejected'" class="flex items-start gap-4 relative z-10">
                            <div class="w-6 h-6 rounded-full bg-rose-100 flex items-center justify-center shrink-0 border-2 border-white ring-2 ring-rose-50">
                                <XCircle class="w-4 h-4 text-rose-600" />
                            </div>
                            <div>
                                <p class="font-medium text-gray-900 text-sm">Rejected</p>
                                <p class="text-xs text-gray-500">{{ formatDate(formsData.updated_at) }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Requester Info -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="space-y-3">
                        <h4
                            class="text-sm font-semibold text-gray-900 uppercase tracking-wider flex items-center gap-2">
                            <User class="w-4 h-4" /> Requester
                        </h4>
                        <div class="bg-gray-50 rounded-lg p-4 space-y-2">
                            <p class="font-medium text-gray-900">{{ requesterDisplayName }}</p>
                            <p class="text-sm text-gray-600">{{ displayText(formsData.requester?.position) }}</p>
                            <p class="text-sm text-gray-600">{{ displayText(formsData.requester?.affiliation) }}</p>
                            <p v-if="formsData.requester?.philrice_id" class="text-sm text-gray-600">PhilRice ID: {{ formsData.requester.philrice_id }}</p>
                        </div>
                    </div>

                    <div class="space-y-3">
                        <h4
                            class="text-sm font-semibold text-gray-900 uppercase tracking-wider flex items-center gap-2">
                            <Clock class="w-4 h-4" /> Schedule
                        </h4>
                        <div class="bg-gray-50 rounded-lg p-4 space-y-2">
                            <div class="flex items-center gap-2 text-sm">
                                <span class="text-gray-500">From:</span>
                                <span class="font-medium">
                                    {{ formatDate(formsData.requestForm?.date_of_use) }}
                                    {{ formatTime(formsData.requestForm?.time_of_use) }}
                                </span>
                            </div>
                            <div v-if="formsData.requestForm?.date_of_use_end" class="flex items-center gap-2 text-sm">
                                <span class="text-gray-500">To:</span>
                                <span class="font-medium">
                                    {{ formatDate(formsData.requestForm?.date_of_use_end) }}
                                    {{ formatTime(formsData.requestForm?.time_of_use_end) }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Project Info -->
                <div class="space-y-3">
                    <h4 class="text-sm font-semibold text-gray-900 uppercase tracking-wider flex items-center gap-2">
                        <InfoIcon class="w-4 h-4" /> Other Information
                    </h4>
                    <div class="bg-gray-50 rounded-lg p-4 space-y-3">
                        <div v-if="formsData.requestForm?.project_title">
                            <span class="text-sm text-gray-500">Title</span>
                            <p class="font-medium text-gray-900">{{ displayText(formsData.requestForm?.project_title) }}</p>
                        </div>
                        <div v-if="formsData.requestForm?.request_purpose">
                            <span class="text-sm text-gray-500">Purpose</span>
                            <p class="text-gray-700">{{ displayText(formsData.requestForm?.request_purpose) }}</p>
                        </div>
                        <div v-if="formsData.requestForm?.request_details">
                            <span class="text-sm text-gray-500">Details</span>
                            <p class="text-gray-700 whitespace-pre-wrap">{{ displayText(formsData.requestForm.request_details) }}</p>
                        </div>
                    </div>
                </div>

                <!-- Items Grid -->
                <div v-if="hasItems" class="space-y-3">
                    <h4 class="text-sm font-semibold text-gray-900 uppercase tracking-wider flex items-center gap-2">
                        <Package class="w-4 h-4" /> Requested Items
                    </h4>

                    <div class="grid gap-3">
                        <!-- Laboratories -->
                        <div v-if="formsData.requestForm?.laboratories_labels?.length || formsData.requestForm?.labs_to_use?.length"
                            class="flex items-start gap-3 p-3 bg-purple-50 rounded-lg border border-purple-100">
                            <Microscope class="w-5 h-5 text-purple-600 mt-0.5" />
                            <div class="flex-1">
                                <span class="text-sm font-medium text-purple-900">Laboratories</span>
                                <p class="text-sm text-purple-800 mt-1">
                                    {{ formatItems(formsData.requestForm.laboratories_labels,
                                        formsData.requestForm.labs_to_use) }}
                                </p>
                            </div>
                        </div>

                        <!-- Equipment -->
                        <div v-if="formsData.requestForm?.equipments_labels?.length || formsData.requestForm?.equipments_to_use?.length"
                            class="flex items-start gap-3 p-3 bg-blue-50 rounded-lg border border-blue-100">
                            <FlaskConical class="w-5 h-5 text-blue-600 mt-0.5" />
                            <div class="flex-1">
                                <span class="text-sm font-medium text-blue-900">Equipment</span>
                                <p class="text-sm text-blue-800 mt-1">
                                    {{ formatItems(formsData.requestForm.equipments_labels,
                                        formsData.requestForm.equipments_to_use) }}
                                </p>
                            </div>
                        </div>

                        <!-- Consumables -->
                        <div v-if="formsData.requestForm?.consumables_to_use?.length || formsData.requestForm?.consumables_labels?.length"
                            class="flex items-start gap-3 p-3 bg-green-50 rounded-lg border border-green-100">
                            <Package class="w-5 h-5 text-green-600 mt-0.5" />
                            <div class="flex-1">
                                <span class="text-sm font-medium text-green-900">Consumables</span>
                                <p class="text-sm text-green-800 mt-1">
                                    {{ formatItems(formsData.requestForm.consumables_labels,
                                        formsData.requestForm.consumables_to_use) }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <div class="px-6 py-4 bg-gray-50 border-t border-gray-100 flex items-center justify-between gap-3">
                <UseRequestApprovalBtn :data="formsData" @updated="refreshData" />
            </div>
        </div>
    </Modal>
</template>

<style scoped>
/* Smooth transitions for modal */
:deep(.modal-enter-active),
:deep(.modal-leave-active) {
    transition: opacity 0.3s ease;
}

:deep(.modal-enter-from),
:deep(.modal-leave-to) {
    opacity: 0;
}

/* Custom scrollbar for modal content */
.overflow-y-auto {
    scrollbar-width: thin;
    scrollbar-color: #cbd5e1 transparent;
}

.overflow-y-auto::-webkit-scrollbar {
    width: 6px;
}

.overflow-y-auto::-webkit-scrollbar-track {
    background: transparent;
}

.overflow-y-auto::-webkit-scrollbar-thumb {
    background-color: #cbd5e1;
    border-radius: 3px;
}
</style>
