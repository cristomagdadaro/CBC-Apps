<script>
/**
 * DynamicFieldEmail.vue - Email input field component
 */
export default {
    name: "DynamicFieldEmail",
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
            const p = this.field.placeholder || this.field.label || 'Email';
            return this.required ? `${p}*` : p;
        },
    },
};
</script>

<template>
    <TextInput
        :id="field.field_key"
        v-model="inputValue"
        type="email"
        :error="error"
        :placeholder="placeholder"
        autocomplete="email"
    />
</template>
