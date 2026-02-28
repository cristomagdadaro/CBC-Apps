<script>
/**
 * DynamicFieldCheckboxGroup.vue - Multiple checkbox selection field component
 */
export default {
    name: "DynamicFieldCheckboxGroup",
    props: {
        modelValue: { type: Array, default: () => [] },
        field: { type: Object, required: true },
        error: { type: String, default: null },
        required: { type: Boolean, default: false },
    },
    emits: ['update:modelValue'],
    computed: {
        inputValue: {
            get() { return this.modelValue || []; },
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
    methods: {
        toggleOption(value) {
            const current = [...this.inputValue];
            const index = current.indexOf(value);
            if (index === -1) {
                current.push(value);
            } else {
                current.splice(index, 1);
            }
            this.inputValue = current;
        },
        isChecked(value) {
            return this.inputValue.includes(value);
        },
    },
};
</script>

<template>
    <div class="relative">
        <label v-if="field.label" class="block text-sm font-medium text-gray-700 mb-2">
            {{ field.label }}<span v-if="required" class="text-red-600">*</span>
        </label>
        <div :class="layoutClass">
            <label 
                v-for="option in options" 
                :key="option.value"
                class="flex items-center gap-2 cursor-pointer hover:bg-gray-50 p-1 rounded"
            >
                <input
                    type="checkbox"
                    :value="option.value"
                    :checked="isChecked(option.value)"
                    @change="toggleOption(option.value)"
                    class="w-4 h-4 text-AB focus:ring-AB border-gray-300 rounded"
                />
                <span class="text-sm">{{ option.label }}</span>
            </label>
        </div>
        <div v-if="field.description" class="text-xs text-gray-500 mt-1">{{ field.description }}</div>
        <transition-container type="slide-bottom">
            <InputError v-show="!!error" class="mt-1" :message="error" />
        </transition-container>
    </div>
</template>
