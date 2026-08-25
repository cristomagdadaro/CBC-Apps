<script>
/**
 * FieldConfigModal.vue
 *
 * Modal for configuring individual field properties.
 * Handles label, placeholder, validation rules, options, etc.
 */
import { FIELD_TYPES, VALIDATION_TYPES, supportsValidation } from "@/Modules/Events/Config/FormFieldTypes";
import ApiMixin from "@/Modules/mixins/ApiMixin";

export default {
    name: "FieldConfigModal",
    mixins: [ApiMixin],
    props: {
        field: {
            type: Object,
            required: true,
        },
        fieldTypes: {
            type: Object,
            default: () => FIELD_TYPES,
        },
        /**
         * All sections in the form (for skip logic targeting)
         */
        sections: {
            type: Array,
            default: () => [],
        },
    },
    emits: ["save", "cancel"],
    data() {
        return {
            editedField: JSON.parse(JSON.stringify(this.field)),
            validationTypes: VALIDATION_TYPES,
            newOption: "",
            locationRegions: [],
            locationProvinces: [],
            locationCities: [],
            countryOptions: ["Philippines", "Brunei", "Cambodia", "Indonesia", "Laos", "Malaysia", "Myanmar", "Singapore", "Thailand", "Vietnam", "Japan", "South Korea", "China", "India", "United States", "Canada", "Australia", "United Kingdom", "Germany", "France"],
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
        canConfigureDefaultValue() {
            return !this.isDecorative && this.editedField.field_type !== "file";
        },
        defaultValueInputType() {
            if (["number", "likert_scale", "linear_scale"].includes(this.editedField.field_type)) {
                return "number";
            }
            if (["email"].includes(this.editedField.field_type)) {
                return "email";
            }
            if (["phone"].includes(this.editedField.field_type)) {
                return "tel";
            }
            if (["date"].includes(this.editedField.field_type)) {
                return "date";
            }
            if (["datetime"].includes(this.editedField.field_type)) {
                return "datetime-local";
            }
            if (["time"].includes(this.editedField.field_type)) {
                return "time";
            }
            return "text";
        },
        usesOptionDefault() {
            return ["select", "radio"].includes(this.editedField.field_type);
        },
        usesLocationDefault() {
            return ["location_region", "location_province", "location_city"].includes(this.editedField.field_type);
        },
        isCountryLikeField() {
            const haystack = `${this.editedField.field_key || ""} ${this.editedField.label || ""} ${this.editedField.placeholder || ""}`.toLowerCase();
            return haystack.includes("country");
        },
        usesCountryDefault() {
            return !this.usesOptionDefault && (this.editedField.field_type === "location_country" || this.isCountryLikeField);
        },
        locationDefaultOptions() {
            if (this.editedField.field_type === "location_region") {
                return this.locationRegions;
            }
            if (this.editedField.field_type === "location_province") {
                return this.locationProvinces;
            }
            if (this.editedField.field_type === "location_city") {
                return this.locationCities;
            }
            return [];
        },
        usesBooleanDefault() {
            return ["checkbox", "checkbox_agreement"].includes(this.editedField.field_type);
        },
        usesArrayDefault() {
            return this.editedField.field_type === "checkbox_group";
        },
        isLikertOrLinear() {
            return ["likert_scale", "linear_scale"].includes(this.editedField.field_type);
        },
        supportsSkipLogic() {
            return this.fieldTypeConfig.supportsSkipLogic || false;
        },
        skipLogicTargets() {
            // Available targets: sections + special actions
            const targets = [
                { value: "", label: "Continue to next" },
                { value: "__submit__", label: "Submit form" },
            ];
            // Add all sections as targets
            this.sections.forEach((section, index) => {
                const label = section.label || `Section ${index + 1}`;
                targets.push({ value: section.field_key, label: `Go to: ${label}` });
            });
            return targets;
        },
    },
    methods: {
        async loadLocationDefaults() {
            if (!this.usesLocationDefault) return;

            const [regionsResponse, provincesResponse, citiesResponse] = await Promise.all([this.fetchGetApi("api.locations.regions"), this.fetchGetApi("api.locations.provinces"), this.fetchGetApi("api.locations.cities", { region: null, province: null })]);

            this.locationRegions = regionsResponse?.data ?? [];
            this.locationProvinces = provincesResponse?.data ?? [];
            this.locationCities = citiesResponse?.data ?? [];
        },
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
                this.editedField.validation_rules[validationType] = config.hasValue ? "" : true;
            }
        },

        getValidationValue(validationType) {
            return this.editedField.validation_rules?.[validationType] ?? "";
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
                value: this.newOption.trim().toLowerCase().replace(/\s+/g, "_"),
                label: this.newOption.trim(),
            });

            this.newOption = "";
        },

        removeOption(index) {
            this.editedField.options.splice(index, 1);
        },

        updateOptionLabel(index, newLabel) {
            this.editedField.options[index].label = newLabel;
            this.editedField.options[index].value = newLabel.toLowerCase().replace(/\s+/g, "_");
        },

        updateOptionSkipTo(index, targetKey) {
            if (!this.editedField.options[index]) return;
            this.editedField.options[index].skipTo = targetKey || null;
        },

        getOptionSkipTo(index) {
            return this.editedField.options[index]?.skipTo || "";
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
        ensureFieldConfig() {
            if (!this.editedField.field_config) {
                this.editedField.field_config = {};
            }
        },
        isFieldChangeable() {
            return this.editedField.field_config?.changeable !== false;
        },
        setFieldChangeable(value) {
            this.ensureFieldConfig();
            this.editedField.field_config.changeable = value;
        },
        getDefaultValue() {
            return this.editedField.field_config?.defaultValue;
        },
        setDefaultValue(value) {
            this.ensureFieldConfig();
            this.editedField.field_config.defaultValue = value;
        },
        toggleArrayDefaultOption(value, checked) {
            const current = Array.isArray(this.getDefaultValue()) ? [...this.getDefaultValue()] : [];
            const index = current.indexOf(value);

            if (checked && index === -1) {
                current.push(value);
            }
            if (!checked && index !== -1) {
                current.splice(index, 1);
            }

            this.setDefaultValue(current);
        },

        // ====================
        // Save & Cancel
        // ====================
        handleSave() {
            this.$emit("save", this.editedField);
        },

        handleCancel() {
            this.$emit("cancel");
        },
    },
    mounted() {
        this.loadLocationDefaults();
    },
};
</script>

