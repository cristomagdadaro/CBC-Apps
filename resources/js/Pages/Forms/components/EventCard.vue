<script>
import ApiMixin from "@/Modules/mixins/ApiMixin";
import Form from "@/Modules/domain/Form.js";
import Modal from "@/Components/Modal.vue";
import ConfirmationModal from "@/Components/ConfirmationModal.vue";
import DeleteBtn from "@/Components/Buttons/DeleteBtn.vue";
import CancelBtn from "@/Components/Buttons/CancelBtn.vue";
import SuspendFormBtn from "@/Pages/Forms/components/SuspendFormBtn.vue";
import TransitionContainer from "@/Components/Transitions/TransitionContrainer.vue";

export default {
    name: "EventCard",
    components: {TransitionContainer, SuspendFormBtn, CancelBtn, DeleteBtn, ConfirmationModal, Modal},
    computed: {
        Form() {
            return Form
        },
        formsData(){
            if (this.updatedData){
                return this.updatedData;
            }
            return this.data;
        }
    },
    mixins: [ApiMixin],
    beforeMount() {
        this.model = new Form();
    },
    data(){
        return {
            confirmDelete: false,
            updatedData: null,
        }
    },
    methods: {
        handleDelete()
        {
            this.confirmDelete = true;
        },
    },
}
</script>

<template>
    <div v-if="formsData" class="p-2 rounded-md flex flex-col gap-2 lg:max-w-2xl max-w-full min-w-[30rem] w-full justify-between overflow-x-auto" :class="formsData.is_suspended ? 'bg-yellow-100':'bg-gray-100 border'">
        <div class="flex flex-row bg-gray-200 p-2 rounded-md justify-between shadow py-4">
            <div class="flex flex-col justify-center">
                <label class="leading-none font-semibold">{{ formsData.title }}</label>
                <p class="text-xs leading-none">
                    {{ formsData.description }}
                </p>
            </div>
            <div class="flex flex-col items-center justify-center">
                <label class="text-xl leading-none font-[1000]">{{ formsData.event_id }}</label>
                <span class="text-[0.6rem] leading-none select-none">Event ID</span>
            </div>
        </div>
        <div class="grid grid-cols-2 grid-rows-2 px-1">
            <div>
                <span class="font-bold uppercase">Start Date: </span>
                <label>{{ formsData.date_from }}</label>
            </div>
            <div>
                <span class="font-bold uppercase">End Date: </span>
                <label>{{ formsData.date_to }}</label>
            </div>
            <div>
                <span class="font-bold uppercase">Start Time: </span>
                <label>{{ formsData.time_from }}</label>
            </div>
            <div>
                <span class="font-bold uppercase">End Time: </span>
                <label>{{ formsData.time_to }}</label>
            </div>
        </div>
        <div class="px-1">
            <div>
                <span class="font-bold uppercase">Venue: </span>
                <label>{{ formsData.venue }}</label>
            </div>
            <p class="text-sm leading-none">{{ formsData.details }}</p>
        </div>

            <div class="px-1 flex flex-row w-full">
                <transition-container type="slide-right" :duration="500">
                    <div v-show="!formsData.is_suspended" v-if="!formsData.is_suspended" class="w-full">
                        <label class="font-bold uppercase" title="Additional steps for the form">
                            Evaluation Requirements
                        </label>
                        <div class="flex justify-evenly">
                            <div class="flex items-center gap-1" title="Require guests to pre-register">
                                <input type="checkbox" class="rounded-full" :checked="formsData.has_preregistration">
                                <label>Preregistration</label>
                            </div>
                            <div class="flex items-center gap-1" title="Require guests to take pre-test">
                                <input type="checkbox" class="rounded-full" :checked="formsData.has_pretest">
                                <label>Pretest</label>
                            </div>
                            <div class="flex items-center gap-1" title="Require guests to take post-test">
                                <input type="checkbox" class="rounded-full" :checked="formsData.has_posttest">
                                <label>Posttest</label>
                            </div>
                        </div>
                    </div>
                </transition-container>
                <transition-container type="slide-left" :duration="500">
                    <div v-show="formsData.is_suspended" v-if="formsData.is_suspended" class="flex flex-col border-t p-2 bg-yellow-300 w-full">
                        <span class="font-bold uppercase leading-none text-center">This Form is suspended</span>
                        <span class="leading-none text-xs text-center">will not be able to accept request</span>
                    </div>
                </transition-container>
            </div>
        <div class="flex flex-col border-t p-2 bg-gray-200 rounded-md">
            <span class="font-bold uppercase text-center">Statistics</span>
            <div class="flex gap-1 justify-center">
                <div class="flex flex-col items-center border-r-2 border-gray-900 text-green-900 w-fit px-2 py-1">
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
                <a :href="route('forms.guest.index')+'/'+formsData.event_id" target="_blank" class="bg-green-200 text-green-900 w-fit px-2 py-1 rounded" title="Temporarily stop accepting responses">
                    Visit
                </a>

                <Link :href="route('forms.update')+'/'+formsData.event_id" class="bg-blue-200 text-blue-900 w-fit px-2 py-1 rounded" title="Temporarily stop accepting responses">
                    Modify
                </Link>

                <button class="bg-blue-600 text-blue-100 w-fit px-2 py-1 rounded" title="Temporarily stop accepting responses">
                    Registration
                </button>

                <button class="bg-cyan-200 text-cyan-900 w-fit px-2 py-1 rounded" title="Temporarily stop accepting responses">
                    Export
                </button>

                <suspend-form-btn :data="formsData" @updated="updatedData = $event.data" />

                <button @click="handleDelete"  class="bg-red-200 text-red-900 w-fit px-2 py-1 rounded" title="Permanently remove this form">
                    Delete
                </button>
            </div>
        </div>
        <confirmation-modal :show="confirmDelete" @close="confirmDelete = false">
            <template v-slot:title>
                Are you sure you want to delete this form?
            </template>

            <template v-slot:content>
                This will permanently delete <b>{{ formsData.title }}</b> form.
            </template>

            <template v-slot:footer>
               <div class="flex justify-between w-full">
                   <delete-btn @close="confirmDelete = false" @click="submitDelete" :class="{'animate-pulse':model.processing}">
                    <span v-if="!model.processing">
                        Confirm
                    </span>
                       <span v-else>
                        Deleting
                    </span>
                   </delete-btn>
                   <cancel-btn @click="confirmDelete = false">
                       Cancel
                   </cancel-btn>
               </div>
            </template>
        </confirmation-modal>
    </div>
</template>

<style scoped>

</style>
