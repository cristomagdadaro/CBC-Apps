<script>
/**
 * DynamicFieldNumber.vue - Number input field component
 */
export default {
    name: "DynamicFieldNumber",
    props: {
        modelValue: { type: [Number, String], default: null },
        field: { type: Object, required: true },
        error: { type: String, default: null },
        required: { type: Boolean, default: false },
    },
    emits: ['update:modelValue'],
    computed: {
        inputValue: {
            get() { return this.modelValue; },
            set(val) { this.$emit('update:modelValue', val ? Number(val) : null); }
        },
        placeholder() {
            const p = this.field.placeholder || this.field.label || '';
            return this.required ? `${p}*` : p;
        },
        min() {
            return this.field.validation_rules?.min ?? this.field.field_config?.min ?? null;
        },
        max() {
            return this.field.validation_rules?.max ?? this.field.field_config?.max ?? null;
        },
        step() {
            return this.field.field_config?.step || 1;
        },
    },
};
</script>

<template>
    <TextInput
        :id="field.field_key"
        v-model="inputValue"
        :required="required"
        type="number"
        :error="error"
        :placeholder="placeholder"
        :min="min"
        :max="max"
        :step="step"
        :autocomplete="field.field_key"
    />
</template>
