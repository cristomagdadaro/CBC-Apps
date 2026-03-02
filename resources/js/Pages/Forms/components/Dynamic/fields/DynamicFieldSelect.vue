<script>
/**
 * DynamicFieldSelect.vue - Dropdown select field component
 */
export default {
    name: "DynamicFieldSelect",
    props: {
        modelValue: { type: [String, Number], default: null },
        field: { type: Object, required: true },
        error: { type: String, default: null },
        required: { type: Boolean, default: false },
    },
    emits: ['update:modelValue'],
    computed: {
        inputValue: {
            get() { return this.modelValue; },
            set(val) { this.$emit('update:modelValue', val); }
        },
        placeholder() {
            return this.field.placeholder || this.field.field_config?.placeholder || 'Choose an option';
        },
        options() {
            return this.field.options || [];
        },
    },
};
</script>

<template>
    <div class="relative">
        <label v-if="field.label" class="block text-sm font-medium text-gray-700 mb-1">
            {{ field.label }}<span v-if="required" class="text-red-600">*</span>
        </label>
        <select
            :id="field.field_key"
            v-model="inputValue"
            :required="required"
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
        <transition-container type="slide-bottom">
            <InputError v-show="!!error" class="mt-1" :message="error" />
        </transition-container>
    </div>
</template>
