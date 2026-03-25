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
                this.$emit("updated", response);
            } else {
                this.updateError = extractRequestErrorMessage(response, 'Unable to update request approval.');
                this.$emit("failedUpdate", response);
            }
        },
    },
    computed: {
        rejected() {
            return 'rejected';
        },
        approved() {
            return 'approved';
        },
        areRemarksUpdated() {
            return this.data.approval_constraint !== this.form.approval_constraint || this.data.disapproved_remarks !== this.form.disapproved_remarks;
        }
    }
}
</script>

<template>
    <div class="flex flex-col w-full gap-3">
        <h3 v-if="form.request_status === approved || form.request_status === rejected" class="text-left leading-none">To be filled by the Officer In-Charge</h3>
        <p v-if="updateError" class="text-sm text-red-600">{{ updateError }}</p>
        <div class="flex flex-col w-full">
            <div class="flex flex-col w-full gap-1">
                <div class="flex flex-col w-full">
                    <text-area v-if="form.request_status === approved" v-model="form.approval_constraint" label="Approval Special Conditions" />
                    <text-area v-if="form.request_status === rejected" v-model="form.disapproved_remarks" label="Remarks for Disapproval" />
                </div>
                <submit-btn v-if="areRemarksUpdated" @click="handleUpdateApprovalBtn(form.request_status)" :disabled="model.api.processing">
                    <span v-if="model.api.processing">
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
                <span v-if="form.approved_by && form.request_status === approved" >Approved by: {{ form.approved_by }}</span>
                <span v-else-if="form.approved_by && form.request_status === rejected" >Rejected by: {{ form.approved_by }}</span>
                <span>Last updated: {{ formatDate(data.updated_at) }}</span>
            </div>
        <div class="flex">
            <form v-if="!!form"
                    @submit.prevent="handleUpdateApprovalBtn(rejected)"
                    :class="[ 'flex items-center gap-1 text-gray-900 w-fit px-2 py-1 rounded-l transition hover:scale-105', (form.request_status === rejected || model.api.processing) ? 'bg-gray-300 text-gray-500 cursor-not-allowed' : 'bg-red-400']"
                    title="Disapprove request">
                <button
                    type="submit"
                    :disabled="form.request_status === rejected || model.api.processing"
                    class="disabled:cursor-not-allowed"
                    aria-label="Reject request"
                >
                    <span v-if="model.api.processing && form.request_status === approved">Rejecting...</span>
                    <span v-else-if="form.request_status === rejected">Rejected</span>
                    <span v-else>Reject</span>
                </button>
            </form>

            <form v-if="!!form"
                    @submit.prevent="handleUpdateApprovalBtn(approved)"
                    :class="['flex items-center gap-1 text-gray-900 w-fit px-2 py-1 rounded-r transition hover:scale-105',(form.request_status === approved || model.api.processing)? 'bg-gray-300 text-gray-500 cursor-not-allowed': 'bg-green-400']"
                    title="Approve request">
                <button
                    type="submit"
                    :disabled="form.request_status === approved || model.api.processing"
                    class="disabled:cursor-not-allowed"
                    aria-label="Approve request"
                >
                    <span v-if="model.api.processing && form.request_status === rejected">Approving...</span>
                    <span v-else-if="form.request_status === approved">Approved</span>
                    <span v-else>Approve</span>
                </button>
            </form>
        </div>
        </div>
    </div>
</template>

<style scoped>

</style>
