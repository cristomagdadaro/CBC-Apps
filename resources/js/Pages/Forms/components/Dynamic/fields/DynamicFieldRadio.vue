<script>
/**
 * DynamicFieldRadio.vue - Radio button group field component
 */
export default {
    name: "DynamicFieldRadio",
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
        options() {
            return this.field.options || [];
        },
        layout() {
            return this.field.field_config?.layout || 'vertical';
        },
        layoutClass() {
            return this.layout === 'horizontal' ? 'flex flex-row flex-wrap gap-4' : 'flex flex-col gap-2';
        },
    },
};
</script>

<template>
    <div class="relative">
        <label v-if="field.label" class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-2">
            {{ field.label }}<span v-if="required" class="text-red-600 dark:text-red-400">*</span>
        </label>
        <div :class="layoutClass">
            <label 
                v-for="option in options" 
                :key="option.value"
                class="flex items-center gap-2 cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-700 p-1 rounded transition-colors"
            >
                <input
                    type="radio"
                    :name="field.field_key"
                    :value="option.value"
                    :required="required"
                    v-model="inputValue"
                    class="w-4 h-4 text-AB focus:ring-AB border-gray-800 dark:border-gray-600 dark:bg-gray-100"
                />
                <span class="text-sm text-gray-700 dark:text-gray-200">{{ option.label }}</span>
            </label>
        </div>
        <div v-if="field.description" class="text-xs text-gray-600 dark:text-gray-400 mt-1">{{ field.description }}</div>
        <transition-container type="slide-bottom">
            <InputError v-show="!!error" class="mt-1" :message="error" />
        </transition-container>
    </div>
</template>
