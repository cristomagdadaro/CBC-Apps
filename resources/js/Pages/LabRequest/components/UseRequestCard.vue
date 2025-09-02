<script>
import ApiMixin from "@/Modules/mixins/ApiMixin.js";
import DataFormatterMixin from "@/Modules/mixins/DataFormatterMixin.js";
import DtoResponse from "@/Modules/dto/DtoResponse";
import RequestFormPivot from "@/Modules/domain/RequestFormPivot";
import Modal from "@/Components/Modal.vue";
import UseRequestApprovalBtn from "@/Pages/LabRequest/components/UseRequestApprovalBtn.vue";
import TextArea from "@/Components/TextArea.vue";

export default {
    name: "UseRequestCard",
    components: {TextArea, UseRequestApprovalBtn, Modal},
    mixins: [ApiMixin, DataFormatterMixin],
    data(){
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
        formsData(){
            if (this.updatedData && this.updatedData instanceof DtoResponse){
                return this.updatedData.data;
            }
            return this.data;
        }
    },
    methods: {
        confirmAction()
        {
            this.confirmDelete = true;
        },
        async refreshData(updatedData) {
            this.closeModal();
            this.$emit("updated", updatedData);
        },
        async handleDelete()
        {
            this.toDelete = { event_id : this.formsData.event_id };
            const response = await this.submitDelete();
            if (response instanceof DtoResponse)
            {
                this.confirmDelete = false;
                this.$emit("deletedModel", response.data);
            }
        },
        colorStatus(status)
        {
            if (status === "approved")
                return 'text-green-500';
            else if (status === "rejected")
                return 'text-red-500';
            else if (status === "pending")
                return 'text-yellow-500';
            else
                return ''
        },
        closeModal()
        {
            this.showModal = false;
        }
    },
}
</script>

<template>
    <div v-if="formsData" class="p-2 rounded-md flex flex-col gap-2 max-w-full min-w-[30rem] w-full shadow justify-between bg-gray-100 border overflow-x-auto">
        <div @click="showModal = true" class="flex flex-row p-2 rounded-md justify-between py-4 gap-1">
            <div class="flex flex-col">
                <label class="leading-none font-semibold">{{ formsData.requester?.fullName ?? 'NULL' }}</label>
                <p class="text-sm leading-snug break-all">
                    {{ `${formsData.requester?.position} - ${formsData.requester?.affiliation }` }}
                </p>
                <p class="text-sm leading-none break-all">
                    Date Created: {{ formatDate(formsData.created_at) }}
                </p>
            </div>
            <a :href="route('forms.generate.pdf', formsData.id)" class="btn btn-primary">Download PDF</a>
            <div class="flex flex-col items-center justify-center">
                <label class="text-xl leading-none font-[1000] uppercase" :class="colorStatus(formsData.request_status)">{{ formsData.request_status }}</label>
                <span class="text-[0.6rem] leading-none select-none">on {{ formatDate(formsData.updated_at) }}</span>
            </div>
        </div>
        <Modal
            :show="showModal"
            :closeable="true"
            @close="closeModal"
        >
            <div class="bg-white dark:bg-gray-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                <div class="sm:flex sm:items-start">
                    <div class="mt-3 text-center sm:text-start w-full">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 pb-2 uppercase text-center">
                            Access and Use Request Form
                        </h3>
                        <div class="flex flex-row bg-gray-200 p-2 rounded-md justify-between shadow py-4 gap-1l">
                            <div class="flex flex-col">
                                <label class="leading-none font-semibold">{{ formsData.requester?.fullName }}</label>
                                <p class="text-sm leading-snug break-all">
                                    {{ `${formsData.requester?.position} - ${formsData.requester?.affiliation}` }}
                                </p>
                                <p class="text-sm leading-none break-all">
                                    Date Created: {{ formatDate(formsData.created_at) }}
                                </p>
                            </div>
                            <div class="flex flex-col items-center justify-center">
                                <label class="text-xl leading-none font-[1000] uppercase" :class="colorStatus(formsData.request_status)">{{ formsData.request_status }}</label>
                                <span class="text-[0.6rem] leading-none select-none">on {{ formatDate(formsData.updated_at) }}</span>
                            </div>
                        </div>
                        <div class="mt-4 text-sm text-gray-600 dark:text-gray-400">
                            <div class="flex flex-col leading-tight p-2 h-full">
                                <div class="grid grid-cols-1">
                                    <span><b>Purpose of Request:</b> {{ formsData.requestForm?.request_purpose }}</span>
                                    <span><b>Project Title:</b> {{ formsData.requestForm?.project_title }}</span>
                                    <span><b>Requested Time and Date of Use:</b> {{ formatTime(formsData.requestForm?.time_of_use) }} {{ formatDate(formsData.requestForm?.date_of_use) }}</span>
                                    <span><b>Reviewed by:</b> {{ formsData.approved_by }}</span>
                                    <span><b>Details of Request:</b> {{ formsData.requestForm?.request_details }}</span>
                                </div>
                                <span class="text-left font-bold uppercase pt-3">Items Requested</span>
                                <table>
                                    <tbody>
                                        <tr v-if="formsData.requestForm?.labs_to_use">
                                            <th class="py-1 uppercase w-5 px-4 border text-left"><b>Laboratories</b></th>
                                            <td class="border">
                                                {{ arrayToString(formsData.requestForm?.labs_to_use) }}
                                            </td>
                                        </tr>
                                        <tr v-if="formsData.requestForm?.equipments_to_use">
                                            <th class="py-1 uppercase w-5 px-4 border text-left"><b>Equipments</b></th>
                                            <td class="border">
                                                {{ arrayToString(formsData.requestForm?.equipments_to_use) }}
                                            </td>
                                        </tr>
                                        <tr v-if="formsData.requestForm?.consumables_to_use">
                                            <th class="py-1 uppercase w-5 px-4 border text-left"><b>Consumables</b></th>
                                            <td class="border">
                                                {{ arrayToString(formsData.requestForm?.consumables_to_use) }}
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="flex flex-row justify-between px-6 py-4 bg-gray-100 dark:bg-gray-800 text-end items-center">
               <use-request-approval-btn :data="formsData" @updated="refreshData($event)" />
            </div>
        </Modal>
    </div>
</template>

<style scoped>

</style>
