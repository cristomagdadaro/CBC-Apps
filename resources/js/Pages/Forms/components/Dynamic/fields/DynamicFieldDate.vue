<script>
/**
 * DynamicFieldDate.vue - Date/Time input field component
 */
export default {
    name: "DynamicFieldDate",
    props: {
        modelValue: { type: String, default: '' },
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
            const p = this.field.placeholder || this.field.label || '';
            return this.required ? `${p}*` : p;
        },
        inputType() {
            if (this.field.field_type === 'datetime') return 'datetime-local';
            if (this.field.field_type === 'time') return 'time';
            return 'date';
        },
    },
};
</script>

<template>
    <div class="relative">
        <label v-if="field.label" class="block text-sm font-medium text-gray-700 mb-1">
            {{ field.label }}<span v-if="required" class="text-red-600">*</span>
        </label>
        <input
            :id="field.field_key"
            v-model="inputValue"
            :type="inputType"
            :required="required"
            :placeholder="placeholder"
            class="w-full px-3 py-2 border border-gray-600 rounded-md focus:outline-none focus:ring-2 focus:ring-AB focus:border-transparent"
            :class="{'border-red-500': error}"
        />
        <div v-if="field.description" class="text-xs text-gray-500 mt-1">{{ field.description }}</div>
        <transition-container type="slide-bottom">
            <InputError v-show="!!error" class="mt-1" :message="error" />
        </transition-container>
    </div>
</template>
