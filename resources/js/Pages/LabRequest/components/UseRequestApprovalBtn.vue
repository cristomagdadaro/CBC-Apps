<script>
import ApiMixin from "@/Modules/mixins/ApiMixin";
import DtoError from "@/Modules/dto/DtoError";
import RequestFormPivot from "@/Modules/domain/RequestFormPivot";
import DataFormatterMixin from "@/Modules/mixins/DataFormatterMixin";
import { extractRequestErrorMessage } from "@/Pages/LabRequest/utils/requestErrorUtils";
import axios from "axios";
import { Printer, AlertCircle, Loader2, X, FileText, CheckCircle2, XCircle, Package } from "lucide-vue-next";

export default {
    name: "UseRequestApprovalBtn",
    components: {
        Printer,
        AlertCircle,
        Loader2,
        X,
        FileText,
        CheckCircle2,
        XCircle,
        Package,
    },
    props: {
        data: Object,
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
        this.setFormAction("update");
    },
    methods: {
        async handlePrint() {
            if (!this.data?.id || this.isPrinting) return;

            this.printError = null;
            this.printProgress = 0;
            this.isPrinting = true;
            this.showPrintModal = true;

            const baseUrl = this.data?.pdf_url || route("forms.generate.pdf", this.data.id);
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

                    const pdfResponse = await axios.get(targetUrl, { responseType: "blob" });
                    const contentType = (pdfResponse.headers?.["content-type"] ?? "").toLowerCase();

                    if (!contentType.includes("application/pdf")) {
                        const rawBlob = pdfResponse.data instanceof Blob ? pdfResponse.data : new Blob([pdfResponse.data]);

                        let errorMessage = "Failed to render PDF. Please try again.";
                        try {
                            const text = await rawBlob.text();
                            const parsed = JSON.parse(text);
                            errorMessage = parsed?.message || errorMessage;
                        } catch {
                            // keep default error message
                        }

                        throw new Error(errorMessage);
                    }

                    const blob = pdfResponse.data instanceof Blob ? pdfResponse.data : new Blob([pdfResponse.data], { type: "application/pdf" });
                    const url = window.URL.createObjectURL(blob);

                    const disposition = pdfResponse.headers?.["content-disposition"] ?? "";
                    const match = disposition.match(/filename="?([^";]+)"?/i);
                    const filename = match?.[1] ?? "request-form.pdf";

                    const link = document.createElement("a");
                    link.href = url;
                    link.download = filename;
                    document.body.appendChild(link);
                    link.click();
                    document.body.removeChild(link);
                    window.URL.revokeObjectURL(url);

                    setTimeout(() => {
                        this.isPrinting = false;
                        this.showPrintModal = false;
                        this.printProgress = 0;
                    }, 500);
                } else {
                    throw new Error("PDF is not ready yet. Please try again.");
                }
            } catch (error) {
                this.printError = extractRequestErrorMessage(error, "Failed to render PDF. Please try again.");
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
                this.form.approval_constraint = response.data.approval_constraint ?? "";
                this.form.disapproved_remarks = response.data.disapproved_remarks ?? "";
                this.form.approved_by = response.data.approved_by ?? "";
                this.form.approved_at = response.data.approved_at ?? null;
                this.form.released_by = response.data.released_by ?? "";
                this.form.released_at = response.data.released_at ?? null;
                this.form.returned_by = response.data.returned_by ?? "";
                this.form.returned_at = response.data.returned_at ?? null;
                this.form.display_status = response.data.display_status ?? response.data.request_status;
                this.form.is_overdue = Boolean(response.data.is_overdue);
                this.$emit("updated", response);
            } else {
                this.updateError = extractRequestErrorMessage(response, "Unable to update request approval.");
                this.$emit("failedUpdate", response);
            }
        },
    },
    computed: {
        pending() {
            return "pending";
        },
        rejected() {
            return "rejected";
        },
        approved() {
            return "approved";
        },
        released() {
            return "released";
        },
        returned() {
            return "returned";
        },
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
        },
    },
};
</script>

