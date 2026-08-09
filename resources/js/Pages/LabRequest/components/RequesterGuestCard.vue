<script>
import ApiMixin from "@/Modules/mixins/ApiMixin";
import FormLocalMixin from "@/Modules/mixins/FormLocalMixin";
import DtoResponse from "@/Modules/dto/DtoResponse";
import RequestFormPivot from "@/Modules/domain/RequestFormPivot";
import DataFormatterMixin from "@/Modules/mixins/DataFormatterMixin";
import ResourceSelectionStep from "@/Pages/LabRequest/components/ResourceSelectionStep.vue";
import { END_TIME_REQUEST_TYPES, REQUEST_FORM_STEPS, RESOURCE_STEP_CONFIG, STEP_TO_REQUEST_TYPE } from "@/Pages/LabRequest/config/requestStepConfig";

export default {
    name: "RequesterGuestCard",
    components: { ResourceSelectionStep },
    props: {
        requestTypeOptions: {
            type: Array,
            default: () => []
        }
    },
    mixins: [ApiMixin, FormLocalMixin, DataFormatterMixin],
    data() {
        return {
            model: null,
            employee_id: '',
            isNonPhilRiceEmployee: false,
            steps: REQUEST_FORM_STEPS,
            currentStep: 0,
            clientErrors: {},
            employeeFound: false,
        };
    },
    methods: {
        async handleCreate() {
            this.syncResourceSelections();
            const response = await this.submitCreate();
            if (response instanceof DtoResponse) {
                this.showSuccessModal = true;
                this.$emit('createdModel', response);
            }
        },
        toArray(value) {
            if (Array.isArray(value)) {
                return value.filter(Boolean);
            }

            return value ? [value] : [];
        },
        mergeUnique(...lists) {
            return [...new Set(lists.flat().filter(Boolean))];
        },
        syncResourceSelections() {
            const equipmentSelections = this.mergeUnique(
                this.toArray(this.form?.equipments_to_use),
                this.toArray(this.form?.ict_equipments),
                this.toArray(this.form?.laboratory_equipments),
                this.toArray(this.form?.biofreezers),
                this.toArray(this.form?.medicool_units),
                this.toArray(this.form?.plant_growth_chambers),
            );

            const laboratorySelections = this.mergeUnique(
                this.toArray(this.form?.labs_to_use),
                this.toArray(this.form?.laboratory_access),
                this.toArray(this.form?.field_spaces),
                this.toArray(this.form?.screenhouse_spaces),
                this.toArray(this.form?.office_spaces),
                this.toArray(this.form?.storage_spaces),
                this.toArray(this.form?.utility_spaces),
                this.toArray(this.form?.parking_spaces),
            );

            const consumableSelections = this.mergeUnique(
                this.toArray(this.form?.consumables_to_use),
                this.toArray(this.form?.ict_supplies),
                this.toArray(this.form?.laboratory_consumables),
                this.toArray(this.form?.office_supplies),
                this.toArray(this.form?.iec_materials),
                this.toArray(this.form?.tokens),
            );

            this.form.equipments_to_use = equipmentSelections;
            this.form.labs_to_use = laboratorySelections;
            this.form.consumables_to_use = consumableSelections;
        },
        handlePersonnelFound(data) {
            this.form.name = data.fullName || this.form.name;
            this.form.position = data.position ?? this.form.position;
            this.form.phone = data.phone ?? this.form.phone;
            this.form.email = data.email ?? this.form.email;
            this.form.affiliation = data.affiliation ?? this.form.affiliation;
            this.form.clearErrors('employee_id');
            this.employeeFound = true;
        },
        handlePersonnelError(error) {
            this.clientErrors[error.field] = error.message;
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
                if (this.employee_id && !this.form.name) {
                    document.getElementById('personnel-lookip-btn') ? document.getElementById('personnel-lookip-btn').click() : this.clientErrors['employee_id'] = 'Please search for your PhilRice ID'
                    this.clientErrors['employee_id'] = 'Please wait for personnel data to load';
                }
                else if (this.isNonPhilRiceEmployee) {
                    required('name', 'Full name');
                    required('affiliation', 'Affiliation');
                    required('phone', 'Contact number');
                    required('email', 'Email');
                } else {
                    if (!this.employee_id) {
                        this.clientErrors['employee_id'] = 'Please search for and select your PhilRice ID';
                    }
                    if (!f.name) {
                        this.clientErrors['name'] = 'Could not find personnel. Please try again or mark as non-PhilRice employee';
                    }
                }
            } else if (stepKey === 'details') {
                required('request_purpose', 'Request purpose');
                required('date_of_use', 'Date of use');
                required('time_of_use', 'Time of use');
                if (this.requiresEndTime) {
                    required('date_of_use_end', 'End date of use');
                    required('time_of_use_end', 'End time of use');
                }
            } else if (this.resourceStepConfig) {
                if (!f[this.resourceStepConfig.field]?.length) {
                    this.clientErrors[this.resourceStepConfig.field] = this.resourceStepConfig.errorMessage;
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
        requestTypeWhitelist() {
            const defaults = [
                'Biofreezer',
                'Field Experimental Space',
                'ICT Equipment',
                'ICT Supplies',
                'IEC Materials',
                'Laboratory Access',
                'Laboratory Consumables',
                'Laboratory Equipment',
                'Medicool',
                'Office Space',
                'Office Supplies',
                'Parking Space',
                'Plant Growth Chamber',
                'Screenhouse Space',
                'Storage Space',
                'Tokens',
                'Utility Space',
            ];

            const fromProps = (this.requestTypeOptions || []).map((entry) =>
                typeof entry === 'string'
                    ? { name: entry, label: entry }
                    : { name: entry?.name ?? entry?.label, label: entry?.label ?? entry?.name }
            ).filter((entry) => entry.name);

            const seen = new Set(fromProps.map((entry) => entry.name));
            const missing = defaults
                .filter((name) => !seen.has(name))
                .map((name) => ({ name, label: name }));

            return [...fromProps, ...missing];
        },
        selectedRequestTypes() {
            const type = this.form?.request_type;
            if (!type) return [];
            if (Array.isArray(type)) return type.filter(Boolean);
            return [type].filter(Boolean);
        },
        resourceStepConfig() {
            return RESOURCE_STEP_CONFIG[this.currentStepKey] ?? null;
        },
        filteredSteps() {
            const types = new Set(this.selectedRequestTypes.map(t => t.name || t));
            return this.steps.filter(step => {
                if (['request_type', 'requestor', 'details', 'terms'].includes(step.key)) return true;

                return types.has(STEP_TO_REQUEST_TYPE[step.key]);
            });
        },
        stepLabels() {
            return this.filteredSteps.map(s => s.label);
        },
        currentStepKey() {
            return this.filteredSteps[this.currentStep]?.key;
        },
        requiresEndTime() {
            return this.selectedRequestTypes.some(type => {
                const typeName = type.name || type;
                return END_TIME_REQUEST_TYPES.includes(typeName);
            });
        },
        isAuthenticated() {
            return (this.$page.props.auth && this.$page.props.auth.user);
        },
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

        if (this.isAuthenticated && this.$page.props.auth.user.employee_id) {
            this.employee_id = this.$page.props.auth.user.employee_id;
            this.form.name = this.$page.props.auth.user.name;
            this.form.requester_philrice_id = this.$page.props.auth.user.employee_id;
            this.form.email = this.$page.props.auth.user.email;
            this.employeeFound = true;
        }
    },
    beforeMount() {
        this.model = new RequestFormPivot();
        this.setFormAction('create');
    },
};
</script>

<template>
    <div class="bg-white rounded-2xl shadow-xl border border-gray-100 overflow-hidden mx-auto w-full max-w-4xl">
        <SuccessModal
            :show="showSuccessModal"
            title="Request submitted"
            :message="successMessage"
            @close="showSuccessModal = false"
        />
        <div class="bg-slate-50 border-b border-gray-100 p-6">
            <div class="max-w-2xl mx-auto overflow-x-auto">
                <ProgressTabs :steps="stepLabels" :current="currentStep" @update:current="handleStepChange" :clickable="true" />
            </div>
        </div>
        <form v-if="form" @submit.prevent="handleCreate()" class="flex flex-col relative overflow-hidden min-h-[400px]">
            
            <!-- Step 0: Request Type -->
            <transition name="slide-fade" mode="out-in">
            <div v-show="currentStepKey === 'request_type'" class="p-6 sm:p-10 flex flex-col min-h-[400px]">
                <h3 class="text-xl sm:text-2xl font-bold text-gray-900 mb-2">Request Type</h3>
                <p class="text-sm text-gray-500 mb-8">Select the type(s) of resources or facilities you need. You can choose multiple options.</p>
                <div class="w-full relative max-w-2xl">
                    <h2 class="flex justify-between items-center mb-2">
                        <span class="font-semibold text-gray-700">Select Categories <b class="text-red-500">*</b></span>
                        <transition-container type="slide-bottom">
                            <InputError v-show="!!hasErr('request_type')" :message="errMsg('request_type')" />
                        </transition-container>
                    </h2>
                    <TagifyInput
                        v-model="form.request_type"
                        name="request_type"
                        placeholder="Select one or more"
                        :whitelist="requestTypeWhitelist"
                        :enforce-whitelist="true"
                        @update:modelValue="form.clearErrors('request_type')"
                    />
                </div>
            </div>
            </transition>

            <!-- Step 1: Requestor Information -->
            <transition name="slide-fade" mode="out-in">
            <div v-show="currentStepKey === 'requestor'" class="p-6 sm:p-10 flex flex-col min-h-[400px]">
                <h3 class="text-xl sm:text-2xl font-bold text-gray-900 mb-2">Requestor Information</h3>
                <p class="text-sm text-gray-500 mb-8">Provide your contact and affiliation details. You can search for your PhilRice ID to auto-fill some fields.</p>
                
                <div class="max-w-2xl">
                <PersonnelLookup
                    v-if="!isNonPhilRiceEmployee && !isAuthenticated"
                    v-model="employee_id"
                    @found="handlePersonnelFound"
                    @error="handlePersonnelError"
                />
                
                <div v-if="isAuthenticated" class="text-sm font-medium text-emerald-700 bg-emerald-50 p-3 rounded-lg mb-2 flex items-center gap-2">
                    <LuCheckCircle2 class="w-4 h-4" />
                    <span>Using authenticated ID: {{ $page.props.auth.user.employee_id || 'Linked Account' }}</span>
                </div>
                <label v-if="form.name && !isNonPhilRiceEmployee && !isAuthenticated" class="text-AC text-semibold text-sm leading-none">Hi! {{ form.name }}</label>
                <label v-else-if="clientErrors['employee_id'] && !isAuthenticated" class="text-AC text-semibold text-sm leading-none">{{ clientErrors['employee_id'] }}</label>
                <div class="flex items-center gap-2 pt-4 pb-4">
                    <input 
                        type="checkbox" 
                        id="isNonPhilRice" 
                        v-model="isNonPhilRiceEmployee"
                        class="rounded border-gray-300 text-AB shadow-sm focus:border-AB focus:ring focus:ring-AB focus:ring-opacity-50"
                    />
                    <label for="isNonPhilRice" class="text-gray-700 cursor-pointer font-medium">
                        I am a non-PhilRice employee/personnel
                    </label>
                </div>
                <div v-show="isNonPhilRiceEmployee || employeeFound" class="flex flex-col gap-5 pt-6 border-t border-gray-100">
                    <p class="text-sm text-gray-500 mb-2">Manually enter your information</p>
                    <div class="grid md:grid-cols-2 gap-5">
                        <TextInput
                            v-if="isNonPhilRiceEmployee"
                            id="requester_philrice_id"
                            v-model="form.requester_philrice_id"
                            type="text"
                            :error="errMsg('requester_philrice_id')"
                            label="PhilRice ID (for returning requesters)"
                            placeholder="Optional if previously issued"
                            autocomplete="off"
                            @input="form.clearErrors('requester_philrice_id')"
                        />
                        <TextInput id="name" v-model="form.name" required type="text" :error="errMsg('name')" label="Full Name" placeholder="Juan Dela Cruz" autocomplete="name" @input="form.clearErrors('name')" />
                        <TextInput id="position" v-model="form.position" type="text" :error="form.errors.position" label="Position" placeholder="SRS I, Student" autocomplete="position" @input="form.clearErrors('position')" />
                        <TextInput id="affiliation" v-model="form.affiliation" required type="text" :error="errMsg('affiliation')" label="Affiliation/Agency/Office" placeholder="Office Name" autocomplete="affiliation" @input="form.clearErrors('affiliation')" />
                        <TextInput id="phone" v-model="form.phone" required type="text" :error="errMsg('phone')" label="Contact Number" placeholder="0900 000 000" autocomplete="phone" @input="form.clearErrors('phone')" />
                        <TextInput id="email" v-model="form.email" required type="email" :error="errMsg('email')" label="Email Address" placeholder="sample@email.com" autocomplete="email" @input="form.clearErrors('email')" />
                    </div>
                </div>
                </div>
            </div>
            </transition>

            <!-- Step 2: Request Form Details -->
            <transition name="slide-fade" mode="out-in">
            <div v-show="currentStepKey === 'details'" class="p-6 sm:p-10 flex flex-col min-h-[400px]">
                <h3 class="text-xl sm:text-2xl font-bold text-gray-900 mb-2">Request Details</h3>
                <p class="text-sm text-gray-500 mb-8">Tell us the purpose of your request and when you plan to use the resources.</p>
                <div class="max-w-2xl flex flex-col gap-5">
                    <TextInput id="request_purpose" v-model="form.request_purpose" required type="text" :error="errMsg('request_purpose')" label="Purpose of Request" placeholder="Reason or purpose of your request" autocomplete="request_purpose" @input="form.clearErrors('request_purpose')" />
                    <TextInput id="request_details" v-model="form.request_details" type="text" :error="form.errors.request_details" label="Special Request or Instructions" placeholder="If applicable" autocomplete="request_details" @input="form.clearErrors('request_details')" />
                    <TextInput id="project_title" v-model="form.project_title" type="text" :error="form.errors.project_title" label="Project Title" placeholder="Research or Thesis Title" autocomplete="project_title" @input="form.clearErrors('project_title')" />
                    <div class="grid grid-cols-2 gap-5">
                        <DateInput id="date_of_use" v-model="form.date_of_use" required type="text" :error="errMsg('date_of_use')" label="Date of Use" autocomplete="date_of_use" @input="form.clearErrors('date_of_use')" />
                        <TimeInput id="time_of_use" v-model="form.time_of_use" required type="text" :error="errMsg('time_of_use')" label="Time of Use" autocomplete="time_of_use" @input="form.clearErrors('time_of_use')" />
                    </div>
                    <div v-if="requiresEndTime" class="grid grid-cols-2 gap-5">
                        <DateInput id="date_of_use_end" v-model="form.date_of_use_end" required type="text" :error="errMsg('date_of_use_end')" label="End Date of Use" autocomplete="date_of_use_end" @input="form.clearErrors('date_of_use_end')" />
                        <TimeInput id="time_of_use_end" v-model="form.time_of_use_end" required type="text" :error="errMsg('time_of_use_end')" label="End Time of Use" autocomplete="time_of_use_end" @input="form.clearErrors('time_of_use_end')" />
                    </div>
                </div>
            </div>
            </transition>

            <resource-selection-step
                v-if="resourceStepConfig"
                v-show="currentStepKey === resourceStepConfig.key"
                v-model="form[resourceStepConfig.field]"
                :config="resourceStepConfig"
                :error="errMsg(resourceStepConfig.field)"
            />

            <!-- Terms & Conditions -->
            <transition name="slide-fade" mode="out-in">
            <div v-show="currentStepKey === 'terms'" class="p-6 sm:p-10 flex flex-col min-h-[400px]">
                <h3 class="text-xl sm:text-2xl font-bold text-gray-900 mb-2">Terms & Conditions</h3>
                <p class="text-sm text-gray-500 mb-8">Please read and agree to all terms and conditions below to complete your request.</p>
                <div class="flex flex-col gap-6 max-w-2xl bg-slate-50 p-6 rounded-xl border border-gray-100">
                    <label class="flex items-start text-left gap-3 cursor-pointer select-none group" title="Acknowledge lab usage risk">
                        <input 
                            type="checkbox" 
                            v-model="form.agreed_clause_1" 
                            class="mt-1 rounded border-gray-300 text-AB shadow-sm focus:border-AB focus:ring focus:ring-AB focus:ring-opacity-50"
                        />
                        <span class="text-sm text-gray-700 leading-relaxed group-hover:text-gray-900 transition-colors">
                            I hereby acknowledge that I will utilize the supply/equipment/laboratory at my own risk; and agree to use it responsibly and in accordance with any provided instructions or safety guidelines.
                            <span v-if="hasErr('agreed_clause_1')" class="block text-sm text-red-500 mt-1 font-medium">
                                {{ errMsg('agreed_clause_1') }}
                            </span>
                        </span>
                    </label>

                    <label class="flex items-start text-left gap-3 cursor-pointer select-none group" title="Assume damage responsibility">
                        <input 
                            type="checkbox" 
                            v-model="form.agreed_clause_2" 
                            class="mt-1 rounded border-gray-300 text-AB shadow-sm focus:border-AB focus:ring focus:ring-AB focus:ring-opacity-50"
                        />
                        <span class="text-sm text-gray-700 leading-relaxed group-hover:text-gray-900 transition-colors">
                            I agree to assume full responsibility for any damage or loss of the equipment while it is in my possession.
                            <span v-if="hasErr('agreed_clause_2')" class="block text-sm text-red-500 mt-1 font-medium">
                                {{ errMsg('agreed_clause_2') }}
                            </span>
                        </span>
                    </label>

                    <label class="flex items-start text-left gap-3 cursor-pointer select-none group" title="Liability disclaimer">
                        <input 
                            type="checkbox" 
                            v-model="form.agreed_clause_3" 
                            class="mt-1 rounded border-gray-300 text-AB shadow-sm focus:border-AB focus:ring focus:ring-AB focus:ring-opacity-50"
                        />
                        <span class="text-sm text-gray-700 leading-relaxed group-hover:text-gray-900 transition-colors">
                            I agree that the Center shall not be held liable for the quality, accuracy, reliability, or completeness of any data generated by the Requestor using the lab's facilities, equipment, or resources. The Requestor assumes full responsibility for the design, execution, and interpretation of the experiments and the data derived therefrom. The Center makes no warranties, express or implied, regarding the outcomes of the Requestor's research activities.
                            <span v-if="hasErr('agreed_clause_3')" class="block text-sm text-red-500 mt-1 font-medium">
                                {{ errMsg('agreed_clause_3') }}
                            </span>
                        </span>
                    </label>
                </div>
            </div>
            </transition>

            <!-- Navigation Controls -->
            <div class="bg-gray-50 px-6 py-4 border-t border-gray-100 flex items-center justify-between">
                <button type="button" class="px-6 py-2.5 rounded-xl border border-gray-200 text-gray-700 bg-white hover:bg-gray-50 hover:shadow-sm font-medium transition-all duration-300 disabled:opacity-50" :disabled="currentStep === 0" @click="prevStep">Back</button>
                <div class="flex items-center gap-3">
                    <button v-if="currentStep < filteredSteps.length - 1" type="button" class="px-8 py-2.5 rounded-xl bg-AB text-white font-bold shadow-md hover:bg-AB/90 transition-all hover:-translate-y-0.5 duration-300" @click="nextStep">Next Step</button>
                    <submit-btn v-else :disabled="model.api.processing" :processing="model.api.processing" class="px-8 py-2.5 rounded-xl bg-AB text-white font-bold shadow-md hover:bg-AB/90 transition-all hover:-translate-y-0.5 duration-300">
                        <span v-if="!model.api.processing">Submit Request</span>
                        <span v-else>Submitting...</span>
                    </submit-btn>
                </div>
            </div>
        </form>
    </div>
</template>

<style scoped>
</style>
