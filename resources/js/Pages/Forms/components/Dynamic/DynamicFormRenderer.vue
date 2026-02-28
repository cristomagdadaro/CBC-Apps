<script>
/**
 * DynamicFormRenderer.vue
 * 
 * Renders a form dynamically based on a field schema.
 * Replaces hardcoded form cards with a single, schema-driven renderer.
 */
import SubformMixin from "@/Modules/mixins/SubformMixin";
import LocationMixin from "@/Modules/mixins/LocationMixin";
import SubformResponse from "@/Modules/domain/SubformResponse";
import { FIELD_TYPES } from "@/Modules/Events/Config/FormFieldTypes";

// Field Components
import DynamicFieldText from "./fields/DynamicFieldText.vue";
import DynamicFieldTextarea from "./fields/DynamicFieldTextarea.vue";
import DynamicFieldNumber from "./fields/DynamicFieldNumber.vue";
import DynamicFieldEmail from "./fields/DynamicFieldEmail.vue";
import DynamicFieldPhone from "./fields/DynamicFieldPhone.vue";
import DynamicFieldDate from "./fields/DynamicFieldDate.vue";
import DynamicFieldSelect from "./fields/DynamicFieldSelect.vue";
import DynamicFieldRadio from "./fields/DynamicFieldRadio.vue";
import DynamicFieldCheckbox from "./fields/DynamicFieldCheckbox.vue";
import DynamicFieldCheckboxGroup from "./fields/DynamicFieldCheckboxGroup.vue";
import DynamicFieldAgreement from "./fields/DynamicFieldAgreement.vue";
import DynamicFieldLikertScale from "./fields/DynamicFieldLikertScale.vue";
import DynamicFieldLinearScale from "./fields/DynamicFieldLinearScale.vue";
import DynamicFieldFile from "./fields/DynamicFieldFile.vue";
import DynamicFieldLocation from "./fields/DynamicFieldLocation.vue";
import DynamicFieldSectionHeader from "./fields/DynamicFieldSectionHeader.vue";
import DynamicFieldParagraph from "./fields/DynamicFieldParagraph.vue";