<template>
    <div class="flex w-full flex-col">
        <!-- Error Alert -->
        <div
            v-if="updateError"
            class="mb-4 flex items-center gap-2 rounded-xl border border-red-200 bg-red-50 p-3 text-sm font-semibold text-red-700 shadow-sm dark:border-red-500/30 dark:bg-red-500/10 dark:text-red-400">
            <AlertCircle class="h-4 w-4 shrink-0" />
            {{ updateError }}
        </div>

        <!-- Remarks & Conditions -->
        <div class="mb-5 flex w-full flex-col gap-3">
            <div
                v-if="shouldShowApprovalConditions || form.request_status === rejected"
                class="flex w-full flex-col gap-1">
                <text-area
                    v-if="shouldShowApprovalConditions"
                    v-model="form.approval_constraint"
                    label="Approval Special Conditions"
                    class="w-full rounded-xl border-slate-200 bg-white text-sm font-medium focus:ring-indigo-500 dark:border-slate-700 dark:bg-slate-900" />
                <text-area
                    v-if="form.request_status === rejected"
                    v-model="form.disapproved_remarks"
                    label="Remarks for Disapproval"
                    class="w-full rounded-xl border-slate-200 bg-white text-sm font-medium focus:ring-indigo-500 dark:border-slate-700 dark:bg-slate-900" />
            </div>

            <div
                v-if="areRemarksUpdated"
                class="mt-1 flex justify-end">
                <button
                    @click="handleUpdateApprovalBtn(form.request_status)"
                    :disabled="isProcessing"
                    class="inline-flex items-center gap-2 rounded-xl bg-indigo-600 px-5 py-2.5 text-sm font-bold text-white shadow-md shadow-indigo-600/20 transition-all hover:bg-indigo-700 active:scale-95 disabled:opacity-50">
                    <Loader2
                        v-if="isProcessing"
                        class="h-4 w-4 animate-spin" />
                    {{ isProcessing ? "Saving Changes..." : "Save Remarks" }}
                </button>
            </div>
        </div>

        <!-- Actions Footer -->
        <div class="flex flex-col items-center justify-between gap-4 border-t border-slate-200 pt-4 sm:flex-row dark:border-slate-700">
            <!-- Left Info -->
            <div class="flex flex-col items-center text-[0.65rem] font-bold uppercase text-slate-400 sm:items-start dark:text-slate-500">
                <span
                    v-if="statusActorLabel"
                    class="mb-0.5 text-slate-600 dark:text-slate-300">
                    {{ statusActorLabel }}
                </span>
                <span>Last updated: {{ formatDate(data.updated_at) }}</span>
            </div>

            <!-- Right Buttons -->
            <div class="flex w-full flex-wrap items-center justify-center gap-2.5 sm:w-auto sm:justify-end">
                <!-- Print Button -->
                <button
                    v-if="data.request_status !== 'pending'"
                    type="button"
                    @click.stop="handlePrint"
                    :disabled="isPrinting"
                    class="inline-flex items-center gap-1.5 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-bold text-slate-700 shadow-sm transition-all hover:bg-slate-50 active:scale-95 disabled:cursor-not-allowed disabled:opacity-50 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200 dark:hover:bg-slate-700">
                    <Printer
                        class="h-4 w-4 text-slate-400"
                        :class="{ 'animate-pulse text-indigo-500': isPrinting }" />
                    <span v-if="isPrinting">Printing...</span>
                    <span v-else>Print</span>
                </button>

                <!-- Reject Button -->
                <form
                    v-if="!!form && canReject"
                    @submit.prevent="handleUpdateApprovalBtn(rejected)">
                    <button
                        type="submit"
                        :disabled="isProcessing"
                        class="inline-flex items-center gap-1.5 rounded-xl border border-rose-200 bg-rose-50 px-4 py-2.5 text-sm font-bold text-rose-600 shadow-sm transition-all hover:bg-rose-100 active:scale-95 disabled:cursor-not-allowed disabled:opacity-50 dark:border-rose-500/30 dark:bg-rose-500/10 dark:text-rose-400 dark:hover:bg-rose-500/20">
                        <Loader2
                            v-if="isProcessing"
                            class="h-4 w-4 animate-spin" />
                        <XCircle
                            v-else
                            class="h-4 w-4" />
                        <span v-if="isProcessing">Saving...</span>
                        <span v-else>Reject</span>
                    </button>
                </form>

                <!-- Approve Button -->
                <form
                    v-if="!!form && canApprove"
                    @submit.prevent="handleUpdateApprovalBtn(approved)">
                    <button
                        type="submit"
                        :disabled="isProcessing"
                        class="inline-flex items-center gap-1.5 rounded-xl bg-emerald-500 px-6 py-2.5 text-sm font-bold text-white shadow-md shadow-emerald-500/20 transition-all hover:bg-emerald-600 active:scale-95 disabled:cursor-not-allowed disabled:bg-slate-300 disabled:text-slate-500 disabled:shadow-none">
                        <Loader2
                            v-if="isProcessing"
                            class="h-4 w-4 animate-spin" />
                        <CheckCircle2
                            v-else
                            class="h-4 w-4" />
                        <span v-if="isProcessing">Saving...</span>
                        <span v-else>Approve</span>
                    </button>
                </form>

                <!-- Release Button -->
                <form
                    v-if="!!form && canRelease"
                    @submit.prevent="handleUpdateApprovalBtn(released)">
                    <button
                        type="submit"
                        :disabled="isProcessing"
                        class="inline-flex items-center gap-1.5 rounded-xl bg-blue-600 px-6 py-2.5 text-sm font-bold text-white shadow-md shadow-blue-600/20 transition-all hover:bg-blue-700 active:scale-95 disabled:cursor-not-allowed disabled:bg-slate-300 disabled:text-slate-500 disabled:shadow-none">
                        <Loader2
                            v-if="isProcessing"
                            class="h-4 w-4 animate-spin" />
                        <Package
                            v-else
                            class="h-4 w-4" />
                        <span v-if="isProcessing">Saving...</span>
                        <span v-else>Release</span>
                    </button>
                </form>

                <!-- Return Button -->
                <form
                    v-if="!!form && canReturn"
                    @submit.prevent="handleUpdateApprovalBtn(returned)">
                    <button
                        type="submit"
                        :disabled="isProcessing"
                        class="inline-flex items-center gap-1.5 rounded-xl bg-slate-800 px-6 py-2.5 text-sm font-bold text-white shadow-md transition-all hover:bg-slate-900 active:scale-95 disabled:cursor-not-allowed disabled:opacity-50 dark:bg-slate-700 dark:hover:bg-slate-600">
                        <Loader2
                            v-if="isProcessing"
                            class="h-4 w-4 animate-spin" />
                        <CheckCircle2
                            v-else
                            class="h-4 w-4" />
                        <span v-if="isProcessing">Saving...</span>
                        <span v-else>Mark Returned</span>
                    </button>
                </form>

                <span
                    v-if="isClosedState"
                    class="rounded-xl border border-slate-200 bg-slate-100 px-4 py-2.5 text-[0.65rem] font-bold uppercase text-slate-400 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-500">
                    Workflow Completed
                </span>
            </div>
        </div>

        <!-- Print PDF Generation Modal -->
        <Modal
            :show="showPrintModal"
            :closeable="!isPrinting"
            @close="showPrintModal = false"
            max-width="sm">
            <div class="rounded-2xl bg-white p-8 dark:bg-slate-900">
                <div class="flex flex-col items-center text-center">
                    <div
                        class="mb-5 flex h-16 w-16 items-center justify-center rounded-full border-2 shadow-inner"
                        :class="printError ? 'border-red-100 bg-red-50 dark:border-red-500/20 dark:bg-red-500/10' : 'border-blue-100 bg-blue-50 dark:border-blue-500/20 dark:bg-blue-500/10'">
                        <Loader2
                            v-if="!printError"
                            class="h-7 w-7 animate-spin text-blue-600 dark:text-blue-400" />
                        <AlertCircle
                            v-else
                            class="h-7 w-7 text-red-600 dark:text-red-400" />
                    </div>

                    <h3 class="mb-2 text-lg font-black text-slate-900 dark:text-white">
                        {{ printError ? "Download Failed" : "Generating Document" }}
                    </h3>

                    <div
                        v-if="!printError"
                        class="mt-2 w-full space-y-3">
                        <div class="h-2 w-full overflow-hidden rounded-full bg-slate-100 shadow-inner dark:bg-slate-800">
                            <div
                                class="h-full rounded-full bg-blue-600 transition-all duration-300 ease-out dark:bg-blue-500"
                                :style="{ width: `${Math.min(printProgress, 100)}%` }"></div>
                        </div>
                        <p class="text-xs font-bold uppercase text-slate-500 dark:text-slate-400">
                            {{ printProgress < 100 ? "Rendering PDF..." : "Starting download..." }}
                        </p>
                    </div>

                    <div
                        v-if="printError"
                        class="mt-4 w-full">
                        <div class="mb-4 rounded-xl border border-red-200 bg-red-50 p-3 text-sm font-medium text-red-700 shadow-sm dark:border-red-500/30 dark:bg-red-500/10 dark:text-red-400">
                            {{ printError }}
                        </div>
                        <button
                            @click="showPrintModal = false"
                            class="w-full rounded-xl bg-slate-100 px-4 py-2.5 text-sm font-bold text-slate-700 transition-colors hover:bg-slate-200 dark:bg-slate-800 dark:text-slate-300 dark:hover:bg-slate-700">
                            Close
                        </button>
                    </div>
                </div>
            </div>
        </Modal>
    </div>
</template>

<style scoped></style>
