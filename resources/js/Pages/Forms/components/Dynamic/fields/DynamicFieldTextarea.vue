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
        <textarea
            :id="field.field_key"
            v-model="inputValue"
            :placeholder="placeholder"
            :rows="rows"
            :required="required"
            :maxlength="maxLength"
            class="w-full px-3 py-2 border border-gray-600 rounded-md focus:outline-none focus:ring-2 focus:ring-AB focus:border-transparent resize-y"
            :class="{'border-red-500': error}"
        ></textarea>
        <div v-if="field.description" class="text-xs text-gray-500 mt-1">{{ field.description }}</div>
        <transition-container type="slide-bottom">
            <InputError v-show="!!error" class="mt-1" :message="error" />
        </transition-container>
    </div>
</template>
