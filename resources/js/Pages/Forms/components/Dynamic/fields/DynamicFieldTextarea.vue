<script>
/**
 * DynamicFieldTextarea.vue - Textarea field component
 */
export default {
    name: "DynamicFieldTextarea",
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
        rows() {
            return this.field.field_config?.rows || 4;
        },
        maxLength() {
            return this.field.validation_rules?.max || this.field.field_config?.maxLength || 2000;
        },
    },
};
</script>

<template>
    <div class="relative">
        <label v-if="field.label" class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-2">
            {{ field.label }}<span v-if="required" class="text-red-600 dark:text-red-400">*</span>
        </label>
        <div v-if="field.description" class="text-xs text-gray-600 dark:text-gray-400 mb-2">{{ field.description }}</div>
        <textarea
            :id="field.field_key"
            v-model="inputValue"
            :placeholder="placeholder"
            :rows="rows"
            :required="required"
            :maxlength="maxLength"
            class="w-full px-3 py-2 border border-gray-600 dark:border-gray-700 rounded-md bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 placeholder-gray-500 dark:placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-AB focus:border-transparent dark:focus:border-gray-600 resize-y transition-colors"
            :class="{'border-red-500 dark:border-red-600': error}"
        ></textarea>
        <transition-container type="slide-bottom">
            <InputError v-show="!!error" class="mt-1" :message="error" />
        </transition-container>
    </div>
</template>