<template>
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 p-4">
        <div class="flex max-h-[90vh] w-full max-w-2xl flex-col overflow-hidden rounded-lg bg-white shadow-xl">
            <!-- Header -->
            <div class="flex items-center justify-between border-b border-gray-200 px-6 py-4">
                <h3 class="text-lg font-semibold text-gray-800">Configure Field</h3>
                <button
                    @click="handleCancel"
                    class="text-gray-400 hover:text-gray-600">
                    <svg
                        class="h-5 w-5"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <!-- Content -->
            <div class="flex-1 space-y-6 overflow-y-auto p-6">
                <!-- Basic Info -->
                <div class="space-y-4">
                    <h4 class="border-b pb-2 font-medium text-gray-700">Basic Information</h4>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="mb-1 block text-sm font-medium text-gray-600">Field Key</label>
                            <input
                                v-model="editedField.field_key"
                                type="text"
                                class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-AB focus:ring-AB"
                                placeholder="field_key" />
                            <p class="mt-1 text-xs text-gray-400">Unique identifier (no spaces)</p>
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-medium text-gray-600">Field Type</label>
                            <div class="rounded-md bg-gray-100 px-3 py-2 text-sm text-gray-700">
                                {{ editedField.field_type }}
                            </div>
                        </div>
                    </div>

                    <div>
                        <label class="mb-1 block text-sm font-medium text-gray-600">Label*</label>
                        <input
                            v-model="editedField.label"
                            type="text"
                            class="w-full rounded-md border border-gray-300 px-3 py-2 focus:border-AB focus:ring-AB"
                            placeholder="Enter field label" />
                    </div>

                    <div v-if="!isDecorative">
                        <label class="mb-1 block text-sm font-medium text-gray-600">Placeholder</label>
                        <input
                            v-model="editedField.placeholder"
                            type="text"
                            class="w-full rounded-md border border-gray-300 px-3 py-2 focus:border-AB focus:ring-AB"
                            placeholder="Placeholder text" />
                    </div>

                    <div>
                        <label class="mb-1 block text-sm font-medium text-gray-600">Description/Help Text</label>
                        <textarea
                            v-model="editedField.description"
                            rows="2"
                            class="w-full resize-none rounded-md border border-gray-300 px-3 py-2 focus:border-AB focus:ring-AB"
                            placeholder="Optional help text shown below the field"></textarea>
                    </div>

                    <div
                        v-if="canConfigureDefaultValue"
                        class="space-y-3 rounded-md border border-gray-200 bg-gray-50 p-3">
                        <div class="flex items-center justify-between">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Default Value</label>
                                <p class="text-xs text-gray-500">Sets the pre-filled value when this field loads.</p>
                            </div>
                        </div>

                        <div v-if="usesOptionDefault || usesLocationDefault || usesCountryDefault">
                            <select
                                :value="getDefaultValue() ?? ''"
                                @change="setDefaultValue($event.target.value || null)"
                                class="w-full rounded-md border border-gray-300 px-3 py-2 focus:border-AB focus:ring-AB">
                                <option value="">No default</option>
                                <option
                                    v-for="option in usesLocationDefault
                                        ? locationDefaultOptions.map((value) => ({
                                              value,
                                              label: value,
                                          }))
                                        : usesCountryDefault
                                          ? countryOptions.map((value) => ({ value, label: value }))
                                          : editedField.options || []"
                                    :key="option.value"
                                    :value="option.value">
                                    {{ option.label }}
                                </option>
                            </select>
                        </div>

                        <div
                            v-else-if="usesBooleanDefault"
                            class="flex items-center gap-3">
                            <label class="flex items-center gap-2 text-sm">
                                <input
                                    type="checkbox"
                                    :checked="!!getDefaultValue()"
                                    @change="setDefaultValue($event.target.checked)"
                                    class="h-4 w-4 rounded border-gray-300 text-AB focus:ring-AB" />
                                Checked by default
                            </label>
                        </div>

                        <div
                            v-else-if="usesArrayDefault"
                            class="space-y-2">
                            <label class="block text-sm text-gray-600">Default selected options</label>
                            <label
                                v-for="option in editedField.options || []"
                                :key="option.value"
                                class="flex items-center gap-2 text-sm">
                                <input
                                    type="checkbox"
                                    :checked="(getDefaultValue() || []).includes(option.value)"
                                    @change="toggleArrayDefaultOption(option.value, $event.target.checked)"
                                    class="h-4 w-4 rounded border-gray-300 text-AB focus:ring-AB" />
                                {{ option.label }}
                            </label>
                        </div>

                        <div v-else>
                            <input
                                :value="getDefaultValue() ?? ''"
                                @input="setDefaultValue($event.target.value === '' ? null : $event.target.value)"
                                :type="defaultValueInputType"
                                class="w-full rounded-md border border-gray-300 px-3 py-2 focus:border-AB focus:ring-AB"
                                placeholder="Set default value" />
                        </div>

                        <label class="flex items-center gap-2 text-sm text-gray-700">
                            <input
                                type="checkbox"
                                :checked="isFieldChangeable()"
                                @change="setFieldChangeable($event.target.checked)"
                                class="h-4 w-4 rounded border-gray-300 text-AB focus:ring-AB" />
                            Allow respondents to change this value
                        </label>
                    </div>
                </div>

                <!-- Options (for select, radio, checkbox_group) -->
                <div
                    v-if="hasOptions"
                    class="space-y-4">
                    <h4 class="border-b pb-2 font-medium text-gray-700">Options</h4>

                    <!-- Skip Logic Info -->
                    <div
                        v-if="supportsSkipLogic && sections.length > 0"
                        class="rounded bg-blue-50 p-2 text-xs text-gray-500">
                        <strong>Skip Logic:</strong>
                        You can configure each option to jump to a specific section when selected.
                    </div>

                    <div class="space-y-2">
                        <div
                            v-for="(option, index) in editedField.options"
                            :key="index"
                            class="flex flex-col gap-1 rounded-md bg-gray-50 p-2">
                            <div class="flex items-center gap-2">
                                <input
                                    :value="option.label"
                                    @input="updateOptionLabel(index, $event.target.value)"
                                    type="text"
                                    class="flex-1 rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-AB focus:ring-AB"
                                    placeholder="Option label" />
                                <button
                                    @click="moveOptionUp(index)"
                                    :disabled="index === 0"
                                    class="p-1.5 text-gray-400 hover:text-gray-600 disabled:opacity-30"
                                    title="Move up">
                                    <svg
                                        class="h-4 w-4"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M5 15l7-7 7 7" />
                                    </svg>
                                </button>
                                <button
                                    @click="moveOptionDown(index)"
                                    :disabled="index === editedField.options.length - 1"
                                    class="p-1.5 text-gray-400 hover:text-gray-600 disabled:opacity-30"
                                    title="Move down">
                                    <svg
                                        class="h-4 w-4"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M19 9l-7 7-7-7" />
                                    </svg>
                                </button>
                                <button
                                    @click="removeOption(index)"
                                    class="p-1.5 text-red-400 hover:text-red-600"
                                    title="Remove option">
                                    <svg
                                        class="h-4 w-4"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>
                            <!-- Skip Logic Dropdown -->
                            <div
                                v-if="supportsSkipLogic && sections.length > 0"
                                class="ml-2 flex items-center gap-2">
                                <label class="whitespace-nowrap text-xs text-gray-500">Go to:</label>
                                <select
                                    :value="getOptionSkipTo(index)"
                                    @change="updateOptionSkipTo(index, $event.target.value)"
                                    class="flex-1 rounded border border-gray-300 px-2 py-1 text-xs focus:border-AB focus:ring-AB">
                                    <option
                                        v-for="target in skipLogicTargets"
                                        :key="target.value"
                                        :value="target.value">
                                        {{ target.label }}
                                    </option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="flex gap-2">
                        <input
                            v-model="newOption"
                            type="text"
                            class="flex-1 rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-AB focus:ring-AB"
                            placeholder="Add new option"
                            @keyup.enter="addOption" />
                        <button
                            @click="addOption"
                            class="rounded-md bg-gray-100 px-3 py-2 text-sm hover:bg-gray-200">
                            Add
                        </button>
                    </div>
                </div>

                <!-- Scale Configuration (for likert/linear scales) -->
                <div
                    v-if="isLikertOrLinear"
                    class="space-y-4">
                    <h4 class="border-b pb-2 font-medium text-gray-700">Scale Configuration</h4>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="mb-1 block text-sm font-medium text-gray-600">Minimum Value</label>
                            <input
                                :value="editedField.field_config?.min || 1"
                                @input="updateScaleConfig('min', Number($event.target.value))"
                                type="number"
                                min="0"
                                class="w-full rounded-md border border-gray-300 px-3 py-2 focus:border-AB focus:ring-AB" />
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-medium text-gray-600">Maximum Value</label>
                            <input
                                :value="editedField.field_config?.max || 5"
                                @input="updateScaleConfig('max', Number($event.target.value))"
                                type="number"
                                min="1"
                                class="w-full rounded-md border border-gray-300 px-3 py-2 focus:border-AB focus:ring-AB" />
                        </div>
                    </div>

                    <div
                        v-if="editedField.field_type === 'linear_scale'"
                        class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="mb-1 block text-sm font-medium text-gray-600">Min Label</label>
                            <input
                                :value="editedField.field_config?.minLabel || ''"
                                @input="updateScaleConfig('minLabel', $event.target.value)"
                                type="text"
                                class="w-full rounded-md border border-gray-300 px-3 py-2 focus:border-AB focus:ring-AB"
                                placeholder="e.g., Not at all" />
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-medium text-gray-600">Max Label</label>
                            <input
                                :value="editedField.field_config?.maxLabel || ''"
                                @input="updateScaleConfig('maxLabel', $event.target.value)"
                                type="text"
                                class="w-full rounded-md border border-gray-300 px-3 py-2 focus:border-AB focus:ring-AB"
                                placeholder="e.g., Very much" />
                        </div>
                    </div>
                </div>

                <!-- Validation Rules -->
                <div
                    v-if="!isDecorative && supportedValidations.length > 0"
                    class="space-y-4">
                    <h4 class="border-b pb-2 font-medium text-gray-700">Validation Rules</h4>

                    <div class="space-y-3">
                        <div
                            v-for="validationType in supportedValidations"
                            :key="validationType"
                            class="flex items-center gap-3">
                            <label class="flex cursor-pointer items-center gap-2">
                                <input
                                    type="checkbox"
                                    :checked="isValidationEnabled(validationType)"
                                    @change="toggleValidation(validationType)"
                                    class="h-4 w-4 rounded border-gray-300 text-AB focus:ring-AB" />
                                <span class="text-sm">
                                    {{ validationTypes[validationType]?.label || validationType }}
                                </span>
                            </label>

                            <input
                                v-if="validationTypes[validationType]?.hasValue && isValidationEnabled(validationType)"
                                :type="validationTypes[validationType]?.valueType || 'text'"
                                :value="getValidationValue(validationType)"
                                @input="setValidationValue(validationType, $event.target.value)"
                                class="flex-1 rounded border border-gray-300 px-2 py-1 text-sm"
                                placeholder="Value" />
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <div class="flex justify-end gap-3 border-t border-gray-200 px-6 py-4">
                <button
                    @click="handleCancel"
                    class="rounded-md bg-gray-100 px-4 py-2 text-gray-700 transition-colors hover:bg-gray-200">
                    Cancel
                </button>
                <button
                    @click="handleSave"
                    class="rounded-md bg-AB px-4 py-2 text-white transition-colors hover:bg-AB/90">
                    Save Changes
                </button>
            </div>
        </div>
    </div>
</template>
