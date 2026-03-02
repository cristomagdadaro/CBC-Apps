<script>
/**
 * DynamicFieldPhone.vue - Phone input field component
 */
export default {
    name: "DynamicFieldPhone",
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
            const p = this.field.placeholder || this.field.label || 'Phone Number';
            return this.required ? `${p}*` : p;
        },
    },
};
</script>

<template>
    <TextInput
        :id="field.field_key"
        v-model="inputValue"
        :required="required"
        type="tel"
        :error="error"
        :placeholder="placeholder"
        autocomplete="tel"
    />
</template>
