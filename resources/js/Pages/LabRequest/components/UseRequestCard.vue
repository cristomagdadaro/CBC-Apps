<script>
import ApiMixin from "@/Modules/mixins/ApiMixin.js";
import DataFormatterMixin from "@/Modules/mixins/DataFormatterMixin.js";
import DtoResponse from "@/Modules/dto/DtoResponse";
import RequestFormPivot from "@/Modules/domain/RequestFormPivot";

export default {
    name: "UseRequestCard",
    mixins: [ApiMixin, DataFormatterMixin],
    data(){
        return {
            confirmDelete: false,
            updatedData: null,
            errors: null,
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
        }
    },
}
</script>

<template>
    <div v-if="formsData" class="p-2 rounded-md flex flex-col gap-2 max-w-full min-w-[30rem] w-full justify-between bg-gray-100 border overflow-x-auto">
        <div class="flex flex-row bg-gray-200 p-2 rounded-md justify-between shadow py-4 gap-1">
            <div class="flex flex-col">
                <label class="leading-none font-semibold">{{ formsData.requester.fullName }}</label>
                <p class="text-xs leading-snug break-all">
                    {{ `${formsData.requester.position} - ${formsData.requester.affiliation}` }}
                </p>
            </div>
            <div class="flex flex-col items-center justify-center">
                <label class="text-xl leading-none font-[1000] uppercase" :class="colorStatus(formsData.request_status)">{{ formsData.request_status }}</label>
                <span class="text-[0.6rem] leading-none select-none">Status</span>
            </div>
        </div>
        <div class="flex flex-col leading-tight">
           <div class="grid grid-cols-2">
               <span><b>Details:</b> {{ formsData.requestForm.request_details }}</span>
               <span><b>Purpose:</b> {{ formsData.requestForm.request_purpose }}</span>
               <span><b>Requested Date of Use:</b> {{ formatDate(formsData.requestForm.date_of_use) }}</span>
               <span><b>Requested Time of Use:</b> {{ formatTime(formsData.requestForm.time_of_use) }}</span>
               <span><b>Reviewed by:</b> {{ formsData.approved_by }}</span>
           </div>
            <span class="text-left font-bold uppercase pt-3">Items Requested</span>
            <span><b>Laboratories:</b> {{ arrayToString(formsData.requestForm.labs_to_use) }}</span>
            <span><b>Equipments:</b> {{ arrayToString(formsData.requestForm.equipments_to_use) }}</span>
            <span><b>Consumables:</b> {{ arrayToString(formsData.requestForm.consumables_to_use) }}</span>
        </div>
    </div>
</template>

<style scoped>

</style>
