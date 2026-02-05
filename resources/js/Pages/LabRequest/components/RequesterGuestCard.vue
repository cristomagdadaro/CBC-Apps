<script>
import ApiMixin from "@/Modules/mixins/ApiMixin";
import FormLocalMixin from "@/Modules/mixins/FormLocalMixin";
import DtoResponse from "@/Modules/dto/DtoResponse";
import RequestFormPivot from "@/Modules/domain/RequestFormPivot";
import DataFormatterMixin from "@/Modules/mixins/DataFormatterMixin";
import TagifyInput from "@/Components/Tagify.vue";
import Personnel from "@/Modules/domain/Personnel";

export default {
    name: "RequesterGuestCard",
    components: {
        TagifyInput,
    },
    mixins: [ApiMixin, FormLocalMixin, DataFormatterMixin],
    data() {
        return {
            model: null,
            employee_id: '',
            searchLoading: false,
            steps: [
                { key: 'request_type', label: 'Request Type' },
                { key: 'requestor', label: 'Requestor Info' },
                { key: 'details', label: 'Request Details' },
                { key: 'supplies', label: 'Supplies' },
                { key: 'equipments', label: 'Equipments' },
                { key: 'labs', label: 'Laboratory Facilities' },
                { key: 'terms', label: 'Terms & Conditions' },
            ],
            requestTypeOptions: ['Supplies', 'Equipments', 'Laboratory Access'],
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
                if (!Array.isArray(this.form?.request_type) || !this.form?.request_type?.length) {
                    this.clientErrors['request_type'] = 'Select at least one request type';
                }
            } else if (stepKey === 'requestor') {
                required('name', 'Full name');
                required('affiliation', 'Affiliation');
                required('phone', 'Contact number');
                required('email', 'Email');
            } else if (stepKey === 'details') {
                required('request_purpose', 'Request purpose');
                required('date_of_use', 'Date of use');
                required('time_of_use', 'Time of use');
                if (this.requiresEndTime) {
                    required('date_of_use_end', 'End date of use');
                    required('time_of_use_end', 'End time of use');
                }
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
        selectedRequestTypes() {
            const type = this.form?.request_type;
            if (!type) return [];
            if (Array.isArray(type)) return type.filter(Boolean);
            return [type].filter(Boolean);
        },
        filteredSteps() {
            const types = new Set(this.selectedRequestTypes);
            return this.steps.filter(step => {
                if (['request_type', 'requestor', 'details', 'terms'].includes(step.key)) return true;
                if (step.key === 'supplies') return types.has('Supplies');
                if (step.key === 'equipments') return types.has('Equipments');
                if (step.key === 'labs') return types.has('Laboratory Access');
                return false;
            });
        },
        stepLabels() {
            return this.filteredSteps.map(s => s.label);
        },
        currentStepKey() {
            return this.filteredSteps[this.currentStep]?.key;
        },
        requiresEndTime() {
            return this.selectedRequestTypes.includes('Equipments') || this.selectedRequestTypes.includes('Laboratory Access');
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
    mounted() {
        if (!Array.isArray(this.form?.request_type)) {
            this.form.request_type = this.form?.request_type ? [this.form.request_type] : [];
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
                <p class="text-sm text-gray-600">Select the type(s) of resources or facilities you need. You can choose multiple options if your request requires different types of support.</p>
                <div class="w-full relative">
                    <h2 class="flex justify-between items-center">
                        <span class="font-bold uppercase">Request Types:<b class="text-red-500 ">*</b></span>
                        <transition-container type="slide-bottom">
                            <InputError v-show="!!hasErr('request_type')" :message="errMsg('request_type')" />
                        </transition-container>
                    </h2>
                    <TagifyInput
                        v-model="form.request_type"
                        name="request_type"
                        placeholder="Select one or more"
                        :whitelist="requestTypeOptions"
                        :enforce-whitelist="true"
                        classes="inline-flex items-center justify-between px-3 py-3 border border-gray-900 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white w-full hover:text-gray-700 focus:bg-gray-50"
                        @update:modelValue="form.clearErrors('request_type')"
                    />
                </div>
            </div>

            <!-- Step 1: Requestor Information -->
            <div v-show="currentStepKey === 'requestor'" class="flex flex-col gap-2">
                <p class="text-sm text-gray-600">Provide your contact and affiliation details. You can search for your PhilRice ID to auto-fill some fields, or enter your information manually.</p>
                <h2>
                    <span class="font-bold uppercase">Requestor Information: </span>
                </h2>
                <p class="text-sm text-gray-600">Auto-fill by PhilRice ID</p>
                <div class="flex items-end gap-2">
                    <TextInput id="employee_id" v-model="employee_id" type="text" :error="errMsg('employee_id')" label="PhilRice ID" placeholder="**-****" name="employee_id" autocomplete="employee_id" @input="form.clearErrors('employee_id')"/>
                    <button type="button" class="px-3 py-2 rounded bg-gray-800 text-white text-sm hover:bg-gray-900 disabled:opacity-50" :disabled="searchLoading" @click="searchPersonnel">
                        <span v-if="!searchLoading">Search</span>
                        <span v-else>Searching…</span>
                    </button>
                </div>
                
                <br class="border" />
                <p class="text-sm text-gray-600">Manually enter your information</p>
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
                <p class="text-sm text-gray-600">Tell us the purpose of your request and when you plan to use the resources. Include any special instructions or project details that may help us better assist you.</p>
                <span class="font-bold uppercase">Request Form: </span>
                <TextInput id="request_purpose" v-model="form.request_purpose" required type="text" :error="errMsg('request_purpose')" label="Purpose of Request" placeholder="Reason or purpose of your request" autocomplete="request_purpose" @input="form.clearErrors('request_purpose')" />
                <TextInput id="request_details" v-model="form.request_details" type="text" :error="form.errors.request_details" label="Special Request or Instructions" placeholder="If applicable" autocomplete="request_details" @input="form.clearErrors('request_details')" />
                <TextInput id="project_title" v-model="form.project_title" type="text" :error="form.errors.project_title" label="Project Title" placeholder="Research or Thesis Title" autocomplete="project_title" @input="form.clearErrors('project_title')" />
                <div class="flex gap-2">
                    <DateInput id="date_of_use" v-model="form.date_of_use" required type="text" :error="errMsg('date_of_use')" label="Date of Use" autocomplete="date_of_use" @input="form.clearErrors('date_of_use')" />
                    <TimeInput id="time_of_use" v-model="form.time_of_use" required type="text" :error="errMsg('time_of_use')" label="Time of Use" autocomplete="time_of_use" @input="form.clearErrors('time_of_use')" />
                </div>
                <div v-if="requiresEndTime" class="flex gap-2">
                    <DateInput id="date_of_use_end" v-model="form.date_of_use_end" required type="text" :error="errMsg('date_of_use_end')" label="End Date of Use" autocomplete="date_of_use_end" @input="form.clearErrors('date_of_use_end')" />
                    <TimeInput id="time_of_use_end" v-model="form.time_of_use_end" required type="text" :error="errMsg('time_of_use_end')" label="End Time of Use" autocomplete="time_of_use_end" @input="form.clearErrors('time_of_use_end')" />
                </div>
            </div>

            <!-- Step 3: Supplies -->
            <div v-show="currentStepKey === 'supplies'" class="flex flex-col gap-2">
                <p class="text-sm text-gray-600">Search and select the supplies or consumables you need for your project. Start typing to find available items.</p>
                <h2>
                    <span class="font-bold uppercase">Supplies: </span><span class="text-sm">Type to SEARCH and press ENTER select</span>
                </h2>
                <TagifyInput v-model="form.consumables_to_use" name="consumables_to_use" placeholder="Select available supplies" api-link="api.inventory.items.public" />
            </div>

            <!-- Step 4: Equipments -->
            <div v-show="currentStepKey === 'equipments'" class="flex flex-col gap-2">
                <p class="text-sm text-gray-600">Select the equipment you'll need for your research or project. Search by typing the equipment name.</p>
                <h2>
                    <span class="font-bold uppercase">Equipments: </span><span class="text-sm">Type to SEARCH and press ENTER select</span>
                </h2>
                <TagifyInput v-model="form.equipments_to_use" name="equipments_to_use" placeholder="Select available laboratory facilities" api-link="api.inventory.equipments.public" />
            </div>

            <!-- Step 5: Laboratory Facilities -->
            <div v-show="currentStepKey === 'labs'" class="flex flex-col gap-2">
                <p class="text-sm text-gray-600">Choose the laboratory or lab facilities you'll be using. Search to find the available labs that match your needs.</p>
                <h2>
                    <span class="font-bold uppercase">Laboratory Facilities: </span><span class="text-sm">Type to SEARCH and press ENTER select</span>
                </h2>
                <TagifyInput v-model="form.labs_to_use" name="labs_to_use" placeholder="Select available laboratory facilities" api-link="api.inventory.laboratories.public" />
            </div>

            <!-- Step 6: Terms & Conditions -->
            <div v-show="currentStepKey === 'terms'" class="flex flex-col gap-3 text-sm leading-tight text-justify">
                <p class="text-sm text-gray-600">Please read and agree to all terms and conditions below to complete your request. By checking the boxes, you acknowledge your responsibilities as a resource user.</p>
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
                        <span v-if="!model.api.processing">Submit</span>
                        <span v-else>Saving</span>
                    </submit-btn>
                </div>
            </div>
        </form>
    </div>
</template>

<style scoped>
</style>
