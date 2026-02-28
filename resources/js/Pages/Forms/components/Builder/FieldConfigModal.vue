<script>
/**
 * FieldConfigModal.vue
 * 
 * Modal for configuring individual field properties.
 * Handles label, placeholder, validation rules, options, etc.
 */
import { FIELD_TYPES, VALIDATION_TYPES, supportsValidation } from "@/Modules/Events/Config/FormFieldTypes";

export default {
    name: "FieldConfigModal",
    props: {
        field: {
            type: Object,
            required: true,
        },
        fieldTypes: {
            type: Object,
            default: () => FIELD_TYPES,
        },
    },
    emits: ['save', 'cancel'],
    data() {
        return {
            editedField: JSON.parse(JSON.stringify(this.field)),
            validationTypes: VALIDATION_TYPES,
            newOption: '',
        };
    },
    computed: {
        fieldTypeConfig() {
            return this.fieldTypes[this.editedField.field_type] || {};
        },
        hasOptions() {
            return this.fieldTypeConfig.hasOptions || false;
        },
        isDecorative() {
            return this.fieldTypeConfig.isDecorative || false;
        },
        supportedValidations() {
            return this.fieldTypeConfig.supportedValidations || [];
        },
        isLikertOrLinear() {
            return ['likert_scale', 'linear_scale'].includes(this.editedField.field_type);
        },
    },
    methods: {
        // ====================
        // Validation Rules
        // ====================
        isValidationEnabled(validationType) {
            const rules = this.editedField.validation_rules || {};
            return validationType in rules;
        },

        toggleValidation(validationType) {
            if (!this.editedField.validation_rules) {
                this.editedField.validation_rules = {};
            }

            if (this.isValidationEnabled(validationType)) {
                delete this.editedField.validation_rules[validationType];
            } else {
                const config = this.validationTypes[validationType];
                this.editedField.validation_rules[validationType] = config.hasValue ? '' : true;
            }
        },

        getValidationValue(validationType) {
            return this.editedField.validation_rules?.[validationType] ?? '';
        },

        setValidationValue(validationType, value) {
            if (!this.editedField.validation_rules) {
                this.editedField.validation_rules = {};
            }
            this.editedField.validation_rules[validationType] = value;
        },

        // ====================
        // Options Management
        // ====================
        addOption() {
            if (!this.newOption.trim()) return;
            
            if (!this.editedField.options) {
                this.editedField.options = [];
            }
            
            this.editedField.options.push({
                value: this.newOption.trim().toLowerCase().replace(/\s+/g, '_'),
                label: this.newOption.trim(),
            });
            
            this.newOption = '';
        },

        removeOption(index) {
            this.editedField.options.splice(index, 1);
        },

        updateOptionLabel(index, newLabel) {
            this.editedField.options[index].label = newLabel;
            this.editedField.options[index].value = newLabel.toLowerCase().replace(/\s+/g, '_');
        },

        moveOptionUp(index) {
            if (index === 0) return;
            const temp = this.editedField.options[index];
            this.editedField.options[index] = this.editedField.options[index - 1];
            this.editedField.options[index - 1] = temp;
        },

        moveOptionDown(index) {
            if (index === this.editedField.options.length - 1) return;
            const temp = this.editedField.options[index];
            this.editedField.options[index] = this.editedField.options[index + 1];
            this.editedField.options[index + 1] = temp;
        },

        // ====================
        // Scale Config
        // ====================
        updateScaleConfig(key, value) {
            if (!this.editedField.field_config) {
                this.editedField.field_config = {};
            }
            this.editedField.field_config[key] = value;
        },

        // ====================
        // Save & Cancel
        // ====================
        handleSave() {
            this.$emit('save', this.editedField);
        },

        handleCancel() {
            this.$emit('cancel');
        },
    },
};
</script>

