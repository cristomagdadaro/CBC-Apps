<script>
/**
 * DynamicFieldLocation.vue - Philippine location selector field component
 * Handles region, province, and city selection
 */
export default {
    name: "DynamicFieldLocation",
    props: {
        modelValue: { type: String, default: null },
        field: { type: Object, required: true },
        error: { type: String, default: null },
        required: { type: Boolean, default: false },
        disabled: { type: Boolean, default: false },
        regions: { type: Array, default: () => [] },
        provinces: { type: Array, default: () => [] },
        cities: { type: Array, default: () => [] },
    },
    emits: ['update:modelValue'],
    computed: {
        inputValue: {
            get() { return this.modelValue; },
            set(val) { this.$emit('update:modelValue', val); }
        },
        placeholder() {
            const p = this.field.placeholder || this.field.label || 'Select';
            return this.required ? `${p}*` : p;
        },
        locationType() {
            return this.field.field_type; // location_city, location_province, location_region
        },
        options() {
            switch (this.locationType) {
                case 'location_region':
                    return this.regions.map(r => ({ value: r, label: r }));
                case 'location_province':
                    return this.provinces.map(p => ({ value: p, label: p }));
                case 'location_city':
                    return this.cities.map(c => ({ value: c, label: c }));
                default:
                    return [];
            }
        },
    },
    watch: {
        options: {
            immediate: true,
            handler() {
                this.applyConfiguredDefaultIfMissing();
            },
        },
    },
    methods: {
        applyConfiguredDefaultIfMissing() {
            const currentValue = this.modelValue;
            if (currentValue !== null && currentValue !== undefined && currentValue !== '') {
                return;
            }

            const configuredDefault = this.field?.field_config?.defaultValue;
            if (configuredDefault === null || configuredDefault === undefined || configuredDefault === '') {
                return;
            }

            const hasMatchingOption = this.options.some(option => option.value === configuredDefault);
            if (hasMatchingOption) {
                this.$emit('update:modelValue', configuredDefault);
            }
        },
    },
};
</script>

<template>
    <div class="relative">
        <div v-if="field.label" class="text-xs text-gray-700 dark:text-gray-200 flex items-center justify-between">
            <span class="flex gap-0.5 whitespace-nowrap">{{ field.label }} <b v-if="required" class="text-red-500 ">*</b></span>
            <transition-container type="slide-bottom">
                <InputError v-show="!!error" class="" :message="error" />
            </transition-container>
        </div>
        <select
            :id="field.field_key"
            v-model="inputValue"
            :required="required"
            :disabled="disabled"
            class="w-full px-3 py-2 border border-gray-600 rounded-md focus:outline-none focus:ring-2 focus:ring-AB focus:border-transparent bg-white"
            :class="{'border-red-500': error}"
        >
            <option value="" disabled>{{ placeholder }}</option>
            <option 
                v-for="option in options" 
                :key="option.value" 
                :value="option.value"
            >
                {{ option.label }}
            </option>
        </select>
        <div v-if="field.description" class="text-xs text-gray-500 mt-1">{{ field.description }}</div>
        
    </div>
</template>
