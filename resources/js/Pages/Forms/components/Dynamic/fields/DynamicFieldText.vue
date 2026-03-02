<script>
/**
 * DynamicFieldText.vue - Text input field component
 */
export default {
    name: "DynamicFieldText",
    props: {
        modelValue: { type: [String, Number], default: '' },
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
        maxLength() {
            return this.field.validation_rules?.max || this.field.field_config?.maxLength || 255;
        },
    },
};
</script>

<template>
    <TextInput
        :id="field.field_key"
        v-model="inputValue"
        type="text"
        :error="error"
        :required="required"
        :placeholder="placeholder"
        :maxlength="maxLength"
        :autocomplete="field.field_key"
    />
</template>
