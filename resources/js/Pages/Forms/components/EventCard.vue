<script>
import ApiMixin from "@/Modules/mixins/ApiMixin";
import Form from "@/Modules/domain/Form.js";
import Modal from "@/Components/Modal.vue";
import ConfirmationModal from "@/Components/ConfirmationModal.vue";
import DeleteBtn from "@/Components/Buttons/DeleteBtn.vue";
import CancelBtn from "@/Components/Buttons/CancelBtn.vue";
import SuspendFormBtn from "@/Pages/Forms/components/SuspendFormBtn.vue";
import TransitionContainer from "@/Components/Transitions/TransitionContrainer.vue";
import DtoResponse from "@/Modules/dto/DtoResponse";
import DataFormatterMixin from "@/Modules/mixins/DataFormatterMixin";
import LoaderIcon from "@/Components/Icons/LoaderIcon.vue";
import Participant from "@/Modules/domain/Participant";
import RequirementsManager from "@/Components/Forms/RequirementsManager.vue";
import DeleteConfirmationModal from "@/Components/DeleteConfirmationModal.vue";
export default {
    name: "EventCard",
    components: {
        DeleteConfirmationModal,
        RequirementsManager,
        LoaderIcon, TransitionContainer, SuspendFormBtn, CancelBtn, DeleteBtn, ConfirmationModal, Modal},
    computed: {
        Form() {
            return Form
        },
        formsData(){
            if (this.updatedData && this.updatedData instanceof DtoResponse){
                return this.updatedData.data;
            }
            return this.data;
        }
    },
    mixins: [ApiMixin, DataFormatterMixin],
    beforeMount() {
        this.model = new Form();
        //start timer to determine if timestamp has expired
        this.startCountdown();
    },
    data(){
        return {
            confirmDelete: false,
            updatedData: null,
            errors: null,
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
        async handleExport(eventId, filename)
        {
            this.model = new Participant();
            this.setFormAction('get');
            this.form.filter = 'event_id';
            this.form.search = eventId;
            this.form.is_exact = true;

            const response = await this.fetchData();
            await this.exportCSV(response.data, filename);
            this.model = new Form();
        }
    },
}
</script>

<template>
    <div v-if="formsData" class="p-2 rounded-md flex flex-col gap-2 lg:max-w-2xl max-w-full min-w-[30rem] w-full justify-between bg-gray-100 border overflow-x-auto">
        <div class="flex flex-row bg-gray-200 p-2 rounded-md justify-between shadow py-4 gap-1">
            <div class="flex flex-col min-h-[3rem]">
                <label class="leading-none font-semibold">{{ formsData.title }}</label>
                <p class="text-xs leading-snug break-all">
                    {{ formsData.description }}
                </p>
            </div>
            <div class="flex flex-col items-center justify-center">
                <label class="lg:text-4xl text-2xl leading-none font-[1000]">{{ formsData.event_id }}</label>
                <span class="text-[0.6rem] leading-none select-none">Event ID</span>
            </div>
        </div>
        <div class="grid grid-cols-2 grid-rows-2 px-1">
            <div>
                <span class="font-bold uppercase">Start Date: </span>
                <label>{{ formatDate(formsData.date_from) }}</label>
            </div>
            <div>
                <span class="font-bold uppercase">End Date: </span>
                <label>{{ formatDate(formsData.date_to) }}</label>
            </div>
            <div>
                <span class="font-bold uppercase">Start Time: </span>
                <label>{{ formatTime(formsData.time_from) }}</label>
            </div>
            <div>
                <span class="font-bold uppercase">End Time: </span>
                <label>{{ formatTime(formsData.time_to) }}</label>
            </div>
        </div>
        <div class="px-1 min-h-[4rem]">
            <div class="line-clamp-1">
                <span class="font-bold uppercase">Venue: </span>
                <label class="leading-snug break-all">{{ formsData.venue }}</label>
            </div>
            <p class="text-sm leading-none line-clamp-3 break-all">{{ formsData.details }}</p>
        </div>
        <div v-if="isExpired" class="px-1 flex w-full">
            <div v-show="isExpired" class="relative w-full min-w-full">
                <div class="flex flex-col border-t p-2 bg-gray-600 w-full text-white" >
                    <span class="font-bold uppercase leading-none text-center">This Form is expired</span>
                    <span class="leading-none text-xs text-center">adjust date or time to reopen</span>
                </div>
            </div>
        </div>
        <div v-else class="flex w-full">
            <transition-container type="slide-right" :duration="1000">
                <div v-show="formsData.requirements.length" v-if="formsData.requirements.length" class="relative w-full min-w-full gap-2 flex flex-wrap">
                    <span class="font-bold uppercase">Requirements: </span>
                    <div v-for="data in formsData.requirements" v-bind:key="data.id" class="w-fit gap-2 flex items-center uppercase font-bold">
                        <input type="checkbox" class="rounded-full" :checked="true" disabled>
                        <label>{{ data.form_type }}</label>
                    </div>
                </div>
            </transition-container>
            <transition-container type="slide-right" :duration="1000">
                <div v-show="formsData.is_suspended" v-if="formsData.is_suspended" class="relative w-full min-w-full bg-yellow-300 rounded-md">
                    <div class="flex flex-col border-t p-2" >
                        <span class="font-bold uppercase leading-none text-center">This Form is suspended</span>
                        <span class="leading-none text-xs text-center">unable to accept request</span>
                    </div>
                </div>
            </transition-container>
        </div>
        <div class="flex flex-col border-t p-2 bg-gray-200 rounded-md">
            <span class="font-bold uppercase text-center">Statistics</span>
            <div class="flex gap-1 justify-center">
                <div :class="{'text-red-600':  data.max_slots > 0 && data.participants_count >= data.max_slots}" class="flex flex-col items-center border-r-2 border-gray-900 text-green-900 w-fit px-2 py-1">
                    <label class="text-xl leading-none font-[1000]">{{ formsData.max_slots > 0 ? formsData.max_slots : 'No' }}</label>
                    <span class="text-[0.6rem] leading-none select-none">Limit</span>
                </div>

                <div :class="{'text-red-600': data.max_slots > 0 &&data.responses_count >= data.responses_count}" class="flex flex-col items-center border-r-2 border-gray-900 text-green-900 w-fit px-2 py-1">
                    <label class="text-xl leading-none font-[1000]">{{ formsData.responses_count ?? 0 }}</label>
                    <span class="text-[0.6rem] leading-none select-none">Responses</span>
                </div>

                <div :class="{'text-red-600': data.max_slots > 0 &&data.participants_count >= data.max_slots}" class="flex flex-col items-center border-r-2 border-gray-900 text-green-900 w-fit px-2 py-1">
                    <label class="text-xl leading-none font-[1000]">{{ formsData.participants_count ?? 0 }}</label>
                    <span class="text-[0.6rem] leading-none select-none">Registered Participants</span>
                </div>

                <div class="flex flex-col items-center border-r-2 border-gray-900 text-green-900 w-fit px-2 py-1">
                    <label class="text-xl leading-none font-[1000]">{{ formsData.pretests_count ?? 0 }}</label>
                    <span class="text-[0.6rem] leading-none select-none">Pretest Responses</span>
                </div>

                <div class="flex flex-col items-center text-green-900 w-fit px-2 py-1">
                    <label class="text-xl leading-none font-[1000]">{{ formsData.posttests_count ?? 0 }}</label>
                    <span class="text-[0.6rem] leading-none select-none">Posttest Responses</span>
                </div>
            </div>
        </div>
        <div class="flex flex-col p-2">
            <div class="flex gap-1 justify-center">
                <a :href="route('forms.guest.index')+'/'+formsData.event_id" target="_blank" class="bg-green-200 text-green-900 w-fit px-2 py-1 rounded" title="Preview form">
                    Visit
                </a>

                <Link :href="route('forms.update')+'/'+formsData.event_id" class="bg-blue-200 text-blue-900 w-fit px-2 py-1 rounded" title="Modify details in the form">
                    Modify
                </Link>

                <button class="hidden bg-blue-600 text-blue-100 w-fit px-2 py-1 rounded" title="Manually register poarticipants">
                    Registration
                </button>

                <button @click.prevent="handleExport(formsData.event_id, `${formsData.title} (${formsData.event_id})`)" class="bg-cyan-200 text-cyan-900 w-fit px-2 py-1 rounded" title="Download form data in csv format">
                    Export
                </button>

                <suspend-form-btn v-if="!isExpired" :data="formsData" @updated="updatedData = $event" @failedUpdate="errors = $event"/>

                <button @click="confirmAction"  class="bg-red-200 text-red-900 w-fit px-2 py-1 rounded" title="Permanently remove this form">
                    Delete
                </button>
            </div>
            <label class="text-xs text-center text-red-600">{{errors?.toObject()?.message}}</label>
        </div>
        <delete-confirmation-modal
            :show="confirmDelete"
            :is-processing="model.api.processing"
            title="Confirm Delete Form"
            :message="`This will permanently delete the form. This action cannot be undone.`"
            :item-name="formsData.title"
            @confirm="handleDelete"
            @close="confirmDelete = false"
        />
    </div>
</template>

<style scoped>

</style>