<template>
    <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
        <div class="bg-white rounded-lg shadow-xl w-full max-w-2xl max-h-[90vh] overflow-hidden flex flex-col">
            <!-- Header -->
            <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-800">Configure Field</h3>
                <button @click="handleCancel" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <!-- Content -->
            <div class="flex-1 overflow-y-auto p-6 space-y-6">
                <!-- Basic Info -->
                <div class="space-y-4">
                    <h4 class="font-medium text-gray-700 border-b pb-2">Basic Information</h4>
                    
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-600 mb-1">Field Key</label>
                            <input
                                v-model="editedField.field_key"
                                type="text"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-AB focus:border-AB text-sm"
                                placeholder="field_key"
                            />
                            <p class="text-xs text-gray-400 mt-1">Unique identifier (no spaces)</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-600 mb-1">Field Type</label>
                            <div class="px-3 py-2 bg-gray-100 rounded-md text-sm text-gray-700">
                                {{ fieldTypeConfig.label || editedField.field_type }}
                            </div>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-600 mb-1">Label*</label>
                        <input
                            v-model="editedField.label"
                            type="text"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-AB focus:border-AB"
                            placeholder="Enter field label"
                        />
                    </div>

                    <div v-if="!isDecorative">
                        <label class="block text-sm font-medium text-gray-600 mb-1">Placeholder</label>
                        <input
                            v-model="editedField.placeholder"
                            type="text"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-AB focus:border-AB"
                            placeholder="Placeholder text"
                        />
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-600 mb-1">Description/Help Text</label>
                        <textarea
                            v-model="editedField.description"
                            rows="2"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-AB focus:border-AB resize-none"
                            placeholder="Optional help text shown below the field"
                        ></textarea>
                    </div>
                </div>

                <!-- Options (for select, radio, checkbox_group) -->
                <div v-if="hasOptions" class="space-y-4">
                    <h4 class="font-medium text-gray-700 border-b pb-2">Options</h4>
                    
                    <div class="space-y-2">
                        <div 
                            v-for="(option, index) in editedField.options" 
                            :key="index"
                            class="flex items-center gap-2"
                        >
                            <input
                                :value="option.label"
                                @input="updateOptionLabel(index, $event.target.value)"
                                type="text"
                                class="flex-1 px-3 py-2 border border-gray-300 rounded-md focus:ring-AB focus:border-AB text-sm"
                            />
                            <button 
                                @click="moveOptionUp(index)"
                                :disabled="index === 0"
                                class="p-1.5 text-gray-400 hover:text-gray-600 disabled:opacity-30"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7" />
                                </svg>
                            </button>
                            <button 
                                @click="moveOptionDown(index)"
                                :disabled="index === editedField.options.length - 1"
                                class="p-1.5 text-gray-400 hover:text-gray-600 disabled:opacity-30"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                </svg>
                            </button>
                            <button 
                                @click="removeOption(index)"
                                class="p-1.5 text-red-400 hover:text-red-600"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                    </div>

                    <div class="flex gap-2">
                        <input
                            v-model="newOption"
                            type="text"
                            class="flex-1 px-3 py-2 border border-gray-300 rounded-md focus:ring-AB focus:border-AB text-sm"
                            placeholder="Add new option"
                            @keyup.enter="addOption"
                        />
                        <button 
                            @click="addOption"
                            class="px-3 py-2 bg-gray-100 hover:bg-gray-200 rounded-md text-sm"
                        >
                            Add
                        </button>
                    </div>
                </div>

                <!-- Scale Configuration (for likert/linear scales) -->
                <div v-if="isLikertOrLinear" class="space-y-4">
                    <h4 class="font-medium text-gray-700 border-b pb-2">Scale Configuration</h4>
                    
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-600 mb-1">Minimum Value</label>
                            <input
                                :value="editedField.field_config?.min || 1"
                                @input="updateScaleConfig('min', Number($event.target.value))"
                                type="number"
                                min="0"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-AB focus:border-AB"
                            />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-600 mb-1">Maximum Value</label>
                            <input
                                :value="editedField.field_config?.max || 5"
                                @input="updateScaleConfig('max', Number($event.target.value))"
                                type="number"
                                min="1"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-AB focus:border-AB"
                            />
                        </div>
                    </div>

                    <div v-if="editedField.field_type === 'linear_scale'" class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-600 mb-1">Min Label</label>
                            <input
                                :value="editedField.field_config?.minLabel || ''"
                                @input="updateScaleConfig('minLabel', $event.target.value)"
                                type="text"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-AB focus:border-AB"
                                placeholder="e.g., Not at all"
                            />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-600 mb-1">Max Label</label>
                            <input
                                :value="editedField.field_config?.maxLabel || ''"
                                @input="updateScaleConfig('maxLabel', $event.target.value)"
                                type="text"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-AB focus:border-AB"
                                placeholder="e.g., Very much"
                            />
                        </div>
                    </div>
                </div>

                <!-- Validation Rules -->
                <div v-if="!isDecorative && supportedValidations.length > 0" class="space-y-4">
                    <h4 class="font-medium text-gray-700 border-b pb-2">Validation Rules</h4>
                    
                    <div class="space-y-3">
                        <div 
                            v-for="validationType in supportedValidations" 
                            :key="validationType"
                            class="flex items-center gap-3"
                        >
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input
                                    type="checkbox"
                                    :checked="isValidationEnabled(validationType)"
                                    @change="toggleValidation(validationType)"
                                    class="w-4 h-4 text-AB focus:ring-AB border-gray-300 rounded"
                                />
                                <span class="text-sm">{{ validationTypes[validationType]?.label || validationType }}</span>
                            </label>
                            
                            <input
                                v-if="validationTypes[validationType]?.hasValue && isValidationEnabled(validationType)"
                                :type="validationTypes[validationType]?.valueType || 'text'"
                                :value="getValidationValue(validationType)"
                                @input="setValidationValue(validationType, $event.target.value)"
                                class="flex-1 px-2 py-1 border border-gray-300 rounded text-sm"
                                placeholder="Value"
                            />
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <div class="px-6 py-4 border-t border-gray-200 flex justify-end gap-3">
                <button
                    @click="handleCancel"
                    class="px-4 py-2 text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-md transition-colors"
                >
                    Cancel
                </button>
                <button
                    @click="handleSave"
                    class="px-4 py-2 bg-AB text-white rounded-md hover:bg-AB/90 transition-colors"
                >
                    Save Changes
                </button>
            </div>
        </div>
    </div>
</template>
