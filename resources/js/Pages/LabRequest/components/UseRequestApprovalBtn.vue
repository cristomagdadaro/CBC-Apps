<script>
import ApiMixin from "@/Modules/mixins/ApiMixin";
import DtoError from "@/Modules/dto/DtoError";
import RequestFormPivot from "@/Modules/domain/RequestFormPivot";
import DataFormatterMixin from "@/Modules/mixins/DataFormatterMixin";
import { extractRequestErrorMessage } from "@/Pages/LabRequest/utils/requestErrorUtils";

export default {
    name: "UseRequestApprovalBtn",
    props: {
        data: Object
    },
    mixins: [ApiMixin, DataFormatterMixin],
    data() {
        return {
            updateError: null,
        };
    },
    beforeMount() {
        this.model = new RequestFormPivot();
        this.setFormAction('update');
    },
    methods: {
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
</template>

<style scoped>

</style>
