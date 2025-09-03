<script>
import DateInput from "@/Components/DateInput.vue";
import ApiMixin from "@/Modules/mixins/ApiMixin";
import FormLocalMixin from "@/Modules/mixins/FormLocalMixin";
import TextInput from "@/Components/TextInput.vue";
import CustomDropdown from "@/Components/CustomDropdown/CustomDropdown.vue";
import Dropdown from "@/Components/Dropdown.vue";
import InputError from "@/Components/InputError.vue";
import DropdownLink from "@/Components/DropdownLink.vue";
import TransitionContainer from "@/Components/Transitions/TransitionContrainer.vue";
import TimeInput from "@/Components/TimeInput.vue";
import SubmitBtn from "@/Components/Buttons/SubmitBtn.vue";
import DtoResponse from "@/Modules/dto/DtoResponse";
import RequestFormPivot from "@/Modules/domain/RequestFormPivot";
import DataFormatterMixin from "@/Modules/mixins/DataFormatterMixin";

export default {
    name: "RequesterGuestCard",
    components: {SubmitBtn, TimeInput, TransitionContainer, DropdownLink, InputError, Dropdown, CustomDropdown, TextInput, DateInput },
    mixins: [ApiMixin, FormLocalMixin, DataFormatterMixin],
    data() {
        return {
            model: null,
            laboratories: [
                "Laboratory 1",
                "Laboratory 2",
                "Laboratory 3",
                "Laboratory 4",
            ],
          consumables: [
            "Consumable 1",
            "Consumable 2",
            "Consumable 3",
            "Consumable 4",
          ],
            equipments: [
            "qPCR",
            "Microscope",
            "Oven",
            "LED Wall",
          ], hallsandrooms: [
            "Plenary Hall",
            "Multi-purpose Hall",
            "Training Room",
            "Meeting Room 2nd Floor",
            "Consultants Room",
            "Prayer Room"
          ],
        };
    },
    methods: {
        async handleCreate() {
            const response = await this.submitCreate();
            if (response instanceof DtoResponse) {
                console.log(response);
                this.$emit('createdModel', response);
            }
        }
    },
    beforeMount() {
        this.model = new RequestFormPivot();
        this.setFormAction('create');
    },
};
</script>

