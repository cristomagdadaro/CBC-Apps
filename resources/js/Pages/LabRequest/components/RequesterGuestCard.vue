<script>
import ApiMixin from "@/Modules/mixins/ApiMixin";
import FormLocalMixin from "@/Modules/mixins/FormLocalMixin";
import DtoResponse from "@/Modules/dto/DtoResponse";
import RequestFormPivot from "@/Modules/domain/RequestFormPivot";
import DataFormatterMixin from "@/Modules/mixins/DataFormatterMixin";

export default {
    name: "RequesterGuestCard",
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
            steps: [
                { key: 'request_type', label: 'Request Type' },
                { key: 'requestor', label: 'Requestor Info' },
                { key: 'details', label: 'Request Details' },
                { key: 'biofreezer', label: 'Biofreezer' },
                { key: 'field_experimental_space', label: 'Field Experimental Space' },
                { key: 'ict_equipment', label: 'ICT Equipment' },
                { key: 'ict_supplies', label: 'ICT Supplies' },
                { key: 'iec_materials', label: 'IEC Materials' },
                { key: 'laboratory_access', label: 'Laboratory Access' },
                { key: 'laboratory_consumables', label: 'Laboratory Consumables' },
                { key: 'laboratory_equipment', label: 'Laboratory Equipment' },
                { key: 'medicool', label: 'Medicool' },
                { key: 'office_space', label: 'Office Space' },
                { key: 'office_supplies', label: 'Office Supplies' },
                { key: 'parking_space', label: 'Parking Space' },
                { key: 'plant_growth_chamber', label: 'Plant Growth Chamber' },
                { key: 'screenhouse_space', label: 'Screenhouse Space' },
                { key: 'storage_space', label: 'Storage Space' },
                { key: 'tokens', label: 'Tokens' },
                { key: 'utility_space', label: 'Utility Space' },
                { key: 'terms', label: 'Terms & Conditions' },
            ],
            currentStep: 0,
            clientErrors: {},
            employeeFound: false,
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
        handlePersonnelFound(data) {
            this.form.name = data.fullName || this.form.name;
            this.form.position = data.position ?? this.form.position;
            this.form.phone = data.phone ?? this.form.phone;
            this.form.email = data.email ?? this.form.email;
            this.form.affiliation = data.affiliation;
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
            } else if (stepKey === 'biofreezer') {
                if (!f.biofreezers?.length) this.clientErrors['biofreezers'] = 'Please select at least one biofreezer';
            } else if (stepKey === 'field_experimental_space') {
                if (!f.field_spaces?.length) this.clientErrors['field_spaces'] = 'Please select at least one field space';
            } else if (stepKey === 'ict_equipment') {
                if (!f.ict_equipments?.length) this.clientErrors['ict_equipments'] = 'Please select at least one ICT equipment';
            } else if (stepKey === 'ict_supplies') {
                if (!f.ict_supplies?.length) this.clientErrors['ict_supplies'] = 'Please select at least one ICT supply';
            } else if (stepKey === 'iec_materials') {
                if (!f.iec_materials?.length) this.clientErrors['iec_materials'] = 'Please select at least one IEC material';
            } else if (stepKey === 'laboratory_access') {
                if (!f.laboratory_access?.length) this.clientErrors['laboratory_access'] = 'Please select at least one laboratory';
            } else if (stepKey === 'laboratory_consumables') {
                if (!f.laboratory_consumables?.length) this.clientErrors['laboratory_consumables'] = 'Please select at least one consumable';
            } else if (stepKey === 'laboratory_equipment') {
                if (!f.laboratory_equipments?.length) this.clientErrors['laboratory_equipments'] = 'Please select at least one laboratory equipment';
            } else if (stepKey === 'medicool') {
                if (!f.medicool_units?.length) this.clientErrors['medicool_units'] = 'Please select at least one Medicool unit';
            } else if (stepKey === 'office_space') {
                if (!f.office_spaces?.length) this.clientErrors['office_spaces'] = 'Please select at least one office space';
            } else if (stepKey === 'office_supplies') {
                if (!f.office_supplies?.length) this.clientErrors['office_supplies'] = 'Please select at least one office supply';
            } else if (stepKey === 'parking_space') {
                if (!f.parking_spaces?.length) this.clientErrors['parking_spaces'] = 'Please select at least one parking space';
            } else if (stepKey === 'plant_growth_chamber') {
                if (!f.plant_growth_chambers?.length) this.clientErrors['plant_growth_chambers'] = 'Please select at least one plant growth chamber';
            } else if (stepKey === 'screenhouse_space') {
                if (!f.screenhouse_spaces?.length) this.clientErrors['screenhouse_spaces'] = 'Please select at least one screenhouse space';
            } else if (stepKey === 'storage_space') {
                if (!f.storage_spaces?.length) this.clientErrors['storage_spaces'] = 'Please select at least one storage space';
            } else if (stepKey === 'tokens') {
                if (!f.tokens?.length) this.clientErrors['tokens'] = 'Please select at least one token type';
            } else if (stepKey === 'utility_space') {
                if (!f.utility_spaces?.length) this.clientErrors['utility_spaces'] = 'Please select at least one utility space';
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
        selectedRequestTypes() {
            const type = this.form?.request_type;
            if (!type) return [];
            if (Array.isArray(type)) return type.filter(Boolean);
            return [type].filter(Boolean);
        },
        filteredSteps() {
            const types = new Set(this.selectedRequestTypes.map(t => t.name || t));
            return this.steps.filter(step => {
                // Common steps always shown
                if (['request_type', 'requestor', 'details', 'terms'].includes(step.key)) return true;
                
                // Map step keys to request type names
                const stepToTypeMap = {
                    'biofreezer': 'Biofreezer',
                    'field_experimental_space': 'Field Experimental Space',
                    'ict_equipment': 'ICT Equipment',
                    'ict_supplies': 'ICT Supplies',
                    'iec_materials': 'IEC Materials',
                    'laboratory_access': 'Laboratory Access',
                    'laboratory_consumables': 'Laboratory Consumables',
                    'laboratory_equipment': 'Laboratory Equipment',
                    'medicool': 'Medicool',
                    'office_space': 'Office Space',
                    'office_supplies': 'Office Supplies',
                    'parking_space': 'Parking Space',
                    'plant_growth_chamber': 'Plant Growth Chamber',
                    'screenhouse_space': 'Screenhouse Space',
                    'storage_space': 'Storage Space',
                    'tokens': 'Tokens',
                    'utility_space': 'Utility Space',
                };
                
                return types.has(stepToTypeMap[step.key]);
            });
        },
        stepLabels() {
            return this.filteredSteps.map(s => s.label);
        },
        currentStepKey() {
            return this.filteredSteps[this.currentStep]?.key;
        },
        requiresEndTime() {
            const equipmentTypes = [
                'Biofreezer',
                'ICT Equipment', 
                'Laboratory Equipment',
                'Medicool',
                'Plant Growth Chamber',
                'Laboratory Access',
                'Field Experimental Space',
                'Screenhouse Space',
                'Office Space',
                'Storage Space',
                'Utility Space'
            ];
            return this.selectedRequestTypes.some(type => {
                const typeName = type.name || type;
                return equipmentTypes.includes(typeName);
            });
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
    <div class="border p-2 md:rounded-md flex flex-col gap-2 bg-white w-full drop-shadow-lg mx-auto">
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
                <PersonnelLookup
                    v-if="!isNonPhilRiceEmployee"
                    v-model="employee_id"
                    @found="handlePersonnelFound"
                    @error="handlePersonnelError"
                />
                <label v-if="form.name && !isNonPhilRiceEmployee" class="text-AC text-semibold text-sm leading-none">Hi! {{ form.name }}</label>
                <label v-else-if="clientErrors['employee_id']" class="text-AC text-semibold text-sm leading-none">{{ clientErrors['employee_id'] }}</label>
                <div class="flex items-center gap-2 pt-2">
                    <input 
                        type="checkbox" 
                        id="isNonPhilRice" 
                        v-model="isNonPhilRiceEmployee"
                        class="rounded"
                    />
                    <label for="isNonPhilRice" class="text-gray-600 cursor-pointer leading-none">
                        I am a non-PhilRice employee/personnel
                    </label>
                </div>
                <div v-show="isNonPhilRiceEmployee || employeeFound" class="flex flex-col gap-2 pt-2 border-t">
                    <p class="text-sm text-gray-600">Manually enter your information</p>
                    <TextInput id="name" v-model="form.name" required type="text" :error="errMsg('name')" label="Full Name" placeholder="Juan Dela Cruz" autocomplete="name" @input="form.clearErrors('name')" />
                    <TextInput id="position" v-model="form.position" type="text" :error="form.errors.position" label="Position" placeholder="SRS I, Student" autocomplete="position" @input="form.clearErrors('position')" />
                    <TextInput id="affiliation" v-model="form.affiliation" required type="text" :error="errMsg('affiliation')" label="Affiliation/Agency/Office" placeholder="Office Name" autocomplete="affiliation" @input="form.clearErrors('affiliation')" />
                    <div class="flex items-center gap-2">
                        <TextInput id="phone" v-model="form.phone" required type="text" :error="errMsg('phone')" label="Contact Number" placeholder="0900 000 000" autocomplete="phone" @input="form.clearErrors('phone')" />
                        <TextInput id="email" v-model="form.email" required type="email" :error="errMsg('email')" label="Email Address" placeholder="sample@email.com" autocomplete="email" @input="form.clearErrors('email')" />
                    </div>
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

            <!-- Biofreezer Section -->
            <div v-show="currentStepKey === 'biofreezer'" class="flex flex-col gap-2">
                <p class="text-sm text-gray-600">Select the biofreezer units you need for sample storage. Start typing to find available units.</p>
                <h2>
                    <span class="font-bold uppercase">Biofreezer Units: </span><span class="text-sm">Type to SEARCH and press ENTER to select</span>
                </h2>
                <TagifyInput 
                    v-model="form.biofreezers" 
                    name="biofreezers" 
                    placeholder="Select available biofreezers" 
                    api-link="api.inventory.categories.public" 
                    :params="{ routeParams: {categoryName: 'Biofreezer'} }" 
                />
                <InputError v-if="hasErr('biofreezers')" :message="errMsg('biofreezers')" />
            </div>

            <!-- Field Experimental Space Section -->
            <div v-show="currentStepKey === 'field_experimental_space'" class="flex flex-col gap-2">
                <p class="text-sm text-gray-600">Select the field experimental spaces you need. Start typing to find available spaces.</p>
                <h2>
                    <span class="font-bold uppercase">Field Experimental Spaces: </span><span class="text-sm">Type to SEARCH and press ENTER to select</span>
                </h2>
                <TagifyInput 
                    v-model="form.field_spaces" 
                    name="field_spaces" 
                    placeholder="Select available field spaces" 
                    api-link="api.inventory.categories.public" 
                    :params="{ routeParams: {categoryName: 'Field Experimental Space'} }" 
                />
                <InputError v-if="hasErr('field_spaces')" :message="errMsg('field_spaces')" />
            </div>

            <!-- ICT Equipment Section -->
            <div v-show="currentStepKey === 'ict_equipment'" class="flex flex-col gap-2">
                <p class="text-sm text-gray-600">Select the ICT equipment you'll need. Search by typing the equipment name.</p>
                <h2>
                    <span class="font-bold uppercase">ICT Equipment: </span><span class="text-sm">Type to SEARCH and press ENTER to select</span>
                </h2>
                <TagifyInput 
                    v-model="form.ict_equipments" 
                    name="ict_equipments" 
                    placeholder="Select available ICT equipment" 
                    api-link="api.inventory.categories.public" 
                    :params="{ routeParams: {categoryName: 'ICT Equipment'} }" 
                />
                <InputError v-if="hasErr('ict_equipments')" :message="errMsg('ict_equipments')" />
            </div>

            <!-- ICT Supplies Section -->
            <div v-show="currentStepKey === 'ict_supplies'" class="flex flex-col gap-2">
                <p class="text-sm text-gray-600">Select the ICT supplies or consumables you need. Start typing to find available items.</p>
                <h2>
                    <span class="font-bold uppercase">ICT Supplies: </span><span class="text-sm">Type to SEARCH and press ENTER to select</span>
                </h2>
                <TagifyInput 
                    v-model="form.ict_supplies" 
                    name="ict_supplies" 
                    placeholder="Select available ICT supplies" 
                    api-link="api.inventory.categories.public" 
                    :params="{ routeParams: {categoryName: 'ICT Supplies'} }" 
                />
                <InputError v-if="hasErr('ict_supplies')" :message="errMsg('ict_supplies')" />
            </div>

            <!-- IEC Materials Section -->
            <div v-show="currentStepKey === 'iec_materials'" class="flex flex-col gap-2">
                <p class="text-sm text-gray-600">Select the IEC (Information, Education, Communication) materials you need.</p>
                <h2>
                    <span class="font-bold uppercase">IEC Materials: </span><span class="text-sm">Type to SEARCH and press ENTER to select</span>
                </h2>
                <TagifyInput 
                    v-model="form.iec_materials" 
                    name="iec_materials" 
                    placeholder="Select available IEC materials" 
                    api-link="api.inventory.categories.public" 
                    :params="{ routeParams: {categoryName: 'IEC Materials'} }" 
                />
                <InputError v-if="hasErr('iec_materials')" :message="errMsg('iec_materials')" />
            </div>

            <!-- Laboratory Access Section -->
            <div v-show="currentStepKey === 'laboratory_access'" class="flex flex-col gap-2">
                <p class="text-sm text-gray-600">Choose the laboratory facilities you'll be accessing. Search to find available labs.</p>
                <h2>
                    <span class="font-bold uppercase">Laboratory Access: </span><span class="text-sm">Type to SEARCH and press ENTER to select</span>
                </h2>
                <TagifyInput 
                    v-model="form.laboratory_access" 
                    name="laboratory_access" 
                    placeholder="Select available laboratories" 
                    api-link="api.inventory.laboratories.public" 
                    :params="{ routeParams: {group: 'laboratories'} }" 
                />
                <InputError v-if="hasErr('laboratory_access')" :message="errMsg('laboratory_access')" />
            </div>

            <!-- Laboratory Consumables Section -->
            <div v-show="currentStepKey === 'laboratory_consumables'" class="flex flex-col gap-2">
                <p class="text-sm text-gray-600">Select the laboratory consumables you need for your experiments.</p>
                <h2>
                    <span class="font-bold uppercase">Laboratory Consumables: </span><span class="text-sm">Type to SEARCH and press ENTER to select</span>
                </h2>
                <TagifyInput 
                    v-model="form.laboratory_consumables" 
                    name="laboratory_consumables" 
                    placeholder="Select available consumables" 
                    api-link="api.inventory.categories.public" 
                    :params="{ routeParams: {categoryName: 'Laboratory Consumables'} }" 
                />
                <InputError v-if="hasErr('laboratory_consumables')" :message="errMsg('laboratory_consumables')" />
            </div>

            <!-- Laboratory Equipment Section -->
            <div v-show="currentStepKey === 'laboratory_equipment'" class="flex flex-col gap-2">
                <p class="text-sm text-gray-600">Select the laboratory equipment you'll need for your research.</p>
                <h2>
                    <span class="font-bold uppercase">Laboratory Equipment: </span><span class="text-sm">Type to SEARCH and press ENTER to select</span>
                </h2>
                <TagifyInput 
                    v-model="form.laboratory_equipments" 
                    name="laboratory_equipments" 
                    placeholder="Select available laboratory equipment" 
                    api-link="api.inventory.categories.public" 
                    :params="{ routeParams: {categoryName: 'Laboratory Equipment'} }" 
                />
                <InputError v-if="hasErr('laboratory_equipments')" :message="errMsg('laboratory_equipments')" />
            </div>

            <!-- Medicool Section -->
            <div v-show="currentStepKey === 'medicool'" class="flex flex-col gap-2">
                <p class="text-sm text-gray-600">Select the Medicool units you need for temperature-controlled storage.</p>
                <h2>
                    <span class="font-bold uppercase">Medicool Units: </span><span class="text-sm">Type to SEARCH and press ENTER to select</span>
                </h2>
                <TagifyInput 
                    v-model="form.medicool_units" 
                    name="medicool_units" 
                    placeholder="Select available Medicool units" 
                    api-link="api.inventory.categories.public" 
                    :params="{ routeParams: {categoryName: 'Medicool'} }" 
                />
                <InputError v-if="hasErr('medicool_units')" :message="errMsg('medicool_units')" />
            </div>

            <!-- Office Space Section -->
            <div v-show="currentStepKey === 'office_space'" class="flex flex-col gap-2">
                <p class="text-sm text-gray-600">Select the office spaces you need for your work.</p>
                <h2>
                    <span class="font-bold uppercase">Office Spaces: </span><span class="text-sm">Type to SEARCH and press ENTER to select</span>
                </h2>
                <TagifyInput 
                    v-model="form.office_spaces" 
                    name="office_spaces" 
                    placeholder="Select available office spaces" 
                    api-link="api.inventory.categories.public" 
                    :params="{ routeParams: {categoryName: 'Office Space'} }" 
                />
                <InputError v-if="hasErr('office_spaces')" :message="errMsg('office_spaces')" />
            </div>

            <!-- Office Supplies Section -->
            <div v-show="currentStepKey === 'office_supplies'" class="flex flex-col gap-2">
                <p class="text-sm text-gray-600">Select the office supplies you need for your project.</p>
                <h2>
                    <span class="font-bold uppercase">Office Supplies: </span><span class="text-sm">Type to SEARCH and press ENTER to select</span>
                </h2>
                <TagifyInput 
                    v-model="form.office_supplies" 
                    name="office_supplies" 
                    placeholder="Select available office supplies" 
                    api-link="api.inventory.categories.public" 
                    :params="{ routeParams: {categoryName: 'Office Supplies'} }" 
                />
                <InputError v-if="hasErr('office_supplies')" :message="errMsg('office_supplies')" />
            </div>

            <!-- Parking Space Section -->
            <div v-show="currentStepKey === 'parking_space'" class="flex flex-col gap-2">
                <p class="text-sm text-gray-600">Select the parking spaces you need during your visit.</p>
                <h2>
                    <span class="font-bold uppercase">Parking Spaces: </span><span class="text-sm">Type to SEARCH and press ENTER to select</span>
                </h2>
                <TagifyInput 
                    v-model="form.parking_spaces" 
                    name="parking_spaces" 
                    placeholder="Select available parking spaces" 
                    api-link="api.inventory.categories.public" 
                    :params="{ routeParams: {categoryName: 'Parking Space'} }" 
                />
                <InputError v-if="hasErr('parking_spaces')" :message="errMsg('parking_spaces')" />
            </div>

            <!-- Plant Growth Chamber Section -->
            <div v-show="currentStepKey === 'plant_growth_chamber'" class="flex flex-col gap-2">
                <p class="text-sm text-gray-600">Select the plant growth chambers you need for your research.</p>
                <h2>
                    <span class="font-bold uppercase">Plant Growth Chambers: </span><span class="text-sm">Type to SEARCH and press ENTER to select</span>
                </h2>
                <TagifyInput 
                    v-model="form.plant_growth_chambers" 
                    name="plant_growth_chambers" 
                    placeholder="Select available plant growth chambers" 
                    api-link="api.inventory.categories.public" 
                    :params="{ routeParams: {categoryName: 'Plant Growth Chamber'} }" 
                />
                <InputError v-if="hasErr('plant_growth_chambers')" :message="errMsg('plant_growth_chambers')" />
            </div>

            <!-- Screenhouse Space Section -->
            <div v-show="currentStepKey === 'screenhouse_space'" class="flex flex-col gap-2">
                <p class="text-sm text-gray-600">Select the screenhouse spaces you need for plant experiments.</p>
                <h2>
                    <span class="font-bold uppercase">Screenhouse Spaces: </span><span class="text-sm">Type to SEARCH and press ENTER to select</span>
                </h2>
                <TagifyInput 
                    v-model="form.screenhouse_spaces" 
                    name="screenhouse_spaces" 
                    placeholder="Select available screenhouse spaces" 
                    api-link="api.inventory.categories.public" 
                    :params="{ routeParams: {categoryName: 'Screenhouse Space'} }" 
                />
                <InputError v-if="hasErr('screenhouse_spaces')" :message="errMsg('screenhouse_spaces')" />
            </div>

            <!-- Storage Space Section -->
            <div v-show="currentStepKey === 'storage_space'" class="flex flex-col gap-2">
                <p class="text-sm text-gray-600">Select the storage spaces you need for your materials or equipment.</p>
                <h2>
                    <span class="font-bold uppercase">Storage Spaces: </span><span class="text-sm">Type to SEARCH and press ENTER to select</span>
                </h2>
                <TagifyInput 
                    v-model="form.storage_spaces" 
                    name="storage_spaces" 
                    placeholder="Select available storage spaces" 
                    api-link="api.inventory.categories.public" 
                    :params="{ routeParams: {categoryName: 'Storage Space'} }" 
                />
                <InputError v-if="hasErr('storage_spaces')" :message="errMsg('storage_spaces')" />
            </div>

            <!-- Tokens Section -->
            <div v-show="currentStepKey === 'tokens'" class="flex flex-col gap-2">
                <p class="text-sm text-gray-600">Select the tokens you need for facility access or services.</p>
                <h2>
                    <span class="font-bold uppercase">Tokens: </span><span class="text-sm">Type to SEARCH and press ENTER to select</span>
                </h2>
                <TagifyInput 
                    v-model="form.tokens" 
                    name="tokens" 
                    placeholder="Select available tokens" 
                    api-link="api.inventory.categories.public" 
                    :params="{ routeParams: {categoryName: 'Tokens'} }" 
                />
                <InputError v-if="hasErr('tokens')" :message="errMsg('tokens')" />
            </div>

            <!-- Utility Space Section -->
            <div v-show="currentStepKey === 'utility_space'" class="flex flex-col gap-2">
                <p class="text-sm text-gray-600">Select the utility spaces you need for your activities.</p>
                <h2>
                    <span class="font-bold uppercase">Utility Spaces: </span><span class="text-sm">Type to SEARCH and press ENTER to select</span>
                </h2>
                <TagifyInput 
                    v-model="form.utility_spaces" 
                    name="utility_spaces" 
                    placeholder="Select available utility spaces" 
                    api-link="api.inventory.categories.public" 
                    :params="{ routeParams: {categoryName: 'Utility Space'} }" 
                />
                <InputError v-if="hasErr('utility_spaces')" :message="errMsg('utility_spaces')" />
            </div>

            <!-- Terms & Conditions -->
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
                    <span>I agree that the Center shall not be held liable for the quality, accuracy, reliability, or completeness of any data generated by the Requestor using the lab's facilities, equipment, or resources. The Requestor assumes full responsibility for the design, execution, and interpretation of the experiments and the data derived therefrom. The Center makes no warranties, express or implied, regarding the outcomes of the Requestor's research activities.
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