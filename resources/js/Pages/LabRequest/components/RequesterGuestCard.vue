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
import TagifyInput from "@/Components/Tagify.vue";
import ProgressTabs from "@/Components/ProgressTabs.vue";
import Personnel from "@/Pages/Inventory/Personnel/components/model/Personnel";
import SuccessModal from "@/Components/SuccessModal.vue";

export default {
    name: "RequesterGuestCard",
    components: {
        TagifyInput,
        SubmitBtn, TimeInput, TransitionContainer, DropdownLink, InputError, Dropdown, CustomDropdown, TextInput, DateInput,
        ProgressTabs,
        SuccessModal,
    },
    mixins: [ApiMixin, FormLocalMixin, DataFormatterMixin],
    data() {
        return {
            model: null,
            employee_id: '',
            searchLoading: false,
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
            ],
            hallsandrooms: [
                "Plenary Hall",
                "Multi-purpose Hall",
                "Training Room",
                "Meeting Room 2nd Floor",
                "Consultants Room",
                "Prayer Room"
            ],
            steps: [
                { key: 'request_type', label: 'Request Type' },
                { key: 'requestor', label: 'Requestor Info' },
                { key: 'details', label: 'Request Details' },
                { key: 'supplies', label: 'Supplies' },
                { key: 'equipments', label: 'Equipments' },
                { key: 'labs', label: 'Laboratory Facilities' },
                { key: 'terms', label: 'Terms & Conditions' },
            ],
            currentStep: 0,
            clientErrors: {},
            showSuccessModal: false,
            successMessage: 'Your request has been submitted successfully.',
        };
    },
    methods: {
        async handleCreate() {
            const response = await this.submitCreate();
            if (response instanceof DtoResponse) {
                this.showSuccessModal = true;
                this.$emit('createdModel', response);
            }
        },
        async searchPersonnel() {
            this.clientErrors = { ...this.clientErrors, employee_id: null };

            if (!this.employee_id) {
                this.clientErrors.employee_id = 'PhilRice ID is required';
                return;
            }

            this.searchLoading = true;
            try {
                const response = await this.fetchGetApi('api.inventory.personnels.index.guest', {
                    filter: 'employee_id',
                    search: this.employee_id,
                    is_exact: true,
                }, Personnel);

                const payload = response?.data ?? response ?? [];
                const record = Array.isArray(payload?.data ?? payload)
                    ? (payload.data ?? payload)[0]
                    : (payload.data ?? payload);

                if (!record) {
                    this.clientErrors.employee_id = 'No personnel found for this ID';
                    return;
                }

                const fullName = record.fullName;

                this.form.name = fullName || this.form.name;
                this.form.position = record.position ?? this.form.position;
                this.form.phone = record.phone ?? this.form.phone;
                this.form.email = record.email ?? this.form.email;
                this.form.affiliation = "Philippine Rice Research Institute";
                delete this.clientErrors.employee_id;
                this.form.clearErrors('employee_id');
            } catch (error) {
                console.error(error);
                this.clientErrors.employee_id = 'Lookup failed. Please try again.';
            } finally {
                this.searchLoading = false;
            }
        },
        validateStep(index) {
            this.clientErrors = {};
            const f = this.form || {};
            const required = (field, label) => {
                if (!f[field]) this.clientErrors[field] = `${label} is required`;
            };
            const stepKey = this.filteredSteps[index]?.key;
            if (stepKey === 'request_type') {
                required('request_type', 'Request type');
            } else if (stepKey === 'requestor') {
                required('name', 'Full name');
                required('affiliation', 'Affiliation');
                required('phone', 'Contact number');
                required('email', 'Email');
            } else if (stepKey === 'details') {
                required('request_purpose', 'Request purpose');
                required('date_of_use', 'Date of use');
                required('time_of_use', 'Time of use');
            } else if (stepKey === 'terms') {
                if (!f.agreed_clause_1) this.clientErrors['agreed_clause_1'] = 'You must agree to this clause';
                if (!f.agreed_clause_2) this.clientErrors['agreed_clause_2'] = 'You must agree to this clause';
                if (!f.agreed_clause_3) this.clientErrors['agreed_clause_3'] = 'You must agree to this clause';
            }
            return Object.keys(this.clientErrors).length === 0;
        },
        nextStep() {
            if (this.validateStep(this.currentStep)) {
                this.currentStep = Math.min(this.currentStep + 1, this.filteredSteps.length - 1);
            }
        },
        prevStep() {
            this.currentStep = Math.max(this.currentStep - 1, 0);
        },
        handleStepChange(target) {
            if (target < 0 || target >= this.filteredSteps.length) return;
            if (target <= this.currentStep) {
                this.currentStep = target;
                return;
            }
            // Attempt to move forward if current step is valid
            if (this.validateStep(this.currentStep)) {
                this.currentStep = target;
            }
        },
        hasErr(field) {
            return this.clientErrors[field] || (this.form?.errors?.[field]);
        },
        errMsg(field) {
            return this.clientErrors[field] || this.form?.errors?.[field] || '';
        },
    },
    computed: {
        filteredSteps() {
            const type = this.form?.request_type;
            return this.steps.filter(step => {
                if (['request_type', 'requestor', 'details', 'terms'].includes(step.key)) return true;
                if (step.key === 'supplies') return type === 'Supplies';
                if (step.key === 'equipments') return type === 'Equipments';
                if (step.key === 'labs') return type === 'Laboratory Access' || type === 'Event Halls Access';
                return false;
            });
        },
        stepLabels() {
            return this.filteredSteps.map(s => s.label);
        },
        currentStepKey() {
            return this.filteredSteps[this.currentStep]?.key;
        }
    },
    watch: {
        'form.request_type'() {
            if (this.currentStep >= this.filteredSteps.length) {
                this.currentStep = this.filteredSteps.length - 1;
            }
            if (this.currentStep < 0) this.currentStep = 0;
        }
    },
    beforeMount() {
        this.model = new RequestFormPivot();
        this.setFormAction('create');
    },
};
</script>

