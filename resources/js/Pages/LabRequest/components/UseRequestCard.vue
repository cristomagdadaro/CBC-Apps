<script>
import axios from "axios";
import ApiMixin from "@/Modules/mixins/ApiMixin.js";
import DataFormatterMixin from "@/Modules/mixins/DataFormatterMixin.js";
import DtoResponse from "@/Modules/dto/DtoResponse";
import RequestFormPivot from "@/Modules/domain/RequestFormPivot";
import UseRequestApprovalBtn from "@/Pages/LabRequest/components/UseRequestApprovalBtn.vue";
import DeleteIcon from "@/Components/Icons/DeleteIcon.vue";
import PrinterIcon from "@/Components/Icons/PrinterIcon.vue";

export default {
    name: "UseRequestCard",
    components: {DeleteIcon, UseRequestApprovalBtn, PrinterIcon},
    mixins: [ApiMixin, DataFormatterMixin],
    data(){
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
        formsData(){
            if (this.updatedData && this.updatedData instanceof DtoResponse){
                return this.updatedData.data;
            }
            return this.data;
        }
    },
    methods: {
        async handlePrint()
        {
            if (!this.formsData?.id || this.isPrinting) return;

            this.printError = null;
            this.printProgress = 0;
            this.isPrinting = true;
            this.showPrintModal = true;

            const baseUrl = route('forms.generate.pdf', this.formsData.id);
            const prefetchUrl = `${baseUrl}?prefetch=1&force_refresh=1`;

            let progressTimer = null;
            try {
                progressTimer = setInterval(() => {
                    if (this.printProgress < 90) {
                        this.printProgress += 5;
                    }
                }, 300);

                const response = await axios.get(prefetchUrl);

                if (response?.data?.ready) {
                    this.printProgress = 100;
                    const targetUrl = response.data.download_url ?? response.data.url;

                    const pdfResponse = await axios.get(targetUrl, { responseType: 'blob' });
                    const blob = new Blob([pdfResponse.data], { type: 'application/pdf' });
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

                    this.isPrinting = false;
                    this.showPrintModal = false;
                    this.printProgress = 0;
                } else {
                    this.printError = 'PDF is not ready yet.';
                }
            } catch (error) {
                this.printError = 'Failed to render PDF. Please try again.';
            } finally {
                if (progressTimer) clearInterval(progressTimer);
                if (this.printProgress < 100) {
                    this.isPrinting = false;
                }
            }
        },
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
    <div v-if="formsData" class="p-2 rounded-md flex flex-col gap-2 max-w-full min-w-[30rem] w-full shadow justify-between bg-gray-100 hover:bg-gray-300 border overflow-x-auto">
        <div @dblclick="showModal = true" class="relative h-full flex flex-row p-2 rounded-md justify-between py-4 gap-1 cursor-pointer">
            <div class="flex flex-col w-full">
                <label class="leading-none font-semibold">{{ formsData.requester?.fullName ?? 'NULL' }}</label>
                <p class="text-sm leading-snug break-all">
                    {{ `${formsData.requester?.position} - ${formsData.requester?.affiliation }` }}
                </p>
                <p class="text-sm leading-none break-all">
                    Date Created: {{ formatDate(formsData.created_at) }}
                </p>
            </div>
            <button
                v-if="formsData.request_status !== 'pending'"
                type="button"
                @click.prevent="handlePrint"
                class="btn btn-primary mx-5 my-auto"
            >
                <printer-icon class="duration-100 h-auto w-5 hover:text-green-600 hover:scale-110 active:scale-100" />
            </button>
            <div class="flex flex-col items-center justify-center">
                <label class="text-xl leading-none font-[1000] uppercase" :class="colorStatus(formsData.request_status)">{{ formsData.request_status }}</label>
                <span class="text-[0.6rem] leading-none ">on {{ formatDate(formsData.updated_at) }}</span>
            </div>
        </div>
        <Modal
            :show="showPrintModal"
            :closeable="!isPrinting"
            @close="showPrintModal = false"
        >
            <div class="bg-white dark:bg-gray-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                <div class="text-center w-full">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 pb-2">
                        Rendering PDF
                    </h3>
                    <div class="w-full bg-gray-200 rounded-full h-3 overflow-hidden">
                        <div
                            class="h-full bg-green-500 transition-all duration-300"
                            :style="{ width: `${printProgress}%` }"
                        ></div>
                    </div>
                    <p class="text-xs text-gray-500 mt-2">
                        {{ printProgress }}%
                    </p>
                    <p v-if="printError" class="text-xs text-red-500 mt-2">
                        {{ printError }}
                    </p>
                </div>
            </div>
        </Modal>
        <Modal
            :show="showModal"
            :closeable="true"
            @close="closeModal"
        >
            <div class="bg-white dark:bg-gray-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                <div class="sm:flex sm:items-start">
                    <div class="mt-3 text-center sm:text-start w-full">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 pb-2 uppercase text-center">
                            Facilities, Equipment, and Supplies Request Form
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
                                <span class="text-[0.6rem] leading-none ">on {{ formatDate(formsData.updated_at) }}</span>
                            </div>
                        </div>
                        <div class="mt-4 text-sm text-gray-600 dark:text-gray-400">
                            <div class="flex flex-col leading-tight p-2 h-full">
                                <div class="grid grid-cols-1">
                                    <span><b>Purpose of Request:</b> {{ formsData.requestForm?.request_purpose }}</span>
                                    <span><b>Project Title:</b> {{ formsData.requestForm?.project_title }}</span>
                                    <span><b>Requested Time and Date of Use:</b> {{ formatTime(formsData.requestForm?.time_of_use) }} {{ formatDate(formsData.requestForm?.date_of_use) }}</span>
                                    <span v-if="formsData.requestForm?.date_of_use_end || formsData.requestForm?.time_of_use_end"><b>Requested End Time and Date:</b> {{ formatTime(formsData.requestForm?.time_of_use_end) }} {{ formatDate(formsData.requestForm?.date_of_use_end) }}</span>
                                    <span><b>Reviewed by:</b> {{ formsData.approved_by }}</span>
                                    <span><b>Details of Request:</b> {{ formsData.requestForm?.request_details }}</span>
                                </div>
                                <span class="text-left font-bold uppercase pt-3">Items Requested</span>
                                <table>
                                    <tbody>
                                        <tr v-if="formsData.requestForm?.labs_to_use.length">
                                            <th class="py-1 uppercase w-5 px-4 border text-left"><b>Laboratories</b></th>
                                            <td class="border">
                                                {{ arrayToString(formsData.requestForm?.labs_to_use) }}
                                            </td>
                                        </tr>
                                        <tr v-if="formsData.requestForm?.equipments_to_use.length">
                                            <th class="py-1 uppercase w-5 px-4 border text-left"><b>Equipments</b></th>
                                            <td class="border">
                                                {{ arrayToString(formsData.requestForm?.equipments_to_use) }}
                                            </td>
                                        </tr>
                                        <tr v-if="formsData.requestForm?.consumables_to_use.length">
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
            <use-request-approval-btn :data="formsData" /> <!-- @updated="refreshData($event)" /> -->
            </div>
        </Modal>
    </div>
</template>

<style scoped>

</style>