export default {
    name: "DynamicFormRenderer",
    mixins: [SubformMixin, LocationMixin],
    components: {
        DynamicFieldText,
        DynamicFieldTextarea,
        DynamicFieldNumber,
        DynamicFieldEmail,
        DynamicFieldPhone,
        DynamicFieldDate,
        DynamicFieldSelect,
        DynamicFieldRadio,
        DynamicFieldCheckbox,
        DynamicFieldCheckboxGroup,
        DynamicFieldAgreement,
        DynamicFieldLikertScale,
        DynamicFieldLinearScale,
        DynamicFieldFile,
        DynamicFieldLocation,
        DynamicFieldSectionHeader,
        DynamicFieldParagraph,
    },
    props: {
        /**
         * Field schema array from EventSubform.field_schema
         */
        fieldSchema: {
            type: Array,
            required: true,
        },
        /**
         * Existing response data for edit mode
         */
        responseData: {
            type: Object,
            default: null,
        },
        /**
         * Event ID for the form submission
         */
        eventId: {
            type: String,
            required: true,
        },
        /**
         * Subform type (e.g., 'preregistration', 'feedback')
         */
        subformType: {
            type: String,
            required: true,
        },
        /**
         * Form configuration from parent
         */
        config: {
            type: Object,
            default: () => ({}),
        },
        /**
         * Participant ID for linking response
         */
        participantId: {
            type: String,
            default: null,
        },
        /**
         * Form title override
         */
        title: {
            type: String,
            default: '',
        },
        /**
         * Form description/instructions
         */
        description: {
            type: String,
            default: 'Kindly provide the required and correct details. Fields marked with * are required.',
        },
    },
    emits: ['createdModel', 'updatedModel', 'submit', 'error'],
    data() {
        return {
            showSuccess: false,
            isSubmitting: false,
            locationData: {
                regions: [],
                provinces: [],
                cities: [],
            },
        };
    },
    computed: {
        isEditMode() {
            return !!this.responseData?.id;
        },
        registrationIDHashed() {
            return this.model?.response?.participant_hash ?? null;
        },
        /**
         * Fields sorted by sort_order
         */
        sortedFields() {
            return [...this.fieldSchema].sort((a, b) => (a.sort_order || 0) - (b.sort_order || 0));
        },
        /**
         * Group fields into sections (for section_header type fields)
         */
        fieldSections() {
            const sections = [];
            let currentSection = { header: null, fields: [] };

            for (const field of this.sortedFields) {
                if (field.field_type === 'section_header') {
                    if (currentSection.fields.length > 0 || currentSection.header) {
                        sections.push(currentSection);
                    }
                    currentSection = { header: field, fields: [] };
                } else {
                    currentSection.fields.push(field);
                }
            }
            
            if (currentSection.fields.length > 0 || currentSection.header) {
                sections.push(currentSection);
            }

            return sections.length > 0 ? sections : [{ header: null, fields: this.sortedFields }];
        },
    },
    watch: {
        'form.response_data.region_address'(value) {
            if (!this.form || !value) return;
            this.form.response_data.province_address = null;
            this.form.response_data.city_address = null;
            this.loadProvinces(value);
            this.locationCities = [];
        },
        'form.response_data.province_address'(value) {
            if (!this.form || !value) return;
            this.form.response_data.city_address = null;
            this.loadCities(value, this.form.response_data.region_address);
        },
    },
    methods: {
        /**
         * Get the component name for a field type
         */
        getFieldComponent(fieldType) {
            const componentMap = {
                text: 'DynamicFieldText',
                textarea: 'DynamicFieldTextarea',
                number: 'DynamicFieldNumber',
                email: 'DynamicFieldEmail',
                phone: 'DynamicFieldPhone',
                date: 'DynamicFieldDate',
                time: 'DynamicFieldDate', // Reuse date with time mode
                datetime: 'DynamicFieldDate', // Reuse date with datetime mode
                select: 'DynamicFieldSelect',
                radio: 'DynamicFieldRadio',
                checkbox: 'DynamicFieldCheckbox',
                checkbox_group: 'DynamicFieldCheckboxGroup',
                checkbox_agreement: 'DynamicFieldAgreement',
                likert_scale: 'DynamicFieldLikertScale',
                linear_scale: 'DynamicFieldLinearScale',
                checkbox_grid: 'DynamicFieldCheckboxGroup', // Fallback for now
                radio_grid: 'DynamicFieldRadio', // Fallback for now
                file: 'DynamicFieldFile',
                location_city: 'DynamicFieldLocation',
                location_province: 'DynamicFieldLocation',
                location_region: 'DynamicFieldLocation',
                section_header: 'DynamicFieldSectionHeader',
                paragraph: 'DynamicFieldParagraph',
            };
            return componentMap[fieldType] || 'DynamicFieldText';
        },

        /**
         * Check if a field is required
         */
        isFieldRequired(field) {
            const rules = field.validation_rules || {};
            return rules.required === true || rules.required === 'true';
        },

        /**
         * Get error message for a field
         */
        getFieldError(fieldKey) {
            return this.form?.errors?.[fieldKey] || null;
        },

        /**
         * Clear error for a field
         */
        clearFieldError(fieldKey) {
            if (this.form?.clearErrors) {
                this.form.clearErrors(fieldKey);
            }
        },

        /**
         * Initialize form data based on schema
         */
        initializeFormData() {
            const data = {};
            for (const field of this.fieldSchema) {
                if (field.field_type === 'section_header' || field.field_type === 'paragraph') continue;
                
                // Set default value based on field type
                const defaultValue = this.getDefaultValueForType(field);
                data[field.field_key] = this.responseData?.response_data?.[field.field_key] ?? defaultValue;
            }
            return data;
        },

        /**
         * Get default value for a field type
         */
        getDefaultValueForType(field) {
            const type = field.field_type;
            const config = field.field_config || {};

            switch (type) {
                case 'checkbox':
                case 'checkbox_agreement':
                    return false;
                case 'checkbox_group':
                    return [];
                case 'number':
                case 'likert_scale':
                case 'linear_scale':
                    return config.defaultValue ?? null;
                default:
                    return config.defaultValue ?? null;
            }
        },

        /**
         * Handle form submission
         */
        async handleSubmit() {
            if (this.isSubmitting) return;
            
            this.isSubmitting = true;
            try {
                if (this.isEditMode) {
                    await this.handleUpdate();
                } else {
                    await this.handleCreate();
                }
            } finally {
                this.isSubmitting = false;
            }
        },

        async handleCreate() {
            const response = await this.submitCreate();
            if (response?.status === 201) {
                this.model.response = response.data;
                this.showSuccess = true;
                this.$emit('createdModel', response.data);
            } else if (response?.status >= 400) {
                this.$emit('error', response);
            }
        },

        async handleUpdate() {
            const response = await this.submitUpdate(null, 'response_data');
            if (response?.status === 200) {
                this.showSuccess = true;
                this.$emit('updatedModel', response.data);
            } else if (response?.status >= 400) {
                this.$emit('error', response);
            }
        },

        /**
         * Reset success state
         */
        dismissSuccess() {
            this.showSuccess = false;
        },
    },
    beforeMount() {
        this.model = new SubformResponse();
        
        if (this.isEditMode) {
            this.setFormAction('update');
            this.form.id = this.responseData.id;
            this.form.response_data = Object.assign({}, this.responseData.response_data || {});
        } else {
            this.setFormAction('create');
            this.form.response_data = this.initializeFormData();
            this.form.form_parent_id = this.eventId;
            this.form.response_data.event_id = this.config?.event_id ?? this.eventId;
        }
        
        this.form.subform_type = this.subformType;
        
        if (this.participantId) {
            this.form.participant_id = this.participantId;
        }
    },
    mounted() {
        // Load location data if needed
        const hasLocationFields = this.fieldSchema.some(f => 
            ['location_city', 'location_province', 'location_region'].includes(f.field_type)
        );
        
        if (hasLocationFields) {
            this.loadRegions();
            if (this.form?.response_data?.region_address) {
                this.loadProvinces(this.form.response_data.region_address);
            }
            if (this.form?.response_data?.province_address) {
                this.loadCities(this.form.response_data.province_address, this.form.response_data.region_address);
            }
        }
    },
};
</script>