<template>
    <div class="border p-2 md:rounded-md flex flex-col gap-2 bg-white w-full drop-shadow-lg">
        <SuccessModal
            :show="showSuccessModal"
            title="Request submitted"
            :message="successMessage"
            @close="showSuccessModal = false"
        />
        <div class="px-2 pt-4 overflow-x-auto">
            <ProgressTabs :steps="stepLabels" :current="currentStep" @update:current="handleStepChange" />
        </div>
        <form v-if="form" @submit.prevent="handleCreate()" class="px-2 py-0  md:rounded-md flex flex-col gap-4 bg-white">
            <!-- Step 0: Request Type -->
            <div v-show="currentStepKey === 'request_type'" class="flex flex-col gap-2 w-full">
                <div class="w-full relative">
                    <h2 class="flex justify-between items-center">
                        <span class="font-bold uppercase">Request Type:<b class="text-red-500 select-none">*</b></span>
                        <transition-container type="slide-bottom">
                            <InputError v-show="!!hasErr('request_type')" :message="errMsg('request_type')" />
                        </transition-container>
                    </h2>
                    <Dropdown align="right">
                        <template #trigger>
                            <button type="button" :class="{'border-red-500' : hasErr('request_type')}" class="inline-flex items-center justify-between px-3 py-3 border border-gray-900 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-500 dark:text-gray-400 bg-white w-full dark:bg-gray-800 hover:text-gray-700 dark:hover:text-gray-300 focus:bg-gray-50 dark:focus:bg-gray-700 active:bg-gray-50 dark:active:bg-gray-700 transition ease-in-out duration-150">
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
                                    <span class="leading-none">Request for Supplies</span>
                                    </div>
                                </DropdownLink>
                                <DropdownLink as="button" @click.prevent="form.request_type = 'Equipments'" @click="form.clearErrors('request_type')">
                                    <div class="flex items-center gap-1">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-pc-display-horizontal text-red-300" viewBox="0 0 16 16">
                                            <path d="M1.5 0A1.5 1.5 0 0 0 0 1.5v7A1.5 1.5 0 0 0 1.5 10H6v1H1a1 1 0 0 0-1 1v3a1 1 0 0 0 1 1h14a1 1 0 0 0 1-1v-3a1 1 0 0 0-1-1h-5v-1h4.5A1.5 1.5 0 0 0 16 8.5v-7A1.5 1.5 0 0 0 14.5 0zm0 1h13a.5.5 0 0 1 .5.5v7a.5.5 0 0 1-.5.5h-13a.5.5 0 0 1-.5-.5v-7a.5.5 0 0 1 .5-.5M12 12.5a.5.5 0 1 1 1 0 .5.5 0 0 1-1 0m2 0a.5.5 0 1 1 1 0 .5.5 0 0 1-1 0M1.5 12h5a.5.5 0 0 1 0 1h-5a.5.5 0 0 1 0-1M1 14.25a.25.25 0 0 1 .25-.25h5.5a.25.25 0 1 1 0 .5h-5.5a.25.25 0 0 1-.25-.25"/>
                                        </svg>
                                    <span class="leading-none">Request for Equipments</span>
                                    </div>
                                </DropdownLink>
                                <DropdownLink as="button" @click.prevent="form.request_type = 'Laboratory Access'" @click="form.clearErrors('request_type')">
                                    <div class="flex items-center gap-1">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-key-fill text-yellow-300" viewBox="0 0 16 16">
                                            <path d="M3.5 11.5a3.5 3.5 0 1 1 3.163-5H14L15.5 8 14 9.5l-1-1-1 1-1-1-1 1-1-1-1 1H6.663a3.5 3.5 0 0 1-3.163 2M2.5 9a1 1 0 1 0 0-2 1 1 0 0 0 0 2"/>
                                        </svg>
                                        <span class="leading-none">Laboratory Access Request</span>
                                    </div>
                                </DropdownLink>
                                <DropdownLink as="button" @click.prevent="form.request_type = 'Event Halls Access'" @click="form.clearErrors('request_type')">
                                    <div class="flex items-center gap-1">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-buildings-fill" viewBox="0 0 16 16">
                                            <path d="M15 .5a.5.5 0 0 0-.724-.447l-8 4A.5.5 0 0 0 6 4.5v3.14L.342 9.526A.5.5 0 0 0 0 10v5.5a.5.5 0 0 0 .5.5h9a.5.5 0 0 0 .5-.5V14h1v1.5a.5.5 0 0 0 .5.5h3a.5.5 0 0 0 .5-.5zM2 11h1v1H2zm2 0h1v1H4zm-1 2v1H2v-1zm1 0h1v1H4zm9-10v1h-1V3zM8 5h1v1H8zm1 2v1H8V7zM8 9h1v1H8zm2 0h1v1h-1zm-1 2v1H8v-1zm1 0h1v1h-1zm3-2v1h-1V9zm-1 2h1v1h-1zm-2-4h1v1h-1zm3 0v1h-1V7zm-2-2v1h-1V5zm1 0h1v1h-1z"/>
                                        </svg>
                                        <span class="leading-none">Event Halls Access Request</span>
                                    </div>
                                </DropdownLink>
                            </div>
                        </template>
                    </Dropdown>
                </div>
            </div>

            <!-- Step 1: Requestor Information -->
            <div v-show="currentStepKey === 'requestor'" class="flex flex-col gap-2">
                <h2>
                    <span class="font-bold uppercase">Requestor Information: </span>
                </h2>
        
                <div class="flex items-end gap-2">
                    <TextInput id="employee_id" v-model="employee_id" type="text" :error="errMsg('employee_id')" label="PhilRice ID" placeholder="**-****" name="employee_id" autocomplete="employee_id" @input="form.clearErrors('employee_id')"/>
                    <button type="button" class="px-3 py-2 rounded bg-gray-800 text-white text-sm hover:bg-gray-900 disabled:opacity-50" :disabled="searchLoading" @click="searchPersonnel">
                        <span v-if="!searchLoading">Search</span>
                        <span v-else>Searching…</span>
                    </button>
                </div>
                
                <br class="border" />
                
                <TextInput id="name" v-model="form.name" required type="text" :error="errMsg('name')" label="Full Name" placeholder="Juan Dela Cruz" autocomplete="name" @input="form.clearErrors('name')" />
                <TextInput id="position" v-model="form.position" type="text" :error="form.errors.position" label="Position" placeholder="SRS I, Student" autocomplete="position" @input="form.clearErrors('position')" />
                <TextInput id="affiliation" v-model="form.affiliation" required type="text" :error="errMsg('affiliation')" label="Affiliation/Agency/Office" placeholder="Office Name" autocomplete="affiliation" @input="form.clearErrors('affiliation')" />
                <div class="flex items-center gap-2">
                    <TextInput id="phone" v-model="form.phone" required type="text" :error="errMsg('phone')" label="Contact Number" placeholder="0900 000 000" autocomplete="phone" @input="form.clearErrors('phone')" />
                    <TextInput id="email" v-model="form.email" required type="email" :error="errMsg('email')" label="Email Address" placeholder="sample@email.com" autocomplete="email" @input="form.clearErrors('email')" />
                </div>
            </div>

            <!-- Step 2: Request Form Details -->
            <div v-show="currentStepKey === 'details'" class="flex flex-col gap-2">
                <span class="font-bold uppercase">Request Form: </span>
                <TextInput id="request_purpose" v-model="form.request_purpose" required type="text" :error="errMsg('request_purpose')" label="Purpose of Request" placeholder="Reason or purpose of your request" autocomplete="request_purpose" @input="form.clearErrors('request_purpose')" />
                <TextInput id="request_details" v-model="form.request_details" type="text" :error="form.errors.request_details" label="Special Request or Instructions" placeholder="If applicable" autocomplete="request_details" @input="form.clearErrors('request_details')" />
                <TextInput id="project_title" v-model="form.project_title" type="text" :error="form.errors.project_title" label="Project Title" placeholder="Research or Thesis Title" autocomplete="project_title" @input="form.clearErrors('project_title')" />
                <div class="flex gap-2">
                    <DateInput id="date_of_use" v-model="form.date_of_use" required type="text" :error="errMsg('date_of_use')" label="Date of Use" autocomplete="date_of_use" @input="form.clearErrors('date_of_use')" />
                    <TimeInput id="time_of_use" v-model="form.time_of_use" required type="text" :error="errMsg('time_of_use')" label="Time of Use" autocomplete="time_of_use" @input="form.clearErrors('time_of_use')" />
                </div>
            </div>

            <!-- Step 3: Supplies -->
            <div v-show="currentStepKey === 'supplies'" class="flex flex-col gap-2">
                <h2>
                    <span class="font-bold uppercase">Supplies: </span><span class="text-sm">Type to SEARCH and press ENTER select</span>
                </h2>
                <TagifyInput v-model="form.consumables_to_use" name="consumables_to_use" placeholder="Select available supplies" api-link="api.inventory.items.public" />
            </div>

            <!-- Step 4: Equipments -->
            <div v-show="currentStepKey === 'equipments'" class="flex flex-col gap-2">
                <h2>
                    <span class="font-bold uppercase">Equipments: </span><span class="text-sm">Type to SEARCH and press ENTER select</span>
                </h2>
                <TagifyInput v-model="form.equipments_to_use" name="equipments_to_use" placeholder="Select available laboratory facilities" api-link="api.inventory.equipments.public" />
            </div>

            <!-- Step 5: Laboratory Facilities -->
            <div v-show="currentStepKey === 'labs'" class="flex flex-col gap-2">
                <h2>
                    <span class="font-bold uppercase">Laboratory Facilities: </span><span class="text-sm">Type to SEARCH and press ENTER select</span>
                </h2>
                <TagifyInput v-model="form.labs_to_use" name="labs_to_use" placeholder="Select available laboratory facilities" api-link="api.inventory.laboratories.public" />
            </div>

            <!-- Step 6: Terms & Conditions -->
            <div v-show="currentStepKey === 'terms'" class="flex flex-col gap-3 text-sm leading-tight text-justify">
                <h2>
                    <span class="font-bold uppercase">Terms & Conditions: </span>
                </h2>
                <div class="flex items-center gap-1" title="Require guests to pre-register">
                    <input type="checkbox" @change="form.agreed_clause_1 = $event.target.checked">
                    <span>I hereby acknowledge that I will utilize the supply/equipment/laboratory at my own risk; and agree to use it responsibly and in accordance with any provided instructions or safety guidelines.
                        <span v-if="hasErr('agreed_clause_1')" class="text-red-500">{{ errMsg('agreed_clause_1') }}</span>
                    </span>
                </div>
                <div class="flex items-center gap-1" title="Require guests to pre-register">
                    <input type="checkbox" @change="form.agreed_clause_2 = $event.target.checked">
                    <span>I agree to assume full responsibility for any damage or loss of the equipment while it is in my possession.
                        <span v-if="hasErr('agreed_clause_2')" class="text-red-500">{{ errMsg('agreed_clause_2') }}</span>
                    </span>
                </div>
                <div class="flex items-center gap-1" title="Require guests to pre-register">
                    <input type="checkbox" @change="form.agreed_clause_3 = $event.target.checked">
                    <span>I agree that the Center shall not be held liable for the quality, accuracy, reliability, or completeness of any data generated by the Requestor using the lab’s facilities, equipment, or resources. The Requestor assumes full responsibility for the design, execution, and interpretation of the experiments and the data derived therefrom. The Center makes no warranties, express or implied, regarding the outcomes of the Requestor’s research activities.
                        <span v-if="hasErr('agreed_clause_3')" class="text-red-500">{{ errMsg('agreed_clause_3') }}</span>
                    </span>
                </div>
            </div>

            <!-- Navigation Controls -->
            <div class="flex items-center justify-between pt-2">
                <button type="button" class="px-3 py-2 rounded border text-sm text-gray-700 bg-white hover:bg-gray-50 disabled:opacity-50" :disabled="currentStep === 0" @click="prevStep">Back</button>
                <div class="flex items-center gap-2">
                    <button v-if="currentStep < filteredSteps.length - 1" type="button" class="px-3 py-2 rounded bg-blue-600 text-white text-sm hover:bg-blue-700" @click="nextStep">Next</button>
                    <submit-btn v-else :disabled="model.api.processing" :processing="model.api.processing">
                        <span v-if="!model.api.processing">Register</span>
                        <span v-else>Registering</span>
                    </submit-btn>
                </div>
            </div>
        </form>
    </div>
</template>

<style scoped>
</style>