<template>
    <div class="border p-2 md:rounded-md flex flex-col gap-2 bg-white max-w-2xl drop-shadow-lg">
        <form v-if="form" @submit.prevent="handleCreate()" class="px-2 py-4  md:rounded-md flex flex-col gap-2 bg-white">
        <div class="flex flex-col gap-2 w-full">
            <div class="w-full relative">
                <h2 class="flex justify-between items-center">
                    <span class="font-bold uppercase">Request Type:<b class="text-red-500 select-none">*</b></span>
                    <transition-container type="slide-bottom">
                        <InputError v-show="!!form.errors.request_type" :message="form.errors.request_type" />
                    </transition-container>
                </h2>
                <Dropdown align="right">
                    <template #trigger>
                        <button type="button" :class="{'border-red-500' : form.errors.request_type}" class="inline-flex items-center justify-between px-3 py-3 border border-gray-900 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-500 dark:text-gray-400 bg-white w-full dark:bg-gray-800 hover:text-gray-700 dark:hover:text-gray-300 focus:bg-gray-50 dark:focus:bg-gray-700 active:bg-gray-50 dark:active:bg-gray-700 transition ease-in-out duration-150">
                            {{ form.request_type ?? 'Select' }}
                            <svg class="ms-2 -me-0.5 h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 15L12 18.75 15.75 15m-7.5-6L12 5.25 15.75 9" />
                            </svg>
                        </button>
                    </template>

                    <template #content>
                        <div>
                            <DropdownLink as="button" @click.prevent="form.request_type = 'Supplies'" @click="form.clearErrors('request_type')">
                                <div class="flex items-center gap-1">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-boxes text-blue-300" viewBox="0 0 16 16">
                                        <path d="M7.752.066a.5.5 0 0 1 .496 0l3.75 2.143a.5.5 0 0 1 .252.434v3.995l3.498 2A.5.5 0 0 1 16 9.07v4.286a.5.5 0 0 1-.252.434l-3.75 2.143a.5.5 0 0 1-.496 0l-3.502-2-3.502 2.001a.5.5 0 0 1-.496 0l-3.75-2.143A.5.5 0 0 1 0 13.357V9.071a.5.5 0 0 1 .252-.434L3.75 6.638V2.643a.5.5 0 0 1 .252-.434zM4.25 7.504 1.508 9.071l2.742 1.567 2.742-1.567zM7.5 9.933l-2.75 1.571v3.134l2.75-1.571zm1 3.134 2.75 1.571v-3.134L8.5 9.933zm.508-3.996 2.742 1.567 2.742-1.567-2.742-1.567zm2.242-2.433V3.504L8.5 5.076V8.21zM7.5 8.21V5.076L4.75 3.504v3.134zM5.258 2.643 8 4.21l2.742-1.567L8 1.076zM15 9.933l-2.75 1.571v3.134L15 13.067zM3.75 14.638v-3.134L1 9.933v3.134z"/>
                                    </svg>
                                    <span>Request for Supplies</span>
                                </div>
                            </DropdownLink>
                            <DropdownLink as="button" @click.prevent="form.request_type = 'Equipments'" @click="form.clearErrors('request_type')">
                                <div class="flex items-center gap-1">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-pc-display-horizontal text-red-300" viewBox="0 0 16 16">
                                        <path d="M1.5 0A1.5 1.5 0 0 0 0 1.5v7A1.5 1.5 0 0 0 1.5 10H6v1H1a1 1 0 0 0-1 1v3a1 1 0 0 0 1 1h14a1 1 0 0 0 1-1v-3a1 1 0 0 0-1-1h-5v-1h4.5A1.5 1.5 0 0 0 16 8.5v-7A1.5 1.5 0 0 0 14.5 0zm0 1h13a.5.5 0 0 1 .5.5v7a.5.5 0 0 1-.5.5h-13a.5.5 0 0 1-.5-.5v-7a.5.5 0 0 1 .5-.5M12 12.5a.5.5 0 1 1 1 0 .5.5 0 0 1-1 0m2 0a.5.5 0 1 1 1 0 .5.5 0 0 1-1 0M1.5 12h5a.5.5 0 0 1 0 1h-5a.5.5 0 0 1 0-1M1 14.25a.25.25 0 0 1 .25-.25h5.5a.25.25 0 1 1 0 .5h-5.5a.25.25 0 0 1-.25-.25"/>
                                    </svg>
                                    <span>Request for Equipments</span>
                                </div>
                            </DropdownLink>
                            <DropdownLink as="button" @click.prevent="form.request_type = 'Laboratory Access'" @click="form.clearErrors('request_type')">
                                <div class="flex items-center gap-1">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-key-fill text-yellow-300" viewBox="0 0 16 16">
                                        <path d="M3.5 11.5a3.5 3.5 0 1 1 3.163-5H14L15.5 8 14 9.5l-1-1-1 1-1-1-1 1-1-1-1 1H6.663a3.5 3.5 0 0 1-3.163 2M2.5 9a1 1 0 1 0 0-2 1 1 0 0 0 0 2"/>
                                    </svg>
                                    <span>Laboratory Access Request</span>
                                </div>
                            </DropdownLink>
                            <DropdownLink as="button" @click.prevent="form.request_type = 'Event Halls Access'" @click="form.clearErrors('request_type')">
                                <div class="flex items-center gap-1">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-buildings-fill" viewBox="0 0 16 16">
                                        <path d="M15 .5a.5.5 0 0 0-.724-.447l-8 4A.5.5 0 0 0 6 4.5v3.14L.342 9.526A.5.5 0 0 0 0 10v5.5a.5.5 0 0 0 .5.5h9a.5.5 0 0 0 .5-.5V14h1v1.5a.5.5 0 0 0 .5.5h3a.5.5 0 0 0 .5-.5zM2 11h1v1H2zm2 0h1v1H4zm-1 2v1H2v-1zm1 0h1v1H4zm9-10v1h-1V3zM8 5h1v1H8zm1 2v1H8V7zM8 9h1v1H8zm2 0h1v1h-1zm-1 2v1H8v-1zm1 0h1v1h-1zm3-2v1h-1V9zm-1 2h1v1h-1zm-2-4h1v1h-1zm3 0v1h-1V7zm-2-2v1h-1V5zm1 0h1v1h-1z"/>
                                    </svg>
                                    <span>Event Halls Access Request</span>
                                </div>
                            </DropdownLink>
                        </div>
                    </template>
                </Dropdown>
            </div>
            <h2>
                <span class="font-bold uppercase">Requestor Information: </span>
            </h2>
            <TextInput
                id="name"
                v-model="form.name"
                required
                type="text"
                :error="form.errors.name"
                label="Full Name"
                placeholder="Juan Dela Cruz"
                autocomplete="name"
                @input="form.clearErrors('name')"
            />
            <TextInput
                id="name"
                v-model="form.position"
                type="text"
                :error="form.errors.position"
                label="Position"
                placeholder="SRS I, Student"
                autocomplete="position"
                @input="form.clearErrors('position')"
            />
            <TextInput
                id="affiliation"
                v-model="form.affiliation"
                required
                type="text"
                :error="form.errors.affiliation"
                label="Affiliation/Agency/Office"
                placeholder="Office Name"
                autocomplete="affiliation"
                @input="form.clearErrors('affiliation')"
            />
            <TextInput
                id="phone"
                v-model="form.phone"
                required
                type="text"
                :error="form.errors.phone"
                label="Contact Number"
                placeholder="0900 000 000"
                autocomplete="phone"
                @input="form.clearErrors('phone')"
            />
            <TextInput
                id="email"
                v-model="form.email"
                required
                type="email"
                :error="form.errors.email"
                label="Email Address"
                placeholder="sample@email.com"
                autocomplete="email"
                @input="form.clearErrors('email')"
            />
        </div>
        <div class="flex flex-col gap-2">
            <span class="font-bold uppercase">Request Form: </span>
            <TextInput
                id="request_purpose"
                v-model="form.request_purpose"
                required
                type="text"
                :error="form.errors.request_purpose"
                label="Request Purpose"
                placeholder="Reason or purpose of your request"
                autocomplete="request_purpose"
                @input="form.clearErrors('request_purpose')"
            />
            <TextInput
                id="request_details"
                v-model="form.request_details"
                type="text"
                :error="form.errors.request_details"
                label="Request Details"
                placeholder="If applicable"
                autocomplete="request_details"
                @input="form.clearErrors('request_details')"
            />
            <TextInput
                id="project_title"
                v-model="form.project_title"
                type="text"
                :error="form.errors.project_title"
                label="Project Title"
                placeholder="If project related request"
                autocomplete="project_title"
                @input="form.clearErrors('project_title')"
            />
            <div class="flex gap-2">
                <DateInput
                    id="date_of_use"
                    v-model="form.date_of_use"
                    required
                    type="text"
                    :error="form.errors.date_of_use"
                    label="Date of Use"
                    autocomplete="date_of_use"
                    @input="form.clearErrors('date_of_use')"
                />
                <TimeInput
                    id="time_of_use"
                    v-model="form.time_of_use"
                    required
                    type="text"
                    :error="form.errors.time_of_use"
                    label="Time of Use"
                    autocomplete="time_of_use"
                    @input="form.clearErrors('time_of_use')"
                />
            </div>
        </div>
        <div class="flex flex-col gap-2">
            <h2>
                <span class="font-bold uppercase">Supplies: </span>
            </h2>
            <div class="grid grid-cols-2 gap-3">
                <div v-for="item in consumables" class="flex items-center gap-1" title="Require guests to pre-register">
                  <input
                      type="checkbox"
                      class="rounded-full"
                      :value="item"
                      @change="toggleOption('consumables_to_use', item, $event.target.checked)"
                  >
                  <label>{{ item }}</label>
                </div>
            </div>
        </div>
        <div class="flex flex-col gap-2">
            <h2>
                <span class="font-bold uppercase">Equipments: </span>
            </h2>
            <div class="grid grid-cols-2 gap-3">
                <div v-for="item in equipments" class="flex items-center gap-1" title="Require guests to pre-register">
                  <input
                      type="checkbox"
                      class="rounded-full"
                      :value="item"
                      @change="toggleOption('equipments_to_use', item, $event.target.checked)"
                  >
                  <label>{{ item }}</label>
                </div>
            </div>
        </div>
        <div class="flex flex-col gap-2">
            <h2>
                <span class="font-bold uppercase">Laboratory Facilities: </span>
            </h2>
            <div class="grid grid-cols-2 gap-3">
                <div v-for="item in laboratories" class="flex items-center gap-1" title="Require guests to pre-register">
                  <input
                      type="checkbox"
                      class="rounded-full"
                      :value="item"
                      @change="toggleOption('labs_to_use', item, $event.target.checked)"
                  >
                  <label>{{ item }}</label>
                </div>
            </div>
        </div>
        <div class="flex flex-col gap-2">
            <h2>
                <span class="font-bold uppercase">Event Halls and Rooms: </span>
            </h2>
            <div class="grid grid-cols-2 gap-3">
                <div v-for="item in hallsandrooms" class="flex items-center gap-1" title="Require guests to pre-register">
                  <input
                      type="checkbox"
                      class="rounded-full"
                      :value="item"
                      @change="toggleOption('labs_to_use', item, $event.target.checked)"
                  >
                  <label>{{ item }}</label>
                </div>
            </div>
        </div>
        <div class="flex flex-col gap-3 text-sm leading-tight text-justify">
            <h2>
                <span class="font-bold uppercase">Terms & Conditions: </span>
            </h2>
            <div class="flex items-center gap-1" title="Require guests to pre-register">
                <input
                    type="checkbox"
                    @change="form.agreed_clause_1 = $event.target.checked"
                >
                <span>I hereby acknowledge that I will utilize the supply/equipment/laboratory at my own risk; and agree to use it responsibly and in accordance with any provided instructions or safety guidelines. <span v-if="form.errors.agreed_clause_1" class="text-red-500">{{ form.errors.agreed_clause_1 }}</span></span>
            </div>
            <div class="flex items-center gap-1" title="Require guests to pre-register">
                <input
                    type="checkbox"
                    @change="form.agreed_clause_2 = $event.target.checked"
                >
                <span>I agree to assume full responsibility for any damage or loss of the equipment while it is in my possession. <span v-if="form.errors.agreed_clause_2" class="text-red-500">{{ form.errors.agreed_clause_2 }}</span></span>
            </div>
            <div class="flex items-center gap-1" title="Require guests to pre-register">
                <input
                    type="checkbox"
                    @change="form.agreed_clause_3 = $event.target.checked"
                >
                <span>I agree that the Center shall not be held liable for the quality, accuracy, reliability, or completeness of any data generated by the Requestor using the lab’s facilities, equipment, or resources. The Requestor assumes full responsibility for the design, execution, and interpretation of the experiments and the data derived therefrom. The Center makes no warranties, express or implied, regarding the outcomes of the Requestor’s research activities. <span v-if="form.errors.agreed_clause_1" class="text-red-500">{{ form.errors.agreed_clause_1 }}</span></span>
            </div>
        </div>
        <submit-btn :disabled="model.api.processing" :processing="model.api.processing">
            <span v-if="!model.api.processing">Register</span>
            <span v-else>Registering</span>
        </submit-btn>
    </form>
    </div>
</template>

<style scoped>

</style>
