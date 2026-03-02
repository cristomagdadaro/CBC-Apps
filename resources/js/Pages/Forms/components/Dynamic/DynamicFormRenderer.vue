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
import html2canvas from "html2canvas";

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
import DynamicFieldAgreeUpdates from "./fields/DynamicFieldAgreeUpdates.vue";
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
        DynamicFieldAgreeUpdates,
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
            isDownloadingImage: false,
            locationData: {
                regions: [],
                provinces: [],
                cities: [],
            },
            // Skip logic state
            currentSectionIndex: 0,
            skipToSubmit: false,
            skippedSections: new Set(),
        };
    },
    computed: {
        isEditMode() {
            return !!this.responseData?.id;
        },
        participantIdHash() {
            return this.model?.response?.participant_hash
                ?? this.model?.response?.registration?.id
                ?? this.model?.response?.data?.participant_hash
                ?? this.model?.response?.data?.id
                ?? this.model?.response?.id
                ?? null;
        },
        successEventTitle() {
            return this.config?.event_title
                ?? this.config?.event?.title
                ?? this.config?.title
                ?? 'Event';
        },
        successEventId() {
            return this.config?.event_id ?? this.eventId;
        },
        successSubformTitle() {
            return this.title || this.config?.title || this.resolvedSubformType;
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
            let currentSection = { header: null, fields: [], index: 0 };

            for (const field of this.sortedFields) {
                if (field.field_type === 'section_header') {
                    if (currentSection.fields.length > 0 || currentSection.header) {
                        sections.push(currentSection);
                    }
                    currentSection = { header: field, fields: [], index: sections.length };
                } else {
                    currentSection.fields.push(field);
                }
            }
            
            if (currentSection.fields.length > 0 || currentSection.header) {
                currentSection.index = sections.length;
                sections.push(currentSection);
            }

            return sections.length > 0 ? sections : [{ header: null, fields: this.sortedFields, index: 0 }];
        },
        /**
         * Check if form has any skip logic configured
         */
        hasSkipLogic() {
            return this.sortedFields.some(field => {
                if (!field.options || !Array.isArray(field.options)) return false;
                return field.options.some(opt => opt.skipTo);
            });
        },
        hasSectionHeaders() {
            return this.sortedFields.some(field => field.field_type === 'section_header') && this.fieldSections.length > 1;
        },
        progressSteps() {
            if (!this.hasSectionHeaders) {
                return [];
            }

            return this.fieldSections.map((section, index) => {
                const headerLabel = section.header?.label || section.header?.placeholder;
                return headerLabel || `Section ${index + 1}`;
            });
        },
        /**
         * Get visible sections based on skip logic
         */
        visibleSections() {
            if (this.hasSectionHeaders) {
                const active = this.fieldSections[this.currentSectionIndex] || null;
                return active ? [active] : [];
            }

            return this.fieldSections;
        },
        /**
         * Check if there are more sections after the current one
         */
        hasNextSection() {
            return this.currentSectionIndex < this.fieldSections.length - 1 && !this.skipToSubmit;
        },
        /**
         * Check if currently on last visible section
         */
        isLastSection() {
            return this.currentSectionIndex >= this.fieldSections.length - 1 || this.skipToSubmit;
        },
        regionFieldKey() {
            return this.fieldSchema.find(f => f.field_type === 'location_region')?.field_key ?? null;
        },
        provinceFieldKey() {
            return this.fieldSchema.find(f => f.field_type === 'location_province')?.field_key ?? null;
        },
        cityFieldKey() {
            return this.fieldSchema.find(f => f.field_type === 'location_city')?.field_key ?? null;
        },
        selectedRegionValue() {
            if (!this.form || !this.regionFieldKey) return null;
            return this.form.response_data?.[this.regionFieldKey] ?? null;
        },
        selectedProvinceValue() {
            if (!this.form || !this.provinceFieldKey) return null;
            return this.form.response_data?.[this.provinceFieldKey] ?? null;
        },
        resolvedSubformType() {
            if (typeof this.subformType === 'string' && !this.subformType.includes('-')) {
                return this.subformType;
            }

            if (typeof this.config?.form_type === 'string' && this.config.form_type.trim() !== '') {
                return this.config.form_type;
            }

            return this.subformType;
        },
    },
    watch: {
        selectedRegionValue(value, oldValue) {
            if (!this.form) return;

            if (!value) {
                this.locationProvinces = [];
                this.locationCities = [];
                if (this.provinceFieldKey) this.form.response_data[this.provinceFieldKey] = null;
                if (this.cityFieldKey) this.form.response_data[this.cityFieldKey] = null;
                return;
            }

            if (oldValue !== undefined && value !== oldValue) {
                if (this.provinceFieldKey) this.form.response_data[this.provinceFieldKey] = null;
                if (this.cityFieldKey) this.form.response_data[this.cityFieldKey] = null;
                this.locationCities = [];
            }

            this.loadProvinces(value);
        },
        selectedProvinceValue(value, oldValue) {
            if (!this.form) return;

            if (!value) {
                this.locationCities = [];
                if (this.cityFieldKey) this.form.response_data[this.cityFieldKey] = null;
                return;
            }

            if (oldValue !== undefined && value !== oldValue && this.cityFieldKey) {
                this.form.response_data[this.cityFieldKey] = null;
            }

            this.loadCities(value, this.selectedRegionValue);
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
                checkbox_updates: 'DynamicFieldAgreeUpdates',
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
        isFieldChangeable(field) {
            return field?.field_config?.changeable !== false;
        },
        isFieldLocked(field) {
            return !this.isFieldChangeable(field);
        },
        getFieldDefaultValue(field) {
            return field?.field_config?.defaultValue;
        },
        applyLockedDefaults(formData = {}) {
            const updated = { ...formData };

            for (const field of this.fieldSchema) {
                if (field.field_type === 'section_header' || field.field_type === 'paragraph') continue;
                if (!this.isFieldLocked(field)) continue;

                const defaultValue = this.getFieldDefaultValue(field);
                if (defaultValue !== undefined) {
                    updated[field.field_key] = defaultValue;
                }
            }

            return updated;
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
         * Handle field value change and check for skip logic
         */
        handleFieldChange(fieldKey, value, field) {
            if (this.isFieldLocked(field)) {
                const defaultValue = this.getFieldDefaultValue(field);
                if (defaultValue !== undefined) {
                    this.form.response_data[fieldKey] = defaultValue;
                }
                return;
            }

            this.clearFieldError(fieldKey);
            
            // Check for skip logic on choice fields
            if (field && field.options && Array.isArray(field.options)) {
                const selectedOption = field.options.find(opt => opt.value === value);
                if (selectedOption?.skipTo) {
                    this.handleSkipLogic(selectedOption.skipTo);
                }
            }
        },

        /**
         * Execute skip logic based on target
         */
        handleSkipLogic(targetKey) {
            if (targetKey === '__submit__') {
                // Skip to submit - mark all remaining sections as skipped
                this.skipToSubmit = true;
                return;
            }

            // Find the target section by field_key
            const targetIndex = this.fieldSections.findIndex(
                section => section.header?.field_key === targetKey
            );

            if (targetIndex !== -1 && targetIndex > this.currentSectionIndex) {
                // Mark sections between current and target as skipped
                for (let i = this.currentSectionIndex + 1; i < targetIndex; i++) {
                    this.skippedSections.add(i);
                }
                this.currentSectionIndex = targetIndex;
            }
        },

        /**
         * Move to next section
         */
        goToNextSection() {
            if (this.hasNextSection) {
                let nextIndex = this.currentSectionIndex + 1;
                // Skip any sections that were marked as skipped
                while (this.skippedSections.has(nextIndex) && nextIndex < this.fieldSections.length) {
                    nextIndex++;
                }
                this.currentSectionIndex = nextIndex;
            }
        },

        /**
         * Move to previous section
         */
        goToPreviousSection() {
            if (this.currentSectionIndex > 0) {
                let prevIndex = this.currentSectionIndex - 1;
                // Skip any sections that were marked as skipped (going backwards)
                while (this.skippedSections.has(prevIndex) && prevIndex > 0) {
                    prevIndex--;
                }
                this.currentSectionIndex = prevIndex;
                this.skipToSubmit = false; // Reset submit flag when going back
            }
        },
        onSectionTabChange(index) {
            if (!this.hasSectionHeaders) {
                return;
            }

            if (index < 0 || index >= this.fieldSections.length) {
                return;
            }

            this.currentSectionIndex = index;
            if (index < this.fieldSections.length - 1) {
                this.skipToSubmit = false;
            }
        },

        /**
         * Check if a field supports skip logic
         */
        fieldSupportsSkipLogic(field) {
            return ['select', 'radio'].includes(field.field_type);
        },

        /**
         * Check if a field is a location field type
         */
        isLocationField(field) {
            return ['location_city', 'location_province', 'location_region'].includes(field.field_type);
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
            return this.applyLockedDefaults(data);
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
                case 'checkbox_updates':
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
        buildSuccessImageName() {
            const eventPart = String(this.successEventId || 'event').trim();
            const subformPart = String(this.successSubformTitle || 'subform').trim();
            const hashPart = String(this.participantIdHash || 'reference').trim();
            return [eventPart, subformPart, hashPart]
                .join('-')
                .toLowerCase()
                .replace(/[^a-z0-9-]+/g, '-')
                .replace(/-+/g, '-')
                .replace(/^-|-$/g, '');
        },
        async downloadSuccessAsPng() {
            if (this.isDownloadingImage) {
                return;
            }

            const target = this.$refs.successOverlay;
            if (!target) {
                return;
            }

            this.isDownloadingImage = true;
            try {
                const computedBackgroundColor = window.getComputedStyle(target).backgroundColor;
                const canvas = await html2canvas(target, {
                    backgroundColor: computedBackgroundColor,
                    scale: Math.max(window.devicePixelRatio || 1, 2),
                    useCORS: true,
                });

                const link = document.createElement('a');
                link.href = canvas.toDataURL('image/png');
                link.download = `${this.buildSuccessImageName() || 'submission'}-success.png`;
                document.body.appendChild(link);
                link.click();
                link.remove();
            } catch (error) {
                this.$emit('error', error);
            } finally {
                this.isDownloadingImage = false;
            }
        },
    },
    beforeMount() {
        this.model = new SubformResponse();
        
        if (this.isEditMode) {
            this.setFormAction('update');
            this.form.id = this.responseData.id;
            this.form.response_data = this.applyLockedDefaults(Object.assign({}, this.responseData.response_data || {}));
        } else {
            this.setFormAction('create');
            this.form.response_data = this.initializeFormData();
            this.form.form_parent_id = this.eventId;
            this.form.response_data.event_id = this.config?.event_id ?? this.eventId;
        }
        
        this.form.subform_type = this.resolvedSubformType;
        
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
            if (this.selectedRegionValue) {
                this.loadProvinces(this.selectedRegionValue);
            }
            if (this.selectedProvinceValue) {
                this.loadCities(this.selectedProvinceValue, this.selectedRegionValue);
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
                ref="successOverlay"
                :id="`success-${successEventId}-${participantIdHash}`"
                class="absolute flex top-0 left-0 bg-AB w-full h-full z-50 text-white text-xl font-medium justify-center items-center rounded-b-md shadow"
            >
                <button @click.prevent="dismissSuccess" class="absolute top-0 right-0 p-2">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                        <path d="M2.146 2.854a.5.5 0 1 1 .708-.708L8 7.293l5.146-5.147a.5.5 0 0 1 .708.708L8.707 8l5.147 5.146a.5.5 0 0 1-.708.708L8 8.707l-5.146 5.147a.5.5 0 0 1-.708-.708L7.293 8z"/>
                    </svg>
                </button>
                <div ref="successCapture" class="flex flex-col text-center w-full gap-0.5 px-3">
                    <div class="text-sm leading-tight opacity-95 mb-1">
                        <div>{{ successEventId }} - {{ successSubformTitle }}</div>
                    </div>
                    <div v-if="participantIdHash" class="text-sm w-full flex flex-col gap-1 justify-center mb-1 py-2">
                        <qrcode-vue
                            :value="participantIdHash"
                            :size="300"
                            level="H"
                            render-as="canvas"
                            class="mx-auto border-4 shadow"
                        />
                        {{ participantIdHash }}
                    </div>
                    <span class="drop-shadow leading-none text-sm">DA-Crop Biotechnology Center</span>
                    <span class="drop-shadow leading-none text-sm font-light">cropbiotechcenter@gmail.com</span>
                    <button
                        type="button"
                        data-html2canvas-ignore="true"
                        class="mx-auto mt-3 px-3 py-1 text-sm rounded-md border border-white/80 hover:bg-white/15 disabled:opacity-60"
                        :disabled="isDownloadingImage"
                        @click.prevent="downloadSuccessAsPng"
                    >
                        {{ isDownloadingImage ? 'Preparing PNG...' : 'Save this QR for your next step' }}
                    </button>
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

        <!-- Section Progress -->
        <div v-if="hasSectionHeaders" class="mb-4">
            <ProgressTabs
                :steps="progressSteps"
                :current="currentSectionIndex"
                :clickable="true"
                :showProgress="true"
                @update:current="onSectionTabChange"
            />
            <div v-if="hasSkipLogic && skippedSections.size > 0" class="text-xs text-blue-600 mt-1 text-right">
                {{ skippedSections.size }} section{{ skippedSections.size > 1 ? 's' : '' }} skipped
            </div>
        </div>

        <!-- Dynamic Fields -->
        <transition name="section-slide" mode="out-in">
        <div class="flex flex-col gap-3" :key="hasSectionHeaders ? `section-${currentSectionIndex}` : 'single-section'">
            <template v-for="section in visibleSections" :key="section.header?.field_key || 'default'">
                <!-- Section Header -->
                <component 
                    v-if="section.header"
                    :is="getFieldComponent(section.header.field_type)"
                    :field="section.header"
                />

                <!-- Section Fields -->
                <template v-for="field in section.fields" :key="field.field_key">
                    <div :class="isFieldLocked(field) ? 'opacity-80 pointer-events-none cursor-not-allowed' : 'cursor-text'" :title="isFieldLocked(field) ? 'Default value is locked for this field.' : ''">
                        <component
                            :is="getFieldComponent(field.field_type)"
                            v-model="form.response_data[field.field_key]"
                            :field="field"
                            :error="getFieldError(field.field_key)"
                            :label="field.label"
                            :placeholder="field.placeholder"
                            :required="isFieldRequired(field)"
                            :disabled="isFieldLocked(field)"
                            v-bind="isLocationField(field) ? { regions: locationRegions, provinces: locationProvinces, cities: locationCities } : {}"
                            @update:modelValue="handleFieldChange(field.field_key, $event, field)"
                        />
                    </div>
                </template>
            </template>
        </div>
        </transition>

        <!-- Section Navigation -->
        <div v-if="hasSectionHeaders" class="mt-4 flex items-center justify-between gap-2">
            <button
                v-if="currentSectionIndex > 0"
                type="button"
                @click="goToPreviousSection"
                class="px-4 py-2 text-sm border border-gray-300 rounded-md hover:bg-gray-50 transition"
            >
                ← Previous
            </button>
            <div v-else></div>
            
            <button
                v-if="hasNextSection && !isLastSection"
                type="button"
                @click="goToNextSection"
                class="px-4 py-2 text-sm bg-AB text-white rounded-md hover:bg-AB/90 transition"
            >
                Next →
            </button>
        </div>

        <!-- Submit Button -->
        <div class="mt-4" v-if="!hasSectionHeaders || isLastSection">
            <button 
                :disabled="isSubmitting || form.processing"
                type="submit"
                class="w-full px-4 py-2 bg-AB text-white font-semibold rounded-md hover:bg-AB/90 transition disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-2"
            >
                <svg 
                    v-if="isSubmitting || form.processing" 
                    class="animate-spin h-4 w-4" 
                    xmlns="http://www.w3.org/2000/svg" 
                    fill="none" 
                    viewBox="0 0 24 24"
                >
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                {{ isEditMode ? 'Update' : 'Submit' }}
            </button>
        </div>
    </form>
</template>

<style scoped>
.section-slide-enter-active,
.section-slide-leave-active {
    transition: all 0.25s ease;
}

.section-slide-enter-from {
    opacity: 0;
    transform: translateX(22px);
}

.section-slide-leave-to {
    opacity: 0;
    transform: translateX(-22px);
}
</style>