<template>
    <form 
        v-if="form" 
        @submit.prevent="handleSubmit()" 
        class="py-3 relative bg-white px-3" 
        :class="{'border border-red-600 rounded-md': form.hasErrors}"
    >
        <!-- Success Overlay -->
        <transition-container type="slide-top">
            <div 
                v-show="showSuccess" 
                class="absolute flex top-0 left-0 bg-AB w-full h-full z-50 text-white text-xl font-medium justify-center items-center rounded-b-md shadow"
            >
                <button @click.prevent="dismissSuccess" class="absolute top-0 right-0 p-2">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                        <path d="M2.146 2.854a.5.5 0 1 1 .708-.708L8 7.293l5.146-5.147a.5.5 0 0 1 .708.708L8.707 8l5.147 5.146a.5.5 0 0 1-.708.708L8 8.707l-5.146 5.147a.5.5 0 0 1-.708-.708L7.293 8z"/>
                    </svg>
                </button>
                <div class="flex flex-col text-center w-full gap-0.5">
                    <div v-if="registrationIDHashed" class="text-xl w-full flex flex-col gap-1 justify-center mb-1 py-2">
                        {{ registrationIDHashed }}
                        <qrcode-vue
                            :value="registrationIDHashed"
                            :size="200"
                            level="H"
                            render-as="canvas"
                            class="mx-auto border-4 shadow"
                        />
                    </div>
                    <span class="drop-shadow leading-none font-light">Submission Successful!</span>
                    <span class="drop-shadow leading-none text-sm">Check your email or take a screenshot</span>
                </div>
            </div>
        </transition-container>

        <!-- Form Header -->
        <div class="pb-3 pt-1">
            <h3 v-if="title" class="text-lg leading-tight uppercase font-extrabold">
                {{ isEditMode ? `Update ${title}` : title }}
            </h3>
            <p v-if="description" class="text-sm leading-none">
                {{ description }}
            </p>
            <label 
                v-if="form.errors.suspended || form.errors.full || form.errors.expired || form.errors.limit" 
                class="text-red-700 uppercase justify-center flex text-sm leading-tight"
            >
                {{ form.errors.suspended || form.errors.full || form.errors.expired || form.errors.limit }}
            </label>
        </div>

        <!-- Dynamic Fields -->
        <div class="flex flex-col gap-3">
            <template v-for="section in fieldSections" :key="section.header?.field_key || 'default'">
                <!-- Section Header -->
                <component 
                    v-if="section.header"
                    :is="getFieldComponent(section.header.field_type)"
                    :field="section.header"
                />

                <!-- Section Fields -->
                <template v-for="field in section.fields" :key="field.field_key">
                    <component
                        :is="getFieldComponent(field.field_type)"
                        v-model="form.response_data[field.field_key]"
                        :field="field"
                        :error="getFieldError(field.field_key)"
                        :required="isFieldRequired(field)"
                        :regions="locationRegions"
                        :provinces="locationProvinces"
                        :cities="locationCities"
                        @update:modelValue="clearFieldError(field.field_key)"
                    />
                </template>
            </template>
        </div>

        <!-- Submit Button -->
        <div class="mt-4">
            <FormButton 
                :loading="isSubmitting || form.processing"
                type="submit"
                class="w-full"
            >
                {{ isEditMode ? 'Update' : 'Submit' }}
            </FormButton>
        </div>
    </form>
</template>

<style scoped>
</style>
