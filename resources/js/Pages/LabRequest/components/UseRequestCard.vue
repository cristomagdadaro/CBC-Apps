<script>
import axios from "axios";
import ApiMixin from "@/Modules/mixins/ApiMixin.js";
import DataFormatterMixin from "@/Modules/mixins/DataFormatterMixin";
import DtoResponse from "@/Modules/dto/DtoResponse";
import RequestFormPivot from "@/Modules/domain/RequestFormPivot";
import UseRequestApprovalBtn from "@/Pages/LabRequest/components/UseRequestApprovalBtn.vue";
import { extractRequestErrorMessage, normalizeRequestDisplayText } from "@/Pages/LabRequest/utils/requestErrorUtils";
import {
    Printer,
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
    Loader2
} from "lucide-vue-next";

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
        Printer,
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
        Loader2
    },
    mixins: [ApiMixin, DataFormatterMixin],
    data() {
        return {
            confirmDelete: false,
            updatedData: null,
            errors: null,
            showModal: false,
            showPrintModal: false,
            printProgress: 0,
            isPrinting: false,
            printError: null,
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
        async handlePrint() {
            if (!this.formsData?.id || this.isPrinting) return;

            this.printError = null;
            this.printProgress = 0;
            this.isPrinting = true;
            this.showPrintModal = true;

            const baseUrl = this.formsData?.pdf_url || route('forms.generate.pdf', this.formsData.id);
            const prefetchUrl = `${baseUrl}?prefetch=1`;

            let progressTimer = null;
            try {
                progressTimer = setInterval(() => {
                    if (this.printProgress < 90) {
                        this.printProgress += Math.random() * 15 + 5;
                        if (this.printProgress > 90) this.printProgress = 90;
                    }
                }, 400);

                const response = await axios.get(prefetchUrl);

                if (response?.data?.ready) {
                    this.printProgress = 100;
                    const targetUrl = response.data.download_url ?? response.data.url;

                    const pdfResponse = await axios.get(targetUrl, { responseType: 'blob' });
                    const contentType = (pdfResponse.headers?.['content-type'] ?? '').toLowerCase();

                    if (!contentType.includes('application/pdf')) {
                        const rawBlob = pdfResponse.data instanceof Blob
                            ? pdfResponse.data
                            : new Blob([pdfResponse.data]);

                        let errorMessage = 'Failed to render PDF. Please try again.';
                        try {
                            const text = await rawBlob.text();
                            const parsed = JSON.parse(text);
                            errorMessage = parsed?.message || errorMessage;
                        } catch {
                            // keep default error message
                        }

                        throw new Error(errorMessage);
                    }

                    const blob = pdfResponse.data instanceof Blob
                        ? pdfResponse.data
                        : new Blob([pdfResponse.data], { type: 'application/pdf' });
                    const url = window.URL.createObjectURL(blob);

                    const disposition = pdfResponse.headers?.['content-disposition'] ?? '';
                    const match = disposition.match(/filename="?([^";]+)"?/i);
                    const filename = match?.[1] ?? 'request-form.pdf';

                    const link = document.createElement('a');
                    link.href = url;
                    link.download = filename;
                    document.body.appendChild(link);
                    link.click();
                    document.body.removeChild(link);
                    window.URL.revokeObjectURL(url);

                    // Auto-close after successful download
                    setTimeout(() => {
                        this.isPrinting = false;
                        this.showPrintModal = false;
                        this.printProgress = 0;
                    }, 500);
                } else {
                    throw new Error('PDF is not ready yet. Please try again.');
                }
            } catch (error) {
                this.printError = extractRequestErrorMessage(error, 'Failed to render PDF. Please try again.');
                this.printProgress = 0;
            } finally {
                if (progressTimer) clearInterval(progressTimer);
            }
        },
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
                <div class="flex-1 min-w-0 space-y-3">
                    <div class="flex items-center gap-3">
                        <div
                            class="w-10 h-10 rounded-full bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center text-white font-semibold text-sm">
                            {{ requesterInitial }}
                        </div>
                        <div class="min-w-0">
                            <h3 class="font-semibold text-gray-900 truncate">
                                {{ requesterDisplayName }}
                            </h3>
                            <p class="text-sm text-gray-500 flex items-center gap-1.5">
                                <Briefcase class="w-3.5 h-3.5" />
                                <span class="truncate">{{ displayText(formsData.requester?.position) }}</span>
                                <span class="text-gray-300">•</span>
                                <Building2 class="w-3.5 h-3.5" />
                                <span class="truncate">{{ displayText(formsData.requester?.affiliation) }}</span>
                            </p>
                        </div>
                    </div>

                    <div class="flex items-center gap-4 text-sm text-gray-500">
                        <span class="flex items-center gap-1.5">
                            <Calendar class="w-4 h-4" />
                            Created {{ formatDate(formsData.created_at) }}
                        </span>
                    </div>
                </div>

                <!-- Right: Actions & Status -->
                <div class="flex flex-col items-end gap-3">
                    <!-- Print Button -->
                    <button v-if="formsData.request_status !== 'pending'" type="button" @click.stop="handlePrint"
                        :disabled="isPrinting"
                        aria-label="Download request form PDF"
                        class="group/btn inline-flex items-center gap-2 px-3 py-1.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 hover:border-gray-400 hover:text-gray-900 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed transition-all duration-200">
                        <Printer class="w-4 h-4" :class="{ 'animate-pulse': isPrinting }" />
                        <span>Print</span>
                    </button>

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

        <!-- Hover Hint -->
        <div class="absolute bottom-2 right-2 opacity-0 group-hover:opacity-100 transition-opacity duration-300">
            <span class="text-xs text-gray-400">Click to view details</span>
        </div>
    </div>

    <!-- Print Modal -->
    <Modal :show="showPrintModal" :closeable="!isPrinting" @close="showPrintModal = false" max-width="sm">
        <div class="p-6">
            <div class="text-center">
                <div class="mx-auto w-12 h-12 rounded-full bg-blue-50 flex items-center justify-center mb-4">
                    <Loader2 v-if="!printError" class="w-6 h-6 text-blue-600 animate-spin" />
                    <AlertCircle v-else class="w-6 h-6 text-red-600" />
                </div>

                <h3 class="text-lg font-semibold text-gray-900 mb-2">
                    {{ printError ? 'Download Failed' : 'Generating PDF' }}
                </h3>

                <div v-if="!printError" class="space-y-3">
                    <div class="w-full bg-gray-100 rounded-full h-2 overflow-hidden">
                        <div class="h-full bg-blue-600 transition-all duration-500 ease-out rounded-full"
                            :style="{ width: `${Math.min(printProgress, 100)}%` }"></div>
                    </div>
                    <p class="text-sm text-gray-500">
                        {{ printProgress < 100 ? 'Preparing your document...' : 'Download starting...' }} </p>
                </div>

                <div v-if="printError" class="mt-4">
                    <p class="text-sm text-red-600 bg-red-50 rounded-lg p-3">
                        {{ printError }}
                    </p>
                    <button @click="showPrintModal = false"
                        aria-label="Close print dialog"
                        class="mt-4 px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg text-sm font-medium transition-colors">
                        Close
                    </button>
                </div>
            </div>
        </div>
    </Modal>

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
                        <p class="text-sm text-gray-500">Form #{{ formsData.id }}</p>
                    </div>
                </div>
                <button @click="closeModal" class="p-2 hover:bg-gray-100 rounded-lg transition-colors">
                    <span class="sr-only">Close request details</span>
                    <X class="w-5 h-5 text-gray-500" />
                </button>
            </div>

            <!-- Content -->
            <div class="p-6 space-y-6 max-h-[70vh] overflow-y-auto">
                <!-- Status Banner -->
                <div class="flex items-center justify-between p-4 rounded-xl border"
                    :class="[statusConfig.bgColor, statusConfig.borderColor]">
                    <div class="flex items-center gap-3">
                        <component :is="statusConfig.icon" class="w-5 h-5" :class="statusConfig.color" />
                        <div>
                            <p class="font-medium" :class="statusConfig.color">
                                {{ statusConfig.label }}
                            </p>
                            <p class="text-sm text-gray-600">
                                {{ lifecycleHint || `Last updated ${formatDate(formsData.updated_at)}` }}
                            </p>
                        </div>
                    </div>
                    <span v-if="formsData.returned_by || formsData.released_by || formsData.approved_by" class="text-sm text-gray-600">
                        by {{ formsData.returned_by || formsData.released_by || formsData.approved_by }}
                    </span>
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
                    <h4 class="text-sm font-semibold text-gray-900 uppercase tracking-wider">Project Information</h4>
                    <div class="bg-gray-50 rounded-lg p-4 space-y-3">
                        <div>
                            <span class="text-sm text-gray-500">Title</span>
                            <p class="font-medium text-gray-900">{{ displayText(formsData.requestForm?.project_title) }}</p>
                        </div>
                        <div>
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
                <button v-if="formsData.request_status !== 'pending'" @click="handlePrint" :disabled="isPrinting"
                    aria-label="Print request form"
                    class="inline-flex items-center gap-2 px-4 py-2 bg-white border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 hover:border-gray-400 transition-all disabled:opacity-50">
                    <Printer class="w-4 h-4" />
                    Print Form
                </button>

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
