<script>
import ApiMixin from "@/Modules/mixins/ApiMixin";
import DtoError from "@/Modules/dto/DtoError";
import RequestFormPivot from "@/Modules/domain/RequestFormPivot";
import DataFormatterMixin from "@/Modules/mixins/DataFormatterMixin";
import { extractRequestErrorMessage } from "@/Pages/LabRequest/utils/requestErrorUtils";
import axios from "axios";
import { Printer, AlertCircle, Loader2 } from "lucide-vue-next";

export default {
    name: "UseRequestApprovalBtn",
    components: {
        Printer,
        AlertCircle,
        Loader2
    },
    props: {
        data: Object
    },
    mixins: [ApiMixin, DataFormatterMixin],
    data() {
        return {
            updateError: null,
            showPrintModal: false,
            printProgress: 0,
            isPrinting: false,
            printError: null,
        };
    },
    beforeMount() {
        this.model = new RequestFormPivot();
        this.setFormAction('update');
    },
    methods: {
        async handlePrint() {
            if (!this.data?.id || this.isPrinting) return;

            this.printError = null;
            this.printProgress = 0;
            this.isPrinting = true;
            this.showPrintModal = true;

            const baseUrl = this.data?.pdf_url || route('forms.generate.pdf', this.data.id);
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
        async handleUpdateApprovalBtn(status) {
            this.updateError = null;
            this.form.request_status = status;
            const response = await this.submitUpdate();
            if (!(response instanceof DtoError)) {
                this.form.request_status = response.data.request_status;
                this.form.approval_constraint = response.data.approval_constraint ?? '';
                this.form.disapproved_remarks = response.data.disapproved_remarks ?? '';
                this.form.approved_by = response.data.approved_by ?? '';
                this.form.approved_at = response.data.approved_at ?? null;
                this.form.released_by = response.data.released_by ?? '';
                this.form.released_at = response.data.released_at ?? null;
                this.form.returned_by = response.data.returned_by ?? '';
                this.form.returned_at = response.data.returned_at ?? null;
                this.form.display_status = response.data.display_status ?? response.data.request_status;
                this.form.is_overdue = Boolean(response.data.is_overdue);
                this.$emit("updated", response);
            } else {
                this.updateError = extractRequestErrorMessage(response, 'Unable to update request approval.');
                this.$emit("failedUpdate", response);
            }
        },
    },
    computed: {
        pending() { return 'pending'; },
        rejected() { return 'rejected'; },
        approved() { return 'approved'; },
        released() { return 'released'; },
        returned() { return 'returned'; },
        areRemarksUpdated() {
            return this.data.approval_constraint !== this.form.approval_constraint || this.data.disapproved_remarks !== this.form.disapproved_remarks;
        },
        isProcessing() {
            return this.model.api.processing;
        },
        canApprove() {
            return this.form.request_status === this.pending;
        },
        canReject() {
            return [this.pending, this.approved].includes(this.form.request_status);
        },
        canRelease() {
            return this.form.request_status === this.approved;
        },
        canReturn() {
            return this.form.request_status === this.released;
        },
        isClosedState() {
            return [this.rejected, this.returned].includes(this.form.request_status);
        },
        shouldShowApprovalConditions() {
            return [this.approved, this.released, this.returned].includes(this.form.request_status);
        },
        statusActorLabel() {
            if (this.form.request_status === this.returned && this.form.returned_by) {
                return `Returned to: ${this.form.returned_by}`;
            }
            if (this.form.request_status === this.released && this.form.released_by) {
                return `Released by: ${this.form.released_by}`;
            }
            if (this.form.request_status === this.approved && this.form.approved_by) {
                return `Approved by: ${this.form.approved_by}`;
            }
            if (this.form.request_status === this.rejected && this.form.approved_by) {
                return `Rejected by: ${this.form.approved_by}`;
            }

            return null;
        }
    }
}
</script>

<template>
    <div class="flex flex-col w-full gap-3">
        <h3 class="text-left leading-none">To be filled by the Officer In-Charge</h3>
        <p v-if="updateError" class="text-sm text-red-600">{{ updateError }}</p>
        <div class="flex flex-col w-full">
            <div class="flex flex-col w-full gap-1">
                <div class="flex flex-col w-full">
                    <text-area v-if="shouldShowApprovalConditions" v-model="form.approval_constraint" label="Approval Special Conditions" />
                    <text-area v-if="form.request_status === rejected" v-model="form.disapproved_remarks" label="Remarks for Disapproval" />
                </div>
                <submit-btn v-if="areRemarksUpdated" @click="handleUpdateApprovalBtn(form.request_status)" :disabled="isProcessing">
                    <span v-if="isProcessing">
                        Saving Changes
                    </span>
                    <span v-else>
                        Save Changes
                    </span>
                </submit-btn>
            </div>
        </div>
        <div class="flex justify-between">
            <div class="flex text-xs flex-col leading-none text-left">
                <span v-if="statusActorLabel">{{ statusActorLabel }}</span>
                <span>Last updated: {{ formatDate(data.updated_at) }}</span>
            </div>
            <div class="flex flex-wrap justify-end gap-2">
                <button v-if="data.request_status !== 'pending'" type="button" @click.stop="handlePrint"
                    :disabled="isPrinting"
                    aria-label="Download request form PDF"
                    class="flex items-center gap-1 text-gray-900 w-fit px-3 py-1.5 rounded transition hover:scale-105 bg-white border border-gray-300 disabled:opacity-50 disabled:cursor-not-allowed">
                    <Printer class="w-4 h-4" :class="{ 'animate-pulse': isPrinting }" />
                    <span v-if="isPrinting">Printing...</span>
                    <span v-else>Print</span>
                </button>

                <form v-if="!!form && canReject" @submit.prevent="handleUpdateApprovalBtn(rejected)">
                    <button
                        type="submit"
                        :disabled="isProcessing"
                        class="flex items-center gap-1 text-gray-900 w-fit px-3 py-1.5 rounded transition hover:scale-105 bg-red-400 disabled:bg-gray-300 disabled:text-gray-500 disabled:cursor-not-allowed"
                        aria-label="Reject request"
                    >
                        <span v-if="isProcessing">Saving...</span>
                        <span v-else>Reject</span>
                    </button>
                </form>

                <form v-if="!!form && canApprove" @submit.prevent="handleUpdateApprovalBtn(approved)">
                    <button
                        type="submit"
                        :disabled="isProcessing"
                        class="flex items-center gap-1 text-gray-900 w-fit px-3 py-1.5 rounded transition hover:scale-105 bg-green-400 disabled:bg-gray-300 disabled:text-gray-500 disabled:cursor-not-allowed"
                        aria-label="Approve request"
                    >
                        <span v-if="isProcessing">Saving...</span>
                        <span v-else>Approve</span>
                    </button>
                </form>

                <form v-if="!!form && canRelease" @submit.prevent="handleUpdateApprovalBtn(released)">
                    <button
                        type="submit"
                        :disabled="isProcessing"
                        class="flex items-center gap-1 text-gray-900 w-fit px-3 py-1.5 rounded transition hover:scale-105 bg-blue-400 disabled:bg-gray-300 disabled:text-gray-500 disabled:cursor-not-allowed"
                        aria-label="Release request"
                    >
                        <span v-if="isProcessing">Saving...</span>
                        <span v-else>Release</span>
                    </button>
                </form>

                <form v-if="!!form && canReturn" @submit.prevent="handleUpdateApprovalBtn(returned)">
                    <button
                        type="submit"
                        :disabled="isProcessing"
                        class="flex items-center gap-1 text-gray-900 w-fit px-3 py-1.5 rounded transition hover:scale-105 bg-slate-300 disabled:bg-gray-300 disabled:text-gray-500 disabled:cursor-not-allowed"
                        aria-label="Mark request returned"
                    >
                        <span v-if="isProcessing">Saving...</span>
                        <span v-else>Mark Returned</span>
                    </button>
                </form>

                <span v-if="isClosedState" class="text-xs text-gray-500 self-center">
                    Workflow completed for this request.
                </span>
            </div>
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
</template>

<style scoped>

</style>
